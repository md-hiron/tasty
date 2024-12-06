<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 * @author     Md Hiron Mia
 */
class Tasty_Admin{

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
     * Initialize the class and set its properties
     * 
     * @since   1.0.0
     * @access  public
     * @param   string  $plugin_name     The name of the plugin
     * @param   string  $version         The version of this plugin
     */
    public function __construct( $plugin_name, $version ){

        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        $this->load_dependencies();

    }

    /**
     * Register styles and scripts for the admin area
     * 
     * @since   1.0.0
     * @access  public
     */
    public function enqueue_admin_scripts(){
        wp_enqueue_style( $this->plugin_name .'-admin', TASTY_ADMIN_URL . 'assets/css/tasty-admin.css', array(), time() );
        wp_enqueue_script( $this->plugin_name .'-admin', TASTY_ADMIN_URL . 'assets/js/tasty-admin.js', array(), time(), true );
    }

    /**
     * Load dependencies for admin area
     */
    private function load_dependencies(){
        require_once TASTY_ADMIN_DIR . 'traits/trait-tasty-tags-weight.php';
        require_once TASTY_ADMIN_DIR . 'traits/trait-get-tasty-posts.php';
        require_once TASTY_ADMIN_DIR . 'traits/trait-save-user-choice.php';
        require_once TASTY_ADMIN_DIR . 'traits/trait-get-all-user-report.php';

        require_once TASTY_ADMIN_DIR . 'includes/class-tasty-taxonomy.php';
        require_once TASTY_ADMIN_DIR . 'includes/class-tasty-custom-field.php';
        require_once TASTY_ADMIN_DIR . 'includes/class-tasty-user-report.php';
        require_once TASTY_ADMIN_DIR . 'includes/class-tasty-api-endpoint.php';
    }

}