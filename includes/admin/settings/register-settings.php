<?php
/**
 * Register Settings
 *
 * @package     TOPS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @global $tops_options Array of all the TOPS Options
 * @return mixed
 */
function tops_get_option( $key = '', $default = false ) {
	global $tops_options;
	$value = ! empty( $tops_options[ $key ] ) ? $tops_options[ $key ] : $default;
	$value = apply_filters( 'tops_get_option', $value, $key, $default );
	return apply_filters( 'tops_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an edd setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the tops_options array.
 *
 * @since 1.0.0
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @global $tops_options Array of all the TOPS Options
 * @return boolean True if updated, false if not.
 */
function tops_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = tops_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'tops_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'tops_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update = update_option( 'tops_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $tops_options;
		$tops_options[ $key ] = $value;

	}

	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an edd setting value in both the db and the global variable.
 *
 * @since 1.0.0
 * @param string $key The Key to delete
 * @global $tops_options Array of all the TOPS Options
 * @return boolean True if removed, false if not.
 */
function tops_delete_option( $key = '' ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'tops_settings' );

	// Next let's try to update the value
	if( isset( $options[ $key ] ) ) {

		unset( $options[ $key ] );

	}

	$did_update = update_option( 'tops_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $tops_options;
		$tops_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array TOPS settings
 */
function tops_get_settings() {

	$settings = get_option( 'tops_settings' );

	if( empty( $settings ) ) {

		// Update old settings with new single option

		$general_settings = is_array( get_option( 'tops_settings_general' ) )    ? get_option( 'tops_settings_general' )    : array();
		//$gateway_settings = is_array( get_option( 'tops_settings_gateways' ) )   ? get_option( 'tops_settings_gateways' )   : array();
		$email_settings   = is_array( get_option( 'tops_settings_emails' ) )     ? get_option( 'tops_settings_emails' )     : array();
		$style_settings   = is_array( get_option( 'tops_settings_styles' ) )     ? get_option( 'tops_settings_styles' )     : array();
		//$tax_settings     = is_array( get_option( 'tops_settings_taxes' ) )      ? get_option( 'tops_settings_taxes' )      : array();
		$ext_settings     = is_array( get_option( 'tops_settings_extensions' ) ) ? get_option( 'tops_settings_extensions' ) : array();
		$license_settings = is_array( get_option( 'tops_settings_licenses' ) )   ? get_option( 'tops_settings_licenses' )   : array();
		$misc_settings    = is_array( get_option( 'tops_settings_misc' ) )       ? get_option( 'tops_settings_misc' )       : array();

		$settings = array_merge( $general_settings, $email_settings, $style_settings, $ext_settings, $license_settings, $misc_settings );

		update_option( 'tops_settings', $settings );

	}
	return apply_filters( 'tops_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0.0
 * @return void
*/
function tops_register_settings() {

	if ( false == get_option( 'tops_settings' ) ) {
		add_option( 'tops_settings' );
	}

	foreach ( tops_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings) {

			// Check for backwards compatibility
			$section_tabs = tops_get_settings_tab_sections( $tab );
			if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
				$section = 'main';
				$settings = $sections;
			}

			add_settings_section(
				'tops_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'tops_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$args = wp_parse_args( $option, array(
				    'section'       => $section,
				    'id'            => null,
				    'desc'          => '',
				    'name'          => '',
				    'size'          => null,
				    'options'       => '',
				    'std'           => '',
				    'min'           => null,
				    'max'           => null,
				    'step'          => null,
				    'chosen'        => null,
				    'placeholder'   => null,
				    'allow_blank'   => true,
				    'readonly'      => false,
				    'faux'          => false,
				    'tooltip_title' => false,
				    'tooltip_desc'  => false,
				    'field_class'   => '',
				) );

				add_settings_field(
					'tops_settings[' . $args['id'] . ']',
					$args['name'],
					function_exists( 'tops_' . $args['type'] . '_callback' ) ? 'tops_' . $args['type'] . '_callback' : 'tops_missing_callback',
					'tops_settings_' . $tab . '_' . $section,
					'tops_settings_' . $tab . '_' . $section,
					$args
				);
			}
		}

	}

	// Creates our settings in the options table
	register_setting( 'tops_settings', 'tops_settings', 'tops_settings_sanitize' );

}
add_action( 'admin_init', 'tops_register_settings' );

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
*/
function tops_get_registered_settings() {

	/**
	 * 'Whitelisted' TOPS settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$tops_settings = array(
		/** General Settings */
		'general' => apply_filters( 'tops_settings_general',
			array(
				'main' => array(
					'page_settings' => array(
						'id'   => 'page_settings',
						'name' => '<h3>' . __( 'Page Settings', 'total-product-support' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
						'tooltip_title' => __( 'Page Settings', 'total-product-support' ),
						'tooltip_desc'  => __( 'Add tooltip description.','total-product-support' ),
					),
					'tickets_new' => array(
						'id'          => 'tickets_new',
						'name'        => __( 'New Support Ticket Page', 'total-product-support' ),
						'desc'        => __( 'This is the checkout page where buyers will complete their purchases. The [download_checkout] shortcode must be on this page.', 'total-product-support' ),
						'type'        => 'select',
						'options'     => tops_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'total-product-support' ),
					),
					'tickets_page' => array(
						'id'          => 'tickets_page',
						'name'        => __( 'Support Tickets Page', 'total-product-support' ),
						'desc'        => __( 'This is the checkout page where buyers will complete their purchases. The [download_checkout] shortcode must be on this page.', 'total-product-support' ),
						'type'        => 'select',
						'options'     => tops_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'total-product-support' ),
					),
					'ticket_archives' => array(
						'id'          => 'ticket_archives',
						'name'        => __( 'Ticket Archives Page', 'total-product-support' ),
						'desc'        => __( 'This is the page buyers are sent to after completing their purchases. The [tops_receipt] shortcode should be on this page.', 'total-product-support' ),
						'type'        => 'select',
						'options'     => tops_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'total-product-support' ),
					),
					'ticket_categories' => array(
						'id'          => 'ticket_categories',
						'name'        => __( 'Ticket Categories Page', 'total-product-support' ),
						'desc'        => __( 'This is the page buyers are sent to if their transaction is cancelled or fails.', 'total-product-support' ),
						'type'        => 'select',
						'options'     => tops_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'total-product-support' ),
					),
					'tickets_public' => array(
						'id'          => 'tickets_public',
						'name'        => __( 'Public Tickets Page', 'total-product-support' ),
						'desc'        => __( 'This page shows a complete purchase history for the current user, including download links. The [purchase_history] shortcode should be on this page.', 'total-product-support' ),
						'type'        => 'select',
						'options'     => tops_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'total-product-support' ),
					),
					'tickets_private' => array(
						'id'          => 'tickets_private',
						'name'        => __( 'Private Tickets Page', 'total-product-support' ),
						'desc'        => sprintf(
								__( 'This is the page where buyers will be redirected by default once they log in. The [tops_login redirect="%s"] shortcode with the redirect attribute can override this setting.', 'total-product-support' ), trailingslashit( home_url() )
						),
						'type'        => 'select',
						'options'     => tops_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'total-product-support' ),
					),
					'tickets_starred' => array(
						'id'          => 'tickets_starred',
						'name'        => __( 'Starred Tickets Page', 'total-product-support' ),
						'desc'        => sprintf(
								__( 'This is the page where buyers will be redirected by default once they log in. The [tops_login redirect="%s"] shortcode with the redirect attribute can override this setting.', 'total-product-support' ), trailingslashit( home_url() )
						),
						'type'        => 'select',
						'options'     => tops_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'total-product-support' ),
					),
				),
/*
				'currency' => array(
					'currency_settings' => array(
						'id'   => 'currency_settings',
						'name' => '<h3>' . __( 'Currency Settings', 'total-product-support' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'currency' => array(
						'id'      => 'currency',
						'name'    => __( 'Currency', 'total-product-support' ),
						'desc'    => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'total-product-support' ),
						'type'    => 'select',
						'options' => tops_get_currencies(),
						'chosen'  => true,
					),
					'currency_position' => array(
						'id'      => 'currency_position',
						'name'    => __( 'Currency Position', 'total-product-support' ),
						'desc'    => __( 'Choose the location of the currency sign.', 'total-product-support' ),
						'type'    => 'select',
						'options' => array(
							'before' => __( 'Before - $10', 'total-product-support' ),
							'after'  => __( 'After - 10$', 'total-product-support' ),
						),
					),
					'thousands_separator' => array(
						'id'   => 'thousands_separator',
						'name' => __( 'Thousands Separator', 'total-product-support' ),
						'desc' => __( 'The symbol (usually , or .) to separate thousands', 'total-product-support' ),
						'type' => 'text',
						'size' => 'small',
						'std'  => ',',
					),
					'decimal_separator' => array(
						'id'   => 'decimal_separator',
						'name' => __( 'Decimal Separator', 'total-product-support' ),
						'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'total-product-support' ),
						'type' => 'text',
						'size' => 'small',
						'std'  => '.',
					),
				),
				'api' => array(
					'api_settings' => array(
						'id'   => 'api_settings',
						'name' => '<h3>' . __( 'API Settings', 'total-product-support' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
						'tooltip_title' => __( 'API Settings', 'total-product-support' ),
						'tooltip_desc'  => __( 'The Total Product Support REST API provides access to store data through our API endpoints. Enable this setting if you would like all user accounts to be able to generate their own API keys.', 'total-product-support' ),
					),
					'api_allow_user_keys' => array(
						'id'   => 'api_allow_user_keys',
						'name' => __( 'Allow User Keys', 'total-product-support' ),
						'desc' => __( 'Check this box to allow all users to generate API keys. Users with the \'manage_tops_ticket_settings\' capability are always allowed to generate keys.', 'total-product-support' ),
						'type' => 'checkbox',
					),
					'api_help' => array(
						'id'   => 'api_help',
						'desc' => sprintf( __( 'Visit the <a href="%s" target="_blank">REST API documentation</a> for further information.', 'total-product-support' ), 'http://docs.easydigitaldownloads.com/article/1131-edd-rest-api-introduction' ),
						'type' => 'descriptive_text',
					),
				),
*/
			)
		),
		/** Payment Gateways Settings */
/*
		'gateways' => apply_filters('tops_settings_gateways',
			array(
				'main' => array(
					'gateway_settings' => array(
						'id'   => 'api_header',
						'name' => '<h3>' . __( 'Gateway Settings', 'total-product-support' ) . '</h3>',
						'desc' => '',
						'type' => 'header',
					),
					'test_mode' => array(
						'id'   => 'test_mode',
						'name' => __( 'Test Mode', 'total-product-support' ),
						'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'total-product-support' ),
						'type' => 'checkbox',
					),
					'gateways' => array(
						'id'      => 'gateways',
						'name'    => __( 'Payment Gateways', 'total-product-support' ),
						'desc'    => __( 'Choose the payment gateways you want to enable.', 'total-product-support' ),
						'type'    => 'gateways',
						'options' => tops_get_payment_gateways(),
					),
					'default_gateway' => array(
						'id'      => 'default_gateway',
						'name'    => __( 'Default Gateway', 'total-product-support' ),
						'desc'    => __( 'This gateway will be loaded automatically with the checkout page.', 'total-product-support' ),
						'type'    => 'gateway_select',
						'options' => tops_get_payment_gateways(),
					),
					'accepted_cards' => array(
						'id'      => 'accepted_cards',
						'name'    => __( 'Accepted Payment Method Icons', 'total-product-support' ),
						'desc'    => __( 'Display icons for the selected payment methods', 'total-product-support' ) . '<br/>' . __( 'You will also need to configure your gateway settings if you are accepting credit cards', 'total-product-support' ),
						'type'    => 'payment_icons',
						'options' => apply_filters('tops_accepted_payment_icons', array(
								'mastercard'      => 'Mastercard',
								'visa'            => 'Visa',
								'americanexpress' => 'American Express',
								'discover'        => 'Discover',
								'paypal'          => 'PayPal',
							)
						),
					),
				),
			)
		),
*/
		/** Emails Settings */
		'emails' => apply_filters('tops_settings_emails',
			array(
				'main' => array(
					'email_settings_header' => array(
						'id'   => 'email_settings_header',
						'name' => '<h3>' . __( 'Email Settings', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'email_template' => array(
						'id'      => 'email_template',
						'name'    => __( 'Email Template', 'total-product-support' ),
						'desc'    => __( 'Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'total-product-support' ),
						'type'    => 'select',
						'options' => tops_get_email_templates(),
					),
					'email_logo' => array(
						'id'   => 'email_logo',
						'name' => __( 'Logo', 'total-product-support' ),
						'desc' => __( 'Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'total-product-support' ),
						'type' => 'upload',
					),
					'email_settings' => array(
						'id'   => 'email_settings',
						'name' => '',
						'desc' => '',
						'type' => 'hook',
					),
				),
				'new_ticket_notification_group' => array(
					'new_ticket_notification_settings' => array(
						'id'   => 'new_ticket_notification_settings',
						'name' => '<h3>' . __( 'New Tickets', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'new_ticket_from_name' => array(
						'id'   => 'new_ticket_from_name',
						'name' => __( 'From Name', 'total-product-support' ),
						'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'total-product-support' ),
						'type' => 'text',
						'std'  => get_bloginfo( 'name' ),
					),
					'new_ticket_from_email' => array(
						'id'   => 'new_ticket_from_email',
						'name' => __( 'From Email', 'total-product-support' ),
						'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'total-product-support' ),
						'type' => 'text',
						'std'  => get_bloginfo( 'admin_email' ),
					),
					'new_ticket_subject' => array(
						'id'   => 'new_ticket_subject',
						'name' => __( 'Sale Notification Subject', 'total-product-support' ),
						'desc' => __( 'Enter the subject line for the sale notification email', 'total-product-support' ),
						'type' => 'text',
						'std'  => 'New download purchase - Order #{payment_id}',
					),
					'new_ticket_heading' => array(
						'id'   => 'new_ticket_heading',
						'name' => __( 'Sale Notification Subject', 'total-product-support' ),
						'desc' => __( 'Enter the subject line for the sale notification email', 'total-product-support' ),
						'type' => 'text',
						'std'  => 'New download purchase - Order #{payment_id}',
					),
					'new_ticket_notification' => array(
						'id'   => 'new_ticket_notification',
						'name' => __( 'Sale Notification', 'total-product-support' ),
						'desc' => __( 'Enter the text that is sent as sale notification email after completion of a purchase. HTML is accepted. Available template tags:', 'total-product-support' ) . '<br/>' . tops_get_emails_tags_list(),
						'type' => 'rich_editor',
						'std'  => tops_get_default_new_ticket_notification_email(),
					),
					'ticket_admin_notice_emails' => array(
						'id'   => 'admin_notice_emails',
						'name' => __( 'Sale Notification Emails', 'total-product-support' ),
						'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'total-product-support' ),
						'type' => 'textarea',
						'std'  => get_bloginfo( 'admin_email' ),
					),
					'disable_ticket_admin_notices' => array(
						'id'   => 'disable_admin_notices',
						'name' => __( 'Disable Admin Notifications', 'total-product-support' ),
						'desc' => __( 'Check this box if you do not want to receive sales notification emails.', 'total-product-support' ),
						'type' => 'checkbox',
					),
				),
				'new_comment_notification_group' => array(
					'new_comment_notification_settings' => array(
						'id'   => 'new_comment_notification_settings',
						'name' => '<h3>' . __( 'New Comments', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'new_comment_from_name' => array(
						'id'   => 'from_name',
						'name' => __( 'From Name', 'total-product-support' ),
						'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'total-product-support' ),
						'type' => 'text',
						'std'  => get_bloginfo( 'name' ),
					),
					'new_comment_from_email' => array(
						'id'   => 'from_email',
						'name' => __( 'From Email', 'total-product-support' ),
						'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'total-product-support' ),
						'type' => 'text',
						'std'  => get_bloginfo( 'admin_email' ),
					),
					'new_comment_subject' => array(
						'id'   => 'new_comment_subject',
						'name' => __( 'Purchase Email Subject', 'total-product-support' ),
						'desc' => __( 'Enter the subject line for the purchase receipt email', 'total-product-support' ),
						'type' => 'text',
						'std'  => __( 'Purchase Receipt', 'total-product-support' ),
					),
					'new_comment_heading' => array(
						'id'   => 'new_comment_heading',
						'name' => __( 'Purchase Email Heading', 'total-product-support' ),
						'desc' => __( 'Enter the heading for the purchase receipt email', 'total-product-support' ),
						'type' => 'text',
						'std'  => __( 'Purchase Receipt', 'total-product-support' ),
					),
					'new_comment_notification' => array(
						'id'   => 'new_ticket_notification',
						'name' => __( 'Sale Notification', 'total-product-support' ),
						'desc' => __( 'Enter the text that is sent as sale notification email after completion of a purchase. HTML is accepted. Available template tags:', 'total-product-support' ) . '<br/>' . tops_get_emails_tags_list(),
						'type' => 'rich_editor',
						'std'  => tops_get_default_new_ticket_notification_email(),
					),
					'comment_admin_notice_emails' => array(
						'id'   => 'admin_notice_emails',
						'name' => __( 'Sale Notification Emails', 'total-product-support' ),
						'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'total-product-support' ),
						'type' => 'textarea',
						'std'  => get_bloginfo( 'admin_email' ),
					),
					'disable_comment_admin_notices' => array(
						'id'   => 'disable_admin_notices',
						'name' => __( 'Disable Admin Notifications', 'total-product-support' ),
						'desc' => __( 'Check this box if you do not want to receive sales notification emails.', 'total-product-support' ),
						'type' => 'checkbox',
					),
				),
			)
		),
		/** Styles Settings */
		'styles' => apply_filters('tops_settings_styles',
			array(
				'main' => array(
					'style_settings' => array(
						'id'   => 'style_settings',
						'name' => '<h3>' . __( 'Style Settings', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'disable_styles' => array(
						'id'            => 'disable_styles',
						'name'          => __( 'Disable Styles', 'total-product-support' ),
						'desc'          => __( 'Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'total-product-support' ),
						'type'          => 'checkbox',
						'tooltip_title' => __( 'Disabling Styles', 'total-product-support' ),
						'tooltip_desc'  => __( 'If your theme has a complete custom CSS file for Total Product Support, you may wish to disable our default styles. This is not recommended unless your sure your theme has a complete custom CSS.', 'total-product-support' ),
					),
/*
					'button_header' => array(
						'id'   => 'button_header',
						'name' => '<strong>' . __( 'Buttons', 'total-product-support' ) . '</strong>',
						'desc' => __( 'Options for add to cart and purchase buttons', 'total-product-support' ),
						'type' => 'header',
					),
					'button_style' => array(
						'id'      => 'button_style',
						'name'    => __( 'Default Button Style', 'total-product-support' ),
						'desc'    => __( 'Choose the style you want to use for the buttons.', 'total-product-support' ),
						'type'    => 'select',
						'options' => tops_get_button_styles(),
					),
					'checkout_color' => array(
						'id'      => 'checkout_color',
						'name'    => __( 'Default Button Color', 'total-product-support' ),
						'desc'    => __( 'Choose the color you want to use for the buttons.', 'total-product-support' ),
						'type'    => 'color_select',
						'options' => tops_get_button_colors(),
					),
*/
				),
			)
		),
		/** Extension Settings */
		'extensions' => apply_filters('tops_settings_extensions',
			array()
		),
		'licenses' => apply_filters('tops_settings_licenses',
			array()
		),
		/** Misc Settings */
		'misc' => apply_filters('tops_settings_misc',
			array(
				'main' => array(
					'misc_settings' => array(
						'id'   => 'misc_settings',
						'name' => '<h3>' . __( 'Misc Settings', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
/*
					'redirect_on_add' => array(
						'id'   => 'redirect_on_add',
						'name' => __( 'Redirect to Checkout', 'total-product-support' ),
						'desc' => __( 'Immediately redirect to checkout after adding an item to the cart?', 'total-product-support' ),
						'type' => 'checkbox',
						'tooltip_title' => __( 'Redirect to Checkout', 'total-product-support' ),
						'tooltip_desc'  => __( 'When enabled, once an item has been added to the cart, the customer will be redirected directly to your checkout page. This is useful for stores that sell single items.', 'total-product-support' ),
					),
					'item_quantities' => array(
						'id'   => 'item_quantities',
						'name' => __('Item Quantities','total-product-support' ),
						'desc' => __('Allow item quantities to be changed.','total-product-support' ),
						'type' => 'checkbox',
					),
*/
					'uninstall_on_delete' => array(
						'id'   => 'uninstall_on_delete',
						'name' => __( 'Remove Data on Uninstall?', 'total-product-support' ),
						'desc' => __( 'Check this box if you would like TOPS to completely remove all of its data when the plugin is deleted.', 'total-product-support' ),
						'type' => 'checkbox',
					),
				),
/*
				'checkout' => array(
					'checkout_settings' => array(
						'id'   => 'checkout_settings',
						'name' => '<h3>' . __( 'Checkout Settings', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'enforce_ssl' => array(
						'id'   => 'enforce_ssl',
						'name' => __( 'Enforce SSL on Checkout', 'total-product-support' ),
						'desc' => __( 'Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'total-product-support' ),
						'type' => 'checkbox',
					),
					'logged_in_only' => array(
						'id'   => 'logged_in_only',
						'name' => __( 'Require Login', 'total-product-support' ),
						'desc' => __( 'Require that users be logged-in to purchase files.', 'total-product-support' ),
						'type' => 'checkbox',
						'tooltip_title' => __( 'Require Login', 'total-product-support' ),
						'tooltip_desc'  => __( 'You can require that customers create and login to user accounts prior to purchasing from your store by enabling this option. When unchecked, users can purchase without being logged in by using their name and email address.', 'total-product-support' ),
					),
					'show_register_form' => array(
						'id'      => 'show_register_form',
						'name'    => __( 'Show Register / Login Form?', 'total-product-support' ),
						'desc'    => __( 'Display the registration and login forms on the checkout page for non-logged-in users.', 'total-product-support' ),
						'type'    => 'select',
						'std'     => 'none',
						'options' => array(
							'both'         => __( 'Registration and Login Forms', 'total-product-support' ),
							'registration' => __( 'Registration Form Only', 'total-product-support' ),
							'login'        => __( 'Login Form Only', 'total-product-support' ),
							'none'         => __( 'None', 'total-product-support' ),
						),
					),
					'allow_multiple_discounts' => array(
						'id'   => 'allow_multiple_discounts',
						'name' => __('Multiple Discounts','total-product-support' ),
						'desc' => __('Allow customers to use multiple discounts on the same purchase?','total-product-support' ),
						'type' => 'checkbox',
					),
					'enable_cart_saving' => array(
						'id'   => 'enable_cart_saving',
						'name' => __( 'Enable Cart Saving', 'total-product-support' ),
						'desc' => __( 'Check this to enable cart saving on the checkout.', 'total-product-support' ),
						'type' => 'checkbox',
						'tooltip_title' => __( 'Cart Saving', 'total-product-support' ),
						'tooltip_desc'  => __( 'Cart saving allows shoppers to create a temporary link to their current shopping cart so they can come back to it later, or share it with someone.', 'total-product-support' ),
					),
				),
*/
/*
				'button_text' => array(
					'button_settings' => array(
						'id'   => 'button_settings',
						'name' => '<h3>' . __( 'Button Text', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'checkout_label' => array(
						'id'   => 'checkout_label',
						'name' => __( 'Complete Purchase Text', 'total-product-support' ),
						'desc' => __( 'The button label for completing a purchase.', 'total-product-support' ),
						'type' => 'text',
						'std'  => __( 'Purchase', 'total-product-support' ),
					),
					'add_to_cart_text' => array(
						'id'   => 'add_to_cart_text',
						'name' => __( 'Add to Cart Text', 'total-product-support' ),
						'desc' => __( 'Text shown on the Add to Cart Buttons.', 'total-product-support' ),
						'type' => 'text',
						'std'  => __( 'Add to Cart', 'total-product-support' ),
					),
					'buy_now_text' => array(
						'id'   => 'buy_now_text',
						'name' => __( 'Buy Now Text', 'total-product-support' ),
						'desc' => __( 'Text shown on the Buy Now Buttons.', 'total-product-support' ),
						'type' => 'text',
						'std'  => __( 'Buy Now', 'total-product-support' ),
					),
				),
*/
/*
				'file_downloads' => array(
					'file_settings' => array(
						'id'   => 'file_settings',
						'name' => '<h3>' . __( 'File Download Settings', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'download_method' => array(
						'id'      => 'download_method',
						'name'    => __( 'Download Method', 'total-product-support' ),
						'desc'    => sprintf( __( 'Select the file download method. Note, not all methods work on all servers.', 'total-product-support' ), tops_get_label_singular() ),
						'type'    => 'select',
						'tooltip_title' => __( 'Download Method', 'total-product-support' ),
						'tooltip_desc' => __( 'Due to its consistency in multiple platforms and better file protection, \'forced\' is the default method. Because Total Product Support uses PHP to process the file with the \'forced\' method, larger files can cause problems with delivery, resulting in hitting the \'max execution time\' of the server. If users are getting 404 or 403 errors when trying to access their purchased files when using the \'forced\' method, changing to the \'redirect\' method can help resolve this.', 'total-product-support' ),
						'options' => array(
							'direct'   => __( 'Forced', 'total-product-support' ),
							'redirect' => __( 'Redirect', 'total-product-support' ),
						),
					),
					'symlink_file_downloads' => array(
						'id'   => 'symlink_file_downloads',
						'name' => __( 'Symlink File Downloads?', 'total-product-support' ),
						'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'total-product-support' ),
						'type' => 'checkbox',
					),
					'file_download_limit' => array(
						'id'   => 'file_download_limit',
						'name' => __( 'File Download Limit', 'total-product-support' ),
						'desc' => sprintf( __( 'The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'total-product-support' ), tops_get_label_singular() ),
						'type' => 'number',
						'size' => 'small',
						'tooltip_title' => __( 'File Download Limits', 'total-product-support' ),
						'tooltip_desc'  => sprintf( __( 'Set the global default for the number of times a customer can download items they purchase. Using a value of 0 is unlimited. This can be defined on a %s-specific level as well. Download limits can also be reset for an individual purchase.', 'total-product-support' ), tops_get_label_singular( true ) ),
					),
					'download_link_expiration' => array(
						'id'            => 'download_link_expiration',
						'name'          => __( 'Download Link Expiration', 'total-product-support' ),
						'desc'          => __( 'How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'total-product-support' ),
						'tooltip_title' => __( 'Download Link Expiration', 'total-product-support' ),
						'tooltip_desc'  => __( 'When a customer receives a link to their downloads via email, in their receipt, or in their purchase history, the link will only be valid for the timeframe (in hours) defined in this setting. Sending a new purchase receipt or visiting the account page will re-generate a valid link for the customer.', 'total-product-support' ),
						'type'          => 'number',
						'size'          => 'small',
						'std'           => '24',
						'min'           => '0',
					),
					'disable_redownload' => array(
						'id'   => 'disable_redownload',
						'name' => __( 'Disable Redownload?', 'total-product-support' ),
						'desc' => __( 'Check this if you do not want to allow users to redownload items from their purchase history.', 'total-product-support' ),
						'type' => 'checkbox',
					),
				),
*/
/*
				'accounting'     => array(
					'accounting_settings' => array(
						'id'   => 'accounting_settings',
						'name' => '<h3>' . __( 'Accounting Settings', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'enable_skus' => array(
						'id'   => 'enable_skus',
						'name' => __( 'Enable SKU Entry', 'total-product-support' ),
						'desc' => __( 'Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'total-product-support' ),
						'type' => 'checkbox',
					),
					'enable_sequential' => array(
						'id'   => 'enable_sequential',
						'name' => __( 'Sequential Order Numbers', 'total-product-support' ),
						'desc' => __( 'Check this box to enable sequential order numbers.', 'total-product-support' ),
						'type' => 'checkbox',
					),
					'sequential_start' => array(
						'id'   => 'sequential_start',
						'name' => __( 'Sequential Starting Number', 'total-product-support' ),
						'desc' => __( 'The number at which the sequence should begin.', 'total-product-support' ),
						'type' => 'number',
						'size' => 'small',
						'std'  => '1',
					),
					'sequential_prefix' => array(
						'id'   => 'sequential_prefix',
						'name' => __( 'Sequential Number Prefix', 'total-product-support' ),
						'desc' => __( 'A prefix to prepend to all sequential order numbers.', 'total-product-support' ),
						'type' => 'text',
					),
					'sequential_postfix' => array(
						'id'   => 'sequential_postfix',
						'name' => __( 'Sequential Number Postfix', 'total-product-support' ),
						'desc' => __( 'A postfix to append to all sequential order numbers.', 'total-product-support' ),
						'type' => 'text',
					),
				),
*/
/*
				'site_terms'     => array(
					'terms_settings' => array(
						'id'   => 'terms_settings',
						'name' => '<h3>' . __( 'Agreement Settings', 'total-product-support' ) . '</h3>',
						'type' => 'header',
					),
					'show_agree_to_terms' => array(
						'id'   => 'show_agree_to_terms',
						'name' => __( 'Agree to Terms', 'total-product-support' ),
						'desc' => __( 'Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'total-product-support' ),
						'type' => 'checkbox',
					),
					'agree_label' => array(
						'id'   => 'agree_label',
						'name' => __( 'Agree to Terms Label', 'total-product-support' ),
						'desc' => __( 'Label shown next to the agree to terms check box.', 'total-product-support' ),
						'type' => 'text',
						'size' => 'regular',
					),
					'agree_text' => array(
						'id'   => 'agree_text',
						'name' => __( 'Agreement Text', 'total-product-support' ),
						'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'total-product-support' ),
						'type' => 'rich_editor',
					),
				),
*/
			)
		)
	);

	return apply_filters( 'tops_registered_settings', $tops_settings );
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.8.2
 *
 * @param array $input The value inputted in the field
 * @global $tops_options Array of all the TOPS Options
 *
 * @return string $input Sanitizied value
 */
function tops_settings_sanitize( $input = array() ) {
	global $tops_options;

	$doing_section = false;
	if ( ! empty( $_POST['_wp_http_referer'] ) ) {
		$doing_section = true;
	}

	$setting_types = tops_get_registered_settings_types();
	$input         = $input ? $input : array();

	if ( $doing_section ) {

		parse_str( $_POST['_wp_http_referer'], $referrer ); // Pull out the tab and section
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
		$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

		// Run a general sanitization for the tab for special fields (like taxes)
		$input = apply_filters( 'tops_settings_' . $tab . '_sanitize', $input );

		// Run a general sanitization for the section so custom tabs with sub-sections can save special data
		$input = apply_filters( 'tops_settings_' . $tab . '-' . $section . '_sanitize', $input );

	}

	// Merge our new settings with the existing
	$output = array_merge( $tops_options, $input );

	foreach ( $setting_types as $key => $type ) {

		if ( empty( $type ) ) {
			continue;
		}

		// Some setting types are not actually settings, just keep moving along here
		$non_setting_types = apply_filters( 'tops_non_setting_types', array(
			'header', 'descriptive_text', 'hook',
		) );

		if ( in_array( $type, $non_setting_types ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {
			$output[ $key ] = apply_filters( 'tops_settings_sanitize_' . $type, $output[ $key ], $key );
			$output[ $key ] = apply_filters( 'tops_settings_sanitize', $output[ $key ], $key );
		}

		if ( $doing_section ) {
			switch( $type ) {
				case 'checkbox':
				case 'gateways':
				case 'multicheck':
				case 'payment_icons':
					if ( array_key_exists( $key, $input ) && $output[ $key ] === '-1' ) {
						unset( $output[ $key ] );
					}
					break;
				default:
					if ( array_key_exists( $key, $input ) && empty( $input[ $key ] ) ) {
						unset( $output[ $key ] );
					}
					break;
			}
		} else {
			if ( empty( $input[ $key ] ) ) {
				unset( $output[ $key ] );
			}
		}

	}

	if ( $doing_section ) {
		add_settings_error( 'tops-notices', '', __( 'Settings updated.', 'total-product-support' ), 'updated' );
	}

	return $output;
}

/**
 * Flattens the set of registered settings and their type so we can easily sanitize all the settings
 * in a much cleaner set of logic in tops_settings_sanitize
 *
 * @since  2.6.5
 * @return array Key is the setting ID, value is the type of setting it is registered as
 */
function tops_get_registered_settings_types() {
	$settings      = tops_get_registered_settings();
	$setting_types = array();

	foreach ( $settings as $tab ) {

		foreach ( $tab as $section_or_setting ) {

			// See if we have a setting registered at the tab level for backwards compatibility
			if ( is_array( $section_or_setting ) && array_key_exists( 'type', $section_or_setting ) ) {
				$setting_types[ $section_or_setting['id'] ] = $section_or_setting['type'];
				continue;
			}

			foreach ( $section_or_setting as $section => $section_settings ) {
				$setting_types[ $section_settings['id'] ] = $section_settings['type'];
			}
		}

	}

	return $setting_types;
}

/**
 * Misc File Download Settings Sanitization
 *
 * @since 2.5
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
/*
function tops_settings_sanitize_misc_file_downloads( $input ) {

	if( ! current_user_can( 'manage_tops_ticket_settings' ) ) {
		return $input;
	}

	if( tops_get_file_download_method() != $input['download_method'] || ! tops_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		tops_create_protection_files( true, $input['download_method'] );
	}

	return $input;
}
add_filter( 'tops_settings_misc-file_downloads_sanitize', 'tops_settings_sanitize_misc_file_downloads' );
*/

/**
 * Misc Accounting Settings Sanitization
 *
 * @since 2.5
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
/*
function tops_settings_sanitize_misc_accounting( $input ) {

	if( ! current_user_can( 'manage_tops_ticket_settings' ) ) {
		return $input;
	}

	if( ! empty( $input['enable_sequential'] ) && ! tops_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		TOPS()->session->set( 'upgrade_sequential', '1' );

	}

	return $input;
}
add_filter( 'tops_settings_misc-accounting_sanitize', 'tops_settings_sanitize_misc_accounting' );
*/

/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since 1.6
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
/*
function tops_settings_sanitize_taxes( $input ) {

	if( ! current_user_can( 'manage_tops_ticket_settings' ) ) {
		return $input;
	}

	if( ! isset( $_POST['tax_rates'] ) ) {
		return $input;
	}

	$new_rates = ! empty( $_POST['tax_rates'] ) ? array_values( $_POST['tax_rates'] ) : array();

	update_option( 'tops_tax_rates', $new_rates );

	return $input;
}
add_filter( 'tops_settings_taxes_sanitize', 'tops_settings_sanitize_taxes' );
*/

/**
 * Payment Gateways Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 *
 * @since 2.7
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
/*
function tops_settings_sanitize_gateways( $input ) {

	if ( ! current_user_can( 'manage_tops_ticket_settings' ) || empty( $input['default_gateway'] ) ) {
		return $input;
	}

	if ( empty( $input['gateways'] ) || '-1' == $input['gateways'] )  {

		add_settings_error( 'tops-notices', '', __( 'Error setting default gateway. No gateways are enabled.', 'total-product-support' ) );
		unset( $input['default_gateway'] );

	} else if ( ! array_key_exists( $input['default_gateway'], $input['gateways'] ) ) {

		$enabled_gateways = $input['gateways'];
		$all_gateways     = tops_get_payment_gateways();
		$selected_default = $all_gateways[ $input['default_gateway'] ];

		reset( $enabled_gateways );
		$first_gateway = key( $enabled_gateways );

		if ( $first_gateway ) {
			add_settings_error( 'tops-notices', '', sprintf( __( '%s could not be set as the default gateway. It must first be enabled.', 'total-product-support' ), $selected_default['admin_label'] ), 'error' );
			$input['default_gateway'] = $first_gateway;
		}

	}

	return $input;
}
add_filter( 'tops_settings_gateways_sanitize', 'tops_settings_sanitize_gateways' );
*/

/**
 * Sanitize text fields
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function tops_sanitize_text_field( $input ) {
	$tags = array(
		'p' => array(
			'class' => array(),
			'id'    => array(),
		),
		'span' => array(
			'class' => array(),
			'id'    => array(),
		),
		'a' => array(
			'href' => array(),
			'title' => array(),
			'class' => array(),
			'title' => array(),
			'id'    => array(),
		),
		'strong' => array(),
		'em' => array(),
		'br' => array(),
		'img' => array(
			'src'   => array(),
			'title' => array(),
			'alt'   => array(),
			'id'    => array(),
		),
		'div' => array(
			'class' => array(),
			'id'    => array(),
		),
		'ul' => array(
			'class' => array(),
			'id'    => array(),
		),
		'li' => array(
			'class' => array(),
			'id'    => array(),
		)
	);

	$allowed_tags = apply_filters( 'tops_allowed_html_tags', $tags );

	return trim( wp_kses( $input, $allowed_tags ) );
}
add_filter( 'tops_settings_sanitize_text', 'tops_sanitize_text_field' );

/**
 * Sanitize HTML Class Names
 *
 * @since 2.6.11
 * @param  string|array $class HTML Class Name(s)
 * @return string $class
 */
function tops_sanitize_html_class( $class = '' ) {

	if ( is_string( $class ) ) {
		$class = sanitize_html_class( $class );
	} else if ( is_array( $class ) ) {
		$class = array_values( array_map( 'sanitize_html_class', $class ) );
		$class = implode( ' ', array_unique( $class ) );
	}

	return $class;

}

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @return array $tabs
 */
function tops_get_settings_tabs() {

	$settings = tops_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'total-product-support' );
	$tabs['emails']   = __( 'Emails', 'total-product-support' );
	$tabs['styles']   = __( 'Styles', 'total-product-support' );

	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'total-product-support' );
	}
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'total-product-support' );
	}

	$tabs['misc']      = __( 'Misc', 'total-product-support' );

	return apply_filters( 'tops_settings_tabs', $tabs );
}

/**
 * Retrieve settings tabs
 *
 * @since 2.5
 * @return array $section
 */
function tops_get_settings_tab_sections( $tab = false ) {

	$tabs     = false;
	$sections = tops_get_registered_settings_sections();

	if( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  2.5
 * @return array Array of tabs and sections
 */
function tops_get_registered_settings_sections() {

	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'general'    => apply_filters( 'tops_settings_sections_general', array(
			'main'               => __( 'General Settings', 'total-product-support' ),
			//'currency'           => __( 'Currency Settings', 'total-product-support' ),
			//'api'                => __( 'API Settings', 'total-product-support' ),
		) ),
		'emails'     => apply_filters( 'tops_settings_sections_emails', array(
			'main'               				=> __( 'Email Settings', 'total-product-support' ),
			'new_comment_notification'  => __( 'New Comments', 'total-product-support' ),
			'new_ticket_notification' 	=> __( 'New Tickets', 'total-product-support' ),
		) ),
		'styles'     => apply_filters( 'tops_settings_sections_styles', array(
			'main'               => __( 'Style Settings', 'total-product-support' ),
		) ),
		'extensions' => apply_filters( 'tops_settings_sections_extensions', array(
			'main'               => __( 'Main', 'total-product-support' )
		) ),
		'licenses'   => apply_filters( 'tops_settings_sections_licenses', array() ),
		'misc'       => apply_filters( 'tops_settings_sections_misc', array(
			'main'               => __( 'Misc Settings', 'total-product-support' ),
			//'checkout'           => __( 'Checkout Settings', 'total-product-support' ),
			//'button_text'        => __( 'Button Text', 'total-product-support' ),
			//'file_downloads'     => __( 'File Downloads', 'total-product-support' ),
			//'accounting'         => __( 'Accounting Settings', 'total-product-support' ),
			//'site_terms'         => __( 'Terms of Agreement', 'total-product-support' ),
		) ),
	);

	$sections = apply_filters( 'tops_settings_sections', $sections );

	return $sections;
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.9.5
 * @param bool $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function tops_get_pages( $force = false ) {

	$pages_options = array( '' => '' ); // Blank option

	if( ( ! isset( $_GET['page'] ) || 'tops-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function tops_header_callback( $args ) {
	echo apply_filters( 'tops_after_setting_output', '', $args );
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_checkbox_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$name = '';
	} else {
		$name = 'name="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	$checked  = ! empty( $tops_option ) ? checked( 1, $tops_option, false ) : '';
	$html     = '<input type="hidden"' . $name . ' value="-1" />';
	$html    .= '<input type="checkbox" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"' . $name . ' value="1" ' . $checked . ' class="' . $class . '"/>';
	$html    .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_multicheck_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	$class = tops_sanitize_html_class( $args['field_class'] );

	$html = '';
	if ( ! empty( $args['options'] ) ) {
		$html .= '<input type="hidden" name="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" value="-1" />';
		foreach( $args['options'] as $key => $option ):
			if( isset( $tops_option[ $key ] ) ) { $enabled = $option; } else { $enabled = NULL; }
			$html .= '<input name="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']">' . wp_kses_post( $option ) . '</label><br/>';
		endforeach;
		$html .= '<p class="description">' . $args['desc'] . '</p>';
	}

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Payment method icons callback
 *
 * @since 2.1
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_payment_icons_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	$class = tops_sanitize_html_class( $args['field_class'] );

	$html = '<input type="hidden" name="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" value="-1" />';
	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ) {

			if( isset( $tops_option[ $key ] ) ) {
				$enabled = $option;
			} else {
				$enabled = NULL;
			}

			$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" style="margin-right:10px;line-height:16px;height:16px;display:inline-block;">';

				$html .= '<input name="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';

				if( tops_string_is_image_url( $key ) ) {

					$html .= '<img class="payment-icon" src="' . esc_url( $key ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';

				} else {

					$card = strtolower( str_replace( ' ', '', $option ) );

					if( has_filter( 'tops_accepted_payment_' . $card . '_image' ) ) {

						$image = apply_filters( 'tops_accepted_payment_' . $card . '_image', '' );

					} else {

						$image       = tops_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.png', false );
						$content_dir = WP_CONTENT_DIR;

						if( function_exists( 'wp_normalize_path' ) ) {

							// Replaces backslashes with forward slashes for Windows systems
							$image = wp_normalize_path( $image );
							$content_dir = wp_normalize_path( $content_dir );

						}

						$image = str_replace( $content_dir, content_url(), $image );

					}

					$html .= '<img class="payment-icon" src="' . esc_url( $image ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';
				}


			$html .= $option . '</label>';

		}
		$html .= '<p class="description" style="margin-top:16px;">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_radio_callback( $args ) {
	$tops_options = tops_get_option( $args['id'] );

	$html = '';

	$class = tops_sanitize_html_class( $args['field_class'] );

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( $tops_options && $tops_options == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! $tops_options )
			$checked = true;

		$html .= '<input name="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" class="' . $class . '" type="radio" value="' . tops_sanitize_key( $key ) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label><br/>';
	endforeach;

	$html .= '<p class="description">' . apply_filters( 'tops_after_setting_output', wp_kses_post( $args['desc'] ), $args ) . '</p>';

	echo $html;
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_gateways_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	$class = tops_sanitize_html_class( $args['field_class'] );

	$html = '<input type="hidden" name="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" value="-1" />';

	foreach ( $args['options'] as $key => $option ) :
		if ( isset( $tops_option[ $key ] ) )
			$enabled = '1';
		else
			$enabled = null;

		$html .= '<input name="tops_settings[' . esc_attr( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']" class="' . $class . '" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . '][' . tops_sanitize_key( $key ) . ']">' . esc_html( $option['admin_label'] ) . '</label><br/>';
	endforeach;

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_gateway_select_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	$class = tops_sanitize_html_class( $args['field_class'] );

	$html = '';

	$html .= '<select name="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" class="' . $class . '">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $tops_option ) ? selected( $key, $tops_option, false ) : '';
		$html .= '<option value="' . tops_sanitize_key( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_text_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} elseif( ! empty( $args['allow_blank'] ) && empty( $tops_option ) ) {
		$value = '';
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="tops_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	$disabled = ! empty( $args['disabled'] ) ? ' disabled="disabled"' : '';
	$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html     = '<input type="text" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"' . $readonly . $disabled . ' placeholder="' . esc_attr( $args['placeholder'] ) . '"/>';
	$html    .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_number_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value = isset( $args['std'] ) ? $args['std'] : '';
		$name  = '';
	} else {
		$name = 'name="tops_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" ' . $name . ' value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_textarea_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	$html = '<textarea class="' . $class . ' large-text" cols="50" rows="5" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" name="tops_settings[' . esc_attr( $args['id'] ) . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_password_callback( $args ) {
	$tops_options = tops_get_option( $args['id'] );

	if ( $tops_options ) {
		$value = $tops_options;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $class . ' ' . sanitize_html_class( $size ) . '-text" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" name="tops_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function tops_missing_callback($args) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'total-product-support' ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_select_callback($args) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	if ( isset( $args['chosen'] ) ) {
		$class .= ' tops-chosen';
	}

	$html = '<select id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" name="tops_settings[' . esc_attr( $args['id'] ) . ']" class="' . $class . '" data-placeholder="' . esc_html( $placeholder ) . '" />';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_color_select_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	$html = '<select id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" class="' . $class . '" name="tops_settings[' . esc_attr( $args['id'] ) . ']"/>';

	foreach ( $args['options'] as $option => $color ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $color['label'] ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 */
function tops_rich_editor_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} else {
		if( ! empty( $args['allow_blank'] ) && empty( $tops_option ) ) {
			$value = '';
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	$class = tops_sanitize_html_class( $args['field_class'] );

	ob_start();
	wp_editor( stripslashes( $value ), 'tops_settings_' . esc_attr( $args['id'] ), array( 'textarea_name' => 'tops_settings[' . esc_attr( $args['id'] ) . ']', 'textarea_rows' => absint( $rows ), 'editor_class' => $class ) );
	$html = ob_get_clean();

	$html .= '<br/><label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_upload_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} else {
		$value = isset($args['std']) ? $args['std'] : '';
	}

	$class = tops_sanitize_html_class( $args['field_class'] );

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" clas="' . $class . '" name="tops_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="tops_settings_upload_button button-secondary" value="' . __( 'Upload File', 'total-product-support' ) . '"/></span>';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
function tops_color_callback( $args ) {
	$tops_option = tops_get_option( $args['id'] );

	if ( $tops_option ) {
		$value = $tops_option;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$class = tops_sanitize_html_class( $args['field_class'] );

	$html = '<input type="text" class="' . $class . ' tops-color-picker" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" name="tops_settings[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 2.1.3
 * @param array $args Arguments passed by the setting
 * @return void
 */
function tops_descriptive_text_callback( $args ) {
	$html = wp_kses_post( $args['desc'] );

	echo apply_filters( 'tops_after_setting_output', $html, $args );
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 *
 * @return void
 */
if ( ! function_exists( 'tops_license_key_callback' ) ) {
	function tops_license_key_callback( $args ) {
		$tops_option = tops_get_option( $args['id'] );

		$messages = array();
		$license  = get_option( $args['options']['is_valid_license_option'] );

		if ( $tops_option ) {
			$value = $tops_option;
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		if( ! empty( $license ) && is_object( $license ) ) {

			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if ( false === $license->success ) {

				switch( $license->error ) {

					case 'expired' :

						$class = 'expired';
						$messages[] = sprintf(
							__( 'Your license key expired on %s. Please <a href="%s" target="_blank">renew your license key</a>.', 'total-product-support' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
							'https://easydigitaldownloads.com/checkout/?tops_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'revoked' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'total-product-support' ),
							'https://easydigitaldownloads.com/support?utm_campaign=admin&utm_source=licenses&utm_medium=revoked'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'missing' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Invalid license. Please <a href="%s" target="_blank">visit your account page</a> and verify it.', 'total-product-support' ),
							'https://easydigitaldownloads.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'invalid' :
					case 'site_inactive' :

						$class = 'error';
						$messages[] = sprintf(
							__( 'Your %s is not active for this URL. Please <a href="%s" target="_blank">visit your account page</a> to manage your license key URLs.', 'total-product-support' ),
							$args['name'],
							'https://easydigitaldownloads.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
						);

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'item_name_mismatch' :

						$class = 'error';
						$messages[] = sprintf( __( 'This appears to be an invalid license key for %s.', 'total-product-support' ), $args['name'] );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'no_activations_left':

						$class = 'error';
						$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'total-product-support' ), 'https://easydigitaldownloads.com/your-account/' );

						$license_status = 'license-' . $class . '-notice';

						break;

					case 'license_not_activable':

						$class = 'error';
						$messages[] = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'total-product-support' );

						$license_status = 'license-' . $class . '-notice';
						break;

					default :

						$class = 'error';
						$error = ! empty(  $license->error ) ?  $license->error : __( 'unknown_error', 'total-product-support' );
						$messages[] = sprintf( __( 'There was an error with this license key: %s. Please <a href="%s">contact our support team</a>.', 'total-product-support' ), $error, 'https://easydigitaldownloads.com/support' );

						$license_status = 'license-' . $class . '-notice';
						break;
				}

			} else {

				switch( $license->license ) {

					case 'valid' :
					default:

						$class = 'valid';

						$now        = current_time( 'timestamp' );
						$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

						if( 'lifetime' === $license->expires ) {

							$messages[] = __( 'License key never expires.', 'total-product-support' );

							$license_status = 'license-lifetime-notice';

						} elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

							$messages[] = sprintf(
								__( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank">Renew your license key</a>.', 'total-product-support' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
								'https://easydigitaldownloads.com/checkout/?tops_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
							);

							$license_status = 'license-expires-soon-notice';

						} else {

							$messages[] = sprintf(
								__( 'Your license key expires on %s.', 'total-product-support' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
							);

							$license_status = 'license-expiration-date-notice';

						}

						break;

				}

			}

		} else {
			$class = 'empty';

			$messages[] = sprintf(
				__( 'To receive updates, please enter your valid %s license key.', 'total-product-support' ),
				$args['name']
			);

			$license_status = null;
		}

		$class .= ' ' . tops_sanitize_html_class( $args['field_class'] );

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class( $size ) . '-text" id="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" name="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( $value ) . '"/>';

		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'total-product-support' ) . '"/>';
		}

		$html .= '<label for="tops_settings[' . tops_sanitize_key( $args['id'] ) . ']"> '  . wp_kses_post( $args['desc'] ) . '</label>';

		if ( ! empty( $messages ) ) {
			foreach( $messages as $message ) {

				$html .= '<div class="tops-license-data tops-license-' . $class . ' ' . $license_status . '">';
					$html .= '<p>' . $message . '</p>';
				$html .= '</div>';

			}
		}

		wp_nonce_field( tops_sanitize_key( $args['id'] ) . '-nonce', tops_sanitize_key( $args['id'] ) . '-nonce' );

		echo $html;
	}
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function tops_hook_callback( $args ) {
	do_action( 'tops_' . $args['id'], $args );
}

/**
 * Set manage_tops_ticket_settings as the cap required to save TOPS settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function tops_set_settings_cap() {
	return 'manage_tops_ticket_settings';
}
add_filter( 'option_page_capability_tops_settings', 'tops_set_settings_cap' );

function tops_add_setting_tooltip( $html, $args ) {

	if ( ! empty( $args['tooltip_title'] ) && ! empty( $args['tooltip_desc'] ) ) {
		$tooltip = '<span alt="f223" class="tops-help-tip dashicons dashicons-editor-help" title="<strong>' . $args['tooltip_title'] . '</strong>: ' . $args['tooltip_desc'] . '"></span>';
		$html .= $tooltip;
	}

	return $html;
}
add_filter( 'tops_after_setting_output', 'tops_add_setting_tooltip', 10, 2 );
