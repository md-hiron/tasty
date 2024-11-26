<?php

/**
 * Creating custom taxonomy for the plugin
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
 * reating custom taxonomy for the plugin
 *
 * Define Custom taxonomy
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 * @author     Md Hiron Mia
 */
class Tasty_Taxonomies{

    /**
     * Register styles and scripts for the admin Area
     * 
     * @since   1.0.0
     * @access  public
     */
    public function custom_taxonomies(){

        // Style taxonomy
        $labels = array(
            'name'              => _x( 'Style', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Style', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Style', 'tasty' ),
            'all_items'         => __( 'All Styles', 'tasty' ),
            'parent_item'       => __( 'Parent Style', 'tasty' ),
            'parent_item_colon' => __( 'Parent style:', 'tasty' ),
            'edit_item'         => __( 'Edit Style', 'tasty' ),
            'update_item'       => __( 'Update Style', 'tasty' ),
            'add_new_item'      => __( 'Add New Style', 'tasty' ),
            'new_item_name'     => __( 'New Style Name', 'tasty' ),
            'menu_name'         => __( 'Style', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'style' ),
        );

        register_taxonomy( 'style', 'post', $args );

        unset( $labels );
        unset( $args );

        // Color taxonomy
        $labels = array(
            'name'              => _x( 'Color', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Color', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Color', 'tasty' ),
            'all_items'         => __( 'All Colors', 'tasty' ),
            'parent_item'       => __( 'Parent Color', 'tasty' ),
            'parent_item_colon' => __( 'Parent Color:', 'tasty' ),
            'edit_item'         => __( 'Edit Color', 'tasty' ),
            'update_item'       => __( 'Update Color', 'tasty' ),
            'add_new_item'      => __( 'Add New Color', 'tasty' ),
            'new_item_name'     => __( 'New Color Name', 'tasty' ),
            'menu_name'         => __( 'Color', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'color' ),
        );

        register_taxonomy( 'color', 'post', $args );

        unset( $labels );
        unset( $args );

        // Element Type taxonomy
        $labels = array(
            'name'              => _x( 'Element Type', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Element Type', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Element Type', 'tasty' ),
            'all_items'         => __( 'All Element Types', 'tasty' ),
            'parent_item'       => __( 'Parent Element Type', 'tasty' ),
            'parent_item_colon' => __( 'Parent Element Type:', 'tasty' ),
            'edit_item'         => __( 'Edit Element Type', 'tasty' ),
            'update_item'       => __( 'Update Element Type', 'tasty' ),
            'add_new_item'      => __( 'Add New Element Type', 'tasty' ),
            'new_item_name'     => __( 'New Element Type Name', 'tasty' ),
            'menu_name'         => __( 'Element Type', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'element-type' ),
        );

        register_taxonomy( 'element-type', 'post', $args );

        unset( $labels );
        unset( $args );

        // Material taxonomy
        $labels = array(
            'name'              => _x( 'Material', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Material', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Material', 'tasty' ),
            'all_items'         => __( 'All Materials', 'tasty' ),
            'parent_item'       => __( 'Parent Material', 'tasty' ),
            'parent_item_colon' => __( 'Parent Material:', 'tasty' ),
            'edit_item'         => __( 'Edit Material', 'tasty' ),
            'update_item'       => __( 'Update Material', 'tasty' ),
            'add_new_item'      => __( 'Add New Material', 'tasty' ),
            'new_item_name'     => __( 'New Material Name', 'tasty' ),
            'menu_name'         => __( 'Material', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'material' ),
        );

        register_taxonomy( 'material', 'post', $args );

        unset( $labels );
        unset( $args );

        // Size taxonomy
        $labels = array(
            'name'              => _x( 'Size', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Size', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Size', 'tasty' ),
            'all_items'         => __( 'All Sizes', 'tasty' ),
            'parent_item'       => __( 'Parent Size', 'tasty' ),
            'parent_item_colon' => __( 'Parent Size:', 'tasty' ),
            'edit_item'         => __( 'Edit Size', 'tasty' ),
            'update_item'       => __( 'Update Size', 'tasty' ),
            'add_new_item'      => __( 'Add New Size', 'tasty' ),
            'new_item_name'     => __( 'New Size Name', 'tasty' ),
            'menu_name'         => __( 'Size', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'size' ),
        );

        register_taxonomy( 'size', 'post', $args );

        unset( $labels );
        unset( $args );

        // Shape taxonomy
        $labels = array(
            'name'              => _x( 'Shape', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Shape', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Shape', 'tasty' ),
            'all_items'         => __( 'All Shapes', 'tasty' ),
            'parent_item'       => __( 'Parent Shape', 'tasty' ),
            'parent_item_colon' => __( 'Parent Shape:', 'tasty' ),
            'edit_item'         => __( 'Edit Shape', 'tasty' ),
            'update_item'       => __( 'Update Shape', 'tasty' ),
            'add_new_item'      => __( 'Add New Shape', 'tasty' ),
            'new_item_name'     => __( 'New Shape Name', 'tasty' ),
            'menu_name'         => __( 'Shape', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'shape' ),
        );

        register_taxonomy( 'shape', 'post', $args );

        unset( $labels );
        unset( $args );

        // Features taxonomy
        $labels = array(
            'name'              => _x( 'Features', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Feature', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Features', 'tasty' ),
            'all_items'         => __( 'All Features', 'tasty' ),
            'parent_item'       => __( 'Parent Feature', 'tasty' ),
            'parent_item_colon' => __( 'Parent Feature:', 'tasty' ),
            'edit_item'         => __( 'Edit Feature', 'tasty' ),
            'update_item'       => __( 'Update Feature', 'tasty' ),
            'add_new_item'      => __( 'Add New Feature', 'tasty' ),
            'new_item_name'     => __( 'New Feature Name', 'tasty' ),
            'menu_name'         => __( 'Features', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'query_var'         => true,
            'public'            => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'features' ),
        );

        register_taxonomy( 'features', 'post', $args );

        unset( $labels );
        unset( $args );

        // Room Type taxonomy
        $labels = array(
            'name'              => _x( 'Room Type', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Room Type', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Room Type', 'tasty' ),
            'all_items'         => __( 'All Room Types', 'tasty' ),
            'parent_item'       => __( 'Parent Room Type', 'tasty' ),
            'parent_item_colon' => __( 'Parent Room Type:', 'tasty' ),
            'edit_item'         => __( 'Edit Room Type', 'tasty' ),
            'update_item'       => __( 'Update Room Type', 'tasty' ),
            'add_new_item'      => __( 'Add New Room Type', 'tasty' ),
            'new_item_name'     => __( 'New Room Type Name', 'tasty' ),
            'menu_name'         => __( 'Room Type', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'query_var'         => true,
            'public'            => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'room-type' ),
        );

        register_taxonomy( 'room-type', 'post', $args );

        unset( $labels );
        unset( $args );

        // Price Category taxonomy
        $labels = array(
            'name'              => _x( 'Price Category', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Price Category', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Price Category', 'tasty' ),
            'all_items'         => __( 'All Price Categories', 'tasty' ),
            'parent_item'       => __( 'Parent Price Category', 'tasty' ),
            'parent_item_colon' => __( 'Parent Price Category:', 'tasty' ),
            'edit_item'         => __( 'Edit Price Category', 'tasty' ),
            'update_item'       => __( 'Update Price Category', 'tasty' ),
            'add_new_item'      => __( 'Add New Price Category', 'tasty' ),
            'new_item_name'     => __( 'New Price Category Name', 'tasty' ),
            'menu_name'         => __( 'Price Category', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'price-category' ),
        );

        register_taxonomy( 'price-category', 'post', $args );

        unset( $labels );
        unset( $args );

        // Mood/Theme taxonomy
        $labels = array(
            'name'              => _x( 'Mood/Theme', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Mood/Theme', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Mood/Theme', 'tasty' ),
            'all_items'         => __( 'All Mood/Theme', 'tasty' ),
            'parent_item'       => __( 'Parent Mood/Theme', 'tasty' ),
            'parent_item_colon' => __( 'Parent Mood/Theme:', 'tasty' ),
            'edit_item'         => __( 'Edit Mood/Theme', 'tasty' ),
            'update_item'       => __( 'Update Mood/Theme', 'tasty' ),
            'add_new_item'      => __( 'Add New Mood/Theme', 'tasty' ),
            'new_item_name'     => __( 'New Mood/Theme', 'tasty' ),
            'menu_name'         => __( 'Mood/Theme', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'mood-theme' ),
        );

        register_taxonomy( 'mood-theme', 'post', $args );

        unset( $labels );
        unset( $args );

        // Usage taxonomy
        $labels = array(
            'name'              => _x( 'Usage', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Usage', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Usage', 'tasty' ),
            'all_items'         => __( 'All Usage', 'tasty' ),
            'parent_item'       => __( 'Parent Usage', 'tasty' ),
            'parent_item_colon' => __( 'Parent Usage:', 'tasty' ),
            'edit_item'         => __( 'Edit Usage', 'tasty' ),
            'update_item'       => __( 'Update Usage', 'tasty' ),
            'add_new_item'      => __( 'Add New Usage', 'tasty' ),
            'new_item_name'     => __( 'New Usage', 'tasty' ),
            'menu_name'         => __( 'Usage', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'usage' ),
        );

        register_taxonomy( 'usage', 'post', $args );

        unset( $labels );
        unset( $args );

        // Functionality taxonomy
        $labels = array(
            'name'              => _x( 'Functionality', 'taxonomy general name', 'tasty' ),
            'singular_name'     => _x( 'Functionality', 'taxonomy singular name', 'tasty' ),
            'search_items'      => __( 'Search Functionality', 'tasty' ),
            'all_items'         => __( 'All Functionalities', 'tasty' ),
            'parent_item'       => __( 'Parent Functionality', 'tasty' ),
            'parent_item_colon' => __( 'Parent Functionality:', 'tasty' ),
            'edit_item'         => __( 'Edit Functionality', 'tasty' ),
            'update_item'       => __( 'Update Functionality', 'tasty' ),
            'add_new_item'      => __( 'Add New Functionality', 'tasty' ),
            'new_item_name'     => __( 'New Functionality', 'tasty' ),
            'menu_name'         => __( 'Functionality', 'tasty' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'public'            => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'functionality' ),
        );

        register_taxonomy( 'functionality', 'post', $args );
    }

}