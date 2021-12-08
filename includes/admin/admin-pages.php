<?php
/**
 * Admin Pages
 *
 * @package     TOPS
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 1.0.0
 * @global $tops_discounts_page
 * @global $tops_payments_page
 * @global $tops_customers_page
 * @global $tops_settings_page
 * @global $tops_reports_page
 * @global $tops_add_ons_page
 * @global $tops_settings_export
 * @global $tops_upgrades_screen
 * @return void
 */
function tops_add_options_link() {
	//global $tops_discounts_page, $tops_payments_page, $tops_settings_page, $tops_reports_page, $tops_add_ons_page, $tops_settings_export, $tops_upgrades_screen, $tops_tools_page, $tops_customers_page;
	global $tops_settings_page;

	//$tops_payment            = get_post_type_object( 'tops_payment' );

	//$customer_view_role     = apply_filters( 'tops_view_customers_role', 'view_shop_reports' );

	//$tops_payments_page      = add_submenu_page( 'edit.php?post_type=tops_ticket', $tops_payment->labels->name, $tops_payment->labels->menu_name, 'edit_shop_payments', 'edd-payment-history', 'tops_payment_history_page' );
	//$tops_customers_page     = add_submenu_page( 'edit.php?post_type=tops_ticket', __( 'Customers', 'total-product-support' ), __( 'Customers', 'total-product-support' ), $customer_view_role, 'edd-customers', 'tops_customers_page' );
	//$tops_discounts_page     = add_submenu_page( 'edit.php?post_type=tops_ticket', __( 'Discount Codes', 'total-product-support' ), __( 'Discount Codes', 'total-product-support' ), 'manage_shop_discounts', 'edd-discounts', 'tops_discounts_page' );
	//$tops_reports_page       = add_submenu_page( 'edit.php?post_type=tops_ticket', __( 'Earnings and Sales Reports', 'total-product-support' ), __( 'Reports', 'total-product-support' ), 'view_shop_reports', 'edd-reports', 'tops_reports_page' );
	$tops_settings_page      = add_submenu_page( 'edit.php?post_type=tops_ticket', __( 'Total Product Support Settings', 'total-product-support' ), __( 'Settings', 'total-product-support' ), 'manage_tops_ticket_settings', 'tops-settings', 'tops_options_page' );
	//$tops_tools_page         = add_submenu_page( 'edit.php?post_type=tops_ticket', __( 'Total Product Support Info and Tools', 'total-product-support' ), __( 'Tools', 'total-product-support' ), 'manage_tops_ticket_settings', 'edd-tools', 'tops_tools_page' );
	//$tops_add_ons_page       = add_submenu_page( 'edit.php?post_type=tops_ticket', __( 'Total Product Support Extensions', 'total-product-support' ), __( 'Extensions', 'total-product-support' ), 'manage_tops_ticket_settings', 'edd-addons', 'tops_add_ons_page' );
	//$tops_upgrades_screen    = add_submenu_page( null, __( 'TOPS Upgrades', 'total-product-support' ), __( 'TOPS Upgrades', 'total-product-support' ), 'manage_tops_ticket_settings', 'edd-upgrades', 'tops_upgrades_screen' );

}
add_action( 'admin_menu', 'tops_add_options_link', 10 );

/**
 *  Determines whether the current admin page is a specific TOPS admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook. Failure to pass in $view will match all views of $main_page.
 *  Failure to pass in $main_page will return true if on any TOPS page
 *
 *  @since 1.9.6
 *
 *  @param string $page Optional. Main page's slug
 *  @param string $view Optional. Page view ( ex: `edit` or `delete` )
 *  @return bool True if TOPS admin page we're looking for or an TOPS page or if $page is empty, any TOPS page
 */
function tops_is_admin_page( $passed_page = '', $passed_view = '' ) {

	global $pagenow, $typenow;

	$found      	= false;
	$post_type  	= isset( $_GET['post_type'] )  	? strtolower( $_GET['post_type'] )  	: false;
	$action     	= isset( $_GET['action'] )     	? strtolower( $_GET['action'] )     	: false;
	$taxonomy   	= isset( $_GET['taxonomy'] )   	? strtolower( $_GET['taxonomy'] )   	: false;
	$page       	= isset( $_GET['page'] )       	? strtolower( $_GET['page'] )       	: false;
	$view       	= isset( $_GET['view'] )       	? strtolower( $_GET['view'] )       	: false;
	$tops_action 	= isset( $_GET['tops-action'] ) ? strtolower( $_GET['tops-action'] ) 	: false;
	$tab        	= isset( $_GET['tab'] )        	? strtolower( $_GET['tab'] )        	: false;

	switch ( $passed_page ) {
		case 'tops_ticket':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'post.php' ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'post-new.php' ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) || 'tops_ticket' === $post_type || ( 'post-new.php' == $pagenow && 'tops_ticket' === $post_type ) ) {
						$found = true;
					}
					break;
			}
			break;
		case 'categories':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'download_category' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
/*
		case 'tags':
			switch ( $passed_view ) {
				case 'list-table':
				case 'new':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' !== $action && 'download_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'edit' === $action && 'download_tax' === $taxonomy ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit-tags.php' && 'download_tax' === $taxonomy ) {
						$found = true;
					}
					break;
			}
			break;
*/
/*
		case 'payments':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-payment-history' === $page && false === $view  ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-payment-history' === $page && 'view-order-details' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-payment-history' === $page ) {
						$found = true;
					}
					break;
			}
			break;
*/
/*
		case 'discounts':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page && false === $tops_action ) {
						$found = true;
					}
					break;
				case 'edit':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page && 'edit_discount' === $tops_action ) {
						$found = true;
					}
					break;
				case 'new':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page && 'add_discount' === $tops_action ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-discounts' === $page ) {
						$found = true;
					}
					break;
			}
			break;
*/
/*
		case 'reports':
			switch ( $passed_view ) {
				// If you want to do something like enqueue a script on a particular report's duration, look at $_GET[ 'range' ]
				case 'earnings':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && ( 'earnings' === $view || '-1' === $view || false === $view ) ) {
						$found = true;
					}
					break;
				case 'downloads':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'downloads' === $view ) {
						$found = true;
					}
					break;
				case 'customers':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'customers' === $view ) {
						$found = true;
					}
					break;
				case 'gateways':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'gateways' === $view ) {
						$found = true;
					}
					break;
				case 'taxes':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'taxes' === $view ) {
						$found = true;
					}
					break;
				case 'export':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'export' === $view ) {
						$found = true;
					}
					break;
				case 'logs':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page && 'logs' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page ) {
						$found = true;
					}
					break;
			}
			break;
*/
		case 'settings':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'tops-settings' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'emails':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'tops-settings' === $page && 'emails' === $tab ) {
						$found = true;
					}
					break;
				case 'styles':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'tops-settings' === $page && 'styles' === $tab ) {
						$found = true;
					}
					break;
				case 'extensions':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'tops-settings' === $page && 'extensions' === $tab ) {
						$found = true;
					}
					break;
				case 'licenses':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'tops-settings' === $page && 'licenses' === $tab ) {
						$found = true;
					}
					break;
				case 'misc':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'tops-settings' === $page && 'misc' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'tops-settings' === $page ) {
						$found = true;
					}
					break;
			}
			break;
/*
		case 'tools':
			switch ( $passed_view ) {
				case 'general':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && ( 'general' === $tab || false === $tab ) ) {
						$found = true;
					}
					break;
				case 'api_keys':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && 'api_keys' === $tab ) {
						$found = true;
					}
					break;
				case 'system_info':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && 'system_info' === $tab ) {
						$found = true;
					}
					break;
				case 'import_export':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page && 'import_export' === $tab ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-tools' === $page ) {
						$found = true;
					}
					break;
			}
			break;
*/
/*
		case 'addons':
			if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-addons' === $page ) {
				$found = true;
			}
			break;
*/
/*
		case 'customers':
			switch ( $passed_view ) {
				case 'list-table':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page && false === $view ) {
						$found = true;
					}
					break;
				case 'overview':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page && 'overview' === $view ) {
						$found = true;
					}
					break;
				case 'notes':
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page && 'notes' === $view ) {
						$found = true;
					}
					break;
				default:
					if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-customers' === $page ) {
						$found = true;
					}
					break;
			}
			break;
*/
/*
		case 'reports':
			if ( ( 'tops_ticket' == $typenow || 'tops_ticket' === $post_type ) && $pagenow == 'edit.php' && 'edd-reports' === $page ) {
				$found = true;
			}
			break;
*/
		default:
			//global $tops_discounts_page, $tops_payments_page, $tops_settings_page, $tops_reports_page, $tops_system_info_page, $tops_add_ons_page, $tops_settings_export, $tops_upgrades_screen, $tops_customers_page, $tops_reports_page;
			global $tops_settings_page;
			//$admin_pages = apply_filters( 'tops_admin_pages', array( $tops_discounts_page, $tops_payments_page, $tops_settings_page, $tops_reports_page, $tops_system_info_page, $tops_add_ons_page, $tops_settings_export, $tops_customers_page, $tops_reports_page ) );
			$admin_pages = apply_filters( 'tops_admin_pages', array( $tops_settings_page ) );
			if ( 'tops_ticket' == $typenow || 'index.php' == $pagenow || 'post-new.php' == $pagenow || 'post.php' == $pagenow ) {
				$found = true;
/*
				if( 'edd-upgrades' === $page ) {
					$found = false;
				}
*/
			} elseif ( in_array( $pagenow, $admin_pages ) ) {
				$found = true;
			}
			break;
	}

	return (bool) apply_filters( 'tops_is_admin_page', $found, $page, $view, $passed_page, $passed_view );
}
