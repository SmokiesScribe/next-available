<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleAuthManager;
use GriffinVendor\Google\Client as Google_Client;

/**
 * Initiates connection and disconnection of the Google integration.
 * 
 * @since 1.0.0
 */
class GoogleConnectionManager {

    /**
     * The GoogleAuthManager instance.
     * 
     * @var GoogleAuthManager
     */
    private $auth_manager;

    /**
     * The connect status.
     * 'connect' or 'disconnect'.
     * 
     * @var string
     */
    private $connect;

    /**
     * The code returned from Google.
     * 
     * @var string
     */
    private $code;

    /**
     * The success of the connection.
     * 
     * @var string
     */
    private $success;

    /**
     * The code returned from the proxy server.
     * 
     * @var string
     */
    private $auth_code;

    /**
     * The current page.
     * 
     * @var string
     */
    private $page;

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->auth_manager = new GoogleAuthManager();
        $this->connect = nextav_get_param( 'nextav_google_connect' );
        $this->code = nextav_get_param( 'code' );
        $this->success = nextav_get_param( 'auth' );
        $this->auth_code = nextav_get_param( 'auth_code' );
        $this->page = nextav_get_param( 'page' );
    }

    /**
     * Defines the hooks.
     * 
     * @since 1.0.0
     */
    public function init() {
        add_action( 'admin_init', [$this, 'route_actions' ] );
    }

    /**
     * Routes actions based on url params.
     * 
     * @since 1.0.0
     */
    public function route_actions() {

        // Make sure user has authority to connect
        if ( current_user_can( 'manage_options' ) ) {

            // Connect Google integration
            if ( $this->connect === 'connect' && ! $this->code ) {
                return $this->connect_google();
            }

            // Disonnect Google integration
            if ( $this->connect === 'disconnect' ) {
                return $this->disconnect_google();
            }
        }

        // Make sure we're on integrations settings page
        if ( $this->page === 'nextav-integrations-settings' ) {

            // Check for failed connection
            if ( $this->success === 'error' ) {
                $reason = nextav_get_param( 'reason' );
                $error = __( 'Connection failed: ', 'next-available' ) . ( $reason ?? __( 'Unknown error.', 'next-available' ) );
                nextav_add_connection_notice( $error );
            }

            // Check for successful connection
            if ( nextav_get_param( 'connected' ) == '1' ) {
                $this->success_admin_notice();
            }

            // Handle auth code from proxy
            if ( $this->success === 'success' && $this->auth_code ) {
                $this->handle_proxy_auth();
            }

            // Handle code from Google
            if ( $this->code ) {
                $this->handle_google_code();
            }
        }        
    }

    /**
     * Redirects to the Google auth screen.
     * 
     * @since 1.0.0
     */
    public function connect_google() {

        // User confirmed, final redirect
        if ( isset( $_POST['confirm_connect'] ) ) {
            $redirect_url = $this->auth_manager->auth_url();
            wp_redirect( $redirect_url );
            exit;

        } else {

            // Output confirmation form
            $confirmation_form = $this->build_confirmation_form();
            $allowed_html = self::confirmation_form_html();
            echo wp_kses( $confirmation_form, $allowed_html );
        }
    }

    /**
     * Defines the confirmation form content.
     * 
     * @since 1.0.0
     * 
     * @return  array   {
     *     An array of content items.
     * 
     *     @type    string  $title      The form title. 
     *     @type    string  $content    The form content.
     * }
     */
    private function confirmation_form_content() {

        // Check whether we're bypassing the proxy server
        if ( $this->auth_manager->bypass_proxy ) {

            // Bypassing proxy content
            $title = __( 'Bypassing Proxy Server', 'next-available' );
            $content = sprintf(
                '<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
                __( 'You are connecting directly to Google Cloud API.', 'next-available' ),
                __( 'Please check your credentials or opt to use the proxy server in the Advanced Settings.', 'next-available' ),
                __( 'If you are sure your credentials are correct, continue to Google authorization.', 'next-available' )
            );

        } else {

            // Using proxy content
            $title = __( 'Connect Your Google Calendar', 'next-available' );
            $content = sprintf(
                '<p>%1$s</p><p>%2$s</p>',
                __( 'Please confirm that you want to connect your Google Calendar using our secure proxy server.', 'next-available' ),
                __( 'If you prefer not to use the proxy, you can configure your own Google credentials in the Advanced Settings.', 'next-available' )
            );
        }

        // Return array
        return ['title' => $title, 'content' => $content];
    }

    /**
     * Builds the confirmation form. 
     * 
     * @since 1.0.0
     */
    private function build_confirmation_form() {

        $form_content = $this->confirmation_form_content();
        $title = $form_content['title'];
        $content = $form_content['content'];

        // Build confirmation form
        ob_start();
        ?>
        <div class="wrap nextav-proxy-confirm-fullscreen">
            <div class="wrap nextav-proxy-confirm-wrap">
                <h1><?php echo esc_html( $title ); ?></h1>
                <?php echo wp_kses_post( $content ); ?>
                <form method="post">
                    <?php wp_nonce_field( 'nextav_connect_google_action', 'nextav_connect_google_nonce' ); ?>
                    <input type="submit" name="confirm_connect" class="button button-primary" value="Yes, Connect" />
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=nextav-integrations-settings' ) ); ?>" class="button">Cancel</a>
                </form>
            </div>
        </div>
        <?php
        $form = ob_get_clean();

        // Return html
        return $form;

    }

    /**
     * Defines the allowed html for the confirmation form.
     * 
     * @since 1.0.0
     */
    private static function confirmation_form_html() {
        return [
            'div' => [
                'class' => [],
            ],
            'h1' => [],
            'p' => [],
            'form' => [
                'method' => [],
            ],
            'input' => [
                'type' => [],
                'name' => [],
                'class' => [],
                'value' => [],
            ],
            'a' => [
                'href' => [],
                'class' => [],
            ],
            'span' => [], // in case WordPress inserts nonce as a span element
        ];
    }

    /**
     * Handles an auth code sent from the proxy server.
     * 
     * @since 1.0.0
     */
    public function handle_proxy_auth() {

        // Exchange auth code for token
        $data = $this->auth_for_token( $this->auth_code );
        $success = $data['success'] ?? false;
        $error = $data['error'] ?? null;

        // Add error message and redirect
        $this->admin_redirect( $success, $error );
    }

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
    private function auth_for_token( $auth_code ) {

        // Initialize return data to failure
        $data = ['success' => 0];

        // Exchange auth code for token on proxy server
        $response = wp_remote_post( NEXTAV_PROXY_URL, [
            'headers' => [
                'Nextav-API-Key' => NEXTAV_PROXY_API_KEY,
            ],
            'body' => [
                'plugin_name' => NEXTAV_PLUGIN_NAME,
                'action'      => 'google_exchange_auth',
                'auth_code'   => $auth_code,
                // Remove api_key from here, since it's now in headers
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

        error_log( "OAuth token fetch response body: " . $body );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $data['error'] = ("OAuth token fetch JSON decode error: " . json_last_error_msg());
            return $data;
        }

        if ( isset( $token_data['error'] ) ) {
            // Handle error returned from proxy server
            $data['error'] = ("OAuth token fetch error: " . $token_data['error'] . ' - ' . ($token_data['message'] ?? ''));
            return $data;
        }

        // Check for malformed token data
        if ( ! is_array( $token_data ) || ! isset( $token_data['access_token'] ) ) {
            $data['error'] = __( 'Invalid or corrupt token data. Please reconnect Google.', 'next-available' );
            return $data;
        }

        // Store new token data
        nextav_update_token_data( $token_data );
        nextav_update_refresh_token( $token_data['refresh_token'] ?? null );

        // Return success data
        return [
            'success'   => 1,
            'token'     => $token_data
        ];
    }

    /**
     * Handles the successful Google authorization directly from Google.
     * Used when bypassing proxy server.
     * 
     * @since 1.0.0
     */
    public function handle_google_code() {

        // Init
        $success = false;
        $error = false;

        // Verify state
        if ( $this->verify_state() ) {

            // Fetch token
            $token_data = $this->fetch_token();

            // Validate token
            $error = $this->get_token_error( $token_data );

            // Five by five
            if ( ! $error ) {

                // Store new token data
                nextav_update_token_data( $token_data );

                // Success
                $success = true;
            }

        // Invalid state param
        } else {
            $error = __( 'Invalid state parameter received from Google.', 'next-available' );
        }

        // Add error message and redirect
        $this->admin_redirect( $success, $error );
    }

    /**
     * Redirects to success or failure message and sets admin notice.
     * 
     * @since 1.0.0
     * 
     * @param   bool        $success    Whether the operation was successful.
     * @param   string|bool The error message or false if no error.
     */
    private function admin_redirect( $success, $error = false ) {

        // Add error message if it exists
        $error_msg = '';
        if ( $error ) {
            $error_msg = urlencode( $error );
        }

        // Build the base redirect URL
        $redirect_url = admin_url( "admin.php?page=nextav-integrations-settings&connected=$success" );

        // Append the error param only if there is an error
        if ( $error_msg ) {
            $redirect_url = add_query_arg( 'nextav_error', $error_msg, $redirect_url );
        }

        // Redirect
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Verifies the state param from Google.
     * 
     * @since 1.0.0
     */
    private function verify_state() {
        // Get the state param
        $state = nextav_get_param( 'state' );
        if ( ! $state ) return false;

        // Verify nonce
        return wp_verify_nonce( $state ?? '', 'nextav_google_auth' );
    }

    /**
     * Fetches the token from Google.
     * 
     * @since 1.0.0
     */
    private function fetch_token() {
        // Create Google Client instance
        $args = [
            'client_id'     => $this->auth_manager->client_id,
            'client_secret' => $this->auth_manager->client_secret,
            'redirect_uri'  => $this->auth_manager->connect_url,
        ];
        $google_client = new GoogleClientFactory( $args );

        // Fetch access token
        $token_data = $google_client->fetchAccessTokenWithAuthCode( $this->code );
        return $token_data;        
    }

    /**
     * Retrieves the error message from the token data. 
     * 
     * @since 1.0.0
     * 
     * @param   array       $token_data The array of token data.
     * @return  string|bool Error message or false if no error.
     */
    private function get_token_error( $token_data ) {
        // Init
        $error = false;

        // Check for error
        if ( isset( $token_data['error'] ) && ! empty( $token_data['error'] ) ) {

            // Retrieve error from data
            $token_error = $token_data['error_description'] ?? $token_data['error'] ?? __( 'Unknown error', 'next-available' );

            // Format error message
            $error = sprintf(
                /* translators: %s: the error message */
                __( 'Token error: %s', 'next-available' ),
                esc_html( $token_error )
            );
        }

        // Return string or false
        return $error;
    }

    /**
     * Disconnects from the connected Google account.
     * 
     * @since 1.0.0
     */
    public function disconnect_google() {

        // Remove token data
        nextav_delete_tokens();

        // Redirect to a success message
        wp_safe_redirect( admin_url( 'admin.php?page=nextav-integrations-settings&disconnected=1' ) );
        exit;
    }

    /**
     * Outputs the admin notice on successful connection.
     * 
     * @since 1.0.0
     */
    private function success_admin_notice() {
        $args = [
            'message'       => __( 'Google Calendar connected successfully!', 'next-available' ),
            'color'         => 'green',
            'dismissable'   => true
        ];
        nextav_admin_notice( $args );        
    }
}