<?php
class PSM_Resource_Manager_Admin {
    private static $instance = null;
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
    }
    public function add_admin_menu() {
        add_menu_page(
            'Resources',
            'Resources',
            'manage_options',
            'psm_resources',
            [ $this, 'resources_page' ],
            'dashicons-portfolio',
            6
        );
        add_submenu_page('psm_resources', 'All Resources', 'All Resources', 'manage_options', 'psm_resources', [ $this, 'resources_page' ]);
        add_submenu_page('psm_resources', 'Add New', 'Add New', 'manage_options', 'psm_add_resource', [ $this, 'add_resource_page' ]);
        add_submenu_page('psm_resources', 'Settings', 'Settings', 'manage_options', 'psm_resource_settings', [ $this, 'settings_page' ]);
    }
    public function enqueue_admin_scripts() {
        wp_enqueue_style( 'psm-resource-admin', PSM_RM_PLUGIN_URL . 'admin/admin.css', [], PSM_RM_VERSION );
        wp_enqueue_script( 'psm-resource-admin', PSM_RM_PLUGIN_URL . 'admin/admin.js', [ 'jquery' ], PSM_RM_VERSION, true );
    }
    public function resources_page() {
        require_once PSM_RM_PLUGIN_DIR . 'admin/resources-list.php';
    }
    public function add_resource_page() {
        require_once PSM_RM_PLUGIN_DIR . 'admin/resource-form.php';
    }
    public function settings_page() {
        require_once PSM_RM_PLUGIN_DIR . 'admin/settings.php';
    }
}
