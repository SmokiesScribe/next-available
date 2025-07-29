<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin page manager.
 *
 * Organizes all admin pages.
 */
class AdminPageManager {

    /**
     * Instance of the class.
     *
     * @var AdminPageManager|null The single instance of the class.
     * @since 0.1.0
     */
    protected static $instance = null;

    /**
     * Retrieves the instance of the class.
     *
     * @since 0.1.0
     * @static
     * @return AdminPageManager The instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Creates all admin pages.
     * 
     * @since 0.1.0
     */
    public function run() {
        foreach (self::admin_pages() as $key => $data) {
            new AdminPage( $key, $data );
        }
    }

    /**
     * Retrieves an array of admin pages.
     * 
     * @since 0.1.0
     * @return array An associative array of admin pages info.
     */
    public static function admin_pages() {
        $pages = [
            // Settings Pages
            'general' => [
                'key' => 'general',
                'settings' => true,
                'title' => __('Settings', 'next-available'),
                'parent_slug' => 'nextav-dashboard',
                'nextav_menu_order' => 26,
                'group' => 'settings'
            ],
            'integrations' => [
                'key' => 'integrations',
                'settings' => true,
                'title' => __('Integrations', 'next-available'),
                'parent_slug' => null,
            ],
            'advanced' => [
                'key' => 'advanced',
                'settings' => true,
                'title' => __('Advanced', 'next-available'),
                'parent_slug' => null,
            ],

            // Other Pages
            //'email_log' => [
            //    'key' => 'email-log',
            //    'title' => __('Email Log', 'next-available'),
            //    'settings' => false,
            //    'parent_slug' => null,
            //    'callable' => 'nextav_email_log_content',
            //]
        ];

        /**
         * Filters the admin pages.
         *
         * @since 0.3.4
         *
         * @param array $pages An array of admin pages info.
         * @return array Modified array of admin pages info.
         */
        $pages = apply_filters('nextav_admin_pages', $pages);

        return $pages;
    }
}
