<?php
/**
 * Install Function
 *
 * @package     TOPS
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'tops_ticket' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the TOPS Welcome
 * screen.
 *
 * @since 1.0
 * @global $wpdb
 * @global $tops_options
 * @param  bool $network_side If the plugin is being network-activated
 * @return void
 */
 
 
function tops_install( $network_wide = false ) {
	global $wpdb;

	if( is_multisite() && $network_wide ) {

		foreach( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {

			switch_to_blog( $blog_id );
			tops_run_install();
			restore_current_blog();
		}

	} else {

		tops_run_install();
	}
}
register_activation_hook( TOPS_PLUGIN_FILE, 'tops_install' );


/**
 * Run the TOPS Install process
 *
 * @since  1.0.0
 * @return void
 */
function tops_run_install() {
	
	global $wpdb, $tops_options;

/*
	if( !function_exists( 'tops_create_protection_files' ) ) {
		require_once TOPS_PLUGIN_DIR.'includes/admin/upload-functions.php';
	}
*/

	// Setup the TOPS Custom Post Types
	tops_setup_post_types();

	// Setup the TOPs Taxonomies
	tops_setup_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Add Upgraded From Option
	$current_version = get_option( 'tops_version' );
	if( $current_version ) {
		update_option( 'tops_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();

	// Pull options from WP, not TOPS's global
	$current_options = get_option( 'tops_settings', array() );
	
	// Checks if the support tickets submit page option exists
	$tickets_new = array_key_exists( 'tickets_new', $current_options ) ? get_post( $current_options['tickets_new'] ) : false;
	if ( empty( $tickets_page ) ) {
		// Tickets Page
		$tickets = wp_insert_post(
			array(
				'post_title'     => __( 'Submit a Ticket', 'total-product-support' ),
				'post_content'   => '[tops_new_ticket_form]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['tickets_new'] = $tickets;
	}
	
	// Checks if the support tickets page option exists
	$tickets_page = array_key_exists( 'tickets_page', $current_options ) ? get_post( $current_options['tickets_page'] ) : false;
	if ( empty( $tickets_page ) ) {
		// Tickets Page
		$tickets = wp_insert_post(
			array(
				'post_title'     => __( 'Support Tickets', 'total-product-support' ),
				'post_content'   => '[tops_tickets]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['tickets_page'] = $tickets;
	}
	
	$tickets = isset( $tickets ) ? $tickets : $current_options['tickets_page'];
	
	$ticket_archive_page = array_key_exists( 'ticket_archives', $current_options ) ? get_post( $current_options['ticket_archives'] ) : false;
	if ( empty( $ticket_archive_page ) ) {
		// Ticket Archives Page
		$page = wp_insert_post(
			array(
				'post_title'     => __( 'Archive', 'total-product-support' ),
				'post_content'   => '[tops_tickets_archive]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $tickets,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['ticket_archives'] = $page;
	}
	
	$ticket_category_page = array_key_exists( 'ticket_categories', $current_options ) ? get_post( $current_options['ticket_categories'] ) : false;
	if ( empty( $ticket_archive_page ) ) {
		// Ticket Categories Page
		$page = wp_insert_post(
			array(
				'post_title'     => __( 'Category', 'total-product-support' ),
				'post_content'   => '[tops_tickets_category]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $tickets,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['ticket_categories'] = $page;
	}
	
	$tickets_public_page = array_key_exists( 'tickets_public', $current_options ) ? get_post( $current_options['tickets_public'] ) : false;
	if ( empty( $tickets_public_page ) ) {
		// Ticket Categories Page
		$page = wp_insert_post(
			array(
				'post_title'     => __( 'Public Tickets', 'total-product-support' ),
				'post_content'   => '[tops_tickets_public]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $tickets,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['tickets_public'] = $page;
	}
	
	$tickets_private_page = array_key_exists( 'tickets_private', $current_options ) ? get_post( $current_options['tickets_private'] ) : false;
	if ( empty( $tickets_private_page ) ) {
		// Ticket Categories Page
		$page = wp_insert_post(
			array(
				'post_title'     => __( 'Private Tickets', 'total-product-support' ),
				'post_content'   => '[tops_tickets_private]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $tickets,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['tickets_private'] = $page;
	}
	
	$tickets_starred_page = array_key_exists( 'tickets_starred', $current_options ) ? get_post( $current_options['tickets_starred'] ) : false;
	if ( empty( $tickets_starred_page ) ) {
		// Ticket Categories Page
		$page = wp_insert_post(
			array(
				'post_title'     => __( 'Starred', 'total-product-support' ),
				'post_content'   => '[tops_tickets_starred]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $tickets,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['tickets_starred'] = $page;
	}


	// Checks if the ticket submit page option exists
	$ticket_submit_page = array_key_exists( 'ticket_submit_page', $current_options ) ? get_post( $current_options['ticket_submit_page'] ) : false;
	if( empty($ticket_submit_page) ) {
		
		// Ticket Archive Page
		$page = wp_insert_post(
			array(
				'post_title'     => __( 'Submit a Ticket', 'total-product-support' ),
				'post_content'   => '[tops_new_ticket_form title=""]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['ticket_submit_page'] = $page;
	}

	// Populate some default values
	foreach( tops_get_registered_settings() as $tab => $sections ) {
		foreach( $sections as $section => $settings) {

			// Check for backwards compatibility
/*
			$tab_sections = tops_get_settings_tab_sections( $tab );
			if( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
				$section = 'main';
				$settings = $sections;
			}
*/

			foreach ( $settings as $option ) {

				if( ! empty( $option['type'] ) && 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
					$options[ $option['id'] ] = '1';
				}

			}
		}

	}

	$merged_options = array_merge( $tops_options, $options );
	$tops_options = $merged_options;

	update_option( 'tops_settings', $merged_options );
	update_option( 'tops_version', TOPS_VERSION );

	// Create wp-content/uploads/tops/ folder and the .htaccess file
	//tops_create_protection_files( true );

	// Create TOPS roles
	$roles = new TOPS_Roles;
	$roles->add_roles();
	$roles->add_caps();

	//$api = new TOPS_API;
	//update_option( 'tops_default_api_version', 'v' . $api->get_version() );

	// Create the customer databases
	//@TOPS()->customers->create_table();
	////@TOPS()->customer_meta->create_table();

	// Check for PHP Session support, and enable if available
	//TOPS()->session->use_php_sessions();

	// Add a temporary option to note that TOPS pages have been created
	//set_transient( '_tops_installed', $merged_options, 30 );

	if( !$current_version ) {
/*
		require_once TOPS_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

		// When new upgrade routines are added, mark them as complete on fresh install
		$upgrade_routines = array(
			'upgrade_payment_taxes',
			'upgrade_customer_payments_association',
			'upgrade_user_api_keys',
			'remove_refunded_sale_logs'
		);

		foreach ( $upgrade_routines as $upgrade ) {
			tops_set_upgrade_complete( $upgrade );
		}
*/
	}

}


/**
 * When a new Blog is created in multisite, see if TOPS is network activated, and run the installer
 *
 * @since  1.0.0
 * @param  int    $blog_id The Blog ID created
 * @param  int    $user_id The User ID set as the admin
 * @param  string $domain  The URL
 * @param  string $path    Site Path
 * @param  int    $site_id The Site ID
 * @param  array  $meta    Blog Meta
 * @return void
 */
function tops_new_blog_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	if( is_plugin_active_for_network(plugin_basename(TOPS_PLUGIN_FILE)) ) {

		switch_to_blog( $blog_id );
		tops_install();
		restore_current_blog();
	}
}
add_action( 'wpmu_new_blog', 'tops_new_blog_created', 10, 6 );


/**
 * Drop our custom tables when a mu site is deleted
 *
 * @since  1.0.0
 * @param  array $tables  The tables to drop
 * @param  int   $blog_id The Blog ID being deleted
 * @return array          The tables to drop
 */
/*
function tops_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$customers_db = new TOPS_DB_Customers();
	$customer_meta_db = new TOPS_DB_Customer_Meta();
	if( $customers_db->installed() ) {
		$tables[] = $customers_db->table_name;
		$tables[] = $customer_meta_db->table_name;
	}
	restore_current_blog();

	return $tables;
}
add_filter( 'wpmu_drop_tables', 'tops_wpmu_drop_tables', 10, 2 );
*/


/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * tops_after_install hook.
 *
 * @since 1.0.0
 * @return void
 */
function tops_after_install() {

	if( !is_admin() ) {
		return;
	}

	//$tops_options = get_transient( '_tops_installed' );
	//$tops_table_check = get_option( '_tops_table_check', false );

/*
	if( false === $tops_table_check || current_time('timestamp') > $tops_table_check ) {

		if( !@TOPS()->customer_meta->installed() ) {

			// Create the customer meta database (this ensures it creates it on multisite instances where it is network activated)
			@TOPS()->customer_meta->create_table();
		}

		if( !@TOPS()->customers->installed() ) {
			// Create the customers database (this ensures it creates it on multisite instances where it is network activated)
			@TOPS()->customers->create_table();
			@TOPS()->customer_meta->create_table();

			do_action( 'tops_after_install', $tops_options );
		}

		update_option( '_tops_table_check', (current_time('timestamp') + WEEK_IN_SECONDS) );
	}
*/

/*
	if ( false !== $tops_options ) {
		// Delete the transient
		delete_transient( '_tops_installed' );
	}
*/


}
add_action( 'admin_init', 'tops_after_install' );


/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when TOPS is network activation so we need to create them during admin_init
 *
 * @since 1.0.0
 * @return void
 */
function tops_install_roles_on_network() {

	global $wp_roles;

	if( !is_object($wp_roles) ) {
		return;
	}

	if( empty($wp_roles->roles) || !array_key_exists('tops_ticket_agent', $wp_roles->roles) ) {

		// Create TOPS roles
		$roles = new TOPS_Roles;
		$roles->add_roles();
		$roles->add_caps();
	}
}
add_action( 'admin_init', 'tops_install_roles_on_network' );
