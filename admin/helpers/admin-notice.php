<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use NextAv\Admin\AdminNotice;
use NextAv\Admin\ConnectionNotice;

/**
 * Builds an admin notice.
 * 
 * @since 0.1.0
 * 
 * @param   array   $args {
 *     An array of arguments for building the admin notice.
 * 
 *     @type    string  $repair_link        The link to the repair page.
 *     @type    string  $repair_link_text   Optional. The link text.
 *                                          Defaults to 'Repair'.
 *     @type    string  $message            The message to display in the notice.
 *     @type    bool    $dismissable        Optional. Whether the notice should be dismissable.
 *                                          Defaults to false.
 *     @type    string  $color              Optional. The color of the notice.
 *                                          Accepts 'green', 'blue', 'orange', 'red'.
 *                                          Defaults to blue.
 * }
 */
function nextav_admin_notice( $args ) {
    new AdminNotice( $args );
}

/**
 * Adds a message to the connection notice.
 * 
 * @since 1.0.0
 * 
 * @param   string|array  $message    Single or array of messages to add to the notice.
 */
function nextav_add_connection_notice( $message ) {
    $messages = (array) $message;
    new ConnectionNotice( $messages );
}

/**
 * Checks for an error message in the url params.
 * 
 * @since 1.0.0
 */
function nextav_add_url_connection_notice() {
    $error = nextav_get_param( 'nextav_error' );
    if ( $error ) {
        nextav_add_connection_notice( $error );
    }
}
add_action( 'admin_init', 'nextav_add_url_connection_notice' );