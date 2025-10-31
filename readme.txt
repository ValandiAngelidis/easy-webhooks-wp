=== Easy Webhooks ===
Contributors: valandiangelidis
Tags: webhook, automation, n8n, zapier, make, integration, api
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WordPress to n8n, Make, Zapier, or any custom webhook. Send posts, custom fields, taxonomies, and author data anywhere — instantly and effortlessly.

== Description ==

**Easy Webhooks** is a powerful, lightweight plugin that connects your WordPress site to external automation platforms and custom webhooks. Send your posts, custom fields, taxonomies, and author data to n8n, Make (formerly Integero), Zapier, or any webhook endpoint with just one click.

= Key Features =

* **One-Click Webhook Sending** - Send posts to your webhook URL directly from the editor (both Classic and Block Editor)
* **User Webhook Trigger** - Dedicated interface to send webhooks with specific user data and their authored posts
* **Rich Data Payload** - Automatically includes post content, meta fields, taxonomies, featured images, and author information
* **Custom Fields Support** - Sends ALL custom fields including ACF, Metabox, Pods, and any other meta data
* **Author Information** - Optionally include detailed author data with user meta fields
* **Featured Image URLs** - Automatically includes featured image ID and URL
* **Flexible Post Types** - Works with posts, pages, custom post types, and WooCommerce products
* **Activity Logging** - Track webhook sends with detailed logs (HTTP status, response time, data size)
* **Configurable Timeout** - Set custom timeout values for slow or fast webhook endpoints
* **Block Editor Support** - Native sidebar panel for Gutenberg editor
* **Classic Editor Support** - Meta box for Classic Editor users
* **Developer Friendly** - Clean, well-documented code with hooks and filters
* **Translation Ready** - Full i18n support with 69 translatable strings

= Perfect For =

* Sending posts to external content management systems
* Syncing WordPress content with databases or APIs
* Triggering automation workflows (n8n, Make, Zapier)
* Building custom integrations with external platforms
* Content distribution to multiple platforms
* Real-time notifications and alerts
* Headless WordPress setups

= How It Works =

1. Install and activate the plugin
2. Go to **Settings > Easy Webhooks** and configure your webhook URL
3. Select which post types should have webhook functionality
4. Open any post/page in the editor
5. Click "Send to Webhook" button
6. Your post data is sent as JSON to your webhook endpoint

= Webhook Payload =

The plugin sends a comprehensive JSON payload including:

* Post ID, title, content, excerpt, slug, permalink
* Post status and post type
* All custom fields (meta data)
* Author ID and optionally full author info with user meta
* Featured image ID and URL (if configured)
* Taxonomies (categories, tags, custom taxonomies)

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* A valid webhook URL endpoint

= Support =

For support, feature requests, or bug reports, please visit the [plugin support forum](https://wordpress.org/support/plugin/easy-webhooks-wp/) or contact the author.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to **Plugins > Add New**
3. Search for "Easy Webhooks"
4. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Go to **Plugins > Add New > Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Activate the plugin

= Configuration =

1. Go to **Easy Webhooks WP > Main Settings** in the WordPress admin menu
2. Enter your **Webhook URL** (the endpoint where data will be sent)
3. Configure **Timeout** (default: 180 seconds)
4. Select **Post Types** you want to enable webhooks for
5. Configure optional settings:
   - Include author info by default
   - Include all custom author meta (ACF/user fields)
   - Include featured image
   - Enable logging
   - Max log entries
6. Click **Save Changes**

= User Webhook Trigger =

The plugin includes a powerful User Webhooks feature accessible from **Easy Webhooks WP > User Webhooks**:

1. **Set User Webhook URL** - Enter a webhook URL specifically for user data (independent from main webhook URL)
2. **Select a User** - Choose from all WordPress users (shows display name and login)
3. **Select Post Types** - Check which post types to include
4. **Preview Posts** - See real-time preview of posts grouped by type with counts
5. **Send Webhook** - Send user data and authored posts to your webhook endpoint

Perfect for:
* Exporting specific user data with their content
* Syncing author information with external systems
* Creating user-specific content reports
* Migrating user data to other platforms

== Frequently Asked Questions ==

= What is a webhook? =

A webhook is an HTTP callback that sends data to a specified URL when triggered. It allows real-time communication between your WordPress site and external services.

= What data is sent to the webhook? =

The plugin sends comprehensive post data including: ID, title, content, excerpt, slug, permalink, status, type, all custom fields (meta), taxonomies, author information, and featured image details.

= Does this work with custom post types? =

Yes! The plugin works with all public post types including custom post types, WooCommerce products, and any other registered post type.

= Does it work with ACF (Advanced Custom Fields)? =

Absolutely! All custom fields are automatically included in the webhook payload, including ACF fields, Metabox fields, Pods, and any other meta data.

= Can I send WooCommerce products? =

Yes, just enable the "product" post type in the settings and you can send product data to your webhook.

= How do I view webhook logs? =

Go to **Tools > Webhook Logs** to see a detailed log of all webhook sends including timestamps, HTTP status codes, response times, and data sizes.

= Can I customize the payload data? =

The plugin includes filters and hooks for developers to customize the payload. Check the source code documentation for available hooks.

= What happens if the webhook fails? =

Failed requests are logged (if logging is enabled) with error details. The plugin displays error messages in the editor so you can troubleshoot.

= Is this secure? =

Yes! The plugin includes:
- Nonce verification for all AJAX requests
- Capability checks (users must have permission to edit the specific post)
- Input sanitization and validation
- Output escaping in admin pages
- No data is stored except optional logs

= Does this affect site performance? =

No. Webhook sends are triggered manually via button click, not automatically on post save. The plugin only loads resources on post editor pages.

= Can I use this with n8n, Make, or Zapier? =

Yes! The plugin works with any service that accepts webhook data. Simply use the webhook URL provided by n8n, Make (Integero), Zapier, or any custom endpoint.

= What format is the data sent in? =

Data is sent as JSON via HTTP POST request with Content-Type: application/json header.

= Can I translate the plugin to my language? =

Yes! The plugin is fully translation-ready with 69 translatable strings. You can contribute translations via:
- WordPress.org translate system (after plugin approval)
- Using POEdit with the included .pot template file in the languages/ folder
- Submitting translations to: info@valandiangelidis.com

Currently available: English (default), Spanish (es_ES)

= How do I send webhooks for specific users? =

Use the new User Webhooks feature:
1. Go to **Easy Webhooks WP > User Webhooks**
2. Enter a User Webhook URL and save it
3. Select a user from the dropdown
4. Choose post types to include
5. Preview the posts that will be sent
6. Click "Send Webhook"

This sends a dedicated payload with user information and all their authored posts for the selected post types.

== Screenshots ==

1. Main Settings page - Configure webhook URL, timeout, post types, and logging
2. User Webhooks page - Select users, post types, preview posts, and send user-specific webhooks
3. Block Editor sidebar - Send to Webhook panel in Gutenberg
4. Classic Editor metabox - Meta box in Classic Editor
5. Webhook Logs page - View detailed logs of all webhook sends

== Changelog ==

= 1.2.0 =
* Added: User Webhooks feature - Send webhooks with specific user data and their authored posts
* Added: Independent webhook URL for user-specific webhooks
* Added: Real-time posts preview when selecting users and post types
* Added: Main menu item "Easy Webhooks WP" in WordPress admin sidebar with icon
* Added: Organized all settings pages under main menu (Main Settings, User Webhooks, Webhook Logs)
* Security: Enhanced AJAX handlers with comprehensive isset() checks
* Security: Added filter_var() URL validation for webhook URLs
* Security: Added parse_url() scheme validation (http/https only)
* Security: Improved error handling with wp_send_json_error()
* Added: 27 new translatable strings for User Webhooks interface
* Added: Spanish translations for all new strings
* Improvement: Better menu organization and navigation
* Improvement: Independent webhook URL storage for user webhooks

= 1.1.1 =
* Security: Enhanced capability checks in AJAX handlers (verify user can edit specific post)
* Security: Added sanitization for webhook response data to prevent XSS
* Security: Moved nonce verification before other checks for better security
* Added: GPL-2.0-or-later license headers
* Added: Full internationalization support - plugin is now translation-ready
* Added: Text domain loading for translations
* Added: POT template file for translators
* Added: Complete Spanish (es_ES) translation
* Added: uninstall.php for proper cleanup on plugin deletion
* Added: 42 translatable strings with proper escaping
* Fixed: Max log entries field now properly disabled when logging is disabled
* Improvement: Max log entries field includes explanatory note
* Improvement: Settings page JS enqueued as separate file instead of inline
* Improvement: All user-facing strings now use WordPress i18n functions

= 1.1.0 =
* Added: Block Editor (Gutenberg) support with native sidebar panel
* Added: Activity logging system with configurable limits
* Added: Logs viewer page (Tools > Webhook Logs)
* Added: Featured image support (ID and URL)
* Added: Author meta fields inclusion option
* Improvement: Enhanced error handling and user feedback
* Improvement: Better payload structure and organization

= 1.0.0 =
* Initial release
* Classic Editor meta box support
* Configurable webhook URL and timeout
* Post type selection
* Author info inclusion option
* Custom fields support
* Basic error handling

== Upgrade Notice ==

= 1.2.0 =
Major update with User Webhooks feature, enhanced security validation, and improved menu organization. Adds ability to send webhooks with specific user data and their authored posts.

= 1.1.1 =
Security update with enhanced permission checks and sanitization. Recommended update for all users.

= 1.1.0 =
Major update adding Block Editor support, logging system, and featured image support.

== Technical Details ==

= Webhook Payload Structure =

Standard post webhook:
```json
{
  "ID": 123,
  "type": "post",
  "status": "publish",
  "title": "Post Title",
  "content": "Full post content...",
  "excerpt": "Post excerpt...",
  "slug": "post-slug",
  "permalink": "https://example.com/post-slug/",
  "author_id": 1,
  "featured_image_id": 456,
  "featured_image_url": "https://example.com/wp-content/uploads/image.jpg",
  "meta": {
    "custom_field_1": "value",
    "custom_field_2": "value"
  },
  "author": {
    "ID": 1,
    "display_name": "John Doe",
    "user_login": "johndoe",
    "user_email": "john@example.com",
    "roles": ["administrator"],
    "first_name": "John",
    "last_name": "Doe",
    "meta": { ... }
  }
}
```

User webhook (from User Webhooks page):
```json
{
  "user": {
    "ID": 12,
    "display_name": "Author Name",
    "user_login": "authorlogin",
    "email": "author@example.com",
    "roles": ["author"],
    "first_name": "First",
    "last_name": "Last",
    "user_url": "https://example.com",
    "description": "User bio...",
    "meta": {
      "user_meta_field": "value"
    }
  },
  "posts": {
    "post": ["Post Title 1", "Post Title 2"],
    "page": ["Page Title 1"],
    "product": ["Product Title 1"]
  }
}
```

= System Requirements =

* WordPress: 5.0+
* PHP: 7.4+
* PHP Extensions: cURL (for wp_remote_post)
* Memory: Standard WordPress requirements

= Developer Information =

GitHub: https://github.com/valandiangelidis/easy-webhooks-wp (if applicable)
Support: https://wordpress.org/support/plugin/easy-webhooks-wp/

== Privacy ==

This plugin:
* Does NOT collect any user data
* Does NOT send data to third parties except the webhook URL you configure
* Does NOT use cookies
* Does NOT track users
* Only stores optional webhook logs locally in your WordPress database
* Is fully GDPR compliant

Webhook logs contain:
* Timestamp
* Post ID
* HTTP status code
* Response message
* Request duration and size
* Webhook URL

You can disable logging or clear logs at any time from the settings.

== Translations ==

The plugin is translation-ready and includes:
* Complete internationalization (i18n) support
* 69 translatable strings
* POT template file for translators
* Spanish (es_ES) translation included

**Contribute translations:**
* Email: info@valandiangelidis.com
* WordPress.org: https://translate.wordpress.org/projects/wp-plugins/easy-webhooks-wp/ (after approval)

**Translation files location:** `/languages/`
