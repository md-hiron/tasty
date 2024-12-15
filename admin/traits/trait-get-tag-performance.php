<?php

/**
 * This trati is responsible for getting all user report of tasty
 * This trait is connected with Tasty_API_Endpoint class in admin/includes/class-tasty-api-endpoint.php
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/admin/trait
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * Get all user reports
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 * @author     Md Hiron Mia
 */
trait Get_Tag_Performance{

    /**
     * Save user choices
     * Store user choices in database. This method also create app user on first swip if user is not logged in
     * 
     * @param   array     $request    Requested parameter
     * 
     * @since   1.0.0
     * @access  public
     */
    public function get_tag_performance( $request ){

        $user_info = Tasty_Helper::get_user_info( $request['user'] );
        $column    = !empty( $user_info['column'] ) ? $user_info['column'] : 'user_id';
        $user_id   = !empty( $user_info['user_id'] ) ? $user_info['user_id'] : get_current_user_id();
        $perform   = !empty( $request['perform'] ) ? $request['perform'] : 'popularity';

        switch ( $perform ){
            case 'relevance' :
                $relevance = $this->get_tag_relevance( $user_id, $column );

                return new WP_REST_Response( $relevance, 200 );

                break;
            case 'avoidance' :
                $avoidance = $this->get_avoidance_rate( $user_id, $column );

                return new WP_REST_Response( $avoidance, 200 );

                break;
            case 'top_tag_element' :
                $top_tags = $this->get_top_tags( $user_id, $column );

                return new WP_REST_Response( $top_tags, 200 );

                break;
            case 'interaction_depth' :
                $interaction_depth = $this->get_interaction_depth( $user_id, $column );

                return new WP_REST_Response( $interaction_depth, 200 );

                break;
            default :
                $popularity = $this->get_popular_tags( $user_id, $column );

                return new WP_REST_Response( $popularity, 200 );
        }

        

    }

    /**
     * Get popular tags
     */
    public function get_popular_tags( $user_id, $column ){

        global $wpdb;
        $wpdb_prefix   = $wpdb->prefix;
        $user_choice   = $wpdb_prefix . 'user_choices';
        $term_relation = $wpdb_prefix . 'term_relationships';
        $term_taxonomy = $wpdb_prefix . 'term_taxonomy';
        $terms         = $wpdb_prefix . 'terms';
         

        $query = "
            SELECT 
            tt.term_id,
            term.name,
            tt.taxonomy,
            count(*) as total_interactions,
            SUM( CASE WHEN choice = 'like' THEN 1 ELSE 0 END) AS likes,
            SUM( CASE WHEN choice = 'dislike' THEN 1 ELSE 0 END ) as dislikes,
            ( SUM( CASE WHEN choice = 'like' THEN 1 ELSE 0 END) / COUNT(*) ) * 100 AS popularity_percentage
            FROM $user_choice uc
            INNER JOIN $term_relation tr ON uc.post_id = tr.object_id
            INNER JOIN $term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN $terms terms ON tt.term_taxonomy_id = terms.term_id
            WHERE $column = %d
            GROUP BY tt.term_id, tt.taxonomy, terms.name
            ORDER BY popularity_percentage DESC 
        ";

        return $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
    }

    /**
     * get tag relevance
     */
    public function get_tag_relevance( $user_id, $column ){
        global $wpdb;
        $wpdb_prefix   = $wpdb->prefix;
        $tag_wieght    = $wpdb_prefix . 'tag_weight';


        return $wpdb->get_results( $wpdb->prepare(
            "SELECT tag_id, taxonomy
            FROM $tag_wieght
            where $column = %d
            ORDER BY tag_weight_score DESC",
            $user_id
        ) );
    }

    /**
     * Get avoidence rates
     */
    public function get_avoidance_rate( $user_id, $column ){

        global $wpdb;
        $wpdb_prefix   = $wpdb->prefix;
        $user_choice   = $wpdb_prefix . 'user_choices';
        $term_relation = $wpdb_prefix . 'term_relationships';
        $term_taxonomy = $wpdb_prefix . 'term_taxonomy';
         

        $query = "
            SELECT 
            tt.term_id,
            tt.taxonomy,
            count(*) as total_interactions,
            SUM( CASE WHEN choice = 'dislike' THEN 1 ELSE 0 END ) as dislikes,
            ( SUM( CASE WHEN choice = 'dislike' THEN 1 ELSE 0 END) / COUNT(*) ) * 100 AS avoidance_rate
            FROM $user_choice uc
            INNER JOIN $term_relation tr ON uc.post_id = tr.object_id
            INNER JOIN $term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE $column = %d
            GROUP BY tt.term_id, tt.taxonomy
            ORDER BY avoidance_rate DESC 
        ";

        return $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
    }


    /**
     * Get avoidence rates
     */
    public function get_top_tags( $user_id, $column ){

        global $wpdb;
        $wpdb_prefix   = $wpdb->prefix;
        $user_choice   = $wpdb_prefix . 'user_choices';
        $term_relation = $wpdb_prefix . 'term_relationships';
        $term_taxonomy = $wpdb_prefix . 'term_taxonomy';
         

        $query = "
            SELECT 
            tt.term_id,
            tt.taxonomy,
            count(*) as total_likes
            FROM $user_choice uc
            INNER JOIN $term_relation tr ON uc.post_id = tr.object_id
            INNER JOIN $term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE $column = %d AND choice = 'like'
            GROUP BY tt.term_id, tt.taxonomy
            ORDER BY tt.taxonomy ASC, total_likes DESC
        ";

        return $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
    }

    /**
     * Get avoidence rates
     */
    public function get_interaction_depth( $user_id, $column ){

        global $wpdb;
        $wpdb_prefix   = $wpdb->prefix;
        $user_choice   = $wpdb_prefix . 'user_choices';
        $term_relation = $wpdb_prefix . 'term_relationships';
        $term_taxonomy = $wpdb_prefix . 'term_taxonomy';
         

        $query = "
            SELECT 
            tt.taxonomy,
            count(*) as total_interaction
            FROM $user_choice uc
            INNER JOIN $term_relation tr ON uc.post_id = tr.object_id
            INNER JOIN $term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE $column = %d
            GROUP BY tt.term_id, tt.taxonomy
            ORDER BY total_interaction DESC
        ";

        return $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
    }
    

}