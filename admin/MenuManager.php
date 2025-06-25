<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Admin\PostTypeManager;

/**
 * Menu manager.
 *
 * Orders the admin menu post types and pages.
 */
class MenuManager {
    
    /**
     * An array of pages to register.
     * 
     * @var array
     */
    private $pages = [];
    
	/**
	 * Instance of the class.
	 *
	 * @var MenuManager The single instance of the class
	 * @since 1.0.0
	 */
	protected static $instance = null;
	
	/**
	 * MenuManager Instance.
	 *
	 * @since 1.0.0
	 * 
	 * @return MenuManager instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
    
    /**
     * Initializes the post type manager and the page manager.
     * 
     * @since 1.0.0
     */
    public function run() {
        
        // Get all admin pages
        $page_manager = AdminPageManager::instance();
        $pages = $page_manager::admin_pages();
        
        $posts = [];
        if ( class_exists( PostTypeManager::class ) ) {
            // Get all post types
            $post_manager = PostTypeManager::instance();
            $posts = $post_manager::post_types();
        }

        // Combine arrays and sort by key
        $grouped = self::sort_into_groups( $pages, $posts );
        
        $this->pages = $grouped;
        
        $this->register_all_pages();
    }
    
    /**
     * Registers all pages.
     * 
     * @since 1.0.0
     */
    public function register_all_pages() {
        // Loop through groups
        foreach ( $this->pages as $group => $page_args ) {
            // Loop through pages
            foreach ( $page_args as $key => $args ) {
                $this->register_page( $key, $args );
            }
        }
    }
    
    /**
     * Registers a page.
     * 
     * @since 1.0.0
     * 
     * @param   array   $args   The array of page args.
     */
    public function register_page( $key, $args ) {
        new AdminPage( $key, $args );
    }
    
    /**
     * Registers a post type.
     * 
     * @since 1.0.0
     * 
     * @param   string  $key    The post type slug.
     * @param   array   $args   The array of post type args.
     */
    public function register_post_type( $key, $args ) {
        new PostType( $key, $args );
        AdminColumns::get_instance( $key );
    }
    
    /**
     * Sorts arrays by key 'menu_order' while preserving array keys.
     * 
     * @since 1.0.0
     */
    private static function sort_by_menu_order( $array ) {
        // Comparison function for sorting by 'menu_order'
        uasort( $array, function( $a, $b ) {
            // Provide a default value if 'menu_order' does not exist
            $a_order = $a['buddyc_menu_order'] ?? null;
            $b_order = $b['buddyc_menu_order'] ?? null;
            
            return $a_order <=> $b_order;
        });
        
        // Return sorted array with keys preserved
        return $array;
    }
    
    /**
     * Sorts arrays into groups and orders the groups.
     * 
     * @since 1.0.0
     */
    private static function sort_into_groups( $pages, $posts ) {
        $grouped = [];
    
        // Combine the arrays
        $combined = array_merge( $pages, $posts );
    
        foreach ( $combined as $key => $args ) {
            // Define group
            $group = $args['group'] ?? 'primary';
            // Add to grouped array
            $grouped[$group][$key] = $args;
        }
    
        // Sort items within each group by menu order
        foreach ( $grouped as $group => $items ) {
            $grouped[$group] = self::sort_by_menu_order( $items );
        }
    
        // Define the desired order of groups
        $group_order = [
            'primary' => 1,
            'settings' => 3,
        ];
    
        // Assign a default order for other groups
        $default_order = 2;
    
        // Sort groups by the defined order
        uksort( $grouped, function( $a, $b ) use ( $group_order, $default_order ) {
            $order_a = $group_order[$a] ?? $default_order;
            $order_b = $group_order[$b] ?? $default_order;
            return $order_a <=> $order_b;
        });
    
        // Return the sorted and grouped array
        return $grouped;
    }
}