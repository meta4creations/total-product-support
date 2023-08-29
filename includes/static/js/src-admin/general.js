/* global wp:true */

jQuery(document).ready(function($) {

	// Setup strict mode
	(function() {

		"use strict";

		/**
		 * Open the media uploader for category thumbnails
		 *
		 * @access  public
		 * @since   1.0.0
		 */

		$('.tops-thumbnail-select').on('click', function(e) {

			e.preventDefault();

			// Save the container
			var $trigger = $(this),
				$input = $trigger.children('input'),
				$image = $trigger.children('img');

			// Create a custom uploader
			var uploader;
			if (uploader) {
				uploader.open();
				return;
			}

			// Set the uploader attributes
			uploader = wp.media({
				multiple: false,
				library: {
					type: 'image'
				}
			});

			uploader.on('select', function() {

				var attachments = uploader.state().get('selection').toJSON();
				if (attachments.length > 0) {
					$input.val(attachments[0].id);
					$image.attr('src', attachments[0].url);
					$image.attr('title', attachments[0].title);
					$image.attr('alt', attachments[0].alt);
				}
			});

			//Open the uploader dialog
			uploader.open();

			return false;
		});

	}());

});