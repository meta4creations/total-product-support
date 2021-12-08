<div class="tops-ticket-archive">

	<h3 class="tops-ticket-archive-heading"><?php _e('Your Tickets', 'total-product-support'); ?></h3>
	
	<?php
	/**
	 * Query all the read tickets first
	 * This needs to be queried first in order to filter read tickets from the rest of the queries
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	$filters = array(
		'is_read' => true,
		'status' => 'open',
		'order'		=> 'DESC',
		//'debug_query' => true
	);
	$read_tickets = TOPS()->tickets->get_tickets( $filters );	
	?>


	<?php
	/**
	 * Display unread tickets order than 24 hours
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	$one_day_ago = current_time('timestamp', 1) - DAY_IN_SECONDS;
	$one_hour_ago = current_time('timestamp', 1) - HOUR_IN_SECONDS;
	$filters = array(
		'status' => 'open',
		'before' => $one_day_ago,
		//'debug_query' => true
	);
	$tickets = TOPS()->tickets->get_tickets( $filters );	

	$args = array(
		'tickets' => array_udiff( $tickets, $read_tickets, 'tops_remove_duplicate_posts' ),
		'heading' => __('Needs Response', 'total-product-support'),
		'class' => 'tops-ticket-archive-list-unread'
	);
	echo tops_get_template_part( 'ticket-list', $args );
	?>
	
	
	<?php
	/**
	 * Display unread tickets newer than 24 hours, but older than an hour
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	$one_day_ago = current_time('timestamp', 1) - DAY_IN_SECONDS;
	$one_hour_ago = current_time('timestamp', 1) - HOUR_IN_SECONDS;
	$filters = array(
		'status' => 'open',
		'after' => $one_day_ago,
		'before' => $one_hour_ago,
		//'debug_query' => true
	);
	$tickets = TOPS()->tickets->get_tickets( $filters );

	$args = array(
		'tickets' => array_udiff( $tickets, $read_tickets, 'tops_remove_duplicate_posts' ),
		'heading' => __('Updated today', 'total-product-support'),
		'class' => 'tops-ticket-archive-list-unread'
	);
	echo tops_get_template_part( 'ticket-list', $args );
	?>
	
	
	<?php
	/**
	 * Query unread tickets newer than one hour
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	$one_hour_ago = current_time('timestamp', 1) - HOUR_IN_SECONDS;
	$filters = array(
		'status' => 'open',
		'after' => $one_hour_ago,
		//'debug_query' => true
	);
	$tickets = TOPS()->tickets->get_tickets( $filters );

	$args = array(
		'tickets' => array_udiff( $tickets, $read_tickets, 'tops_remove_duplicate_posts' ),
		'heading' => __('Updated within the hour', 'total-product-support'),
		'class' => 'tops-ticket-archive-list-unread'
	);
	echo tops_get_template_part( 'ticket-list', $args );
	?>
	
	
	<?php
	/**
	 * Display all read, open tickets
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	$args = array(
		'tickets' => $read_tickets,
		'heading' => __('No Action Needed', 'total-product-support'),
	);
	echo tops_get_template_part( 'ticket-list', $args );
	?>
	
</div><!-- .tops-ticket-archive -->