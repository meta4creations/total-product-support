<?php

/**
 * TOPS user class
 *
 * @package     TOPS
 * @subpackage  Classes/TOPS users
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/
class TOPS_User {
	
	public $id;
	
	public $user_id;
	
	public $user_name;
	
	public $user_email;


	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( $id=false, $type='' ) {
		
		if( $id ) {
			$this->set_data( $id );
		}
	}
	
	
	/**
	 * Create a new ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function create( $data=array() ) {
		
		if( !isset($data['id']) ) {
			$data['id'] = $this->set_user_id();
		}

		if( !isset($data['user_id']) ) {
			$current_user = wp_get_current_user();
			if( $current_user->ID != 0 ) {
				$data['user_id'] = $current_user->ID;
				$data['user_name'] = $current_user->display_name;
				$data['user_email'] = $current_user->user_email;
			}
		}
				
		// Create the ticket post
		$post_id = $this->create_user( $data );
	}
	
	
	/**
	 * Create the ticket post
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function create_user( $data=array() ) {
		
		// Setup the new post data
		$ticket = array(
			'post_type'			=> 'tops_ticket',
			'post_name'			=> isset($data['id']) ? $data['id'] : false,
			'post_title'		=> isset($data['subject']) ? $data['subject'] : false,
			'post_status'		=> 'publish'
		);
		
		return wp_insert_post( $ticket, true );
	}

	
	/**
	 * Get the next ticket id
	 *
	 * @access  private
	 * @return	ticket_id
	 * @since   1.0.0
	 */
	private function set_user_id() {

		$counter = get_option( 'tops_ticket_counter', 999 );
		$counter++;
		$this->id = $counter;
		update_option( 'tops_ticket_counter', $counter );
		
		return $counter;
	}

}
