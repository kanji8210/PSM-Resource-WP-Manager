<?php
/**
 * Plugin Name: PSM Resource Manager
 * Plugin URI: https://github.com/kanji8210/PSM-Resource-WP-Manager
 * Description: A comprehensive resource management plugin for WordPress with custom post types, taxonomies, and dynamic forms.
 * Version: 1.0.0
 * Author: PSM Consult
 * License: GPL v2 or later
 * Text Domain: psm-resource-manager
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PSM_RESOURCE_MANAGER_VERSION', '1.0.0');
define('PSM_RESOURCE_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PSM_RESOURCE_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PSM_RESOURCE_MANAGER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main PSM Resource Manager Class
 */
class PSM_Resource_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load plugin textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // Load includes
        $this->load_includes();
        
        // Add template filters
        add_filter('single_template', array($this, 'load_single_template'));
        add_filter('archive_template', array($this, 'load_archive_template'));
    }
    
    /**
     * Load include files
     */
    private function load_includes() {
        require_once PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'includes/resource-handler.php';
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        $this->register_post_type();
        $this->register_taxonomy();
        $this->setup_admin_menu();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_database_tables();
        $this->register_post_type();
        $this->register_taxonomy();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('psm-resource-manager', false, dirname(PSM_RESOURCE_MANAGER_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Create custom database tables
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Resources table
        $table_name = $wpdb->prefix . 'psm_resources';
        
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
    }
    
    /**
     * Register custom post type
     */
    private function register_post_type() {
        $labels = array(
            'name'                  => __('Resources', 'psm-resource-manager'),
            'singular_name'         => __('Resource', 'psm-resource-manager'),
            'menu_name'             => __('Resources', 'psm-resource-manager'),
            'name_admin_bar'        => __('Resource', 'psm-resource-manager'),
            'add_new'               => __('Add New', 'psm-resource-manager'),
            'add_new_item'          => __('Add New Resource', 'psm-resource-manager'),
            'new_item'              => __('New Resource', 'psm-resource-manager'),
            'edit_item'             => __('Edit Resource', 'psm-resource-manager'),
            'view_item'             => __('View Resource', 'psm-resource-manager'),
            'all_items'             => __('All Resources', 'psm-resource-manager'),
            'search_items'          => __('Search Resources', 'psm-resource-manager'),
            'parent_item_colon'     => __('Parent Resources:', 'psm-resource-manager'),
            'not_found'             => __('No resources found.', 'psm-resource-manager'),
            'not_found_in_trash'    => __('No resources found in Trash.', 'psm-resource-manager')
        );
        
        $args = array(
            'labels'                => $labels,
            'description'           => __('Resources for PSM Resource Manager', 'psm-resource-manager'),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => false, // We'll create custom admin menu
            'query_var'             => true,
            'rewrite'               => array('slug' => 'resource'),
            'capability_type'       => 'post',
            'has_archive'           => true,
            'hierarchical'          => false,
            'menu_position'         => null,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest'          => true,
        );
        
        register_post_type('psm_resource', $args);
    }
    
    /**
     * Register taxonomy
     */
    private function register_taxonomy() {
        $labels = array(
            'name'                       => _x('Resource Types', 'Taxonomy General Name', 'psm-resource-manager'),
            'singular_name'              => _x('Resource Type', 'Taxonomy Singular Name', 'psm-resource-manager'),
            'menu_name'                  => __('Resource Types', 'psm-resource-manager'),
            'all_items'                  => __('All Resource Types', 'psm-resource-manager'),
            'parent_item'                => __('Parent Resource Type', 'psm-resource-manager'),
            'parent_item_colon'          => __('Parent Resource Type:', 'psm-resource-manager'),
            'new_item_name'              => __('New Resource Type Name', 'psm-resource-manager'),
            'add_new_item'               => __('Add New Resource Type', 'psm-resource-manager'),
            'edit_item'                  => __('Edit Resource Type', 'psm-resource-manager'),
            'update_item'                => __('Update Resource Type', 'psm-resource-manager'),
            'view_item'                  => __('View Resource Type', 'psm-resource-manager'),
            'separate_items_with_commas' => __('Separate resource types with commas', 'psm-resource-manager'),
            'add_or_remove_items'        => __('Add or remove resource types', 'psm-resource-manager'),
            'choose_from_most_used'      => __('Choose from the most used', 'psm-resource-manager'),
            'popular_items'              => __('Popular Resource Types', 'psm-resource-manager'),
            'search_items'               => __('Search Resource Types', 'psm-resource-manager'),
            'not_found'                  => __('Not Found', 'psm-resource-manager'),
            'no_terms'                   => __('No resource types', 'psm-resource-manager'),
            'items_list'                 => __('Resource Types list', 'psm-resource-manager'),
            'items_list_navigation'      => __('Resource Types list navigation', 'psm-resource-manager'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        
        register_taxonomy('psm_resource_type', array('psm_resource'), $args);
        
        // Create default resource types
        $this->create_default_resource_types();
    }
    
    /**
     * Create default resource types
     */
    private function create_default_resource_types() {
        $default_types = array(
            'pdf' => __('PDF Document', 'psm-resource-manager'),
            'video' => __('Video', 'psm-resource-manager'),
            'podcast' => __('Podcast', 'psm-resource-manager'),
            'article' => __('Article', 'psm-resource-manager'),
            'link' => __('External Link', 'psm-resource-manager')
        );
        
        foreach ($default_types as $slug => $name) {
            if (!term_exists($name, 'psm_resource_type')) {
                wp_insert_term($name, 'psm_resource_type', array('slug' => $slug));
            }
        }
    }
    
    /**
     * Setup admin menu
     */
    private function setup_admin_menu() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Resources', 'psm-resource-manager'),
            __('Resources', 'psm-resource-manager'),
            'manage_options',
            'psm-resources',
            array($this, 'admin_page_resources'),
            'dashicons-portfolio',
            30
        );
        
        add_submenu_page(
            'psm-resources',
            __('All Resources', 'psm-resource-manager'),
            __('All Resources', 'psm-resource-manager'),
            'manage_options',
            'psm-resources',
            array($this, 'admin_page_resources')
        );
        
        add_submenu_page(
            'psm-resources',
            __('Add New Resource', 'psm-resource-manager'),
            __('Add New Resource', 'psm-resource-manager'),
            'manage_options',
            'psm-add-resource',
            array($this, 'admin_page_add_resource')
        );
        
        add_submenu_page(
            'psm-resources',
            __('Resource Types', 'psm-resource-manager'),
            __('Resource Types', 'psm-resource-manager'),
            'manage_options',
            'psm-resource-types',
            array($this, 'admin_page_resource_types')
        );
    }
    
    /**
     * Admin page: Resources
     */
    public function admin_page_resources() {
        include PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'templates/admin/resources-list.php';
    }
    
    /**
     * Admin page: Add New Resource
     */
    public function admin_page_add_resource() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['psm_nonce'], 'psm_add_resource')) {
            $result = PSM_Resource_Handler::save_resource($_POST);
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Resource saved successfully!', 'psm-resource-manager') . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Error saving resource. Please try again.', 'psm-resource-manager') . '</p></div>';
            }
        }
        
        include PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'templates/admin/add-resource.php';
    }
    
    /**
     * Admin page: Resource Types
     */
    public function admin_page_resource_types() {
        include PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'templates/admin/resource-types.php';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'psm-') !== false) {
            wp_enqueue_script(
                'psm-admin-js',
                PSM_RESOURCE_MANAGER_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                PSM_RESOURCE_MANAGER_VERSION,
                true
            );
            
            wp_enqueue_style(
                'psm-admin-css',
                PSM_RESOURCE_MANAGER_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                PSM_RESOURCE_MANAGER_VERSION
            );
            
            // Localize script for AJAX
            wp_localize_script('psm-admin-js', 'psm_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('psm_ajax_nonce')
            ));
            
            // Enqueue media uploader
            wp_enqueue_media();
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        if (is_singular('psm_resource') || is_post_type_archive('psm_resource')) {
            wp_enqueue_style(
                'psm-frontend-css',
                PSM_RESOURCE_MANAGER_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                PSM_RESOURCE_MANAGER_VERSION
            );
            
            wp_enqueue_script(
                'psm-frontend-js',
                PSM_RESOURCE_MANAGER_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                PSM_RESOURCE_MANAGER_VERSION,
                true
            );
            
            // Localize script for frontend functionality
            wp_localize_script('psm-frontend-js', 'psm_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('psm_frontend_nonce')
            ));
        }
    }
    
    /**
     * Load custom single template
     */
    public function load_single_template($single_template) {
        global $post;
        
        if ($post->post_type === 'psm_resource') {
            $custom_template = PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'templates/frontend/single-psm_resource.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $single_template;
    }
    
    /**
     * Load custom archive template
     */
    public function load_archive_template($archive_template) {
        if (is_post_type_archive('psm_resource') || is_tax('psm_resource_type')) {
            $custom_template = PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'templates/frontend/archive-psm_resource.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $archive_template;
    }
}

// Initialize the plugin
new PSM_Resource_Manager();