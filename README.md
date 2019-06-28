# GMT Mailchimp WP Rest API
Add WP Rest API middleman for the [Mailchimp API](https://developer.mailchimp.com/documentation/mailchimp/).

This allows you to keep your credentials private on the server when using the Mailchimp API in client-side code (aka JavaScript).

**Supported Use Cases**

- Adding subscribers
- Updating existing subscriber interest groups
- Getting subscriber counts for your entire list or a specific interest group

## Getting started

First, configure your API settings under `Settings > MailChimp API` in the Dashboard.

**Required Fields**

- `API Key` - Your [API key from Mailchimp](https://mailchimp.com/help/about-api-keys/).
- `List ID` - The [ID of the list/audience](https://mailchimp.com/help/find-audience-id/) that you want to add subscribers to.

**Optional Fields**

- `Form Key`/`Form Secret` - If you're using a form to submit to the API, adding a hidden key/secret field to the form can help minimize bulk robot submissions. Details on how to use it in the `Sign Up Form` section below.
- `Honeypot` - The `name of a hidden` field you can add to your field to help detect bot form submissions without inconveniencing real humans. Details on how to use it in the `Sign Up Form` section below.
- `Allowed Domains` - You can restrict use of the API to only calls that happen from an allowed list of domains. Separate allowed domains with a comma.

**API Details**

You can find a list of categories and interest groups at the bottom of the settings page. It includes category and group IDs that you will need to add users to these groups with the API.



## The Endpoints

The *GMT Mailchimp WP Rest API* supports two endpoints.

### `/subscribe`

Add a new subscriber or update an existing one.

**HTTP Method:** `POST`

**Endpoint**

```bash
<your-website-domain>/wp-json/gmt-mailchimp/v1/subscribe
```

**Parameters**

Pass these along as query string parameters on your endpoint.

| Parameter       | Description                                 | Value   | Example              | Required |
|-----------------|---------------------------------------------|---------|----------------------|----------|
| `email`         | The subscriber email address                | String  | `hi@there.com`       | Yes      |
| `merge`         | Any merge tags to add (ex `FNAME`)          | String  | `merge[FNAME]=chris` | No       |
| `group`         | Any interest groups to add                  | Boolean | `group[1234]=1`      | No       |
| `<Form Key>`    | `<Form Secret>` (from your settings)        | String  | `1234=abcdef`        | No       |
| `<Honeypot>`    | A honeypot value if enabled in settings     | String  | `     `              | No       |
| `do-not-create` | Only update existing user, do not subscribe | Boolean | `1`                  | No       |
| `u`             | Remove user from provided interest groups   | Boolean | `1`                  | No       |

**Sample API Calls**

```bash
# Add a subscriber
<your-website-domain>/wp-json/gmt-mailchimp/v1/subscribe?email=developer@awesomewebsites.com&merge[FNAME]=Chris&group[1234]=1

# Remove existing subscriber from a group
<your-website-domain>/wp-json/gmt-mailchimp/v1/subscribe?email=developer@awesomewebsites.com&group[abcde]=0&u=1&do-not-create=1
```

**Sample Response**

The API will return a JSON object with a code, status, and message.

```json
{
	"code": 400,
	"status": "disallowed_domain",
	"message": "This domain is not whitelisted."
}
```

**Status Codes**

| `code` | `status`            | `message`                                                                   |
|--------|---------------------|-----------------------------------------------------------------------------|
| `400`  | `disallowed_domain` | This domain is not whitelisted.                                             |
| `400`  | `failed`            | Unable to subscribe at this time. Please try again.                         |
| `400`  | `failed`            | Please use a valid email address.                                           |
| `400`  | `invalid_user`      | This subscriber does not exist.                                             |
| `400`  | `unsubscribed`      | You had previously unsubscribed and cannot be resubscribed using this form. |
| `200`  | `success`           | You're now subscribed.                                                      |
| `200`  | `success`           | Your account has been updated.                                              |
| `202`  | `pending`           | Almost there! To complete your subscription, please click the confirmation link in the email that was just sent to your inbox. |


### `/count`

Get the subscriber count for a list/audience or interest group.

**HTTP Method:** `GET`

**Endpoint**

```bash
<your-website-domain>/wp-json/gmt-mailchimp/v1/count
```

**Parameters**

Pass these along as query string parameters on your endpoint.

If you do not provide `category` or `id` parameters, it will return to the subscriber count for the entire list/audience.

| Parameter    | Description                               | Value   | Example    | Required |
|--------------|-------------------------------------------|---------|------------|----------|
| `category`   | An interest group category ID             | String  | `12345`    | No       |
| `id`         | An interest group ID                      | String  | `abcde`    | No       |
| `round`      | The value to round down to, if any        | Integer | `10`/`100` | No       |

*The `round` parameter will round down by the specified place value. For example, `round=100` will round `7982` down to `7900`, while `round=10` will round it down to `7980`.*

**Sample API Calls**

```bash
# Get subscriber count for an interest group
<your-website-domain>/wp-json/gmt-mailchimp/v1/count?category=12345&id=abcde

# Get subscriber count for entire list/audience, and round by 100
<your-website-domain>/wp-json/gmt-mailchimp/v1/count?round=100
```

**Sample Response**

The API will return a JSON object with a code, status, and message.

```json
{
	"code": 400,
	"status": "success",
	"message": 7982
}
```

**Status Codes**

| `code` | `status`  | `message`                                         |
|--------|-----------|---------------------------------------------------|
| `400`  | `failed`  | Unable to get subscriber count. Please try again. |
| `200`  | `success` | `<Subscriber Count>`                              |



## Sign Up Form

You can use the *GMT Mailchimp WP Rest API* to provide a more customized Mailchimp signup experience.

###  Required Fields

The only required field is an email address.

```html
<input type="email" name="email">
```

### Field Names

For ease-of-use, you may want to use the API parameter names as the field names. This lets you use a serialize method to create your query string instead of having to manually map fields.

For example, use `group[<ID>]` for interest groups and `merge[<MERGE FIELD>]` for merge fields.

```html
<label for="name">Your Name</label>
<input type="text" name="merge[FNAME]" id="name">

<label for="email">Your Email</label>
<input type="email" name="email" id="email">

<input type="hidden" name="group[1234]" value="1">
<input type="hidden" name="group[abcde]" value="1">
```

### Honeypot

A honeypot is a field that you visually hide in the markup, but that form bots will still see and try to fill out.

If this field has a value, you can assume the submitter was a bot and not a real person. The *GMT Mailchimp WP Rest API* will ignore API calls with a value for parameters that match your honeypot field name.

**Sample HTML**

Give the field a name that sounds real, but with a label telling humans not to fill out (important for screen reader users).

```html
<!-- This is a fake field to trick spam bots -->
<!-- Do not use display: hidden. You need to visually hide the field but leave it discoverable -->
<!-- More on that here: https://gomakethings.com/hidden-content-for-better-a11y/ -->
<div class="gotcha">
	<label form="confirm-email">If you are human, leave this blank</label>
	<input type="email" name="confirm-email" id="confirm-email">
</div>

<!-- Your groups and merge tags -->
<input type="hidden" name="group[1234]" value="1">
```

**Sample CSS**

You want to hide this field *visually only*. That means you should *NOT* use `display: none` to hide it.

```css
/*
 * Hide only visually, but have it available for screen readers:
 * @link https://snook.ca/archives/html_and_css/hiding-content-for-accessibility
 *
 * 1. For long content, line feeds are not interpreted as spaces and small width
 *    causes content to wrap 1 word per line:
 *    https://medium.com/@jessebeach/beware-smushed-off-screen-accessible-text-5952a4c2cbfe
 */
.gotcha {
	border: 0;
	clip: rect(0 0 0 0);
	height: 1px;
	margin: -1px;
	overflow: hidden;
	padding: 0;
	position: absolute;
	white-space: nowrap;
	/* 1 */
	width: 1px;
}
```