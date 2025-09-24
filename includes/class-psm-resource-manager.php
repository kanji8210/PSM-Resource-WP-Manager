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
            'taxonomies' => [ 'county', 'type', 'content_type', 'sector' ],
        ];
        register_post_type( 'resource', $args );
    }
    public static function register_taxonomy() {
        // Register county (multi, hierarchical)
        register_taxonomy('county', [ 'resource' ], [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Counties',
                'singular_name' => 'County',
                'search_items' => 'Search Counties',
                'all_items' => 'All Counties',
                'edit_item' => 'Edit County',
                'update_item' => 'Update County',
                'add_new_item' => 'Add New County',
                'new_item_name' => 'New County Name',
                'menu_name' => 'County',
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'county' ],
        ]);
        // Register type (multi, not hierarchical)
        register_taxonomy('type', [ 'resource' ], [
            'hierarchical' => false,
            'labels' => [
                'name' => 'Types',
                'singular_name' => 'Type',
                'search_items' => 'Search Types',
                'all_items' => 'All Types',
                'edit_item' => 'Edit Type',
                'update_item' => 'Update Type',
                'add_new_item' => 'Add New Type',
                'new_item_name' => 'New Type Name',
                'menu_name' => 'Type',
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'type' ],
        ]);
        // Register content_type (single, not hierarchical)
        register_taxonomy('content_type', [ 'resource' ], [
            'hierarchical' => false,
            'labels' => [
                'name' => 'Content Types',
                'singular_name' => 'Content Type',
                'search_items' => 'Search Content Types',
                'all_items' => 'All Content Types',
                'edit_item' => 'Edit Content Type',
                'update_item' => 'Update Content Type',
                'add_new_item' => 'Add New Content Type',
                'new_item_name' => 'New Content Type Name',
                'menu_name' => 'Content Type',
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'content-type' ],
        ]);
        // Register sector (multi, not hierarchical)
        register_taxonomy('sector', [ 'resource' ], [
            'hierarchical' => false,
            'labels' => [
                'name' => 'Sectors',
                'singular_name' => 'Sector',
                'search_items' => 'Search Sectors',
                'all_items' => 'All Sectors',
                'edit_item' => 'Edit Sector',
                'update_item' => 'Update Sector',
                'add_new_item' => 'Add New Sector',
                'new_item_name' => 'New Sector Name',
                'menu_name' => 'Sector',
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'sector' ],
        ]);
    }
    // Force the use of custom templates for each resource type (PDF, Video, Podcast)
    public static function setup_templates() {
        add_filter('the_content', [__CLASS__, 'inject_resource_content']);
    }
    // Inject the custom template content into the page content
    public static function inject_resource_content($content) {
        global $post;
        if (!is_singular('resource') || !in_the_loop() || !is_main_query()) return $content;
        $ct = get_the_terms($post->ID, 'content_type');
        $ct_slug = $ct && !is_wp_error($ct) ? strtolower($ct[0]->name) : '';
        $tpl = PSM_RM_PLUGIN_DIR . 'templates/resource-' . $ct_slug . '.php';
        if (file_exists($tpl)) {
            ob_start();
            include $tpl;
            $custom = ob_get_clean();
            return $custom;
        }
        return $content;
    }
}
