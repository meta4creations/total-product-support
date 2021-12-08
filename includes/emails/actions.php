<?php
/**
 * Email Actions
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
 * Triggers a New Ticket email to be sent to the ticket agent after a new ticket is created
 *
 * @since 1.0.0
 * @param int $post_id Ticket post id
 * @return void
 */
function tops_trigger_new_ticket_email( $post_id ) {
	// Make sure we don't send a purchase receipt while editing a payment
/*
	if( isset( $_POST['tops-action'] ) && 'edit_ticket' == $_POST['tops-action'] ) {
		return;
	}
*/
	// Send new ticket notification
	tops_new_ticket_email( $post_id );
}
add_action( 'tops_ticket_after_create_submit', 'tops_trigger_new_ticket_email', 10 );



/**
 * Triggers a New Comment email to be sent to the ticket agent or user after a new comment is added
 *
 * @since 1.0.0
 * @param int $post_id Ticket post id
 * @return void
 */
function tops_trigger_new_comment_email( $data, $comment_id  ) {
	// Make sure we don't send a purchase receipt while editing a payment
/*
	if( isset( $_POST['tops-action'] ) && 'edit_ticket' == $_POST['tops-action'] ) {
		return;
	}
*/
	// Send new ticket notification
	tops_new_comment_email( $data, $comment_id );
}
add_action( 'tops_ticket_after_comment_submit', 'tops_trigger_new_comment_email', 10, 2 );



/**
 * Trigger the sending of a Test Email
 *
 * @since 1.5
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function tops_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'tops-test-email' ) ) {
		return;
	}

	// Send a test email
	tops_email_test_purchase_receipt();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'tops_action' ) ); exit;
}
add_action( 'tops_send_test_email', 'tops_send_test_email' );
