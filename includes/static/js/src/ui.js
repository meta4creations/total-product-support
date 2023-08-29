/* //global tops_vars:true */
/* global Switchery:true */
/* //global console:true */

jQuery( document ).ready( function($) {

	// Setup strict mode
	(function() {

    "use strict";
    
    // Enable switchery
/*
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
		elems.forEach(function(html) {
		  var switchery = new Switchery( html, {
			  size: 'small'
		  });
		});
*/
		
		$('.js-switch').each( function() {
			
			var settings = {
				size: 'small'
			};
			
			if( $(this).hasClass('tops-ticket-public-comment') ) {
				settings.color = '#DA5830';
			}
			
			if( $(this).attr('id') === 'tops-ticket-close-ticket' ) {
				settings.color = '#dfdfdf';
			}

			var switchery = new Switchery( $(this)[0], settings);
		});
 
	}());

});