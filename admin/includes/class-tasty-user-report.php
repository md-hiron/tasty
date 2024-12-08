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
     * Preferences Bathroom element page content
     */
    public function preferences_bathroom_element(){
       
        $all_user = Tasty_Helper::get_all_wp_and_app_users();
        $options  = [];
        if( is_array( $all_user ) ){
            foreach( $all_user as $user ){

                $user_email = Tasty_Helper::get_user_email_by_id( $user['user_type'], $user['user_id'] );

                if( 'wp_user' === $user['user_type'] ){
                   $options[] = array(
                        'user'  => 'wp_user_' . $user['user_id'],
                        'email' => $user_email
                   );
                }

                if( 'app_user' === $user['user_type'] ){
                    $options[] = array(
                        'user'  => 'app_user_' . $user['user_id'],
                        'email' => $user_email,
                    );
                }
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e( 'Tasty User Report', 'tasty' );?></h1>
            <div class="user-dropdown-area">
                <label for="preference-user"><?php _e( 'Preference By User:' ); ?></label>
                <select name="preference-user" id="preference-user">
                    <?php
                        foreach( $options as $option ){
                            printf( '<option value="%s">%s</option>', $option['user'], $option['email'] );
                        }
                    ?>
                </select>
                
            </div>
            <div class="preference-tab-area">
                <div class="preference-tab-btn-area">
                    <button class="preference-tab-btn active-element" data-element="sink"><?php _e( 'Sink', 'tasty' );?></button>
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