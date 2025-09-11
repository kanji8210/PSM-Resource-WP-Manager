<?php
class PSM_Resource_Manager_Uninstaller {
    public static function uninstall() {
        global $wpdb;
        $resources_table = $wpdb->prefix . 'psm_resources';
        $meta_table = $wpdb->prefix . 'psm_resource_meta';
        $wpdb->query("DROP TABLE IF EXISTS $resources_table");
        $wpdb->query("DROP TABLE IF EXISTS $meta_table");
        // Supprimer les CPT et taxonomies
        $posts = get_posts([
            'post_type' => 'resource',
            'numberposts' => -1,
            'fields' => 'ids'
        ]);
        foreach ($posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        // Supprimer les termes de la taxonomie
        $terms = get_terms(['taxonomy' => 'resource_type', 'hide_empty' => false]);
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'resource_type');
        }
    }
}
