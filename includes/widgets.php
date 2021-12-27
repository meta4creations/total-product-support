<?php

/**
 * Show ticket categories
 *
 * @since   1.0.0
 * @return  void
 */
class tops_ticket_categories_widget extends WP_Widget {

	/** Constructor */
	function __construct() {
		parent::__construct(
			'tops-ticket-categories-widget',
			__('TOPS Ticket Categories', 'total-product-support'),
			array(
				'classname' => 'tops-ticket-categories-widget',
				'description' => __('Displays ticket counts for each category.', 'total-product-support')
			)
		);
	}
	
	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		
		extract( $args );
		
		// User-selected settings	
		$title = $instance['title'];
		$title = apply_filters( 'widget_title', $title );
		
		$html = '';
		$args = array(
			'taxonomy' => 'tops_category',
			'post_type' => 'tops_ticket'
		);
		$categories = get_terms( $args );
		if( is_array($categories) && count($categories) > 0 ) {
			$html .= '<ul>';
			foreach( $categories as $i=>$category ) {
				$count = TOPS()->tickets->get_ticket_counts( array('status'=>'open','category'=>$category->slug) );
				if( $count > 0 ) {
					$count_notification = ($count > 0) ? '<span class="tops-count-notification">'.$count.'</span>' : '';
					$unread_count = TOPS()->tickets->get_unread_ticket_counts( array('category'=>$category->slug, 'is_unread'=>get_current_user_id()) );
					$unread_notification = ($unread_count > 0) ? '<span class="tops-unread-notification">'.$unread_count.'</span>' : '';
					$html .= '<li><a href="'.TOPS()->tickets->get_tickets_page_url('', array('category'=>$category->slug)).'"><span class="tops-ticket-categories-icon">'.TOPS()->categories->thumbnail( $category->term_id ).'</span>'.$category->name.'<span class="tops-ticket-category-notifications">'.$unread_notification.$count_notification.'</span></a></li>';
				}
			}
			$html .= '</ul>';
		}

		if( $html != '<ul></ul>' ) {
			
			// Before widget (defined by themes)
			echo $before_widget;
			
			// Title of widget (before and after defined by themes)
			if( $title ) {
				echo $before_title . $title . $after_title;
			}
			
			// After widget (defined by themes)
			echo $after_widget;
		}
	}
	
	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		// Strip tags (if needed) and update the widget settings
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		
		return $instance;
	}
	
	/** @see WP_Widget::form */
	function form( $instance ) {
		
		// Set up some default widget settings
		$defaults = array(
			'title' => '',
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:', 'total-product-support' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:97%;" />
		</p>
		
		<?php
	}
}


/**
 * Show ticket details
 *
 * @since   1.0.0
 * @return  void
 */
class tops_ticket_details_widget extends WP_Widget {

	/** Constructor */
	function __construct() {
		parent::__construct(
			'tops-ticket-details-widget',
			__('TOPS Ticket Details', 'total-product-support'),
			array(
				'classname' => 'tops-ticket-details-widget',
				'description' => __('Displays information about the current ticket.', 'total-product-support')
			)
		);
	}
	
	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		
		extract( $args );
		
		// User-selected settings	
		$title = $instance['title'];
		$title = apply_filters( 'widget_title', $title );

		// Before widget (defined by themes)
		echo $before_widget;
		
		// Title of widget (before and after defined by themes)
		if( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		$ticket = TOPS()->tickets->get_ticket( get_the_id(), 'post_id' );
		$full_access = ( get_current_user_id() == $ticket->get_agent_id() || get_current_user_id() == $ticket->get_user_id() ) ? true : false;
		
		if( $ticket ) {
			
			echo '<div class="tops-ticket-details-actions">';
			
				echo '<p>'.__('Status', 'total-product-support').': '.$ticket->status_label().'</p>';
				echo '<p>'.__('Category', 'total-product-support').': <a class="tops-ticket-details-category" href="'.$ticket->category_url().'"><span class="tops-ticket-details-icon">'.$ticket->category_thumbnail().'</span>'.$ticket->category_name().'</span></a></p>';
			
			echo '</div>';
			
			echo '<dl class="tops-ticket-details-list">';
				
				if( $full_access ) {
					echo '<dt class="tops-ticket-details-customer">'.__('Customer', 'total-product-support').'</dt>';
					echo '<dd class="tops-ticket-details-customer"><span class="tops-ticket-details-icon">'.$ticket->get_user_avatar().'</span> '.$ticket->get_user_link().'</dd>';
					
					echo '<dt class="tops-ticket-details-contact">'.__('Contact', 'total-product-support').'</dt>';
					echo '<dd class="tops-ticket-details-contact"><span class="tops-ticket-details-icon"><i class="fa fa-fw fa-envelope-o" aria-hidden="true"></i></span> '.$ticket->get_user_email_link().'</dd>';
					
					if( $ticket->related_url ) {
						echo '<dt class="tops-ticket-details-contact">'.__('Related', 'total-product-support').'</dt>';
						echo '<dd class="tops-ticket-details-contact"><span class="tops-ticket-details-icon"><i class="fa fa-fw fa-link" aria-hidden="true"></i></span> '.$ticket->related_url_link().'</dd>';
					}
					
					echo '<dt class="tops-ticket-details-category">'.__('Category', 'total-product-support').'</dt>';
					echo '<dd class="tops-ticket-details-category"><span class="tops-ticket-details-icon">'.$ticket->category_thumbnail().'</span>'.$ticket->category_link().'</dd>';
					
					echo '<dt class="tops-ticket-details-agent">'.__('Assigned', 'total-product-support').'</dt>';
					echo '<dd class="tops-ticket-details-agent"><span class="tops-ticket-details-icon">'.$ticket->get_agent_avatar().'</span> '.$ticket->get_agent_link().'</dd>';
				}
				
				echo '<dt class="tops-ticket-details-created">'.__('Created', 'total-product-support').'</dt>';
				echo '<dd class="tops-ticket-details-created"><span class="tops-ticket-details-icon"><i class="fa fa-fw fa-calendar-o" aria-hidden="true"></i></span> '.$ticket->created_date().'</dd>';
				
				//echo '<dt class="tops-ticket-details-response">'.__('Response', 'total-product-support').'</dt>';
				//echo '<dd class="tops-ticket-details-response"><span class="tops-ticket-details-icon"><i class="fa fa-fw fa-clock-o" aria-hidden="true"></i></span> '.$ticket->response_time().'</dd>';
				
			echo '</dl>';
			
			//echo '<pre>';print_r($ticket);echo '</pre>';
		}

		// After widget (defined by themes)
		echo $after_widget;
	}
	
	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		// Strip tags (if needed) and update the widget settings
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		
		return $instance;
	}
	
	/** @see WP_Widget::form */
	function form( $instance ) {
		
		// Set up some default widget settings
		$defaults = array(
			'title' => '',
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:', 'total-product-support' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:97%;" />
		</p>
		
		<?php
	}
}

/**
 * Show article nav
 *
 * @since   1.0.0
 * @return  void
 */
class tops_article_nav_widget extends WP_Widget {

	/** Constructor */
	function __construct() {
		parent::__construct(
			'tops-article-nav-widget',
			__('TOPS Article Navigation', 'total-product-support'),
			array(
				'classname' => 'tops-article-nav-widget',
				'description' => __('Displays navigation for the current article.', 'total-product-support')
			)
		);
	}
	
	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		
		extract( $args );
		
		// Before widget (defined by themes)
		echo $before_widget;

		if ( is_singular( 'tops_article' ) ) {
			$terms = get_the_terms( get_queried_object_id(), 'tops_category' );
			if ( $terms ) {
				$parent_term = false;
				if ( is_array( $terms ) && count( $terms ) > 0 ) {
					foreach ( $terms as $i => $term ) {
						if ( 0 != $term->parent ) {
							$parent_term = $term;
							break;
						}
					}
				}
				if ( ! $parent_term ) {
					$parent_term = reset( $terms );
				}
				echo $before_title;
					echo '<a href="' . get_term_link( $parent_term ) . '">' . $parent_term->name . '</a>';
				echo $after_title;

				$post_args = array(
					'posts_per_page'  => -1,
					'orderby'         => 'title',
					'order'           => 'ASC',
					'post_type'       => 'tops_article',
					'tax_query'       => array(
						array(
							'taxonomy' => 'tops_category',
							'field'    => 'term_id',
							'terms'    => $parent_term->term_id,
						),
					),
				);
				$tops_article_query = new WP_Query( $post_args );
				if ( $tops_article_query->have_posts() ) :
					echo '<ul class="tops-article-nav">';
					while ( $tops_article_query->have_posts() ) : $tops_article_query->the_post();
						$active = ( get_queried_object_id() == get_the_id() ) ? ' tops-article-nav-item--active' : '';
						echo '<li class="tops-article-nav-item' . $active . '"><a href="' . get_permalink() . '" title="' . sprintf( __( 'Link to ', 'total-product-support' ), get_the_title() ) . '"><i class="fal fa-file-alt"></i> <span>' . get_the_title() . '</span></a></li>'; 
					endwhile;
					echo '</ul>';
					wp_reset_postdata();
				else :
				endif;
			}
		} elseif( is_tax( 'tops_category' ) ) {
			
			$current_term = get_term( get_queried_object_id(), 'tops_category' );
			if ( 0 == $current_term->parent ) {
				$parent_term = $current_term;
			} else {
				$parent_term = get_term( $current_term->parent, 'tops_category' );
			}
			
			echo $before_title;
				echo '<a href="' . get_term_link( $parent_term ) . '">' . $parent_term->name . '</a>';
			echo $after_title;
			
			$terms = get_terms( array(
				'taxonomy' => 'tops_category',
				'child_of' => $parent_term->term_id,
			) );
			if ( is_array( $terms ) && count( $terms ) > 0 ) {
				echo '<ul class="tops-article-nav">';
				foreach ( $terms as $i => $term ) {
					$active = ( $term->term_id == $current_term->term_id ) ? ' tops-article-nav-item--active' : '';
					echo '<li class="tops-article-nav-item' . $active . '"><a href="' . get_term_link( $term ) . '" title="' . sprintf( __( 'Link to ', 'total-product-support' ), $term->name ) . '"><span>' . $term->name . '</span></a></li>'; 
				}
				echo '</ul>';
			}		
		}

		// After widget (defined by themes)
		echo $after_widget;
	}
}


/**
 * Registers the TOPS Widgets
 *
 * @since 1.0.0
 * @return void
 */
function tops_register_widgets() {
	register_widget( 'tops_ticket_categories_widget' );
	register_widget( 'tops_ticket_details_widget' );
	register_widget( 'tops_article_nav_widget' );
}
add_action( 'widgets_init', 'tops_register_widgets' );
