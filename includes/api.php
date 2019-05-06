<?php

	function gmt_mailchimp_wp_rest_api_subscribe_user($request) {

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
		if ( empty( filter_var( $params['email'], FILTER_VALIDATE_EMAIL ) ) ) {
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Please use a valid email address.'
			), 400);
		}

		// Create merge fields array
		if ( empty( $params['merge'] ) ) {
			$merge_fields = new stdClass();
		} else {
			$merge_fields = array();
			foreach ( $params['merge'] as $key => $merge_field ) {
				$merge_fields[$key] = $merge_field;
			}
		}

		// Create interest groups array
		if ( empty( $params['group'] ) ) {
			$groups = new stdClass();
		} else {
			$groups = array();
			foreach ( $params['group'] as $key => $group ) {
				$groups[$key] = true;
			}
		}

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/members';
		$mc_params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
			'body' => json_encode(array(
				'status' => 'subscribed',
				'email_address' => $params['email'],
				'merge_fields' => $merge_fields,
				'interests' => $groups,
			)),
		);

		// Add subscriber
		if ( !$params['do-not-create'] ) {
			$request = wp_remote_post( $url, $mc_params );
			$response = wp_remote_retrieve_body( $request );
			$data = json_decode( $response, true );
		}

		// If subscriber already exists, update profile
		if ( $params['do-not-create'] || ( array_key_exists( 'status', $data ) && $data['status'] === 400 && $data['title'] === 'Member Exists' ) ) {

			$url .= '/' . md5( $params['email'] );
			$mc_params = array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
				),
				'method' => 'PUT',
				'body' => json_encode(array(
					'status' => 'subscribed',
					'merge_fields' => $merge_fields,
					'interests' => $groups,
				)),
			);
			$request = wp_remote_request( $url, $mc_params );
			$response = wp_remote_retrieve_body( $request );
			$data = json_decode( $response, true );

			// If there's an error
			if ( array_key_exists( 'status', $data ) && $data['status'] === 400 ) {

				// If the user does not exist
				if ( $params['do-not-create'] ) {
					return new WP_REST_Response(array(
						'code' => 400,
						'status' => 'invalid_user',
						'message' => 'This subscriber does not exist.'
					), 400);
				}

				// If they're unsubscribed, resend confirmation email
				$mc_params['body'] = json_encode(array(
						'status' => 'pending',
						'merge_fields' => $merge_fields,
						'interests' => $groups,
				));
				$request = wp_remote_request( $url, $mc_params );
				$response = wp_remote_retrieve_body( $request );
				$data = json_decode( $response, true );

				// If there's an error
				if ( array_key_exists( 'status', $data ) && $data['status'] === 400 ) {
					return new WP_REST_Response(array(
						'code' => 400,
						'status' => 'unsubscribed',
						'message' => 'You had previously unsubscribed and cannot be resubscribed using this form.'
					), 400);
				}

				// Otherwise, partial success
				return new WP_REST_Response(array(
					'code' => 202,
					'status' => 'subscribed',
					'message' => 'Almost there! To complete your subscription, please click the confirmation link in the email that was just sent to your inbox.'
				), 202);

			}

			// If still pending, return "new" status again
			if ( array_key_exists( 'status', $data ) && $data['status'] === 'pending' ) {
				return new WP_REST_Response(array(
					'code' => 200,
					'status' => 'success',
					'message' => 'You\'re now subscribed.'
				), 200);
			}

			// Otherwise, throw success message
			return new WP_REST_Response(array(
				'code' => 200,
				'status' => 'success',
				'message' => 'Your account has been updated.'
			), 200);

		}

		// If something went wrong, throw an error
		if ( array_key_exists( 'status', $data ) && $data['status'] === 404 ) {
			return new WP_REST_Response(array(
				'code' => 400,
				'status' => 'failed',
				'message' => 'Unable to subscribe at this time. Please try again.'
			), 400);
		}

		// Success
		return new WP_REST_Response(array(
			'code' => 200,
			'status' => 'success',
			'message' => 'You\'re now subscribed.'
		), 200);

	}


	function gmt_mailchimp_wp_rest_api_register_routes () {
		register_rest_route('gmt-mailchimp/v1', '/subscribe', array(
			'methods' => 'POST',
			'callback' => 'gmt_mailchimp_wp_rest_api_subscribe_user',
		));
	}
	add_action('rest_api_init', 'gmt_mailchimp_wp_rest_api_register_routes');