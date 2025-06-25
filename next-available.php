<?php
/**
 * Plugin Name: Next Available
 * Plugin URI:  
 * Description: Display your next available date from your Google Calendar. Perfect for freelancers.
 * Author:      Victoria Griffin
 * Author URI:  https://victoriagriffin.com/
 * Version:     1.0.0
 * Text Domain: next-available
 * Domain Path: /languages/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define constants
if ( ! defined( 'NEXTAV_PLUGIN_VERSION' ) ) {
	define( 'NEXTAV_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'NEXTAV_PLUGIN_FILE' ) ) {
	define( 'NEXTAV_PLUGIN_FILE', __FILE__ );
}

require_once( plugin_dir_path(__FILE__) . 'NextAv-class.php' );

/**
 * Returns the one true NextAv Instance.
 * 
 * @since 1.0.0
 *
 * @return NextAv|null The one true NextAv Instance.
 */
function nextav() {
    if ( function_exists( 'nextav' ) && class_exists( 'NextAv' ) ) {
	    return NextAv::instance();
    }
}

/**
 * Initializes the plugin.
 * 
 * Let's go!
 * 
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'nextav' );