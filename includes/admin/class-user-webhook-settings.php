<?php
/**
 * User Webhook Trigger Page
 * Interface for sending webhooks with user data
 */

if (!defined('ABSPATH')) exit;

class EW_WP_User_Webhook_Settings {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_send_user_webhook', [$this, 'handle_webhook_send']);
        add_action('wp_ajax_preview_user_posts', [$this, 'handle_preview_posts']);
        add_action('wp_ajax_save_user_webhook_url', [$this, 'handle_save_webhook_url']);
    }
    
    /**
     * Enqueue scripts for user webhook page
     */
    public function enqueue_scripts($hook) {
        // Only load on our settings page
        if ($hook !== 'easy-webhooks-wp_page_easy-webhooks-users') {
            return;
        }

        wp_enqueue_script('jquery');
        
        wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            var isLoading = false;
            var previewData = {};
            
            function updatePostTypes() {
                var userId = $("#webhook_user_select").val();
                var $cptSection = $("#cpt-checkboxes");
                var $sendButton = $("#send-webhook-btn");
                var $previewSection = $("#posts-preview");
                
                if (!userId) {
                    $cptSection.hide();
                    $sendButton.prop("disabled", true);
                    $previewSection.hide();
                    return;
                }
                
                $cptSection.show();
                checkSendButtonState();
                updatePreview();
            }
            
            function updatePreview() {
                var userId = $("#webhook_user_select").val();
                var selectedCpts = [];
                
                $("#cpt-checkboxes input[type=checkbox]:checked").each(function() {
                    selectedCpts.push($(this).val());
                });
                
                if (!userId || selectedCpts.length === 0) {
                    $("#posts-preview").hide();
                    return;
                }
                
                $("#posts-preview").show().html("<p><em>Loading preview...</em></p>");
                
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    data: {
                        action: "preview_user_posts",
                        user_id: userId,
                        post_types: selectedCpts,
                        nonce: "' . wp_create_nonce('preview_user_posts') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            previewData = response.data;
                            displayPreview(response.data);
                        } else {
                            $("#posts-preview").html("<p><em>Error loading preview.</em></p>");
                        }
                    },
                    error: function() {
                        $("#posts-preview").html("<p><em>Error loading preview.</em></p>");
                    }
                });
            }
            
            function displayPreview(data) {
                var html = "<h4>Preview of posts to be included:</h4>";
                var totalPosts = 0;
                
                if (data.posts && Object.keys(data.posts).length > 0) {
                    html += "<div style=\"background: #f9f9f9; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;\">";
                    
                    for (var postType in data.posts) {
                        var posts = data.posts[postType];
                        totalPosts += posts.length;
                        
                        html += "<h5>" + postType + " (" + posts.length + " posts):</h5>";
                        html += "<ul style=\"margin-left: 20px;\">";
                        
                        for (var i = 0; i < posts.length; i++) {
                            html += "<li>" + posts[i] + "</li>";
                        }
                        
                        html += "</ul>";
                    }
                    
                    html += "</div>";
                    html += "<p><strong>Total posts: " + totalPosts + "</strong></p>";
                } else {
                    html += "<p><em>No posts found for the selected user and post types.</em></p>";
                }
                
                $("#posts-preview").html(html);
            }
            
            function checkSendButtonState() {
                var userId = $("#webhook_user_select").val();
                var hasChecked = $("#cpt-checkboxes input[type=checkbox]:checked").length > 0;
                var webhookUrl = $("#webhook_url_input").val().trim();
                var $sendButton = $("#send-webhook-btn");
                
                $sendButton.prop("disabled", !userId || !hasChecked || !webhookUrl || isLoading);
            }
            
            function showMessage(message, type) {
                var $msg = $("<div class=\"notice notice-" + type + " is-dismissible\"><p>" + message + "</p></div>");
                $(".wrap h1").after($msg);
                setTimeout(function() { $msg.fadeOut(); }, 5000);
            }
            
            $("#webhook_user_select").on("change", updatePostTypes);
            $(document).on("change", "#cpt-checkboxes input[type=checkbox]", function() {
                checkSendButtonState();
                updatePreview();
            });
            
            // Update send button state when webhook URL changes
            $("#webhook_url_input").on("input", checkSendButtonState);
            
            // Handle save webhook URL
            $("#save-webhook-url-btn").on("click", function(e) {
                e.preventDefault();
                
                var webhookUrl = $("#webhook_url_input").val().trim();
                
                if (!webhookUrl) {
                    showMessage("Please enter a webhook URL.", "error");
                    return;
                }
                
                $(this).prop("disabled", true).text("Saving...");
                
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    data: {
                        action: "save_user_webhook_url",
                        webhook_url: webhookUrl,
                        nonce: "' . wp_create_nonce('save_user_webhook_url') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data.message, "success");
                        } else {
                            showMessage(response.data.message || "Failed to save webhook URL.", "error");
                        }
                    },
                    error: function() {
                        showMessage("Network error occurred while saving.", "error");
                    },
                    complete: function() {
                        $("#save-webhook-url-btn").prop("disabled", false).text("Save URL");
                    }
                });
            });
            
            $("#send-webhook-btn").on("click", function(e) {
                e.preventDefault();
                
                if (isLoading) return;
                
                var userId = $("#webhook_user_select").val();
                var selectedCpts = [];
                var webhookUrl = $("#webhook_url_input").val().trim();
                
                $("#cpt-checkboxes input[type=checkbox]:checked").each(function() {
                    selectedCpts.push($(this).val());
                });
                
                if (!userId || selectedCpts.length === 0) {
                    showMessage("Please select a user and at least one post type.", "error");
                    return;
                }
                
                if (!webhookUrl) {
                    showMessage("Please enter a webhook URL.", "error");
                    return;
                }
                
                isLoading = true;
                $(this).prop("disabled", true).text("Sending...");
                
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    data: {
                        action: "send_user_webhook",
                        user_id: userId,
                        post_types: selectedCpts,
                        webhook_url: webhookUrl,
                        nonce: "' . wp_create_nonce('send_user_webhook') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data.message, "success");
                        } else {
                            showMessage(response.data.message || "An error occurred.", "error");
                        }
                    },
                    error: function() {
                        showMessage("Network error occurred.", "error");
                    },
                    complete: function() {
                        isLoading = false;
                        $("#send-webhook-btn").prop("disabled", false).text("Send Webhook");
                        checkSendButtonState();
                    }
                });
            });
            
            // Initial state
            updatePostTypes();
        });
        ');
    }
    
    /**
     * Add settings page to WordPress admin
     */
    public function add_settings_page() {
        add_submenu_page(
            'easy-webhooks-main',
            __('User Webhooks', 'easy-webhooks-wp'),
            __('User Webhooks', 'easy-webhooks-wp'), 
            'manage_options',
            'easy-webhooks-users',
            [$this, 'settings_page_html']
        );
    }
    
    /**
     * Handle AJAX webhook send
     */
    public function handle_webhook_send() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'send_user_webhook')) {
            wp_send_json_error(['message' => __('Security check failed.', 'easy-webhooks-wp')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have sufficient permissions.', 'easy-webhooks-wp')]);
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $post_types = isset($_POST['post_types']) && is_array($_POST['post_types']) ? array_map('sanitize_text_field', $_POST['post_types']) : [];
        $webhook_url = isset($_POST['webhook_url']) ? esc_url_raw(trim($_POST['webhook_url'])) : '';
        
        if (!$user_id || empty($post_types)) {
            wp_send_json_error(['message' => __('Invalid user or post types.', 'easy-webhooks-wp')]);
        }
        
        if (!$webhook_url || !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(['message' => __('Invalid webhook URL.', 'easy-webhooks-wp')]);
        }
        
        // Generate webhook data
        $webhook_data = $this->generate_webhook_data($user_id, $post_types);
        
        if (!$webhook_data) {
            wp_send_json_error(['message' => __('Unable to generate webhook data.', 'easy-webhooks-wp')]);
        }
        
        // Send webhook with custom URL
        $result = $this->send_webhook($webhook_data, $webhook_url);
        
        if ($result['ok']) {
            wp_send_json_success(['message' => $result['message']]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * Handle AJAX preview posts request
     */
    public function handle_preview_posts() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'preview_user_posts')) {
            wp_send_json_error(['message' => __('Security check failed.', 'easy-webhooks-wp')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have sufficient permissions.', 'easy-webhooks-wp')]);
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $post_types = isset($_POST['post_types']) && is_array($_POST['post_types']) ? array_map('sanitize_text_field', $_POST['post_types']) : [];
        
        if (!$user_id || empty($post_types)) {
            wp_send_json_error(['message' => __('Invalid user or post types.', 'easy-webhooks-wp')]);
        }
        
        // Generate preview data
        $preview_data = $this->generate_preview_data($user_id, $post_types);
        
        wp_send_json_success($preview_data);
    }
    
    /**
     * Handle AJAX save webhook URL request
     */
    public function handle_save_webhook_url() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'save_user_webhook_url')) {
            wp_send_json_error(['message' => __('Security check failed.', 'easy-webhooks-wp')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have sufficient permissions.', 'easy-webhooks-wp')]);
        }
        
        $webhook_url = isset($_POST['webhook_url']) ? esc_url_raw(trim($_POST['webhook_url'])) : '';
        
        if (!$webhook_url || !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(['message' => __('Invalid webhook URL. Please enter a valid URL.', 'easy-webhooks-wp')]);
        }
        
        // Additional validation: ensure it's http or https
        $parsed_url = parse_url($webhook_url);
        if (!isset($parsed_url['scheme']) || !in_array($parsed_url['scheme'], ['http', 'https'])) {
            wp_send_json_error(['message' => __('Webhook URL must use http or https protocol.', 'easy-webhooks-wp')]);
        }
        
        // Save the webhook URL
        update_option('user_webhook_url', $webhook_url);
        
        wp_send_json_success(['message' => __('Webhook URL saved successfully!', 'easy-webhooks-wp')]);
    }
    
    /**
     * Generate preview data for selected user and post types
     */
    private function generate_preview_data($user_id, $post_types) {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return ['posts' => []];
        }
        
        // Add posts for selected post types
        $posts_data = [];
        
        foreach ($post_types as $post_type) {
            $posts = get_posts([
                'author' => $user->ID,
                'post_type' => $post_type,
                'post_status' => 'publish',
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            $post_titles = [];
            foreach ($posts as $post) {
                $post_titles[] = $post->post_title;
            }
            
            if (!empty($post_titles)) {
                $posts_data[$post_type] = $post_titles;
            }
        }
        
        return ['posts' => $posts_data];
    }
    
    /**
     * Generate webhook data for selected user and post types
     */
    private function generate_webhook_data($user_id, $post_types) {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return null;
        }
        
        // Prepare user data
        $user_data = [
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'user_login' => $user->user_login,
            'email' => $user->user_email,
            'meta' => get_user_meta($user->ID)
        ];
        
        $webhook_data = ['user' => $user_data];
        
        // Add posts for selected post types
        $posts_data = [];
        
        foreach ($post_types as $post_type) {
            $posts = get_posts([
                'author' => $user->ID,
                'post_type' => $post_type,
                'post_status' => 'publish',
                'numberposts' => -1
            ]);
            
            $post_titles = [];
            foreach ($posts as $post) {
                $post_titles[] = $post->post_title;
            }
            
            if (!empty($post_titles)) {
                $posts_data[$post_type] = $post_titles;
            }
        }
        
        $webhook_data['posts'] = $posts_data;
        
        return $webhook_data;
    }
    
    /**
     * Send webhook with data
     */
    private function send_webhook($data, $custom_url = null) {
        $settings = EW_WP_Settings::get();
        $url = $custom_url ? trim($custom_url) : trim($settings['webhook_url']);
        
        if (!$url) {
            return ['ok' => false, 'message' => __('Webhook URL missing', 'easy-webhooks-wp')];
        }
        
        $json = wp_json_encode($data);
        $res = wp_remote_post($url, [
            'timeout' => (int)$settings['timeout'],
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $json
        ]);
        
        // Handle errors
        if (is_wp_error($res)) {
            return ['ok' => false, 'message' => sprintf(__('Error: %s', 'easy-webhooks-wp'), $res->get_error_message())];
        }
        
        $code = wp_remote_retrieve_response_code($res);
        if ($code < 200 || $code > 299) {
            return ['ok' => false, 'message' => sprintf(__('Webhook error %d', 'easy-webhooks-wp'), $code)];
        }
        
        return ['ok' => true, 'message' => __('User webhook sent successfully!', 'easy-webhooks-wp')];
    }
    
    /**
     * Settings page HTML
     */
    public function settings_page_html() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'easy-webhooks-wp'));
        }
        
        $users = get_users();
        $post_types = get_post_types(['public' => true], 'objects');
        $excluded_types = ['attachment', 'revision', 'nav_menu_item'];
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Send User Webhook', 'easy-webhooks-wp') . '</h1>';
        echo '<p>' . esc_html__('Send a webhook containing user information and their authored posts for selected post types.', 'easy-webhooks-wp') . '</p>';
        
        echo '<table class="form-table">';
        
        // User selection
        echo '<tr>';
        echo '<th scope="row"><label for="webhook_user_select">' . esc_html__('Select User', 'easy-webhooks-wp') . '</label></th>';
        echo '<td>';
        echo '<select id="webhook_user_select" class="regular-text">';
        echo '<option value="">' . esc_html__('Select a user...', 'easy-webhooks-wp') . '</option>';
        
        foreach ($users as $user) {
            echo '<option value="' . esc_attr($user->ID) . '">';
            echo esc_html($user->display_name . ' (' . $user->user_login . ')');
            echo '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select a user whose information and posts will be included in the webhook.', 'easy-webhooks-wp') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Webhook URL input
        echo '<tr>';
        echo '<th scope="row"><label for="webhook_url_input">' . esc_html__('User Webhook URL', 'easy-webhooks-wp') . '</label></th>';
        echo '<td>';
        
        // Get saved user webhook URL (independent from main settings)
        $user_webhook_url = get_option('user_webhook_url', '');
        
        echo '<input type="url" id="webhook_url_input" class="regular-text" placeholder="https://example.com/user-webhook" value="' . esc_attr($user_webhook_url) . '" />';
        echo '<button type="button" id="save-webhook-url-btn" class="button" style="margin-left: 10px;">' . esc_html__('Save URL', 'easy-webhooks-wp') . '</button>';
        echo '<p class="description">' . esc_html__('Enter the webhook URL specifically for user data. This is independent from the main webhook URL in Easy Webhooks settings.', 'easy-webhooks-wp') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Post types selection
        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Post Types', 'easy-webhooks-wp') . '</th>';
        echo '<td>';
        echo '<div id="cpt-checkboxes" style="display: none; padding: 10px; border: 1px solid #ddd; background: #f9f9f9; max-height: 200px; overflow-y: auto;">';
        
        foreach ($post_types as $post_type) {
            if (in_array($post_type->name, $excluded_types)) {
                continue;
            }
            
            echo '<div style="margin-bottom: 5px;">';
            echo '<label for="cpt_' . esc_attr($post_type->name) . '">';
            echo '<input type="checkbox" value="' . esc_attr($post_type->name) . '" id="cpt_' . esc_attr($post_type->name) . '">';
            echo ' ' . esc_html($post_type->label) . ' <code>(' . esc_html($post_type->name) . ')</code>';
            echo '</label>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '<p class="description">' . esc_html__('Select post types to include authored posts from the selected user.', 'easy-webhooks-wp') . '</p>';
        echo '</td>';
        echo '</tr>';
        
        // Preview section
        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Posts Preview', 'easy-webhooks-wp') . '</th>';
        echo '<td>';
        echo '<div id="posts-preview" style="display: none;"></div>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        // Send button
        echo '<p class="submit">';
        echo '<button type="button" id="send-webhook-btn" class="button button-primary" disabled>';
        echo esc_html__('Send Webhook', 'easy-webhooks-wp');
        echo '</button>';
        echo '</p>';
        
        echo '</div>';
    }
}