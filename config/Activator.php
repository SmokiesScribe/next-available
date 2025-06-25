<?php
namespace NextAv\Config;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Admin\PageManager;

/**
 * Activation methods.
 * 
 * Initializes actions the first time the plugin is activated.
 * 
 * @since 1.0.0
 */
class Activator {

    /**
     * Handles first-time plugin activation.
     *
     * @since 1.0.0
     */
    public static function activate() {

        // Check if the plugin has been activated before
        if (get_option('nextav_activated') !== 'yes') {

            // Set the activation flag
            update_option('nextav_activated', 'yes');

            /**
             * Fires when BuddyClients plugin is activated for the first time.
             * 
             * @since 1.0.0
             */
            do_action('nextav_activated');
        }
    }
}