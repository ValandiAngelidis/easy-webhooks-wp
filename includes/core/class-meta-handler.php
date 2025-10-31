<?php
/**
 * Meta Handler
 * Normalizes and processes post/user meta data
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Meta_Handler {
    
    /**
     * Normalize meta value (handle serialized data, arrays, objects)
     *
     * @param mixed $v Meta value
     * @return mixed Normalized value
     */
    public static function normalize($v) {
        if (is_string($v)) {
            $m = maybe_unserialize($v);
            return $m !== $v ? self::normalize($m) : $v;
        } elseif (is_array($v)) {
            $o = [];
            foreach ($v as $k => $vv) {
                $o[$k] = self::normalize($vv);
            }
            return $o;
        } elseif (is_object($v)) {
            return self::normalize(json_decode(json_encode($v), true));
        }
        return $v;
    }

    /**
     * Get all post meta for a given post ID
     *
     * @param int $post_id Post ID
     * @return array Normalized post meta
     */
    public static function get_post_meta($post_id) {
        $meta = [];
        foreach (get_post_meta($post_id) as $k => $v) {
            $meta[$k] = count($v) == 1 ? self::normalize($v[0]) : self::normalize($v);
        }
        return $meta;
    }

    /**
     * Get user meta for a given user ID, excluding sensitive fields
     *
     * @param int $user_id User ID
     * @return array Normalized user meta
     */
    public static function get_user_meta($user_id) {
        $raw = get_user_meta($user_id);
        $safe = [];
        
        $exclude = [
            'user_pass',
            'session_tokens',
            'activation_key',
            'default_password_nag',
            'primary_blog',
            'dismissed_wp_pointers',
            'community-events-location'
        ];

        foreach ($raw as $k => $v) {
            if (in_array($k, $exclude, true)) continue;
            $safe[$k] = count($v) == 1 ? self::normalize($v[0]) : self::normalize($v);
        }

        return $safe;
    }
}
