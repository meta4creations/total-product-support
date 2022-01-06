<?php
$ticket = isset($ticket) ? $ticket : TOPS()->tickets->get_ticket( get_the_id(), 'post_id' );
$ticket_type_icon = ( $ticket->type == 'private' ) ? '<i class="fa fa-lock" aria-hidden="true"></i>' : '<i class="fa fa-unlock" aria-hidden="true"></i>';
?>

<div id="tops-ticket-type-heading">
	<?php
	if( $ticket->type == 'public' ) {
		printf( __('%s Public Ticket #%s', 'total-product-support'), $ticket_type_icon, $ticket->id);
		echo '<a href="#" id="tops-ticket-private-toggle" data-ticket-id="'.$ticket->id.'"><i class="fa fa-lock" aria-hidden="true"></i> '.__('Make Private', 'total-product-support').'</a>';
	} else {
		printf( __('%s Private Ticket #%s', 'total-product-support'), $ticket_type_icon, $ticket->id); 
	}
	?>
</div>

<div id="tops-ticket-title-bar">
	<h2 id="tops-ticket-title"><?php echo $ticket->title(); ?></h2>
	<?php //if( current_user_can('edit_tops_tickets') ) { ?>
		<div id="tops-ticket-response-toggle">
			<?php if( $ticket->is_unread() ) {
				echo '<a href="#" title="'.tops_string('needs_response').'" data-ticket-id="'.$ticket->id.'"><i class="fa fa-circle" aria-hidden="true"></i> '.tops_string('needs_response').'</a>';
			} else {
				echo '<a href="#" title="'.tops_string('mark_as_unread').'" data-ticket-id="'.$ticket->id.'"><i class="fa fa-circle-o" aria-hidden="true"></i> '.tops_string('mark_as_unread').'</a>';
			} ?>
		</div>
		<div id="tops-ticket-starred-toggle">
			<?php if( $ticket->is_starred() ) {
				echo '<a href="#" data-ticket-id="'.$ticket->id.'"><i class="fa fa-star" aria-hidden="true"></i></a>';
			} else {
				echo '<a href="#" data-ticket-id="'.$ticket->id.'"><i class="fa fa-star-o" aria-hidden="true"></i></a>';
			} ?>
		</div>
	<?php //} ?>
</div>