<?php

/**
 * Theme Options v1.1.0
 * Adjust theme settings from the admin dashboard.
 * Find and replace `YourTheme` with your own namepspacing.
 *
 * Created by Michael Fields.
 * https://gist.github.com/mfields/4678999
 *
 * Forked by Chris Ferdinandi
 * http://gomakethings.com
 *
 * Free to use under the MIT License.
 * http://gomakethings.com/mit/
 */


	/**
	 * Theme Options Fields
	 * Each option field requires its own uniquely named function. Select options and radio buttons also require an additional uniquely named function with an array of option choices.
	 */

	function mailchimp_rest_api_settings_field_mailchimp_rest_api_api_key() {
		$options = mailchimp_rest_api_get_theme_options();
		?>
		<input type="text" name="mailchimp_rest_api_theme_options[mailchimp_api_key]" class="regular-text" id="mailchimp_rest_api_api_key" value="<?php echo esc_attr( $options['mailchimp_api_key'] ); ?>" />
		<label class="description" for="mailchimp_rest_api_api_key"><?php _e( 'MailChimp API key', 'mailchimp_rest_api' ); ?></label>
		<?php
	}

	function mailchimp_rest_api_settings_field_mailchimp_rest_api_list_id() {
		$options = mailchimp_rest_api_get_theme_options();
		?>
		<input type="text" name="mailchimp_rest_api_theme_options[mailchimp_list_id]" class="regular-text" id="mailchimp_rest_api_list_id" value="<?php echo esc_attr( $options['mailchimp_list_id'] ); ?>" />
		<label class="description" for="mailchimp_rest_api_list_id"><?php _e( 'MailChimp list ID', 'mailchimp_rest_api' ); ?></label>
		<?php
	}

	function mailchimp_rest_api_settings_field_key() {
		$options = mailchimp_rest_api_get_theme_options();
		?>
		<input type="text" name="mailchimp_rest_api_theme_options[mailchimp_form_key]" class="regular-text" id="mailchimp_rest_api_form_key" value="<?php echo esc_attr( $options['mailchimp_form_key'] ); ?>" />
		<label class="description" for="mailchimp_rest_api_form_key"><?php _e( 'Form Key (optional)', 'mailchimp_rest_api' ); ?></label>
		<?php
	}

	function mailchimp_rest_api_settings_field_secret() {
		$options = mailchimp_rest_api_get_theme_options();
		?>
		<input type="text" name="mailchimp_rest_api_theme_options[mailchimp_form_secret]" class="regular-text" id="mailchimp_rest_api_api_form_secret" value="<?php echo esc_attr( $options['mailchimp_form_secret'] ); ?>" />
		<label class="description" for="mailchimp_rest_api_api_form_secret"><?php _e( 'Form Secret (optional)', 'mailchimp_rest_api' ); ?></label>
		<?php
	}

	function mailchimp_rest_api_settings_field_honeypot() {
		$options = mailchimp_rest_api_get_theme_options();
		?>
		<input type="text" name="mailchimp_rest_api_theme_options[mailchimp_honeypot]" class="regular-text" id="mailchimp_rest_api_api_honeypot" value="<?php echo esc_attr( $options['mailchimp_honeypot'] ); ?>" />
		<label class="description" for="mailchimp_rest_api_api_honeypot"><?php _e( 'Honeypot Field Name (optional)', 'mailchimp_rest_api' ); ?></label>
		<?php
	}

	function mailchimp_rest_api_settings_field_origin() {
		$options = mailchimp_rest_api_get_theme_options();
		?>
		<input type="text" name="mailchimp_rest_api_theme_options[mailchimp_origin]" class="regular-text" id="mailchimp_rest_api_api_origin" value="<?php echo esc_attr( $options['mailchimp_origin'] ); ?>" />
		<label class="description" for="mailchimp_rest_api_api_origin"><?php _e( 'Allowed domain origins for the API (optional, comma-separated)', 'mailchimp_rest_api' ); ?></label>
		<?php
  }

  function mailchimp_rest_api_settings_field_required_merge() {
		$options = mailchimp_rest_api_get_theme_options();
		?>
		<input type="text" name="mailchimp_rest_api_theme_options[mailchimp_required_merge]" class="regular-text" id="mailchimp_rest_api_required_merge" value="<?php echo esc_attr( $options['mailchimp_required_merge'] ); ?>" />
		<label class="description" for="mailchimp_rest_api_required_merge"><?php _e( 'Required MERGE tags of the list (optional, comma-separated)', 'mailchimp_rest_api' ); ?></label>
    <p>
      <?php
        _e( 'If you have a required MERGE tag you have to fill that field in order to marketing fields to work', 'mailchimp_rest_api' );
      ?>
    </p>
		<?php
	}



	/**
	 * Theme Option Defaults & Sanitization
	 * Each option field requires a default value under mailchimp_rest_api_get_theme_options(), and an if statement under mailchimp_rest_api_theme_options_validate();
	 */

	// Get the current options from the database.
	// If none are specified, use these defaults.
	function mailchimp_rest_api_get_theme_options() {
		$saved = (array) get_option( 'mailchimp_rest_api_theme_options' );
		$defaults = array(
			'mailchimp_api_key' => '',
			'mailchimp_list_id' => '',
			'mailchimp_form_key' => '',
			'mailchimp_form_secret' => '',
			'mailchimp_honeypot' => '',
			'mailchimp_origin' => '',
			'mailchimp_required_merge' => '',
		);

		$defaults = apply_filters( 'mailchimp_rest_api_default_theme_options', $defaults );

		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );

		return $options;
	}

	// Sanitize and validate updated theme options
	function mailchimp_rest_api_theme_options_validate( $input ) {
		$output = array();

		if ( isset( $input['mailchimp_api_key'] ) && ! empty( $input['mailchimp_api_key'] ) )
			$output['mailchimp_api_key'] = wp_filter_nohtml_kses( $input['mailchimp_api_key'] );

		if ( isset( $input['mailchimp_list_id'] ) && ! empty( $input['mailchimp_list_id'] ) )
			$output['mailchimp_list_id'] = wp_filter_nohtml_kses( $input['mailchimp_list_id'] );

		if ( isset( $input['mailchimp_form_key'] ) && ! empty( $input['mailchimp_form_key'] ) )
			$output['mailchimp_form_key'] = wp_filter_nohtml_kses( $input['mailchimp_form_key'] );

		if ( isset( $input['mailchimp_form_secret'] ) && ! empty( $input['mailchimp_form_secret'] ) )
			$output['mailchimp_form_secret'] = wp_filter_nohtml_kses( $input['mailchimp_form_secret'] );

		if ( isset( $input['mailchimp_honeypot'] ) && ! empty( $input['mailchimp_honeypot'] ) )
			$output['mailchimp_honeypot'] = wp_filter_nohtml_kses( $input['mailchimp_honeypot'] );

		if ( isset( $input['mailchimp_origin'] ) && ! empty( $input['mailchimp_origin'] ) )
			$output['mailchimp_origin'] = wp_filter_nohtml_kses( str_replace(' ', '', $input['mailchimp_origin']) );
    
      if ( isset( $input['mailchimp_required_merge'] ) && ! empty( $input['mailchimp_required_merge'] ) )
			$output['mailchimp_required_merge'] = wp_filter_nohtml_kses( str_replace(' ', '', $input['mailchimp_required_merge']) );

		return apply_filters( 'mailchimp_rest_api_theme_options_validate', $output, $input );
	}



	/**
	 * Get data from the MailChimp API
	 * @param  string $group The group ID
	 * @return array         Data from the MailChimp API
	 */
	function mailchimp_rest_api_get_mailchimp_group_data($group = null) {

		$options = mailchimp_rest_api_get_theme_options();

		if (empty($options['mailchimp_api_key']) || empty($options['mailchimp_list_id'])) return;

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/interest-categories' . ( empty( $group ) ? '' : '/' . $group . '/interests?count=99' );
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
		);

		// Get data from  MailChimp
		$request = wp_remote_get( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If request fails, bail
		if ( empty( $group ) ) {
			if ( !array_key_exists( 'categories', $data ) || !is_array( $data['categories'] ) || empty( $data['categories'] ) ) return array();
		} else {
			if ( !array_key_exists( 'interests', $data ) || !is_array( $data['interests'] ) || empty( $data['interests'] ) ) return array();
		}

		return $data;

	}

	/**
	 * Get data from the MailChimp API
	 * @param  string $group The group ID
	 * @return array         Data from the MailChimp API
	 */
	function mailchimp_rest_api_get_mailchimp_tag_data($group = null) {

		$options = mailchimp_rest_api_get_theme_options();

		if (empty($options['mailchimp_api_key']) || empty($options['mailchimp_list_id'])) return;

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/segments?type=static&count=1000';
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
		);

		// Get data from  MailChimp
		$request = wp_remote_get( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

		// If request fails, bail
		// if ( empty( $group ) ) {
		// 	if ( !array_key_exists( 'categories', $data ) || !is_array( $data['categories'] ) || empty( $data['categories'] ) ) return array();
		// } else {
			if ( !array_key_exists( 'segments', $data ) || !is_array( $data['segments'] ) || empty( $data['segments'] ) ) return array();
		// }

		return $data;

  }

  /**
	 * Get Marketing permissions for the configured list/audience
	 * @return array  Marketing permissions for the list
	 */
	function mailchimp_rest_api_get_list_marketing_permissions() {

    $options = mailchimp_rest_api_get_theme_options();

		if ( empty( $options['mailchimp_api_key'] ) || empty( $options['mailchimp_list_id'] ) ) return;

		// Create API call
		$shards = explode( '-', $options['mailchimp_api_key'] );
		$url = 'https://' . $shards[1] . '.api.mailchimp.com/3.0/lists/' . $options['mailchimp_list_id'] . '/members';
		$params = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'mailchimp' . ':' . $options['mailchimp_api_key'] )
			),
		);

		// Get data from  MailChimp
		$request = wp_remote_get( $url, $params );
		$response = wp_remote_retrieve_body( $request );
		$data = json_decode( $response, true );

    $dummy_member = NULL;
    $should_delete_dummy = false;

		// If there are no members in the list, we need to create a dummy one
		if( empty($data['members']) ) {
      $merge_fields = new stdClass();

      // We have to set the required merge tags (if any) for avoiding erros 
      if( !empty( $options['mailchimp_required_merge'] ) ) {
        $merge_fields = [];

        foreach ( explode( ',', $options['mailchimp_required_merge'] ) as $merge_field ) {
          $merge_fields[$merge_field] = 'Dummy';
        }
      }

      $dummy_member = array(
        'email_address' => 'dummy+member@deleteme.com',
        'status' => 'subscribed',
        'merge_fields' => $merge_fields
      );

      $subscribe_params = $params;
      $subscribe_params['method'] = 'POST';
      $subscribe_params['body'] = json_encode($dummy_member);

      // Subscribe the member
      $request = wp_remote_post($url, $subscribe_params);
      $response = wp_remote_retrieve_body( $request );
      $http_code = wp_remote_retrieve_response_code( $request );
      $dummy_member = json_decode( $response, true );

      if( $http_code > 399 ) {
        $text = __( 'There was an error getting marketing permissions. Please make sure you haven\'t forgot to add required MERGE tags', 'mailchimp_rest_api' );
        return array(
          array(
            'text' => $text,
            'marketing_permission_id' => ''
          )
        );
      }
      
      // If we subscribed the member successfully, inform that we have to delete it after
      $should_delete_dummy = true;
    } else {
      // If the list has members, just get one of them
      $dummy_member = $data['members'][0];
    }

    // Get the marketing fields
    $marketing_permissions = !empty($dummy_member['marketing_permissions']) ? $dummy_member['marketing_permissions'] : [];

    // If we created a dummy member, delete (archive) it from the list
    if( $should_delete_dummy ) {
      $links = $dummy_member['_links'];
      $delete_permanent = $links[array_search('delete', array_column($links, 'rel'))];

      $delete_params = $params;
      $delete_params['method'] = $delete_permanent['method'];
      $request = wp_remote_post( $delete_permanent['href'], $delete_params );
      $response = wp_remote_retrieve_body( $request );
    }

		return $marketing_permissions;
	}
  
  /**
	 * Render marketing permissions
	 * @param  array $details  Saved data
	 */
	function mailchimp_rest_api_get_marketing_permissions() {

		// Variables
		$permissions = mailchimp_rest_api_get_list_marketing_permissions();
		$html = '<h3>' . __('Marketing permissions', 'mailchimp_rest_api') . '</h3> <ul>';

		foreach ( $permissions as $permission ) {
			$html .= '<li>' .
          esc_html($permission['text']) . ' (<strong>' . $permission['marketing_permission_id'] . '</strong>)' .
          '</li>';
    }
    
    $html .= '</ul>';

		echo $html;
	}


	/**
	 * Render interest groups
	 * @param  array $details  Saved data
	 */
	function mailchimp_rest_api_get_interest_groups() {

		// Variables
		$categories = mailchimp_rest_api_get_mailchimp_group_data();
		$html = '<h3>' . __('Groups', 'mailchimp_rest_api') . '</h3>';

		foreach ( $categories['categories'] as $category ) {
			$html .=
				'<strong>' . esc_html($category['title']) . ' (' . $category['id'] . ')</strong>' .
				'<ul>';
			$groups = mailchimp_rest_api_get_mailchimp_group_data($category['id']);

			foreach ($groups['interests'] as $group) {
				$html .=
					'<li>' .
						esc_html($group['name']) . ': ' . esc_attr($group['id']) .
					'</li>';
			}
			$html .= '</ul>';

		}

		echo $html;
	}



	/**
	 * Render interest groups
	 * @param  array $details  Saved data
	 */
	function mailchimp_rest_api_get_tags() {

		// Variables
		$tags = mailchimp_rest_api_get_mailchimp_tag_data();
		$html = '<h3>' . __('Tags', 'mailchimp_rest_api') . '</h3>';

		foreach ($tags['segments'] as $tag) {
			$html .= '<li>' . $tag['name'] . ': ' . $tag['id'] . '</li>';
		}

		echo '<ul>' . $html . '</ul>';
	}



	/**
	 * Theme Options Menu
	 * Each option field requires its own add_settings_field function.
	 */

	// Create theme options menu
	// The content that's rendered on the menu page.
	function mailchimp_rest_api_theme_options_render_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'MailChimp WP Rest API Settings', 'mailchimp_rest_api' ); ?></h2>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'mailchimp_rest_api_options' );
					do_settings_sections( 'mailchimp_rest_api_options' );
					submit_button();
					mailchimp_rest_api_get_marketing_permissions();
					mailchimp_rest_api_get_interest_groups();
					mailchimp_rest_api_get_tags();
				?>
			</form>
		</div>
		<?php
	}

	// Register the theme options page and its fields
	function mailchimp_rest_api_theme_options_init() {

		// Register a setting and its sanitization callback
		// register_setting( $option_group, $option_name, $sanitize_callback );
		// $option_group - A settings group name.
		// $option_name - The name of an option to sanitize and save.
		// $sanitize_callback - A callback function that sanitizes the option's value.
		register_setting( 'mailchimp_rest_api_options', 'mailchimp_rest_api_theme_options', 'mailchimp_rest_api_theme_options_validate' );


		// Register our settings field group
		// add_settings_section( $id, $title, $callback, $page );
		// $id - Unique identifier for the settings section
		// $title - Section title
		// $callback - // Section callback (we don't want anything)
		// $page - // Menu slug, used to uniquely identify the page. See mailchimp_rest_api_theme_options_add_page().
		add_settings_section( 'mailchimp_rest_api', '', '__return_false', 'mailchimp_rest_api_options' );


		// Register our individual settings fields
		// add_settings_field( $id, $title, $callback, $page, $section );
		// $id - Unique identifier for the field.
		// $title - Setting field title.
		// $callback - Function that creates the field (from the Theme Option Fields section).
		// $page - The menu page on which to display this field.
		// $section - The section of the settings page in which to show the field.
		add_settings_field( 'mailchimp_api_key', __( 'API Key', 'mailchimp_rest_api' ), 'mailchimp_rest_api_settings_field_mailchimp_rest_api_api_key', 'mailchimp_rest_api_options', 'mailchimp_rest_api' );
		add_settings_field( 'mailchimp_list_id', __( 'List ID', 'mailchimp_rest_api' ), 'mailchimp_rest_api_settings_field_mailchimp_rest_api_list_id', 'mailchimp_rest_api_options', 'mailchimp_rest_api' );
		add_settings_field( 'key', __( 'Form Key', 'mailchimp_rest_api' ), 'mailchimp_rest_api_settings_field_key', 'mailchimp_rest_api_options', 'mailchimp_rest_api' );
		add_settings_field( 'secret', __( 'Form Secret', 'mailchimp_rest_api' ), 'mailchimp_rest_api_settings_field_secret', 'mailchimp_rest_api_options', 'mailchimp_rest_api' );
		add_settings_field( 'honeypot', __( 'Honeypot', 'mailchimp_rest_api' ), 'mailchimp_rest_api_settings_field_honeypot', 'mailchimp_rest_api_options', 'mailchimp_rest_api' );
    add_settings_field( 'origin', __( 'Allowed Domains', 'mailchimp_rest_api' ), 'mailchimp_rest_api_settings_field_origin', 'mailchimp_rest_api_options', 'mailchimp_rest_api' );
    add_settings_field( 'required_merge', __( 'Required MERGE fields', 'mailchimp_rest_api' ), 'mailchimp_rest_api_settings_field_required_merge', 'mailchimp_rest_api_options', 'mailchimp_rest_api' );

	}
	add_action( 'admin_init', 'mailchimp_rest_api_theme_options_init' );

	// Add the theme options page to the admin menu
	// Use add_theme_page() to add under Appearance tab (default).
	// Use add_menu_page() to add as it's own tab.
	// Use add_submenu_page() to add to another tab.
	function mailchimp_rest_api_theme_options_add_page() {

		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		// $page_title - Name of page
		// $menu_title - Label in menu
		// $capability - Capability required
		// $menu_slug - Used to uniquely identify the page
		// $function - Function that renders the options page
		// $theme_page = add_theme_page( __( 'Theme Options', 'mailchimp_rest_api' ), __( 'Theme Options', 'mailchimp_rest_api' ), 'edit_theme_options', 'theme_options', 'mailchimp_rest_api_theme_options_render_page' );

		// $theme_page = add_menu_page( __( 'Theme Options', 'mailchimp_rest_api' ), __( 'Theme Options', 'mailchimp_rest_api' ), 'edit_theme_options', 'theme_options', 'mailchimp_rest_api_theme_options_render_page' );
		$theme_page = add_submenu_page( 'options-general.php', __( 'MailChimp API', 'mailchimp_rest_api' ), __( 'MailChimp API', 'mailchimp_rest_api' ), 'edit_theme_options', 'mailchimp_rest_api_options', 'mailchimp_rest_api_theme_options_render_page' );
	}
	add_action( 'admin_menu', 'mailchimp_rest_api_theme_options_add_page' );



	// Restrict access to the theme options page to admins
	function mailchimp_rest_api_option_page_capability( $capability ) {
		return 'edit_theme_options';
	}
	add_filter( 'option_page_capability_mailchimp_rest_api_options', 'mailchimp_rest_api_option_page_capability' );
