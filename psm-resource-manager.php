<?php
/*
Plugin Name: PSM Resource Manager
Description: Gestionnaire de ressources (PDF, Vidéo, Podcast) pour WordPress avec CPT, taxonomie, interface admin et templates frontend.
Version: 1.0.0
Author: kanji8210
Text Domain: psm-resource-manager
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PSM_RM_VERSION', '1.0.0' );
define( 'PSM_RM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PSM_RM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Activation, désactivation, désinstallation
register_activation_hook( __FILE__, [ 'PSM_Resource_Manager_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'PSM_Resource_Manager_Deactivator', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'PSM_Resource_Manager_Uninstaller', 'uninstall' ] );

// Inclure les fichiers principaux
require_once PSM_RM_PLUGIN_DIR . 'includes/class-psm-resource-manager.php';
require_once PSM_RM_PLUGIN_DIR . 'includes/class-psm-resource-manager-activator.php';
require_once PSM_RM_PLUGIN_DIR . 'includes/class-psm-resource-manager-deactivator.php';
require_once PSM_RM_PLUGIN_DIR . 'includes/class-psm-resource-manager-uninstaller.php';

// Lancer le plugin
add_action( 'plugins_loaded', [ 'PSM_Resource_Manager', 'get_instance' ] );
