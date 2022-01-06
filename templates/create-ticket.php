<div id="tops-ticket-new-ticket" class="tops-form-container">

	<form id="tops-ticket-new-ticket-form" class="tops-form">
		
		<div class="tops-form-item">
			<h3 class="tops-form-item-label"><?php _e( 'Product', 'total-product-support'); ?></h3>
			<div class="tops-form-item-description"><?php _e('With which product do you need help?', 'total-product-support'); ?></div>
			<select name="product" class="tops-form-item-element tops-form-item-select required" required>
				<option value=""><?php _e( 'Select a Product', 'total-product-suppport' ); ?></option>
				<?php
				// echo '<option>'.__('Choose one ...', 'total-product-support').'</option>';
				if( function_exists('EDD') ) {	
					$license_keys = edd_software_licensing()->get_license_keys_of_user( get_current_user_id(), 0, 'any', true );	
					if( is_array( $license_keys ) && count( $license_keys ) > 0 ) {
						foreach( $license_keys as $i=>$license ) {
							if ( $download = $license->get_download() ) {
								echo '<option value="'.$download->ID.'">'.$download->post_title.'</option>';
							}
						}
					}
				}
				?>
			</select>
		</div>
		
<!--
		<div class="tops-form-item">
			<h3 class="tops-form-item-label"><?php _e('License Key', 'total-product-support'); ?></h3>
			<div class="tops-form-item-description"><?php _e('Paste your license key here to verify your purchase.', 'total-product-support'); ?></div>
			<input type="text" name="license" class="tops-form-item-element tops-form-item-text" placeholder="<?php _e('ex. c639f1e0a6f64c9341ab354435b1209c', 'total-product-support'); ?>" />
		</div>
-->
		
		<?php if( !is_user_logged_in() ) { ?>
			<div class="tops-form-item">
				<h3 class="tops-form-item-label"><?php _e('Registration', 'total-product-support'); ?></h3>
				<div class="tops-form-item-description">
					<?php _e('You will need to register an account with us to get support.', 'total-product-support'); ?>
					<br/><?php _e('Already have an account? Log in <a href="#">here</a>.', 'total-product-support'); ?>
				</div>
				<div class="tops-form-item-complex">
					<div class="tops-form-item-complex-element">
						<input type="text" name="your-name" class="tops-form-item-element tops-form-item-text" placeholder="<?php _e('Your Name ...', 'total-product-support'); ?>" required />
					</div>
					<div class="tops-form-item-complex-element">
						<input type="email" name="your-email" class="tops-form-item-element tops-form-item-email" placeholder="<?php _e('Email Address ...', 'total-product-support'); ?>" required />
					</div>
					<div class="tops-form-item-complex-element">
						<input type="password" name="your-password" class="tops-form-item-element tops-form-item-password" placeholder="<?php _e('Choose a Password ...', 'total-product-support'); ?>" required />
					</div>
					<div class="tops-form-item-complex-element">
						<input type="password" name="your-password-again" class="tops-form-item-element tops-form-item-password" placeholder="<?php _e('Repeat the password ...', 'total-product-support'); ?>" required />
					</div>
				</div>	
			</div>
		<?php } ?>
		
		<div class="tops-form-item">
			<div class="tops-form-item-complex">
				<div class="tops-form-item-complex-element">
					<h3 class="tops-form-item-label"><?php _e('Ticket Subject', 'total-product-support'); ?></h3>
					<div class="tops-form-item-description"><?php _e('In general, what is this ticket about?', 'total-product-support'); ?></div>
					<input type="text" name="subject" class="tops-form-item-element tops-form-item-text" placeholder="<?php _e('Ticket subject ...', 'total-product-support'); ?>" required />
				</div>
				<div class="tops-form-item-complex-element">
					<h3 class="tops-form-item-label"><?php _e('Related URL', 'total-product-support'); ?></h3>
					<div class="tops-form-item-description"><?php _e('Optional, but very helpful.', 'total-product-support'); ?></div>
					<input type="text" name="related-url" class="tops-form-item-element tops-form-item-url" placeholder="<?php _e('http://', 'total-product-support'); ?>" />
				</div>
			</div>	
		</div>
		
		<div class="tops-form-item">
			<h3 class="tops-form-item-label"><?php _e('Ticket Description', 'total-product-support'); ?></h3>
			<div class="tops-form-item-description"><?php _e('Please be as descriptive as possible regarding the details of this ticket.', 'total-product-support'); ?></div>
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
<!-- 			<textarea name="description" rows="10" class="tops-form-item-element tops-form-item-textarea" placeholder="<?php _e('How can we help you today?', 'total-product-support'); ?>" required ></textarea> -->
		</div>
		
		<div class="tops-form-item">
			<h3 class="tops-form-item-label"><?php _e('Ticket Visibility', 'total-product-support'); ?></h3>
			<div class="tops-form-item-description"><?php _e('By default, only the support team can view and respond to your tickets. A public ticket, however, would allow the entire community to view and reply. Note that they cannot view any information entered into the "private" fields above.', 'total-product-support'); ?></div>
			<label class="tops-form-item-checkbox-label"><input type="checkbox" name="type" value="public" class="tops-form-item-element tops-form-item-checkbox" /><?php _e('Make the ticket public', 'total-product-support'); ?></label>
		</div>
		
		<div class="tops-form-submit">
			<input id="tops-ticket-new-ticket-submit" type="submit" value="<?php _e('Submit Ticket', 'total-product-support'); ?>" />
		</div>

	</form>

</div>