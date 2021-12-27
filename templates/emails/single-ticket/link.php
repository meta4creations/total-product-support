<?php	
$post_id = isset( $post_id ) ? $post_id : get_the_id();	
$ticket = TOPS()->tickets->get_ticket( $post_id, 'post_id' );
$ticket_type_icon = ( $ticket->type == 'private' ) ? '<i class="fa fa-lock" aria-hidden="true"></i>' : '<i class="fa fa-unlock" aria-hidden="true"></i>';
?>

<li <?php echo $ticket->ticket_class('tops-ticket-link'); ?>>
	<a href="<?php echo $ticket->url(); ?>">
		<?php echo $ticket->get_user_avatar(); ?>
		<span class="tops-ticket-link-data">
			<span class="tops-ticket-link-user"><?php echo $ticket->get_user_name(); ?></span>
			<h3 class="tops-ticket-link-subject"><?php echo $ticket_type_icon.$ticket->title(); ?></h3>
			<?php if( $ticket->status == 'open' ) { ?>
				<span class="tops-ticket-link-meta">
					<span class="tops-ticket-link-category">
						<?php echo $ticket->category_thumbnail(); ?>
						<?php echo '<span class="tops-ticket-link-meta-text">'.$ticket->category_name().'</span>'; ?>
					</span>
					<span class="tops-ticket-link-agent">
						<?php echo $ticket->get_agent_avatar(); ?>
						<span class="tops-ticket-link-meta-text"><?php echo $ticket->get_agent_name(); ?></span>
					</span>
					<span class="tops-ticket-link-updated">
						<?php echo '<i class="fa fa-calendar-o" aria-hidden="true"></i>'; ?>
						<?php echo '<span class="tops-ticket-link-meta-text">'.sprintf(__('Updated %s ago', 'total-product-support'), $ticket->last_updated_time()).'</span>'; ?>
					</span>
					<span class="tops-ticket-link-comment-count">
						<?php echo '<i class="fa fa-comment-o" aria-hidden="true"></i>'; ?>
						<?php echo '<span class="tops-ticket-link-meta-text">'.$ticket->comment_count().'</span>'; ?>
					</span>
				</span>
			<?php } ?>
		</span>
		<?php if( $ticket->status == 'open' ) { ?>
			<span class="tops-ticket-link-icons">
				<span class="tops-ticket-link-icons-starred" data-ticket-id="<?php echo $ticket->id; ?>"><i class="fa fa-star<?php if( !$ticket->is_starred() ) { echo '-o'; } ?>" aria-hidden="true"></i></span>
				<span class="tops-ticket-link-icons-status"><i class="fa fa-circle<?php if($ticket->comment_count() > 1) { echo '-o'; } ?>" aria-hidden="true"></i></span>
				<?php if( !$ticket->verified() ) { ?>
					<span class="tops-ticket-link-icons-verified"><i class="fa fa-check-circle" aria-hidden="true"></i></span>
				<?php } ?>
			</span>
		<?php } ?>
	</a>
</li>