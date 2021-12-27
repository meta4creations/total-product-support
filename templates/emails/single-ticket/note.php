<?php
if( isset($id) ) {
	
	$note = new TOPS_Ticket_Note( $id );	
	
	if( current_user_can('edit_tops_tickets') ) { ?>
		
		<div id="tops-ticket-comment-<?php echo $note->id; ?>" <?php echo $note->class_names(); ?>>
			<div class="tops-ticket-comment-avatar"><?php echo get_avatar( $note->user_id, 100 ); ?></div>
			<div class="tops-ticket-comment-content">
				<div class="tops-ticket-comment-content-inner">
					
					<?php if( is_admin() ) { ?>
						<div id="tops-ticket-comment-edit-form-<?php echo $note->id; ?>" class="tops-ticket-comment-edit-form">
					<?php } else { ?>
						<form id="tops-ticket-comment-edit-form-<?php echo $note->id; ?>" class="tops-ticket-comment-edit-form">
					<?php } ?>
						
						<div class="tops-ticket-comment-edit-form-editor"></div>
						
						<div class="tops-ticket-comment-actions">
							<div class="tops-ticket-comment-buttons">
								<button type="submit" class="tops-ticket-comment-update" form="tops-ticket-comment-edit-form-<?php echo $note->id; ?>" value="<?php _e('Update Comment', 'total-product-support'); ?>"><?php _e('Update Comment', 'total-product-support'); ?></button>
								<a href="#" class="tops-ticket-comment-attachments" data-comment-id="<?php echo $note->id; ?>"><i class="fa fa-paperclip" aria-hidden="true"></i> <?php _e('Edit Attachments', 'total-product-support'); ?></a>
							</div>
							<div class="tops-ticket-comment-toggles">
								<a class="tops-ticket-cancel-comment" href="#"><?php _e('Cancel', 'total-product-support'); ?></a>
							</div>
						</div>
						
						<input type="hidden" name="tops-ticket-comment-id" value="<?php echo $note->id; ?>" />
						
					
					<?php if( is_admin() ) { ?>
						</div>
					<?php } else { ?>
						</form>
					<?php } ?>
					
					<div class="tops-ticket-comment-display">
						<div class="tops-ticket-comment-user">
							<?php echo sprintf(__('<span class="tops-ticket-comment-username">%1$s</span> <span class="tops-ticket-comment-reply-type">%2$s</span>', 'total-product-support'), $note->get_user_name(), tops_string('added_a_note')); ?>
						</div>
						<div class="tops-ticket-comment-time"><?php echo $note->human_time(); ?></div>
						<div class="tops-ticket-comment-description">
							<?php echo $note->get_comment(); ?>
						</div>
						<?php if( $attachments = $note->get_attachments() ) { ?>
						<p class="tops-ticket-comment-attachments">
							<strong><?php _e('Attached files:'); ?></strong>
							<?php echo $attachments; ?>
						</p>
						<?php } ?>
						<?php if( current_user_can('edit_tops_tickets') || get_current_user_id() == $note->user_id ) { ?>
							<div class="tops-ticket-comment-modify">
								<a class="tops-ticket-comment-edit" href="#" data-comment-id="<?php echo $note->id; ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
								<?php if( current_user_can('edit_tops_tickets') ) { ?>
									<a class="tops-ticket-comment-delete" href="#" data-comment-id="<?php echo $note->id; ?>"><i class="fa fa-trash" aria-hidden="true"></i></a>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
					
				</div>
			</div>
		</div>
		
	<?php
	}
}