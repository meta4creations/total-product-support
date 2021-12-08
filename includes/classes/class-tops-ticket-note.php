<?php

/**
 * TOPS ticket note class
 *
 * @package     TOPS
 * @subpackage  Classes/TOPS Ticket Note
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/
class TOPS_Ticket_Note {	
	
	public $id;

	public $comment;

	public $ticket_id;

	public $post_id;
	
	public $time;
	
	public $user_id;
	
	public $ticket_user_id;
	
	public $attachments;
	

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
	 * Create a new note
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function create( $data=array() ) {
		
		// Get the ticket and post ids
		if( isset($data['post_id']) ) {	
			$this->post_id = intval($data['post_id']);
			$this->ticket_id = isset($data['ticket_id']) ? intval($data['ticket_id']) : $this->get_ticket_id();
		} elseif( isset($data['ticket_id']) ) {
			$this->ticket_id = intval($data['ticket_id']);
			$this->post_id = isset($data['post_id']) ? intval($data['post_id']) : $this->get_post_id();
		}
		
		// Set the ticket user id
		$this->ticket_user_id = isset($data['ticket_user_id']) ? intval($data['ticket_user_id']) : $this->get_ticket_user_id();
		
		// Get the user id
		if( !isset($data['user_id']) || $data['user_id'] == '' ) {
			$current_user = wp_get_current_user();
			if( $current_user->ID != 0 ) {
				$this->user_id = $current_user->ID;
			}
		}
		
		// Create the note ID
		$this->id = $this->create_id();
		
		// Set the created time
		$this->time = current_time('timestamp', 1);
		
		// Set additional data
		$this->set_meta( $data );
		
		return $this->id;
	}
	
	
	/**
	 * Update a note
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function update( $data=array() ) {
		if( $this->id ) {			
			$this->set_meta( $data );
		}
	}


	/**
	 * Get the note meta
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	 
	private function get_meta() {
		
		$notes = get_post_meta( $this->get_post_id(), '_tops_ticket_notes', true );
		
		// Find the ticket history position
		if( is_array($notes) && count($notes) > 0 ) {
			foreach( $notes as $i=>$meta ) {
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
		
		$filtered_keys = apply_filters('tops_ticket_note_set_meta_keys', array(
			'comment',
			'attachments',
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
					case 'ticket_user_id':
					case 'post_id':
					case 'user_id':
					case 'time':
						$sanitized_value = intval( $value );
						break;
					
					case 'id':
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
	 * Update the note in the ticket's history
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function update_history() {

		$notes = get_post_meta( $this->get_post_id(), '_tops_ticket_notes', true );
		if( !is_array($notes) ) {
			$notes = array();	
		}
		$notes[$this->id] = (array) $this;
		update_post_meta( $this->get_post_id(), '_tops_ticket_notes', $notes );
	}
	
	
	/**
	 * Return post id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_post_id() {
		
		if( $this->post_id ) {
			
			return $this->post_id;
		
		} elseif( $ticket_id = $this->get_ticket_id() ) {
			$args = array(
				'posts_per_page' => 1,
				'post_type' => 'tops_ticket',
				'meta_query' => array(
					array(
						'key'     => '_tops_ticket_id',
						'value'   => $ticket_id,
						'type'    => 'numeric',
					),
				)
			);
						
			if( $posts = get_posts($args) ) {
				$this->post_id = $posts[0]->ID;
				return $this->post_id;
			}
		}
	}
	
	
	/**
	 * Return ticket id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_ticket_id() {
		
		if( $this->ticket_id ) {
			return $this->ticket_id;
		
		} elseif( $this->post_id ) {
			$this->ticket_id = get_post_meta( $this->post_id, '_tops_ticket_id', true );
			return $this->ticket_id;
		
		} elseif( $this->id ) {
			$parts = explode( '_', $this->id );
			$this->ticket_id = $parts[0];
			return $this->ticket_id;
		}	
	}
	
	
	/**
	 * Return ticket title
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_ticket_title() {
		
		return get_the_title( $this->get_post_id() );
	}
	
	
	/**
	 * Return ticket user id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_ticket_user_id() {
		
		return get_post_meta( $this->get_post_id(), '_tops_ticket_user_id', true );
	}


	/**
	 * Set the note id
	 *
	 * @access  public
	 * @return	counter
	 * @since   1.0.0
	 */
	 
	public function create_id() {
		
		$counter = intval( get_post_meta($this->get_post_id(), '_tops_ticket_note_counter', true) );
		if( !$counter ) {
			$counter = 0;
		}
		$counter++;
		update_post_meta( $this->get_post_id(), '_tops_ticket_note_counter', $counter );
		$note_id = $this->get_ticket_id().'_note_'.$counter;

		return $note_id;
	}
	
	
	/**
	 * Return the verified attribute
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function class_names( $class='' ) {
		return 'class="'.join( ' ', $this->get_class_names($class) ).'"';
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
		$classes[] = 'tops-ticket-note';
	
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
		if( current_user_can('edit_tops_tickets') ) {
			return true;
		}
	}
	
	
	/**
	 * Return the public response
	 *
	 * @access  public
	 * @return  $response
	 * @since   1.0.0
	 */
	 
	public function public_response( $echo=false ) {		
		
		$response = __('Sorry, you do not have access to this comment', 'total-product-support');
		if( $echo ) {
			echo $response;
		} else {
			return $response;
		} 
	}
	

	/**
	 * Return the category name
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function category_name( $echo=false ) {
		
		return 'Category';
	}
	
	
	/**
	 * Return the user id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_id( $echo=false ) {
		
		if( $echo ) {
			echo $this->user_id;
		} else {
			return $this->user_id;
		}
	}
	
	
	/**
	 * Return the comment username
	 *
	 * @access  public
	 * @return	time
	 * @since   1.0.0
	 */
	public function get_user_name() {

		if( $user_info = get_userdata( $this->user_id ) ) {
			return $user_info->display_name;
		}
	}
	
	
	/**
	 * Return the human comment time
	 *
	 * @access  public
	 * @return	time
	 * @since   1.0.0
	 */
	public function human_time() {
		
		$current_time = current_time('timestamp', 1);
		if( ($current_time-$this->time) < 60 ) {
			return __('Just now', 'total-product-support');
		}
		
		$time_diff = human_time_diff($this->time, $current_time);	
		return sprintf(__('%s ago', 'total-product-support'), $time_diff);
	}
	
	
	/**
	 * Return the formatted comment
	 *
	 * @access  public
	 * @return	comment
	 * @since   1.0.0
	 */
	public function get_comment( $email=false ) {
		
		if( $email || $this->current_user_can_view() ) {
			return do_shortcode(wpautop(convert_chars(wptexturize($this->comment))));
		} else {
			return $this->public_response();
		}
	}
	
	
	/**
	 * Get the url
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function url( $echo=false ) {
		
		$url = get_permalink( $this->get_post_id() ).'#'.$this->id;
		
		if( $echo ) {
			echo $url;
		} else {
			return $url;
		}
	}
	
	
	/**
	 * Delete the note
	 *
	 * @access  public
	 * @return 	success
	 * @since   1.0.0
	 */
	public function delete( $id=false ) {
		
		if( $id ) {
			$this->id = esc_attr($id);
		}
		$notes = get_post_meta( $this->get_post_id(), '_tops_ticket_notes', true );
		unset( $notes[$this->id] );
		update_post_meta( $this->get_post_id(), '_tops_ticket_notes', $notes );
	}
	
	
	/**
	 * Return the attachments
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_attachments() {
		if( $this->attachments && $this->attachments != '' ) {
			
			$attachments = html_entity_decode($this->attachments);
			$attachments = json_decode( $attachments, true );

			if( is_array($attachments) && count($attachments) > 0 ) {
				$list = '';
				foreach( $attachments as $i=>$attachment ) {
					
					$attachment['comment_id'] = $this->id;
					switch( $attachment['type'] ) {
						case 'image':
							$attachment['icon'] = '<i class="fa fa-picture-o" aria-hidden="true"></i>';
							break;
					}
					
					$list .= tops_template( 'comment_attachment', $attachment );

				}
				return $list;
			}
		}
	}
	
	
	/**
	 * Delete an attachment
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function delete_attachment( $attachment_id ) {
		if( $this->attachments && $this->attachments != '' ) {
			
			$attachments = html_entity_decode($this->attachments);
			$attachments = json_decode( $attachments, true );
			
			$update_comment = false;
			$updated_attachments = array();

			if( is_array($attachments) && count($attachments) > 0 ) {
				foreach( $attachments as $i=>$attachment ) {
					if( $attachment['id'] == $attachment_id ) {
						$update_comment = true;
					} else {
						$updated_attachments[] = $attachment;
					}
				}
			}
			
			if( $update_comment ) {
				if( count($updated_attachments) == 0 ) {
					$this->attachments = false;
				} else {
					$this->attachments = json_encode( $updated_attachments );
				}
				$this->update_history();
			}
		}
	}

}
