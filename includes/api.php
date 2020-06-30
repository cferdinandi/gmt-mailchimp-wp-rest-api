<?php

	/**
	 * Add tags to a user
	 * @param  String $base_url  The base URL
	 * @param  Array  $mc_params MailChimp API Parameters
	 * @param  String $email     The user's email address
	 * @param  Array  $tags      Tags to add to the user account
	 */
	function gmt_mailchimp_wp_rest_api_add_tags_to_user($base_url, $mc_params, $email, $tags) {

		// If there are no tags to add, bail
		if ( empty($tags) ) return;

		// Update method
		$mc_params['method'] = 'POST';

		// Add each tag to the user
		foreach ($tags as $tag => $val) {
			$mc_params['body'] = array('email_address' => $email);
			$request = wp_remote_request( $base_url . $tag . '/members', $mc_params );
		}

	}

	/**
	 * Check if an email address is on the block list
	 * @param  String $email      The subscriber email
	 * @param  String $block_list The list of blocked emails
	 * @return Boolean            If true, email is blocked
	 */
	function gmt_mailchimp_wp_rest_api_is_blocked($email, $block_list) {

		// Clean up the email
		$email = trim($email);
		$email_parts = explode('@', $email);
		$email_no_plus = explode('+', $email_parts[0]);
		$emails = array(
			$email,
			$email_no_plus[0] . '@' . $email_parts[1],
			str_replace ('.', '', $email_no_plus[0]) . '@' . $email_parts[1],
		);

		// Convert block list to an array
		$block_list_arr = explode(',', $block_list);

		// Check for matches
		return count(array_intersect($emails, $block_list_arr)) > 0;

	}

	/**
	 * Check if email is valid
	 * https://stackoverflow.com/a/42037557
	 * @param  String  $email The email to test
	 * @return boolean        If true, email is valid
	 */
	function is_email_valid ($email) {
		if (empty($email)) return false;
		$domain = ltrim(stristr($email, '@'), '@');
		$user = stristr($email, '@', true);
		return (!empty($user) && !empty($domain) && checkdnsrr($domain));
	}

	/**
	 * Subscriber a user
	 * @param  Object $request The request data
	 * @return JSON            WP Response Object
	 */
	function gmt_mailchimp_wp_rest_api_subscribe_user($request) {

		// Variables
		$options = mailchimp_rest_api_get_theme_options();
		$params = $request->get_params();

		// Check domain whitelist
		if (!empty($options['mailchimp_origin'])) {
			$origin = $request->get_header('origin');
			if (empty($origin) || !in_array($origin, explode(',', $options['mailchimp_origin']))) {
				return new WP_REST_Response(array(
					'code' => 400,
					'status' => 'disallowed_domain',
					'message' => 'This domain is not whitelisted.'
				), 400);
			}
		}

		// Check key/secret
		if ( !empty($options['mailchimp_form_key']) && !empty($options['mailchimp_form_secret']) && (!isset($params[$options['mailchimp_form_key']]) || empty($params[$options['mailchimp_form_key']]) || $params[$options['mailchimp_form_key']] !== $options['mailchimp_form_secret']) ) {
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Unable to subscribe at this time. Please try again.'
			), 400);
		}

		// Check honeypot field
		if ( !empty($options['mailchimp_honeypot']) && isset($params[$options['mailchimp_honeypot']]) && !empty($params[$options['mailchimp_honeypot']])  ) {
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Unable to subscribe at this time. Please try again.'
			), 400);
		}

		// If email is invalid
		if ( empty( is_url_valid( $params['email'] ) ) ) {
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Please use a valid email address.'
			), 400);
		}

		// If subscriber email is blocked, fail silently (bwhahaha)
		if (!empty($options['mailchimp_blocked_emails']) && gmt_mailchimp_wp_rest_api_is_blocked($params['email'], $options['mailchimp_blocked_emails'])) {
			return new WP_REST_Response(array(
				'code' => 200,
				'status' => 'success',
				'message' => 'Success.'
			), 200);

		}

		// Create merge fields array
		$merge_fields = new stdClass();
		if ( !empty( $params['merge'] ) ) {
			foreach ( $params['merge'] as $key => $merge_field ) {
				$merge_fields->$key = $merge_field;
			}
		}

		// Create interest groups array
		$join = $params['u'] ? false : true;
		$groups = new stdClass();
		if ( !empty( $params['group'] ) ) {
			foreach ( $params['group'] as $key => $group ) {
				$groups->$key = $join;
			}
		}

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/members/' . md5( $params['email'] );
		$tags_url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/segments/';
		$body_params = array(
			'status' => 'subscribed',
			'merge_fields' => $merge_fields,
			'interests' => $groups,
		);
		if ( !$params['do-not-create'] ) {
			$body_params['email_address'] = $params['email'];
			$body_params['status_if_new'] = 'subscribed';
		}
		$mc_params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
			'method' => 'PUT',
			'body' => json_encode($body_params),
		);

		// Add or edit the subscriber
		$request = wp_remote_request( $url, $mc_params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If there was an error
		if ( array_key_exists( 'status', $data ) && $data['status'] >= 400 ) {

			// If user doesn't exist and they shouldn't be created, bail
			if ( $params['do-not-create'] && $data['title'] === 'Invalid Resource' ) {
				return new WP_REST_Response(array(
					'code' => 400,
					'status' => 'invalid_user',
					'message' => 'This subscriber does not exist.'
				), 400);
			}

			// If user previously unsubscribed, resend confirmation email
			if ( $data['title'] === 'Member In Compliance State' ) {

				// Send the API call
				$body_params['status'] = 'pending';
				$mc_params['body'] = json_encode($body_params);
				$request = wp_remote_request( $url, $mc_params );
				$response = wp_remote_retrieve_body( $request );
				$data = json_decode( $response, true );

				// If there's an error
				if ( array_key_exists( 'status', $data ) && $data['status'] >= 400 ) {
					return new WP_REST_Response(array(
						'code' => 400,
						'status' => 'unsubscribed',
						'message' => 'You had previously unsubscribed and cannot be resubscribed using this form.'
					), 400);
				}

				// Add tags
				gmt_mailchimp_wp_rest_api_add_tags_to_user($tags_url, $mc_params, $params['email'], $params['tag']);

				// Otherwise, partial success
				return new WP_REST_Response(array(
					'code' => 202,
					'status' => 'subscribed',
					'message' => 'Almost there! To complete your subscription, please click the confirmation link in the email that was just sent to your inbox.'
				), 202);

			}

			// Otherwise, throw a generic error
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Unable to subscribe at this time. Please try again.'
			), 400);

		}

		// Add tags
		gmt_mailchimp_wp_rest_api_add_tags_to_user($tags_url, $mc_params, $params['email'], $params['tag']);

		// Return a success message
		return new WP_REST_Response(array(
			'code' => 200,
			'status' => 'success',
			'message' => 'You\'re now subscribed.'
		), 200);

	}

	/**
	 * Round a number down to nearest value (10, 100, etc)
	 * @param  Integer $num       The number to round
	 * @param  Integer $precision The precision to round by
	 * @return Integer            The rounded number
	 */
	function gmt_mailchimp_wp_rest_api_round($num, $precision) {
		$num = intval($num);
		if (empty($precision)) return $num;
		$precision = intval($precision);
		return number_format(floor($num / $precision) * $precision);
	}


	/**
	 * Get the number of total subscribers on the list
	 * @param  Array $options The API options
	 * @param  Array $params  The request parameters
	 * @return JSON           The WP API response
	 */
	function gmt_mailchimp_wp_rest_api_get_all_subscribers($options, $params) {

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/';
		$mc_params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
		);

		// Make the request
		$request = wp_remote_get( $url, $mc_params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If something went wrong, throw an error
		if ( !array_key_exists( 'stats', $data ) || !array_key_exists( 'member_count', $data['stats'] ) ) {
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Unable to get subscriber count. Please try again.'
			), 400);
		}

		// Otherwise, return success
		return new WP_REST_Response(array(
			'code' => 200,
			'status' => 'success',
			'message' => gmt_mailchimp_wp_rest_api_round($data['stats']['member_count'], $params['round']),
		), 200);

	}


	/**
	 * Get the number of total subscribers on the list
	 * @param  Array $options The API options
	 * @param  Array $params  The request parameters
	 * @return JSON           The WP API response
	 */
	function gmt_mailchimp_wp_rest_api_get_subscribers_by_group($options, $params) {

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/interest-categories/' . $params['category'] . '/interests/' . $params['id'];
		$mc_params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
		);

		// Make the request
		$request = wp_remote_get( $url, $mc_params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If something went wrong, throw an error
		if ( !array_key_exists( 'subscriber_count', $data ) ) {
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Unable to get subscriber count. Please try again.'
			), 400);
		}

		return new WP_REST_Response(array(
			'code' => 200,
			'status' => 'success',
			'message' => gmt_mailchimp_wp_rest_api_round($data['subscriber_count'], $params['round']),
		), 200);

	}


	/**
	 * Get the subscriber count
	 * @param  Array $request The request data
	 * @return JSON           WP Response Object
	 */
	function gmt_mailchimp_wp_rest_api_subscriber_count($request) {

		// Variables
		$options = mailchimp_rest_api_get_theme_options();
		$params = $request->get_params();

		// Check domain whitelist
		if (!empty($options['origin'])) {
			$origin = $request->get_header('origin');
			if (empty($origin) || !in_array($origin, explode(',', $options['origin']))) {
				return new WP_REST_Response(array(
					'code' => 400,
					'status' => 'disallowed_domain',
					'message' => 'This domain is not whitelisted.'
				), 400);
			}
		}

		// If not interest group details are provided, get all subscribers
		if (empty($params['category']) || empty($params['id'])) {
			return gmt_mailchimp_wp_rest_api_get_all_subscribers($options, $params);
		}

		// Otherwise, get subscribers for an interest group
		return gmt_mailchimp_wp_rest_api_get_subscribers_by_group($options, $params);

	}


	function gmt_mailchimp_wp_rest_api_register_routes () {

		// Add subscribers
		register_rest_route('gmt-mailchimp/v1', '/subscribe', array(
			'methods' => 'POST',
			'callback' => 'gmt_mailchimp_wp_rest_api_subscribe_user',
		));

		// Get subscriber count
		register_rest_route('gmt-mailchimp/v1', '/count', array(
			'methods' => 'GET',
			'callback' => 'gmt_mailchimp_wp_rest_api_subscriber_count',
		));

	}
	add_action('rest_api_init', 'gmt_mailchimp_wp_rest_api_register_routes');