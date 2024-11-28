<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
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
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tasty
 * @subpackage Tasty/includes
 * @author     Md Hiron Mia
 */
class Tasty {

    /**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tasty_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
     * @access   public
	 */
    public function __construct(){
        
        if( defined( 'TASTY_VERSION' ) ){
            $this->version = TASTY_VERSION;
        }else{
            $this->version = '1.0.0';
        }

        $this->plugin_name = 'tasty';

        //load all dependencies
        $this->load_dependencies();

		//Load textdomain hook
		$this->set_locale();

		//Load all hooks from admin
		$this->define_admin_hooks();

    }

    /**
     * Load the required dependencies for the plugin.
     * 
     * Created an instance of the loader which will be used to register the hooks with WordPress
     * 
     * @since   1.0.0
     * @access  private
     */
    private function load_dependencies(){

        //The class responsible for orchestrting the actions ad filters of the core plugin
        require_once TASTY_DIR . 'includes/class-tasty-loader.php';
		$this->loader = new Tasty_Loader();

        //The class responsible for defining internationalization functionality of the plugin
        require_once TASTY_DIR . 'includes/class-tasty-i18n.php';

		//The class responsible for load all herlper method that uses all over the tasty file
		require_once TASTY_DIR	. 'includes/class-tasty-helper.php';

		//The class responsible for creating necessary database table for tasty
		require_once TASTY_DIR . 'includes/class-tasty-database.php';

		// The class responsible for defining all actions that occur in the admin area
		require_once TASTY_DIR . 'admin/class-tasty-admin.php';

        
    }

    /**
     * Define the locale for this plugin for internationalization
     * 
     * Uses the Tasty_i18n class in order to set the domain and to register the hook with WordPress
     * 
     * @since   1.0.0
     * @access  private
     */
    private function set_locale(){
        
        $plugin_i18n = new Tasty_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

	/**
	 * Register all of the hooks relatedto the admin area functionality of this plugin
	 * 
	 * @since	1.0.0
	 * @access	private
	 */
	private function define_admin_hooks(){
		
		$tasty_admin 		= new Tasty_Admin( $this->get_plugin_name(), $this->get_version() );
		$tasty_taxonomy 	= new Tasty_Taxonomies();
		$tasty_custom_field = new Tasty_Custom_Meta_Field();
		
		$this->loader->add_action( 'admin_enqueue_scripts', $tasty_admin, 'enqueue_admin_scripts' );
		$this->loader->add_action( 'init', $tasty_taxonomy, 'custom_taxonomies', 10 );
		$this->loader->add_action( 'add_meta_boxes', $tasty_custom_field, 'custom_meta_box' );
		$this->loader->add_action( 'save_post', $tasty_custom_field, 'save_custom_post_meta' );

		/**
		 * add custom field to custom taxnomy
		 * This code used for temporary. It will reduced next time
		 */
		$this->loader->add_action( 'style_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'color_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'element-type_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'material_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'size_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'shape_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'features_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'room-type_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'price-category_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'mood-theme_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'usage_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );
		$this->loader->add_action( 'functionality_add_form_fields', $tasty_custom_field, 'add_custom_meta_for_tax' );

		/**
		 * Edit custom field form of custom taxonomy adding into hook
		 * his code used for temporary. It will reduced next time
		 */
		$this->loader->add_action( 'style_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'color_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'element-type_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'material_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'size_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'shape_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'features_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'room-type_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'price-category_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'mood-theme_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'usage_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );
		$this->loader->add_action( 'functionality_edit_form_fields', $tasty_custom_field, 'edit_custom_meta_for_tax' );

		/**
		 * Create custom field form adding into hook
		 * This code used for temporary. It will reduced next time
		 */
		$this->loader->add_action( 'created_style', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_color', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_element-type', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_material', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_size', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_shape', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_features', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_room-type', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_price-category', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_mood-theme', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_usage', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'created_functionality', $tasty_custom_field, 'save_custom_meta_for_tax' );

		/**
		 * udpate customer data adding into hook
		 * This code used for temporary. It will reduced next time
		 */
		$this->loader->add_action( 'edited_style', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_color', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_element-type', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_material', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_size', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_shape', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_features', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_room-type', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_price-category', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_mood-theme', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_usage', $tasty_custom_field, 'save_custom_meta_for_tax' );
		$this->loader->add_action( 'edited_functionality', $tasty_custom_field, 'save_custom_meta_for_tax' );

		/**
		 * ad customer column adding into hook
		 * This code used for temporary. It will reduced next time
		 */
		$this->loader->add_filter( 'manage_edit-style_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-color_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-element-type_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-material_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-size_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-shape_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-features_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-room-type_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-price-category_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-mood-theme_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-usage_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );
		$this->loader->add_filter( 'manage_edit-functionality_columns', $tasty_custom_field, 'add_custom_meta_in_tax_column' );

		/**
		 * ad customer column adding into hook
		 * This code used for temporary. It will reduced next time
		 */
		$this->loader->add_filter( 'manage_style_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_color_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_element-type_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_material_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_size_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_shape_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_features_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_room-type_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_price-category_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_mood-theme_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_usage_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
		$this->loader->add_filter( 'manage_functionality_custom_column', $tasty_custom_field, 'show_column_value_in_tax', 10, 3 );
	}

    /**
     * Run the loader to execute all of the hooks with WordPress
     * 
     * @since   1.0.0
     * @access  public
     */
    public function run(){
        $this->loader->run();
    }

    /**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tasty_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}