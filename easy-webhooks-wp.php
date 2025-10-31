<?php
/**
 * Plugin Name: Easy Webhooks
 * Plugin URI:  https://valandiangelidis.com
 * Description: Connect WordPress to n8n, Make, Zapier, or any custom webhook. Send posts, custom fields, taxonomies, and author data anywhere — instantly and effortlessly.
 * Version:     1.1.1
 * Author:      Valandi Angelidis
 * Author URI:  https://valandiangelidis.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easy-webhooks-wp
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

// Define plugin constants
define('EW_WP_VERSION', '1.1.1');
define('EW_WP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EW_WP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load text domain for translations
add_action('plugins_loaded', function() {
    load_plugin_textdomain('easy-webhooks-wp', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Load core classes
require_once EW_WP_PLUGIN_DIR . 'includes/core/class-settings.php';
require_once EW_WP_PLUGIN_DIR . 'includes/core/class-logger.php';
require_once EW_WP_PLUGIN_DIR . 'includes/core/class-meta-handler.php';
require_once EW_WP_PLUGIN_DIR . 'includes/core/class-webhook-sender.php';

// Load admin classes
if (is_admin()) {
    require_once EW_WP_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
    require_once EW_WP_PLUGIN_DIR . 'includes/admin/class-logs-page.php';
    require_once EW_WP_PLUGIN_DIR . 'includes/admin/class-metabox.php';
    require_once EW_WP_PLUGIN_DIR . 'includes/admin/class-block-editor.php';
    require_once EW_WP_PLUGIN_DIR . 'includes/admin/class-user-webhook-settings.php';
}

/**
 * Main Plugin Class
 * Initializes all components
 */
class EW_WP_Main {
    
    /**
     * Constructor - Initialize the plugin
     */
    public function __construct() {
        $this->init_admin_components();
    }

    /**
     * Initialize admin components
     */
    private function init_admin_components() {
        if (!is_admin()) {
            return;
        }

        new EW_WP_Settings_Page();
        new EW_WP_Logs_Page();
        new EW_WP_Metabox();
        new EW_WP_Block_Editor();
        new EW_WP_User_Webhook_Settings();
    }
}

// Initialize the plugin
new EW_WP_Main();
