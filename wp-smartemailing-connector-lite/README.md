# SmartEmailing Connector Lite

Lightweight WordPress connector for **SmartEmailing** (or any email service with a compatible REST endpoint).

- Shortcode form with double opt‑in (handled by your provider)
- Optional auto‑insert after N paragraphs
- Optional popup with delay + frequency
- AJAX submission to a configurable API URL
- Admin settings (API URL, API key, default list)

## Install
1. Upload folder to `wp-content/plugins/`.
2. Activate **SmartEmailing Connector Lite**.
3. Go to **Settings → SmartEmailing** and fill in your API details.

## Usage
- Shortcode: `[smartemailing_form list="123"]`
- Template tag:
```php
if ( function_exists('secl_render_form') ) {
    secl_render_form();
}
```

> Note: API authentication varies by provider. This plugin sends a JSON payload `{ email, list }` to `API Base URL` with `Authorization: Bearer <API key>` and optional `X-User` header. Adjust your endpoint accordingly.
