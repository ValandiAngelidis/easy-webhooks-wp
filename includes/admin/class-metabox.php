<?php
/**
 * Classic Editor Meta Box
 * Adds a meta box to classic editor for sending posts to webhook
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Metabox {
    const NONCE_ACTION = 'ew_wp_send';
    const NONCE_FIELD = '_ew_wp_nonce';
    const AJAX_ACTION = 'ew_wp_send_post_to_webhook';

    /**
     * Initialize hooks
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'ajax_handler']);
    }

    /**
     * Register meta box
     */
    public function register() {
        $settings = EW_WP_Settings::get();
        foreach ($settings['post_types'] as $pt) {
            add_meta_box(
                'ew_wp_box',
                __('Send to Webhook', 'easy-webhooks-wp'),
                [$this, 'render'],
                $pt,
                'side',
                'high'
            );
        }
    }

    /**
     * Enqueue scripts for meta box
     */
    public function enqueue_scripts($hook) {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_script(
            'ew-wp-metabox',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/metabox.js',
            ['jquery'],
            '1.1.1',
            true
        );

        wp_localize_script('ew-wp-metabox', 'EW_WP_AJAX', [
            'action' => self::AJAX_ACTION,
            'nonce_field' => self::NONCE_FIELD
        ]);
    }

    /**
     * Render meta box
     */
    public function render($post) {
        $settings = EW_WP_Settings::get();
        $default = !empty($settings['include_author_global']);

        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);

        echo '<p>';
        echo '<label>';
        echo '<input type="checkbox" id="ew-wp-include-author" ' . checked($default, true, false) . ' /> ';
        echo esc_html__('Include post author info', 'easy-webhooks-wp');
        echo '</label>';
        echo '</p>';

        echo '<p>';
        echo '<button type="button" class="button button-primary" id="ew-wp-send">' . esc_html__('Send to Webhook', 'easy-webhooks-wp') . '</button>';
        echo '</p>';

        echo '<div id="ew-wp-status" style="margin-top:6px;white-space:pre-line;"></div>';
    }

    /**
     * AJAX handler for sending post
     */
    public function ajax_handler() {
        // Verify nonce first
        if (!wp_verify_nonce($_POST[self::NONCE_FIELD] ?? '', self::NONCE_ACTION)) {
            wp_send_json(['ok' => false, 'message' => __('Invalid nonce', 'easy-webhooks-wp')], 403);
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id || !get_post($post_id)) {
            wp_send_json(['ok' => false, 'message' => __('Invalid post', 'easy-webhooks-wp')], 400);
        }

        // Check user can edit THIS specific post
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json(['ok' => false, 'message' => __('Unauthorized', 'easy-webhooks-wp')], 403);
        }

        $include_author = !empty($_POST['include_author']);
        $result = EW_WP_Webhook_Sender::send($post_id, $include_author);

        if ($result['ok']) {
            wp_send_json($result, 200);
        } else {
            wp_send_json($result, 500);
        }
    }
}
