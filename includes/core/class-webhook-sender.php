<?php
/**
 * Webhook Sender
 * Handles the core webhook sending logic
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Webhook_Sender {

    /**
     * Send post data to webhook
     *
     * @param int $post_id Post ID to send
     * @param bool $include_author Whether to include author info
     * @return array Response with 'ok', 'message', and optional 'permalink'
     */
    public static function send($post_id, $include_author = false) {
        $settings = EW_WP_Settings::get();
        $t0 = microtime(true);

        $post = get_post($post_id);
        if (!$post) {
            return ['ok' => false, 'message' => __('Invalid post', 'easy-webhooks-wp')];
        }

        $url = trim($settings['webhook_url']);
        if (!$url) {
            return ['ok' => false, 'message' => __('Webhook URL missing in settings', 'easy-webhooks-wp')];
        }

        // Build payload
        $payload = self::build_payload($post, $include_author);
        
        // Send request
        $json = wp_json_encode($payload);
        $res = wp_remote_post($url, [
            'timeout' => (int)$settings['timeout'],
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $json
        ]);

        $ms = (int)round(1000 * (microtime(true) - $t0));
        $kb = (int)ceil(strlen($json) / 1024);
        $code = is_wp_error($res) ? 0 : wp_remote_retrieve_response_code($res);
        $body = is_wp_error($res) ? '' : wp_remote_retrieve_body($res);

        // Handle errors
        if (is_wp_error($res)) {
            EW_WP_Logger::push([
                'time' => current_time('mysql'),
                'post_id' => $post_id,
                'http' => 'ERR',
                'ok' => 0,
                'message' => sanitize_text_field($res->get_error_message()),
                'ms' => $ms,
                'kb' => $kb,
                'webhook' => $url
            ]);
            return ['ok' => false, 'message' => sprintf(__('Error: %s', 'easy-webhooks-wp'), $res->get_error_message())];
        }

        if ($code < 200 || $code > 299) {
            EW_WP_Logger::push([
                'time' => current_time('mysql'),
                'post_id' => $post_id,
                'http' => $code,
                'ok' => 0,
                'message' => sanitize_text_field(substr($body, 0, 500)),
                'ms' => $ms,
                'kb' => $kb,
                'webhook' => $url
            ]);
            return ['ok' => false, 'message' => sprintf(__('Webhook error %d', 'easy-webhooks-wp'), $code), 'raw' => $body];
        }

        // Success
        $data = json_decode($body, true);
        $resp = [
            'ok' => true,
            'message' => isset($data['message']) ? sanitize_text_field($data['message']) : __('Completed.', 'easy-webhooks-wp'),
            'permalink' => !empty($data['permalink']) ? esc_url_raw($data['permalink']) : get_permalink($post_id),
        ];

        EW_WP_Logger::push([
            'time' => current_time('mysql'),
            'post_id' => $post_id,
            'http' => $code,
            'ok' => 1,
            'message' => sanitize_text_field($resp['message']),
            'ms' => $ms,
            'kb' => $kb,
            'webhook' => $url
        ]);

        return $resp;
    }

    /**
     * Build the payload to send to webhook
     *
     * @param WP_Post $post The post object
     * @param bool $include_author Whether to include author info
     * @return array Payload data
     */
    private static function build_payload($post, $include_author = false) {
        $settings = EW_WP_Settings::get();

        // Collect post meta
        $meta = EW_WP_Meta_Handler::get_post_meta($post->ID);

        // Featured image
        $feat = [];
        if (!empty($settings['include_featured'])) {
            $fid = get_post_thumbnail_id($post->ID);
            $feat = [
                'featured_image_id' => $fid,
                'featured_image_url' => $fid ? wp_get_attachment_url($fid) : null
            ];
        }

        // Base payload
        $payload = array_merge([
            'ID' => $post->ID,
            'type' => $post->post_type,
            'status' => $post->post_status,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt ?: get_the_excerpt($post),
            'slug' => $post->post_name,
            'permalink' => get_permalink($post->ID),
            'author_id' => $post->post_author,
            'meta' => $meta
        ], $feat);

        // Add extended author info
        if ($include_author) {
            $author = self::get_author_data($post->post_author);
            if ($author) {
                $payload['author'] = $author;
            }
        }

        return $payload;
    }

    /**
     * Get author data
     *
     * @param int $author_id Author user ID
     * @return array|null Author data or null
     */
    private static function get_author_data($author_id) {
        $settings = EW_WP_Settings::get();
        $user = get_userdata($author_id);
        
        if (!$user) return null;

        $author = [
            'ID' => (int)$user->ID,
            'display_name' => $user->display_name,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'roles' => $user->roles,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'user_url' => $user->user_url,
            'description' => $user->description
        ];

        if (!empty($settings['include_author_meta'])) {
            $author['meta'] = EW_WP_Meta_Handler::get_user_meta($user->ID);
        }

        return $author;
    }
}
