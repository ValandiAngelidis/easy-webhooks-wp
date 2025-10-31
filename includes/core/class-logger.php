<?php
/**
 * Logger
 * Handles logging of webhook sends
 */

if (!defined('ABSPATH')) exit;

class EW_WP_Logger {
    const LOG_KEY = 'ew_wp_logs';

    /**
     * Add a log entry
     *
     * @param array $entry Log entry data
     * @return void
     */
    public static function push($entry) {
        $settings = EW_WP_Settings::get();
        if (empty($settings['log_enabled'])) return;

        $logs = get_option(self::LOG_KEY, []);
        array_unshift($logs, $entry);

        $limit = max(10, intval($settings['log_limit']));
        if (count($logs) > $limit) {
            $logs = array_slice($logs, 0, $limit);
        }

        update_option(self::LOG_KEY, $logs, false);
    }

    /**
     * Get all log entries
     *
     * @return array Log entries
     */
    public static function get_all() {
        return get_option(self::LOG_KEY, []);
    }

    /**
     * Clear all logs
     *
     * @return void
     */
    public static function clear() {
        update_option(self::LOG_KEY, [], false);
    }
}
