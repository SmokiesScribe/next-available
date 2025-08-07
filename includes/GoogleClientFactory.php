<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use \GriffinVendor\Google\Client as Google_Client;

/**
 * Creates instances of the Google Client class.
 * 
 * @since 1.0.0
 */
class GoogleClientFactory {

    /**
     * The client ID.
     * 
     * @var string
     */
    protected $client_id;

    /**
     * The client secret.
     * 
     * @var string
     */
    protected $client_secret;

    /**
     * The redirect uri.
     * 
     * @var string
     */
    protected $redirect_uri;

    /**
     * The google scopes.
     * 
     * @var array
     */
    protected $scopes = [];

    /**
     * The access type.
     * 
     * @var string
     */
    protected $access_type = 'offline';

    /**
     * The prompt.
     * 
     * @var string
     */
    protected $prompt = 'consent';

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     * 
     * @param   $args {
     *     An array of optional args for creating the Client instance.
     * 
     *     @type    string  $client_id      The client ID.
     *     @type    string  $client_secret  The client secret.
     *     @type    string  $redirect_uri   The redirect uri.
     *     @type    array   $scopes         An array of scopes.
     * }
     */
    public function __construct( $args = [] ) {
        $this->client_id     = $args['client_id'] ?? '';
        $this->client_secret = $args['client_secret'] ?? '';
        $this->redirect_uri  = $args['redirect_uri'] ?? '';
        $this->scopes        = isset( $args['scopes'] ) ? (array) $args['scopes'] : [];
    }

    /**
     * Returns a configured Google_Client instance.
     * 
     * @since 1.0.0
     *
     * @param array|null $tokens Optional access and refresh tokens to set.
     * @return Google_Client
     */
    public function make( array $tokens = null ) {
        $client = new Google_Client();

        if ( ! empty( $this->client_id ) ) {
            $client->setClientId( $this->client_id );
        }

        if ( ! empty( $this->client_secret ) ) {
            $client->setClientSecret( $this->client_secret );
        }

        if ( ! empty( $this->redirect_uri ) ) {
            $client->setRedirectUri( $this->redirect_uri );
        }

        if ( ! empty( $this->access_type ) ) {
            $client->setAccessType( $this->access_type );
        }

        if ( ! empty( $this->prompt ) ) {
            $client->setPrompt( $this->prompt );
        }

        if ( ! empty( $this->scopes ) ) {
            $client->setScopes( $this->scopes );
        }

        if ( $this->validate_tokens( $tokens ) ) {
            $client->setAccessToken( $tokens );
        }

        return $client;
    }

    /**
     * Validates the token data to ensure it's not corrupted or malformed. 
     * 
     * @since 1.0.0
     * 
     * @param   array   $tokens The array of token data.
     * @return  bool    True if valid, false if not.
     */
    private function validate_tokens( $tokens = null ) {
        if ( ! $tokens ) return false;

        // Check for correct structure
        $valid = ( is_array( $tokens ) && isset( $tokens['access_token'] ) );

        // Set admin notice for invalid data
        if ( ! $valid ) {
            $message = __( 'Invalid or corrupt token data. Please reconnect Google.', 'next-available' );
            nextav_add_connection_notice( $message );
        }

        // Return bool
        return $valid;
    }
}