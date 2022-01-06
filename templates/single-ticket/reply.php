<?php $ticket = isset( $ticket ) ? $ticket : TOPS()->tickets->get_ticket( get_the_id(), 'post_id' ); ?>

<div id="tops-ticket-comment-reply-container">

	<form id="tops-ticket-comment-reply-form">
		<?php
		$content = '';
		$editor_id = 'topsticketcomment';
		$settings = apply_filters( 'tops_ticket_comment_editor_settings', array(
			'textarea_rows' => 8,
			'drag_drop_upload' => true,
			'wpautop' => true,
			'quicktags' => array('buttons'=>',')
		));
		wp_editor( $content, $editor_id, $settings );
		?>
		<p id="tops-ticket-attachments" class="tops-ticket-comment-attachments">
			<strong><?php _e('Attached files:'); ?></strong>
		</p>	
		<div id="tops-ticket-new-comment-actions" class="tops-ticket-comment-actions">
			<div id="tops-ticket-new-comment-buttons" class="tops-ticket-comment-buttons">
				<button type="submit" id="tops-ticket-post-new-comment" class="tops-ticket-comment-update" form="tops-ticket-comment-reply-form" value="<?php echo tops_string('post_reply'); ?>" data-ticket-id="<?php echo $ticket->id; ?>"><?php echo tops_string('post_reply'); ?></button>
				<a href="#" id="tops-ticket-add-attachment" class="tops-ticket-comment-attachments" data-ticket-id="<?php echo $ticket->id; ?>"><i class="fa fa-paperclip" aria-hidden="true"></i> <?php _e('Add Attachment', 'total-product-support'); ?></a>
			</div>
			<div id="tops-ticket-new-comment-toggles" class="tops-ticket-comment-toggles">
				<?php if( $ticket->type == 'public' ) { ?>
				<div id="tops-ticket-public-comment-switch" class="tops-ticket-toggle">
					<input id="tops-ticket-public-comment" class="js-switch tops-ticket-public-comment" name="tops-ticket-public-comment" value="no" type="checkbox" />
					<label for="tops-ticket-public-comment"><?php _e('Private', 'total-product-support'); ?></label>
				</div>
				<?php } ?>
				<?php if( current_user_can('edit_tops_tickets') ) { ?>
				<div id="tops-ticket-close-ticket-switch"  class="tops-ticket-toggle">
					<input id="tops-ticket-close-ticket" class="js-switch tops-ticket-close-ticket" name="tops-ticket-close-ticket" value="yes" type="checkbox" />
					<label for="tops-ticket-close-ticket"><?php _e('Close Ticket', 'total-product-support'); ?></label>
				</div>
				<?php } ?>
				<a id="tops-ticket-cancel-comment" class="tops-ticket-cancel-comment" href="#"><?php _e('Cancel', 'total-product-support'); ?></a>
			</div>
		</div>
		
		<?php if( current_user_can('edit_tops_tickets') ) { ?>
			<?php $tops_customer_notes = get_user_meta( $ticket->get_user_id(), 'tops_customer_notes', true ); ?>
			<div id="tops-customer-notes"><?php echo wpautop(convert_chars(wptexturize($tops_customer_notes))); ?></div>
			<input type="hidden" name="tops-user-id" value="<?php echo $ticket->get_user_id(); ?>" />
		<?php } ?>
		<input type="hidden" name="tops-ticket-id" value="<?php echo $ticket->id; ?>" />
		<input type="hidden" name="tops-ticket-post-id" value="<?php echo get_the_id(); ?>" />
		<input type="hidden" id="tops-ticket-comment-type" name="tops-ticket-comment-type" value="public" />
		<input type="hidden" id="tops-ticket-comment-object" name="tops-ticket-comment-object" value="comment" />
		<input type="hidden" name="tops-ticket-comment-attachments" value="" />
		
	</form>
	
</div>