<?php

/**
 * TOPS Roles and Capabilities
 *
 * @package     TOPS
 * @subpackage  Classes/TOPS Roles
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/
class TOPS_Roles {
	
	/**
	 * Get things going
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Add new roles with default WP caps
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function add_roles() {
		
		add_role( 'tops_ticket_manager', __( 'Ticket Manager', 'total-product-support' ), array(
			'read'                   => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'delete_others_pages'    => true,
			'delete_others_posts'    => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages'      => true,
			'edit_others_posts'      => true,
			'edit_pages'             => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_published_pages'   => true,
			'edit_published_posts'   => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true
		));
		
		add_role( 'tops_ticket_agent', __( 'Ticket Agent', 'easy-digital-downloads' ), array(
			'read'                   => true,
			'edit_posts'             => false,
			'upload_files'           => true,
			'delete_posts'           => false
		));
	}
	
	

	/**
	 * Add new ticket specific capabilities
	 *
	 * @access public
	 * @since  1.0.0
	 * @global WP_Roles $wp_roles
	 * @return void
	 */
	public function add_caps() {
		global $wp_roles;

		if( class_exists('WP_Roles') ) {
			if( !isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if( is_object($wp_roles) ) {
			
			/** Ticket Manager Capabilities */
			$wp_roles->add_cap( 'tops_ticket_manager', 'manage_tops_ticket_settings' );
			$wp_roles->add_cap( 'tops_ticket_manager', 'manage_tops_terms' );
			$wp_roles->add_cap( 'tops_ticket_manager', 'edit_tops_terms' );
			$wp_roles->add_cap( 'tops_ticket_manager', 'delete_tops_terms' );
			$wp_roles->add_cap( 'tops_ticket_manager', 'assign_tops_terms' );
			
			/** Site Administrator Capabilities */
			$wp_roles->add_cap( 'administrator', 'manage_tops_ticket_settings' );
			$wp_roles->add_cap( 'administrator', 'manage_tops_terms' );
			$wp_roles->add_cap( 'administrator', 'edit_tops_terms' );
			$wp_roles->add_cap( 'administrator', 'delete_tops_terms' );
			$wp_roles->add_cap( 'administrator', 'assign_tops_terms' );
			
			/** Subscriber Capabilities */
			$wp_roles->add_cap( 'subscriber', 'upload_files' );

			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'tops_ticket_manager', $cap );
					$wp_roles->add_cap( 'tops_ticket_agent', $cap );
					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'editor', $cap );
				}
			}
		}
	}


	/**
	 * Gets the core post type capabilities
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array $capabilities Core post type capabilities
	 */
	public function get_core_caps() {
		$capabilities = array();

		$capability_types = array( 'tops_ticket', 'tops_document' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
			);
		}

		return $capabilities;
	}


	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function remove_caps() {

		global $wp_roles;

		if( class_exists('WP_Roles') ) {
			if( !isset($wp_roles) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if( is_object($wp_roles) ) {
			
			/** Ticket Manager Capabilities */
			$wp_roles->remove_cap( 'tops_ticket_manager', 'tops_manage_ticket_settings' );
			$wp_roles->remove_cap( 'tops_ticket_manager', 'manage_tops_terms' );
			$wp_roles->remove_cap( 'tops_ticket_manager', 'edit_tops_terms' );
			$wp_roles->remove_cap( 'tops_ticket_manager', 'delete_tops_terms' );
			$wp_roles->remove_cap( 'tops_ticket_manager', 'assign_tops_terms' );
			
			/** Site Administrator Capabilities */
			$wp_roles->remove_cap( 'administrator', 'tops_manage_ticket_settings' );
			$wp_roles->remove_cap( 'administrator', 'manage_tops_terms' );
			$wp_roles->remove_cap( 'administrator', 'edit_tops_terms' );
			$wp_roles->remove_cap( 'administrator', 'delete_tops_terms' );
			$wp_roles->remove_cap( 'administrator', 'assign_tops_terms' );
			
			/** Subscriber Capabilities */
			$wp_roles->remove_cap( 'subscriber', 'upload_files' );
			

			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'tops_ticket_manager', $cap );
					$wp_roles->remove_cap( 'tops_ticket_agent', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
				}
			}
		}
	}

}
