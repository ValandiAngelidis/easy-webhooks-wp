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
                var $postStatusRow = $("#post-status-row");
                var $sendButton = $("#send-webhook-btn");
                var $previewSection = $("#posts-preview");
                
                if (!userId) {
                    $cptSection.hide();
                    $postStatusRow.hide();
                    $previewSection.hide();
                } else {
                    $cptSection.show();
                    updatePreview();
                }
                
                checkSendButtonState();
            }
            
            function updatePreview() {
                var userId = $("#webhook_user_select").val();
                var selectedCpts = [];
                var postStatus = $("#post_status_select").val() || "publish";
                
                $("#cpt-checkboxes input[type=checkbox]:checked").each(function() {
                    selectedCpts.push($(this).val());
                });
                
                if (!userId || selectedCpts.length === 0) {
                    $("#posts-preview").hide();
                    $("#post-status-row").hide();
                    return;
                }
                
                $("#post-status-row").show();
                $("#posts-preview").show().html("<p><em>' . esc_js(__('Loading preview...', 'easy-webhooks-wp')) . '</em></p>");
                
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    data: {
                        action: "preview_user_posts",
                        user_id: userId,
                        post_types: selectedCpts,
                        post_status: postStatus,
                        nonce: "' . wp_create_nonce('preview_user_posts') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            previewData = response.data;
                            displayPreview(response.data);
                        } else {
                            $("#posts-preview").html("<p><em>' . esc_js(__('Error loading preview.', 'easy-webhooks-wp')) . '</em></p>");
                        }
                    },
                    error: function() {
                        $("#posts-preview").html("<p><em>' . esc_js(__('Error loading preview.', 'easy-webhooks-wp')) . '</em></p>");
                    }
                });
            }
            
            function displayPreview(data) {
                var html = "<h4>' . esc_js(__('Preview of posts to be included:', 'easy-webhooks-wp')) . '</h4>";
                var totalPosts = 0;
                
                if (data.posts && Object.keys(data.posts).length > 0) {
                    // Add select all/deselect all checkbox
                    html += "<div style=\"margin-bottom: 10px;\">";
                    html += "<label><input type=\"checkbox\" id=\"select-all-posts\" checked> <strong>' . esc_js(__('Select All / Deselect All', 'easy-webhooks-wp')) . '</strong></label>";
                    html += "</div>";
                    
                    html += "<div style=\"background: #f9f9f9; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;\">";
                    
                    for (var postType in data.posts) {
                        var posts = data.posts[postType];
                        totalPosts += posts.length;
                        
                        html += "<h5>" + postType + " (" + posts.length + " posts):</h5>";
                        html += "<ul style=\"list-style: none; margin-left: 0; padding-left: 0;\">";
                        
                        for (var i = 0; i < posts.length; i++) {
                            html += "<li style=\"margin-bottom: 5px;\">";
                            html += "<label><input type=\"checkbox\" class=\"post-checkbox\" data-post-id=\"" + posts[i].id + "\" checked> ";
                            html += posts[i].title + "</label>";
                            html += "</li>";
                        }
                        
                        html += "</ul>";
                    }
                    
                    html += "</div>";
                    html += "<p><strong>' . esc_js(__('Total posts:', 'easy-webhooks-wp')) . ' " + totalPosts + "</strong></p>";
                } else {
                    html += "<p><em>' . esc_js(__('No posts found for the selected user and post types.', 'easy-webhooks-wp')) . '</em></p>";
                }
                
                $("#posts-preview").html(html);
                
                // Attach select all handler
                $("#select-all-posts").on("change", function() {
                    $(".post-checkbox").prop("checked", $(this).is(":checked"));
                    checkSendButtonState();
                });
                
                // Update select all state when individual checkboxes change
                $(document).on("change", ".post-checkbox", function() {
                    var totalCheckboxes = $(".post-checkbox").length;
                    var checkedCheckboxes = $(".post-checkbox:checked").length;
                    $("#select-all-posts").prop("checked", totalCheckboxes === checkedCheckboxes);
                    checkSendButtonState();
                });
            }
            
            function checkSendButtonState() {
                var userId = $("#webhook_user_select").val();
                var webhookUrl = $("#webhook_url_input").val().trim();
                var $sendButton = $("#send-webhook-btn");
                
                // Enable button if user and webhook URL are present
                // Posts are optional - can send just user data
                $sendButton.prop("disabled", !userId || !webhookUrl || isLoading);
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
            
            // Handle post status change
            $("#post_status_select").on("change", function() {
                updatePreview();
            });
            
            // Update send button state when webhook URL changes
            $("#webhook_url_input").on("input", checkSendButtonState);
            
            // Handle save webhook URL
            $("#save-webhook-url-btn").on("click", function(e) {
                e.preventDefault();
                
                var webhookUrl = $("#webhook_url_input").val().trim();
                
                if (!webhookUrl) {
                    showMessage("' . esc_js(__('Please enter a webhook URL.', 'easy-webhooks-wp')) . '", "error");
                    return;
                }
                
                $(this).prop("disabled", true).text("' . esc_js(__('Saving...', 'easy-webhooks-wp')) . '");
                
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
                            showMessage(response.data.message || "' . esc_js(__('Failed to save webhook URL.', 'easy-webhooks-wp')) . '", "error");
                        }
                    },
                    error: function() {
                        showMessage("' . esc_js(__('Network error occurred while saving.', 'easy-webhooks-wp')) . '", "error");
                    },
                    complete: function() {
                        $("#save-webhook-url-btn").prop("disabled", false).text("' . esc_js(__('Save URL', 'easy-webhooks-wp')) . '");
                    }
                });
            });
            
            $("#send-webhook-btn").on("click", function(e) {
                e.preventDefault();
                
                if (isLoading) return;
                
                var userId = $("#webhook_user_select").val();
                var webhookUrl = $("#webhook_url_input").val().trim();
                var selectedPostIds = [];
                
                $(".post-checkbox:checked").each(function() {
                    selectedPostIds.push(parseInt($(this).data("post-id")));
                });
                
                if (!userId) {
                    showMessage("' . esc_js(__('Please select a user.', 'easy-webhooks-wp')) . '", "error");
                    return;
                }
                
                if (!webhookUrl) {
                    showMessage("' . esc_js(__('Please enter a webhook URL.', 'easy-webhooks-wp')) . '", "error");
                    return;
                }
                
                isLoading = true;
                $(this).prop("disabled", true).text("' . esc_js(__('Sending...', 'easy-webhooks-wp')) . '");
                
                $.ajax({
                    url: ajaxurl,
                    method: "POST",
                    data: {
                        action: "send_user_webhook",
                        user_id: userId,
                        post_ids: selectedPostIds,
                        webhook_url: webhookUrl,
                        nonce: "' . wp_create_nonce('send_user_webhook') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage(response.data.message, "success");
                        } else {
                            showMessage(response.data.message || "' . esc_js(__('An error occurred.', 'easy-webhooks-wp')) . '", "error");
                        }
                    },
                    error: function() {
                        showMessage("' . esc_js(__('Network error occurred.', 'easy-webhooks-wp')) . '", "error");
                    },
                    complete: function() {
                        isLoading = false;
                        $("#send-webhook-btn").prop("disabled", false).text("' . esc_js(__('Send Webhook', 'easy-webhooks-wp')) . '");
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
        $post_ids = isset($_POST['post_ids']) && is_array($_POST['post_ids']) ? array_map('intval', $_POST['post_ids']) : [];
        $webhook_url = isset($_POST['webhook_url']) ? esc_url_raw(trim($_POST['webhook_url'])) : '';
        
        if (!$user_id) {
            wp_send_json_error(['message' => __('Invalid user ID.', 'easy-webhooks-wp')]);
        }
        
        if (!$webhook_url || !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(['message' => __('Invalid webhook URL.', 'easy-webhooks-wp')]);
        }
        
        // Generate webhook data (posts are optional)
        $webhook_data = $this->generate_webhook_data($user_id, $post_ids);
        
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
        $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'publish';
        
        if (!$user_id || empty($post_types)) {
            wp_send_json_error(['message' => __('Invalid user or post types.', 'easy-webhooks-wp')]);
        }
        
        // Generate preview data
        $preview_data = $this->generate_preview_data($user_id, $post_types, $post_status);
        
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
    private function generate_preview_data($user_id, $post_types, $post_status = 'publish') {
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
                'post_status' => $post_status,
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            $post_items = [];
            foreach ($posts as $post) {
                $post_items[] = [
                    'id' => $post->ID,
                    'title' => $post->post_title
                ];
            }
            
            if (!empty($post_items)) {
                $posts_data[$post_type] = $post_items;
            }
        }
        
        return ['posts' => $posts_data];
    }
    
    /**
     * Generate webhook data for selected user and post IDs
     */
    private function generate_webhook_data($user_id, $post_ids) {
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
            'roles' => $user->roles,
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
            'user_url' => $user->user_url,
            'description' => $user->description,
            'meta' => get_user_meta($user->ID)
        ];
        
        $webhook_data = ['user' => $user_data];
        
        // Add selected posts grouped by post type
        $posts_data = [];
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post && $post->post_author == $user_id) {
                $post_type = $post->post_type;
                
                if (!isset($posts_data[$post_type])) {
                    $posts_data[$post_type] = [];
                }
                
                $posts_data[$post_type][] = $post->post_title;
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
        
        // Post status selection
        echo '<tr id="post-status-row" style="display: none;">';
        echo '<th scope="row"><label for="post_status_select">' . esc_html__('Post Status', 'easy-webhooks-wp') . '</label></th>';
        echo '<td>';
        echo '<select id="post_status_select" class="regular-text">';
        
        // Get all post statuses including custom ones
        $post_statuses = get_post_stati(['show_in_admin_all_list' => true], 'objects');
        
        foreach ($post_statuses as $status_key => $status_obj) {
            $selected = $status_key === 'publish' ? ' selected' : '';
            echo '<option value="' . esc_attr($status_key) . '"' . $selected . '>';
            echo esc_html($status_obj->label);
            echo '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the post status to filter posts. Default is "Published".', 'easy-webhooks-wp') . '</p>';
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