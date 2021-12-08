<?php

/**
 * Add new fields above 'Update' button.
 *
 * @param WP_User $user User object.
 */
function tops_additional_profile_fields( $user ) {
	$tops_customer_notes = get_user_meta( $user->ID, 'tops_customer_notes', true );
  ?>
  <h3><?php _e('TOPS Extra Profile Information', 'total-product-support'); ?></h3>

  <table class="form-table">
 	 <tr>
 		 <th><label for="topscustomernotes"><?php _e('Customer Notes', 'total-product-support'); ?></label></th>
 		 <td>
	 		 <?php
			$content = '';
			$editor_id = 'topscustomernotes';
			$settings = apply_filters( 'tops-customer-notes-editor-settings', array(
				'textarea_rows' => 8,
				'drag_drop_upload' => true,
			));
			wp_editor( $tops_customer_notes, $editor_id, $settings );
			?>
 		 </td>
 	 </tr>
  </table>
  <?php
}
add_action( 'show_user_profile', 'tops_additional_profile_fields' );
add_action( 'edit_user_profile', 'tops_additional_profile_fields' );


/**
 * Save additional profile fields.
 *
 * @param  int $user_id Current user ID.
 */
function tops_save_profile_fields( $user_id ) {

    if( !current_user_can('edit_user', $user_id) ) {
   	 return false;
    }

    if( empty($_POST['topscustomernotes']) ) {
   	 return false;
    }

    update_usermeta( $user_id, 'tops_customer_notes', wp_kses_post($_POST['topscustomernotes']) );
}
add_action( 'personal_options_update', 'tops_save_profile_fields' );
add_action( 'edit_user_profile_update', 'tops_save_profile_fields' );


/**
 * Add custom user meta columns
 *
 * @access  public 
 * @since   1.0.0
 */
function tops_add_users_columns( $column ) {
    $column['tickets'] = __('Tickets', 'total-product-suppport');
    return $column;
}
add_filter( 'manage_users_columns', 'tops_add_users_columns' );


/**
 * Render the custom user meta columns
 *
 * @access  public
 * @since   1.0.0
 */
function tops_render_user_columns( $val, $column_name, $user_id ) {
    
    switch ($column_name) {
	    
	    case 'tickets':
	    	$args = array(
					'posts_per_page' => -1,
					'post_type' => 'tops_ticket',
					'meta_query' => array(
						'user_id' => array(
							'key' => '_tops_ticket_user_id',
							'value' => $user_id
						)
					)
				);
				$tickets = get_posts( $args );
				$ticket_count = count( $tickets );
				if( $ticket_count > 0 ) {
					return '<i class="dashicons dashicons-tickets-alt"></i> '."<a href='edit.php?post_type=tops_ticket&user={$user_id}'>".count( $tickets )."</a>";
				} else {
					return '<i class="dashicons dashicons-tickets-alt"></i> '.$ticket_count;	
				}
	      
	      break;
	      
    }
    
    return $val;
}
add_filter( 'manage_users_custom_column', 'tops_render_user_columns', 10, 3 );

