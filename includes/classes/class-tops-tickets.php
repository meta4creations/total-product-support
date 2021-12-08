<?php

/**
 * TOPS Tickets class
 *
 * @package     TOPS
 * @subpackage  Classes/TOPS Tickets
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/

class TOPS_Tickets {
	
	private $open_tickets;
	
	private $closed_tickets;
	
	private $starred_tickets;
	
	private $unread_tickets;
	
	private $read_tickets;

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function __construct() {
		
		add_action( 'tops_init', array( $this, 'set_ticket_as_read_for_user' ), 10, 2 );
		
		add_filter( 'tops_get_ticket_query_args', array( $this, 'filter_tickets_by_user' ) );
		add_filter( 'tops_get_ticket_query_args', array( $this, 'filter_tickets_by_filter' ), 10, 2 );
		add_filter( 'tops_get_ticket_query_args', array( $this, 'filter_tickets_query_debug' ), 10, 2 );
		
		add_action( 'pre_get_posts', array( $this, 'limit_user_attachments') );	
	}
	
	
	/**
	 * Check for global agent access to private tickets
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function global_agent_access() {	
		if( current_user_can('manage_tops_ticket_settings') ) {
			return true;	
		}
		return false;
	}
	
	
	/**
	 * Limit media panel uploads to current user
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function limit_user_attachments( $wp_query_obj ) {
		
		global $pagenow;

    $is_attachment_request = ($wp_query_obj->get('post_type')=='attachment');

    if( !$is_attachment_request ) {
	    return;
    }

    if( !is_user_logged_in() ) {
	    return;
    }
    
    if( !in_array($pagenow, array('upload.php', 'admin-ajax.php')) ) {
	    return;
    }
    
    if( !current_user_can('delete_pages') ) {
    	$wp_query_obj->set('author', get_current_user_id() );
    }

    return;
	}
	
	
	/**
	 * Set a ticket as read when a user visits the ticket
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	
	public function set_ticket_as_read_for_user() {
		
		if( get_post_type() == 'tops_ticket' ) {
			
			$ticket = $this->get_ticket( get_the_id(), 'post_id' );
			if( $ticket->get_agent_id() != get_current_user_id() ) {
				$ticket->set_as_read();
			}
		}
	}

	
	/**
	 * Debug the query,
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function filter_tickets_query_debug( $args, $filters ) {
		if( isset($filters['debug_query']) ) {
			echo '<pre>';print_r($args);echo '</pre>';
		}
		return $args;
	}
	
	
	/**
	 * Return an array of tickets,
	 * sorted by unread items first
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_tickets( $filters=array() ) {

		$args = array(
			'posts_per_page' => -1,
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'meta_key' => '_tops_ticket_last_comment_time',
			'post_type' => 'tops_ticket',
			'meta_query' => array(
				'relation' => 'AND',
				'user_ids' => array(
					'relation' => 'OR',
					'agent_id' => array(
						'key' => '_tops_ticket_agent_id',
						'value' => get_current_user_id()
					),
					'user_id' => array(
						'key' => '_tops_ticket_user_id',
						'value' => get_current_user_id()
					)
				)
			)
		);

		$args = apply_filters( 'tops_get_ticket_query_args', $args, $filters );
		
		return get_posts( $args );
	}

	
	/**
	 * Return ticket counts
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_ticket_counts( $filters=array() ) {
		
		$tickets = $this->get_tickets( $filters );
		
		return count( $tickets );
	}
	
	
	/**
	 * Return unread tickets
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_unread_tickets( $filters=array() ) {
		
		$read_filters = array(
			'is_read' => true,
		);
		$read_filters = wp_parse_args( $read_filters, $filters );
		$read_tickets = $this->get_tickets( $read_filters );
		
		$tickets = $this->get_tickets( $filters );
		$tickets = array_udiff( $tickets, $read_tickets, 'tops_remove_duplicate_posts' );
		
		return $tickets;
	}
	
	
	/**
	 * Return unread ticket ids
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_unread_ticket_ids( $filters=array() ) {
		
		$unread_tickets = $this->get_unread_tickets();
		$unread_ticket_ids = array();
		if( is_array($unread_tickets) && count($unread_tickets) > 0 ) {
			foreach( $unread_tickets as $i=>$unread_ticket ) {
				$unread_ticket_ids[] = $unread_ticket->ID;
			}
		}
		
		return $unread_ticket_ids;
	}
	
	
	/**
	 * Return unread ticket counts
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_unread_ticket_counts( $filters=array() ) {

		return count( $this->get_unread_tickets() );
	}
	
	
	/**
	 * Filter the tickets by filter
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function filter_tickets_by_filter( $args, $filters ) {

		if( is_array($filters) && count($filters) > 0 ) {
			foreach( $filters as $filter=>$value ) {
				
				switch( $filter ) {
					
					case 'order':
						$args['order'] = $value;
						break;
					
					case 'category':
						$args['tax_query']['category'] = array(
							'taxonomy' => 'tops_category',
							'field'    => 'slug',
							'terms'    => $value,
						);
						break;
						
					case 'status':
						$args['meta_query']['status'] = array(
							'key' 	=> '_tops_ticket_status',
							'value' => $value
						);
						$args['meta_query']['user_ids'] = array(
							'relation' => 'OR',
							'agent_id' => array(
								'key' => '_tops_ticket_agent_id',
								'value' => get_current_user_id()
							),
							'user_id' => array(
								'key' => '_tops_ticket_user_id',
								'value' => get_current_user_id()
							)
						);
						break;
						
					case 'is_starred':
						$args['meta_query']['is_starred'] = array(
							'key' => '_tops_ticket_starred',
							'value' => get_current_user_id(),
							'compare' => 'IN'
						);
						break;
						
					case 'is_read':
						$args['meta_query']['is_read'] = array(
							'key' => '_tops_ticket_read',
							'value' => get_current_user_id(),
							'compare' => 'IN'
						);
						break;

					case 'after':
						$args['meta_query']['after'] = array(
							'key' 		=> '_tops_ticket_last_comment_time',
							'value' 	=> $value,
							'compare'	=> '>=',
							'type'		=> 'NUMERIC'
						);
						break;
						
					case 'before':
						$args['meta_query']['before'] = array(
							'key' 		=> '_tops_ticket_last_comment_time',
							'value' 	=> $value,
							'compare'	=> '<',
							'type'		=> 'NUMERIC'
						);
						break;
						
					case 'type':
						
						$args['meta_query']['type'] = array(
							'key' => '_tops_ticket_type',
							'value' => $value
						);
						
						if( $value == 'public' ) {
							$args['meta_query']['relation'] = 'OR';
							unset($args['meta_query']['user_ids']);
						}
						
						break;
						
					case 'is_mine':
						unset($args['meta_query']['type']);
						break;
						
					case 'all_users':
						if( $this->global_agent_access() ) {
							unset($args['meta_query']['user_ids']);
						}
						break;
				}				
			}
		}
	
		return $args;
	}
	
	
	/**
	 * Filter the tickets by user
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function filter_tickets_by_user( $args ) {
		
		if( current_user_can('manage_tops_ticket_settings') ) {
			
			unset($args['meta_query']['type']);
		
		} elseif( current_user_can('edit_tops_tickets') ) {
			
			unset($args['meta_query']['type']);
			
			if( !$this->global_agent_access() ) {
				$args['meta_query']['user_ids'] = array(
					'relation' => 'OR',
					'agent_id' => array(
						'key' => '_tops_ticket_agent_id',
						'value' => get_current_user_id()
					),
					'user_id' => array(
						'key' => '_tops_ticket_user_id',
						'value' => get_current_user_id()
					)
				);
			}
			
		} elseif( is_user_logged_in() ) {
			$args['meta_query']['user_ids'] = array(
				'key' => '_tops_ticket_user_id',
				'value' => get_current_user_id()
			);
		}
				
		return $args;
	}
	
	
	/**
	 * Return a ticket object
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_ticket( $post_id=false, $type='' ) {
		
		global $post;
		
		$post_id = $post_id ? $post_id : $post->ID;
		
		$ticket = new TOPS_Ticket( $post_id, $type );

		return $ticket;
	}
	
	
	/**
	 * Sort the comments
	 *
	 * @access  private
	 * @since   1.0.0
	 */
	 
	private function sort( $a, $b ) {
	  return $a['time'] - $b['time'];
	}
	
	
	/**
	 * Return an array of comments
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_comments( $post_id=false, $show_notes=false, $order='DESC' ) {
		
		global $post;
		
		$post_id = $post_id ? $post_id : $post->ID;
		
		$comments = get_post_meta( $post_id, '_tops_ticket_comments', true );
		if( $show_notes ) {
			$notes = $this->get_notes();
			if( is_array($notes) ) {
				$comments = array_merge( $comments, $notes );
			}	
		}
		
		usort( $comments, array($this, 'sort') );
		
		if( is_array($comments) && $order != 'ASC' ) {
			$comments = array_reverse( $comments );
		}

		return $comments;
	}
	
	
	/**
	 * Return an array of notes
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_notes( $post_id=false, $order='DESC' ) {
		
		global $post;
		
		$post_id = $post_id ? $post_id : $post->ID;
		
		$notes = get_post_meta( $post_id, '_tops_ticket_notes', true );
		if( is_array($notes) && $order != 'ASC' ) {
			$notes = array_reverse( $notes );
		}

		return $notes;
	}
	
	
	/**
	 * Return the public attribute of a ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function is_public( $post_id=false ) {
		
		global $post;
		
		$post_id = $post_id ? $post_id : $post->ID;
		
		$type = get_post_meta( $post->ID, '_tops_ticket_type', true );

		return ($type == 'public');
	}
	
	
	/**
	 * Add a note to a ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function add_note( $data=array() ) {

		if( !(isset($data['id']) && isset($data['comment'])) ) {
			return;
		}

		$ticket = new TOPS_Ticket($data['id']);
		$note_id = $ticket->add_note( $data );
				
		// Allow extra data to be saved to the ticket post
		do_action( 'tops_ticket_after_note_submit', $data, $note_id );
		
		return $note_id;
	}
	
	
	/**
	 * Delete a note
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function delete_note( $id ) {
		$note = new TOPS_Ticket_Note();
		$note->delete( $id );
	}
	

	/**
	 * Add a comment to a ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function add_comment( $data=array() ) {

		if( !(isset($data['id']) && isset($data['comment'])) ) {
			return;
		}
	
		$ticket = new TOPS_Ticket($data['id']);
		$comment_id = $ticket->add_comment( $data );
		
		if( isset($data['close']) && $data['close'] == 'yes' ) {
			$ticket->close();
		}
				
		do_action( 'tops_ticket_after_comment_submit', $data, $comment_id );
		
		return $comment_id;
	}
	
	
	/**
	 * Delete a comment
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function delete_comment( $id ) {	
		$comment = new TOPS_Ticket_Comment();
		$comment->delete( $id );
	}
	
	
	/**
	 * Check to make sure a user can view a ticket
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function user_can_view_ticket( $user_id=false, $post_id=false, $ticket_id=false ) {
		
		if( !$post_id && !$ticket_id ) {
			$post_id = get_the_id();
		}
		if( $post_id ) {
			$ticket = new TOPS_Ticket( $post_id, 'post_id' );
		} elseif( $ticket_id ) {
			$ticket = new TOPS_Ticket( $ticket_id );
		}
		
		if( $ticket ) {
			return $ticket->user_can_view_ticket( $user_id );
		}	
	}
	
		
	/**
	 * Return the private page ID
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_tickets_page_id( $type='' ) {		
		
		global $tops_options;
		
		switch ($type) {
			
			case 'new':
				$page_id = $tops_options['tickets_new'];
				break;
			
			case 'tickets':
				$page_id = $tops_options['tickets_page'];
				break;

			case 'archives':
				$page_id = $tops_options['ticket_archives'];
				break;
				
			case 'starred':
				$page_id = $tops_options['tickets_starred'];
				break;
				
			case 'public':
				$page_id = $tops_options['tickets_public'];
				break;
				
			case 'private':
				$page_id = $tops_options['tickets_private'];
				break;
				
			case 'category':
				$page_id = $tops_options['ticket_categories'];
				break;
				
			case 'create':
				$page_id = $tops_options['tickets_public'];
				break;
				
			default:
				$page_id = $tops_options['tickets_page'];
				break;
		}
		
		return $page_id;
	}


	/**
	 * Return the tickets public page URL
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_tickets_page_url( $type=false, $query_args=array() ) {	
		return add_query_arg( $query_args, get_permalink($this->get_tickets_page_id($type)) );
	}
	
	
	/**
	 * Return a ticket user's email
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	 
	public function get_ticket_user_email( $post_id ) {	
		
		$ticket = new TOPS_Ticket( $post_id, 'post_id' );
		return $ticket->get_user_email();
	}

}
