<?php	
	
/**
 * Add metaboxes
 *
 * @access  public
 * @since   1.0.0
 */

function tops_metabox() {
	
	global $post;
	
	if( is_object($post) ) {
		
		$ticket_id = get_post_meta( $post->ID, '_tops_ticket_id', true );
		
		// Add a ticket details metabox
		add_meta_box( 'tops_ticket_details', sprintf(__('Ticket ID: %s', 'total-product-support'), $ticket_id), 'tops_ticket_details_metabox', 'tops_ticket', 'side', 'high' );
		
		// Add a ticket history metabox
		add_meta_box( 'tops_ticket_history', __('Ticket History', 'total-product-support'), 'tops_ticket_history_metabox', 'tops_ticket', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'tops_metabox' );

	
/**
 * Render the ticket details metabox
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_details_metabox() {
	
	global $post;
	$agent = get_post_meta( $post->ID, '_tops_ticket_agent_id', true );
	$user = get_post_meta( $post->ID, '_tops_ticket_user_id', true );
	$license = get_post_meta( $post->ID, '_tops_ticket_license', true );
	$related_url = get_post_meta( $post->ID, '_tops_ticket_related_url', true );
	$status = get_post_meta( $post->ID, '_tops_ticket_status', true );
	$status = ($status == 'closed') ? $status : 'open';
	$type = get_post_meta( $post->ID, '_tops_ticket_type', true );
	$starred = get_post_meta( $post->ID, '_tops_ticket_starred', true );

	echo '<input type="hidden" name="tops_ticket_metabox_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	echo '<div class="tops-admin-meta">';
	
		echo '<div class="tops-admin-meta-row tops-admin-meta-row-client">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_user_id">'.__('Client', 'total-product-support').'</label>';
				if( $user ) {
					$userdata = get_userdata($user);
					echo '<a href="'.get_edit_user_link( $user ).'">'.$userdata->display_name.'</a>';
				} else {
					echo '<input type="number" name="_tops_ticket_user_id" value="'.$user.'" />';
				}
			echo '</div>';
		echo '</div>';
	
		echo '<div class="tops-admin-meta-row">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_license">'.__('License', 'total-product-support').'</label>';
				echo '<input type="text" name="_tops_ticket_license" value="'.$license.'" />';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="tops-admin-meta-row">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_related_url">'.__('Related URL', 'total-product-support').'</label>';
				echo '<input type="text" name="_tops_ticket_related_url" value="'.$related_url.'" />';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="tops-admin-meta-row">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_status">'.__('Ticket Status', 'total-product-support').'</label>';
				echo '<label><input type="radio" name="_tops_ticket_status" value="open" '.checked('open', $status, false).' /> '.__('Open', 'total-product-support').'</label> &nbsp;&nbsp;';
				echo '<label><input type="radio" name="_tops_ticket_status" value="closed" '.checked('closed', $status, false).' /> '.__('Closed', 'total-product-support').'</label>';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="tops-admin-meta-row">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_type">'.__('Ticket Type', 'total-product-support').'</label>';
				echo '<label><input type="radio" name="_tops_ticket_type" value="public" '.checked('public', $type, false).' /> '.__('Public', 'total-product-support').'</label> &nbsp;&nbsp;';
				echo '<label><input type="radio" name="_tops_ticket_type" value="private" '.checked('private', $type, false).' /> '.__('Private', 'total-product-support').'</label>';
			echo '</div>';
		echo '</div>';
		
/*
		echo '<div class="tops-admin-meta-row">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_is_unread">'.__('Response Needed', 'total-product-support').'</label>';
				echo '<label><input type="radio" name="_tops_ticket_is_unread" value="yes" '.checked('yes', $unread, false).' /> '.__('Yes', 'total-product-support').'</label> &nbsp;&nbsp;';
				echo '<label><input type="radio" name="_tops_ticket_is_unread" value="no" '.checked('no', $unread, false).' /> '.__('No', 'total-product-support').'</label>';
			echo '</div>';
		echo '</div>';
*/
		
/*
		echo '<div class="tops-admin-meta-row">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_is_starred">'.__('Favorite', 'total-product-support').'</label>';
				echo '<label><input type="radio" name="_tops_ticket_is_starred" value="yes" '.checked('yes', $favorite, false).' /> '.__('Yes', 'total-product-support').'</label> &nbsp;&nbsp;';
				echo '<label><input type="radio" name="_tops_ticket_is_starred" value="no" '.checked('no', $favorite, false).' /> '.__('No', 'total-product-support').'</label>';
			echo '</div>';
		echo '</div>';
*/

		$args = array(
			'role__in' => array('administrator', 'tops_ticket_manager', 'tops_ticket_agent'),
		);
		$users = get_users( $args );
		echo '<div class="tops-admin-meta-row">';
			echo '<div class="tops-admin-meta-item">';
				echo '<label class="tops-admin-meta-label" for="_tops_ticket_agent_id">'.__('Agent', 'total-product-support').'</label>';
				echo '<select name="_tops_ticket_agent_id">';
					if( is_array($users) && count($users) > 0 ) {
						foreach( $users as $i=>$user ) {
							echo '<option value="'.$user->ID.'" '.selected($user->ID, $agent, false).'>'.$user->data->display_name.'</option>';
						}
					}
				echo '</select>';
			echo '</div>';
		echo '</div>';

	echo '</div>';	
}


/**
 * Render the ticket history metabox
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_history_metabox() {	
	echo tops_get_template_part( 'single-ticket/comments' );
}


/**
 * Update the post meta on save
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_metabox_save( $post_id ) {

	global $post;

	// verify nonce
	if (!isset($_POST['tops_ticket_metabox_nonce']) || !wp_verify_nonce($_POST['tops_ticket_metabox_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) return $post_id;

	// don't save if only a revision
	if ( isset($post->post_type) && $post->post_type == 'revision' ) return $post_id;

	// check permissions
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}	
	
	// Update the general meta
	$agent = isset($_POST['_tops_ticket_agent_id']) ? $_POST['_tops_ticket_agent_id'] : '';
	$user = isset($_POST['_tops_ticket_user_id']) ? $_POST['_tops_ticket_user_id'] : '';
	$license = isset($_POST['_tops_ticket_license']) ? $_POST['_tops_ticket_license'] : '';
	$related_url = isset($_POST['_tops_ticket_related_url']) ? $_POST['_tops_ticket_related_url'] : '';
	$status = isset($_POST['_tops_ticket_status']) ? $_POST['_tops_ticket_status'] : 'open';
	$type = isset($_POST['_tops_ticket_type']) ? $_POST['_tops_ticket_type'] : 'private';
	//$favorite = isset($_POST['_tops_ticket_is_starred']) ? $_POST['_tops_ticket_is_starred'] : 'no';

	// Update the ticket history
	if( isset($_POST['post_content']) && $_POST['post_content'] != '' ) {
		
		$ticket_id = get_post_meta( $post_id, '_tops_ticket_id', true );
		$args = array(
			'id' => $ticket_id,
			'post_id' => $post_id,
			'comment' => $_POST['post_content'],
			'type' => isset($_POST['_tops_ticket_comment_type']) ? esc_attr($_POST['_tops_ticket_comment_type']) : 'public',
			'close' => isset($_POST['_tops_ticket_close_ticket']) ? esc_attr($_POST['_tops_ticket_close_ticket']) : false,
		);
		$comment = TOPS()->tickets->add_comment( $args );

		// Reset the post content
		$reset_post = array(
      'ID' 			=> $post_id,
      'content' => ''
    );   
    remove_action( 'save_post', 'tops_ticket_metabox_save' );
    wp_update_post( $post );
    add_action( 'save_post', 'tops_ticket_metabox_save' );
    
    // Set unread
    $unread = 'no';
	}
	
	update_post_meta( $post_id, '_tops_ticket_agent_id', $agent );
	if( $user != '' ) {
		update_post_meta( $post_id, '_tops_ticket_user_id', $user );
	}
	update_post_meta( $post_id, '_tops_ticket_license', $license );
	update_post_meta( $post_id, '_tops_ticket_related_url', $related_url );
	update_post_meta( $post_id, '_tops_ticket_status', $status );
	update_post_meta( $post_id, '_tops_ticket_type', $type );	
	//update_post_meta( $post_id, '_tops_ticket_is_starred', $favorite );	
}
add_action( 'save_post', 'tops_ticket_metabox_save' );


/**
 * Add the custom tops_ticket meta columns
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_add_edit_columns( $columns ) {

	$updated_columns = array();
	$counter = 0;
	
	if( is_array($columns) && count($columns) > 0 ) {
		foreach( $columns as $i=>$column ) {
			
			if( $counter == 1 ) {
				$updated_columns['unread'] = false;
			}
			$updated_columns[$i] = $column;	
			$counter++;
		}
	}
	
	$updated_columns['last_update'] = __('Last Update', 'total-product-support');
	$updated_columns['status'] = __('Status', 'total-product-support');
	$updated_columns['type'] = __('Type', 'total-product-support');
	$updated_columns['agent'] = __('Agent', 'total-product-support');
	$updated_columns['user'] = __('User', 'total-product-support');
	$updated_columns['comment_count'] = '<i class="fa fa-comment" aria-hidden="true"></i>';
	
	unset($updated_columns['date']);
	
  return $updated_columns;
}
add_filter( 'manage_tops_ticket_posts_columns' , 'tops_ticket_add_edit_columns' );


/**
 * Render the custom tops_ticket meta columns
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_render_edit_columns( $column, $post_id ) {
	
	global $post;
	$ticket = new Tops_Ticket( $post_id, 'post_id' );
	
	switch( $column ) {
		
		case 'unread':	
			
			if( $ticket->status == 'open' && ($ticket->get_last_commenter_id() != $ticket->get_agent_id()) ) {
				echo '<div class="tops-ticket-unread">';
			} else {
				echo '<div class="tops-ticket-read">';
			}
			if( $ticket->comment_count() > 1 ) {
				echo '<i class="fa fa-circle-o" aria-hidden="true"></i>';
			} else {
				echo '<i class="fa fa-circle" aria-hidden="true"></i>';
			}
			echo '</div>';
      break;
		
		case 'last_update':	
			$last_update = $ticket->last_updated_time();
			echo sprintf(__('%s ago'), $last_update);
      break;
	
	  case 'user':	
			$user_id = $ticket->get_user_id();
			echo "<a href='edit.php?post_type={$post->post_type}&user={$user_id}'>".$ticket->get_user_name()."</a>";
      break;
	
	  case 'agent':
	  	$user_id = $ticket->get_agent_id();
      echo "<a href='edit.php?post_type={$post->post_type}&agent={$user_id}'>".$ticket->get_agent_name()."</a>";
      break;
      
    case 'status':
	  	$status = $ticket->status;
	  	echo "<a href='edit.php?post_type={$post->post_type}&status={$status}'>".ucfirst($status)."</a>";
      break;
      
    case 'type':
	  	$type = $ticket->type;
	  	echo "<a href='edit.php?post_type={$post->post_type}&type={$type}'>".ucfirst($type)."</a>";
      break;
      
     case 'comment_count':
	  	echo $ticket->comment_count();
      break;

  }
}
add_action( 'manage_tops_ticket_posts_custom_column' , 'tops_ticket_render_edit_columns', 10, 2 );


/**
 * Add custom screen filters
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_edit_screen_filters() {

	global $typenow;
	
	if( $typenow == 'tops_ticket' ) {
		
		$action_required = isset($_GET['action_required']) ? $_GET['action_required'] : '';
		$agent = isset($_GET['agent']) ? $_GET['agent'] : '';
		$status = isset($_GET['status']) ? $_GET['status'] : '';
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		
/*
		echo '<select name="action_required">';
			echo '<option value="">'.__('Response Status', 'total-product-support').'</option>';
			echo '<option value="yes" '.selected('yes', $status, false).'>'.__('Needs Response', 'total-product-support').'</option>';
			echo '<option value="no" '.selected('no', $status, false).'>'.__('None Required', 'total-product-support').'</option>';
		echo '</select>';
*/
		
		echo '<select name="status">';
			echo '<option value="">'.__('All Status', 'total-product-support').'</option>';
			//echo '<option value="unread" '.selected('unread', $status, false).'>'.__('Unread', 'total-product-support').'</option>';
			echo '<option value="open" '.selected('open', $status, false).'>'.__('Open', 'total-product-support').'</option>';
			echo '<option value="closed" '.selected('closed', $status, false).'>'.__('Closed', 'total-product-support').'</option>';
		echo '</select>';
		
		echo '<select name="type">';
			echo '<option value="">'.__('All Types', 'total-product-support').'</option>';
			echo '<option value="public" '.selected('public', $type, false).'>'.__('Public', 'total-product-support').'</option>';
			echo '<option value="private" '.selected('private', $type, false).'>'.__('Private', 'total-product-support').'</option>';
		echo '</select>';
		
		echo '<select name="agent">';
			echo '<option value="">'.__('All Agents', 'total-product-support').'</option>';
			$args = array(
				'role__in' => array('administrator', 'tops_ticket_manager', 'tops_ticket_agent'),
			);
			$users = get_users( $args );
			if( is_array($users) && count($users) > 0 ) {
				foreach( $users as $i=>$user ) {
					echo '<option value="'.$user->ID.'" '.selected($user->ID, $agent, false).'>'.$user->data->display_name.'</option>';
				}
			}
		echo '</select>';
		
	}
}
add_action( 'restrict_manage_posts', 'tops_ticket_edit_screen_filters' );


/**
 * Filter the list of tickets
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_parse_edit_query( $query ) {
  
  global $pagenow;
  $qv = &$query->query_vars;
  
  if( $pagenow=='edit.php' && $qv['post_type']=='tops_ticket' ) {
  
  	$meta_query = array();
	  
	  if( isset($_GET['agent']) && $_GET['agent'] != '' ) {
	  	$meta_query[] = array(
	  		'key' => '_tops_ticket_agent_id',
				'value' => $_GET['agent'],
	  	);
	  }
	  
	  if( isset($_GET['status']) && $_GET['status'] != '' ) {
		  if( $_GET['status'] == 'unread' ) {
			  //echo '<pre>';print_r(TOPS()->tickets->get_unread_ticket_ids());echo '</pre>';
			  //$qv['post__in'] = TOPS()->tickets->get_unread_ticket_ids();
		  } else {
			  $meta_query[] = array(
		  		'key' => '_tops_ticket_status',
					'value' => $_GET['status'],
		  	);
		  }	
	  }
	  
	  if( isset($_GET['type']) && $_GET['type'] != '' ) {
	  	$meta_query[] = array(
	  		'key' => '_tops_ticket_type',
				'value' => $_GET['type'],
	  	);
	  }
	  
	  if( isset($_GET['user']) && $_GET['user'] != '' ) {
	  	$meta_query[] = array(
	  		'key' => '_tops_ticket_user_id',
				'value' => $_GET['user'],
	  	);
	  }
	  
	  if( count($meta_query) > 0 ) {
		  $qv['meta_query'] = $meta_query;
	  }
  }
}
add_filter( 'parse_query', 'tops_ticket_parse_edit_query' );


/**
 * Add sortable columns
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_sortable_columns( $columns ) {
	
	$columns['last_update'] = 'last_update';
	$columns['status'] = 'status';
	$columns['type'] = 'type';
	$columns['comment_count'] = 'comment_count';
	$columns['user'] = 'user';

	return $columns;
}
add_filter( 'manage_edit-tops_ticket_sortable_columns', 'tops_ticket_sortable_columns' );


/**
 * Set the custom column order
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_column_order_request( $vars ) {
	
	if( isset($vars['orderby']) && 'last_update' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_tops_ticket_last_comment_time',
			'orderby' => 'meta_value_num'
		));
	}
	
	if( isset($vars['orderby']) && 'status' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_tops_ticket_status',
			'orderby' => 'meta_value'
		));
	}
	
	if( isset($vars['orderby']) && 'type' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_tops_ticket_type',
			'orderby' => 'meta_value'
		));
	}
	
	if( isset($vars['orderby']) && 'comment_count' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_tops_ticket_comments',
			'orderby' => 'meta_value'
		));
	}
	
	if( isset($vars['orderby']) && 'user' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_tops_ticket_user_id',
			'orderby' => 'meta_value_num'
		));
	}
	 
	return $vars;
}
add_filter( 'request', 'tops_ticket_column_order_request' );