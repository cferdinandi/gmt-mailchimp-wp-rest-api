<?php

/**
 * Plugin Name: GMT Mailchimp WP Rest API
 * Plugin URI: https://github.com/cferdinandi/gmt-mailchimp-wp-rest-api/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-mailchimp-wp-rest-api/
 * Description: Add WP Rest API for Mailchimp integration.
 * Version: 0.1.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * License: GPLv3
 */

	function gmt_mailchimp_wp_rest_api_subscribe_user($request) {

		return new WP_REST_Response('yea boyyyy!', 200);

		// if (empty($country) || !is_array($country) || !array_key_exists('country_name', $country) || !array_key_exists('country_code', $country)) {
		// 	return new WP_Error( 400, __( 'Location not found.', 'edd_for_courses' ) );
		// }
		// $discount = get_posts(array(
		// 	'post_type' => 'gmt_pricing_parity',
		// 	'meta_key' => 'pricing_parity_country',
		// 	'meta_value' => $_GET['country_code'] ? $_GET['country_code'] : $country['country_code']
		// ));
		// if (empty($discount)) {
		// 	return new WP_REST_Response(array('status' => 'no_discount', 'msg' => __( 'No discounts found.', 'pricing_parity' )), 200);
		// }

	}


	function gmt_mailchimp_wp_rest_api_register_routes () {
		register_rest_route('gmt-mailchimp/v1', '/subscribe', array(
			'methods' => 'POST',
			'callback' => 'gmt_mailchimp_wp_rest_api_subscribe_user',
		));
	}
	add_action('rest_api_init', 'gmt_mailchimp_wp_rest_api_register_routes');