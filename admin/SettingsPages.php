<?php
namespace BuddyClients\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BuddyClients\Admin\PageManager;

/**
 * Defines the Pages settings.
 *
 * @since 1.0.25
 */
class SettingsPages {

    /**
     * Defines default Pages settings.
     * 
     * @since 1.0.25
     */
    public static function defaults() {
            // Get page list
            $pages = PageManager::pages();

            // Initialize
            $defaults = [];
            
            // Loop through page types
            foreach ($pages as $page_type => $pages) {
                foreach ($pages as $page_key => $page_data) {
                    $defaults[$page_key] = null;
                }
            }
            
            return $defaults;
    }
    
   /**
     * Defines the Pages settings.
     * core pages
     * @since 1.0.25
     */
    public static function settings( $defaults = null ) {
        // Get page list
        $pages = PageManager::pages();
        
        // Initialize settings array
        $settings = [];
        
        // Loop through page types
        foreach ($pages as $page_type => $pages) {
            $settings[$page_type] = [
                'title' => sprintf(
                    /* translators: %s: page type (e.g., 'Service', 'Product') */
                    __('%s Pages', 'buddyclients-free'),
                    esc_html( ucfirst( $page_type ) ),
                ),
                'description' => sprintf(
                    /* translators: %s: page type (e.g., 'service', 'product') */
                    __('Choose or create your %s pages.', 'buddyclients-free'),
                    esc_html( $page_type )
                ),
            ];
            
            foreach ($pages as $page_key => $page_data) {
                $settings[$page_type]['fields'][$page_key] = [
                    'label' => $page_data['label'],
                    'type' => 'page',
                    'options' => self::page_options(),
                    'post_title' => $page_data['post_title'] ?? '',
                    'post_content' => $page_data['post_content'] ?? '',
                    'required_component' => $page_data['required_component'] ?? null,
                    'description' => $page_data['description'] ?? '',
                ];
            }
        }
        return $settings;
    }
    
    /**
     * Builds a list of all page options.
     * 
     * @since 0.1.0
     */
    private static function page_options() {
        // Initialize
        $options = [ '' => __( 'Select a Page', 'buddyclients-free' ) ];
        
        // Retrieve all pages
        $all_pages = get_pages(); // wp function
        
        // Loop through pages
        foreach ($all_pages as $single_page) {
            // Add to array
            $options[$single_page->ID] = $single_page->post_title;
        }
        return $options;
    }
}