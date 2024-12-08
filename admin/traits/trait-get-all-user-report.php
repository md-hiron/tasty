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
trait Get_All_User_Report{

    private $user_choices_table = 'user_choices';
    private $app_users_table    = 'app_users';

    /**
     * Save user choices
     * Store user choices in database. This method also create app user on first swip if user is not logged in
     * 
     * @param   array     $request    Requested parameter
     * 
     * @since   1.0.0
     * @access  public
     */
    public function get_all_user_report( $request ){

        $all_tasty_users = Tasty_Helper::get_all_wp_and_app_users();

        $user_report = [];

        if( is_array( $all_tasty_users ) && count( $all_tasty_users ) > 0 ){
            foreach( $all_tasty_users as $user ){
                $user_report[] = array(
                    'email'              => $this->get_tasty_user_data( $user, 'email' ),
                    'like_share'         => $this->get_tasty_user_data( $user, 'like_share' ),
                    'total_interactions' => $this->get_tasty_user_data( $user, 'total_interactions' ),
                    'last_interaction'   => $this->get_tasty_user_data( $user, 'last_interaction' ),
                );
            }
        }

        $search_term = $request['search'];

        if( ! empty( $search_term ) ){
            $user_report = array_filter( $user_report, function( $user ){
                return strpos( $user['email'], $search_term ) !== false;
            } );
        }

        return new WP_REST_Response( $user_report, 200 );

    }

    /**
     * Get tasty user data by user id
     * 
     * @param   array    $user_indentifier   An array with user id and user type (wp or app user)
     * @param   string   $target_info               Targeted user info that we want to show
     * 
     * @return  string  User data according to user identifier and target data
     * 
     * @since   1.0.0
     * @access  private
     */
    private function get_tasty_user_data( $user_indentifier, $target_info ){

        // Check arguments are provided
        if( ! is_array( $user_indentifier ) || empty( $target_info ) ){
            return;
        }

        global $wpdb;

        $app_user_table    = $wpdb->prefix . 'app_users';
        $user_choice_table = $wpdb->prefix . 'user_choices';

        $user_column       = $user_indentifier['user_type'] === 'wp_user' ? 'user_id' : 'app_user_id';

        // Get user email
        if( 'email' === $target_info ){
           return Tasty_Helper::get_user_email_by_id( $user_indentifier['user_type'], $user_indentifier['user_id'] );
        }


        //user choices
        $user_choices = $wpdb->get_col( 
            $wpdb->prepare(
                "SELECT choice FROM $user_choice_table
                WHERE $user_column = %d",
                $user_indentifier['user_id']
            )
        );

        //end function if there is no interaction
        if( count( $user_choices ) === 0 ){
            return;
        }

        //Get Like share
        if( 'like_share' === $target_info ){

			$total_choices = count( $user_choices );
			$total_likes = count(array_filter( $user_choices, function( $item ){
				return $item === 'like';
			} ));

            if( $total_choices && $total_likes ){
                return number_format( ( $total_likes / $total_choices ) * 100, 1) . '%';
            }
			//return like share in percentage

            return '0.00%';
			
        }

        //Get total Interaction
        if( 'total_interactions' == $target_info ){
            return count( $user_choices );
        }

        //Get last intercations timem
        if( 'last_interaction' == $target_info ){
            $last_date = $wpdb->get_var( $wpdb->prepare(
                "SELECT Max(time) FROM $user_choice_table WHERE $user_column = %d",
                $user_indentifier['user_id']

            ) );

            return (new DateTime($last_date))->format('d M, Y');
        }


    }
    

}