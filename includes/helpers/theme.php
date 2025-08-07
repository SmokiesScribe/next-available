<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use NextAv\Includes\Icon;

/**
 * Check for BuddyBoss theme.
 * 
 * @since 0.1.0
 * 
 * @return bool
 */
function nextav_buddyboss_theme() {
    if (function_exists('buddyboss_theme_register_required_plugins')) {
        return true;
    } else {
        return false;
    }
}

/**
 * Outputs icon html.
 * 
 * @since 1.0.20
 * 
 * @param   string  $key    The identifying key of the icon.
 * @param   string  $color  Optional. The color of the icon.
 *                          Accepts 'blue', 'black', 'green', 'red', or 'gray'.
 * 
 * @return  string  The icon html.
 */
function nextav_icon( $key, $color = null ) {
    $icon = new Icon( $key, $color );
    return $icon->html;
}

/**
 * Outputs a string of icon classes
 * 
 * @since 1.0.25
 * 
 * @param   string  $key    The identifying key of the icon.
 * @param   string  $color  Optional. The color of the icon.
 *                          Accepts 'blue', 'black', 'green', 'red', or 'gray'.
 * 
 * @return  string  The string of icon classes.
 */
function nextav_icon_class( $key, $color = null ) {
    $icon = new Icon( $key, $color );
    return $icon->class;
}