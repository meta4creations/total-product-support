<?php
	
	
/**
 * The TOPS login shortcode
 *
 * @access  public
 * @since   1.0.0
 */

function tops_login_form_display( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'id' => '',
		'class' => ''
	), $atts ) );
	
	$html = '';
	
	if( is_user_logged_in() ) {
		$html .= '<p>'.__('You are already logged in!', 'total-product-support').'</p>';
	} else {
		$html .= '<div id="tops-login-form" class="tops-form-container">';
			$args = array( 'echo' => false );
			$html .= wp_login_form( $args );
		$html .= '</div>';
	}
	
	return $html;
}
add_shortcode( 'tops_login_form', 'tops_login_form_display' );


/**
 * TOPS tickets list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_tickets_display( $atts, $content = null ) {
	
	$category = isset($_GET['category']) ? esc_attr($_GET['category']) : false;
	
	if( $category ) {
		return tops_get_template_part( 'tickets-category', array('category'=>$category) );
	} elseif( current_user_can('edit_tops_tickets') ) {
		return tops_get_template_part( 'tickets' );
	} else {
		return tops_get_template_part( 'tickets-customer' );
	}
}
add_shortcode( 'tops_tickets', 'tops_tickets_display' );


/**
 * TOPS tickets archive list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_archive_display() {
	return tops_get_template_part( 'tickets-archive' );
}
add_shortcode( 'tops_tickets_archive', 'tops_ticket_archive_display' );


/**
 * TOPS tickets starred list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_starred_display() {
	return tops_get_template_part( 'tickets-starred' );
}
add_shortcode( 'tops_tickets_starred', 'tops_ticket_starred_display' );


/**
 * TOPS tickets public list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_public_display() {
	return tops_get_template_part( 'tickets-public' );
}
add_shortcode( 'tops_tickets_public', 'tops_ticket_public_display' );


/**
 * TOPS tickets private list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_private_display() {
	return tops_get_template_part( 'tickets-private' );
}
add_shortcode( 'tops_tickets_private', 'tops_ticket_private_display' );


/**
 * TOPS tickets category list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_tickets_category_display() {
	return tops_get_template_part( 'tickets-category' );
}
add_shortcode( 'tops_tickets_category', 'tops_tickets_category_display' );


/**
 * Create a new ticket shortcode
 *
 * @access  public
 * @since   1.0.0
 */

function tops_new_ticket_form_display( $atts, $content = null ) {
	return tops_get_template_part( 'create-ticket' );
}
add_shortcode( 'tops_new_ticket_form', 'tops_new_ticket_form_display' );