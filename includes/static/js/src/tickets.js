jQuery( document ).ready( function($) {

	// Setup strict mode
	(function() {

    "use strict";
    
    var comment_cache,
    		customer_note,
    		attachment_data = [];
    		
    /**
     * Open the media uploader for ticket attachments
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-add-attachment').on( 'click', function( e ) {

      e.preventDefault();
	
		  // Save the container
		  var $list = $('#tops-ticket-attachments'),
		  		$input = $('input[name="tops-ticket-comment-attachments"]');
	
		  // Create a custom uploader
		  var uploader;
		  if( uploader ) {
		    uploader.open();
		    return;
		  }
	
		  // Set the uploader attributes
		  uploader = wp.media({
		    multiple: true,
		    library: {
		    	type: 'image'
	    	}
		  });
		  
		  // opens the modal with the correct attachment selected
			uploader.on( 'open' , function() {
				var selection = uploader.state().get('selection');
				uploader.reset();
				
				jQuery.each(attachment_data, function( key, attachment ) {
			    jQuery.each(attachment, function( key, value ) {
				    if( key === 'id' ) {
					    var file = wp.media.attachment(value);
					    selection.add(file);
				    }
					});  
				});
			});
	
		  uploader.on( 'select', function() {
				
				// Reset the attachments
				$list.hide();
				$list.find('.tops-ticket-comment-attachment').remove();
				attachment_data = [];
				
				var attachments = uploader.state().get('selection').toJSON();	
				
				if( attachments.length > 0 ) {
					$list.show();
				}
							
				for( var i=0; i<attachments.length; i++ ) {
				
					var data = {
						'id' : attachments[i].id,
						'type' : attachments[i].type,
						'url' : attachments[i].url,
						'filename' : attachments[i].filename,
					};
					
					attachment_data.push( data );
					
					switch( attachments[i].type ) {
						case 'image':
							data.icon = '<i class="fa fa-picture-o" aria-hidden="true"></i>';
							break;
					}
					
					$list.append( tops_template('comment_attachment', data) );
				}
				
				$input.val( JSON.stringify(attachment_data) );
		  });
	
		  //Open the uploader dialog
		  uploader.open();
	
		  return false;
    });
    
    $('body').on( 'click', '.tops-ticket-attachment-remove', function(e) {
	    e.preventDefault();
	    
	    var $attachment = $(this).parents('.tops-ticket-comment-attachment'),
					$container = $(this).parents('.tops-ticket-comment-attachments'),
					count = $attachment.siblings('.tops-ticket-comment-attachment').length,
	    		ok = confirm(  tops_vars.strings.confirm_delete_attachment ),
	    		is_new_comment = $(this).parents('#tops-ticket-attachments').length;
	    		
	    if( ok ) {
		    
		    // Remove the attachment from the display
		    $attachment.remove();
				if( count === 0 ) {
					$container.hide();
				}
	    	
	    	// If this is a new comment
		    if( is_new_comment ) {
			    
			    var $input = $('input[name="tops-ticket-comment-attachments"]'),
			    		attachment_id = $(this).data('attachment-id'),
			    		new_attachment_data = [];
			    
			    jQuery.each(attachment_data, function( key, attachment ) {
				    
				    var keep_attachment = true;
				    
				    jQuery.each(attachment, function( key, value ) {
					    if( key === 'id' && value === attachment_id ) {
						    keep_attachment = false;
					    }
						});
						
						if( keep_attachment ) {
					    new_attachment_data.push( attachment );
				    }
				    
				    attachment_data = new_attachment_data;
				    $input.val( JSON.stringify(attachment_data) );
					    
					});
 
		    // If the attachment is in an existing comment
		    } else {
		
			    var data = {
						action: 'tops_ticket_delete_attachment',
						id: $(this).data('comment-id'),
						attachment_id: $(this).data('attachment-id'),
						security: tops_vars.security
					};
					jQuery.post( tops_vars.ajaxurl, data );
				}
			}
			
    });
    
    
    /**
     * Reset the comment editor
     *
     * @access  public
     * @since   1.0.0
     */
     
    function reset_reply_forms() {
	    
	    $('#tops-ticket-action-reply').removeClass('active');
	    $('#tops-ticket-action-note').removeClass('active');
	    $('#tops-ticket-action-customer-note').removeClass('active');
	    
	    $('#tops-ticket-new-comment-container').removeAttr('class');
	    $('#tops-ticket-comment-reply-container').slideUp();
	    tinyMCE.activeEditor.setContent('');
    }
    
    
    /**
     * Reset the comment editor
     *
     * @access  public
     * @since   1.0.0
     */
     
    function reset_comment_editors() {
	    
	    // Reset the comment wp_editor
	    $('#tops-ticket-comment-editor').append( $('#tops-ticket-comment-editor-contents') );
	    
	    $('.tops-ticket-comment').each( function() {	
				$(this).find('.tops-ticket-comment-edit-form').hide();
    		$(this).find('.tops-ticket-comment-display').show();
	    });
    }
    
    
    /**
     * Toggle the unread button
     *
     * @access  public
     * @since   1.0.0
     */
    function toggle_unread_button( is_unread ) {
	    
	    var $ticket = $('.tops-ticket-container'),
	    		$button = $('#tops-ticket-response-toggle a');

	    if( is_unread ) {
		    $ticket.removeClass('tops-ticket-read');
		    $ticket.addClass('tops-ticket-unread');
		    $button.html('<i class="fa fa-circle" aria-hidden="true"></i> '+tops_vars.strings.needs_response); 
	    } else {
		    $ticket.removeClass('tops-ticket-unread');
		    $ticket.addClass('tops-ticket-read');
		    $button.html('<i class="fa fa-circle-o" aria-hidden="true"></i> '+tops_vars.strings.mark_as_unread);
	    }
    }
    
    
    /**
     * Toggle the starred button
     *
     * @access  public
     * @since   1.0.0
     */
    function toggle_starred_button( $button, $ticket, is_starred ) {

	    if( is_starred ) {
		    $ticket.addClass('tops-ticket-starred');
		    $button.html('<i class="fa fa-star" aria-hidden="true"></i>'); 
	    } else {
		    $ticket.removeClass('tops-ticket-starred');
		    $button.html('<i class="fa fa-star-o" aria-hidden="true"></i>');
	    }
    }
    
    
    /**
     * Update the new comment area
     *
     * @access  public
     * @since   1.0.0
     */
    function update_new_comment_area() {
	    
	    var is_private = $('#tops-ticket-public-comment').is(':checked'),
	    		is_closed = $('#tops-ticket-close-ticket').is(':checked');
	    		
	    if( is_private ) {
		    $('#tops-ticket-comment-type').val('private');
		    $('#tops-ticket-new-comment-container').attr('class', 'tops-ticket-create-reply-private');
		    $('#tops-ticket-post-new-comment').text( is_closed ? tops_vars.strings.reply_privately_and_close : tops_vars.strings.reply_privately );
	    } else {
		    $('#tops-ticket-comment-type').val('public');
		    $('#tops-ticket-new-comment-container').attr('class', 'tops-ticket-create-reply');
		    $('#tops-ticket-post-new-comment').text( is_closed ? tops_vars.strings.reply_and_close : tops_vars.strings.reply );
	    }
    }
    
    
    /**
     * Listen to the public toggle change
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-public-comment').change( function() {  
	    update_new_comment_area();
		});
		
		
		/**
     * Listen to the close ticket change
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-close-ticket').change( function() {
	    update_new_comment_area();
		});
		
		
		/**
     * Listen to the public toggle updated change
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('.tops-ticket-public-comment-update').change( function() {
	    
	    var checked = $(this).is(':checked'),
	    		$ticket = $(this).parents('.tops-ticket-container'),
	    		$comment = $(this).parents('.tops-ticket-comment'),
	    		$update_button = $comment.find('.tops-ticket-comment-update'),
	    		$reply_type = $comment.find('.tops-ticket-comment-reply-type');
	    		
	    if( checked && $ticket.hasClass('tops-ticket-type-public') ) {
		    $comment.addClass('tops-ticket-comment-private');
		    $reply_type.text( tops_vars.strings.replied_privately );
		    $update_button.text( tops_vars.strings.update_private_comment ); 
	    } else {
		    $comment.removeClass('tops-ticket-comment-private');
		    $reply_type.text( tops_vars.strings.replied );
		    $update_button.text( tops_vars.strings.update_comment );
	    }
		});
    
    
    /**
     * Toggle the reply field
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-action-reply').click( function(e) {
	    e.preventDefault();
	    
	    if( $('#tops-ticket-new-comment-container').hasClass('tops-ticket-create-reply') ) {
		  	return; 
		  }

	    $(this).addClass('active');
	    $(this).siblings().removeClass('active');
	    $('#tops-ticket-comment-reply-container').show();
	    update_new_comment_area();
	    
	    // Set the comment type value
	    $('#tops-ticket-comment-object').val('comment');
	    
	    tinyMCE.execCommand( 'mceRemoveEditor', false, 'topsticketcomment' );
	    tinyMCE.execCommand( 'mceAddEditor', false, 'topsticketcomment' );
			if( comment_cache ) {
	      tinyMCE.activeEditor.setContent( comment_cache );
      } else if( customer_note ) {
	      tinyMCE.activeEditor.setContent( '' );
      }
      
      tinyMCE.get('topsticketcomment').focus();
      
      comment_cache = false;
      customer_note = false;
    });
    
    
    /**
     * Toggle the note field
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-action-note').click( function(e) {
	    e.preventDefault();
	    
	    if( $('#tops-ticket-new-comment-container').hasClass('tops-ticket-create-note') ) {
		  	return; 
		  }
	    
	    $('#tops-ticket-post-new-comment').text( tops_vars.strings.add_note );
	    
	    $(this).addClass('active');
	    $(this).siblings().removeClass('active');
	    $('#tops-ticket-new-comment-container').attr('class', 'tops-ticket-create-note');
	    $('#tops-ticket-comment-reply-container').show();
	    
	    // Set the comment type value
	    $('#tops-ticket-comment-object').val('note');
	    
	    tinyMCE.execCommand( 'mceRemoveEditor', false, 'topsticketcomment' );
	    tinyMCE.execCommand( 'mceAddEditor', false, 'topsticketcomment' );
	    if( comment_cache ) {  
	      tinyMCE.activeEditor.setContent( comment_cache );
      } else if( customer_note ) {
	      tinyMCE.activeEditor.setContent( '' );
      } 
      tinyMCE.get('topsticketcomment').focus();
      
      comment_cache = false;
      customer_note = false;
      
    });
    
    
    /**
     * Toggle the customer note field
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-action-customer-note').click( function(e) {
	    e.preventDefault();
	    
	    if( $('#tops-ticket-new-comment-container').hasClass('tops-ticket-create-customer-note') ) {
		  	return; 
		  }
		    
	    $('#tops-ticket-post-new-comment').text( tops_vars.strings.save_customer_notes );
	    
	    $(this).addClass('active');
	    $(this).siblings().removeClass('active');
	    $('#tops-ticket-new-comment-container').attr('class', 'tops-ticket-create-customer-note');
	    $('#tops-ticket-comment-reply-container').show();
	    
	    // Set the comment type value
	    $('#tops-ticket-comment-object').val('customer-note');
	    
	    var $customer_notes = $('#tops-customer-notes');
	    
	    customer_note = true;
	    		
	    tinyMCE.triggerSave();
	    comment_cache = tinyMCE.activeEditor.getContent();
	    		
	    tinyMCE.execCommand( 'mceRemoveEditor', false, 'topsticketcomment' );
      tinyMCE.execCommand( 'mceAddEditor', false, 'topsticketcomment' );
      tinyMCE.activeEditor.setContent( $customer_notes.html() );
      tinyMCE.get('topsticketcomment').focus();
      
      // Move the cursor to the end of the note
      tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
			tinyMCE.activeEditor.selection.collapse(false);
    });
    
    
    /**
     * Cancel a reply
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-cancel-comment').click( function(e) {
	    e.preventDefault();
	    reset_reply_forms();
    });
    
    
    /**
     * Cancel an edit
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('.tops-ticket-cancel-comment').click( function(e) {
	    e.preventDefault();
			reset_comment_editors();	
    });
    
    
    /**
     * Modify a comment
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('body').on('click', '.tops-ticket-comment-edit', function(e) {
	    e.preventDefault();
	    
	    // Reset any current editors
	    reset_comment_editors();
	    
	    var $comment = $(this).parents('.tops-ticket-comment'),
	    		$comment_display = $comment.find('.tops-ticket-comment-display'),
	    		$comment_edit_form = $comment.find('.tops-ticket-comment-edit-form'),
	    		$comment_edit_form_editor = $comment.find('.tops-ticket-comment-edit-form-editor'),
	    		description_html = $comment.find('.tops-ticket-comment-description').html();
	    		
	    $comment_display.hide();
	    
	    // Add and re-initialize the editor
	    $comment_edit_form_editor.append( $('#tops-ticket-comment-editor-contents') );
      tinyMCE.execCommand( 'mceRemoveEditor', false, 'topsticketedit' );
      tinyMCE.execCommand( 'mceAddEditor', false, 'topsticketedit' );
      tinyMCE.activeEditor.setContent( description_html );
	    
	    $comment_edit_form.show();
    });
		
		
		/**
     * Make ticket private
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('#tops-ticket-private-toggle').click( function(e) {
	    e.preventDefault();

			var data = {
				action: 'tops_ticket_update',
				tops_action: 'set_as_private',
				id: $(this).data('ticket-id'),
				security: tops_vars.security
			};
			jQuery.post( tops_vars.ajaxurl, data, function( response ) {
				if( response.success ) {
			    location.reload();
		    }	  
			}, 'json');
    });
    
    
    /**
     * Toggle ticket unread
     *
     * @access  public
     * @since   1.0.0
     */
    $('#tops-ticket-response-toggle a').click( function(e) {
	    e.preventDefault();

	    var $ticket = $(this).parents('.tops-ticket-container'),
	    		tops_action;
	    
	    if( $ticket.hasClass('tops-ticket-unread') ) {
		    tops_action = 'set_as_read';
		    toggle_unread_button();  
	    } else {
		    tops_action = 'set_as_unread';
		    toggle_unread_button( true );
	    }

			var data = {
				action: 'tops_ticket_update',
				id: $(this).data('ticket-id'),
				tops_action: tops_action,
				security: tops_vars.security
			};
			jQuery.post( tops_vars.ajaxurl, data, function( response ) {
				if( response.success ) {
			    // Do nothing yet
		    }	  
			}, 'json');
    });
    
    
    /**
     * Toggled the starred attribute of a ticket
     *
     * @access  public
     * @since   1.0.0
     */
     
    function set_starred_ticket( $button, $ticket ) {
	  	
	  	var tops_action;

	  	if( $ticket.hasClass('tops-ticket-starred') ) {
		    tops_action = 'set_as_unstarred';
		    toggle_starred_button( $button, $ticket );
	    } else {
		    tops_action = 'set_as_starred';
		    toggle_starred_button( $button, $ticket, true );
	    }

	    var data = {
				action: 'tops_ticket_update',
				id: $button.data('ticket-id'),
				tops_action: tops_action,
				security: tops_vars.security
			};
			
			jQuery.post( tops_vars.ajaxurl, data, function( response ) {
				if( response.success ) {
			    // Do nothing yet
		    }	  
			}, 'json');
    }


    /**
     * Delete a comment
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('body').on('click', '.tops-ticket-comment-delete', function(e) {
	    e.preventDefault();
	    
	    var $comment = $(this).parents('.tops-ticket-comment'),
	    		ok = confirm( 'Are you sure you want to delete this comment?' ),
	    		object = $comment.hasClass('tops-ticket-note') ? 'note' : 'comment';
					
			if( ok ) {
				
				$comment.addClass('tops-comment-delete');
		    		
		    var data = {
					action: 'tops_ticket_delete_comment',
					id: $(this).data('comment-id'),
					object: object,
					security: tops_vars.security
				};
				jQuery.post( tops_vars.ajaxurl, data, function( response ) {
					if( response.success ) {
				    $comment.slideUp( function() {
					    $comment.remove();
				    });
			    }	  
				}, 'json');
				
			}
    });
    
    
    /**
     * Flag a comment
     *
     * @access  public
     * @since   1.0.0
     */
     
    $('body').on('click', '.tops-ticket-comment-flag', function(e) {
	    e.preventDefault();
	    
	    var $comment = $(this).parents('.tops-ticket-comment'),
	    		is_flagged;

			if( $comment.hasClass('tops-ticket-comment-flagged') ) {
				is_flagged = 'no';
				$comment.removeClass('tops-ticket-comment-flagged');
			} else {
				is_flagged = 'yes';
				$comment.addClass('tops-ticket-comment-flagged');
			}
	    		
	    var data = {
				action: 'tops_ticket_flag_comment',
				id: $(this).data('comment-id'),
				is_flagged: is_flagged,
				security: tops_vars.security
			};
			jQuery.post( tops_vars.ajaxurl, data, function( response ) {
				if( response.success ) {
					// Nothing here as of yet...
		    }
			}, 'json');
				
    });


		/**
		 * Ticket new ticket submit
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		 
		// $('#tops-ticket-new-ticket-form').validate({
	  // 	submitHandler: function(form) {
	  //   	form.submit();
	  //   }
	  // });
		
		$('#tops-ticket-new-ticket-form').submit(function(e) { 
			e.preventDefault();
	    
	    tinyMCE.triggerSave();
	    
	    $(this).ajaxSubmit({
		    url: tops_vars.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
	        action: 'tops_ticket_new_ticket_submit',
	        security: tops_vars.security
		    },
		    beforeSubmit: function() {
			    return true;
		    },
        success: function( response ) {
	        //console.log(response);
	        if( response.error ) {
	        } else if( response.success ) {
				    window.location = response.success;
			    }
        },
	    }); 
    });


		/**
		 * Ticket reply submit
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		 
		$('#tops-ticket-comment-reply-form').submit(function(e) { 
			e.preventDefault();
	    
	    tinyMCE.triggerSave();
	    
	    $('#tops-ticket-action-bar').addClass('submitting');
	    
	    tinyMCE.triggerSave();
	    var updated_html = tinyMCE.activeEditor.getContent();
	    
	    $(this).ajaxSubmit({
		    url: tops_vars.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
	        action: 'tops_ticket_comment_submit',
	        security: tops_vars.security
		    },
        success: function( response ) {
	        //console.log(response);
				  if( response.success ) {
					  location.reload();
		        
/*
		        $('#tops-ticket-action-bar').removeClass('submitting');
		        reset_reply_forms();
		        
		        var $comment = $(response.success);
		        $comment.hide();
		        $comment.addClass('tops-comment-new');
		        $('#tops-ticket-comments').prepend( $comment );
		        $comment.slideDown();
		        setTimeout(function() {
			        $comment.removeClass('tops-comment-new');
		        }, 1000);
		        if( response.type === 'comment' ) {
			        toggle_unread_button();
		        }
*/
			    } else if( response.updated ) {
				    
				    $('#tops-ticket-action-bar').removeClass('submitting');
				    $('#tops-customer-notes').html( updated_html );
				    reset_reply_forms();
			    }
        },
	    }); 
    });
    
    
    /**
		 * Ticket edit submit
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		 
		$('.tops-ticket-comment-edit-form').submit(function(e) { 
			e.preventDefault();
			
			var $comment = $(this).parents('.tops-ticket-comment'),
	    		$comment_display = $comment.find('.tops-ticket-comment-display'),
	    		$comment_description = $comment.find('.tops-ticket-comment-description');
	    
	    tinyMCE.triggerSave();
	    var updated_html = tinyMCE.activeEditor.getContent();
	    
	    $(this).ajaxSubmit({
		    url: tops_vars.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
	        action: 'tops_ticket_comment_edit_submit',
	        security: tops_vars.security
		    },
        success: function( response ) {

	        if( response.success ) {
		        $comment_description.html( updated_html );
		        
		        // Reset any current editors
						reset_comment_editors();
		        
		        // Show the display
		        $comment_display.show();
			    }
        },
	    }); 
    });
    
    
    /**
     * Toggle ticket starred
     *
     * @access  public
     * @since   1.0.0
     */
    $('#tops-ticket-starred-toggle a').click( function(e) {
	    e.preventDefault();
	    set_starred_ticket( $(this), $(this).parents('.tops-ticket-container') );
    });
    
    
    /**
     * Toggle archive ticket starred
     *
     * @access  public
     * @since   1.0.0
     */
    $('.tops-ticket-link-icons-starred').click( function(e) {
	    e.preventDefault();
	    set_starred_ticket( $(this), $(this).parents('.tops-ticket-link') );
    });
    
 
	}());

});