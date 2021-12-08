<?php

/**
 * TOPS Categories class
 *
 * @package     TOPS
 * @subpackage  Classes/TOPS Categories
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/
class TOPS_Categories {
	
	public $id;
	
	public $thumbnail;


	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( $id=false, $type='' ) {
		
		if( $id ) {
			$this->get_meta( $id, $type );
		}
	}
	
	
	/**
	 * Return the thumbnail
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function thumbnail( $id, $echo=false ) {

		$thumbnail = '<img src="'.TOPS_PLUGIN_URL.'includes/static/img/no-image.png" alt="'.__('No Image', 'total-product-support').'" />';
		
		$tops_term_thumbnail = get_term_meta( $id, 'tops_term_thumbnail', true );
		if( $image = wp_get_attachment_image($tops_term_thumbnail, 'thumbnail') ) {
	  	$thumbnail = $image;
  	}
		
		if( $echo ) {
			echo $thumbnail;
		} else {
			return $thumbnail;
		}
	}

}
