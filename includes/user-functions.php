<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     TOPS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Metaphor Creations
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Users Purchases
 *
 * Retrieves a list of all purchases by a specific user.
 *
 * @since  1.0
 *
 * @param int $user User ID or email address
 * @param int $number Number of purchases to retrieve
 * @param bool pagination
 * @param string|array $status Either an array of statuses, a single status as a string literal or a comma separated list of statues
 *
 * @return bool|object List of all user purchases
 */
function tops_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( 0 === $user ) {
		return false;
	}

	if ( is_string( $status ) ) {
		if ( strpos( $status, ',' ) ) {
			$status = explode( ',', $status );
		} else {
			$status = $status === 'complete' ? 'publish' : $status;
			$status = array( $status );
		}

	}

	if ( is_array( $status ) ) {
		$status = array_unique( $status );
	}

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) )
			$paged = get_query_var('paged');
		else if ( get_query_var( 'page' ) )
			$paged = get_query_var( 'page' );
		else
			$paged = 1;
	}

	$args = array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date'
	);

	if ( $pagination ) {

		$args['page'] = $paged;

	} else {

		$args['nopaging'] = true;

	}

	$by_user_id = is_numeric( $user ) ? true : false;
	$customer   = new TOPS_User( $user, $by_user_id );

	if( ! empty( $customer->payment_ids ) ) {

		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $customer->payment_ids ) );

	}

	$purchases = tops_get_payments( apply_filters( 'tops_get_users_purchases_args', $args ) );

	// No purchases
	if ( ! $purchases )
		return false;

	return $purchases;
}

/**
 * Get Users Purchased Products
 *
 * Returns a list of unique products purchased by a specific user
 *
 * @since  2.0
 *
 * @param int    $user User ID or email address
 * @param string $status
 *
 * @return bool|object List of unique products purchased by user
 */
function tops_get_users_purchased_products( $user = 0, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( empty( $user ) ) {
		return false;
	}

	$by_user_id = is_numeric( $user ) ? true : false;

	$customer = new TOPS_User( $user, $by_user_id );

	if ( empty( $customer->payment_ids ) ) {
		return false;
	}

	// Get all the items purchased
	$payment_ids    = array_reverse( explode( ',', $customer->payment_ids ) );
	$limit_payments = apply_filters( 'tops_users_purchased_products_payments', 50 );
	if ( ! empty( $limit_payments ) ) {
		$payment_ids = array_slice( $payment_ids, 0, $limit_payments );
	}
	$purchase_data  = array();

	foreach ( $payment_ids as $payment_id ) {
		$purchase_data[] = tops_get_payment_meta_downloads( $payment_id );
	}

	if ( empty( $purchase_data ) ) {
		return false;
	}

	// Grab only the post ids of the products purchased on this order
	$purchase_product_ids = array();
	foreach ( $purchase_data as $purchase_meta ) {

		$purchase_ids = @wp_list_pluck( $purchase_meta, 'id' );

		if ( ! is_array( $purchase_ids ) || empty( $purchase_ids ) ) {
			continue;
		}

		$purchase_ids           = array_values( $purchase_ids );
		$purchase_product_ids[] = $purchase_ids;

	}

	// Ensure that grabbed products actually HAVE downloads
	$purchase_product_ids = array_filter( $purchase_product_ids );

	if ( empty( $purchase_product_ids ) ) {
		return false;
	}

	// Merge all orders into a single array of all items purchased
	$purchased_products = array();
	foreach ( $purchase_product_ids as $product ) {
		$purchased_products = array_merge( $product, $purchased_products );
	}

	// Only include each product purchased once
	$product_ids = array_unique( $purchased_products );

	// Make sure we still have some products and a first item
	if ( empty ( $product_ids ) || ! isset( $product_ids[0] ) ) {
		return false;
	}

	$args = apply_filters( 'tops_get_users_purchased_products_args', array(
		'include'        => $product_ids,
		'post_type'      => 'download',
		'posts_per_page' => -1,
	) );

	return apply_filters( 'tops_users_purchased_products_list', get_posts( $args ) );
}

/**
 * Has User Purchased
 *
 * Checks to see if a user has purchased a download.
 *
 * @access      public
 * @since       1.0
 * @param       int $user_id - the ID of the user to check
 * @param       array $downloads - Array of IDs to check if purchased. If an int is passed, it will be converted to an array
 * @param       int $variable_price_id - the variable price ID to check for
 * @return      boolean - true if has purchased, false otherwise
 */
function tops_has_user_purchased( $user_id, $downloads, $variable_price_id = null ) {

	if( empty( $user_id ) ) {
		return false;
	}

	/**
	 * @since 2.7.7
	 *
	 * Allow 3rd parties to take actions before the history is queried.
	 */
	do_action( 'tops_has_user_purchased_before', $user_id, $downloads, $variable_price_id );

	$users_purchases = tops_get_users_purchases( $user_id );

	$return = false;

	if ( ! is_array( $downloads ) ) {
		$downloads = array( $downloads );
	}

	if ( $users_purchases ) {
		foreach ( $users_purchases as $purchase ) {
			$payment         = new TOPS_Payment( $purchase->ID );
			$purchased_files = $payment->cart_details;

			if ( is_array( $purchased_files ) ) {
				foreach ( $purchased_files as $download ) {
					if ( in_array( $download['id'], $downloads ) ) {
						$variable_prices = tops_has_variable_prices( $download['id'] );
						if ( $variable_prices && ! is_null( $variable_price_id ) && $variable_price_id !== false ) {
							if ( isset( $download['item_number']['options']['price_id'] ) && $variable_price_id == $download['item_number']['options']['price_id'] ) {
								$return = true;
								break 2; // Get out to prevent this value being overwritten if the customer has purchased item twice
							} else {
								$return = false;
							}
						} else {
							$return = true;
							break 2;  // Get out to prevent this value being overwritten if the customer has purchased item twice
						}
					}
				}
			}
		}
	}

	/**
	 * @since 2.7.7
	 *
	 * Filter has purchased result
	 */
	$return = apply_filters( 'tops_has_user_purchased', $return, $user_id, $downloads, $variable_price_id );

	return $return;
}

/**
 * Has Purchases
 *
 * Checks to see if a user has purchased at least one item.
 *
 * @access      public
 * @since       1.0
 * @param       int $user_id - the ID of the user to check
 * @return      bool - true if has purchased, false other wise.
 */
function tops_has_purchases( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( tops_get_users_purchases( $user_id, 1 ) ) {
		return true; // User has at least one purchase
	}
	return false; // User has never purchased anything
}


/**
 * Get Purchase Status for User
 *
 * Retrieves the purchase count and the total amount spent for a specific user
 *
 * @access      public
 * @since       1.6
 * @param       int|string $user - the ID or email of the customer to retrieve stats for
 * @param       string $mode - "test" or "live"
 * @return      array
 */
function tops_get_purchase_stats_by_user( $user = '' ) {

	if ( is_email( $user ) ) {

		$field = 'email';

	} elseif ( is_numeric( $user ) ) {

		$field = 'user_id';

	}

	$stats    = array();
	$customer = TOPS()->customers->get_customer_by( $field, $user );

	if( $customer ) {

		$customer = new TOPS_User( $customer->id );

		$stats['purchases']   = absint( $customer->purchase_count );
		$stats['total_spent'] = tops_sanitize_amount( $customer->purchase_value );

	}


	return (array) apply_filters( 'tops_purchase_stats_by_user', $stats, $user );
}


/**
 * Count number of purchases of a customer
 *
 * Returns total number of purchases a customer has made
 *
 * @access      public
 * @since       1.3
 * @param       mixed $user - ID or email
 * @return      int - the total number of purchases
 */
function tops_count_purchases_of_customer( $user = null ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$stats = ! empty( $user ) ? tops_get_purchase_stats_by_user( $user ) : false;

	return isset( $stats['purchases'] ) ? $stats['purchases'] : 0;
}

/**
 * Calculates the total amount spent by a user
 *
 * @access      public
 * @since       1.3
 * @param       mixed $user - ID or email
 * @return      float - the total amount the user has spent
 */
function tops_purchase_total_of_user( $user = null ) {

	$stats = tops_get_purchase_stats_by_user( $user );

	return $stats['total_spent'];
}

/**
 * Counts the total number of files a customer has downloaded
 *
 * @access      public
 * @since       1.3
 * @param       mixed $user - ID or email
 * @return      int - The total number of files the user has downloaded
 */
function tops_count_file_downloads_of_user( $user ) {
	global $tops_logs;

	if ( is_email( $user ) ) {
		$meta_query = array(
			array(
				'key'     => '_tops_log_user_info',
				'value'   => $user,
				'compare' => 'LIKE'
			)
		);
	} else {
		$meta_query = array(
			array(
				'key'     => '_tops_log_user_id',
				'value'   => $user
			)
		);
	}

	return $tops_logs->get_log_count( null, 'file_download', $meta_query );
}

/**
 * Validate a potential username
 *
 * @access      public
 * @since       1.3.4
 * @param       string $username The username to validate
 * @return      bool
 */
function tops_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid = ( $sanitized == $username );
	return (bool) apply_filters( 'tops_validate_username', $valid, $username );
}

/**
 * Attach the newly created user_id to a customer, if one exists
 *
 * @since  2.4.6
 * @param  int $user_id The User ID that was created
 * @return void
 */
function tops_connect_existing_customer_to_new_user( $user_id ) {
	$email = get_the_author_meta( 'user_email', $user_id );

	// Update the user ID on the customer
	$customer = new TOPS_User( $email );

	if( $customer->id > 0 ) {
		$customer->update( array( 'user_id' => $user_id ) );
	}
}
add_action( 'user_register', 'tops_connect_existing_customer_to_new_user', 10, 1 );

/**
 * Looks up purchases by email that match the registering user
 *
 * This is for users that purchased as a guest and then came
 * back and created an account.
 *
 * @access      public
 * @since       1.6
 * @param       int $user_id - the new user's ID
 * @return      void
 */
function tops_add_past_purchases_to_new_user( $user_id ) {

	$email    = get_the_author_meta( 'user_email', $user_id );

	$payments = tops_get_payments( array( 's' => $email ) );

	if( $payments ) {

		// Set a flag to force the account to be verified before purchase history can be accessed
		tops_set_user_to_pending( $user_id );

		tops_send_user_verification_email( $user_id );

		foreach( $payments as $payment ) {
			if( intval( tops_get_payment_user_id( $payment->ID ) ) > 0 ) {
				continue; // This payment already associated with an account
			}

			$meta                    = tops_get_payment_meta( $payment->ID );
			$meta['user_info']       = maybe_unserialize( $meta['user_info'] );
			$meta['user_info']['id'] = $user_id;
			$meta['user_info']       = $meta['user_info'];

			// Store the updated user ID in the payment meta
			tops_update_payment_meta( $payment->ID, '_tops_payment_meta', $meta );
			tops_update_payment_meta( $payment->ID, '_tops_payment_user_id', $user_id );
		}
	}

}
add_action( 'user_register', 'tops_add_past_purchases_to_new_user', 10, 1 );


/**
 * Counts the total number of customers.
 *
 * @access 		public
 * @since 		1.7
 * @return 		int - The total number of customers.
 */
function tops_count_total_customers( $args = array() ) {
	return TOPS()->customers->count( $args );
}


/**
 * Returns the saved address for a customer
 *
 * @access 		public
 * @since 		1.8
 * @return 		array - The customer's address, if any
 */
function tops_get_customer_address( $user_id = 0 ) {
	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$address = get_user_meta( $user_id, '_tops_user_address', true );

	if ( ! $address || ! is_array( $address ) || empty( $address ) ) {
		$address = array();
	}

	if( ! isset( $address['line1'] ) )
		$address['line1'] = '';

	if( ! isset( $address['line2'] ) )
		$address['line2'] = '';

	if( ! isset( $address['city'] ) )
		$address['city'] = '';

	if( ! isset( $address['zip'] ) )
		$address['zip'] = '';

	if( ! isset( $address['country'] ) )
		$address['country'] = '';

	if( ! isset( $address['state'] ) )
		$address['state'] = '';

	return $address;
}

/**
 * Sends the new user notification email when a user registers during checkout
 *
 * @access 		public
 * @since 		1.8.8
 * @param int   $user_id
 * @param array $user_data
 * @return 		void
 */
function tops_new_user_notification( $user_id = 0, $user_data = array() ) {

	if( empty( $user_id ) || empty( $user_data ) ) {
		return;
	}

	$emails     = TOPS()->emails;
	$from_name  = tops_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_email = tops_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );

	$admin_subject  = sprintf( __('[%s] New User Registration', 'total-product-support' ), $from_name );
	$admin_heading  = __( 'New user registration', 'total-product-support' );
	$admin_message  = sprintf( __( 'Username: %s', 'total-product-support'), $user_data['user_login'] ) . "\r\n\r\n";
	$admin_message .= sprintf( __( 'E-mail: %s', 'total-product-support'), $user_data['user_email'] ) . "\r\n";

	$emails->__set( 'heading', $admin_heading );

	$emails->send( get_option( 'admin_email' ), $admin_subject, $admin_message );

	$user_subject  = sprintf( __( '[%s] Your username and password', 'total-product-support' ), $from_name );
	$user_heading  = __( 'Your account info', 'total-product-support' );
	$user_message  = sprintf( __( 'Username: %s', 'total-product-support' ), $user_data['user_login'] ) . "\r\n";

	if ( did_action( 'tops_pre_process_purchase' ) ) {
		$password_message = __( 'Password entered at checkout', 'total-product-support' );
	} else {
		$password_message = __( 'Password entered at registration', 'total-product-support' );
	}

	$user_message .= sprintf( __( 'Password: %s', 'total-product-support' ), '[' . $password_message . ']' ) . "\r\n";

	if( $emails->html ) {

		$user_message .= '<a href="' . wp_login_url() . '"> ' . esc_attr__( 'Click here to log in', 'total-product-support' ) . ' &raquo;</a>' . "\r\n";

	} else {

		$user_message .= sprintf( __( 'To log in, visit: %s', 'total-product-support' ), wp_login_url() ) . "\r\n";

	}

	$emails->__set( 'heading', $user_heading );

	$emails->send( $user_data['user_email'], $user_subject, $user_message );

}
add_action( 'tops_insert_user', 'tops_new_user_notification', 10, 2 );

/**
 * Set a user's status to pending
 *
 * @since  2.4.4
 * @param  integer $user_id The User ID to set to pending
 * @return bool             If the update was successful
 */
function tops_set_user_to_pending( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	do_action( 'tops_pre_set_user_to_pending', $user_id );

	$update_successful = (bool) update_user_meta( $user_id, '_tops_pending_verification', '1' );

	do_action( 'tops_post_set_user_to_pending', $user_id, $update_successful );

	return $update_successful;
}

/**
 * Set the user from pending to active
 *
 * @since  2.4.4
 * @param  integer $user_id The User ID to activate
 * @return bool             If the user was marked as active or not
 */
function tops_set_user_to_verified( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		return false;
	}

	if ( ! tops_user_pending_verification( $user_id ) ) {
		return false;
	}

	do_action( 'tops_pre_set_user_to_active', $user_id );

	$update_successful = delete_user_meta( $user_id, '_tops_pending_verification', '1' );

	do_action( 'tops_post_set_user_to_active', $user_id, $update_successful );

	return $update_successful;
}

/**
 * Determines if the user account is pending verification. Pending accounts cannot view purchase history
 *
 * @access  public
 * @since   2.4.4
 * @return  bool
 */
function tops_user_pending_verification( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	// No need to run a DB lookup on an empty user id
	if ( empty( $user_id ) ) {
		return false;
	}

	$pending = get_user_meta( $user_id, '_tops_pending_verification', true );

	return (bool) apply_filters( 'tops_user_pending_verification', ! empty( $pending ), $user_id );

}

/**
 * Gets the activation URL for the specified user
 *
 * @access  public
 * @since   2.4.4
 * @return  string
 */
function tops_get_user_verification_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		return false;
	}

	$base_url = add_query_arg( array(
		'tops_action' => 'verify_user',
		'user_id'    => $user_id,
		'ttl'        => strtotime( '+24 hours' )
	), untrailingslashit( tops_get_user_verification_page() ) );

	$token = tops_get_user_verification_token( $base_url );
	$url   = add_query_arg( 'token', $token, $base_url );

	return apply_filters( 'tops_get_user_verification_url', $url, $user_id );

}

/**
 * Gets the URL that triggers a new verification email to be sent
 *
 * @access  public
 * @since   2.4.4
 * @return  string
 */
function tops_get_user_verification_request_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$url = esc_url( wp_nonce_url( add_query_arg( array(
		'tops_action' => 'send_verification_email'
	) ), 'tops-request-verification' ) );

	return apply_filters( 'tops_get_user_verification_request_url', $url, $user_id );

}

/**
 * Sends an email to the specified user with a URL to verify their account
 *
 * @access  public
 * @since   2.4.4
 * @return  void
 */
function tops_send_user_verification_email( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		return;
	}

	if( ! tops_user_pending_verification( $user_id ) ) {
		return;
	}

	$user_data  = get_userdata( $user_id );

	if( ! $user_data ) {
		return;
	}

	$name       = $user_data->display_name;
	$url        = tops_get_user_verification_url( $user_id );
	$from_name  = tops_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_email = tops_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$subject    = apply_filters( 'tops_user_verification_email_subject', __( 'Verify your account', 'total-product-support' ), $user_id );
	$heading    = apply_filters( 'tops_user_verification_email_heading', __( 'Verify your account', 'total-product-support' ), $user_id );
	$message    = sprintf(
		__( "Hello %s,\n\nYour account with %s needs to be verified before you can access your purchase history. <a href='%s'>Click here</a> to verify your account.\n\nLink missing? Visit the following URL: %s", 'total-product-support' ),
		$name,
		$from_name,
		$url,
		$url
	);

	$message    = apply_filters( 'tops_user_verification_email_message', $message, $user_id );

	$emails     = new TOPS_Emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$emails->send( $user_data->user_email, $subject, $message );

}

/**
 * Generates a token for a user verification URL.
 *
 * An 'o' query parameter on a URL can include optional variables to test
 * against when verifying a token without passing those variables around in
 * the URL. For example, downloads can be limited to the IP that the URL was
 * generated for by adding 'o=ip' to the query string.
 *
 * Or suppose when WordPress requested a URL for automatic updates, the user
 * agent could be tested to ensure the URL is only valid for requests from
 * that user agent.
 *
 * @since  2.4.4
 *
 * @param  string $url The URL to generate a token for.
 * @return string The token for the URL.
 */
function tops_get_user_verification_token( $url = '' ) {

	$args    = array();
	$hash    = apply_filters( 'tops_get_user_verification_token_algorithm', 'sha256' );
	$secret  = apply_filters( 'tops_get_user_verification_token_secret', hash( $hash, wp_salt() ) );

	/*
	 * Add additional args to the URL for generating the token.
	 * Allows for restricting access to IP and/or user agent.
	 */
	$parts   = parse_url( $url );
	$options = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		// o = option checks (ip, user agent).
		if ( ! empty( $query_args['o'] ) ) {

			// Multiple options can be checked by separating them with a colon in the query parameter.
			$options = explode( ':', rawurldecode( $query_args['o'] ) );

			if ( in_array( 'ip', $options ) ) {

				$args['ip'] = tops_get_ip();

			}

			if ( in_array( 'ua', $options ) ) {

				$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$args['user_agent'] = rawurlencode( $ua );

			}

		}

	}

	/*
	 * Filter to modify arguments and allow custom options to be tested.
	 * Be sure to rawurlencode any custom options for consistent results.
	 */
	$args = apply_filters( 'tops_get_user_verification_token_args', $args, $url, $options );

	$args['secret'] = $secret;
	$args['token']  = false; // Removes a token if present.

	$url   = add_query_arg( $args, $url );
	$parts = parse_url( $url );

	// In the event there isn't a path, set an empty one so we can MD5 the token
	if ( ! isset( $parts['path'] ) ) {

		$parts['path'] = '';

	}

	$token = md5( $parts['path'] . '?' . $parts['query'] );

	return $token;

}

/**
 * Generate a token for a URL and match it against the existing token to make
 * sure the URL hasn't been tampered with.
 *
 * @since  2.4.4
 *
 * @param  string $url URL to test.
 * @return bool
 */
function tops_validate_user_verification_token( $url = '' ) {

	$ret        = false;
	$parts      = parse_url( $url );
	$query_args = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		if ( isset( $query_args['ttl'] ) && current_time( 'timestamp' ) > $query_args['ttl'] ) {

			do_action( 'tops_user_verification_token_expired' );

			$link_text = sprintf(
				__( 'Sorry but your account verification link has expired. <a href="%s">Click here</a> to request a new verification URL.', 'total-product-support' ),
				tops_get_user_verification_request_url()
			);

			wp_die( apply_filters( 'tops_verification_link_expired_text', $link_text ), __( 'Error', 'total-product-support' ), array( 'response' => 403 ) );

		}

		if ( isset( $query_args['token'] ) && $query_args['token'] == tops_get_user_verification_token( $url ) ) {

			$ret = true;

		}

	}

	return apply_filters( 'tops_validate_user_verification_token', $ret, $url, $query_args );
}

/**
 * Processes an account verification email request
 *
 * @since  2.4.4
 *
 * @return void
 */
function tops_process_user_verification_request() {

	if( ! wp_verify_nonce( $_GET['_wpnonce'], 'tops-request-verification' ) ) {
		wp_die( __( 'Nonce verification failed.', 'total-product-support' ), __( 'Error', 'total-product-support' ), array( 'response' => 403 ) );
	}

	if( ! is_user_logged_in() ) {
		wp_die( __( 'You must be logged in to verify your account.', 'total-product-support' ), __( 'Notice', 'total-product-support' ), array( 'response' => 403 ) );
	}

	if( ! tops_user_pending_verification( get_current_user_id() ) ) {
		wp_die( __( 'Your account has already been verified.', 'total-product-support' ), __( 'Notice', 'total-product-support' ), array( 'response' => 403 ) );
	}

	tops_send_user_verification_email( get_current_user_id() );

	$redirect = apply_filters(
		'tops_user_account_verification_request_redirect',
		add_query_arg( 'tops-verify-request', '1', tops_get_user_verification_page() )
	);

	wp_safe_redirect( $redirect );
	exit;

}
add_action( 'tops_send_verification_email', 'tops_process_user_verification_request' );

/**
 * Processes an account verification
 *
 * @since 2.4.4
 *
 * @return void
 */
function tops_process_user_account_verification() {

	if( empty( $_GET['token'] ) ) {
		return false;
	}

	if( empty( $_GET['user_id'] ) ) {
		return false;
	}

	if( empty( $_GET['ttl'] ) ) {
		return false;
	}

	$parts = parse_url( add_query_arg( array() ) );
	wp_parse_str( $parts['query'], $query_args );
	$url = add_query_arg( $query_args, untrailingslashit( tops_get_user_verification_page() ) );

	if( ! tops_validate_user_verification_token( $url ) ) {

		do_action( 'tops_invalid_user_verification_token' );

		wp_die( __( 'Invalid verification token provided.', 'total-product-support' ), __( 'Error', 'total-product-support' ), array( 'response' => 403 ) );
	}

	tops_set_user_to_verified( absint( $_GET['user_id'] ) );

	do_action( 'tops_user_verification_token_validated' );

	$redirect = apply_filters(
		'tops_user_account_verified_redirect',
		add_query_arg( 'tops-verify-success', '1', tops_get_user_verification_page() )
	);

	wp_safe_redirect( $redirect );
	exit;

}
add_action( 'tops_verify_user', 'tops_process_user_account_verification' );

/**
 * Retrieves the purchase history page, or main URL for the account verification process
 *
 * @since  2.4.6
 * @return string The base URL to use for account verification
 */
function tops_get_user_verification_page() {
	$url              = home_url();
	$purchase_history = tops_get_option( 'purchase_history_page', 0 );

	if ( ! empty( $purchase_history ) ) {
		$url = get_permalink( $purchase_history );
	}

	return apply_filters( 'tops_user_verification_base_url', $url );
}

/**
 * When a user is deleted, detach that user id from the customer record
 *
 * @since  2.5
 * @param  int $user_id The User ID being deleted
 * @return bool         If the detachment was successful
 */
function tops_detach_deleted_user( $user_id ) {

	$customer = new TOPS_User( $user_id, true );
	$detached = false;

	if ( $customer->id > 0 ) {
		$detached = $customer->update( array( 'user_id' => 0 ) );
	}

	do_action( 'tops_detach_deleted_user', $user_id, $customer, $detached );

	return $detached;
}
add_action( 'delete_user', 'tops_detach_deleted_user', 10, 1 );

/**
 * Modify User Profile
 *
 * Modifies the output of profile.php to add key generation/revocation
 *
 * @since 2.6
 * @param object $user Current user info
 * @return void
 */
function tops_show_user_api_key_field( $user ) {

	if ( get_current_user_id() !== $user->ID ) {
		return;
	}

	if ( ( tops_get_option( 'api_allow_user_keys', false ) || current_user_can( 'manage_shop_settings' ) ) && current_user_can( 'edit_user', $user->ID ) ) {
		$user = get_userdata( $user->ID );
		$public_key = TOPS()->api->get_user_public_key( $user->ID );
		$secret_key = TOPS()->api->get_user_secret_key( $user->ID );
		$token      = TOPS()->api->get_token( $user->ID );
		?>
		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<?php _e( 'Total Product Support API Keys', 'total-product-support' ); ?>
				</th>
				<td>
					<?php if ( empty( $user->tops_user_public_key ) ) { ?>
						<input name="tops_set_api_key" type="checkbox" id="tops_set_api_key" value="0" />
						<span class="description"><?php _e( 'Generate API Key', 'total-product-support' ); ?></span>
					<?php } else { ?>
						<strong style="display:inline-block; width: 125px;"><?php _e( 'Public key:', 'total-product-support' ); ?>&nbsp;</strong><input type="text" readonly="readonly" class="regular-text" id="publickey" value="<?php echo esc_attr( $public_key ); ?>"/><br/>
						<strong style="display:inline-block; width: 125px;"><?php _e( 'Secret key:', 'total-product-support' ); ?>&nbsp;</strong><input type="text" readonly="readonly" class="regular-text" id="privatekey" value="<?php echo esc_attr( $secret_key ); ?>"/><br/>
						<strong style="display:inline-block; width: 125px;"><?php _e( 'Token:', 'total-product-support' ); ?>&nbsp;</strong><input type="text" readonly="readonly" class="regular-text" id="token" value="<?php echo esc_attr( TOPS()->api->get_token( $user->ID ) ); ?>"/><br/>
						<input name="tops_set_api_key" type="checkbox" id="tops_set_api_key" value="0" />
						<span class="description"><label for="tops_set_api_key"><?php _e( 'Revoke API Keys', 'total-product-support' ); ?></label></span>
					<?php } ?>
				</td>
			</tr>
			</tbody>
		</table>

		<?php if ( wp_is_mobile() ) : ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<?php printf( __( 'Total Product Support <a href="%s">iOS App</a>', 'total-product-support' ), 'https://itunes.apple.com/us/app/total-product-support-2/id1169488828?ls=1&mt=8' ); ?>
				</th>
				<td>
					<?php
					$sitename = get_bloginfo( 'name' );
					$ios_url  = 'tops://new?sitename=' . $sitename . '&siteurl=' . home_url() . '&key=' . $public_key . '&token=' . $token;
					?>
					<a class="button-secondary" href="<?php echo $ios_url; ?>"><?php _e( 'Add to iOS App', 'total-product-support' ); ?></a>
				</td>
			</tr>
			</tbody>
		</table>
		<?php endif; ?>

	<?php }
}
add_action( 'show_user_profile', 'tops_show_user_api_key_field' );
add_action( 'edit_user_profile', 'tops_show_user_api_key_field' );

/**
 * Generate and Save API key
 *
 * Generates the key requested by user_key_field and stores it in the database
 *
 * @since 2.6
 * @param int $user_id
 * @return void
 */
function tops_update_user_api_key( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['tops_set_api_key'] ) ) {

		$user       = get_userdata( $user_id );
		$public_key = TOPS()->api->get_user_public_key( $user_id );

		if ( empty( $public_key ) ) {
			$new_public_key = TOPS()->api->generate_public_key( $user->user_email );
			$new_secret_key = TOPS()->api->generate_private_key( $user->ID );

			update_user_meta( $user_id, $new_public_key, 'tops_user_public_key' );
			update_user_meta( $user_id, $new_secret_key, 'tops_user_secret_key' );
		} else {
			TOPS()->api->revoke_api_key( $user_id );
		}
	}
}
add_action( 'personal_options_update',  'tops_update_user_api_key' );
add_action( 'edit_user_profile_update', 'tops_update_user_api_key' );
