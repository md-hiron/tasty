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

    private $user_choices_table = 'user_choices';
    private $app_users_table    = 'app_users';

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
                'app_user_id' => array(
                    'required' => false,
                    'sanitize_callback' => 'absint',
                ),
                'swiped_ids' => array(
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
    }

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
        
        $user_id     = get_current_user_id();
        $app_user_id = !empty( $rquest['app_user_id'] ) ? $rquest['app_user_id'] : false;
        $swiped_ids  = $rquest['swiped_ids'];

        // if ( ! $user_id && ! $app_user_id ) {
        //     return new WP_REST_Response( [ 'message' => 'User not identified.' ], 400 );
        // }

        // Fetch swiped posts for the user
        $column_name       = $user_id ? 'user_id' : 'app_user_id';
        $user_identifier   = $user_id ?: $app_user_id;

        //Post IDs that's already chosed by the user.
        $chose_post_ids    = $this->chose_post_ids( $user_identifier, $column_name );

        //Liked post ids
        $liked_post_ids    = $this->get_liked_post_ids( $user_identifier, $column_name, $swiped_ids );
        $disliked_post_ids = $this->get_disliked_post_ids( $user_identifier, $column_name, $swiped_ids );

        //Fetch taxonomy terms for liked and disliked post
        $liked_terms       = [];
        $disliked_terms    = [];
        $tasty_tags        = Tasty_Helper::get_tasty_tags();

        if( is_array( $tasty_tags ) ){
            foreach( $tasty_tags as $tag ){
                $liked_terms = array_merge(
                    $liked_terms,
                    wp_get_object_terms( $liked_post_ids, $tag, array( 'fields' => 'ids' ) )
                );

                $disliked_terms = array_merge(
                    $disliked_terms,
                    wp_get_object_terms( $disliked_terms, $tag, array( 'fields' => 'ids' ) )
                );
            }
        }

        //Build Tax queries
        $tax_query = [];

        if( !empty( $liked_terms ) ){
            $tax_query[] = array(
                'taxonomies' => $tasty_tags,
                'field'      => 'term_id',
                'terms'      => array_unique( $liked_terms ),
                'operator'   => 'IN'
            );
        }

        if( !empty( $disliked_terms ) ){
            $tax_query[] = array(
                'taxonomies' => $tasty_tags,
                'field'      => 'term_id',
                'terms'      => array_unique( $disliked_terms ),
                'operator'   => 'NOT IN'
            );
        }

        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 6,
            'orderby'        => 'rand',
            'post_status'    => 'publish',
            'tax_query'      => $tax_query,
            'post__not_in'   => $chose_post_ids // Exclude previously swiped posts
        );

        // Fetch posts
        $query = new WP_Query( $args );

        // Prepare response
        $posts = [];

        if ( $query->have_posts() ) {
            $posts = array_map( function( $post ) {
                return array(
                    'id'             => $post->ID,
                    'title'          => get_the_title( $post->ID ),
                    'featured_image' => get_the_post_thumbnail_url( $post->ID, 'full' ),
                );
            }, $query->posts );
        }
        
        return new WP_REST_Response( $posts, 200 );
    

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

        if( empty( $user_identifier ) || $column_name ){
            return array();
        }

        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->prefix}user_choices WHERE $column_name = %d",
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
    private function get_liked_post_ids( $user_identifier, $column_name, $provided_ids = [] ){
        //check args are provided
        if( empty( $user_identifier ) || $column_name ){
            return;
        }

        global $wpdb;

        $latest_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->prefix}{$this->user_choices_table}
                WHERE $column_name = %d AND choices = %s
                ORDER BY time DESC LIMIT 3",
                $user_identifier,
                'like'
            )
        );

        // Merge provided IDs and latest IDs
        return array_unique( array_merge( $provided_ids, $latest_ids ) );
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
    private function get_disliked_post_ids( $user_identifier, $column_name, $provided_ids = [] ){
        //check args are provided
        if( empty( $user_identifier ) || $column_name ){
            return;
        }

        global $wpdb;

        $latest_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->prefix}{$this->user_choices_table}
                WHERE $column_name = %d AND choices = %s
                ORDER BY time DESC LIMIT 3",
                $user_identifier,
                'dislike'
            )
        );

        // Merge provided IDs and latest IDs
        return array_unique( array_merge( $provided_ids, $latest_ids ) );
    }

    /**
     * Save user choices
     * Store user choices in database. This method also create app user on first swip if user is not logged in
     * 
     * @param   array     $request    Requested parameter
     * 
     * @since   1.0.0
     * @access  public
     */
    public function save_user_choices( $request ){

        global $wpdb;

        $user_id     = get_current_user_id();
        $session_id  = isset( $_COOKIE['app_user_session'] ) ? sanitize_text_field( $_COOKIE['app_user_session'] ) : null;
        $app_user_id = null;

        if( ! $user_id ){
            if( ! $session_id ){
                $session_id = uniqid( 'app_user_', true );

                //insert a new app user into the datatable
                $wpdb->insert(
                    $wpdb->prefix . $this->app_users_table,
                    array(
                        'email'         => $session_id . '@michaelgorski.de',
                        'session_id'    => $session_id,
                        'last_activity' => current_time( 'mysql' )
                    ),
                    array( '%s', '%s', '%s' )
                );

                //get the inserted app user id
                $app_user_id = $wpdb->insert_id;

                //Set the session ID as a cookie that expires in 30days

                setcookie( 'app_user_session', $session_id, time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );
            }else{
                $app_user_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}{$this->app_users_table} WHERE session_id = %s",
                        $session_id
                    )
                );
            }
        }

        // get post ID and choice from parameter request
        $post_id     = !empty( $request['post_id'] ) ? absint( $request['post_id'] ) : null;
        $choice      = !empty( $request['choice'] ) ? sanitize_text_field( $request['choice'] ) : null;

        //Validate choices
        if( in_array( $choice, array( 'like', 'dislike' ) ) || !$post_id ){
            return new WP_REST_Response( array( 'message' => 'Invalid Parameters' ), 400 );
        }

        //validate user
        if( ! $user_id || ! $app_user_id ){
            return new WP_REST_Response( array( 'message' => 'User not indentified' ), 400 );
        }

        //Insert or update user choices data in the database
        $wpdb->replace(
            $wpdb->prefix . $this->user_choices_table,
            array(
                'user_id'       => $user_id ?: null,
                'app_user_id'   => $app_user_id,
                'post_id'       => $post_id,
                'choice'        => $choice,
                'time'          => current_time( 'mysql' )
            ),
            array(
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
            )
        );

        return new WP_REST_Response( array( 
            'choice_success' => true, 
            'user_id'        => $user_id, 
            'app_user_id'    => $app_user_id, 
            'message', 'User choice recorded' 
        ), 200 );

    }
    

}