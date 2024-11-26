<?php

/**
 * Creating custom field for the post and taxonimies
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
 * creating custom field for the post and taxonomy
 *
 * Define Custom taxonomy
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 * @author     Md Hiron Mia
 */
class Tasty_Custom_Meta_Field{

    /**
     * Add custom meta box for Bathroom item extra feature
     * 
     * @since   1.0.0
     * @access  public
     */
    public function custom_meta_box(){

        add_meta_box(
            'tasty_post_meta',
            __( 'Tasty Meta', 'tasty' ),
            array( $this, 'tasty_post_meta_callback' ),
            'post',
            'normal',
            'high'
        );
        
    }

    /**
     * Callback function for custom meta box
     * 
     * @param   object  $post Get WordPress post object of the current post
     * 
     * @since   1.0.0
     * @access  public
     */
    public function tasty_post_meta_callback( $post ){

        //Nonce field for security
        wp_nonce_field( 'tasty_save_metabox_data', 'tasty_metabox_nonce' );

        //store post ID for repetitive use
        $post_id = $post->ID;

        $size     = ! empty( get_post_meta( $post_id, 'tasty_size', true ) ) ? get_post_meta( $post_id, 'tasty_size', true ) : '';
        $material = ! empty( get_post_meta( $post_id, 'tasty_material', true ) ) ? get_post_meta( $post_id, 'tasty_material', true ) : '';
        $shape    = ! empty( get_post_meta( $post_id, 'tasty_shape', true ) ) ? get_post_meta( $post_id, 'tasty_shape', true ) : '';

        ?>
            <div class="tasty-custom-meta-field">
                <label for="tasty_size_meta"><?php _e( 'Size', 'tasty' );?></label>
                <input type="text" class="tasty-custom-meta-input" name="tasty_size" id="tasty_size_meta" value="<?php echo esc_attr( $size )?>" size="100" />
            </div>
            <div class="tasty-custom-meta-field">
                <label for="tasty_mateiral_meta"><?php _e( 'Material', 'tasty' );?></label>
                <input type="text" class="tasty-custom-meta-input" name="tasty_material" id="tasty_mateiral_meta" value="<?php echo esc_attr( $material )?>" size="100" />
            </div>
            <div class="tasty-custom-meta-field">
                <label for="tasty_shape_meta"><?php _e( 'Shape', 'tasty' );?></label>
                <input type="text" class="tasty-custom-meta-input" name="tasty_shape" id="tasty_shape_meta" value="<?php echo esc_attr( $shape )?>" size="100" />
            </div>
        <?php

    }

    /**
     * Save custom post meta
     * This method will run on /includes/class-tasty.php
     * 
     * @param   int  $post_id   Get WordPress post object of the current post
     * 
     * @since   1.0.0
     * @access  public    
     */
    public function save_custom_post_meta( $post_id ){

        //check nonce
        if( ! isset( $_POST['tasty_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['tasty_metabox_nonce'], 'tasty_save_metabox_data' ) ){
            return;
        }

        // check if the user has permission to save
        if( !current_user_can( 'edit_post', $post_id ) ){
            return;
        }

        // avoid autosave
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
            return;
        }

        //validate and sanitize size meta
        if( isset( $_POST['tasty_size'] ) ){
            $sanitized_size = sanitize_text_field( $_POST['tasty_size'] );
            update_post_meta( $post_id, 'tasty_size', $sanitized_size );
        }

        //validate and sanitize material meta
        if( isset( $_POST['tasty_material'] ) ){
            $sanitized_material = sanitize_text_field( $_POST['tasty_material'] );
            update_post_meta( $post_id, 'tasty_material', $sanitized_material );
        }

        //validate and sanitize shape meta
        if( isset( $_POST['tasty_shape'] ) ){
            $sanitized_shape = sanitize_text_field( $_POST['tasty_shape'] );
            update_post_meta( $post_id, 'tasty_shape', $sanitized_shape );
        }

    }

    /**
     * Add custom meta field for custom taxonomy
     * This method will run on /includes/class-tasty.php
     * 
     * @since   1.0.0
     * @access  public
     */
    public function add_custom_meta_for_tax(){

        //Noce field for security
        wp_nonce_field( 'tasty_save_meta_for_tax', 'tasty_term_meta_nonce' );

        ?>
        <div class="tasty-custom-meta-field form-field">
            <label for="tasty_tag_weight"><?php _e( 'Tag Weight', 'tasty' );?></label>
            <input type="number" class="tasty-custom-meta-input" name="tasty_tag_weight" id="tasty_tag_weight" />
        </div>
        <?php
    }

    /**
     * Edit custom meta field for custom taxonomy
     * This method will run on /includes/class-tasty.php
     * 
     * @param   object  $term   The term object that will be edited
     * 
     * @since   1.0.0
     * @access  public
     */
    public function edit_custom_meta_for_tax( $term ){

        //Nonce for security
        wp_nonce_field( 'tasty_save_meta_for_tax', 'tasty_term_meta_nonce' );

        $weight = !empty( get_term_meta( $term->term_id, 'tasty_tag_weight', true ) ) ? get_term_meta( $term->term_id, 'tasty_tag_weight', true ) : '';

        ?>
        <tr class="from-field asty-custom-meta-field">
            <th scope="row" valing="top">
                <label for="tasty_tag_weight"><?php _e( 'Tag Weight', 'tasty' );?></label>
            </th>
            <td>
                <input type="number" name="tasty_tag_weight" id="tasty_tag_weight" class="tasty-custom-meta-input" value="<?php echo esc_attr( $weight ); ?>">
            </td>
        </tr>
        <?php
    }

    /**
     * Save custom meta field for custom taxonomy
     * This method will run on /includes/class-tasty.php
     * 
     * @param   int  $term_id   Term Id that we going to save
     * 
     * @since   1.0.0
     * @access  public    
     */
    public function save_custom_meta_for_tax( $term_id ){
        if( ! isset( $_POST['tasty_term_meta_nonce'] ) || ! wp_verify_nonce( $_POST['tasty_term_meta_nonce'], 'tasty_save_meta_for_tax' ) ){
            return false;
        }

        if( isset( $_POST['tasty_tag_weight'] ) ){
            $sanitized_weight = sanitize_text_field( $_POST['tasty_tag_weight'] );

            update_term_meta( $term_id, 'tasty_tag_weight', $sanitized_weight );
        }
    }

    /**
     * Add weight meta to custom column
     * This method will run on /includes/class-tasty.php
     * 
     * @param   array   $columns    Get all taxonomy column list
     * 
     * @since   1.0.0
     * @access  public
     */
    public function add_custom_meta_in_tax_column( $columns ){

        $columns['tag_weight'] = __( 'Tag Weight', 'tasty' );

        return $columns;
    }

    /**
     * Show custom meta value in taxonomy column
     * This method will run on /includes/class-tasty.php
     * 
     * @param   string  $content        The content that will show in column value
     * @param   string  $column_name    The column name of taxonomy
     * @param   int     $term_id        The term id of the taxonomy to get value
     * 
     * @since   1.0.0
     * @access  public
     */
    public function show_column_value_in_tax( $content, $column_name, $term_id ){
        if( 'tag_weight' === $column_name ){
            $weight  = get_term_meta( $term_id, 'tasty_tag_weight', true );
            $content = esc_html( $weight );
        }

        return $content;
    }

    /**
     * Get all custom taxonomy list
     * We will use this function to get all custom taxonomy name to add custom meta in every taxonomy
     * 
     * @deprecated  This method has no use for now. It may use for future
     * 
     * @since   1.0.0
     * @access  public
     */
    public function get_all_tasty_taxonomy(){

        //get all taxonomies
        $all_taxonomies = get_taxonomies( [], 'objects' );

        //Get only custom taxonomies
        $custom_taxonomies = array_filter( $all_taxonomies, function( $taxonomy ){
            return !$taxonomy->_builtin; //exclude built-in taxonomy
        } );

        return array_map( function( $taxonomy ){ return $taxonomy->name; }, $custom_taxonomies );
    }

}