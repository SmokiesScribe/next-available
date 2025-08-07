<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleClientFactory;

/**
 * Handles storage and retrieval of Google tokens.
 * 
 * @since 1.0.0
 */
class GoogleTokenManager {

    /**
     * Whether the user is bypassing the proxy server.
     * 
     * @var bool
     */
    public $bypass_proxy;

    /**
     * The stored refresh token.
     * Used to refresh the access token.
     * 
     * @var string
     */
    private $refresh_token;

    /**
     * The stored access token.
     * 
     * @var string
     */
    private $access_token;

    /**
     * The array of token data.
     * 
     * @var array
     */
    public $token_data;

    /**
     * The settings key for token data. 
     * 
     * @var string
     */
    private static $token_data_key = 'nextav_google_token_data';

    /**
     * The settings key for the refresh token. 
     * 
     * @var string
     */
    private static $refresh_token_key = 'nextav_google_refresh_token';

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->bypass_proxy = nextav_bypass_proxy();
        $this->refresh_token = $this->get_refresh_token();
        $this->token_data = $this->get_token_data();
    }

    /**
     * Updates the stored token data.
     * 
     * @since 1.0.0
     * 
     * @param   array   $token_data The array of new token data.
     */
    public static function update_token_data( $token_data ) {
        return update_option(  self::$token_data_key, $token_data );
    }

    /**
     * Updates the stored refresh token.
     * 
     * @since 1.0.0
     * 
     * @param   string   $refresh_token The new refresh token.
     */
    public static function update_refresh_token( $refresh_token ) {
        return update_option(  self::$refresh_token_key, $refresh_token );
    }

    /**
     * Deletes the stored token data and refresh token.
     * 
     * @since 1.0.0
     */
    public static function delete_tokens() {
        $keys = [self::$refresh_token_key, self::$token_data_key];
        foreach ( $keys as $key ) {
            delete_option( $key );
        }
    }

    /**
     * Retrieves the stored refresh token.
     * 
     * @since 1.0.0
     */
    public function get_refresh_token() {
        return get_option( self::$refresh_token_key );
    }

    /**
     * Retrieves the stored token data.
     * 
     * @since 1.0.0
     */
    public function get_token_data() {
        // Get the stored token data
        $token_data = get_option(  self::$token_data_key );

        // Return empty array if not connected
        if ( ! $token_data ) return [];

        // Validate the token
        $validated_token = $this->validate_token( $token_data );

        // Return the validated token
        return $validated_token;
    }

    /**
     * Validates the token.
     * 
     * @since 1.0.0
     * 
     * @param   array   $token_data The token data to validate.
     * @return  ?array  The valid array of token data or null.
     */
    private function validate_token( $token_data ) {

        // New Client instance
        $google_client = new GoogleClientFactory();
        $client = $google_client->make( $token_data );

        // Check if access token is expired
        if ( $client->isAccessTokenExpired() ) {
            $new_token = $this->refresh_access_token( $client );

            // Update the token and return array
            if ( $new_token ) {
                self::update_token_data( $new_token );
                return $new_token;
            }
        }

        // Return the valid token
        return $token_data;
    }

    /**
     * Refreshes the access token.
     * 
     * @since 1.0.0
     * 
     * @param   Google_Client   $client The Google_Client instance.
     */
    private function refresh_access_token( $client ) {

        // Refresh the token by proxy or google
        if ( $this->bypass_proxy ) {
            $token_data = $this->refresh_token_via_google( $this->refresh_token, $client );
        } else {
            $token_data = $this->refresh_token_via_server( $this->refresh_token );
        }

        // Return data
        return $token_data;
    }

    /**
     * Fetches token via proxy server.
     * 
     * Advanced users can connect to their own Google app to avoid phoning home.
     * 
     * @since 1.0.0
     * 
     * @param   string  $refresh_token  The Google refresh token.
     */
    private function refresh_token_via_server( $refresh_token ) {
        $response = wp_remote_post( NEXTAV_PROXY_URL, [
            'body' => [
                'plugin_name'   => NEXTAV_PLUGIN_NAME,
                'action'        => 'google_refresh_token',
                'refresh_token' => $refresh_token,
                'api_key'       => NEXTAV_PROXY_API_KEY, // to prevent abuse
            ],
        ]);

        // Check for wp error
        if ( is_wp_error( $response ) ) {
            return false;
        }

        // Decode response
        $data = json_decode(wp_remote_retrieve_body($response), true);

        // No access token available
        if ( ! isset( $data['access_token'] ) ) {
            return false;
        }
        
        // Return access token data
        return $data;
    }

    /**
     * Refreshes the access token directly with Google.
     * Used when bypassing the proxy server.
     * 
     * @since 1.0.0
     * 
     * @param   string  $refresh_token  The Google refresh token.
     * @param   Client  $client         The Google Client instance.
     */
    private function refresh_token_via_google( $refresh_token, $client ) {

        $client = 

        // Init Google Client
        $client->setClientId( $this->client_id );
        $client->setClientSecret( $this->client_secret );

        try {
            // Use fetchAccessTokenWithRefreshToken to get detailed response or error
            $token = $client->fetchAccessTokenWithRefreshToken( $refresh_token );

            // Check for errors in the response
            if ( isset( $token['error'] ) ) {
                // Google returned an error, include description if available
                $errorMsg = $token['error_description'] ?? $token['error'];
                throw new Exception('Google token refresh error: ' . $errorMsg);
            }

            // Check if access token is present
            if (empty($token) || !isset($token['access_token'])) {
                throw new Exception('No access token returned after refresh');
            }

            // Return the refreshed token
            return $token;

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'token_refresh_failed',
                'message' => $e->getMessage(),
                'debug_info' => $token ?? null,
            ]);
        }
    }

}