<?php
/**
 * Email Functions
 *
 * @package     TOPS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Notify the ticket agent of a new ticket
 *
 * @since 1.0.0
 * @param in $post_id Ticket post id
 * @param bool $admin_notice Whether to send the admin email notification or not (default: true)
 * @return void
 */
function tops_new_ticket_email( $post_id, $admin_notice = true, $to_email = '' ) {
	
	$ticket = new TOPS_Ticket( $post_id, 'post_id' );
	
	$from_name    = tops_get_option( 'new_ticket_from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name    = apply_filters( 'tops_new_ticket_from_name', $from_name, $ticket );

	$from_email   = tops_get_option( 'new_ticket_from_email', get_bloginfo( 'admin_email' ) );
	$from_email   = apply_filters( 'new_ticket_from_email', $from_email, $ticket );

	if( empty( $to_email ) ) {
		$to_email = $ticket->get_agent_email();
	}

	$subject      = tops_get_option( 'new_ticket_subject', sprintf(__( '%s: New Ticket from {user_name} [Ticket #{id}]', 'total-product-support' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )) );
	$subject      = apply_filters( 'tops_new_ticket_subject', wp_strip_all_tags( $subject ), $ticket );
	$subject      = wp_specialchars_decode( tops_do_email_tags($subject, $ticket) );

	$heading      = tops_get_option( 'new_ticket_heading', sprintf(__( '%s: New Ticket from {user_name} [Ticket #{id}]', 'total-product-support' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )) );
	$heading      = apply_filters( 'tops_new_ticket_heading', $heading, $ticket );
	$heading      = tops_do_email_tags( $heading, $ticket );


	//$attachments  = apply_filters( 'tops_receipt_attachments', array(), $payment_id, $payment_data );
	$message      = tops_do_email_tags( tops_get_ticket_email_body_content($ticket), $ticket );
	$emails = TOPS()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );


	$headers = apply_filters( 'tops_new_ticket_headers', $emails->get_headers(), $ticket );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message );
	//$emails->send( $to_email, $subject, $message, $attachments );

/*
	if ( $admin_notice && ! tops_admin_notices_disabled( $payment_id ) ) {
		do_action( 'tops_admin_sale_notice', $payment_id, $payment_data );
	}
*/
}

/**
 * Notify users of new ticket comments
 *
 * @since 1.0.0
 * @param in $post_id Ticket post id
 * @param bool $admin_notice Whether to send the admin email notification or not (default: true)
 * @return void
 */
function tops_new_comment_email( $data, $comment_id, $to_email = '' ) {

	$comment = new TOPS_Ticket_Comment( $comment_id );
	$ticket = new TOPS_Ticket( $comment->get_ticket_id() );
	
	$from_name    = tops_get_option( 'new_comment_from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name    = apply_filters( 'tops_new_comment_from_name', $from_name, $comment );

	$from_email   = tops_get_option( 'new_comment_from_email', get_bloginfo( 'admin_email' ) );
	$from_email   = apply_filters( 'new_comment_from_email', $from_email, $comment );

	if( empty( $to_email ) ) {
		if( $comment->get_user_id() == $ticket->get_user_id() ) {
			$to_email = $ticket->get_agent_email();
		} else {
			$to_email = $ticket->get_user_email();
		}
	}

	$subject      = tops_get_option( 'new_comment_subject', sprintf(__( '%s: Ticket Comment from {user_name} [Ticket #{ticket_id}]', 'total-product-support' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )) );
	$subject      = apply_filters( 'tops_new_comment_subject', wp_strip_all_tags( $subject ), $comment );
	$subject      = wp_specialchars_decode( tops_do_email_tags($subject, $comment) );

	$heading      = tops_get_option( 'new_comment_heading', sprintf(__( '%s: Ticket Comment from {user_name} [Ticket #{ticket_id}]', 'total-product-support' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )) );
	$heading      = apply_filters( 'tops_new_comment_heading', $heading, $comment );
	$heading      = tops_do_email_tags( $heading, $comment );

	//$attachments  = apply_filters( 'tops_receipt_attachments', array(), $payment_id, $payment_data );
	$message = tops_do_email_tags( tops_get_comment_email_body_content($comment), $comment );
	$emails = TOPS()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'tops_new_comment_headers', $emails->get_headers(), $comment );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message );
	//$emails->send( $to_email, $subject, $message, $attachments );

/*
	if ( $admin_notice && ! tops_admin_notices_disabled( $payment_id ) ) {
		do_action( 'tops_admin_sale_notice', $payment_id, $payment_data );
	}
*/
}

/**
 * Email the download link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.5
 * @return void
 */
function tops_email_test_purchase_receipt() {

	$from_name   = tops_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name   = apply_filters( 'tops_purchase_from_name', $from_name, 0, array() );

	$from_email  = tops_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'tops_test_purchase_from_address', $from_email, 0, array() );

	$subject     = tops_get_option( 'purchase_subject', __( 'Purchase Receipt', 'total-product-support' ) );
	$subject     = apply_filters( 'tops_purchase_subject', wp_strip_all_tags( $subject ), 0 );
	$subject     = tops_do_email_tags( $subject, 0 );

	$heading     = tops_get_option( 'purchase_heading', __( 'Purchase Receipt', 'total-product-support' ) );
	$heading     = apply_filters( 'tops_purchase_heading', $heading, 0, array() );

	$attachments = apply_filters( 'tops_receipt_attachments', array(), 0, array() );

	$message     = tops_do_email_tags( tops_get_email_body_content( 0, array() ), 0 );

	$emails = TOPS()->emails;
	$emails->__set( 'from_name' , $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading'   , $heading );

	$headers = apply_filters( 'tops_receipt_headers', $emails->get_headers(), 0, array() );
	$emails->__set( 'headers', $headers );

	$emails->send( tops_get_admin_notice_emails(), $subject, $message, $attachments );

}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.4.2
 * @param int $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 * @return void
 */
function tops_admin_email_notice( $payment_id = 0, $payment_data = array() ) {

	$payment_id = absint( $payment_id );

	if( empty( $payment_id ) ) {
		return;
	}

	if( ! tops_get_payment_by( 'id', $payment_id ) ) {
		return;
	}

	$from_name   = tops_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name   = apply_filters( 'tops_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email  = tops_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'tops_admin_sale_from_address', $from_email, $payment_id, $payment_data );

	$subject     = tops_get_option( 'sale_notification_subject', sprintf( __( 'New download purchase - Order #%1$s', 'total-product-support' ), $payment_id ) );
	$subject     = apply_filters( 'tops_admin_sale_notification_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject     = tops_do_email_tags( $subject, $payment_id );

	$headers     = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers    .= "Reply-To: ". $from_email . "\r\n";
	//$headers  .= "MIME-Version: 1.0\r\n";
	$headers    .= "Content-Type: text/html; charset=utf-8\r\n";
	$headers     = apply_filters( 'tops_admin_sale_notification_headers', $headers, $payment_id, $payment_data );

	$attachments = apply_filters( 'tops_admin_sale_notification_attachments', array(), $payment_id, $payment_data );

	$message     = tops_get_sale_notification_body_content( $payment_id, $payment_data );

	$emails = TOPS()->emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'headers', $headers );
	$emails->__set( 'heading', __( 'New Sale!', 'total-product-support' ) );

	$emails->send( tops_get_admin_notice_emails(), $subject, $message, $attachments );

}
add_action( 'tops_admin_sale_notice', 'tops_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the TOPS Settings)
 *
 * @since 1.0
 * @return mixed
 */
function tops_get_admin_notice_emails() {
	$emails = tops_get_option( 'admin_notice_emails', false );
	$emails = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'tops_admin_notice_emails', $emails );
}

/**
 * Checks whether admin sale notices are disabled
 *
 * @since 1.5.2
 *
 * @param int $payment_id
 * @return mixed
 */
function tops_admin_notices_disabled( $payment_id = 0 ) {
	$ret = tops_get_option( 'disable_admin_notices', false );
	return (bool) apply_filters( 'tops_admin_notices_disabled', $ret, $payment_id );
}

/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @return string $message
 */
function tops_get_default_new_ticket_notification_email() {
	$default_email_body = __( 'Hello', 'total-product-support' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'total-product-support' ), tops_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'total-product-support' ), tops_get_label_plural() ) . "\n\n";
	$default_email_body .= '{download_list}' . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'total-product-support' ) . ' {name}' . "\n";
	$default_email_body .= __( 'Amount: ', 'total-product-support' ) . ' {price}' . "\n";
	$default_email_body .= __( 'Payment Method: ', 'total-product-support' ) . ' {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you', 'total-product-support' );

	$message = tops_get_option( 'sale_notification', false );
	$message = ! empty( $message ) ? $message : $default_email_body;

	return $message;
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since 1.9
 * @param $user_info
 *
 * @return array $email_names
 */
function tops_get_email_names( $user_info ) {
	$email_names = array();
	$user_info 	= maybe_unserialize( $user_info );

	$email_names['fullname'] = '';
	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
		$user_data = get_userdata( $user_info['id'] );
		$email_names['name']      = $user_info['first_name'];
		$email_names['fullname']  = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names['username']  = $user_data->user_login;
	} elseif ( isset( $user_info['first_name'] ) ) {
		$email_names['name']     = $user_info['first_name'];
		$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names['username'] = $user_info['first_name'];
	} else {
		$email_names['name']     = $user_info['email'];
		$email_names['username'] = $user_info['email'];
	}

	return $email_names;
}
