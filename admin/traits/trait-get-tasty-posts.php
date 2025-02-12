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
    public function get_tasty_posts( $request ){
        global $wpdb;
        
        //current user ID
        $user_id = get_current_user_id();

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

        // Fetch swiped posts for the user
        $column_name       = $user_id ? 'user_id' : 'app_user_id';
        $user_identifier   = $user_id ?: $app_user_id;

        //initially load 5 posts. When user swiped 3 item them load 3 images only
        $swiped_ids     = !empty( $request['swiped_ids'] ) ? $request['swiped_ids'] : [];
        $loaded_ids     = !empty( $request['loaded_ids'] ) ? $request['loaded_ids'] : [];

        //Post IDs that's already chosed by the user.
        $chose_post_ids    = $this->chose_post_ids( $user_identifier, $column_name );
        $excludes_posts    = array_unique( array_merge( $chose_post_ids, $loaded_ids ) );

        //Post per page and order based on user action
        $posts_per_page = $swiped_ids ? 3 : 5;
        $orderby = $swiped_ids ? 'date' : 'rand';
        
        $primary_args = array(
            'post_type'      => 'post',
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'publish',
            'orderby'        => $orderby,
            'tax_query'      => $this->tasty_tax_query( $user_identifier, $column_name ), //tax query from the tasty tax query method
            'post__not_in'   => $excludes_posts, // Exclude previously swiped posts
        );

        // Fetch posts
        $primary_query = new WP_Query( $primary_args );

        
        //user tag weight
        $user_tag_weights = $this->get_user_tag_weight( $user_identifier, $column_name );
        //Rank post using stored tag score
        $ranked_posts = $this->rank_posts_by_tag_weight( $primary_query->posts, $user_tag_weights );

        // exlode post those are fetched already and in ranked post
        $fetched_ids    = wp_list_pluck( $ranked_posts, 'ID' );
        $excludes_posts = array_merge( $excludes_posts, $fetched_ids );

        //if fewer post than needed, fetech additional posts without term
        if( count( $ranked_posts ) < $posts_per_page ){
            $remaining_posts_needed = $posts_per_page - count( $ranked_posts );

            //secondary args without tax query
            $fallback_args = array(
                'post_type'      => 'post',
                'posts_per_page' => $remaining_posts_needed,
                'post_status'    => 'publish',
                'orderby'        => 'rand',
                'post__not_in'   => $excludes_posts, // Exclude previously swiped posts
            );

            $fallback_query = new WP_Query( $fallback_args );
            $ranked_posts = array_merge( $ranked_posts, $fallback_query->posts );
        }

        // Prepare response
        $posts = [];

        if ( !empty( $ranked_posts ) ) {
            $posts = array_map( function( $post ) {
                return array(
                    'id'             => $post->ID,
                    'title'          => get_the_title( $post->ID ),
                    'featured_image' => get_the_post_thumbnail_url( $post->ID, 'full' ),
                    'addition_info'  => !empty( get_post_meta( $post->ID, 'addi_info', true ) ) ? get_post_meta( $post->ID, 'addi_info', true ) : null
                );
            }, $ranked_posts );
        }
        
        return new WP_REST_Response( $posts, 200 );  
    }

    /**
     * Get tasty tex query
     * This is one of the main method that work for recommendation. This tax query first try to get heighest tag weight score
     * if it doesn't found any then it will try to get data by user liked taxonomies. Dislike tags wil be always added if there is disliked terms
     * 
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
            return array();
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

    /**
     * Fetch user specific tag weight
     * 
     * @param   int     $user_identifier    The user ID or app user ID
     * @param   string  $column_name        Column name of user ID or App user ID
     * @param   array  $provided_ids       The ids that user swipped 3 times
     * 
     * @since 1.0.0
     * @access  private
     */
    private function get_user_tag_weight( $user_identifier, $column_name ){
        //check args are provided
        if( empty( $user_identifier ) || empty( $column_name ) ){
            return;
        }

        global $wpdb;

        $weights = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT tag_id, taxonomy, tag_weight_score as score
                FROM {$wpdb->prefix}{$this->tag_weight_table} WHERE 
                $column_name = %d",
                $user_identifier
            )
        );

        $tag_weights = [];
        foreach( $weights as $weight ){
            $tag_weights[ $weight->tag_id ] = (float) $weight->score;
        }

        return $tag_weights;
    }

    /**
     * Rank posts by tag weight
     * 
     * @param   object  $posts              filtered post object for ranking
     * @param   array   $user_tag_weight    tag weights array against tag IDs
     * 
     * @since   1.0.0
     * @access  private
     */
    private function rank_posts_by_tag_weight( $posts, $user_tag_weight ){
        //check args are provided
        if( empty( $posts ) || empty( $user_tag_weight ) ){
            return [];
        }

        $ranked_posts = [];

        foreach( $posts as $post ){
            $score = 0;

            // Get post terms
            $post_terms = wp_get_post_terms( $post->ID, Tasty_Helper::get_tasty_tags(), ['fields' => 'ids'] );

            //calculate score based on stored user tag weight
            foreach( $post_terms as $term_id ){
                if( isset( $user_tag_weight[$term_id] ) ){
                    $score += $user_tag_weight[$term_id];
                }
            }

            $ranked_posts[] = array( 'post' => $post, 'score' => $score );
        }

        //sore post by tags weight
        usort( $ranked_posts, function( $a, $b ){
            return $b['score'] <=> $a['score'];
        } );

        return array_column( $ranked_posts, 'post' );

    }
}