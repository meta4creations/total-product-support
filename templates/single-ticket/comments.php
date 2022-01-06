<div id="tops-ticket-comments">
	
	<?php
	$post_id = isset($ticket) ? $ticket->get_post_id() : false;
	$comments = TOPS()->tickets->get_comments( $post_id, true );
	
	if( is_array($comments) && count($comments) > 0 ) {
		
		$counter = 0;
		$last = count($comments)-1;
		
		foreach( $comments as $obj ) {

			$last_comment = ( $counter == $last ) ? true : false;

			if( isset($obj['type']) ) {
				echo tops_get_template_part( 'single-ticket/comment', array('id'=>$obj['id'], 'last_comment'=>$last_comment) );
			} else {
				echo tops_get_template_part( 'single-ticket/note', array('id'=>$obj['id']) );
			}
			
			$counter++;
		}
	}
	?>
	
	<div id="tops-ticket-comment-editor" style="display: none;">
		<div id="tops-ticket-comment-editor-contents">
			<?php
			$content = '';
			$editor_id = 'topsticketedit';
			$settings = apply_filters( 'tops-ticket-comment-editor-settings', array(
				'textarea_rows' => 8,
				'drag_drop_upload' => true,
			));
			wp_editor( $content, $editor_id, $settings );	
			?>
		</div>
	</div>
	
</div>