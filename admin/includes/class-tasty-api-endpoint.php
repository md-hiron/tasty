<?php

/**
 * This class is responsible for creating end point for get tasty post, save user choices
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/admin/includes
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * creating API endpoints for tasty
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 * @author     Md Hiron Mia
 */
class Tasty_API_Endpoint{

    /**
     * connecting traits
     */
    use Get_Tasty_Posts, Save_User_Choice, Get_All_User_Report;

    /**
     * Register rest route for tatsy
     * 
     * @since   1.0.0
     * @access  public
     */
    public function register_tasty_route(){
        register_rest_route( 'tasty/v1', 'get-tasty-posts', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_tasty_posts' ) ,
            'args'                => array(
                'swiped_ids' => array(
                    'required' => false,
                    'sanitize_callback' => function( $value ){
                        $sanitize_value =  rest_sanitize_array( $value );
                        return is_array( $sanitize_value ) ? array_map( 'absint', $sanitize_value ) : array();
                    },
                ),
                'loaded_ids' => array(
                    'required' => false,
                    'sanitize_callback' => function( $value ){
                        $sanitize_value =  rest_sanitize_array( $value );
                        return is_array( $sanitize_value ) ? array_map( 'absint', $sanitize_value ) : array();
                    },
                ),
            ),
            'permission_callback' => '__return_true'
        ) );

        register_rest_route( 'tasty/v1', 'save_choices', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'save_user_choices' ),
            'args'     => array(
                'post_id'  => array(
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ),
                'choice'   => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            ),
            'permission_callback' => '__return_true'
        ) );

        //Get Choices
        register_rest_route( 'tasty/v1', 'get_choices', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_user_choices' ),
            'args'     => array(
                'element'   => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'choice'   => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'user'   => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            ),
            'permission_callback' => function( $request ){
                return current_user_can('manage_options');
            }
        ) );

        //Get all user choices
        register_rest_route( 'tasty/v1', 'get_all_user_report', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_all_user_report' ),
            'args'     => array(
                'search'   => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            ),
            'permission_callback' => function( $request ){
                return current_user_can('manage_options');
            }
        ) );

        //Get all user choices
        register_rest_route( 'tasty/v1', 'get_tag_performance', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_tag_performance' ),
            'args'     => array(
                'user'   => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'perform'   => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            ),
            'permission_callback' => function( $request ){
                return current_user_can('manage_options');
            }
        ) );

        
    }

    

}