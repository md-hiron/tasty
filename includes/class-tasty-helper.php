<?php

/**
 * This class contain reuseable method all over the tasty module
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
 * Helper class for reuse method all over the tasty
 *
 * This class used to create necessary mysql datatable for tasty
 * 
 *
 * @since      1.0.0
 * @package    Tasty
 * @subpackage Tasty/includes
 * @author     Md Hiron Mia
 */
class Tasty_Helper{

    /**
     * Get Tasty Tags
     * Get all custom taxonomies name in array those are created only for tasty
     */
    public static function get_tasty_tags(){
        //get all taxonomies
        $all_taxonomies = get_taxonomies( [], 'objects' );

        //Get only custom taxonomies
        $custom_taxonomies = array_filter( $all_taxonomies, function( $taxonomy ){
            return !$taxonomy->_builtin; //exclude built-in taxonomy
        } );

        return array_map( function( $taxonomy ){ return $taxonomy->name; }, $custom_taxonomies );
    }

    /**
     * Get all wp and app user
     * 
     * @since   1.0.0
     * @access public
     */
    public static function get_all_wp_and_app_users(){

        global $wpdb;
        
        //all wp user ID
        $wp_users  = get_users( ['fields' => 'ID'] );

        //all app user ID
        $app_users = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}app_users"
        ) );

        //Combined user
        $combined_user = [];
        
        foreach( $wp_users as $user ){
            $combined_user[] = array(
                'user_type' => 'wp_user',
                'user_id'   =>  $user
            );
        }

        foreach( $app_users as $user ){
            $combined_user[] = array(
                'user_type' => 'app_user',
                'user_id'   =>  $user
            );
        }

        return $combined_user;

    }

    /**
     * get user email by ID
     */
    public static function get_user_email_by_id( $user_type, $user_id ){
        if( empty( $user_type ) || empty( $user_id ) ){
            return;
        }

        global $wpdb;

        if( 'wp_user' === $user_type ){
            $user = get_userdata( $user_id );

            return $user->user_email;

        }elseif( 'app_user' === $user_type ){
            return $wpdb->get_var( $wpdb->prepare( 
                "SELECT email FROM {$wpdb->prefix}app_users where id = %d",
                $user_id
             ) );
        }
    }

    /**
     * Get User info
     */
    public static function get_user_info( $user_info ) {
        // Check if the input matches wp_user_[number]
        if (preg_match('/^wp_user_(\d+)$/', $user_info, $matches)) {
            return [
                'column' => 'user_id',
                'user_id' => $matches[1]
            ];
        }
    
        // Check if the input matches app__user_[number]
        if (preg_match('/^app_user_(\d+)$/', $user_info, $matches)) {
            return [
                'column' => 'app_user_id',
                'user_id' => $matches[1]
            ];
        }
    
        // If no match, return null
        return null;
    }
}