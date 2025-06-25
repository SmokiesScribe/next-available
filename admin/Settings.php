<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use NextAv\Admin\SettingsRouter;

/**
 * Organizes all settings data.
 * Retrieves and updates settings values.
 * 
 * @since 0.1.0
 */
class Settings {

    /**
     * The key of the settings group.
     * 
     * @var string
     */
    private $settings_group;

    /**
     * The name of the settings group.
     * 
     * @var string
     */
    private $settings_group_name;

    /**
     * Constructor method.
     * 
     * @since 1.0.25
     * 
     * @param   string  $settings_group     The key of the settings group.
     */
    public function __construct( $settings_group ) {
        $this->settings_group = $settings_group;
        $this->settings_group_name = self::settings_group_name( $settings_group );
        $this->define_hooks();
    }

    /**
     * Defines the hooks used to handle the settings cache.
     * 
     * @since 1.0.25
     */
    public function define_hooks() {
        // Pages added or deleted
        add_action( 'save_post', [$this, 'clear_cache_post_update'], 10, 1 );
        add_action( 'before_delete_post', [$this, 'clear_cache_post_delete'], 10, 1 );
        add_action( 'wp_trash_post', [$this, 'clear_cache_post_delete'], 10, 1 );

        // Post types added or deleted
        add_action( 'admin_init', [$this, 'clear_cache_post_types'], 30 );

        // Available components change
        add_action( 'nextav_components_updated', [$this, 'clear_cache_components'] );
    }

    /**
     * Builds the cache key for the option where the settings arrays are stored.
     * 
     * @since 1.0.25
     * 
     * @param   string  $settings_group The name of the settings group.
     * @param   string  $version        Optional. The plugin version.
     *                                  Defaults to the current version.
     */
    private static function cache_key( $settings_group, $version = null ) {
        $version = $version ?? NEXTAV_PLUGIN_VERSION;
        $formatted_version = str_replace( '.', '_', $version );
        return '_nextav_settings_cache_' . $settings_group . '_' . $formatted_version;
    }

    /**
     * Defines which settings groups to clear based on the post id.
     * 
     * @since 1.0.25
     * 
     * @param   string  $post_id   The ID of the post.
     * @return  array   An array of applicable settings group names.
     */
    private function get_settings_group_by_post_id( $post_id ) {
        // Get the post type
        $post_type = get_post_type( $post_id );

        // Define the settings group
        return match ( $post_type ) {
            'bp-member-type'    => ['sales', 'general'],
            'page'              => ['pages'],
            default             => null
        };
    }

    /**
     * Clears the pages cache when a page is updated.
     * 
     * @since 1.0.25
     * 
     * @param int      $post_id The ID of the post being updated.
     * @param WP_Post  $post    The post object.
     * @param bool     $update  Whether this is an update (true) or a new post (false). 
     */
    public function clear_cache_post_update( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;

        // Get current hook
        $hook = current_filter();

        // Check if already processed
        if ( ! $this->not_processed( $post_id, $hook ) ) return;

        // Get the settings groups
        $settings_group = $this->get_settings_group_by_post_id( $post_id );

        // Make sure it's a matching post type
        if ( ! empty( $settings_group ) ) {
            // Clear the cache
            $this->clear_cache( $settings_group );
        }
    }

    /**
     * Prevents duplicate processing when clearing cache on post updates and deletions.
     * 
     * @since 1.0.25
     * 
     * @param   int     $post_id    The ID of the post being processed.
     * @param   string  $hook       The name of the hook.
     * @return  bool    True if the post has not been processed, false if it has.
     */
    private function not_processed( $post_id, $hook ) {

        // Handle updated posts
        if ( $hook === 'save_post' ) {

            // Check if already processed
            if ( get_post_meta( $post_id, '_processed_once', true ) ) return false;

            // Mark as processed
            update_post_meta( $post_id, '_processed_once', true );
        }

        // Handle deleted posts
        if ( $hook === 'before_delete_post' || $hook === 'wp_trash_post' ) {

            // Check if already processed
            $last_processed = get_transient( 'nextav_last_trashed_page' );
            if ( $last_processed == $post_id ) return false;

            // Set transient to prevent repeated execution
            set_transient( 'nextav_last_trashed_page', $post_id, 30 ); // Expires in 30 seconds
        }

        // Five by five
        return true;
    }

    /**
     * Clears the pages cache when a page is trashed or deleted.
     * 
     * @since 1.0.25
     * 
     * @param   int $post_id The ID of the post being trashed or deleted.
     */
    public function clear_cache_post_delete( $post_id ) {
        if ( 'page' !== get_post_type( $post_id ) ) return;

        // Prevent duplicate execution
        $last_processed = get_transient( 'nextav_last_trashed_page' );
        if ( $last_processed == $post_id ) return;

        // Get the settings groups
        $settings_group = $this->get_settings_group_by_post_id( $post_id );

        // Make sure it's a matching post type
        if ( ! empty( $settings_group ) ) {
            // Clear the cache
            $this->clear_cache( $settings_group );
        }

        // Set transient to prevent repeated execution
        set_transient( 'nextav_last_trashed_page', $post_id, 30 ); // Expires in 30 seconds
    }

    /**
     * Clears the cache when post types are updated.
     * 
     * @since 1.0.25
     */
    public function clear_cache_post_types() {
        // Get post types
        $post_types = get_post_types();

        // Get cached post types
        $cached_post_types = get_option( '_nextav_cached_post_types', [] );

        // Compare current to cached
        if ( $post_types !== $cached_post_types ) {

            // Clear cache if they don't match
            $this->clear_cache( 'help' );

            // Update cache
            update_option( '_nextav_cached_post_types', $post_types );
        }
    }

    /**
     * Clears the cache when available components are updated.
     * 
     * @since 1.0.25
     */
    public function clear_cache_components() {
        $this->clear_cache( 'components' );
    }

    /**
     * Clears the cache for a settings group or for all
     * settings data if no group is defined.
     * 
     * @since 1.0.25
     * 
     * @param   string|array  $settings_group Optional. The name of the settings group to clear.
     *                                  Defaults to null and clears the cache for all groups.
     */
    private function clear_cache( $settings_group = null ) {
        if ( function_exists( 'nextav_version_cache' ) ) {
            $this->run_clear_cache($settings_group);
        } else {
            add_action( 'init', function() use ( $settings_group ) {
                $this->run_clear_cache( $settings_group );
            });
        }
    }

    /**
     * Clears the cache for a settings group or for all
     * settings data if no group is defined.
     * 
     * @since 1.0.25
     * 
     * @param   string|array  $settings_group Optional. The name of the settings group to clear.
     *                                  Defaults to null and clears the cache for all groups.
     */
    private function run_clear_cache( $settings_group = null ) {
        // Get versions
        $version_cache = nextav_version_cache();

        // Cast to array
        $settings_group_array = (array) $settings_group;

        // Delete current cache
        foreach ( $settings_group_array as $settings_group ) {
            delete_option( self::cache_key( $settings_group, $version_cache->curr_version ) );
        }
    }

   /**
     * Updates the cached settings data.
     * 
     * @since 1.0.25
     * 
     * @param   string  $settings_group  The name of the settings group.
     * @param   array   $data            The array of new data to cache.
     */
    private static function update_cache( $settings_group, $data ) {
        // Get versions
        $version_cache = nextav_version_cache();

        // Update current cache
        update_option( self::cache_key( $settings_group, $version_cache->curr_version ), $data );

        // Delete previous cache
        delete_option( self::cache_key( $settings_group, $version_cache->prev_version ) );
    }

	/**
	 * Retrieves all data for a settings group.
	 * 
	 * @since 0.1.0
	 */
	public function get_data() {
        $cache_key = self::cache_key( $this->settings_group );
        $data = get_option( $cache_key, false );

        $data = false;

        if ( false === $data ) {
            // If cache doesn't exist, retrieve the data and cache it
            $data = SettingsRouter::get_settings( $this->settings_group );

            // Cache the data
            self::update_cache( $this->settings_group, $data );
        }

        return $data;
	}
	
	/**
	 * Retrieves default values for a settings group.
	 * 
	 * @since 0.1.0
	 * 
     * @param   string  $settings_field     Optional. The name of the settings field.
	 */
	public function get_defaults( $settings_field = null ) {
        $defaults = SettingsRouter::get_defaults( $this->settings_group );
        return $settings_field ? ( $defaults[$settings_field] ?? '' ) : $defaults;
	}
    
    /**
     * Retrieves the current value of a setting.
     * 
     * @since 0.1.0
     * 
     * @param   string  $settings_field     Optional. The name of the settings field.
     * @return  mixed   The field value if defined or an array of all values in the settings group.
     */
    public function get_value( $settings_field = null ) {
            
        // No field is defined
        if ( ! $settings_field ) {
            // Get all settings group data
            $data = $this->get_data( $this->settings_group );
            $field_value = $data;

        // Field is defined
        } else {
            $curr_settings = get_option( $this->settings_group_name );
            // Fallback to defaults
            $field_value = $curr_settings[$settings_field] ?? $this->get_defaults( $settings_field ) ?? '';
        }

        // Return the cached or newly retrieved value
        return $field_value;
    }
    
    /**
     * Updates the value of a setting.
     * 
     * @since 0.1.0
     * 
     * @param   string  $field_key          The key of the settings field.
     * @param   mixed   $value              The new value for the setting.
     */
    public static function update_value( $settings_group, $field_key, $value ) {
        // Build settings group name
        $settings_group_name = self::settings_group_name( $settings_group );

        // Get existing setting
        $settings = get_option( $settings_group_name );

        // Set new value
        $settings[$field_key] = $value;

        // Update option
        update_option( $settings_group_name, $settings );
    }

    /**
     * Builds the settings group name from the key.
     * 
     * @since 1.0.25
     * 
     * @return  string  The settings group name.
     */
    private static function settings_group_name( $settings_group ) {
        return 'nextav_' . $settings_group . '_settings';
    }
}