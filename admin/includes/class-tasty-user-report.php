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
                <tbody>
                    <?php 
                        $all_tasty_users = Tasty_Helper::get_all_wp_and_app_users();
                        if( is_array( $all_tasty_users ) && count( $all_tasty_users ) > 0 ){
                            foreach( $all_tasty_users as $user ){
                                ?>
                                <tr>
                                    <td><?php echo $this->get_tasty_user_data( $user, 'email' ); ?></td>
                                    <td><?php echo $this->get_tasty_user_data( $user, 'like_share' ); ?></td>
                                    <td><?php echo $this->get_tasty_user_data( $user, 'total_interactions' ); ?></td>
                                    <td><?php echo $this->get_tasty_user_data( $user, 'last_interaction' ); ?></td>
                                </tr>
                                <?php
                            }
                        }
                    ?>
                    
                </tbody>
            </table>
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

    

}