<?php
	
/**
 * New ticket form submit via ajax
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_new_ticket_submit() {

	// Get access to the database
	global $wpdb;

	// Check the nonce
	check_ajax_referer( 'total-product-support', 'security' );
		
	$data = array(
		'name' => isset($_POST['your-name']) ? sanitize_text_field($_POST['your-name']) : false,
		'email' => isset($_POST['your-email']) ? $_POST['your-email'] : false,
		'password' => isset($_POST['your-password']) ? sanitize_text_field($_POST['your-password']) : false,
		'password_again' => isset($_POST['your-password-again']) ? sanitize_text_field($_POST['your-password-again']) : false,
		'product' => isset( $_POST['product'] ) ? $_POST['product'] : false,
		'license' => isset( $_POST['license'] ) ? sanitize_text_field($_POST['license']) : false,
		'subject' => sanitize_text_field($_POST['subject']),
		'related_url' => (isset($_POST['related-url']) && $_POST['related-url'] != '') ? esc_url_raw($_POST['related-url']) : '',
		'comment' => wp_kses_post($_POST['topsticketcomment']),
		'type' => isset($_POST['type']) ? esc_attr($_POST['type']) : 'private'
	);
	
	// Get the current user id, if there is one
	$post_id = 0;
	$user_id = 0;
	if( $current_user = wp_get_current_user() ) {
		$user_id = $current_user->ID;
	}	
	$error = false;
	
	// Create a new user, possibly
	if( isset($data['email']) && is_email($data['email']) ) {
		$user_id = username_exists( $data['email'] );
		if( !$user_id and email_exists($data['email']) == false ) {
			$userdata = array(
				'user_email' => $data['email'],
		    'user_login' => $data['email'],
		    'user_pass' => $data['password'],
		    'user_nicename' => sanitize_title($data['name']),
		    'display_name' => $data['name'],
		    'nickname' => $data['name']
			);
			$user_id = wp_insert_user( $userdata );
			if( is_wp_error($user_id) ) {
				$error = $user_id->get_error_message();
			}
		} else {
			$error = __('User already exists.', 'total-product-support');
		}
	}
		
	if( !$error && $user_id != 0 ) {
		
		// Login the user
		wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );
    
		// Create a new ticket
		$ticket = new TOPS_Ticket();
		$post_id = $ticket->create( $data );
		
		if( is_wp_error($post_id) ) {
			
			$error = $post_id->get_error_message();
			
		} elseif( $post_id ) {
			
			// Add the ticket product
			add_post_meta( $post_id, '_tops_ticket_product', esc_attr( $data['product'] ) );
			
			// Allow extra data to be saved to the ticket post
			do_action( 'tops_ticket_after_create_submit', $post_id );

		} else {
			$error = __('Error: Ticket was not created', 'total-product-support');
		}
	}

	if( $error ) {
		$return = array(
			'error' => $error
		);
	} else {	
		$return = array(
			'success' => get_permalink( $post_id ),
		);
	}
	
	wp_send_json( $return );
}
add_action( 'wp_ajax_tops_ticket_new_ticket_submit', 'tops_ticket_new_ticket_submit' );
add_action( 'wp_ajax_nopriv_tops_ticket_new_ticket_submit', 'tops_ticket_new_ticket_submit' );


/**
 * Comment form submit via ajax
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_comment_submit() {

	// Get access to the database
	global $wpdb;

	// Check the nonce
	check_ajax_referer( 'total-product-support', 'security' );
	
	/**
	 * Add the comment
	 * 
	 * Required attributes:
	 * - ticket_id
	 * - post_id
	 * - comment
	 */
	
	$args = array(
		'id' => isset($_POST['tops-ticket-id']) ? intval($_POST['tops-ticket-id']) : false,
		'post_id' => isset($_POST['tops-ticket-post-id']) ? intval($_POST['tops-ticket-post-id']) : false,
		'comment' => isset($_POST['topsticketcomment']) ? wp_kses_post($_POST['topsticketcomment']) : false,
		'close' => isset($_POST['tops-ticket-close-ticket']) ? esc_attr($_POST['tops-ticket-close-ticket']) : false,
		'type' => isset($_POST['tops-ticket-comment-type']) ? esc_attr($_POST['tops-ticket-comment-type']) : 'public',
		'object' => isset($_POST['tops-ticket-comment-object']) ? esc_attr($_POST['tops-ticket-comment-object']) : 'comment',
		'attachments' => isset($_POST['tops-ticket-comment-attachments']) ? esc_attr($_POST['tops-ticket-comment-attachments']) : '',
	);
	if( $args['object'] == 'customer-note' ) {
		if( isset($_POST['tops-ticket-user-id']) ) {
			update_usermeta( intval($_POST['tops-ticket-user-id']), 'tops_customer_notes', $args['comment'] );
		} else {
			$ticket = new TOPS_Ticket($args['id']);
			update_usermeta( $ticket->get_user_id(), 'tops_customer_notes', $args['comment'] );			
		}
		$return = array(
			'updated' => $args['comment']
		);
		
	} else {
		
		if( isset($args['object']) && $args['object'] == 'note' ) {
			
			$note_id = TOPS()->tickets->add_note( $args );
			$return = array(
				'success' => tops_get_template_part( 'single-ticket/note', array('id'=>$note_id) )
			);
			
		} else {
			
			$comment_id = TOPS()->tickets->add_comment( $args );
			$return = array(
				'success' => tops_get_template_part( 'single-ticket/comment', array('id'=>$comment_id) ),
				'type' => $args['type']
			);
		}
	}
	//exit();
	wp_send_json( $return );
}
add_action( 'wp_ajax_tops_ticket_comment_submit', 'tops_ticket_comment_submit' );
add_action( 'wp_ajax_nopriv_tops_ticket_comment_submit', 'tops_ticket_comment_submit' );


/**
 * Comment edit form submit via ajax
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_comment_edit_submit() {

	// Get access to the database
	global $wpdb;

	// Check the nonce
	check_ajax_referer( 'total-product-support', 'security' );
	
	$data = array(
		'id' => isset($_POST['tops-ticket-comment-id']) ? esc_attr($_POST['tops-ticket-comment-id']) : false,
		'comment' => isset($_POST['topsticketedit']) ? wp_kses_post($_POST['topsticketedit']) : false,
		'type' => isset($_POST['tops-ticket-comment-type']) ? esc_attr($_POST['tops-ticket-comment-type']) : false,
	);
	// Update the comment
	$comment = new TOPS_Ticket_Comment();
	$comment->update( $data );
			
	// Allow extra data to be saved to the ticket post
	do_action( 'tops_ticket_after_comment_edit_submit', $data );

	$return = array(
		'success' => $data
	);

	wp_send_json( $return );
}
add_action( 'wp_ajax_tops_ticket_comment_edit_submit', 'tops_ticket_comment_edit_submit' );
add_action( 'wp_ajax_nopriv_tops_ticket_comment_edit_submit', 'tops_ticket_comment_edit_submit' );


/**
 * Update ticket meta
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_update() {

	// Get access to the database
	global $wpdb;

	// Check the nonce
	check_ajax_referer( 'total-product-support', 'security' );
	
	$ticket_id = isset($_POST['id']) ? $_POST['id'] : false;
	$tops_action = isset($_POST['tops_action']) ? $_POST['tops_action'] : false;
	
	if( $ticket_id && $tops_action ) {
		
		unset($_POST['tops_action']);
		unset($_POST['action']);
		unset($_POST['security']);

		do_action( 'tops_ticket_before_update', $ticket_id, $_POST );
		do_action( "tops_ticket_before_{$tops_action}", $ticket_id, $_POST );

		$ticket = new TOPS_Ticket( $ticket_id );
		
		switch( $tops_action ) {
			case 'set_as_read':
				$ticket->set_as_read();
				break;
				
			case 'set_as_unread':
				$ticket->set_as_unread();
				break;
				
			case 'set_as_starred':
				$ticket->set_as_starred();
				break;
				
			case 'set_as_unstarred':
				$ticket->set_as_unstarred();
				break;
				
			case 'set_as_private':
				$ticket->set_as_private();
				break;
		}
			
		do_action( 'tops_ticket_after_update', $ticket_id, $_POST );
		do_action( "tops_ticket_after_{$tops_action}", $ticket_id, $_POST );

		$return = array(
			'success' => $_POST
		);
		
	} else {
		$return = array(
			'error' => __('No action set', 'total-product-support')
		);
	}
	
	wp_send_json( $return );
}
add_action( 'wp_ajax_tops_ticket_update', 'tops_ticket_update' );
add_action( 'wp_ajax_nopriv_tops_ticket_update', 'tops_ticket_update' );


/**
 * Delete comment via ajax
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_delete_comment() {

	// Get access to the database
	global $wpdb;

	// Check the nonce
	check_ajax_referer( 'total-product-support', 'security' );
	
	if( isset($_POST['id']) ) {
		$data = array(
			'id' => esc_attr($_POST['id']),
			'object' => isset($_POST['object']) ? esc_attr($_POST['object']) : 'comment',
		);
		
		if( $data['object'] == 'note' ) {
			TOPS()->tickets->delete_note( $data['id'] );
		} else {
			TOPS()->tickets->delete_comment( $data['id'] );
		}
		
		do_action( 'tops_ticket_after_comment_deleted', $data );
	
		$return = array(
			'success' => true
		);
		
	} else {
		
		$return = array(
			'error' => __('No comment ID supplied', 'total-product-support')
		);
	}
	
	wp_send_json( $return );
}
add_action( 'wp_ajax_tops_ticket_delete_comment', 'tops_ticket_delete_comment' );
add_action( 'wp_ajax_nopriv_tops_ticket_delete_comment', 'tops_ticket_delete_comment' );


/**
 * Flag comment via ajax
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_flag_comment() {

	// Get access to the database
	global $wpdb;

	// Check the nonce
	check_ajax_referer( 'total-product-support', 'security' );
	
	$data = array(
		'id' => isset($_POST['id']) ? esc_attr($_POST['id']) : false,
		'is_flagged' => isset($_POST['is_flagged']) ? sanitize_text_field($_POST['is_flagged']) : 'no',
	);

	$comment = new TOPS_Ticket_Comment( $data['id'] );
	$comment->update( $data );

	do_action( 'tops_ticket_after_comment_flagged', $data );

	$return = array(
		'success' => $comment
	);

	wp_send_json( $return );
}
add_action( 'wp_ajax_tops_ticket_flag_comment', 'tops_ticket_flag_comment' );
add_action( 'wp_ajax_nopriv_tops_ticket_flag_comment', 'tops_ticket_flag_comment' );


/**
 * Delete an attachment via ajax
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_delete_attachment() {

	// Get access to the database
	global $wpdb;

	// Check the nonce
	check_ajax_referer( 'total-product-support', 'security' );
	
	$data = array(
		'id' => isset($_POST['id']) ? esc_attr($_POST['id']) : false,
		'attachment_id' => isset($_POST['attachment_id']) ? esc_attr($_POST['attachment_id']) : false,
	);

	$comment = new TOPS_Ticket_Comment( $data['id'] );
	$comment->delete_attachment( $data['attachment_id'] );

	do_action( 'tops_ticket_after_delete_attachment', $data );

/*
	$return = array(
		'success' => $comment
	);
*/

	//wp_send_json( $return );
	exit;
}
add_action( 'wp_ajax_tops_ticket_delete_attachment', 'tops_ticket_delete_attachment' );
add_action( 'wp_ajax_nopriv_tops_ticket_delete_attachment', 'tops_ticket_delete_attachment' );