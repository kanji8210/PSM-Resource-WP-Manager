<?php
class PSM_Resource_Manager {
    private static $instance = null;
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'init', [ $this, 'register_taxonomy' ] );
        if ( is_admin() ) {
            require_once PSM_RM_PLUGIN_DIR . 'admin/class-psm-resource-manager-admin.php';
            PSM_Resource_Manager_Admin::get_instance();
        } else {
            self::setup_templates();
        }
    }
    public static function register_cpt() {
        $labels = [
            'name' => 'Resources',
            'singular_name' => 'Resource',
            'menu_name' => 'Resources',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Resource',
            'edit_item' => 'Edit Resource',
            'new_item' => 'New Resource',
            'view_item' => 'View Resource',
            'search_items' => 'Search Resources',
            'not_found' => 'No resources found',
            'not_found_in_trash' => 'No resources found in Trash',
        ];
        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'show_in_menu' => false, // Custom menu
            'supports' => [ 'title', 'editor', 'thumbnail' ],
            'taxonomies' => [ 'category' ], // Add native category taxonomy
        ];
        register_post_type( 'resource', $args );
    }
    public static function register_taxonomy() {
        $labels = [
            'name' => 'Resource Types',
            'singular_name' => 'Resource Type',
            'search_items' => 'Search Resource Types',
            'all_items' => 'All Resource Types',
            'edit_item' => 'Edit Resource Type',
            'update_item' => 'Update Resource Type',
            'add_new_item' => 'Add New Resource Type',
            'new_item_name' => 'New Resource Type Name',
            'menu_name' => 'Resource Type',
        ];
        $args = [
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'resource-type' ],
        ];
        register_taxonomy( 'resource_type', [ 'resource' ], $args );
    }
    // Force the use of custom templates for each resource type (PDF, Video, Podcast)
    public static function setup_templates() {
        add_filter('the_content', [__CLASS__, 'inject_resource_content']);
    }
    // Inject the custom template content into the page content
    public static function inject_resource_content($content) {
        global $post;
        if (!is_singular('resource') || !in_the_loop() || !is_main_query()) return $content;
        $type = get_the_terms($post->ID, 'resource_type');
        $type_slug = $type && !is_wp_error($type) ? strtolower($type[0]->name) : '';
        $tpl = PSM_RM_PLUGIN_DIR . 'templates/resource-' . $type_slug . '.php';
        if (file_exists($tpl)) {
            ob_start();
            include $tpl;
            $custom = ob_get_clean();
            return $custom;
        }
        return $content;
    }
}
