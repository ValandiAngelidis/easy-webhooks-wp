<?php
/**
 * Block Editor Integration
 * Adds sidebar panel to Gutenberg editor for sending posts to webhook
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Block_Editor {
    const NONCE_ACTION = 'ew_wp_send';
    const NONCE_FIELD = '_ew_wp_nonce';
    const AJAX_ACTION = 'ew_wp_send_post_to_webhook';

    /**
     * Initialize hooks
     */
    public function __construct() {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_assets']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'ajax_handler']);
    }

    /**
     * Enqueue block editor assets
     */
    public function enqueue_assets() {
        if (!function_exists('wp_add_inline_script')) {
            return;
        }

        $settings = EW_WP_Settings::get();
        $nonce = wp_create_nonce(self::NONCE_ACTION);
        $default = $settings['include_author_global'] ? 'true' : 'false';

        wp_enqueue_script(
            'ew-wp-block-editor',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/block-editor.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'],
            '1.1.1',
            true
        );

        wp_localize_script('ew-wp-block-editor', 'EW_WP_BLOCK_EDITOR', [
            'action' => self::AJAX_ACTION,
            'nonce_field' => self::NONCE_FIELD,
            'nonce' => $nonce,
            'defaultAuthor' => $default === 'true'
        ]);
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
