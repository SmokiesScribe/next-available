<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use NextAv\Admin\Settings;
/**
 * Retrieves the value of plugin settings.
 * 
 * @since 0.1.0
 * 
 * @param   string  $settings_group     The settings group to retrieve.
 * @param   string  $settings_key       Optional. The specific setting to retrieve.
 */
function nextav_get_setting( $settings_group, $settings_key = null ) {
    $settings = new Settings( $settings_group );
    return $settings->get_value( $settings_key );
}

/**
 * Retrieves the value of plugin settings.
 * 
 * @since 0.1.0
 * 
 * @param   string  $settings_group     The settings group.
 * @param   string  $settings_key       The specific setting field.
 * @param   mixed   $value              The value to set.
 */
function nextav_update_setting( $settings_group, $settings_key, $value ) {
    return Settings::update_value( $settings_group, $settings_key, $value );
}

/**
 * Retrieves colors from settings.
 * 
 * @since 0.1.0
 * 
 * @param   string  $type   The color type to retrieve.
 *                          Accepts 'primary', 'accent', and 'tertiary'.
 */
function nextav_color( $type ) {
    return nextav_get_setting('general', $type . '_color');
}