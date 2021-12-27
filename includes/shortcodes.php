<?php
	
	
/**
 * The TOPS login shortcode
 *
 * @access  public
 * @since   1.0.0
 */

function tops_login_form_display( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'id' => '',
		'class' => ''
	), $atts ) );
	
	$html = '';
	
	if( is_user_logged_in() ) {
		$html .= '<p>'.__('You are already logged in!', 'total-product-support').'</p>';
	} else {
		$html .= '<div id="tops-login-form" class="tops-form-container">';
			$args = array( 'echo' => false );
			$html .= wp_login_form( $args );
		$html .= '</div>';
	}
	
	return $html;
}
add_shortcode( 'tops_login_form', 'tops_login_form_display' );


/**
 * TOPS tickets list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_tickets_display( $atts, $content = null ) {
	
	$category = isset($_GET['category']) ? esc_attr($_GET['category']) : false;
	
	if( $category ) {
		return tops_get_template_part( 'tickets-category', array('category'=>$category) );
	} elseif( current_user_can('edit_tops_tickets') ) {
		return tops_get_template_part( 'tickets' );
	} else {
		return tops_get_template_part( 'tickets-customer' );
	}
}
add_shortcode( 'tops_tickets', 'tops_tickets_display' );


/**
 * TOPS tickets archive list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_archive_display() {
	return tops_get_template_part( 'tickets-archive' );
}
add_shortcode( 'tops_tickets_archive', 'tops_ticket_archive_display' );


/**
 * TOPS tickets starred list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_starred_display() {
	return tops_get_template_part( 'tickets-starred' );
}
add_shortcode( 'tops_tickets_starred', 'tops_ticket_starred_display' );


/**
 * TOPS tickets public list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_public_display() {
	return tops_get_template_part( 'tickets-public' );
}
add_shortcode( 'tops_tickets_public', 'tops_ticket_public_display' );


/**
 * TOPS tickets private list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_ticket_private_display() {
	return tops_get_template_part( 'tickets-private' );
}
add_shortcode( 'tops_tickets_private', 'tops_ticket_private_display' );


/**
 * TOPS tickets category list
 *
 * @access  public
 * @since   1.0.0
 */

function tops_tickets_category_display() {
	return tops_get_template_part( 'tickets-category' );
}
add_shortcode( 'tops_tickets_category', 'tops_tickets_category_display' );


/**
 * Create a new ticket shortcode
 *
 * @access  public
 * @since   1.0.0
 */

function tops_new_ticket_form_display( $atts, $content = null ) {
	return tops_get_template_part( 'create-ticket' );
}
add_shortcode( 'tops_new_ticket_form', 'tops_new_ticket_form_display' );

/**
 * Ticket content display
 *
 * @access  public
 * @since   1.0.0
 */
function tops_ticket_content_display( $atts, $content = null ) {
  return tops_get_template_part( 'ticket' );
}
add_shortcode( 'tops_ticket_content', 'tops_ticket_content_display' );

/**
 * Display article categories
 *
 * @access  public
 * @since   1.0.0
 */
function tops_article_categories_grid_display( $atts, $content = null ) {
  $defaults = array(
    'parent_category' => get_queried_object_id(),
    'post_limit'      => 5,
    'post_orderby'    => 'title',
    'post_order'      => 'ASC',
  );
  $args = shortcode_atts( $defaults, $atts );
  $terms = get_terms( array(
    'taxonomy' => 'tops_category',
    'child_of' => $args['parent_category'],
  ) );
  $html = '';
  if ( is_array( $terms ) && count( $terms ) > 0 ) {
    $html .= '<div class="tops-article-categories">';
    foreach ( $terms as $term ) {
      $html .= '<div class="tops-article-category">';
        $html .= '<h3 class="tops-article-category__name"><a href="' . get_term_link( $term ) . '">' . $term->name . '</a></h3>';
        $html .= '<ul class="tops-category-articles">';
          $post_args = array(
            'posts_per_page'  => -1,
            'orderby'         => $args['post_orderby'],
            'order'           => $args['post_order'],
            'post_type'       => 'tops_article',
            'tax_query'       => array(
              array(
                'taxonomy' => 'tops_category',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
              ),
            ),
          );
          $tops_article_query = new WP_Query( $post_args );
          if ( $tops_article_query->have_posts() ) :
            $counter = 0;
            while ( $tops_article_query->have_posts() ) : $tops_article_query->the_post();	
              $html .= '<li class="tops-category-article"><a href="' . get_permalink() . '" title="' . sprintf( __( 'Link to ', 'total-product-support' ), get_the_title() ) . '"><i class="fal fa-file-alt"></i> <span>' . get_the_title() . '</span></a></li>'; 
              $counter++;
              if ( $counter >= $args['post_limit'] ) {
                break;
              }
            endwhile;
            $html .= '<li class="tops-article-category__view-all"><a href="' . get_term_link( $term ) . '" title="' . sprintf( __( 'Link to all %d articles', 'total-product-support' ), $tops_article_query->post_count ) . '"><span>' . sprintf( __( 'See all %d articles', 'total-product-support' ), $tops_article_query->post_count ) . '</span></a></li>';
            wp_reset_postdata();
          else :
          endif;        
        $html .= '</ul>';
      $html .= '</div>';
    }
    $html .= '</div>';
  }
  return $html;
}
add_shortcode( 'tops_article_categories_grid', 'tops_article_categories_grid_display' );

/**
 * Display article categories
 *
 * @access  public
 * @since   1.0.0
 */
function tops_article_category_post_list_display( $atts, $content = null ) {
  $defaults = array(
    'post_orderby'    => 'title',
    'post_order'      => 'ASC',
  );
  $args = shortcode_atts( $defaults, $atts );
  
  $html = '';
  $post_args = array(
    'posts_per_page'  => -1,
    'orderby'         => $args['post_orderby'],
    'order'           => $args['post_order'],
    'post_type'       => 'tops_article',
    'tax_query'       => array(
      array(
        'taxonomy' => 'tops_category',
        'field'    => 'term_id',
        'terms'    => get_queried_object()->term_id,
      ),
    ),
  );
  $tops_article_query = new WP_Query( $post_args );
  if ( $tops_article_query->have_posts() ) :
    $html .= '<ul class="tops-category-articles">';
    while ( $tops_article_query->have_posts() ) : $tops_article_query->the_post();	
      $html .= '<li class="tops-category-article"><a href="' . get_permalink() . '" title="' . sprintf( __( 'Link to ', 'total-product-support' ), get_the_title() ) . '"><i class="fal fa-file-alt"></i> <span>' . get_the_title() . '</span></a></li>'; 
    endwhile;
    $html .= '</ul>';
    wp_reset_postdata();
  else :
  endif;

  return $html;
}
add_shortcode( 'tops_article_category_post_list', 'tops_article_category_post_list_display' );