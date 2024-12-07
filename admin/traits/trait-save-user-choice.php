<?php

/**
 * This trati is responsible for save user choices into the database
 * This trait is connected with Tasty_API_Endpoint class in admin/includes/class-tasty-api-endpoint.php
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
trait Save_User_Choice{

    private $user_choices_table = 'user_choices';
    private $app_users_table    = 'app_users';

    /**
     * Trait
     */
    use Tasty_Tags_Weight;

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
                $uniqueId = uniqid();
                $session_id = 'app_user_' . substr($uniqueId, -3);

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

        if( !$user_id && !$app_user_id ){
            return new WP_REST_Response( array( 'message' => 'User not defined', 'user_id' => $user_id, 'app_user_id' => $app_user_id, 'session_id' => $session_id  ), 400 );
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

        //Add tag weight scroe
        $this->save_tag_weight( $post_id, $choice, $user_id, $app_user_id );

        return new WP_REST_Response( array( 
            'choice_success' => true, 
            'user_id'        => $user_id, 
            'app_user_id'    => $app_user_id, 
            'message', 'User choice recorded' 
        ), 200 );

    }

    /**
     * Get tasty user choice 
     */
    public function get_user_choices( $request ){

        global $wpdb;

        $user_choice       = !empty( $request['choice'] ) ? $request['choice'] : 'like';
        $user_choice_table = $wpdb->prefix . $this->user_choices_table

        $get_choices_post_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT post_id FROM $user_choice_table WHERE choice = %s",
            $user_choice
        ) );
            
        $user_choice_data = [];

        if( count( $get_choices_post_ids ) > 0 ){
            foreach( $get_choices_post_ids as $id ){
                $user_choice_data[] = array(
                    'image' => get_
                );
            }
        }
    }
    

}