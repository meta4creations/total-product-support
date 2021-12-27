<?php
if( isset($id) ) {

	$comment = new TOPS_Ticket_Comment( $id );
	$last_comment = isset($last_comment) ? $last_comment : false;
	$update_comment_string = ($comment->type == 'private') ? tops_string('update_private_comment') : tops_string('update_comment');
	//echo '<pre>';print_r($comment);echo '</pre>';
	?>	
	
	<div id="tops-ticket-comment-<?php echo $comment->id; ?>" <?php echo $comment->class_names(); ?>>
		<div class="tops-ticket-comment-avatar"><?php echo get_avatar( $comment->user_id, 100 ); ?></div>
		<div class="tops-ticket-comment-content">
			<div class="tops-ticket-comment-content-inner">
				
				<?php /*
if( is_admin() ) { ?>
					<div id="tops-ticket-comment-edit-form-<?php echo $comment->id; ?>" class="tops-ticket-comment-edit-form">
				<?php } else { ?>
					<form id="tops-ticket-comment-edit-form-<?php echo $comment->id; ?>" class="tops-ticket-comment-edit-form">
				<?php } ?>
					
					<div class="tops-ticket-comment-edit-form-editor"></div>
					
					<div class="tops-ticket-comment-actions">
						<div class="tops-ticket-comment-buttons">
							<button type="submit" class="tops-ticket-comment-update" form="tops-ticket-comment-edit-form-<?php echo $comment->id; ?>" value="<?php _e('Update Comment', 'total-product-support'); ?>"><?php echo $update_comment_string; ?></button>
							<a href="#" class="tops-ticket-comment-attachments" data-comment-id="<?php echo $comment->id; ?>"><i class="fa fa-paperclip" aria-hidden="true"></i> <?php _e('Edit Attachments', 'total-product-support'); ?></a>
						</div>
						<div class="tops-ticket-comment-toggles">
							<?php if( $comment->is_public_ticket() == 'yes' ) { ?>
								<div id="tops-ticket-public-comment-switch" class="tops-ticket-toggle">
									<input id="tops-ticket-public-comment-update-<?php echo $comment->id; ?>" class="js-switch tops-ticket-public-comment tops-ticket-public-comment-update" name="tops-ticket-public-comment" value="no" type="checkbox" <?php checked('no', $comment->is_public); ?> />
									<label for="tops-ticket-public-comment-update-<?php echo $comment->id; ?>"><?php _e('Private', 'total-product-support'); ?></label>
								</div>
							<?php } ?>
							<a class="tops-ticket-cancel-comment" href="#"><?php _e('Cancel', 'total-product-support'); ?></a>
						</div>
					</div>
					
					<input type="hidden" name="tops-ticket-comment-id" value="<?php echo $comment->id; ?>" />
					
				
				<?php if( is_admin() ) { ?>
					</div>
				<?php } else { ?>
					</form>
				<?php }
*/ ?>
				
				<div class="tops-ticket-comment-display">
					<div class="tops-ticket-comment-user">
						<?php
						if( $last_comment ) {
							echo sprintf(__('<span class="tops-ticket-comment-username">%s</span> <span class="tops-ticket-comment-reply-type">started the conversation</span>', 'total-product-support'), $comment->get_user_name());
						} else {
							if( $comment->type == 'public' || $comment->is_public_ticket() == 'no' ) {
								echo sprintf(__('<span class="tops-ticket-comment-username">%1$s</span> <span class="tops-ticket-comment-reply-type">%2$s</span>', 'total-product-support'), $comment->get_user_name(), tops_string('replied'));
							} else {
								echo '<i class="fa fa-lock" aria-hidden="true"></i> '.sprintf(__('<span class="tops-ticket-comment-username">%1$s</span> <span class="tops-ticket-comment-reply-type">%2$s</span>', 'total-product-support'), $comment->get_user_name(), tops_string('replied_privately'));
							}
						}
						?>
					</div>
					<div class="tops-ticket-comment-time"><?php echo $comment->human_time(); ?></div>
					<div class="tops-ticket-comment-description">
						<?php echo $comment->get_comment(); ?>
					</div>
					<?php if( $attachments = $comment->get_attachments() ) { ?>
					<p class="tops-ticket-comment-attachments">
						<strong><?php _e('Attached files:'); ?></strong>
						<?php echo $attachments; ?>
					</p>
					<?php } ?>
					<?php if( current_user_can('edit_tops_tickets') || get_current_user_id() == $comment->user_id ) { ?>
						<div class="tops-ticket-comment-modify">
							<a class="tops-ticket-comment-edit" href="#" data-comment-id="<?php echo $comment->id; ?>" data-type="<?php echo $comment->type; ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
							<?php if( current_user_can('edit_tops_tickets') && !$last_comment ) { ?>
								<a class="tops-ticket-comment-delete" href="#" data-comment-id="<?php echo $comment->id; ?>"><i class="fa fa-trash" aria-hidden="true"></i></a>
							<?php } ?>
						</div>
					<?php } ?>
					<?php if( current_user_can('edit_tops_tickets') && get_current_user_id() != $comment->user_id ) { ?>
						<a href="#" class="tops-ticket-comment-flag" data-comment-id="<?php echo $comment->id; ?>"><i class="fa fa-flag-o" aria-hidden="true"></i></a>
					<?php } ?>
				</div>
				
			</div>
		</div>
	</div>
	
	<?php
}