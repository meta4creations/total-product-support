<div class="tops-ticket-archive">
	
	<h3 class="tops-ticket-archive-heading"><?php _e('Closed Tickets', 'total-product-support'); ?></h3>

	<?php
	$filters = array(
		'status' => 'closed',
		'order'		=> 'DESC'
	);
	$tickets = TOPS()->tickets->get_tickets( $filters );
	if( is_array($tickets) && count($tickets) > 0 ) {
		echo '<h4 class="tops-ticket-archive-list-heading">'.__('No Action Needed', 'total-product-support').'</h4>';
		echo '<ul class="tops-ticket-archive-list">';
		foreach( $tickets as $obj ) {
			echo tops_get_template_part( 'single-ticket/link', array('post_id'=>$obj->ID) );
		}
		echo '</ul>';
	}
	?>
	
</div><!-- .tops-ticket-archive -->