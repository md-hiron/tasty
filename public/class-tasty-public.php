<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/public
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * The public-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    Tasty
 * @subpackage Tasty/public
 * @author     Md Hiron Mia
 */
class Tasty_Public{

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
    public function enqueue_public_scripts(){
        wp_enqueue_style( $this->plugin_name .'-main', TASTY_PUBLIC_URL . 'assets/css/tasty-main.css', array(), time() );
        wp_enqueue_script( $this->plugin_name .'-main', TASTY_PUBLIC_URL . 'assets/js/tasty-main.js', array(), time(), true );

        wp_localize_script( $this->plugin_name .'-main', 'wpApiSettings', array(
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    /**
     * Load dependencies for admin area
     */
    private function load_dependencies(){
        require_once TASTY_PUBLIC_DIR . 'includes/class-tasty-page-template.php';
    }

}