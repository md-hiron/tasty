<?php

/**
 * This class is responsible for creating user report of tasty
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
 * creating Usre Report admin page
 *
 * @package    Tasty
 * @subpackage Tasty/admin
 * @author     Md Hiron Mia
 */
class Tasty_User_Report{


    /**
     * Admin menu page and subpages for user report
     * This method will be hooked in /includes/class-tasty.php
     * 
     * @since   1.0.0
     * @access  public
     */
    public function user_report_pages(){
        
        //Add main menu page for user report
        add_menu_page(
            __( 'Tasty User Report', 'tasty' ),
            __( 'Tasty User Report', 'tasty' ),
            'manage_options',
            'tasty-user-report',
            array( $this, 'user_report_primary_page' ),
            'dashicons-analytics'
        );

        //Add submenu page for usr specific report
        add_submenu_page( 
            'tasty-user-report', 
            __( 'Preferences by Bathroom Element', 'tasty' ),
            __( 'Preferences', 'tasty' ),
            'manage_options',
            'preferences-bathroom-element', 
            array( $this, 'preferences_bathroom_element' )
        );
    }

    /**
     * User Primary Report Page
     * 
     * @since   1.0.0
     * @access  public
     */
    public function user_report_primary_page(){
        ?>
        <div class="wrap">
            <h1><?php _e( 'Tasty User Report', 'tasty' );?></h1>
            <table class="tasty-user-report-table">
                <thead>
                    <tr>
                        <th><?php _e( 'User Email', 'tasty' );?></th>
                        <th><?php _e( 'Like Share (%)', 'tasty' );?></th>
                        <th><?php _e( 'Total Interactions', 'tasty' );?></th>
                        <th><?php _e( 'Date of Last Interaction', 'tasty' );?></th>
                    </tr>
                </thead>
                <tbody id="all_user_report"></tbody>
            </table>
            <p id="all-user-loading"><?php _e( 'Loading...', 'tasty' );?></p>
        </div>
        <?php
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
     * @access  public
     */
    public function get_tasty_user_data( $user_indentifier, $target_info ){

        // Check arguments are provided
        if( ! is_array( $user_indentifier ) || empty( $target_info ) ){
            return;
        }

        global $wpdb;

        $app_user_table    = $wpdb->prefix . 'app_users';
        $user_choice_table = $wpdb->prefix . 'user_choices';

        $user_column       = $user_indentifier['user_type'];

        // Get user email
        if( 'email' === $target_info ){
            if( 'user_id' === $user_column ){
                $user = get_userdata( $user_indentifier['user_id'] );

                return $user->user_email;
            }else{
                return $wpdb->get_var( $wpdb->prepare( 
                    "SELECT email FROM $app_user_table where id = %d",
                    $user_indentifier['user_id']
                 ) );
            }
        }


        //user choices
        $user_choices = $wpdb->get_col( 
            $wpdb->prepare(
                "SELECT choice FROM $user_choice_table
                WHERE $user_column = %d",
                $user_indentifier['user_id']
            )
        );

        //Get Like share
        if( 'like_share' === $target_info ){

			$total_choices = count( $user_choices );
			$total_likes = count(array_filter( $user_choices, function( $item ){
				return $item === 'like';
			} ));

			//return like share in percentage
			return number_format( ( $total_likes / $total_choices ) * 100, 1) . '%';
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

            return (new DateTime($date))->format('d M, Y');
        }


    }

    /**
     * Preferences Bathroom element page content
     */
    public function preferences_bathroom_element(){
        ?>
        <div class="wrap">
            <h1><?php _e( 'Tasty User Report', 'tasty' );?></h1>
            <div class="user-dropdown-area">
                <label for="preference-user"><?php _e( 'Preference By User:' ); ?></label>
                <select name="preference-user" id="preference-user">
                    <option value=""><?php _e( 'Global Preference' );?></option>
                </select>
            </div>
            <div class="preference-tab-area">
                <div class="preference-tab-btn-area">
                    <button class="preference-tab-btn" data-element="sink"><?php _e( 'Sink', 'tasty' );?></button>
                    <button class="preference-tab-btn" data-element="bathtub"><?php _e( 'Bathtub', 'tasty' );?></button>
                    <button class="preference-tab-btn" data-element="shower"><?php _e( 'Shower', 'tasty' );?></button>
                </div>
                <div class="preference-tab-content-area">
                    <div id="preference-tab-content">
                        <p><?php _e( 'Loading...', 'tasty' );?></p>
                    </div>
                </div>
            </div>
            
        </div>
        <?php
    }

    

}