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