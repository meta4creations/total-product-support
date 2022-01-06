<div id="tops-ticket-action-bar">
	<div id="tops-ticket-action-bar-loading">
		<i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>
		<span class="sr-only"><?php _e('Loading...', 'total-product-support'); ?></span>
	</div>
	<div id="tops-ticket-action-bar-buttons">
		<a href="#" id="tops-ticket-action-reply"><i class="fa fa-comment" aria-hidden="true"></i> <?php _e('Post a Reply', 'total-product-support'); ?></a>
		<?php if( current_user_can('edit_tops_tickets') ) { ?>
			<a href="#" id="tops-ticket-action-note"><i class="fa fa-pencil" aria-hidden="true"></i> <?php _e('Post a Note', 'total-product-support'); ?></a>
			<a href="#" id="tops-ticket-action-customer-note"><i class="fa fa-user" aria-hidden="true"></i> <?php _e('Customer Notes', 'total-product-support'); ?></a>
		<?php } ?>
	</div>
</div>