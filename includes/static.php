<?php

function tops_enqueue_scripts() {
	
	// Register switchery
	wp_register_style( 'switchery', plugin_dir_url(__FILE__).'static/switchery/switchery.min.css', false, '0.8.2' );
	wp_register_script( 'switchery', plugin_dir_url(__FILE__).'static/switchery/switchery.min.js', false, '0.8.2' );
	
	// Register swipebox
	wp_register_style( 'swipebox', plugin_dir_url(__FILE__).'static/swipebox/css/swipebox.min.css', false, '1.5.2' );
	wp_register_script( 'swipebox', plugin_dir_url(__FILE__).'static/swipebox/js/jquery.swipebox.min.js', false, '1.5.2' );
	
	// Register validation
	wp_register_script( 'jquery-validation', plugin_dir_url(__FILE__).'static/jquery-validation-1.15.0/dist/jquery.validate.min.js', array( 'jquery' ), '1.15.0', true );
	
	// Register the plugin scripts
	wp_register_style( 'font-awesome', plugin_dir_url(__FILE__).'static/font-awesome/css/font-awesome.min.css', false, '4.7.0' );
	wp_enqueue_style( 'total-product-support', plugin_dir_url(__FILE__).'static/css/style.css', array('font-awesome', 'switchery', 'swipebox'), filemtime(plugin_dir_path(__FILE__).'static/css/style.css') );
		
	wp_enqueue_script( 'total-product-support', plugin_dir_url(__FILE__).'static/js/scripts.min.js', array(
		'jquery-form',
		'switchery',
		'swipebox',
		'jquery-validation'
	), filemtime(plugin_dir_path(__FILE__).'static/js/scripts.min.js'), true );
	wp_localize_script( 'total-product-support', 'tops_vars', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'security' => wp_create_nonce( 'total-product-support' ),
			'strings' => tops_strings(),
			'templates' => tops_templates()
		)
	);
	
	wp_enqueue_media();
		
	if( is_admin() ) {
	
		// Register the plugin scripts
		wp_enqueue_style( 'total-product-support-admin', plugin_dir_url(__FILE__).'static/css/style-admin.css', array('switchery'), filemtime(plugin_dir_path(__FILE__).'static/css/style-admin.css') );
		wp_enqueue_script( 'total-product-support-admin', plugin_dir_url(__FILE__).'static/js/scripts-admin.min.js', array('switchery'), filemtime(plugin_dir_path(__FILE__).'static/js/scripts-admin.min.js'), true );
		
	}
}
add_action('admin_enqueue_scripts', 'tops_enqueue_scripts' );
add_action('wp_enqueue_scripts', 'tops_enqueue_scripts' );