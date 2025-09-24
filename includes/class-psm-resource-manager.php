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
        // List of all African countries
        $african_countries = [
            'Algeria','Angola','Benin','Botswana','Burkina Faso','Burundi','Cabo Verde','Cameroon','Central African Republic','Chad','Comoros','Congo','Congo (Democratic Republic)','Djibouti','Egypt','Equatorial Guinea','Eritrea','Eswatini','Ethiopia','Gabon','Gambia','Ghana','Guinea','Guinea-Bissau','Ivory Coast','Kenya','Lesotho','Liberia','Libya','Madagascar','Malawi','Mali','Mauritania','Mauritius','Morocco','Mozambique','Namibia','Niger','Nigeria','Rwanda','Sao Tome and Principe','Senegal','Seychelles','Sierra Leone','Somalia','South Africa','South Sudan','Sudan','Tanzania','Togo','Tunisia','Uganda','Zambia','Zimbabwe'
        ];
        // Register county (multi, hierarchical, prefill terms)
        register_taxonomy('county', [ 'resource' ], [
            'hierarchical' => true,
            'labels' => [
                'name' => 'Countries',
                'singular_name' => 'Country',
                'search_items' => 'Search Countries',
                'all_items' => 'All Countries',
                'edit_item' => 'Edit Country',
                'update_item' => 'Update Country',
                'add_new_item' => 'Add New Country',
                'new_item_name' => 'New Country Name',
                'menu_name' => 'Country',
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'country' ],
        ]);
        // Insert countries if not present
        foreach ($african_countries as $country) {
            if (!term_exists($country, 'county')) {
                wp_insert_term($country, 'county');
            }
        }
        // Register type (multi, hierarchical for subtypes)
        register_taxonomy('type', [ 'resource' ], [
            'hierarchical' => true,
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
        // Register content_type (single, not hierarchical, prefill terms)
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
        $ctypes = ['PDF','Podcast','Video','Web Page'];
        foreach ($ctypes as $ct) {
            if (!term_exists($ct, 'content_type')) {
                wp_insert_term($ct, 'content_type');
            }
        }
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
    // Add taxonomy menus under Resources
    add_action('admin_menu', function() {
        add_submenu_page('edit.php?post_type=resource', 'Manage Countries', 'Countries', 'manage_categories', 'edit-tags.php?taxonomy=county&post_type=resource');
        add_submenu_page('edit.php?post_type=resource', 'Manage Types', 'Types', 'manage_categories', 'edit-tags.php?taxonomy=type&post_type=resource');
        add_submenu_page('edit.php?post_type=resource', 'Manage Content Types', 'Content Types', 'manage_categories', 'edit-tags.php?taxonomy=content_type&post_type=resource');
        add_submenu_page('edit.php?post_type=resource', 'Manage Sectors', 'Sectors', 'manage_categories', 'edit-tags.php?taxonomy=sector&post_type=resource');
    });
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
