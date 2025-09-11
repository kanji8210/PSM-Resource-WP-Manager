<?php
/**
 * PSM Resource Manager Functions
 * Handles saving, updating, and managing resources
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PSM_Resource_Handler {
    
    /**
     * Save resource data to custom table
     */
    public static function save_resource($data) {
        global $wpdb;
        
        // Sanitize input data
        $title = sanitize_text_field($data['resource_title']);
        $description = wp_kses_post($data['resource_description']);
        $resource_type = sanitize_text_field($data['resource_type']);
        $excerpt = sanitize_textarea_field($data['resource_excerpt'] ?? '');
        $tags = sanitize_text_field($data['resource_tags'] ?? '');
        
        // Create the post first
        $post_data = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_excerpt'  => $excerpt,
            'post_status'   => 'publish',
            'post_type'     => 'psm_resource',
            'post_author'   => get_current_user_id(),
            'meta_input'    => array(
                'resource_tags' => $tags
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return false;
        }
        
        // Set the resource type taxonomy
        wp_set_object_terms($post_id, $resource_type, 'psm_resource_type');
        
        // Prepare custom table data
        $table_name = $wpdb->prefix . 'psm_resources';
        $insert_data = array(
            'post_id' => $post_id,
            'resource_type' => $resource_type,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        // Handle type-specific data
        switch ($resource_type) {
            case 'pdf':
                if (!empty($data['resource_image_id'])) {
                    $insert_data['image_id'] = intval($data['resource_image_id']);
                    // Set as featured image
                    set_post_thumbnail($post_id, $insert_data['image_id']);
                }
                break;
                
            case 'video':
            case 'podcast':
                if (!empty($data['resource_url'])) {
                    $insert_data['url'] = esc_url_raw($data['resource_url']);
                }
                if (!empty($data['hosting_option'])) {
                    $insert_data['hosting_option'] = sanitize_text_field($data['hosting_option']);
                }
                break;
                
            case 'link':
                if (!empty($data['external_url'])) {
                    $insert_data['url'] = esc_url_raw($data['external_url']);
                }
                break;
        }
        
        // Store additional data as JSON
        $additional_data = array();
        if (!empty($tags)) {
            $additional_data['tags'] = explode(',', $tags);
        }
        
        if (!empty($additional_data)) {
            $insert_data['additional_data'] = wp_json_encode($additional_data);
        }
        
        // Insert into custom table
        $result = $wpdb->insert($table_name, $insert_data);
        
        if ($result === false) {
            // Rollback post creation if custom table insert fails
            wp_delete_post($post_id, true);
            return false;
        }
        
        return $post_id;
    }
    
    /**
     * Get resource by ID
     */
    public static function get_resource($resource_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'psm_resources';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT r.*, p.post_title, p.post_content, p.post_excerpt, p.post_date 
             FROM $table_name r 
             LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID 
             WHERE r.id = %d",
            $resource_id
        ));
    }
    
    /**
     * Get resources with filtering
     */
    public static function get_resources($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'psm_resources';
        
        $defaults = array(
            'resource_type' => '',
            'search' => '',
            'limit' => 10,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array('1=1');
        $prepare_values = array();
        
        if (!empty($args['resource_type'])) {
            $where_conditions[] = 'r.resource_type = %s';
            $prepare_values[] = $args['resource_type'];
        }
        
        if (!empty($args['search'])) {
            $where_conditions[] = '(p.post_title LIKE %s OR p.post_content LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $prepare_values[] = $search_term;
            $prepare_values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT r.*, p.post_title, p.post_content, p.post_excerpt, p.post_date 
                FROM $table_name r 
                LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID 
                WHERE $where_clause 
                ORDER BY r.{$args['orderby']} {$args['order']} 
                LIMIT %d OFFSET %d";
        
        $prepare_values[] = $args['limit'];
        $prepare_values[] = $args['offset'];
        
        return $wpdb->get_results($wpdb->prepare($sql, $prepare_values));
    }
    
    /**
     * Delete resource
     */
    public static function delete_resource($resource_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'psm_resources';
        
        // Get the resource first to get post_id
        $resource = self::get_resource($resource_id);
        
        if (!$resource) {
            return false;
        }
        
        // Delete from custom table
        $result = $wpdb->delete($table_name, array('id' => $resource_id), array('%d'));
        
        if ($result !== false) {
            // Delete the associated post
            wp_delete_post($resource->post_id, true);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get resource count by type
     */
    public static function get_resource_count($resource_type = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'psm_resources';
        
        if (!empty($resource_type)) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE resource_type = %s",
                $resource_type
            ));
        } else {
            return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }
    }
    
    /**
     * Update resource
     */
    public static function update_resource($resource_id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'psm_resources';
        
        $resource = self::get_resource($resource_id);
        if (!$resource) {
            return false;
        }
        
        // Update post data
        $post_data = array(
            'ID' => $resource->post_id,
            'post_title' => sanitize_text_field($data['resource_title']),
            'post_content' => wp_kses_post($data['resource_description']),
            'post_excerpt' => sanitize_textarea_field($data['resource_excerpt'] ?? ''),
        );
        
        wp_update_post($post_data);
        
        // Update taxonomy
        wp_set_object_terms($resource->post_id, $data['resource_type'], 'psm_resource_type');
        
        // Update custom table
        $update_data = array(
            'resource_type' => sanitize_text_field($data['resource_type']),
            'updated_at' => current_time('mysql')
        );
        
        // Handle type-specific data
        switch ($data['resource_type']) {
            case 'pdf':
                if (!empty($data['resource_image_id'])) {
                    $update_data['image_id'] = intval($data['resource_image_id']);
                    set_post_thumbnail($resource->post_id, $update_data['image_id']);
                }
                break;
                
            case 'video':
            case 'podcast':
                if (!empty($data['resource_url'])) {
                    $update_data['url'] = esc_url_raw($data['resource_url']);
                }
                if (!empty($data['hosting_option'])) {
                    $update_data['hosting_option'] = sanitize_text_field($data['hosting_option']);
                }
                break;
                
            case 'link':
                if (!empty($data['external_url'])) {
                    $update_data['url'] = esc_url_raw($data['external_url']);
                }
                break;
        }
        
        return $wpdb->update($table_name, $update_data, array('id' => $resource_id));
    }
    
    /**
     * Get resources for frontend display
     */
    public static function get_frontend_resources($args = array()) {
        $resources = self::get_resources($args);
        $formatted_resources = array();
        
        foreach ($resources as $resource) {
            $formatted_resource = array(
                'id' => $resource->id,
                'title' => $resource->post_title,
                'content' => $resource->post_content,
                'excerpt' => $resource->post_excerpt,
                'type' => $resource->resource_type,
                'url' => $resource->url,
                'hosting_option' => $resource->hosting_option,
                'image_id' => $resource->image_id,
                'date' => $resource->post_date,
                'permalink' => get_permalink($resource->post_id)
            );
            
            // Get featured image
            if ($resource->image_id) {
                $formatted_resource['image_url'] = wp_get_attachment_image_url($resource->image_id, 'medium');
                $formatted_resource['image_alt'] = get_post_meta($resource->image_id, '_wp_attachment_image_alt', true);
            }
            
            // Get taxonomy terms
            $terms = wp_get_object_terms($resource->post_id, 'psm_resource_type');
            $formatted_resource['type_name'] = !empty($terms) ? $terms[0]->name : ucfirst(str_replace('-', ' ', $resource->resource_type));
            
            $formatted_resources[] = $formatted_resource;
        }
        
        return $formatted_resources;
    }
}

// AJAX handlers
add_action('wp_ajax_psm_save_resource', 'psm_ajax_save_resource');
add_action('wp_ajax_nopriv_psm_save_resource', 'psm_ajax_save_resource');

function psm_ajax_save_resource() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'psm_ajax_nonce')) {
        wp_die(__('Security check failed', 'psm-resource-manager'));
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action', 'psm-resource-manager'));
    }
    
    $form_data = $_POST['form_data'];
    $result = PSM_Resource_Handler::save_resource($form_data);
    
    if ($result) {
        wp_send_json_success(array(
            'message' => __('Resource saved successfully!', 'psm-resource-manager'),
            'resource_id' => $result,
            'reset_form' => true
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Error saving resource. Please try again.', 'psm-resource-manager')
        ));
    }
}

// Frontend AJAX handlers
add_action('wp_ajax_psm_filter_resources', 'psm_ajax_filter_resources');
add_action('wp_ajax_nopriv_psm_filter_resources', 'psm_ajax_filter_resources');

function psm_ajax_filter_resources() {
    if (!wp_verify_nonce($_POST['nonce'], 'psm_frontend_nonce')) {
        wp_die(__('Security check failed', 'psm-resource-manager'));
    }
    
    $args = array(
        'resource_type' => sanitize_text_field($_POST['resource_type'] ?? ''),
        'search' => sanitize_text_field($_POST['search'] ?? ''),
        'limit' => 12
    );
    
    $resources = PSM_Resource_Handler::get_frontend_resources($args);
    
    ob_start();
    foreach ($resources as $resource) {
        include PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'templates/frontend/resource-card.php';
    }
    $html = ob_get_clean();
    
    wp_send_json_success(array('html' => $html));
}

// Add save_resource method to main class
add_action('init', function() {
    if (!method_exists('PSM_Resource_Manager', 'save_resource')) {
        PSM_Resource_Manager::class . '::save_resource';
    }
});

// Add method to main class via action
add_filter('psm_save_resource_data', function($data) {
    return PSM_Resource_Handler::save_resource($data);
});