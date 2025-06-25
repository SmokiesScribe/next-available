<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleCal;
use Google_Client;

/**
 * Redirects to the Google auth screen.
 * 
 * @since 1.0.0
 */
function nextav_maybe_redirect_to_google_auth() {
    $auth = nextav_get_param( 'nextav-auth' );
    $code = nextav_get_param( 'code' );
    if ( $auth === 'connect' && current_user_can( 'manage_options' ) && ! $code ) {
        $cal = new GoogleCal;
        $redirect_url = $cal->auth_url();
        wp_redirect( $redirect_url );
        exit;
    }
}
add_action( 'admin_init', 'nextav_maybe_redirect_to_google_auth' );


/**
 * Handles the successful Google authorization.
 * 
 * @since 1.0.0
 */
function nextav_handle_google_auth() {

    // Exit if no code available or wrong page
    $page = nextav_get_param( 'page' );
    $code = nextav_get_param( 'code' );
    if ( $page !== 'nextav-integrations-settings' || ! $code ) return;

    // Verify state param
    if ( ! wp_verify_nonce( $_GET['state'] ?? '', 'nextav_google_auth' ) ) {
            wp_die( 'Invalid state parameter.' );
    }

    $client = new Google_Client();
    $client->setClientId( 'YOUR_CLIENT_ID' );
    $client->setClientSecret( 'YOUR_CLIENT_SECRET' );
    $client->setRedirectUri( 'YOUR_REGISTERED_REDIRECT_URI' );

    $token = $client->fetchAccessTokenWithAuthCode( $_GET['code'] );

    if ( isset( $token['error'] ) ) {
        wp_die( 'Token error: ' . esc_html( $token['error_description'] ?? $token['error'] ) );
    }

    // Store the tokens (in the options table, or user meta, etc.)
    update_option( 'nextav_google_tokens', $token );

    // Optionally redirect to a success message
    wp_safe_redirect( admin_url( 'admin.php?page=nextav-integrations-settings&connected=1' ) );
    exit;
}
add_action( 'admin_init', 'nextav_handle_google_auth' );
