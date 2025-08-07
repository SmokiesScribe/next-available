<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Config\AssetManager;
use NextAv\Admin\ConnectionNotice;

/**
 * Admin-specific functionality of the plugin.
 *
 * This class handles admin-specific functionality such as enqueueing styles and scripts.
 *
 * @since 0.1.0
 */
class Admin {
    
	/**
	 * Instance of the class.
	 *
	 * @var Admin The single instance of the class
	 * @since 0.1.0
	 */
	protected static $instance = null;
    	
	/**
	 * NextAv Admin Instance.
	 *
	 * @since 0.1.0
	 * @static
	 * @return Admin instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    /**
     * Constructor.
     *
     * Initializes the Admin class.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->includes();
    }

    /**
     * Include necessary files and register hooks and filters.
     *
     * @since 1.0.0
     */
    private function includes() {
        
        // Define hooks
        $this->define_hooks();
        
        // Require helpers
        $this->require_helpers();
        
        // Initialize
        Nav::run();
    }

    /**
     * Define hooks and filters.
     *
     * @since 1.0.0
     */
    private function define_hooks() {
        add_action('admin_init', [$this, 'enqueue_styles']);
        add_action('admin_init', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'color_picker']);
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_menu', [$this, 'admin_pages']);
    }
    
    /**
     * Loads color picker.
     * 
     * @since 0.1.0
     */
    public function color_picker() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('iris');
        wp_enqueue_script('wp-color-picker');
    }
    
    /**
     * Adds top-level menu.
     * 
     * @since 0.1.0
     */
    public function menu() {
        if ( ! function_exists( 'add_menu_page' ) ) {
            return;
        }

        // Add primary menu
        add_menu_page(
            'Next Available',
            'Next Available',
            'manage_options',
            'nextav-dashboard',
            'nextav_dashboard_content',
            'dashicons-nextav',
            5
        );
        add_submenu_page(
            'nextav-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'nextav-dashboard',
            'nextav_dashboard_content',
            0
        );

        // Add hidden menu
        add_menu_page(
            'Hidden Menu',
            'Hidden Menu',
            'manage_options',
            'nextav-hidden-menu',
            '' // no callback needed
        );

        // Remove the hidden menu item so it doesn't appear in the admin menu
        remove_menu_page('nextav-hidden-menu');
    }
    
    /**
     * Adds settings pages.
     * 
     * @since 0.1.0
     */
    public function admin_pages() {
        ( MenuManager::instance() )->run();
    }

    /**
     * Enqueue admin-specific stylesheets.
     *
     * @since 1.0.0
     */
    public function enqueue_styles() {
        $this->enqueue_assets('assets/css');
    }

    /**
     * Enqueue admin-specific JavaScript files.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        $this->enqueue_assets( 'assets/js', 'loading.js' );
        $this->enqueue_assets( 'assets/js' );
    }
    
    /**
     * Require helper functions.
     *
     * @since 1.0.0
     */
    public function require_helpers() {
        $this->enqueue_assets('helpers');
        $this->enqueue_assets('partials');
    }

    /**
     * Enqueues a single asset or all assets in a directory if no file name.
     *
     * @since 1.0.0
     *
     * @param   string  $dir        The directory path where assets are located.
     * @param   string  $file_name  Optional. The file name of the single asset to load.
     */
    private function enqueue_assets( $dir, $file_name = null ) {
        $asset_manager = new AssetManager( __FILE__, $dir, $file_name, true );
        $asset_manager->run();
    }
}
