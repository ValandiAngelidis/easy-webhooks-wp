<?php
/**
 * Logs Page
 * Renders the webhook logs page
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Logs_Page {

    /**
     * Initialize hooks
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page']);
    }

    /**
     * Add logs page to admin menu
     */
    public function add_menu_page() {
        add_submenu_page(
            'easy-webhooks-main',
            __('Webhook Logs', 'easy-webhooks-wp'),
            __('Webhook Logs', 'easy-webhooks-wp'),
            'manage_options',
            'easy-webhooks-logs',
            [$this, 'render']
        );
    }

    /**
     * Render logs page
     */
    public function render() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle clear logs action
        if (isset($_POST['clear_logs']) && check_admin_referer('clear_logs')) {
            EW_WP_Logger::clear();
            echo '<div class="updated"><p>' . esc_html__('Logs cleared.', 'easy-webhooks-wp') . '</p></div>';
        }

        $logs = EW_WP_Logger::get_all();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Webhook Logs', 'easy-webhooks-wp') . '</h1>';
        
        // Clear logs button
        echo '<form method="post" style="margin:10px 0">';
        wp_nonce_field('clear_logs');
        echo '<button class="button" name="clear_logs" value="1">' . esc_html__('Clear Logs', 'easy-webhooks-wp') . '</button>';
        echo '</form>';

        // Logs table
        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Time', 'easy-webhooks-wp') . '</th>';
        echo '<th>' . esc_html__('Post', 'easy-webhooks-wp') . '</th>';
        echo '<th>' . esc_html__('HTTP', 'easy-webhooks-wp') . '</th>';
        echo '<th>' . esc_html__('OK', 'easy-webhooks-wp') . '</th>';
        echo '<th>' . esc_html__('Message', 'easy-webhooks-wp') . '</th>';
        echo '<th>' . esc_html__('ms', 'easy-webhooks-wp') . '</th>';
        echo '<th>' . esc_html__('KB', 'easy-webhooks-wp') . '</th>';
        echo '<th>' . esc_html__('Webhook', 'easy-webhooks-wp') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        if (empty($logs)) {
            echo '<tr><td colspan="8">' . esc_html__('No entries yet.', 'easy-webhooks-wp') . '</td></tr>';
        } else {
            foreach ($logs as $l) {
                echo '<tr>';
                echo '<td>' . esc_html($l['time']) . '</td>';
                echo '<td>' . esc_html($l['post_id']) . '</td>';
                echo '<td>' . esc_html($l['http']) . '</td>';
                echo '<td>' . (!empty($l['ok']) ? '✔' : '✖') . '</td>';
                echo '<td>' . esc_html(wp_strip_all_tags($l['message'] ?? '')) . '</td>';
                echo '<td>' . esc_html($l['ms']) . '</td>';
                echo '<td>' . esc_html($l['kb']) . '</td>';
                echo '<td style="max-width:320px;overflow-wrap:anywhere;">' . esc_html($l['webhook']) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}
