# Easy Webhooks for WordPress

![WordPress Plugin Version](https://img.shields.io/badge/version-1.1.1-blue.svg)
![WordPress Compatibility](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)
![PHP Compatibility](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green.svg)
![Translation Ready](https://img.shields.io/badge/i18n-translation%20ready-green.svg)

Connect WordPress to n8n, Make, Zapier, or any custom webhook. Send posts, custom fields, taxonomies, and author data anywhere — instantly and effortlessly.

## ✨ Features

- **One-Click Webhook Sending** - Send posts to your webhook URL directly from the editor (both Classic and Block Editor)
- **User Webhook Trigger** - Dedicated interface to send webhooks with specific user data and their authored posts
- **Rich Data Payload** - Automatically includes post content, meta fields, taxonomies, featured images, and author information
- **Custom Fields Support** - Sends ALL custom fields including ACF, Metabox, Pods, and any other meta data
- **Author Information** - Optionally include detailed author data with user meta fields
- **Featured Image URLs** - Automatically includes featured image ID and URL
- **Flexible Post Types** - Works with posts, pages, custom post types, and WooCommerce products
- **Activity Logging** - Track webhook sends with detailed logs (HTTP status, response time, data size)
- **Configurable Timeout** - Set custom timeout values for slow or fast webhook endpoints
- **Block Editor Support** - Native sidebar panel for Gutenberg editor
- **Classic Editor Support** - Meta box for Classic Editor users
- **Developer Friendly** - Clean, well-documented code with hooks and filters
- **Translation Ready** - Full i18n support with 69 translatable strings (Spanish included)

## 🎯 Perfect For

- Sending posts to external content management systems
- Syncing WordPress content with databases or APIs
- Triggering automation workflows (n8n, Make, Zapier)
- Building custom integrations with external platforms
- Content distribution to multiple platforms
- Real-time notifications and alerts
- Headless WordPress setups

## 📦 Installation

### From WordPress.org (Recommended)

1. Log in to your WordPress admin panel
2. Go to **Plugins > Add New**
3. Search for "Easy Webhooks"
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the latest release from [WordPress.org](https://wordpress.org/plugins/easy-webhooks-wp/) or [GitHub Releases](https://github.com/ValandiAngelidis/easy-webhooks-wp/releases)
2. Upload the plugin folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

### Via Composer

```bash
composer require wpackagist-plugin/easy-webhooks-wp
```

## ⚙️ Configuration

### Main Settings

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

### User Webhook Trigger

1. Go to **Easy Webhooks WP > User Webhooks** in the WordPress admin menu
2. **Set User Webhook URL** - Enter a webhook URL specifically for user data and click "Save URL" (independent from main webhook settings)
3. **Select a User** from the dropdown (displays all WordPress users with their display name and login)
4. **Select Post Types** - Check the post types to include authored posts from the selected user
5. **Preview Posts** - See a real-time preview of post titles that will be included, grouped by post type with post counts
6. Click **"Send Webhook"** to immediately send a webhook with the selected user's data and their authored posts

This feature is perfect for:
- Exporting specific user data with their content portfolio
- Syncing author information with external systems
- Creating user-specific content reports
- Migrating user data to other platforms

## 🚀 Usage

### Sending a Post to Webhook

#### In Block Editor (Gutenberg)
1. Open any post/page in the Block Editor
2. Look for the **Easy Webhooks** panel in the right sidebar
3. Toggle "Include post author info" if needed
4. Click **Send to Webhook**

#### In Classic Editor
1. Open any post/page in the Classic Editor
2. Find the **Send to Webhook** meta box (usually in the sidebar)
3. Check "Include post author info" if needed
4. Click **Send to Webhook**

### Viewing Logs

Go to **Easy Webhooks WP > Webhook Logs** to see a detailed log of all webhook sends including:
- Timestamp
- Post ID
- HTTP status code
- Success/failure indicator
- Response message
- Request duration (ms)
- Data size (KB)
- Webhook URL

## 📡 Webhook Payload

The plugin sends a comprehensive JSON payload. When user webhook settings are configured, the payload will also include user and posts data:

### Standard Payload
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
    "custom_field_2": "value",
    "_acf_field": "ACF value"
  },
  "author": {
    "ID": 1,
    "display_name": "John Doe",
    "user_login": "johndoe",
    "user_email": "john@example.com",
    "roles": ["administrator"],
    "first_name": "John",
    "last_name": "Doe",
    "user_url": "https://example.com",
    "description": "Author bio...",
    "meta": {
      "user_custom_field": "value"
    }
  }
}
```

### Enhanced Payload with User Webhook Trigger
When using the User Webhook Trigger (**Easy Webhooks WP > User Webhooks**), a dedicated webhook is sent with the following structure:

```json
{
  "user": {
    "ID": 12,
    "display_name": "Selected User",
    "user_login": "selecteduser",
    "email": "selected@example.com",
    "roles": ["author"],
    "first_name": "First",
    "last_name": "Last",
    "user_url": "https://example.com",
    "description": "User bio...",
    "meta": {
      "user_meta_field": "value",
      "another_field": "another_value"
    }
  },
  "posts": {
    "post": ["Post Title 1", "Post Title 2"],
    "page": ["Page Title 1"],
    "product": ["Product Title 1"],
    "custom_post_type": ["Custom Post Title"]
  }
}
```

This payload structure is ideal for:
- User data exports with content portfolio
- Author-specific content synchronization
- User migration between platforms
- Content attribution tracking

## 🔧 Integration Examples

### n8n Workflow
1. Create a Webhook node in n8n
2. Copy the webhook URL
3. Paste it in Easy Webhooks settings
4. Configure your workflow to process the incoming data

### Make (Integromat)
1. Create a new scenario with a Webhook trigger
2. Copy the webhook URL from Make
3. Paste it in Easy Webhooks settings
4. Build your automation flow

### Zapier
1. Create a new Zap with a "Webhooks by Zapier" trigger
2. Choose "Catch Hook" and copy the webhook URL
3. Paste it in Easy Webhooks settings
4. Configure your Zap actions

### Custom Endpoint
Create a simple endpoint to receive the data:

```php
<?php
// receive-webhook.php
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Process the data
error_log('Received post: ' . $data['title']);

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'message' => 'Post received successfully',
    'permalink' => 'https://your-site.com/custom-permalink'
]);
```

## 🛠️ Development

### Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- Composer (for development dependencies)

### Setup

```bash
# Clone the repository
git clone https://github.com/ValandiAngelidis/easy-webhooks-wp.git
cd easy-webhooks-wp

# Install development dependencies (if any)
composer install --dev

# Create a symlink in your WordPress plugins directory
ln -s $(pwd) /path/to/wordpress/wp-content/plugins/easy-webhooks-wp
```

### Project Structure

```
easy-webhooks-wp/
├── assets/
│   └── js/
│       ├── block-editor.js      # Gutenberg integration
│       ├── metabox.js            # Classic editor integration
│       └── settings-page.js      # Settings page UI
├── includes/
│   ├── admin/
│   │   ├── class-block-editor.php
│   │   ├── class-logs-page.php
│   │   ├── class-metabox.php
│   │   ├── class-settings-page.php
│   │   └── class-user-webhook-settings.php
│   └── core/
│       ├── class-logger.php
│       ├── class-meta-handler.php
│       ├── class-settings.php
│       └── class-webhook-sender.php
├── languages/
│   ├── easy-webhooks-wp.pot      # Translation template
│   ├── easy-webhooks-wp-es_ES.po # Spanish translation
│   └── README.md                 # Translation guide
├── easy-webhooks-wp.php          # Main plugin file
├── uninstall.php                 # Cleanup on uninstall
├── readme.txt                    # WordPress.org readme
└── README.md                     # This file
```

### Coding Standards

This plugin follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/). 

Run PHPCS to check your code:
```bash
phpcs --standard=WordPress easy-webhooks-wp.php includes/
```

## 🔒 Security

This plugin implements WordPress security best practices:

- ✅ Nonce verification for all AJAX requests
- ✅ Capability checks (users must have permission to edit the specific post)
- ✅ Input sanitization and validation
- ✅ Output escaping in admin pages
- ✅ Webhook response sanitization to prevent XSS
- ✅ No SQL injection vulnerabilities (uses WordPress APIs)
- ✅ CSRF protection on all forms

**Report security issues:** Please email security concerns to info@valandiangelidis.com

## 🌍 Translations

The plugin is **fully translation-ready** with complete internationalization support.

### Available Languages
- 🇺🇸 **English** (default)
- 🇪🇸 **Spanish** (es_ES) - Complete

### Contribute Translations

We welcome translations in all languages!

**How to contribute:**
1. Use the POT template file in `/languages/easy-webhooks-wp.pot`
2. Translate using [POEdit](https://poedit.net/) or similar tool
3. Submit via:
   - Pull request on GitHub
   - Email to info@valandiangelidis.com
   - WordPress.org translate (after plugin approval)

**Translation stats:**
- Total strings: 69
- Coverage: 100%
- Files: All PHP files use proper i18n functions

See `/languages/README.md` for detailed translation guide.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Contribution Guidelines

- Follow WordPress Coding Standards
- Add PHPDoc comments to all functions/methods
- Test your changes thoroughly
- Update documentation if needed

## 📄 License

This project is licensed under the GPL-2.0-or-later License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **WordPress.org Support:** [Plugin Support Forum](https://wordpress.org/support/plugin/easy-webhooks-wp/)
- **Documentation:** [Plugin Documentation](https://wordpress.org/plugins/easy-webhooks-wp/)
- **Email Support:** info@valandiangelidis.com

## 📝 Changelog

### 1.2.0 (2025-10-31)
- **Added:** User Webhooks feature - Send webhooks with specific user data and their authored posts
- **Added:** Independent webhook URL for user-specific webhooks
- **Added:** Real-time posts preview when selecting users and post types
- **Added:** Main menu item "Easy Webhooks WP" in WordPress admin sidebar with dashicons-admin-links icon
- **Added:** Organized all settings pages under main menu (Main Settings, User Webhooks, Webhook Logs)
- **Security:** Enhanced AJAX handlers with comprehensive isset() checks
- **Security:** Added filter_var() URL validation for webhook URLs
- **Security:** Added parse_url() scheme validation (http/https only)
- **Security:** Improved error handling with wp_send_json_error()
- **Added:** 27 new translatable strings for User Webhooks interface
- **Added:** Spanish translations for all new strings
- **Improvement:** Better menu organization and navigation
- **Improvement:** Independent webhook URL storage for user webhooks

### 1.1.1 (2025-10-30)
- **Security:** Enhanced capability checks in AJAX handlers
- **Security:** Added sanitization for webhook response data
- **Security:** Moved nonce verification before other checks
- **Added:** GPL-2.0-or-later license headers
- **Added:** Full internationalization support - plugin is now translation-ready
- **Added:** Text domain loading for translations
- **Added:** POT template file for translators
- **Added:** Complete Spanish (es_ES) translation
- **Added:** uninstall.php for proper cleanup
- **Added:** 42 translatable strings with proper escaping
- **Fixed:** Max log entries field now disabled when logging is disabled
- **Improvement:** Settings page JS enqueued as separate file
- **Improvement:** All user-facing strings use WordPress i18n functions

### 1.1.0
- **Added:** Block Editor (Gutenberg) support
- **Added:** Activity logging system
- **Added:** Logs viewer page
- **Added:** Featured image support
- **Added:** Author meta fields option
- **Improvement:** Enhanced error handling

### 1.0.0
- Initial release
- Classic Editor support
- Basic webhook functionality

## 🧑‍💻 Credits

**Developed by:**

### 🧠 Valandi Angelidis
Multidisciplinary creator & full-stack developer specializing in digital ecosystems, automation, and medical marketing technologies.

- 🌐 **Website:** [valandiangelidis.com](https://valandiangelidis.com)
- 💌 **Email:** [info@valandiangelidis.com](mailto:info@valandiangelidis.com)
- 💾 **GitHub:** [github.com/ValandiAngelidis](https://github.com/ValandiAngelidis)

---

**Made with ❤️ for the WordPress community**

*If you find this plugin helpful, please consider [leaving a review](https://wordpress.org/support/plugin/easy-webhooks-wp/reviews/) on WordPress.org!*
