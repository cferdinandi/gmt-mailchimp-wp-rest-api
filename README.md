# GMT Mailchimp WP Rest API
Add WP Rest API hooks for JS use of the Mailchimp API.

## How to use it

Before doing anything, configure your settings under `Settings > MailChimp API` in the Dashboard.

### The Endpoint

```bash
/wp-json/gmt-mailchimp/v1/subscribe
```

### Form Fields

For field names, use `group[<ID>]` for interest groups and `merge[<MERGE FIELD>]` for merge fields. You should also include a field for honeypot to prevent spambot requests.

### Status Codes

The API will return a JSON object with a code, status, and message.

```js
var response = {
	code: 400,
	status: 'disallowed_domain',
	message: 'This domain is not whitelisted.'
};
```

| `code` | `status`            | `message`                                                                   |
|--------|---------------------|-----------------------------------------------------------------------------|
| `400`  | `disallowed_domain` | This domain is not whitelisted.                                             |
| `400`  | `failed`            | Unable to subscribe at this time. Please try again.                         |
| `400`  | `failed`            | Please use a valid email address.                                           |
| `400`  | `unsubscribed`      | You had previously unsubscribed and cannot be resubscribed using this form. |
| `200`  | `success`           | You're now subscribed.                                                      |
| `200`  | `success`           | Your account has been updated.                                              |