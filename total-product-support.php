<?php
/*
Plugin Name: Total Product Support
Description: Support tickets and documentation
Version: 0.1
Author: Metaphor Creations
Author URI: http://www.metaphorcreations.com
Text Domain: total-product-support
Domain Path: languages
License: GPL2
*/

/*  
Copyright 2017 Metaphor Creations  (email : joe@metaphorcreations.com)

Total Product Support is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

Total Product Support is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Total Product Support. If not, see <http://www.gnu.org/licenses/>.
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Total_Product_Support' ) ) :

/**
 * Main Total_Product_Support Class.
 *
 * @since 1.0.0
 */
 
final class Total_Product_Support {
	
	/** Singleton *************************************************************/

	/**
	 * @var Total_Product_Support The one true Total_Product_Support
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * TOPS Roles Object.
	 *
	 * @var object|TOPS_Roles
	 * @since 1.0.0
	 */
	public $roles;

	/**
	 * TOPS HTML Session Object.
	 *
	 * This holds cart items, purchase sessions, and anything else stored in the session.
	 *
	 * @var object|TOPS_Session
	 * @since 1.5
	 */
	public $session;

	/**
	 * TOPS HTML Element Helper Object.
	 *
	 * @var object|TOPS_HTML_Elements
	 * @since 1.5
	 */
	public $html;

	/**
	 * TOPS Emails Object.
	 *
	 * @var object|TOPS_Emails
	 * @since 2.1
	 */
	public $emails;

	/**
	 * TOPS Email Template Tags Object.
	 *
	 * @var object|TOPS_Email_Template_Tags
	 * @since 1.9
	 */
	public $email_tags;

	/**
	 * TOPS Customers DB Object.
	 *
	 * @var object|TOPS_DB_Customers
	 * @since 2.1
	 */
	public $customers;

	/**
	 * TOPS Customer meta DB Object.
	 *
	 * @var object|TOPS_DB_Customer_Meta
	 * @since 2.6
	 */
	public $customer_meta;

	/**
	 * Main Total_Product_Support Instance.
	 *
	 * Insures that only one instance of Total_Product_Support exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0.0
	 * @static
	 * @staticvar array $instance
	 * @uses Total_Product_Support::setup_constants() Setup the constants needed.
	 * @uses Total_Product_Support::includes() Include the required files.
	 * @uses Total_Product_Support::load_textdomain() load the language files.
	 * @see TOPS()
	 * @return object|Total_Product_Support The one true Total_Product_Support
	 */
	public static function instance() {
		if( !isset(self::$instance) && !(self::$instance instanceof Total_Product_Support) ) {
			
			self::$instance = new Total_Product_Support;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->roles         = new TOPS_Roles();
			self::$instance->tickets       = new TOPS_Tickets();
			self::$instance->categories    = new TOPS_Categories();
			//self::$instance->session       = new TOPS_Session();
			//self::$instance->html          = new TOPS_HTML_Elements();
			self::$instance->emails        = new TOPS_Emails();
			self::$instance->email_tags    = new TOPS_Email_Template_Tags();
			//self::$instance->users				= new TOPS_DB_Users();
			//self::$instance->customer_meta = new TOPS_DB_Customer_Meta();
		}
		
		do_action( 'tops_init' );

		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'total-product-support' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'total-product-support' ), '1.0.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if( !defined('TOPS_VERSION') ) {
			define( 'TOPS_VERSION', '1.0.0' );
		}

		// Plugin Folder Path.
		if( !defined( 'TOPS_PLUGIN_DIR') ) {
			define( 'TOPS_PLUGIN_DIR', plugin_dir_path(__FILE__) );
		}

		// Plugin Folder URL.
		if( !defined( 'TOPS_PLUGIN_URL') ) {
			define( 'TOPS_PLUGIN_URL', plugin_dir_url(__FILE__) );
		}

		// Plugin Root File.
		if( !defined( 'TOPS_PLUGIN_FILE') ) {
			define( 'TOPS_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.4
	 * @return void
	 */
	private function includes() {
		
		global $tops_options;

		require_once TOPS_PLUGIN_DIR.'includes/admin/settings/register-settings.php';
		$tops_options = tops_get_settings();
		
		//echo '<pre>';print_r($tops_options);echo '</pre>';

		require_once TOPS_PLUGIN_DIR.'includes/posts.php';
		require_once TOPS_PLUGIN_DIR.'includes/helpers.php';
		require_once TOPS_PLUGIN_DIR.'includes/hooks.php';
		require_once TOPS_PLUGIN_DIR.'includes/ajax.php';
		require_once TOPS_PLUGIN_DIR.'includes/shortcodes.php';
		require_once TOPS_PLUGIN_DIR.'includes/static.php';
		require_once TOPS_PLUGIN_DIR.'includes/widgets.php';
		require_once TOPS_PLUGIN_DIR.'includes/formatting.php';
		require_once TOPS_PLUGIN_DIR.'includes/misc-functions.php';
		
		//require_once TOPS_PLUGIN_DIR.'includes/meta-category.php';
		require_once TOPS_PLUGIN_DIR.'includes/meta-ticket.php';
		require_once TOPS_PLUGIN_DIR.'includes/meta-user.php';
		
		require_once TOPS_PLUGIN_DIR.'includes/classes/class-tops-roles.php';
		require_once TOPS_PLUGIN_DIR.'includes/classes/class-tops-ticket.php';	
		require_once TOPS_PLUGIN_DIR.'includes/classes/class-tops-ticket-note.php';
		require_once TOPS_PLUGIN_DIR.'includes/classes/class-tops-ticket-comment.php';
		//require_once TOPS_PLUGIN_DIR.'includes/classes/class-tops-user.php';
		require_once TOPS_PLUGIN_DIR.'includes/classes/class-tops-categories.php';
		require_once TOPS_PLUGIN_DIR.'includes/classes/class-tops-tickets.php';
		
		require_once TOPS_PLUGIN_DIR.'includes/emails/class-tops-emails.php';
		require_once TOPS_PLUGIN_DIR.'includes/emails/class-tops-email-tags.php';
		require_once TOPS_PLUGIN_DIR.'includes/emails/functions.php';
		require_once TOPS_PLUGIN_DIR.'includes/emails/template.php';
		require_once TOPS_PLUGIN_DIR.'includes/emails/actions.php';
		//require_once TOPS_PLUGIN_DIR.'includes/user-functions.php';
		
		if( is_admin() ) {
			require_once TOPS_PLUGIN_DIR.'includes/admin/admin-pages.php';
			require_once TOPS_PLUGIN_DIR.'includes/admin/settings/display-settings.php';
		}
		
		require_once TOPS_PLUGIN_DIR.'includes/install.php';
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$tops_lang_dir = dirname( plugin_basename( TOPS_PLUGIN_FILE ) ) . '/languages/';
		$tops_lang_dir = apply_filters( 'tops_languages_directory', $tops_lang_dir );
		load_plugin_textdomain( 'total-product-support', false, $tops_lang_dir );
		
		$current_options = get_option( 'tops_settings', array() );
	}

}

endif; // End if class_exists check.


/*
The main function for that returns Total_Product_Support

The main function responsible for returning the one true Total_Product_Support
Instance to functions everywhere.

Use this function like you would a global variable, except without needing
to declare the global.

Example: <?php $tops = TOPS(); ?>

@since 1.0.0
@return object|Total_Product_Support The one true Total_Product_Support Instance.
*/
function TOPS() {
	return Total_Product_Support::instance();
}

// Get TOPS Running.
TOPS();