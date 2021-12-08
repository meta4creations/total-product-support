<?php

/**
 * TOPS ticket comment class
 *
 * @package     TOPS
 * @subpackage  Classes/TOPS Ticket Comment
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/
class TOPS_Ticket_Comment extends TOPS_Ticket_Note {	
	
	public $is_flagged;
	
	public $type;
	

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0.0
	 */

	public function __construct( $id=false ) {
		
		if( $id ) {
			$this->id = $id;
			$this->get_post_id();
			$this->get_meta();
		}
	}
	

	/**
	 * Get the note meta
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	 
	private function get_meta() {
		
		$comments = get_post_meta( $this->get_post_id(), '_tops_ticket_comments', true );
		
		// Find the ticket history position
		if( is_array($comments) && count($comments) > 0 ) {
			foreach( $comments as $i=>$meta ) {
				if( esc_attr($meta['id']) == $this->id ) {
					$this->sanitize_meta( $meta );
					break;
				}
			}
		}
	}
	
	
	/**
	 * Setup the note
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function set_meta( $meta=array() ) {
		
		$filtered_keys = apply_filters('tops_ticket_comment_set_meta_keys', array(
			'comment',
			'attachments',
			'is_flagged',
			'type',
		));
		
		$filtered_meta = array();
		if( is_array($filtered_keys) && count($filtered_keys) > 0 ) {
			foreach( $filtered_keys as $key ) {
				if( isset($meta[$key]) ) {
					$filtered_meta[$key] = $meta[$key];
				}
			}
		}
		
		// Sanitize and save new meta
		$this->sanitize_meta( $filtered_meta, true );	
	}

	
	/**
	 * Sanitize the note meta
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function sanitize_meta( $meta=array(), $save=false ) {

		if( is_array($meta) && count($meta) > 0 ) {
			
			foreach( $meta as $key=>$value ) {
				
				$sanitized_value;
				
				switch( $key ) {
					
					case 'ticket_id':
					case 'post_id':
					case 'user_id':
					case 'time':
						$sanitized_value = intval( $value );
						break;
					
					case 'id':
					case 'is_flagged':
					case 'type':
						$sanitized_value = esc_attr( $value );
						break;
						
					case 'comment':
						$sanitized_value = wp_kses_post( $value );
						break;
						
					default:
						$sanitized_value = sanitize_text_field( $value );
						break;
				}
				
				$this->$key = $sanitized_value;
			}
			
			if( $save ) {
				$this->update_history();
			}
		}
	}
	
	
	/**
	 * Update the comment in the ticket's history
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function update_history() {

		$comments = get_post_meta( $this->get_post_id(), '_tops_ticket_comments', true );
		if( !is_array($comments) ) {
			$comments = array();	
		}
		$comments[$this->id] = (array) $this;
		update_post_meta( $this->get_post_id(), '_tops_ticket_comments', $comments );
	}
	

	/**
	 * Set the note id
	 *
	 * @access  public
	 * @return	counter
	 * @since   1.0.0
	 */
	 
	public function create_id() {
		
		$counter = intval( get_post_meta($this->get_post_id(), '_tops_ticket_counter', true) );
		if( !$counter ) {
			$counter = 0;
		}
		$counter++;
		update_post_meta( $this->get_post_id(), '_tops_ticket_counter', $counter );
		$note_id = $this->get_ticket_id().'_'.$counter;

		return $note_id;
	}


	/**
	 * Return the verified attribute
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_class_names( $class='' ) {
		
		$classes = array();

		$classes[] = 'tops-ticket-comment';
		$classes[] = 'tops-ticket-comment-'.$this->type;
		if( $this->is_flagged == 'yes' ) {
			$classes[] = 'tops-ticket-comment-flagged';
		}
		if( !$this->current_user_can_view() ) {
			$classes[] = 'tops-ticket-comment-hidden';
		}
	
		if( !empty($class) ) {
			if( !is_array($class) ) {
				$class = preg_split( '#\s+#', $class );
			}
			$classes = array_merge( $classes, $class );
		} else {
			// Ensure that we always coerce class to being an array.
			$class = array();
		}
	
		$classes = array_map( 'esc_attr', $classes );
	
		return apply_filters( 'tops_ticket_note_class', $classes, $class );
	}
	
	
	/**
	 * Check to see if the current user can view the note
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function current_user_can_view() {
		if( $this->get_ticket_user_id() == get_current_user_id() || current_user_can('edit_tops_tickets') ) {
			return true;
		}
	}

	
	/**
	 * Delete the note
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function delete( $id=false ) {
		
		if( $id ) {
			$this->id = esc_attr($id);
		}
		$comments = get_post_meta( $this->get_post_id(), '_tops_ticket_comments', true );
		unset( $comments[$this->id] );
		update_post_meta( $this->get_post_id(), '_tops_ticket_comments', $comments );
	}
	
	
	/**
	 * Return the tickets public attribute
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function is_public_ticket() {

		return ($this->type == 'public');
	}
	
}
