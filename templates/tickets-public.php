<div class="tops-ticket-archive">
	
	<h3 class="tops-ticket-archive-heading"><?php _e('Publics Tickets', 'total-product-support'); ?></h3>

	<?php
	$filters = array(
		'type' => 'public',
		//'debug_query' => true
	);
	$tickets = TOPS()->tickets->get_tickets( $filters );
	if( is_array($tickets) && count($tickets) > 0 ) {
		echo '<ul class="tops-ticket-archive-list">';
		foreach( $tickets as $obj ) {
			echo tops_get_template_part( 'single-ticket/link', array('post_id'=>$obj->ID) );
		}
		echo '</ul>';
	}
	?>
	
</div><!-- .tops-ticket-archive -->