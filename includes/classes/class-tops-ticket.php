<?php

/**
 * TOPS Ticket base class
 *
 * @package     TOPS
 * @subpackage  Classes/TOPS Ticket
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/
class TOPS_Ticket {
	
	public $id;
	
	public $post_id;
	
	public $license;
	
	public $related_url;
	
	public $type;
	
	public $status;

	public $category;
	private $category_obj;

	public $user_id;
	private $user_obj;
	private $user_response_time;
	
	public $agent_id;
	private $agent_obj;
	private $agent_response_history;
	
	public $last_comment;
	public $last_comment_time;
	
	public $last_commenter_id;
	private $last_commenter_obj;
	
	public $last_updated_time;
	
	public $comments;


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
	 * Create a new ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function create( $data=array() ) {
		
		if( !isset($data['id']) ) {
			$data['id'] = $this->set_ticket_id();
		}
		$this->id = $data['id'];

		// Create the ticket post
		return $this->create_post( $data );
	}
	
	
	/**
	 * Create the ticket post
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function create_post( $data=array() ) {
		
		// Setup the new post data
		$ticket = array(
			'post_type'			=> 'tops_ticket',
			'post_name'			=> $this->id,
			'post_title'		=> isset($data['subject']) ? $data['subject'] : false,
			'post_status'		=> 'publish'
		);
		
		$post_id = wp_insert_post( $ticket, true );

		if( !is_wp_error($post_id) ) {
			
			$this->post_id = $post_id;
			
			if( !isset($data['status']) ) {
				$data['status'] = 'open';
			}
			
			if( !isset($data['type']) ) {
				$data['type'] = 'private';
			}
			
			$this->set_meta( $data );
			
			// Add the ticket ID right away
			update_post_meta( $post_id, '_tops_ticket_id', intval($this->id) );
			
			// Add the user ID right away
			if( !isset($data['user_id']) || $data['user_id'] != '' ) {
				$current_user = wp_get_current_user();
				$data['user_id'] = $current_user->ID;
			}
			update_post_meta( $post_id, '_tops_ticket_user_id', intval($data['user_id']) );
			
			// Add the agent ID right away
			if( !isset($data['agent_id']) || $data['agent_id'] != '' ) {
				if( !isset($data['category']) || $data['category'] != '' ) {
					$data['agent_id'] = $this->get_default_agent_id( $data['category'] );
				} else {
					$data['agent_id'] = 1;
				}
			}
			update_post_meta( $post_id, '_tops_ticket_agent_id', intval($data['agent_id']) );
			
			// Set the users response time
			$this->set_user_response_time();
			
			// Create the initial comment
			$this->add_comment( $data );
			
			// Set the ticket as read
			$this->set_as_read();
		}

		return $post_id;
	}
	
	
	/**
	 * Update a ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function update( $data=array() ) {
		
		if( isset($data['id']) ) {
			
			$this->get_meta( $data['id'] );
			$this->set_meta( $data );
		}
	}
	
	
	/**
	 * Setup the ticket
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function get_meta( $id, $type='' ) {
		
		if( $type == 'post_id' ) {
			$post_id = $id;
			$id = get_post_meta( $id, '_tops_ticket_id', true );
		} else {
			$post_id = $this->get_post_id( $id );
			$id = $id;
		}
		
		$post_custom = get_post_custom( $post_id );
		
		$filtered_keys = apply_filters('tops_ticket_filtered_get_meta_keys', array(
			'license',
			'related_url',
			'type',
			'status',
			'user_id',
			'user_response_time',
			'agent_id',
			'agent_response_history',
			'last_commenter_id',
			'last_comment_time',
			'comments',
		));

		$filtered_meta = array();
		if( is_array($filtered_keys) && count($filtered_keys) > 0 ) {
			foreach( $filtered_keys as $key ) {
				if( isset($post_custom['_tops_ticket_'.$key]) ) {
					$filtered_meta[$key] = maybe_unserialize( $post_custom['_tops_ticket_'.$key][0] );
				}
			}
		}
		
		// Add additional meta
		$filtered_meta['id'] = $id;
		$filtered_meta['post_id'] = $post_id;
		$filtered_meta['time'] = get_the_time( 'U', $post_id );
		
		// Add the ticket category
		$terms = get_the_terms( $post_id, 'tops_category' );
		if( is_array($terms) && count($terms) > 0 ) {
			$filtered_meta['category_obj'] = $terms[0];
			$filtered_meta['category'] = $terms[0]->slug;
		}
		
		// Add the individual ticket object attributes
		if( is_array($filtered_meta) && count($filtered_meta) > 0 ) {
			foreach( $filtered_meta as $attr=>$value ) {
				$this->$attr = $value;
			}
		}	
	}
	
	
	/**
	 * Setup the ticket
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function set_meta( $meta=array() ) {
		
		$defaults = (array) $this;
		$meta = wp_parse_args( $meta, $defaults );
		
		$filtered_keys = apply_filters('tops_ticket_filtered_set_meta_keys', array(
			'license',
			'related_url',
			'type',
			'status',
			'category',
			'agent_id',
		));
		
		$filtered_meta = array();
		if( is_array($filtered_keys) && count($filtered_keys) > 0 ) {
			foreach( $filtered_keys as $key ) {
				if( isset($meta[$key]) ) {
					$filtered_meta[$key] = $meta[$key];
				}
			}
		}
						
		// Add the ticket category
		if( isset($filtered_meta['category']) ) {
			wp_set_object_terms( $this->post_id, $filtered_meta['category'], 'tops_category' );
		}
				
		if( is_array($filtered_meta) && count($filtered_meta) > 0 ) {
			foreach( $filtered_meta as $key=>$value ) {
								
				$sanitized_value;
				
				switch( $key ) {
					
					case 'agent_id':
						$sanitized_value = intval( $value );
						break;
						
					case 'type':
					case 'status':
					case 'category':
						$sanitized_value = esc_attr( $value );
						break;
						
					case 'related_url':
						$sanitized_value = esc_url_raw( $value );
						break;
						
					default:
						$sanitized_value = sanitize_text_field( $value );
						break;
				}

				// Update the post meta
				update_post_meta( $this->post_id, '_tops_ticket_'.$key, $sanitized_value );
				$this->$key = $sanitized_value;
			}
		}	
	}
	
	
	/**
	 * Add the default agent id
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	 
	private function get_default_agent_id( $category_slug ) {
		$category = get_term_by( 'slug', $category_slug, 'tops_category' );
		return get_term_meta( $category->term_id, 'tops_default_ticket_agent_id', true );
	}
	
	
	/**
	 * Add a note
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function add_note( $args=array() ) {
		
		if( isset($args['comment']) && $args['comment'] != '' ) {
			
			// Create the new comment		
			$data['ticket_id'] = $this->id;
			$data['ticket_user_id'] = $this->user_id;
			$data['post_id'] = $this->post_id;
			$data['agent_id'] = $this->get_agent_id();
			$data['comment'] = $args['comment'];
			$data['attachments'] = $args['attachments'];
			
			$note = new TOPS_Ticket_Note();
			return $note->create( $data );
		}
	}
	
	
	/**
	 * Add a comment
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function add_comment( $args=array() ) {
		
		if( isset($args['comment']) && $args['comment'] != '' ) {
			
			$current_user_id = get_current_user_id();
			
			// Create the new comment		
			$data['ticket_id'] = $this->id;
			$data['ticket_user_id'] = $this->user_id;
			$data['post_id'] = $this->post_id;
			$data['agent_id'] = $this->get_agent_id();
			$data['type'] = isset($args['type']) ? $args['type'] : 'public';
			$data['comment'] = $args['comment'];
			$data['attachments'] = $args['attachments'];
			
			// Open the ticket with each comment
			$this->status = 'open';
			update_post_meta( $this->post_id, '_tops_ticket_status', 'open' );
			
			// Update the last comment time
			update_post_meta( $this->post_id, '_tops_ticket_last_commenter_id', $current_user_id );
			update_post_meta( $this->post_id, '_tops_ticket_last_comment_time', current_time('timestamp', 1) );
			
			// Reset the has read array
			update_post_meta( $this->post_id, '_tops_ticket_read', $current_user_id );
			
			$comment = new TOPS_Ticket_Comment();
			return $comment->create( $data );
		}
	}
	
	
	/**
	 * Get the next ticket id
	 *
	 * @access  private
	 * @return	id
	 * @since   1.0.0
	 */
	private function set_ticket_id() {

		$counter = get_option( 'tops_ticket_counter', 999 );
		$counter++;
		$this->id = $counter;
		update_option( 'tops_ticket_counter', $counter );
		
		return $counter;
	}
	
	
	/**
	 * Setup ticket data by ticket id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_post_id( $id=false ) {
		
		$id = $id ? $id : $this->id;
		
		$args = array(
			'posts_per_page' => 1,
			'post_type' => 'tops_ticket',
			'meta_query' => array(
				array(
					'key'     => '_tops_ticket_id',
					'value'   => $id,
					'type'    => 'numeric',
				),
			)
		);
		
		if( $posts = get_posts($args) ) {
			return $posts[0]->ID;
		}
	}

	
	/**
	 * Set the user response time
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function set_user_response_time() {
		
		if( !$this->user_response_time ) {
			$this->user_response_time = current_time('timestamp', 1);
			update_post_meta( $this->get_post_id(), '_tops_ticket_user_response_time', $this->user_response_time );
		}
	}
	
	
	/**
	 * Delete the user response time
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function delete_user_response_time() {
		
		if( $this->user_response_time ) {
			$this->user_response_time = false;
			delete_post_meta( $this->get_post_id(), '_tops_ticket_user_response_time' );
		}
	}
	
	
	/**
	 * Close the ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function close() {
		$this->status = 'closed';
		update_post_meta( $this->post_id, '_tops_ticket_status', $this->status );
	}
	
	
	/**
	 * Open the ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function open() {
		$this->status = 'open';
		update_post_meta( $this->post_id, '_tops_ticket_status', $this->status );
	}
	
	
	/**
	 * Get the url
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function url( $echo=false ) {
		
		$url = get_permalink( $this->post_id );
		
		if( $echo ) {
			echo $url;
		} else {
			return $url;
		}
	}
	
	
	/**
	 * Get the title
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function title( $echo=false ) {
		
		$title = get_the_title( $this->post_id );
		
		if( $echo ) {
			echo $title;
		} else {
			return $title;
		}
	}
		
	
	/**
	 * Get the related url
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function related_url( $echo=false ) {
		
		$url = $this->related_url;
		
		if( $echo ) {
			echo $url;
		} else {
			return $url;
		}
	}
	
	
	/**
	 * Get the related url link
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function related_url_link( $echo=false ) {
		
		$link = '<a href="'.esc_url_raw($this->related_url()).'" target="_blank">'.esc_url($this->related_url()).'</a>';
		
		if( $echo ) {
			echo $link;
		} else {
			return $link;
		}
	}
	
	
	/**
	 * Make sure the category is setup
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function check_category() {
		
		if( !$this->category_obj ) {
			$term = get_term_by( 'slug', $this->category, 'tops_category' );
			$this->category_obj = $term;
		}
		
		if( is_object($this->category_obj) ) {
			return $this->category_obj;
		}
	}
	
	
	/**
	 * Return the category name
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function category_name( $echo=false ) {
		
		if( $category = $this->check_category() ) {
			
			$name = $category->name;
			
			if( $echo ) {
				echo $name;
			} else {
				return $name;
			}
		}
	}
	
	
	/**
	 * Return the category thumbnail
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function category_thumbnail( $echo=false ) {
		
		if( $category = $this->check_category() ) {
			
			$thumbnail = TOPS()->categories->thumbnail( $this->category_obj->term_id );
			
			if( $echo ) {
				echo $thumbnail;
			} else {
				return $thumbnail;
			}
		}
	}
	
	
	/**
	 * Return the category url
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function category_url( $echo=false ) {
		
		if( $category = $this->check_category() ) {
			
			$url = TOPS()->tickets->get_tickets_page_url( false, array('category'=>$category->slug) );
			
			if( $echo ) {
				echo $url;
			} else {
				return $url;
			}
		}
	}
	
	
	/**
	 * Return the ticket category link
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function category_link( $echo=false ) {
		
		if( $category = $this->check_category() ) {
			
			$link = '<a href="'.$this->category_url().'">'.$this->category_name().'</a>';
			
			if( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
	}
	
	
	/**
	 * Make sure the user is setup
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function check_user() {
		
		if( !$this->user_id ) {
			$this->user_id = get_post_meta( $this->get_post_id(), '_tops_ticket_user_id', true );
		}
		
		if( !$this->user_obj ) {
			$user = get_userdata( $this->user_id );
			$this->user_obj = $user;
		}
		
		if( is_object($this->user_obj) ) {
			return $this->user_obj;
		}
	}
	
	
	/**
	 * Return the user id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_id( $echo=false ) {
		
		if( $user = $this->check_user() ) {
			
			$id = $this->user_id;
			
			if( $echo ) {
				echo $id;
			} else {
				return $id;
			}
		}
	}
	
	
	/**
	 * Return the user name
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_name( $echo=false ) {

		if( $user = $this->check_user() ) {
			
			$name = $user->display_name;
			
			if( $echo ) {
				echo $name;
			} else {
				return $name;
			}
		}
	}
	
	
	/**
	 * Return the user avatar
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_avatar( $size=100, $echo=false ) {

		if( $user = $this->check_user() ) {
			
			$avatar = get_avatar($user->ID, $size);
			
			if( $echo ) {
				echo $avatar;
			} else {
				return $avatar;
			}
		}
	}
	
	
	/**
	 * Return the user url
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_url( $echo=false ) {
		
		if( $user = $this->check_user() ) {
			
			$url = TOPS()->tickets->get_tickets_page_url( array('user'=>$user->ID) );
			
			if( $echo ) {
				echo $url;
			} else {
				return $url;
			}
		}
	}
	
	
	/**
	 * Return the user link
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_link( $echo=false ) {
		
		if( $user = $this->check_user() ) {
			
			$link = '<a href="'.$this->get_user_url().'">'.$this->get_user_name().'</a>';
			
			if( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
	}
	
	
	/**
	 * Get the user email
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_email( $echo=false ) {
		
		if( $user = $this->check_user() ) {
			
			$email = $user->user_email;
			
			if( $echo ) {
				echo $email;
			} else {
				return $email;
			}
		}
	}
	
	
	/**
	 * Get the user email link
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_user_email_link( $echo=false ) {
		
		if( $user = $this->check_user() ) {
			
			$link = '<a href="mailto:'.esc_url_raw($this->get_user_email()).'">'.esc_url_raw($this->get_user_email()).'</a>';
			
			if( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
	}
	
	
	/**
	 * Make sure the agent is setup
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function check_agent() {
		
		if( !$this->agent_id ) {
			$this->agent_id = get_post_meta( $this->get_post_id(), '_tops_ticket_agent_id', true );
		}
		
		if( !$this->agent_obj ) {
			$user = get_userdata( $this->agent_id );
			$this->agent_obj = $user;
		}
		
		if( is_object($this->agent_obj) ) {
			return $this->agent_obj;
		}
	}
	
	
	/**
	 * Return the agent id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_agent_id( $echo=false ) {
		
		if( $agent = $this->check_agent() ) {
			
			$id = $this->agent_id;
			
			if( $echo ) {
				echo $id;
			} else {
				return $id;
			}
		}
	}
	
	
	/**
	 * Return the agent name
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_agent_name( $echo=false ) {

		if( $agent = $this->check_agent() ) {

			$name = $agent->display_name;
			
			if( $echo ) {
				echo $name;
			} else {
				return $name;
			}
		}
	}
	
	
	/**
	 * Return the agent avatar
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_agent_avatar( $size=false, $echo=false ) {

		if( $agent = $this->check_agent() ) {
			
			$avatar = get_avatar( $agent->ID, $size );
			
			if( $echo ) {
				echo $avatar;
			} else {
				return $avatar;
			}
		}
	}
	
	
	/**
	 * Return the agent url
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_agent_url( $echo=false ) {
		
		if( $agent = $this->check_agent() ) {
			
			$url = TOPS()->tickets->get_tickets_page_url( array('user'=>$agent->ID) );
			
			if( $echo ) {
				echo $url;
			} else {
				return $url;
			}
		}
	}
	
	
	/**
	 * Return the agent link
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_agent_link( $echo=false ) {
		
		if( $agent = $this->check_agent() ) {
			
			$link = '<a href="'.$this->get_agent_url().'">'.$this->get_agent_name().'</a>';
			
			if( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
	}
	
	
	/**
	 * Get the agent email
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_agent_email( $echo=false ) {
		
		if( $agent = $this->check_agent() ) {
			
			$email = $agent->user_email;
			
			if( $echo ) {
				echo $email;
			} else {
				return $email;
			}
		}
	}
	
	
	/**
	 * Get the agent email link
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_agent_email_link( $echo=false ) {
		
		if( $user = $this->check_agent() ) {
			
			$link = '<a href="mailto:'.esc_url_raw($this->get_agent_email()).'">'.esc_url_raw($this->get_agent_email()).'</a>';
			
			if( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
	}
	
	
	/**
	 * Return an array of comment
	 *
	 * @access  public
	 * @return	$last_comment
	 * @since   1.0.0
	 */
	public function get_comments() {
		
		if( !$this->comments ) {
			$comments = get_post_meta( $this->get_post_id(), '_tops_ticket_comments', true );
			$this->comments = array_reverse( $comments );
		}

		return $this->comments;
	}
	
	
	/**
	 * Return the last comment
	 *
	 * @access  public
	 * @return	$last_comment
	 * @since   1.0.0
	 */
	public function get_last_comment() {
		
		if( !$this->last_comment ) {
			$comments = $this->get_comments();
			$this->last_comment = end( $comments );
		}

		return $this->last_comment;
	}
	
	
	/**
	 * Return the last comment string
	 *
	 * @access  public
	 * @return	$last_comment
	 * @since   1.0.0
	 */
	public function get_last_comment_string() {

		$last_comment = $this->get_last_comment();
		return $last_comment['comment'];
	}
	
	
	/**
	 * Make sure the last commenter is setup
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	private function check_last_commenter() {

		if( !$this->last_commenter_id ) {
			$last_comment = $this->get_last_comment();
			$this->last_commenter_id = $last_comment['user_id'];
		}
		
		if( !$this->last_commenter_obj ) {
			$user = get_userdata( $this->last_commenter_id );
			$this->last_commenter_obj = $user;
		}
		
		if( is_object($this->last_commenter_obj) ) {
			return $this->last_commenter_obj;
		}
	}
	
	
	/**
	 * Return the last commenter id
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_last_commenter_id( $echo=false ) {
		
		if( $last_commenter = $this->check_last_commenter() ) {
			
			$id = $this->last_commenter_id;
			
			if( $echo ) {
				echo $id;
			} else {
				return $id;
			}
		}
	}
	
	
	/**
	 * Return the last commenter name
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_last_commenter_name( $echo=false ) {

		if( $last_commenter = $this->check_last_commenter() ) {
			
			$name = $last_commenter->display_name;
			
			if( $echo ) {
				echo $name;
			} else {
				return $name;
			}
		}
	}
	
	
	/**
	 * Return the last commenter avatar
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_last_commenter_avatar( $size=false, $echo=false ) {

		if( $last_commenter = $this->check_last_commenter() ) {
			
			$avatar = get_avatar( $last_commenter->ID, $size );
			
			if( $echo ) {
				echo $avatar;
			} else {
				return $avatar;
			}
		}
	}
	
	
	/**
	 * Return the last commenter url
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_last_commenter_url( $echo=false ) {
		
		if( $last_commenter = $this->check_last_commenter() ) {
			
			$url = TOPS()->tickets->get_tickets_page_url( array('user'=>$last_commenter->ID) );
			
			if( $echo ) {
				echo $url;
			} else {
				return $url;
			}
		}
	}
	
	
	/**
	 * Return the last commenter link
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_last_commenter_link( $echo=false ) {
		
		if( $last_commenter = $this->check_last_commenter() ) {
			
			$link = '<a href="'.$this->get_last_commenter_url().'">'.$this->get_last_commenter_name().'</a>';
			
			if( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
	}
	
	
	/**
	 * Return the status
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function status_label( $echo=false ) {
				
		$status = ucwords( $this->status );
		
		if( $echo ) {
			echo $status;
		} else {
			return $status;
		}
	}
	
	
	/**
	 * Return the created date
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function created_date( $format=false, $echo=false ) {
		
		$format = $format ? $format : get_option( 'date_format' );
		
		$date = get_the_time( $format, $this->post_id );
		
		if( $echo ) {
			echo $date;
		} else {
			return $date;
		}
	}
	
	
	/**
	 * Return the response time
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function response_time( $echo=false ) {
		
		$curr_timestamp = current_time('timestamp', 1);
		$avg_timestamp = $this->user_response_time;
		
		if( $this->agent_response_history ) {
			$history = $this->agent_response_history;
			$total_history = 0;
			if( is_array($history) && count($history) > 0 ) {
				foreach( $history as $seconds ) {
					$total_history += $seconds;
				}
				$avg_time = ceil( $seconds/count($history) );
				$avg_timestamp = $curr_timestamp-$avg_time;
			}
		}

		$time = human_time_diff( $avg_timestamp, $curr_timestamp );
		
		if( $echo ) {
			echo $time;
		} else {
			return $time;
		}
	}
	
	
	/**
	 * Mark the ticket as private
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_as_private() {
		$this->type = 'private';
		update_post_meta( $this->get_post_id(), '_tops_ticket_type', 'private' );
	}
	
	
	/**
	 * Mark the ticket as public
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_as_public() {
		$this->type = 'public';
		update_post_meta( $this->get_post_id(), '_tops_ticket_type', 'public' );
	}
	
	
	/**
	 * Mark the ticket as starred by a user
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_as_starred() {
		$starred_array = get_post_meta( $this->get_post_id(), '_tops_ticket_starred' );
		if( !in_array(get_current_user_id(), $starred_array) ) {
			add_post_meta( $this->get_post_id(), '_tops_ticket_starred', get_current_user_id() );
		}
	}
	
	
	/**
	 * Mark the ticket as unstarred by a user
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_as_unstarred() {
		delete_post_meta( $this->get_post_id(), '_tops_ticket_starred', get_current_user_id() );
	}
	
	
	/**
	 * Return the starred boolean
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function is_starred() {
		
		$starred_array = get_post_meta( $this->get_post_id(), '_tops_ticket_starred' );
		$starred = in_array( get_current_user_id(), $starred_array );
		
		return  apply_filters( 'tops_is_ticket_starred', $starred, $starred_array );
	}
	
	
	/**
	 * Mark the ticket as read by a user
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_as_read() {
		$read_array = get_post_meta( $this->get_post_id(), '_tops_ticket_read' );
		if( !in_array(get_current_user_id(), $read_array) ) {
			add_post_meta( $this->get_post_id(), '_tops_ticket_read', get_current_user_id() );
		}
	}
	
	
	/**
	 * Mark the ticket as unread by a user
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function set_as_unread() {
		delete_post_meta( $this->get_post_id(), '_tops_ticket_read', get_current_user_id() );
	}
	
	
	/**
	 * Return the unread boolean
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function is_unread() {
		
		$read_array = get_post_meta( $this->get_post_id(), '_tops_ticket_read' );
		$unread = !in_array( get_current_user_id(), $read_array );
		return  apply_filters( 'tops_is_ticket_unread', $unread, $read_array );
	}
	
	
	/**
	 * Return the read boolean
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function is_read() {
		
		$read_array = get_post_meta( $this->get_post_id(), '_tops_ticket_read' );
		$read = in_array( get_current_user_id(), $read_array );
		return  apply_filters( 'tops_is_ticket_read', $read, $read_array );
	}
	
	
	/**
	 * Return the last updated time
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function last_updated_time( $echo=false ) {
		
		$last_comment = $this->get_last_comment();
		$time = human_time_diff( $last_comment['time'], current_time('timestamp', 1) );
		
		if( $echo ) {
			echo $time;
		} else {
			return $time;
		}
	}
	
	
	/**
	 * Return the comment count
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function comment_count( $echo=false ) {
		
		$comment_count = count($this->comments);
		
		if( $echo ) {
			echo $comment_count;
		} else {
			return $comment_count;
		}
	}
	
	
	/**
	 * Return the verified attribute
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function ticket_class( $class='' ) {
		return 'class="'.join( ' ', $this->get_ticket_class($class) ).'"';
	}
	
	
	/**
	 * Return the verified attribute
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_ticket_class( $class='' ) {
		
		$classes = array();
	
		$classes[] = 'tops-ticket-'.$this->type;
		$classes[] = 'tops-ticket-'.$this->status;
		
		if( $this->is_unread() ) {
			$classes[] = 'tops-ticket-unread';
		} else {
			$classes[] = 'tops-ticket-read';
		}
		if( $this->is_starred() ) {
			$classes[] = 'tops-ticket-starred';
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
	
		return apply_filters( 'tops_ticket_class', $classes, $class );
	}
	
	
	/**
	 * Return the verified attribute
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function verified() {
		
		return false;
	}
	
	
	/**
	 * Check if a user can view this ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function user_can_view_ticket( $user_id=false ) {
		
		if( $this->type == 'public' ) {
			return true;
		} else {
			$user_id = $user_id ? $user_id : get_current_user_id();
			if( $this->user_id == $user_id || $this->agent_id == $user_id ) {
				return true;
			} elseif( user_can($user_id, 'edit_tops_tickets') && TOPS()->tickets->global_agent_access() ) {
				return true;
			}
		}
		
	}

}
