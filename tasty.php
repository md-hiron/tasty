<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Tasty
 *
 * Plugin Name:       Tasty
 * Description:       A swipe-based plugin for WordPress that allows users to interact with cards by swiping left or right, with customizable actions for 'like' and 'dislike.'
 * Version:           1.0.0
 * Author:            Md Hiron Mia
 * Text Domain:       tasty
 * Domain Path:       /languages
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

//currently plugin version
define( 'TASTY_VERSION', '1.0.0' );

//Define paths for the sitemap files
define( 'TASTY_DIR', plugin_dir_path( __FILE__ ) );
define( 'TASTY_URL', plugin_dir_url( __FILE__ ) );
define( 'TASTY_ADMIN_DIR', TASTY_DIR . 'admin/' );
define( 'TASTY_ADMIN_URL', TASTY_URL . 'admin/' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tasty-activator.php
 */
function activate_tasty(){
    require_once TASTY_DIR . 'includes/class-tasty-activator.php';
    Tasty_Activator::activate();
}

/**
 * the code that runs during plugin deactivation.
 * This action is documented in includes/class-tasty-deactivator.php
 */
function deactivate_tasty(){
    require_once TASTY_DIR . 'includes/class-tasty-deactivator.php';
    Tasty_Deactivator::deactivate();
}

//plugin activation and deactivation hooks
register_activation_hook( __FILE__, 'activate_tasty' );
register_deactivation_hook( __FILE__, 'deactivate_tasty' );

/**
 * The core plugin class that is used to define internationalization
 * admin-specific hooks, and plublic-facing site hooks
 */
require TASTY_DIR . 'includes/class-tasty.php';

/**
 * Begins execution of the plugin.
 * 
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not effect the page life cycle
 * 
 * @since   1.0.0
 */
function run_tasty(){
    $plugin = new Tasty();
    $plugin->run();
}
run_tasty();