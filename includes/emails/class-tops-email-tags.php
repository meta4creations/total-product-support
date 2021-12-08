<?php
/**
 * Total Product Support API for creating Email template tags
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {download_list}
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: tops_do_email_tags( $content, payment_id );
 *
 * To add tags, use: tops_add_email_tag( $tag, $description, $func ). Be sure to wrap tops_add_email_tag()
 * in a function hooked to the 'tops_add_email_tags' action
 *
 * @package     TOPS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 * @author      Barry Kooij
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class TOPS_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since 1.0.0
	 */
	private $tags;

	/**
	 * Ticket
	 *
	 * @since 1.0.0
	 */
	private $obj;

	/**
	 * Add an email tag
	 *
	 * @since 1.9
	 *
	 * @param string   $tag  Email tag to be replace in email
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}

	/**
	 * Remove an email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all email tags
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content Content to search for email tags
	 * @param object $ticket The ticket object
	 *
	 * @since 1.0.0
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $object ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}
		
		$this->obj = $object;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->obj = null;

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use tops_do_email_tags instead.
	 *
	 * @since 1.9
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->obj, $tag );
	}

}

/**
 * Add an email tag
 *
 * @since 1.9
 *
 * @param string   $tag  Email tag to be replace in email
 * @param callable $func Hook to run when email tag is found
 */
function tops_add_email_tag( $tag, $description, $func ) {
	TOPS()->email_tags->add( $tag, $description, $func );
}

/**
 * Remove an email tag
 *
 * @since 1.9
 *
 * @param string $tag Email tag to remove hook from
 */
function tops_remove_email_tag( $tag ) {
	TOPS()->email_tags->remove( $tag );
}

/**
 * Check if $tag is a registered email tag
 *
 * @since 1.9
 *
 * @param string $tag Email tag that will be searched
 *
 * @return bool
 */
function tops_email_tag_exists( $tag ) {
	return TOPS()->email_tags->email_tag_exists( $tag );
}

/**
 * Get all email tags
 *
 * @since 1.9
 *
 * @return array
 */
function tops_get_email_tags() {
	return TOPS()->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since 1.9
 *
 * @return string
 */
function tops_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = tops_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

		}

	}

	// Return the list
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 * @param int $payment_id The payment id
 *
 * @since 1.9
 *
 * @return string Content with email tags filtered out.
 */
function tops_do_email_tags( $content, $object ) {

	// Replace all tags
	$content = TOPS()->email_tags->do_tags( $content, $object );
	$content = apply_filters( 'tops_email_template_tags', $content, $object );

	// Return content
	return $content;
}

/**
 * Load email tags
 *
 * @since 1.9
 */
function tops_load_email_tags() {
	do_action( 'tops_add_email_tags' );
}
add_action( 'init', 'tops_load_email_tags', -999 );

/**
 * Add default TOPS email template tags
 *
 * @since 1.0.0
 */
function tops_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'id',
			'description' => __( "The ID of the ticket", 'total-product-support' ),
			'function'    => 'tops_email_tag_id'
		),
		array(
			'tag'         => 'ticket_id',
			'description' => __( "The ID of the ticket", 'total-product-support' ),
			'function'    => 'tops_email_tag_ticket_id'
		),
		array(
			'tag'         => 'url',
			'description' => __( "The permalink of the ticket", 'total-product-support' ),
			'function'    => 'tops_email_tag_url'
		),
		array(
			'tag'         => 'category',
			'description' => __( "The ticket category", 'total-product-support' ),
			'function'    => 'tops_email_tag_category'
		),
		array(
			'tag'         => 'comment',
			'description' => __( "The submitted comment", 'total-product-support' ),
			'function'    => 'tops_email_tag_comment'
		),
		array(
			'tag'         => 'user_name',
			'description' => __( "The ticket creator's name on the site", 'total-product-support' ),
			'function'    => 'tops_email_tag_user_name'
		),
		array(
			'tag'         => 'comment_ticket',
			'description' => __( "The comment's ticket", 'total-product-support' ),
			'function'    => 'tops_email_tag_comment_ticket'
		),
	);

	// Apply tops_email_tags filter
	$email_tags = apply_filters( 'tops_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		tops_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

}
add_action( 'tops_add_email_tags', 'tops_setup_email_tags' );

/**
 * Email template tag: id
 * The ID of the object
 *
 * @param int $object
 *
 * @return int id
 */
function tops_email_tag_id( $object ) {
	return $object->id;
}

/**
 * Email template tag: ticket_id
 * The ID of the object
 *
 * @param int $object
 *
 * @return int id
 */
function tops_email_tag_ticket_id( $object ) {
	if( is_a($object, 'TOPS_Ticket') ) {
		return $object->id;
	} else {
		return $object->get_ticket_id();
	}
}

/**
 * Email template tag: url
 * The permalink of the object
 *
 * @param int $object
 *
 * @return string url
 */
function tops_email_tag_url( $object ) {
	return $object->url();
}

/**
 * Email template tag: category
 * The object's category
 *
 * @param int $object
 *
 * @return string category
 */
function tops_email_tag_category( $object ) {
	return $object->category_name();
}

/**
 * Email template tag: comment
 * The submitted comment
 *
 * @param int $object
 *
 * @return string comment
 */
function tops_email_tag_comment( $object ) {
	if( is_a($object, 'TOPS_Ticket') ) {
		return $object->get_last_comment_string();
	} else {
		return $object->get_comment( true );
	}	
}

/**
 * Email template tag: user_name
 * The object's user
 *
 * @param int $object
 *
 * @return string user_name
 */
function tops_email_tag_user_name( $object ) {
	return $object->get_user_name();
}

/**
 * Email template tag: comment_ticket
 * The comment's ticket
 *
 * @param int $comment
 *
 * @return string url
 */
function tops_email_tag_comment_ticket( $comment ) {
	return $comment->get_ticket_title();
}