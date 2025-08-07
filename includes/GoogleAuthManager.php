<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handles authentication of the Google Calendar integration.
 * 
 * @since 1.0.0
 */
class GoogleAuthManager {

    /**
     * The redirect url to connect.
     * 
     * @var string
     */
    public $connect_url;

    /**
     * The redirect url to disconnect.
     * 
     * @var string
     */
    public $disconnect_url;

    /**
     * Whether the user is bypassing the proxy server.
     * 
     * @var bool
     */
    public $bypass_proxy;

    /**
     * The user's client ID.
     * Used when bypassing proxy server.
     * 
     * @var string
     */
    private $client_id;

    /**
     * The user's client secret.
     * Used when bypassing proxy server.
     * 
     * @var string
     */
    private $client_secret;

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->connect_url = $this->redirect_url( 'connect' );
        $this->disconnect_url = $this->redirect_url( 'disconnect' );
        $this->bypass_proxy = self::bypass_proxy();

        if ( $this->bypass_proxy ) {
            $this->client_id = nextav_get_setting( 'advanced', 'user_google_client_id' );
            $this->client_secret = nextav_get_setting( 'advanced', 'user_google_client_secret' );
        }
    }

    /**
     * Checks whether to bypass the proxy server.
     * 
     * @since 1.0.0
     * 
     * @return  bool    True if bypassing proxy, false if not.
     */
    public static function bypass_proxy() {
        $bypass = nextav_get_setting( 'advanced', 'bypass' );
        return ( $bypass === 'yes' );
    }

    /**
     * Builds the redirect url.
     * 
     * Creates the url where the user can connect with Google Calendar API.
     * This url must be added to the list of authorized redirect URLs.
     * 
     * @since 1.0.0
     * 
     * @param   string  $action Connect or disconnect.
     */
    public function redirect_url( $action = 'connect' ) {

        // Define url components
        $admin_url = admin_url();
        $page_param = 'page=nextav-integrations-settings';
        $action_param = 'nextav_google_connect=' . $action;

        return sprintf(
            '%1$sadmin.php?%2$s&%3$s',
            $admin_url,
            $page_param,
            $action_param
        );
    }

    /**
     * Defines the auth url.
     * 
     * @since 1.0.0
     */
    public function auth_url() {

        // Bypass proxy
        if ( $this->bypass_proxy ) {
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $this->client_id,
                'redirect_uri' => $this->connect_url,
                'response_type' => 'code',
                'scope' => 'https://www.googleapis.com/auth/calendar.readonly',
                'access_type' => 'offline',
                'include_granted_scopes' => 'true',
                'prompt' => 'consent',
                'state' => wp_create_nonce( 'nextav_google_auth' ),
            ]);
        }
        
        // Use proxy server
        $params = [
            'state'       => $this->build_state(),
            'api_key'     => NEXTAV_PROXY_API_KEY,
            'plugin_name' => NEXTAV_PLUGIN_NAME,
            'action'      => 'google_connect',
        ];

        return NEXTAV_PROXY_URL . '?' . http_build_query($params);
    }

    /**
     * Builds the state.
     * 
     * @since 1.0.0
     */
    private function build_state() {
        $return_url = admin_url( 'admin.php?page=nextav-integrations-settings' );
        $nonce = wp_create_nonce( 'nextav_google_auth' );

        $state_array = [
            'return_url' => $return_url,
            'nonce'      => $nonce,
        ];

        $state = urlencode( base64_encode( json_encode( $state_array ) ) );

        return $state;
    }
}