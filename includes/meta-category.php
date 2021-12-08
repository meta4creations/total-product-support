<?php
	
/**
 * Add new meta fields to tops_categories
 *
 * @access  public
 * @since   1.0.0
 */
 
function tops_category_add_meta_fields( $taxonomy ) {
  ?>
  
  <?php // Default ticket Agent ?>
  <div class="form-field term-group">
    <label for="tops_default_ticket_agent_id"><?php _e( 'Default Ticket Agent', 'total-product-support' ); ?></label>
    <?php
    $args = array(
			'role__in' => array('administrator', 'tops_ticket_manager', 'tops_ticket_agent'),
		);
		$users = get_users( $args );
		echo '<select name="tops_default_ticket_agent_id">';
			if( is_array($users) && count($users) > 0 ) {
				foreach( $users as $i=>$user ) {
					echo '<option value="'.$user->ID.'">'.$user->data->display_name.'</option>';
				}
			}
		echo '</select>';
		echo '<p class="description">'.__('Select the default agent to assign to the new tickets of this category.', 'total-product-support').'</p>';
    ?>  
  </div>
  
  <?php // Category Icon ?>
  <div class="form-field term-group">
    <label for="tops_term_thumbnail"><?php _e( 'Icon', 'total-product-support' ); ?></label>
    <a href="#" class="tops-thumbnail-select">
	    <input type="hidden" id="tops_term_thumbnail" name="tops_term_thumbnail" />
		 <img src="<?php echo TOPS_PLUGIN_URL; ?>includes/static/img/no-image.png" alt="'.__('No Image', 'total-product-support').'" />
    </a>   
  </div>
  <?php
}
add_action( 'tops_category_add_form_fields', 'tops_category_add_meta_fields', 10, 2 );


/**
 * Add edit meta fields to tops_categories
 *
 * @access  public
 * @since   1.0.0
 */
 
function tops_category_edit_meta_fields( $term, $taxonomy ) {
  $tops_default_ticket_agent_id = get_term_meta( $term->term_id, 'tops_default_ticket_agent_id', true );
  $tops_term_thumbnail = get_term_meta( $term->term_id, 'tops_term_thumbnail', true );
  ?>
  
  <?php // Default ticket Agent ?>
  <tr class="form-field term-group-wrap">
    <th scope="row">
      <label for="tops_default_ticket_agent_id"><?php _e( 'Default Ticket Agent', 'total-product-support' ); ?></label>
    </th>
    <td>
      <?php
	    $args = array(
				'role__in' => array('administrator', 'tops_ticket_manager', 'tops_ticket_agent'),
			);
			$users = get_users( $args );
			echo '<select name="tops_default_ticket_agent_id">';
				if( is_array($users) && count($users) > 0 ) {
					foreach( $users as $i=>$user ) {
						echo '<option value="'.$user->ID.'" '.selected($user->ID, $tops_default_ticket_agent_id, false).'>'.$user->data->display_name.'</option>';
					}
				}
			echo '</select>';
			echo '<p class="description">'.__('Select the default agent to assign to the new tickets of this category.', 'total-product-support').'</p>';
	    ?>
    </td>
  </tr>
  
  <?php // Category Icon ?>
  <tr class="form-field term-group-wrap">
    <th scope="row">
      <label for="tops_term_thumbnail"><?php _e( 'Icon', 'total-product-support' ); ?></label>
    </th>
    <td>
	    <a href="#" class="tops-thumbnail-select">
		    <input type="hidden" id="tops_term_thumbnail" name="tops_term_thumbnail" value="<?php echo $tops_term_thumbnail; ?>" />
		  	<?php if( $image = wp_get_attachment_image($tops_term_thumbnail, 'thumbnail') ) {
			  	echo $image;
		  	} else {
			  	echo '<img src="'.TOPS_PLUGIN_URL.'inc/static/img/no-image.png" alt="'.__('No Image', 'total-product-support').'" />';
		  	} ?>
	    </a> 
    </td>
  </tr>
  <?php
}
add_action( 'tops_category_edit_form_fields', 'tops_category_edit_meta_fields', 10, 2 );


/**
 * Save the tops_categories custom meta
 *
 * @access  public
 * @since   1.0.0
 */
 
function tops_category_save_taxonomy_meta( $term_id, $tag_id ) {
  if( isset($_POST['tops_default_ticket_agent_id']) ) {
    update_term_meta( $term_id, 'tops_default_ticket_agent_id', intval($_POST['tops_default_ticket_agent_id']) );
  }
  if( isset($_POST['tops_term_thumbnail']) ) {
    update_term_meta( $term_id, 'tops_term_thumbnail', intval($_POST['tops_term_thumbnail']) );
  }
}
add_action( 'created_tops_category', 'tops_category_save_taxonomy_meta', 10, 2 );
add_action( 'edited_tops_category', 'tops_category_save_taxonomy_meta', 10, 2 );


/**
 * Add the custom tops_categories meta columns
 *
 * @access  public
 * @since   1.0.0
 */

function tops_category_add_field_columns( $columns ) {
	
	//unset($columns['description']);
	//unset($columns['slug']);
	
	$modified_columns = array();
	$counter = 0;
	
	if( is_array($columns) && count($columns) > 0 ) {
		foreach( $columns as $key=>$label ) {
			$modified_columns[$key] = $label;
			if( $counter == 0 ) {
				$modified_columns['tops_term_thumbnail'] = '';
			}
			if( $counter == 1 ) {
				$modified_columns['tops_default_ticket_agent_id'] = __( 'Default Agent', 'total-product-support' );
			}
			$counter++;
		}
	}
	
	return $modified_columns;
}
add_filter( 'manage_edit-tops_category_columns', 'tops_category_add_field_columns' );


/**
 * Render the custom tops_categories meta columns
 *
 * @access  public
 * @since   1.0.0
 */

function tops_category_render_field_columns( $content, $column_name, $term_id ) {
  
  switch( $column_name ) {
	  
	  case 'tops_term_thumbnail' :
	  	$tops_term_thumbnail = get_term_meta( $term_id, 'tops_term_thumbnail', true );
	  	echo '<a href="'.esc_url(get_edit_term_link($term_id, 'tops_category')).'">';
	  	if( $image = wp_get_attachment_image($tops_term_thumbnail, 'thumbnail') ) {
		  	echo $image;
	  	} else {
		  	echo '<img src="'.TOPS_PLUGIN_URL.'inc/static/img/no-image.png" alt="'.__('No Image', 'total-product-support').'" />';
	  	}
	  	echo '</a>';
	    break;
	    
	   case 'tops_default_ticket_agent_id' :
	  	$agent_id = get_term_meta( $term_id, 'tops_default_ticket_agent_id', true );
	  	$agent = get_userdata( $agent_id );
	    $content = $agent->display_name;
	    break;
  }

  return $content;
}
add_filter( 'manage_tops_category_custom_column', 'tops_category_render_field_columns', 10, 3 );