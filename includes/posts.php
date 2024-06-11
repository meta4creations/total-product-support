<?php

function tops_setup_post_types() {		
	
	/* --------------------------------------------------------- */
	/* !Add the Ticket post type - 1.0.0 */
	/* --------------------------------------------------------- */
	
	$labels = array(
		'name' => __( 'Tickets', 'total-product-support' ),
		'singular_name' => __( 'Ticket', 'total-product-support' ),
		'add_new' => __( 'Add New', 'total-product-support' ),
		'add_new_item' => __( 'Add New Ticket', 'total-product-support' ),
		'edit_item' => __( 'Edit Ticket', 'total-product-support' ),
		'new_item' => __( 'New Ticket', 'total-product-support' ),
		'view_item' => __( 'View Ticket', 'total-product-support' ),
		'search_items' => __( 'Search Tickets', 'total-product-support' ),
		'not_found' => __( 'No Tickets Found', 'total-product-support' ),
		'not_found_in_trash' => __( 'No Tickets Found In Trash', 'total-product-support' ),
		'parent_item_colon' => '',
		'menu_name' => __( 'Tickets', 'total-product-support' )
	);
	
	// Create the arguments
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		//'menu_icon' => 'dashicons-tickets-alt',
		'hierarchical' => false,
		'show_ui' => true,
		'capability_type' => 'tops_ticket',
		'map_meta_cap' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'supports' => array( 'title', 'editor' ),
		//'show_in_nav_menus' => false,
		'rewrite' => array( 'slug'=>'ticket', 'with_front'=>false ),
	);
	register_post_type( 'tops_ticket', $args );
	
	
	/* --------------------------------------------------------- */
	/* !Add the Articles post type - 1.0.0 */
	/* --------------------------------------------------------- */
	
	$labels = array(
		'name' => __( 'Articles', 'total-product-support' ),
		'singular_name' => __( 'Article', 'total-product-support' ),
		'add_new' => __( 'Add New', 'total-product-support' ),
		'add_new_item' => __( 'Add New Article', 'total-product-support' ),
		'edit_item' => __( 'Edit Article', 'total-product-support' ),
		'new_item' => __( 'New Article', 'total-product-support' ),
		'view_item' => __( 'View Article', 'total-product-support' ),
		'search_items' => __( 'Search Articles', 'total-product-support' ),
		'not_found' => __( 'No Articles Found', 'total-product-support' ),
		'not_found_in_trash' => __( 'No Articles Found In Trash', 'total-product-support' ),
		'parent_item_colon' => '',
		'menu_name' => __( 'Articles', 'total-product-support' )
	);
	
	// Create the arguments
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'menu_icon' => 'dashicons-format-aside',
		'hierarchical' => true,
		'has_archive'	=> true,
		'show_ui' => true,
		'capability_type' => 'tops_document',
		'map_meta_cap' => false,
		'show_in_menu' => true,
		'query_var' => true,
		'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'comments' ),
		//'show_in_nav_menus' => false,
		'show_in_rest' => true,
		'rewrite' => array( 'slug' => 'article', 'with_front' => false ),
	);
	
	register_post_type( 'tops_article', $args );
}
add_action( 'init','tops_setup_post_types' );



function tops_setup_taxonomies() {
	
	/* --------------------------------------------------------- */
	/* !Add the category taxonomy - 1.0.0 */
	/* --------------------------------------------------------- */
		
	// Create labels
	$labels = array(
		'name' => __('Categories', 'total-product-support'),
		'singular_name' => __('Category', 'total-product-support'),
		'search_items' =>  __('Search Categories', 'total-product-support'),
		'all_items' => __('All Categories', 'total-product-support'),
		'parent_item' => __('Parent', 'total-product-support'),
		'parent_item_colon' => __('Parent:', 'total-product-support'),
		'edit_item' => __('Edit Category', 'total-product-support'), 
		'update_item' => __('Update Category', 'total-product-support'),
		'add_new_item' => __('Add New Category', 'total-product-support'),
		'new_item_name' => __('New Category', 'total-product-support'),
		'menu_name' => __('Categories', 'total-product-support'),
	); 	 	
	
	// Create the arguments
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'public' => true,
		'publicly_queryable' => true,
		'show_in_nav_menus' => false,
		'show_admin_column' => true,
		'capabilities' => array(
			'manage_terms' => 'manage_tops_terms',
			'edit_terms' 	=> 'edit_tops_terms',
			'assign_terms' => 'assign_tops_terms',
			'delete_terms' => 'delete_tops_terms'
		),
		'rewrite' => array( 'hierarchical' => true ),
		'show_in_rest' => true
	); 
	
	register_taxonomy( 'tops_category', array( 'tops_article' ), $args );
}
add_action( 'init','tops_setup_taxonomies' );



/**
 * Get Default Labels
 *
 * @since 1.0.8.3
 * @return array $defaults Default labels
 */
function tops_get_default_labels() {
	$defaults = array(
	   'singular' => __( 'Ticket', 'total-product-support' ),
	   'plural'   => __( 'Tickets','total-product-support' )
	);
	return apply_filters( 'tops_default_downloads_name', $defaults );
}

/**
 * Get Singular Label
 *
 * @since 1.0.8.3
 *
 * @param bool $lowercase
 * @return string $defaults['singular'] Singular label
 */
function tops_get_label_singular( $lowercase = false ) {
	$defaults = tops_get_default_labels();
	return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0.8.3
 * @return string $defaults['plural'] Plural label
 */
function tops_get_label_plural( $lowercase = false ) {
	$defaults = tops_get_default_labels();
	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}