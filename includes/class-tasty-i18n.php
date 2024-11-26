<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/includes
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Tasty
 * @subpackage Tasty/includes
 * @author     Md Hiron Mia
 */
class Tasty_i18n {
    /**
     * Load the plugin text domain for translation
     * 
     * @since   1.0.0
     */
    public function load_plugin_textdomain(){
        
        load_plugin_textdomain(
            'tasty',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . 'languages/'
        );
    }
}