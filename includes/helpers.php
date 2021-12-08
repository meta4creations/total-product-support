<?php
	
/**
 * Retrieves a template part
 *
 * @access  public
 * @since   1.0.0
 */

function tops_get_template_part( $name, $view_variables = array(), $return = true ) {
	
	// Execute code for this part
	do_action( 'get_template_part_'.$name, $name, $view_variables );
 
	// Setup possible parts
	$templates = array();
	$templates[] = $name.'.php';
	 
	// Allow template parts to be filtered
	$templates = apply_filters( 'tops_get_template_part', $templates, $name, $view_variables );
 
	// Return the part that is found
	return tops_locate_template( $templates, $view_variables, $return = true );
}


/**
 * Retrieve the name of the highest priority template file that exists
 *
 * @access  public
 * @since   1.0.0
 */

function tops_locate_template( $template_names, $view_variables = array(), $return ) {
	
	// No file found yet
	$file_path = false;
	 
	// Try to find a template file
	foreach( (array) $template_names as $template_name ) {
 
		// Continue if template is empty
		if( empty($template_name) ) {
			continue;
		}
 
		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );
		 
		// Check child theme first
		if( file_exists(trailingslashit(get_stylesheet_directory()).'tops/'.$template_name) ) {
			$file_path = trailingslashit(get_stylesheet_directory()).'tops/'.$template_name;
			break;

		// Check parent theme next
		} elseif( file_exists(trailingslashit(get_template_directory()).'tops/'.$template_name) ) {
			$file_path = trailingslashit(get_template_directory()).'tops/'.$template_name;
			break;
			
		// Check theme compatibility last
		} elseif( file_exists(TOPS_PLUGIN_DIR.'templates/'.$template_name) ) {
			$file_path = TOPS_PLUGIN_DIR.'templates/'.$template_name;
			break;
		}
	}
		
	if( !empty($file_path) ) {
		//load_template( $file_path, $require_once );
		return tops_render_view( $file_path, $view_variables, $return );
	}
}


function tops_render_view( $file_path, $view_variables = array(), $return = true ) {
	
	extract( $view_variables, EXTR_REFS );
	unset( $view_variables );

	if( $return ) {
		ob_start();
		require $file_path;
		return ob_get_clean();
	} else {
		require $file_path;
	}
}


/**
 * Return a single localized string
 *
 * @access  public
 * @since   1.0.0
 */

function tops_string( $key ) {
	
	$strings = tops_strings();
	return array_key_exists($key, $strings) ? $strings[$key] : __('Localized string does not exist!', 'total-product-support');
}


/**
 * Return localized strings
 *
 * @access  public
 * @since   1.0.0
 */

function tops_strings() {
	
	$strings = array(
		'post_reply' => __('Post Reply', 'total-product-support'),
		'post_reply_and_close' => __('Post Reply & Close', 'total-product-support'),
		'reply_privately' => __('Reply Privately', 'total-product-support'),
		'reply_privately_and_close' => __('Reply Privately & Close', 'total-product-support'),
		'post_reply_and_close' => __('Post Reply & Close', 'total-product-support'),
		'add_note' => __('Add Note', 'total-product-support'),
		'save_customer_notes' => __('Save Customer Notes', 'total-product-support'),
		'replied' => __('replied', 'total-product-support'),
		'replied_privately' => __('replied privately', 'total-product-support'),
		'added_a_note' => __('added a note', 'total-product-support'),
		'update_comment' => __('Update Comment', 'total-product-support'),
	  'update_private_comment' => __('Update Private Comment', 'total-product-support'),
	  'mark_as_unread' => __('Mark as Unread', 'total-product-support'),
	  'needs_response' => __('Needs Response', 'total-product-support'),
	  'confirm_delete_comment' => __('Are you sure you want to delete this comment?', 'total-product-support'),
	  'confirm_delete_attachment' => __('Are you sure you want to delete this attachment?', 'total-product-support')
	);
	
	return apply_filters( 'tops_strings', $strings );
}


/**
 * Return a single localized string
 *
 * @access  public
 * @since   1.0.0
 */

function tops_template( $key, $args=array() ) {
	
	$templates = tops_templates();

	if( !array_key_exists($key, $templates) ) {
		return __('HTML template does not exist!', 'total-product-support');
	}
	
	$template = $templates[$key];
	if( is_array($args) && count($args) > 0 ) {
		foreach( $args as $key=>$value ) {
			$template = preg_replace( '/{{'.$key.'}}/i', $value, $template );
		}
	}
	
	return $template;
}


/**
 * Return localized strings
 *
 * @access  public
 * @since   1.0.0
 */

function tops_templates() {
	
	$templates = array(
		'comment_attachment' =>'<span class="tops-ticket-comment-attachment">{{icon}}<a rel="comment_{{comment_id}}" class="tops-ticket-attachment-filename tops-swipebox" href="{{url}}">{{filename}}</a><a class="tops-ticket-attachment-remove" href="#" data-comment-id="{{comment_id}}" data-attachment-id="{{id}}"><i class="fa fa-minus-circle" aria-hidden="true"></i></a></span>'
	);
	
	return apply_filters( 'tops_html_templates', $templates );
}


/**
 * Remove any duplicated posts between 2 queries
 *
 * @access  public
 * @since   1.0.0
 */
function tops_remove_duplicate_posts( $post_a, $post_b ) {
  return $post_a->ID - $post_b->ID;
}


/**
 * Determines if we're currently on a single Ticket page.
 *
 * @since 1.0.0
 * @return bool True if on a single Ticket page, false otherwise.
 */
function tops_is_ticket() {
	return apply_filters( 'tops_is_ticket', is_singular('tops_ticket') );
}


/**
 * Determines if we're currently on the New Ticket page.
 *
 * @since 1.0.0
 * @return bool True if on the New Ticket page, false otherwise.
 */
function tops_is_new_ticket_page() {
	$is_new_ticket_page = tops_get_option( 'tickets_new', false );
	$is_new_ticket_page = isset( $is_new_ticket_page ) ? is_page( $is_new_ticket_page ) : false;

	return apply_filters( 'tops_is_new_ticket_page', $is_new_ticket_page );
}


/**
 * Determines if we're currently on the Tickets page.
 *
 * @since 1.0.0
 * @return bool True if on the Tickets page, false otherwise.
 */
function tops_is_tickets_page() {
	$is_tickets_page = tops_get_option( 'tickets_page', false );
	$is_tickets_page = isset( $is_tickets_page ) ? is_page( $is_tickets_page ) : false;

	return apply_filters( 'tops_is_tickets_page', $is_tickets_page );
}


/**
 * Determines if we're currently on the Ticket Archives page.
 *
 * @since 1.0.0
 * @return bool True if on the Ticket Archives page, false otherwise.
 */
function tops_is_ticket_archives_page() {
	$is_ticket_archives_page = tops_get_option( 'ticket_archives', false );
	$is_ticket_archives_page = isset( $is_ticket_archives_page ) ? is_page( $is_ticket_archives_page ) : false;

	return apply_filters( 'tops_is_ticket_archives_page', $is_ticket_archives_page );
}


/**
 * Determines if we're currently on the Ticket Categories page.
 *
 * @since 1.0.0
 * @return bool True if on the Ticket Categories page, false otherwise.
 */
function tops_is_ticket_categories_page() {
	$is_ticket_categories_page = tops_get_option( 'ticket_categories', false );
	$is_ticket_categories_page = isset( $is_ticket_categories_page ) ? is_page( $is_ticket_categories_page ) : false;

	return apply_filters( 'tops_is_ticket_categories_page', $is_ticket_categories_page );
}


/**
 * Determines if we're currently on the Public Tickets page.
 *
 * @since 1.0.0
 * @return bool True if on the Public Tickets  page, false otherwise.
 */
function tops_is_tickets_public_page() {
	$is_tickets_public_page = tops_get_option( 'tickets_public', false );
	$is_tickets_public_page = isset( $is_tickets_public_page ) ? is_page( $is_tickets_public_page ) : false;

	return apply_filters( 'tops_is_tickets_public_page', $is_tickets_public_page );
}


/**
 * Determines if we're currently on the Private Tickets page.
 *
 * @since 1.0.0
 * @return bool True if on the Private Tickets  page, false otherwise.
 */
function tops_is_tickets_private_page() {
	$is_tickets_private_page = tops_get_option( 'tickets_private', false );
	$is_tickets_private_page = isset( $is_tickets_private_page ) ? is_page( $is_tickets_private_page ) : false;

	return apply_filters( 'tops_is_tickets_private_page', $is_tickets_private_page );
}


/**
 * Determines if we're currently on the Starred Tickets page.
 *
 * @since 1.0.0
 * @return bool True if on the Starred Tickets  page, false otherwise.
 */
function tops_is_tickets_starred_page() {
	$is_tickets_starred_page = tops_get_option( 'tickets_starred', false );
	$is_tickets_starred_page = isset( $is_tickets_starred_page ) ? is_page( $is_tickets_starred_page ) : false;

	return apply_filters( 'tops_is_tickets_starred_page', $is_tickets_starred_page );
}
