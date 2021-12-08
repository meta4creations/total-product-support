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
		//'is_mine' => true,
		'status' => 'open',
		'order'		=> 'DESC'
	);
	$read_tickets = TOPS()->tickets->get_tickets( $filters );	
	?>
	
	
	<?php
	/**
	 * Display unread tickets
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	$filters = array(
		//'is_mine' => true,
		//'debug_query' => true
	);
	$tickets = TOPS()->tickets->get_tickets( $filters );
	
	$args = array(
		'tickets' => array_udiff( $tickets, $read_tickets, 'tops_remove_duplicate_posts' ),
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
		'status' => 'open',
	);
	echo tops_get_template_part( 'ticket-list', $args );
	?>
	
</div><!-- .tops-ticket-archive -->