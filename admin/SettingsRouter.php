<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Admin\SettingsIntegrations;

/**
 * Directs to the correct settings class.
 * 
 * @since 1.0.25
 */
class SettingsRouter {

    /**
     * Defines the settings classes.
     * 
     * @since 1.0.25
     */
    private static function classes() {
        $classes = [
            'general'       => SettingsGeneral::class,
            'integrations'  => SettingsIntegrations::class,
        ];

        /**
         * Filters the Settings classes.
         *
         * @since 1.0.25
         *
         * @param array  $callbacks An array of classes keyed by settings group.
         */
        $classes = apply_filters( 'nextav_settings_classes', $classes );

        return $classes;
    }

    /**
     * Retrieves the class for a settings group.
     * 
     * @since 1.0.25
     * 
     * @param string $settings_group The name of the settings group.
     */
    private static function get_class( $settings_group ) {
        // Define classes
        $classes = self::classes();
        
        // Get class for settings group
        $class = $classes[$settings_group] ?? null;

        // Make sure the class exists
        if ( is_string( $class ) && class_exists( $class ) ) {
            return $class;
        }
    }

    /**
     * Retrieves the settings data for a settings group.
     * 
     * @since 1.0.25
     * 
     * @param string $settings_group The name of the settings group.
     * 
     * @return array The settings data.
     */
    public static function get_settings( $settings_group ) {
        return self::get_data( $settings_group, 'settings' );
    }

    /**
     * Retrieves the default settings data for a settings group.
     * 
     * @since 1.0.25
     * 
     * @param string $settings_group The name of the settings group.
     * 
     * @return array The settings data.
     */
    public static function get_defaults( $settings_group ) {
        return self::get_data( $settings_group, 'defaults' );
    }

    /**
     * Directs to the correct settings class based on the given group.
     * 
     * @since 1.0.25
     * 
     * @param   string      $settings_group     The name of the settings group.
     * @param   string      $method             The name of the static method to call.
     * 
     * @return  array       The settings data or default data for the group.
     */
    public static function get_data( $settings_group, $method ) {

        // Get the settings class
        $class = self::get_class( $settings_group );

        // Build the callable
        $callable = [$class, $method];

        // Retrieve the data        
        if ( is_callable( $callable ) ) {     
            return $callable();
        }
    }
}