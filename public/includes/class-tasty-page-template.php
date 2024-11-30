<?php

/**
 * Creating page template for tasty front-end
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/public/includes
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * creating page template for tasty front-end
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 * @author     Md Hiron Mia
 */
class Tasty_Page_Template{

    /**
     * Add page tempalte for tasty
     * 
     * @param   array   $templates   List of all WordPress templates
     * 
     * @since   1.0.0
     * @access  public
     */
    public function add_page_template( $templates ){

        $templates['tasty-template.php'] = __( 'Tasty template', 'tasty' );
        return $templates;

    }

    /**
     * Load tasty template
     * 
     * @param   string  plugin template path
     * 
     * @since   1.0.0
     * @access  public
     */
    public function load_tasty_template( $template ){
        
        if( is_page_template('tasty-template.php') ){
            $tasty_template = TASTY_PUBLIC_DIR . 'templates/tasty-template.php';
            if( file_exists( $tasty_template ) ){
                return $tasty_template;
            }
        }

        return $template;
    }

}