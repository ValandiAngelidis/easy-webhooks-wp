<?php
/**
 * Settings Page
 * Renders the admin settings page
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Settings_Page {

    /**
     * Initialize hooks
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Enqueue scripts for settings page
     */
    public function enqueue_scripts($hook) {
        // Only load on our settings page
        if ($hook !== 'toplevel_page_easy-webhooks-main') {
            return;
        }

        wp_enqueue_script(
            'easy-webhooks-settings',
            plugins_url('assets/js/settings-page.js', dirname(dirname(__FILE__))),
            [],
            '1.0.0',
            true
        );
    }

    /**
     * Add settings page to admin menu
     */
    public function add_menu_page() {
        // Add main menu item
        add_menu_page(
            __('Easy Webhooks WP', 'easy-webhooks-wp'),
            __('Easy Webhooks WP', 'easy-webhooks-wp'),
            'manage_options',
            'easy-webhooks-main',
            [$this, 'render'],
            'dashicons-admin-links', // Icon for webhooks/links
            30 // Position in menu
        );
        
        // Add submenu for main settings (will replace the main menu when clicked)
        add_submenu_page(
            'easy-webhooks-main',
            __('Main Settings', 'easy-webhooks-wp'),
            __('Main Settings', 'easy-webhooks-wp'),
            'manage_options',
            'easy-webhooks-main', // Same as parent to make it the default
            [$this, 'render']
        );
    }

    /**
     * Register settings and fields
     */
    public function register_settings() {
        register_setting(
            EW_WP_Settings::OPT_KEY,
            EW_WP_Settings::OPT_KEY,
            [EW_WP_Settings::class, 'sanitize']
        );

        add_settings_section(
            'ew_wp_main',
            __('Main Settings', 'easy-webhooks-wp'),
            function () {
                echo '<p>' . esc_html__('Configure webhook URL, timeout, post types, author settings, and logging.', 'easy-webhooks-wp') . '</p>';
            },
            EW_WP_Settings::OPT_KEY
        );

        // Webhook URL
        add_settings_field(
            'webhook_url',
            __('Webhook URL', 'easy-webhooks-wp'),
            [$this, 'render_webhook_url_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );

        // Timeout
        add_settings_field(
            'timeout',
            __('Timeout (seconds)', 'easy-webhooks-wp'),
            [$this, 'render_timeout_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );

        // Post types
        add_settings_field(
            'post_types',
            __('Post types', 'easy-webhooks-wp'),
            [$this, 'render_post_types_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );

        // Include Author Default
        add_settings_field(
            'include_author_global',
            __('Include author info by default', 'easy-webhooks-wp'),
            [$this, 'render_include_author_global_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );

        // Include all author meta
        add_settings_field(
            'include_author_meta',
            __('Include all custom author meta (ACF/user fields)', 'easy-webhooks-wp'),
            [$this, 'render_include_author_meta_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );

        // Include Featured
        add_settings_field(
            'include_featured',
            __('Include featured image', 'easy-webhooks-wp'),
            [$this, 'render_include_featured_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );

        // Logging
        add_settings_field(
            'log_enabled',
            __('Enable logging', 'easy-webhooks-wp'),
            [$this, 'render_log_enabled_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );

        add_settings_field(
            'log_limit',
            __('Max log entries', 'easy-webhooks-wp'),
            [$this, 'render_log_limit_field'],
            EW_WP_Settings::OPT_KEY,
            'ew_wp_main'
        );
    }

    /**
     * Render settings page
     */
    public function render() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Easy Webhooks', 'easy-webhooks-wp') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields(EW_WP_Settings::OPT_KEY);
        do_settings_sections(EW_WP_Settings::OPT_KEY);
        submit_button();
        echo '</form></div>';
    }

    /**
     * Render webhook URL field
     */
    public function render_webhook_url_field() {
        $s = EW_WP_Settings::get();
        printf(
            '<input type="url" name="%1$s[webhook_url]" value="%2$s" class="regular-text" placeholder="https://example.com/webhook/..." />',
            esc_attr(EW_WP_Settings::OPT_KEY),
            esc_attr($s['webhook_url'])
        );
    }

    /**
     * Render timeout field
     */
    public function render_timeout_field() {
        $s = EW_WP_Settings::get();
        printf(
            '<input type="number" min="5" step="1" name="%1$s[timeout]" value="%2$d" class="small-text" />',
            esc_attr(EW_WP_Settings::OPT_KEY),
            intval($s['timeout'])
        );
    }

    /**
     * Render post types field
     */
    public function render_post_types_field() {
        $s = EW_WP_Settings::get();
        $enabled = (array) $s['post_types'];
        $pts = get_post_types(['show_ui' => true], 'objects');
        
        foreach ($pts as $pt => $obj) {
            printf(
                '<label style="display:block;margin:3px 0;"><input type="checkbox" name="%1$s[post_types][]" value="%2$s" %3$s /> %4$s (%2$s)</label>',
                esc_attr(EW_WP_Settings::OPT_KEY),
                esc_attr($pt),
                checked(in_array($pt, $enabled, true), true, false),
                esc_html($obj->labels->singular_name ?: $pt)
            );
        }
    }

    /**
     * Render include author global field
     */
    public function render_include_author_global_field() {
        $s = EW_WP_Settings::get();
        printf(
            '<label><input type="checkbox" name="%1$s[include_author_global]" value="1" %2$s /> %3$s</label>',
            esc_attr(EW_WP_Settings::OPT_KEY),
            checked((int)$s['include_author_global'], 1, false),
            esc_html__('Checked = default ON in editor', 'easy-webhooks-wp')
        );
    }

    /**
     * Render include author meta field
     */
    public function render_include_author_meta_field() {
        $s = EW_WP_Settings::get();
        printf(
            '<label><input type="checkbox" name="%1$s[include_author_meta]" value="1" %2$s /> %3$s</label>',
            esc_attr(EW_WP_Settings::OPT_KEY),
            checked((int)$s['include_author_meta'], 1, false),
            esc_html__('Checked = send all user meta fields for author', 'easy-webhooks-wp')
        );
    }

    /**
     * Render include featured field
     */
    public function render_include_featured_field() {
        $s = EW_WP_Settings::get();
        printf(
            '<label><input type="checkbox" name="%1$s[include_featured]" value="1" %2$s /> %3$s</label>',
            esc_attr(EW_WP_Settings::OPT_KEY),
            checked((int)$s['include_featured'], 1, false),
            esc_html__('Add featured_image_id + featured_image_url', 'easy-webhooks-wp')
        );
    }

    /**
     * Render log enabled field
     */
    public function render_log_enabled_field() {
        $s = EW_WP_Settings::get();
        printf(
            '<label><input id="ew_log_enabled" type="checkbox" name="%1$s[log_enabled]" value="1" %2$s /> %3$s</label>',
            esc_attr(EW_WP_Settings::OPT_KEY),
            checked((int)$s['log_enabled'], 1, false),
            esc_html__('Keep a record of recent sends', 'easy-webhooks-wp')
        );
    }

    /**
     * Render log limit field
     */
    public function render_log_limit_field() {
        $s = EW_WP_Settings::get();
        $disabled = ((int)$s['log_enabled']) ? '' : ' disabled';
        printf(
            '<input id="ew_log_limit_input" type="number" min="10" step="10" name="%1$s[log_limit]" value="%2$d" class="small-text"%3$s /><p class="description">%4$s</p>',
            esc_attr(EW_WP_Settings::OPT_KEY),
            intval($s['log_limit']),
            $disabled,
            esc_html__('Only applies when logging is enabled.', 'easy-webhooks-wp')
        );
    }
}
