<?php
	
/*
function sample_email_sending() {
	
	if( !is_admin() && get_current_user_id() == 1 ) {
		$to = 'jradive@gmail.com';
		$subject = 'this is a test';
		$message = 'this should work';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: Me Myself <joe@metaphorcreations.com>';
		$attachments = array();
		
		$sent = wp_mail( $to, $subject, $message );
		if( $sent ) {
			echo '<pre>';print_r('this thing sent!');echo '</pre>';
		} else {
			echo '<pre>';print_r('NOPE!');echo '</pre>';
		}
	}
}
add_action( 'init', 'sample_email_sending' );
*/

/**
 * Add the ticket content to single posts
 *
 * @since   1.0.0
 */

// function tops_ticket_add_content( $content ) {
// 	
// 	if( get_post_type() == 'tops_ticket' ) {
// 		return tops_get_template_part( 'ticket' );
// 	}
// 	return $content;
// }
// add_filter( 'the_content', 'tops_ticket_add_content' );


/**
 * Set the default WP Editor mode
 *
 * @since   1.0.0
 */

function tops_ticket_set_default_editor() {
	//allowed: tinymce, html, test
	return 'tinymce';
}
add_filter( 'wp_default_editor', 'tops_ticket_set_default_editor' );


/**
 * Set body classes
 *
 * @since   1.0.0
 */
 
function tops_add_body_classes( $class ) {
	
	$classes = (array) $class;

	if( tops_is_ticket() ) {
		$ticket = TOPS()->tickets->get_ticket( get_the_id(), 'post_id' );
		$classes[] = 'tops-ticket-'.$ticket->id;
		$ticket_classes = $ticket->get_ticket_class();
		$classes = array_merge( $classes, $ticket_classes );
		$classes[] = 'tops-page';
	}
	
	if( tops_is_new_ticket_page() ) {
		$classes[] = 'tops-new-ticket';
		$classes[] = 'tops-page';
	}

	if( tops_is_tickets_page() ) {
		$classes[] = 'tops-active-tickets';
		$classes[] = 'tops-page';
	}

	if( tops_is_ticket_archives_page() ) {
		$classes[] = 'tops-ticket-archives';
		$classes[] = 'tops-page';
	}

	if( tops_is_ticket_categories_page() ) {
		$classes[] = 'tops-ticket-category-archives';
		if( isset($_GET['category']) ) {
			$classes[] = 'tops-ticket-category-'.esc_attr($_GET['category']).'-archives';
		}
		$classes[] = 'tops-page';
	}
	
	if( tops_is_tickets_public_page() ) {
		$classes[] = 'tops-ticket-public-archives';
		$classes[] = 'tops-page';
	}
	
	if( tops_is_tickets_private_page() ) {
		$classes[] = 'tops-ticket-private-archives';
		$classes[] = 'tops-page';
	}
	
	if( tops_is_tickets_starred_page() ) {
		$classes[] = 'tops-ticket-starred-archives';
		$classes[] = 'tops-page';
	}

	return array_unique( $classes );
}
add_filter( 'body_class', 'tops_add_body_classes' );


/*
function tops_add_category_var( $vars ) {
	$vars[] = 'film_title';
	return $vars;
}
add_filter( 'query_vars', 'tops_add_category_var', 1 );


function dcc_rewrite_tags() {
	add_rewrite_tag('%film_title%', '([^&]+)');	
}
add_action( 'init', 'dcc_rewrite_tags' );
add_action( 'wp_init', 'dcc_rewrite_tags' );


function dcc_rewrite_rules() {
	add_rewrite_rule('^film_title/(.+)/?$', 'index.php?film_title=$matches[1]', 'bottom');
}
add_action( 'init', 'dcc_rewrite_rules' );


function tops_page_titles( $title_parts ) {

	global $wp_query;
	if( isset($wp_query->query['tps_category']) ) {
		$title_parts['title'] = sprintf(__('%s Tickets', 'total-product-support'), $wp_query->query['tps_category']);
	}
	return $title_parts;
}
add_filter( 'document_title_parts', 'tops_page_titles', 10 );
*/


/**
 * Redirect unauthorized ticket viewers
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_redirect() {
	
	if( is_singular('tops_ticket') ) {
		if( !TOPS()->tickets->user_can_view_ticket() ) {
			wp_redirect( TOPS()->tickets->get_tickets_page_url() );
			exit;
		}
	} elseif( TOPS()->tickets->get_tickets_page_id('private') == get_queried_object_id() ) {
		if( !TOPS()->tickets->global_agent_access() ) {
			wp_redirect( TOPS()->tickets->get_tickets_page_url() );
			exit;
		}
	}
}
add_action( 'wp', 'tops_ticket_redirect' );


/**
 * Add a tickets dropdown to the menu
 *
 * @access  public
 * @since   1.0.0
 */
 
function tops_add_tickets_menu_item( $items, $args ) {
	
	if( $args->theme_location == 'primary' || $args->theme_location == 'top' ) {
		
		$tickets = '';

		$unread_count = TOPS()->tickets->get_unread_ticket_counts();
		$unread_notification = ( $unread_count > 0 ) ? '<span class="tops-unread-notification">'.$unread_count.'</span>' : '<i class="fa fa-fw fa-tags" aria-hidden="true"></i>';
			
		if( !current_user_can('edit_tops_tickets') ) {
			
			$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-tickets">';
				$tickets .= '<a href="'.TOPS()->tickets->get_tickets_page_url('create').'"><i class="fa fa-fw fa-tag" aria-hidden="true"></i>'.__('Submit a Ticket', 'total-product-support').'</a>';
			$tickets .= '</li>';
		}

		$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-tickets menu-item-has-children">';
			$tickets .= '<a href="'.TOPS()->tickets->get_tickets_page_url().'">'.$unread_notification.__('Tickets', 'total-product-support').'</a>';
			$tickets .= '<ul class="sub-menu">';
			
				if( current_user_can('edit_tops_tickets') ) {
					
					$open_count = TOPS()->tickets->get_ticket_counts( array('status'=>'open') );
					$closed_count = TOPS()->tickets->get_ticket_counts( array('status'=>'closed') );
					$starred_count = TOPS()->tickets->get_ticket_counts( array('is_starred'=>'yes') );	

					$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-open-tickets"><a href="'.TOPS()->tickets->get_tickets_page_url().'">'.sprintf(_n('<strong>%d</strong> Open Ticket', '<strong>%d</strong> Open Tickets', $open_count, 'total-product-support'), $open_count).$unread_notification.'</a></li>';
					
					$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-closed-tickets"><a href="'.TOPS()->tickets->get_tickets_page_url('archive').'">'.sprintf(_n('<strong>%d</strong> Closed Ticket', '<strong>%d</strong> Closed Tickets', $closed_count, 'total-product-support'), $closed_count).'</a></li>';
					
					$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-starred-tickets"><a href="'.TOPS()->tickets->get_tickets_page_url('starred').'">'.sprintf(_n('<strong>%d</strong> Starred Ticket', '<strong>%d</strong> Starred Tickets', $starred_count, 'total-product-support'), $starred_count).' <i class="fa fa-fw fa-star" aria-hidden="true"></i></a></li>';
				
				} else {
					
					if( is_user_logged_in() ) {
						
						$starred_count = TOPS()->tickets->get_ticket_counts( array('is_starred'=>'yes') );

						$unread_notification = ( $unread_count > 0 ) ? '<span class="tops-unread-notification">'.$unread_count.'</span>' : '<i class="fa fa-fw fa-user" aria-hidden="true"></i>';
						$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-public-tickets"><a href="'.TOPS()->tickets->get_tickets_page_url().'">'.$unread_notification.__('My Tickets', 'total-product-support').'</a></li>';
						
						$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-starred-tickets"><a href="'.TOPS()->tickets->get_tickets_page_url('starred').'">'.sprintf(_n('<strong>%d</strong> Starred Ticket', '<strong>%d</strong> Starred Tickets', $starred_count, 'total-product-support'), $starred_count).' <i class="fa fa-fw fa-star" aria-hidden="true"></i></a></li>';
					}
	
				}
				
				$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-public-tickets"><a href="'.TOPS()->tickets->get_tickets_page_url('public').'"><i class="fa fa-fw fa-unlock" aria-hidden="true"></i>'.__('Public Tickets', 'total-product-support').'</a></li>';
				
				// If global agent access is allowed, show all private tickets
				if( current_user_can('edit_tops_tickets') && TOPS()->tickets->global_agent_access() ) {
					$tickets .= '<li class="menu-item menu-item-type-tops menu-item-type-tops-private-tickets"><a href="'.TOPS()->tickets->get_tickets_page_url('private').'"><i class="fa fa-fw fa-lock" aria-hidden="true"></i>'.__('Private Tickets', 'total-product-support').'</a></li>';
				}
				
			$tickets .= '</ul>';
		$tickets .= '</li>';
		
		$items = $tickets.$items;
	}
	
	return $items;
}
//add_filter( 'wp_nav_menu_items', 'tops_add_tickets_menu_item', 10, 2 );


/**
 * Remove the ticket info widget if not on a ticket page
 *
 * @access  public
 * @since   1.0.0
 */

function tops_remove_widgets( $params ) {
	if( ! is_admin() && get_post_type() != 'tops_ticket' ) {
		$i = count($params);
		while( $i --> 0 ) {
			if( isset($params[$i]['widget_id']) && (strpos($params[$i]['widget_id'], 'tops-ticket-details') !== false) ) {
				unset( $params[$i] );
			}
		}
	}
	return $params;
}
add_filter( 'dynamic_sidebar_params', 'tops_remove_widgets' );


/**
 * Add button to generate missing product categories
 *
 * @access  public
 * @since   1.0.0
 */
function tops_add_category_generate_button( $taxonomy ) {
	if( $taxonomy == 'tops_category' ) {
		?>
		<form method="post" action="edit-tags.php" id="tops-generate-edd-categories">
			<input type="hidden" name="taxonomy" value="tops_category" />
			<input type="hidden" name="post_type" value="tops_ticket" />
			<input type="hidden" name="generate_edd_categories" value="yes" />
		</form>
		<button type="submit" class="button button-primary" form="tops-generate-edd-categories" value="true"><?php _e('Generate missing product categories', 'total-product-support'); ?></button>
		<?php
	}
}
//add_action( 'tops_category_pre_add_form', 'tops_add_category_generate_button' );


function tops_generate_missing_edd_categories( $taxonomy ) {
	$taxonomy = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : false;
	$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : false;
	$generate = isset($_GET['generate_edd_categories']) ? $_GET['generate_edd_categories'] : '';
	
	if( $taxonomy == 'tops_category' && $post_type == 'tops_ticket' && $generate == 'yes' ) {
		
		// Get a default agent
		$default_agent = 0;
		$args = array(
			'role__in' => array('administrator', 'tops_ticket_manager', 'tops_ticket_agent'),
		);
		$users = get_users( $args );
		if( is_array($users) && count($users) > 0 ) {
			$default_agent = $users[0]->ID;
		}
		
		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'download',
		);
		$downloads = get_posts( $args );
		if( is_array($downloads) && count($downloads) > 0 ) {
			foreach( $downloads as $i=>$download ) {
				if( !term_exists( $download->post_title, 'tops_category' ) ) {
					$term_id = wp_insert_term( $download->post_title, 'tops_category' );
					if( $term_id && !is_wp_error( $term_id ) ) {
						update_term_meta( $term_id, 'tops_default_ticket_agent_id', $default_agent );
						update_term_meta( $term_id, 'tops_term_product_type', 'easy-digital-downloads' );
						update_term_meta( $term_id, 'tops_term_product_id', $download->ID );
					}
				}
			}
		}
	}
}
//add_action( 'admin_init', 'tops_generate_missing_edd_categories' );





function tops_toolbar_items( $admin_bar ) {
	
	$unread_tickets = TOPS()->tickets->get_unread_tickets();
	$unread_count = count($unread_tickets);
	$unread_notification = ( $unread_count > 0 ) ? '<span class="tops-unread-notification">'.$unread_count.'</span>' : '<i class="fa fa-fw fa-tags" aria-hidden="true"></i>';
	$tickets_url = is_admin() ? admin_url( 'edit.php?post_type=tops_ticket' ) : TOPS()->tickets->get_tickets_page_url();
	$archive_url = is_admin() ? admin_url( 'edit.php?post_type=tops_ticket' ) : TOPS()->tickets->get_tickets_page_url('archive');
	
	$starred_count = TOPS()->tickets->get_ticket_counts( array('is_starred'=>'yes') );
	$starred_notification = ( $starred_count > 0 ) ? '<span class="tops-unread-notification">'.$starred_count.'</span>' : '';
	$starred_url = is_admin() ? admin_url( 'edit.php?post_type=tops_ticket' ) : TOPS()->tickets->get_tickets_page_url('starred');
	
	$admin_bar->add_menu( array(
    'id'    => 'tops-tickets',
    'title' => sprintf(__('Tickets %s', 'total-product-support'), $unread_notification),
    'href'  => $tickets_url,
    'meta'  => array(
      'title' => sprintf(__('Tickets %s', 'total-product-support'), $unread_notification),            
    ),
	));
	$admin_bar->add_menu( array(
	  'id'    => 'tops-ticket-archives',
	  'parent' => 'tops-tickets',
	  'title' => __('Closed Tickets', 'total-product-support'),
	  'href'  => $archive_url,
	  'meta'  => array(
	    'title' => __('Closed Tickets', 'total-product-support'),
	  ),
	));
	$admin_bar->add_menu( array(
    'id'    => 'tops-ticket-starred',
    'parent' => 'tops-tickets',
    'title' => sprintf(__('Starred Tickets %s', 'total-product-support'), $starred_notification),
    'href'  => $starred_url,
    'meta'  => array(
      'title' => sprintf(__('Starred Tickets %s', 'total-product-support'), $starred_notification),
    ),
	));
	if( is_array($unread_tickets) && count($unread_tickets) > 0 ) {
		foreach( $unread_tickets as $i=>$unread_ticket ) {
			
			$unread_ticket_url = is_admin() ? get_edit_post_link( $unread_ticket->ID ) : get_permalink( $unread_ticket->ID );
			
			$admin_bar->add_menu( array(
		    'id'    => 'tops-ticket-'.$unread_ticket->ID,
		    'parent' => 'tops-tickets',
		    'title' => $unread_ticket->post_title,
		    'href'  => $unread_ticket_url,
		    'meta'  => array(
		      'title' => $unread_ticket->post_title,
		    ),
			));
		}
	}
}
add_action( 'admin_bar_menu', 'tops_toolbar_items', 100 );