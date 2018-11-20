# GMT Mailchimp WP Rest API
Add WP Rest API hooks for JS use of the Mailchimp API.

## How to use it

Before doing anything, configure your settings under `Settings > MailChimp API` in the Dashboard.

You can also find the API Interest Group IDs needed for the field values below.

### The Endpoint

```bash
/wp-json/gmt-mailchimp/v1/subscribe
```

### Form Fields

The only required field is an email address. Give the `name` attribute a value of `email`.

```html
<input type="email" name="email">
```

For field names, use `group[<ID>]` for interest groups and `merge[<MERGE FIELD>]` for merge fields. You should also include a field for honeypot to prevent spambot requests.

```html
<!-- This is a fake field to trick spam bots -->
<!-- Do not use display: hidden. You need to visually hide the field but leave it discoverable -->
<!-- More on that here: https://gomakethings.com/hidden-content-for-better-a11y/ -->
<div class="some-class-to-visually-hide-this-field">
	<label form="confirm-email">If you are human, leave this blank</label>
	<input type="email" name="confirm-email" id="confirm-email">
</div>

<!-- Your groups and merge tags -->
<input type="hidden" name="group[1234]" value="1">
```

If you only want a form to update existing members but *not* create new ones, add a `hidden` field with a `name` of `do-not-create`.

```html
<input type="hidden" name="do-not-create" value="1">
```

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
| `400`  | `invalid_user`      | This subscriber does not exist.                                             |
| `400`  | `unsubscribed`      | You had previously unsubscribed and cannot be resubscribed using this form. |
| `200`  | `success`           | You're now subscribed.                                                      |
| `200`  | `success`           | Your account has been updated.                                              |