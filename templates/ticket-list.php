<?php	
if( isset($tickets) ) {
	
	$heading = isset( $heading ) ? $heading : false;
	$list_class = 'tops-ticket-archive-list';
	if( isset($class) ) {
		$list_class .= ' '.$class;
	}
	
	if( is_array($tickets) && count($tickets) > 0 ) {
		
		if( $heading ) {
			echo '<h4 class="tops-ticket-archive-list-heading">'.$heading.'</h4>';
		}
		echo '<ul class="'.$list_class.'">';
		foreach( $tickets as $obj ) {
			echo tops_get_template_part( 'single-ticket/link', array('post_id'=>$obj->ID) );
		}
		echo '</ul>';
	}
	
}