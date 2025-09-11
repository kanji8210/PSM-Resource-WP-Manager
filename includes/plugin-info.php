<?php
/**
 * Plugin information and constants
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin version and information
define('PSM_RESOURCE_MANAGER_DB_VERSION', '1.0');

/**
 * Plugin activation hook
 */
function psm_resource_manager_install() {
    // Create database tables
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'psm_resources';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        resource_type varchar(50) NOT NULL,
        url varchar(255) DEFAULT '',
        hosting_option varchar(100) DEFAULT '',
        image_id bigint(20) DEFAULT NULL,
        additional_data longtext DEFAULT '',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY post_id (post_id),
        KEY resource_type (resource_type)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    add_option('psm_resource_manager_db_version', PSM_RESOURCE_MANAGER_DB_VERSION);
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation hook
 */
function psm_resource_manager_uninstall() {
    // Clean up on uninstall
    global $wpdb;
    
    // Delete custom table (uncomment if you want to remove data on uninstall)
    // $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}psm_resources");
    
    // Delete options
    delete_option('psm_resource_manager_db_version');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register hooks
register_activation_hook(__FILE__, 'psm_resource_manager_install');
register_uninstall_hook(__FILE__, 'psm_resource_manager_uninstall');