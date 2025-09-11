<?php
class PSM_Resource_Manager_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $resources_table = $wpdb->prefix . 'psm_resources';
        $meta_table = $wpdb->prefix . 'psm_resource_meta';
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $sql1 = "CREATE TABLE $resources_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL,
            resource_type VARCHAR(20) NOT NULL,
            hosting_platform VARCHAR(50),
            resource_url TEXT,
            thumbnail_url TEXT,
            PRIMARY KEY (id),
            KEY post_id (post_id)
        ) $charset_collate;";
        $sql2 = "CREATE TABLE $meta_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            resource_id BIGINT UNSIGNED NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            PRIMARY KEY (id),
            KEY resource_id (resource_id)
        ) $charset_collate;";
        dbDelta($sql1);
        dbDelta($sql2);
        // CPT & Taxonomy
        PSM_Resource_Manager::register_cpt();
        PSM_Resource_Manager::register_taxonomy();
        flush_rewrite_rules();
        // Ajouter les termes par défaut
        if (!term_exists('PDF', 'resource_type')) {
            wp_insert_term('PDF', 'resource_type');
        }
        if (!term_exists('Video', 'resource_type')) {
            wp_insert_term('Video', 'resource_type');
        }
        if (!term_exists('Podcast', 'resource_type')) {
            wp_insert_term('Podcast', 'resource_type');
        }
    }
}
