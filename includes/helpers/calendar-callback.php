<?php
use NextAv\Includes\DisplayCalendar;
/**
 * Generates the updated calendar html on month change.
 * Callback for the javascript function.
 * 
 * @since 1.0.0
 */
function nextav_get_calendar_callback() {

    // Ensure the request is from an authenticated user
    if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
        wp_send_json_error(__('Invalid request', 'next-available'));
        wp_die();
    }

    // Verify nonce
    $valid = nextav_verify_ajax_nonce( 'calendar_nav' );
    if ( ! $valid ) return;

    // Sanitize input parameters
    $month = isset( $_POST['month'] ) ? strval( $_POST['month'] ) : null;
    if ( ! $month ) {
        wp_send_json_error(__('No month provided', 'next-available'));
        wp_die(); 
    }

    // Get attributes from $_POST, assume JSON string or array
    $atts_json = isset( $_POST['atts']) ? wp_unslash( $_POST['atts'] ) : '{}'; // If JSON string
    $atts = json_decode( $atts_json, true );

    // Update month in atts
    $atts['month'] = $month;

    // Generate your calendar HTML
    $calendar = new DisplayCalendar;
    $calendar_html = $calendar->display( $atts );

    // Return the HTML (echo and exit)
    wp_send_json_success( $calendar_html );
    wp_die();
}
add_action( 'wp_ajax_nextav_get_calendar', 'nextav_get_calendar_callback' );
add_action( 'wp_ajax_nopriv_nextav_get_calendar', 'nextav_get_calendar_callback' );