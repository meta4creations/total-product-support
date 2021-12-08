<?php $ticket = TOPS()->tickets->get_ticket( get_the_id(), 'post_id' ); ?>

<div id="tops-ticket-<?php echo $ticket->id; ?>" class="tops-ticket-container">
	<?php
	echo tops_get_template_part( 'single-ticket/heading', array('ticket'=>$ticket) );
	echo '<div id="tops-ticket-new-comment-container">';
		echo tops_get_template_part( 'single-ticket/action-bar', array('ticket'=>$ticket) );
		echo tops_get_template_part( 'single-ticket/reply', array('ticket'=>$ticket) );
	echo '</div>';
	echo tops_get_template_part( 'single-ticket/comments', array('ticket'=>$ticket) );
	?>
</div>