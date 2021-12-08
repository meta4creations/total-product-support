<?php
/**
 * Email Template
 *
 * @package     TOPS
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * TOPS_Emails Class
 *
 * @since 2.1
 */
class TOPS_Emails {

	/**
	 * Holds the from address
	 *
	 * @since 2.1
	 */
	private $from_address;

	/**
	 * Holds the from name
	 *
	 * @since 2.1
	 */
	private $from_name;

	/**
	 * Holds the email content type
	 *
	 * @since 2.1
	 */
	private $content_type;

	/**
	 * Holds the email headers
	 *
	 * @since 2.1
	 */
	private $headers;

	/**
	 * Whether to send email in HTML
	 *
	 * @since 2.1
	 */
	private $html = true;

	/**
	 * The email template to use
	 *
	 * @since 2.1
	 */
	private $template;

	/**
	 * The header text for the email
	 *
	 * @since  2.1
	 */
	private $heading = '';

	/**
	 * Get things going
	 *
	 * @since 2.1
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'tops_email_send_before', array( $this, 'send_before' ) );
		add_action( 'tops_email_send_after', array( $this, 'send_after' ) );

	}

	/**
	 * Set a property
	 *
	 * @since 2.1
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	/**
	 * Get a property
	 *
	 * @since 2.6.9
	 */
	public function __get( $key ) {
		return $this->$key;
	}

	/**
	 * Get the email from name
	 *
	 * @since 2.1
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = tops_get_option( 'from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'tops_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}

	/**
	 * Get the email from address
	 *
	 * @since 2.1
	 */
	public function get_from_address() {
		if ( ! $this->from_address ) {
			$this->from_address = tops_get_option( 'from_email', get_option( 'admin_email' ) );
		}

		return apply_filters( 'tops_email_from_address', $this->from_address, $this );
	}

	/**
	 * Get the email content type
	 *
	 * @since 2.1
	 */
	public function get_content_type() {
		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'tops_email_default_content_type', 'text/html', $this );
		} else if ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'tops_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get the email headers
	 *
	 * @since 2.1
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'tops_email_headers', $this->headers, $this );
	}

	/**
	 * Retrieve email templates
	 *
	 * @since 2.1
	 */
	public function get_templates() {
		$templates = array(
			'default' => __( 'Default Template', 'total-product-support' ),
			'none'    => __( 'No template, plain text only', 'total-product-support' )
		);

		return apply_filters( 'tops_email_templates', $templates );
	}

	/**
	 * Get the enabled email template
	 *
	 * @since 2.1
	 *
	 * @return string|null
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = tops_get_option( 'email_template', 'default' );
		}

		return apply_filters( 'tops_email_template', $this->template );
	}

	/**
	 * Get the header text for the email
	 *
	 * @since 2.1
	 */
	public function get_heading() {
		return apply_filters( 'tops_email_heading', $this->heading );
	}

	/**
	 * Parse email template tags
	 *
	 * @since 2.1
	 * @param string $content
	 */
	public function parse_tags( $content ) {

		// The email tags are parsed during setup for purchase receipts and sale notifications
		// Onoce tags are not restricted to payments, we'll expand this. See https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/2151

		return $content;
	}

	/**
	 * Build the final email
	 *
	 * @since 2.1
	 * @param string $message
	 *
	 * @return string
	 */
	public function build_email( $message ) {

		if ( false === $this->html ) {
			return apply_filters( 'tops_email_message', wp_strip_all_tags( $message ), $this );
		}

		$message = $this->text_to_html( $message );

		ob_start();
		
		echo tops_get_template_part( 'emails/header' );
		//tops_get_template_part( 'emails/header', $this->get_template(), true );

		/**
		 * Hooks into the email header
		 *
		 * @since 2.1
		 */
		do_action( 'tops_email_header', $this );

		if ( has_action( 'tops_email_template_' . $this->get_template() ) ) {
			/**
			 * Hooks into the template of the email
			 *
			 * @param string $this->template Gets the enabled email template
			 * @since 2.1
			 */
			do_action( 'tops_email_template_' . $this->get_template() );
		} else {
			echo tops_get_template_part( 'emails/body' );
			//tops_get_template_part( 'emails/body', $this->get_template(), true );
		}

		/**
		 * Hooks into the body of the email
		 *
		 * @since 2.1
		 */
		do_action( 'tops_email_body', $this );
		
		echo tops_get_template_part( 'emails/footer' );
		//tops_get_template_part( 'emails/footer', $this->get_template(), true );

		/**
		 * Hooks into the footer of the email
		 *
		 * @since 2.1
		 */
		do_action( 'tops_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );

		return apply_filters( 'tops_email_message', $message, $this );
	}

	/**
	 * Send the email
	 * @param  string  $to               The To address to send to.
	 * @param  string  $subject          The subject line of the email to send.
	 * @param  string  $message          The body of the email to send.
	 * @param  string|array $attachments Attachments to the email in a format supported by wp_mail()
	 * @since 1.0.0
	 */
	public function send( $to, $subject, $message, $attachments = '' ) {

		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'You cannot send email with TOPS_Emails until init/admin_init has been reached', 'total-product-support' ), null );
			return false;
		}

		/**
		 * Hooks before the email is sent
		 *
		 * @since 2.1
		 */
		do_action( 'tops_email_send_before', $this );

		$subject = $this->parse_tags( $subject );
		$message = $this->parse_tags( $message );

		$message = $this->build_email( $message );

		$attachments = apply_filters( 'tops_email_attachments', $attachments, $this );

		$sent       = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );
		
		$log_errors = apply_filters( 'tops_log_email_errors', true, $to, $subject, $message );
		
		//echo '<pre>';print_r($to);echo '</pre>';
		//echo '<pre>';print_r($sent);echo '</pre>';
		//echo '<pre>';print_r($subject);echo '</pre>';
		//echo '<pre>';print_r($message);echo '</pre>';
		//echo '<pre>';print_r($this->get_headers());echo '</pre>';

		if( ! $sent && true === $log_errors ) {
			if ( is_array( $to ) ) {
				$to = implode( ',', $to );
			}

			$log_message = sprintf(
				__( "Email from Total Product Support failed to send.\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'total-product-support' ),
				date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
				$to,
				$subject
			);

			error_log( $log_message );
		}

		/**
		 * Hooks after the email is sent
		 *
		 * @since 2.1
		 */
		do_action( 'tops_email_send_after', $this );

		return $sent;

	}

	/**
	 * Add filters / actions before the email is sent
	 *
	 * @since 2.1
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Remove filters / actions after the email is sent
	 *
	 * @since 2.1
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Reset heading to an empty string
		$this->heading = '';
	}

	/**
	 * Converts text to formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @since 2.1
	 */
	public function text_to_html( $message ) {

		if ( 'text/html' == $this->content_type || true === $this->html ) {
			$message = apply_filters( 'tops_email_template_wpautop', true ) ? wpautop( $message ) : $message;
		}

		return $message;
	}

}
