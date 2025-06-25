<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handles the Google Calendar integration.
 * 
 * @since 1.0.0
 */
class GoogleCal {
    
    /**
     * The client ID.
     * 
     * @var string
     */
    public $client_id;

    /**
     * The redirect url.
     * 
     * @var string
     */
    public $redirect_url;

    /**
     * The Google scopes.
     * 
     * @var string
     */
    private $google_scopes;
    
    /**
     * Constructor method.
     * 
     * @since 1.0.0
     * 
     * @param   string  $url    Optional. The URL to modify.
     *                          Defaults to the current URL.
     */
    public function __construct( $url = null ) {
        $this->client_id = '519747815181-6strs25hfugngfo2t78009hnf6a84k48.apps.googleusercontent.com';        
        $this->redirect_url = $this->redirect_url();
        $this->google_scopes = 'https://www.googleapis.com/auth/calendar.readonly';
    }

    /**
     * Builds the redirect url.
     * 
     * Creates the url where the user can connect with Google Calendar API.
     * This url must be added to the list of authorized redirect URLs.
     * 
     * @since 1.0.0
     */
    private function redirect_url() {
        // Define url components
        $admin_url = admin_url();
        $page_param = 'page=nextav-integrations-settings';
        $action_param = 'nextav-auth=connect';

        // TESTING
        $admin_url = 'https://f060-2606-83c0-7801-9b00-2d47-482f-17e4-3979.ngrok-free.app/wp-admin/';

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
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_url,
            'response_type' => 'code',
            'scope' => $this->google_scopes,
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
            'state' => wp_create_nonce( 'nextav_google_auth' ),
        ]);
    }
}