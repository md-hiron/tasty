<?php

/**
 * This trait is responsible for getting post on API request
 * This trait is used in admin/includes/class-tasty-api-endpoint.php
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/admin/traits
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * Get Tasty post with recommendtion logic on API call
 *
 * @package    Tasty
 * @subpackage Tasty/admin/traits
 * @author     Md Hiron Mia
 */
trait Get_Tasty_Posts{

    private $user_choices_table = 'user_choices';
    private $app_users_table    = 'app_users';
    private $tag_weight_table   = 'tag_weight';

    /**
     * Using traits
     * 
     * Tasty_Tags_Weight this trais works for Tasty tags weight functionalty
     * This trait contain two methods save_tag_weight and get_heighest_weight_tag
     * 
     */
    use Tasty_Tags_Weight;

    /**
     * Callback method for get tasty posts
     * 
     * This time we need to add tax query. We have 12 custom taxonomy. We will get liked post id's taxnomy and dislike post id's taxonomy and inlcude liked taxonomy term and exlude disliked taxonomy term
     * 
     * @param   array  $rquest Get all url paramater
     * 
     * @version 1.0.0
     * @access  public
     */
    public function get_tasty_posts( $rquest ){
        global $wpdb;
        
        //current user ID
        $user_id     = get_current_user_id();

        //get app user ID if user is not loggedin
        $session_id  = isset( $_COOKIE['app_user_session'] ) ? sanitize_text_field( $_COOKIE['app_user_session'] ) : null;
        $app_user_id = null;

        if( !$user_id ){
            if( $session_id ){
                $app_user_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}{$this->app_users_table} WHERE session_id = %s",
                        $session_id
                    )
                );
            }
        }

        //initially load 5 posts. When user swiped 3 item them load 3 images only
        $swiped_ids     = !empty( $rquest['swiped_ids'] ) ? $rquest['swiped_ids'] : [];
        $loaded_ids     = !empty( $rquest['loaded_ids'] ) ? $rquest['loaded_ids'] : [];

        //Initail posts per page
        $posts_per_page = 5;

        //Posts per page on next fetch
        if( $swiped_ids ){
            $posts_per_page = 3;
        }
        
        // Fetch swiped posts for the user
        $column_name       = $user_id ? 'user_id' : 'app_user_id';
        $user_identifier   = $user_id ?: $app_user_id;

        //Post IDs that's already chosed by the user.
        $chose_post_ids    = $this->chose_post_ids( $user_identifier, $column_name );

        $excludes_posts    = array_unique( array_merge( $chose_post_ids, $loaded_ids ) );
        
        $primary_args = array(
            'post_type'      => 'post',
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'publish',
            //'orderby'        => 'rand',
            'tax_query'      => $this->tasty_tax_query( $user_identifier, $column_name ), //tax query from the tasty tax query method
            'post__not_in'   => $excludes_posts, // Exclude previously swiped posts
        );

        // Fetch posts
        $primary_query = new WP_Query( $primary_args );
        $found_posts   = $primary_query->found_posts;

        $fetched_ids    = wp_list_pluck( $primary_query->posts, 'ID' );
        $excludes_posts = array_merge( $excludes_posts, $fetched_ids );

        //Initial combined posts
        $combined_posts = $primary_query->posts;

        //if fewer post than needed, fetech additional posts without term
        if( $found_posts < $posts_per_page ){
            $remaining_posts_needed = $posts_per_page - $found_posts;

            //secondary args without tax query
            $secondary_args = array(
                'post_type'      => 'post',
                'posts_per_page' => $posts_per_page,
                'post_status'    => 'publish',
                //'orderby'        => 'rand',
                'post__not_in'   => $excludes_posts, // Exclude previously swiped posts
            );

            $secondary_query = new WP_Query( $secondary_args );

            $combined_posts = array_merge( $combined_posts, $secondary_query->posts );

        }

        // Prepare response
        $posts = [];

        if ( !empty( $combined_posts ) ) {
            $posts = array_map( function( $post ) {
                return array(
                    'id'             => $post->ID,
                    'title'          => get_the_title( $post->ID ),
                    'featured_image' => get_the_post_thumbnail_url( $post->ID, 'full' ),
                );
            }, $combined_posts );
        }
        
        return new WP_REST_Response( $posts, 200 );
    }

    /**
     * Get tasty tex query
     * This is one of the main method that work for recommendation. This tax query first try to get heighest tag weight score
     * if it doesn't found any then it will try to get data by user liked taxonomies. Dislike tags wil be always added if there is disliked terms
     * 
     * 
     */
    private function tasty_tax_query( $user_identifier, $column_name ){
        //Liked post ids
        $liked_post_ids    = $this->get_liked_post_ids( $user_identifier, $column_name );
        $disliked_post_ids = $this->get_disliked_post_ids( $user_identifier, $column_name );

        //Fetch taxonomy terms for liked and disliked post
        $liked_terms    = []; // Associative array to store terms by taxonomy
        $disliked_terms = [];
        $tasty_tags     = Tasty_Helper::get_tasty_tags(); // Assume this returns an array of taxonomy names

        if ( is_array( $tasty_tags ) ) {
            foreach ( $tasty_tags as $taxonomy ) {
                // Get terms for liked posts under the current taxonomy
                $terms_for_liked = wp_get_object_terms( $liked_post_ids, $taxonomy, array( 'fields' => 'ids' ) );
                if ( ! empty( $terms_for_liked ) ) {
                    $liked_terms[ $taxonomy ] = $terms_for_liked; // Store terms by taxonomy label
                }

                // Get terms for disliked posts under the current taxonomy
                $terms_for_disliked = wp_get_object_terms( $disliked_post_ids, $taxonomy, array( 'fields' => 'ids' ) );
                if ( ! empty( $terms_for_disliked ) ) {
                    $disliked_terms[ $taxonomy ] = $terms_for_disliked; // Store terms by taxonomy label
                }
            }
        }

        $tax_query = array(
            'relation' => 'OR', // Combine liked and disliked conditions logically
        );

        $heighest_score_tags = $this->get_heighest_weight_tag( $column_name, $user_identifier, 30 );
        
        // Add liked terms (if available)
        if ( !empty( $heighest_score_tags ) ) {
            foreach ( $heighest_score_tags as $term => $value ) {
                $tax_query[] = array(
                    'taxonomy' => $term,
                    'field'    => 'term_id',
                    'terms'    => $value,
                    'operator' => 'IN',
                );
            }
        }elseif( !empty( $liked_terms)  ){
            foreach ( $liked_terms as $term => $value ) {
                $tax_query[] = array(
                    'taxonomy' => $term,
                    'field'    => 'term_id',
                    'terms'    => $value,
                    'operator' => 'IN',
                );
            }
        }
        
        // Exclude disliked terms (if available)
        if ( !empty( $disliked_terms ) ) {
            foreach ( $disliked_terms as $term => $value ) {
                $tax_query[] = array(
                    'taxonomy' => $term,
                    'field'    => 'term_id',
                    'terms'    => $value,
                    'operator' => 'NOT IN',
                );
            }
        }

        return $tax_query;
    }


    /**
     * Swipped post Id of a user
     * 
     * @param   int     User or App user ID
     * 
     * @version 1.0.0
     * @access  private
     */
    private function chose_post_ids( $user_identifier, $column_name ){

        if( ! $user_identifier  || ! $column_name ){
            return array();
        }

        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->prefix}{$this->user_choices_table} WHERE $column_name = %d",
                $user_identifier
            )
        );

    }

    /**
     * Get latest liked ID of user swip
     * 
     * @param   int     $user_identifier    The user ID or app user ID
     * @param   string  $column_name        Column name of user ID or App user ID
     * @param   array  $provided_ids       The ids that user swipped 3 times
     * 
     * @since   1.0.0
     * @access  private
     */
    private function get_liked_post_ids( $user_identifier, $column_name ){
        //check args are provided
        if( empty( $user_identifier ) || empty( $column_name ) ){
            return;
        }

        global $wpdb;

        $latest_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->prefix}{$this->user_choices_table}
                WHERE $column_name = %d AND choice = %s
                ORDER BY time DESC LIMIT 3",
                $user_identifier,
                'like'
            )
        );

        // Merge provided IDs and latest IDs
        return $latest_ids;
    }

    /**
     * Get latest disliked ID of user swip
     * 
     * @param   int     $user_identifier    The user ID or app user ID
     * @param   string  $column_name        Column name of user ID or App user ID
     * @param   array  $provided_ids       The ids that user swipped 3 times
     * 
     * @since   1.0.0
     * @access  private
     */
    private function get_disliked_post_ids( $user_identifier, $column_name ){
        //check args are provided
        if( empty( $user_identifier ) || empty( $column_name ) ){
            return;
        }

        global $wpdb;

        $latest_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->prefix}{$this->user_choices_table}
                WHERE $column_name = %d AND choice = %s
                ORDER BY time DESC LIMIT 3",
                $user_identifier,
                'dislike'
            )
        );

        // Merge provided IDs and latest IDs
        return $latest_ids;
    }
    

}