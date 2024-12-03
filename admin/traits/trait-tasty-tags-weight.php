<?php

/**
 * This trait responsible for tasty weight scoring functionalty 
 * This trait is connected with Get_Tasty_Posts trait in admin/traits/trait-get-tasty-posts.php
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
 * Tasty tags Weight
 *
 * @package    Tasty
 * @subpackage Tasty/admin/traits
 * @author     Md Hiron Mia
 */
trait Tasty_Tags_Weight{

    //Tags weight table
    private $tag_weight_table   = 'tag_weight';

    /**
     * Save tag weight
     * This method will be use in Tasty 
     * 
     * @param   int     $post_id        Post ID of the swiped post
     * @param   string  $user_choice    Swiped action data
     * @param   int     $user_id        User ID of the swiped post
     * @param   int     $app_user_id    App user ID of the swiped post
     * 
     * @since   1.0.0
     * @access  public
     */
    public function save_tag_weight( $post_id, $user_choice, $user_id = null, $app_user_id = null  ){

        //check that $post_id and $user_choice shouldn't be empty
        if( empty( $post_id ) || empty( $user_choice ) ){
            return;
        }

        global $wpdb;

        $score_adjustment = $user_choice === 'like' ? 10 : -5;
        $user_column      = $user_id ? 'user_id' : 'app_user_id';
        $user_identifier  = $user_id ? $user_id : $app_user_id;

        $tag_weight_table = $wpdb->prefix . $this->tag_weight_table;

        $tasty_tags = Tasty_Helper::get_tasty_tags();

        foreach( $tasty_tags as $tag ){
            //Get terms associated with the post for this taxonomy
            $terms = get_the_terms( $post_id, $tag );
            if( is_array( $terms ) ){
                foreach( $terms as $term ){
                    $tag_id        = $term->term_id;
                    $taxonomy      = $term->taxonomy;
                    $initail_score = !empty( get_term_meta( $term->term_id, 'tasty_tag_weight', true ) ) ? intval( get_term_meta( $term->term_id, 'tasty_tag_weight', true ) ) : 0;

                    //check if the record exists
                    $existing_record = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * FROM $tag_weight_table WHERE tag_id = %d AND taxonomy = %s AND $user_column = %d",
                            $tag_id,
                            $taxonomy,
                            $user_identifier
                        )
                    );

                    if( $existing_record ){
                        //update existing record
                        $wpdb->update(
                            $tag_weight_table,
                            array(
                                'tag_weight_score' => max( 0, $existing_record->tag_weight_score + $score_adjustment ) //ensure that score donesn't go below 0
                            ),
                            array(
                                'id' =>  $existing_record->id
                            ),
                            array('%d'),
                            array('%d')
                        );
                    }else{
                        $wpdb->replace(
                            $tag_weight_table,
                            array(
                                'tag_id'           => $tag_id,
                                'taxonomy'         => $taxonomy,
                                'user_id'          => $user_id,
                                'app_user_id'      => $app_user_id,
                                'tag_weight_score' => max( 0, $initail_score + $score_adjustment )
                            ),
                            array(
                                '%d',
                                '%s',
                                '%d',
                                '%d',
                                '%d',
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * Get Heighest tag weight
     * 
     * @param   string  Column name of user id or app user id
     * @param   int     ID of the user or app user
     * @param   int     Minimum score of tag weight
     */
    public function get_heighest_weight_tag($column_name, $user_identifier, $min_tag_score){

        global $wpdb;

        $restults = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT tag_id, taxonomy FROM {$wpdb->prefix}{$this->tag_weight_table}
                WHERE $column_name = %d AND tag_weight_score >= %d
                ORDER BY tag_weight_score DESC",
                $user_identifier,
                $min_tag_score
            )
        );

        $height_score_tags = array();

        if( count( $restults ) > 0 ){
            foreach( $restults as $reuslt ){
                $height_score_tags[$reuslt->taxonomy][] = $reuslt->tag_id;
            }
        }

        return $height_score_tags;

    }
    

}