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
}