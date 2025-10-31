<?php
/**
 * Settings Handler
 * Manages plugin settings and configuration
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Settings {
    const OPT_KEY = 'ew_wp_settings';

    /**
     * Get plugin settings with defaults
     *
     * @return array Settings array
     */
    public static function get() {
        $defaults = [
            'webhook_url'           => '',
            'timeout'               => 180,
            'post_types'            => ['post'],
            'include_author_global' => 0,
            'include_author_meta'   => 1,
            'include_featured'      => 1,
            'taxonomies'            => ['category','post_tag'],
            'tax_output'            => 'names',
            'log_enabled'           => 1,
            'log_limit'             => 100,
        ];
        $opts = get_option(self::OPT_KEY, []);
        return wp_parse_args($opts, $defaults);
    }

    /**
     * Sanitize settings input
     *
     * @param array $input Raw input from form
     * @return array Sanitized settings
     */
    public static function sanitize($input) {
        $out = self::get();
        $out['webhook_url'] = esc_url_raw($input['webhook_url'] ?? '');
        $out['timeout'] = max(5, intval($input['timeout'] ?? 180));

        $req_pts = !empty($input['post_types']) && is_array($input['post_types']) ? $input['post_types'] : ['post'];
        $all_pts = get_post_types(['show_ui'=>true]);
        $out['post_types'] = array_values(array_intersect($req_pts, $all_pts));
        if (!$out['post_types']) $out['post_types'] = ['post'];

        $out['include_author_global'] = !empty($input['include_author_global']) ? 1 : 0;
        $out['include_author_meta']   = !empty($input['include_author_meta']) ? 1 : 0;
        $out['include_featured']      = !empty($input['include_featured']) ? 1 : 0;
        $out['log_enabled']           = !empty($input['log_enabled']) ? 1 : 0;
        $out['log_limit']             = max(10, intval($input['log_limit'] ?? 100));

        return $out;
    }
}
