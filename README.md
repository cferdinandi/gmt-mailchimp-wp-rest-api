# GMT EDD WP Rest API
Add WP Rest API hooks into Easy Digital Downloads. Returns an array of purchase IDs.

## How to use it

### The Endpoint

```bash
/wp-json/gmt-edd/v1/users/<user_email_address>
```

### Making a request with WordPress

You'll need to configure an options menu to get the domain, username, and password for authorization. I recommend using the [Application Passwords](https://wordpress.org/plugins/application-passwords/) plugin with this.

```php
wp_remote_request(
	rtrim($options['wp_api_url'], '/') . '/wp-json/gmt-edd/v1/users/' . $email,
	array(
		'method'    => 'GET',
		'headers'   => array(
			'Authorization' => 'Basic ' . base64_encode($options['wp_api_username'] . ':' . $options['wp_api_password']),
		),
	)
);
```