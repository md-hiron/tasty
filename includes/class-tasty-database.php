<?php

/**
 * The file that create mysql custom data table during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Tasty
 * @subpackage Tasty/includes
 */

// If this file is called directly, abort
if( ! defined( 'WPINC' ) ){
    die;
}

/**
 * Mysql custom datatable calss
 *
 * This class used to create necessary mysql datatable for tasty
 * 
 *
 * @since      1.0.0
 * @package    Tasty
 * @subpackage Tasty/includes
 * @author     Md Hiron Mia
 */
class Tasty_Database_Tables{
    
    //define table names
    private static $user_choices_table = 'user_choices';
    private static $app_user_table     = 'app_users';
    private static $tag_weight_table   = 'tag_weight';

    /**
     * Create Custom table for tasty
     */
    public static function create_tables(){

        global $wpdb;

        //Get table names with WordPress prefix
        $user_choices_table = $wpdb->prefix . self::$user_choices_table;
        $app_user_table     = $wpdb->prefix . self::$app_user_table;
        $tag_weight_table   = $wpdb->prefix . self::$tag_weight_table;
        $charset_collate    = $wpdb->get_charset_collate();

        //check if the tables already exist
        if( self::table_exists( $user_choices_table ) && self::table_exists( $app_user_table ) && self::table_exists( $tag_weight_table ) ){
            return;
        }

        //SQL query for creating user_choices table
        $sql_user_choices = "
            CREATE TABLE $user_choices_table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) UNSIGNED DEFAULT NULL,
                app_user_id BIGINT(20) DEFAULT NULL,
                post_id BIGINT(20) UNSIGNED NOT NULL,
                choice VARCHAR(50) NOT NULL,
                time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY post_id (post_id),
                KEY app_user_id (app_user_id),
                CONSTRAINT FK_user_id FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE,
                CONSTRAINT FK_post_id FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
            ) $charset_collate
        "; 

        //SQL qurey for creating app_users table
        $sql_app_users = "
            CREATE TABLE $app_user_table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL,
                session_id VARCHAR(255) NOT NULL,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE (session_id)
            ) $charset_collate
        ";

        $sql_tag_weight = "
            CREATE TABLE $tag_weight_table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                tag_id INT(20) NOT NULL,
                user_id BIGINT(20) DEFAULT NULL,
                app_user_id BIGINT(20) DEFAULT NULL,
                tag_weight_score INT(20) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate
        ";

        //Include the wordpress upgrade function
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Suppress any output from dbDelta
        ob_start(); // Start output buffering

        //execute SQL queries
        dbDelta( $sql_user_choices );
        dbDelta( $sql_app_users );
        dbDelta( $sql_tag_weight );

        ob_end_clean(); // Clear the buffer
    }

    /**
     * Check if table exists in the WordPress database
     * 
     * @param   string  $table_name The name of the table to check
     * 
     * @version 1.0.0
     * @access  private
     */
    private static function table_exists( $table_name ){
        
        global $wpdb;

        //check if the table exists by quering for it's name
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
        
        return $table_exists === $table_name;
    }
}