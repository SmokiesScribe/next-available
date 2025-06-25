<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handles version-specific data. 
 * 
 * Caches data for retrieval and clears the data when the version is updated.
 *
 * @since 1.0.25
 */
class VersionCache {

    /**
     * The single instance of the class.
     * 
     * @var VersionCache
     */
    private static $instance = null;


    /**
     * The current plugin version.
     * 
     * @var string
     */
    public $curr_version;

    /**
     * The previous plugin version.
     * 
     * @var string
     */
    public $prev_version;

    /**
     * Returns the single instance of the class.
     * 
     * @since 1.0.25
     *
     * @return Singleton
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor  method.
     * 
     * @since 1.0.25
     */
    private function __construct() {
        if ( ! defined( 'NEXTAV_PLUGIN_VERSION' ) ) return;
        $this->get_versions();
        $this->update_versions();
    }

    /**
     * Retrieves the versions.
     * 
     * @since 1.0.25
     */
    private function get_versions() {
        $this->curr_version = get_option( 'nextav_curr_version' );
        $this->prev_version = get_option( 'nextav_prev_version' );
    }

    /**
     * Updates the versions.
     * 
     * @since 1.0.25
     */
    private function update_versions() {
        // Check if plugin has been updated
        if ( NEXTAV_PLUGIN_VERSION !== $this->curr_version ) {

            // Define versions
            $old_version = $this->curr_version;
            $new_version = NEXTAV_PLUGIN_VERSION;

            // Update options
            update_option( 'nextav_prev_version', $old_version );
            update_option( 'nextav_curr_version', $new_version );

            // Update object
            $this->prev_version = $old_version;
            $this->curr_version = $new_version;

            /**
             * Fires on the transition to a new BuddyClients plugin version.
             *
             * @since 1.0.25
             *
             * @param string  $old_version The previous plugin version.
             * @param string  $new_version The new plugin version.
             */
            do_action( 'nextav_version_updated', $old_version, $new_version );
        }
    }
}