<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleCal;
use GriffinVendor\Google\Client;
use GriffinVendor\Google\Service\Calendar;

/**
 * Redirects to the Google auth screen.
 * 
 * @since 1.0.0
 */
function nextav_maybe_redirect_to_google_auth() {
    $auth = nextav_get_param( 'nextav-auth' );
    $code = nextav_get_param( 'code' );

    if ( $auth === 'connect' && current_user_can( 'manage_options' ) && ! $code ) {

        // Init GoogleCal
        $cal = new GoogleCal;

        if ( isset($_POST['confirm_connect']) ) {
            // User confirmed, redirect now
            $redirect_url = $cal->auth_url();
            wp_redirect( $redirect_url );
            exit;

        } else {

            if ( $cal->bypass_proxy ) {
                // Bypassing Proxy
                $title = __( 'Bypassing Proxy Server', 'next-available' );
                $content = sprintf(
                    '<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
                    __( 'You are connecting directly to Google Cloud API.', 'next-available' ),
                    __( 'Please check your credentials or opt to use the proxy server in the Advanced Settings.', 'next-available' ),
                    __( 'If you are sure your credentials are correct, continue to Google authorization.', 'next-available' )
                );
            } else {

                // Using proxy
                $title = __( 'Connect Your Google Calendar', 'next-available' );
                $content = sprintf(
                    '<p>%1$s</p><p>%2$s</p>',
                    __( 'Please confirm that you want to connect your Google Calendar using our secure proxy server.', 'next-available' ),
                    __( 'If you prefer not to use the proxy, you can configure your own Google credentials in the Advanced Settings.', 'next-available' )
                );
            }



            // Show confirmation form
            ?>
            <div class="wrap nextav-proxy-confirm-fullscreen">
                <div class="wrap nextav-proxy-confirm-wrap">
                    <h1><?php echo $title; ?></h1>
                    <?php echo $content; ?>
                    <form method="post">
                        <?php wp_nonce_field( 'nextav_connect_google_action', 'nextav_connect_google_nonce' ); ?>
                        <input type="submit" name="confirm_connect" class="button button-primary" value="Yes, Connect" />
                        <a href="<?php echo esc_url(admin_url( 'admin.php?page=nextav-integrations-settings' )); ?>" class="button">Cancel</a>
                    </form>
                </div>
            </div>
            <?php
        }
    }
}
add_action( 'admin_init', 'nextav_maybe_redirect_to_google_auth' );


/**
 * Handles the successful Google authorization.
 * 
 * @since 1.0.0
 */
function nextav_handle_google_auth() {    

    if ( ! class_exists( Client::class ) ) {
        return;
    }

    // Get the url params
    $success = nextav_get_param( 'auth' );
    $auth_code = nextav_get_param( 'auth_code' );

    // Exit if not success callback or wrong page
    $page = nextav_get_param( 'page' );
    if ( $page !== 'nextav-integrations-settings' || $success !== 'success' || ! $auth_code ) return;

    $data = nextav_auth_for_token( $auth_code );
    $success = $data['success'] ?? false;
    $error = $data['error'] ?? __( 'Unknown error', 'next-available' );
    $error = urlencode( $error );

    // Redirect to a success message
    wp_safe_redirect( admin_url( "admin.php?page=nextav-integrations-settings&connected=$success&error=$error" ) );
    exit;
}
add_action( 'admin_init', 'nextav_handle_google_auth' );

/**
 * Handles the successful Google authorization directly from Google.
 * 
 * @since 1.0.0
 */
function nextav_handle_google_auth_bypass_proxy() {    

    if ( ! class_exists( Client::class ) ) {
        return;
    }

    // Init
    $error = '';
    $success = false;

    // Exit if no code available or wrong page
    $page = nextav_get_param( 'page' );
    $code = nextav_get_param( 'code' );
    if ( $page !== 'nextav-integrations-settings' || ! $code ) return;

    // Verify state param
    if ( ! wp_verify_nonce( $_GET['state'] ?? '', 'nextav_google_auth' ) ) {
            wp_die( 'Invalid state parameter.' ); // bypass during development to allow ngrok to work
            $success = false;
            $error = esc_html__( 'Invalid state parameter.', 'next-available' );
    }

    // New GoogleCal
    $cal = new GoogleCal;

    $client = new Client();
    $client->setClientId( $cal->get_client_id() );
    $client->setClientSecret( $cal->get_client_secret() );
    $client->setRedirectUri( $cal->redirect_url );

    $token = $client->fetchAccessTokenWithAuthCode( $code );

    // Token error
    if ( isset( $token['error'] ) ) {
        $success = false;
        $error_message = $token['error_description'] ?? $token['error'] ?? __( 'Unknown error', 'next-available' );
        $error = sprintf(
            /* translators: %s: the error message */
            __( 'Token error: %s', 'next-available' ),
            esc_html( $error_message )
        );

    // Token five by five
    } else {
        // Store the tokens (in the options table, or user meta, etc.)
        update_option( 'nextav_google_tokens', $token );
        $success = true;
    }

    // Redirect to success or failure message
    $error = urlencode( $error );
    wp_safe_redirect( admin_url( "admin.php?page=nextav-integrations-settings&connected=$success&error=$error" ) );
    exit;
}
add_action( 'admin_init', 'nextav_handle_google_auth_bypass_proxy' );

/**
 * Exchanges the auth code for a token on proxy site.
 * 
 * Stores the token and returns bool.
 * 
 * @since 1.0.0
 * 
 * @param   string  $auth_code  The auth code to exchange.
 * @return  array {
 *     An array of success or failure data. 
 * 
 *     @type    bool    $success    True on success, false on failure.
 *     @type    string  $error      Error message, if applicable.
 *     @type    array   $token      Array of token data.
 * }
 */
function nextav_auth_for_token( $auth_code ) {

    // Initialize return data to failure
    $data = ['success' => 0];

    // Exchange auth code for token on proxy server
    $remote_url = 'https://buddyclients.com/wp-content/plugins/oauth-proxy/exchange-auth.php';
    $response = wp_remote_post( $remote_url, [
        'body' => [
            'auth_code'     => $auth_code,
            'auth_token'    => 'speak_friend', // to prevent abuse
        ],
    ]);

    if ( is_wp_error( $response ) ) {
        // Handle WP error (network, timeout, etc)
        $error_message = $response->get_error_message();
        // Maybe show admin notice or log error
        $data['error'] = ("OAuth token fetch failed: $error_message");
        return $data;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( $status_code !== 200 ) {
        // Handle HTTP error codes
        $data['error'] = ("OAuth token fetch HTTP error: $status_code - $body");
        return $data;
    }

    $token_data = json_decode( $body, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $data['error'] = ("OAuth token fetch JSON decode error: " . json_last_error_msg());
        return $data;
    }

    if ( isset( $token_data['error'] ) ) {
        // Handle error returned from proxy server
        $data['error'] = ("OAuth token fetch error: " . $token_data['error'] . ' - ' . ($token_data['message'] ?? ''));
        return $data;
    }

    // Success! $token_data contains:
    // - access_token
    // - refresh_token
    // - expires_in, etc

    // Now you can store these tokens as needed, e.g. update_option(), user meta, transient, etc.
    update_option( 'nextav_google_tokens', $token_data );
    update_option( 'nextav_google_refresh_token', $token_data['refresh_token'] );

    return [
        'success'   => 1,
        'token'     => $token_data
    ];
}

/**
 * Outputs a message on Google authorization success or failure.
 * 
 * @since 1.0.0
 */
function nextav_google_auth_admin_notice() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'nextav-integrations-settings' ) {
        return;
    }

    if ( isset( $_GET['connected'] ) ) {
        if ( $_GET['connected'] == '1' ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( 'Google Calendar connected successfully!', 'next-available' ); ?></p>
            </div>
            <?php
        } elseif ( $_GET['connected'] == '0' ) {
            $error = $_GET['error'] ?? '';
            $error = urldecode( $error );
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html__( 'Failed to connect Google Calendar.', 'next-available' ) . ' ' . esc_html( $error ); ?></p>
            </div>
            <?php
        }
    }
}
add_action( 'admin_notices', 'nextav_google_auth_admin_notice' );

/**
 * Disconnects from the connected Google account.
 * 
 * @since 1.0.0
 */
function nextav_maybe_disconnect_google_auth() {
    $auth = nextav_get_param( 'nextav-auth' );
    if ( $auth === 'disconnect' && current_user_can( 'manage_options' ) ) {
        $cal = new GoogleCal;
        $cal->disconnect();

        // Redirect to a success message
        wp_safe_redirect( admin_url( 'admin.php?page=nextav-integrations-settings&disconnected=1' ) );
        exit;
    }
}
add_action( 'admin_init', 'nextav_maybe_disconnect_google_auth' );