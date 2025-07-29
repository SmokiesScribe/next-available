<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use \DateTime;
use \DatePeriod;
use \DateInterval;

/**
 * Handles the Google Calendar integration.
 * 
 * @since 1.0.0
 */
class GoogleCal {

    /**
     * Whether the user is bypassing the proxy server.
     * 
     * @var bool
     */
    public $bypass_proxy;

    /**
     * The redirect url.
     * 
     * @var string
     */
    public $redirect_url;

    /**
     * The disconnect account url.
     * 
     * @var string
     */
    public $disconnect_url;

    /**
     * The stored refresh token.
     * 
     * @var string
     */
    private $refresh_token;

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
     * The google scopes.
     * Used when bypassing proxy server.
     * 
     * @var string
     */
    private $google_scopes = 'https://www.googleapis.com/auth/calendar.readonly';

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     * 
     * @param   string  $url    Optional. The URL to modify.
     *                          Defaults to the current URL.
     */
    public function __construct( $url = null ) {
        $this->refresh_token = get_option( 'nextav_google_refresh_token' );
        $this->redirect_url = $this->redirect_url( 'connect' );
        $this->disconnect_url = $this->redirect_url( 'disconnect' );

        // Get info to bypass proxy server
        $this->get_bypass_info();
    }

    /**
     * Retrieves info for bypassing the proxy server.
     * 
     * @since 1.0.0
     */
    private function get_bypass_info() {
        $bypass = nextav_get_setting( 'advanced', 'bypass' );
        $this->bypass_proxy = ( $bypass === 'yes' );

        if ( $this->bypass_proxy ) {
            $this->client_id = nextav_get_setting( 'advanced', 'user_google_client_id' );
            $this->client_secret = nextav_get_setting( 'advanced', 'user_google_client_secret' );
        }
    }

    /**
     * Retrieves the client ID.
     * 
     * @since 1.0.0
     */
    public function get_client_id() {
        return $this->client_id;
    }

    /**
     * Retrieves the client secret.
     * 
     * @since 1.0.0
     */
    public function get_client_secret() {
        return $this->client_secret;
    }

    /**
     * Disconnects the current Google account.
     * 
     * @since 1.0.0
     */
    public function disconnect() {
        delete_option( 'nextav_google_tokens' );
        nextav_update_setting( 'integrations', 'calendar_id', null );
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
        $action_param = 'nextav-auth=' . $action;

        return sprintf(
            '%1$sadmin.php?%2$s&%3$s',
            $admin_url,
            $page_param,
            $action_param
        );
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
                'redirect_uri' => $this->redirect_url,
                'response_type' => 'code',
                'scope' => $this->google_scopes,
                'access_type' => 'offline',
                'include_granted_scopes' => 'true',
                'prompt' => 'consent',
                'state' => wp_create_nonce( 'nextav_google_auth' ),
            ]);
        }

        // Use proxy server
        $state = $this->build_state();
        return "https://buddyclients.com/auth/google?state=$state";
    }

    /**
     * Builds the Google Client object.
     * 
     * @since 1.0.0
     * 
     * @return  Client  The Google Client object.
     */
    private function google_client() {

        // Get token
        $token = get_option('nextav_google_tokens');

        // Return empty array if not connected
        if ( ! $token ) {
            return [];
        }

        // New Client instance
        $client = new \GriffinVendor\Google\Client();
        $client->setAccessToken( $token );

        // Check if access token is expired
        if ( $client->isAccessTokenExpired() ) {

            // Make sure we have a refresh token
            if ( $this->refresh_token ) {

                // Request new token from proxy
                $new_tokens = $this->refresh_token_via_server( $this->refresh_token );

                    // New token received
                    if ( $new_tokens && isset( $new_tokens['access_token'] ) ) {
                        // Save updated tokens locally
                        update_option( 'nextav_google_tokens', $new_tokens );
                        $client->setAccessToken( $new_tokens );
                    } else {
                        wp_die('Failed to refresh token via server. Please reconnect.');
                    }
                } else {
                    wp_die('Refresh token missing, please reconnect.');
                }
            }
        
        return $client;
    }

    /**
     * Fetches token via proxy server.
     * 
     * Advanced users can connect to their own Google app to avoid phoning home.
     * 
     * @since 1.0.0
     */
    private function refresh_token_via_server( $refresh_token ) {
        $remote_url = 'https://buddyclients.com/wp-content/plugins/oauth-proxy/get-token.php';
        $response = wp_remote_post( $remote_url, [
            'body' => [
                'refresh_token' => $refresh_token,
                'auth_token'    => 'speak_friend', // to prevent abuse
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
     * Builds the Google Calendar Service object. 
     * 
     * @since 1.0.0
     * 
     * @return  Calendar    The Google Calendar object.
     */
    private function google_service() {
        $client = $this->google_client();
        if ( ! $client ) return;
        return new \GriffinVendor\Google\Service\Calendar( $client );
    }

    /**
     * Retrieves the list of calendars from the connected account.
     * 
     * @since 1.0.0
     * 
     * @return  array   An associative array of calendar ids and summaries.
     */
    public function calendar_list() {

        // Build Calendar Service object
        $service = $this->google_service();

        if ( ! $service || ! $service->calendarList ) return;

        // List all calendars
        $calendarList = $service->calendarList->listCalendarList();

        // Build array
        $cal_list = [];
        foreach ($calendarList->getItems() as $calendar) {
            $cal_list[$calendar->getId()] = $calendar->getSummary();
        }

        // Return array        
        return $cal_list;
    }

    /**
     * Returns the primary name of the connected Google acccount.
     * 
     * @since 1.0.0
     */
    public function primary_name() {
        // Build Calendar Service object
        $service = $this->google_service();
        if ( ! $service ) return;
        return $service->calendarList->get('primary')->getSummary();
    }

    /**
     * Defines the date cache key.
     * 
     * @since 1.0.0
     */
    private static function date_cache_key() {
        return '_nextav_cached_date';
    }

    /**
     * Defines the next available date.
     * 
     * @since 1.0.0
     */
    public function date() {
        $cache_key = self::date_cache_key();

        // Check for cached date
        $cached = get_transient( $cache_key );
       if ( $cached ) return $cached;

        // Fetch if no cached
        $date = $this->fetch_date();
        if ( ! $date ) {
            return;
        }

        // Cache for 1 day (adjust as needed)
        set_transient( $cache_key, $date, DAY_IN_SECONDS );
        set_transient( 'nextav_date_updated', current_time('mysql'), DAY_IN_SECONDS );

        return $date;
    }

    /**
     * Fetches the next available date.
     * 
     * @since 1.0.0
     */
    public function fetch_date() {

        $calendar_id = nextav_get_setting('integrations', 'calendar_id');

        if ( empty( $calendar_id ) ) {
            return;
        }
        
        $consecutive_days = nextav_get_setting('general', 'free_days') ?: 7;

        $service = $this->google_service();
        if (!$service) return;

        $busy_days = [];
        $search_start = new DateTime('today');

        // Keep trying year chunks until we find a suitable free window
        while (true) {
            $timeMin = clone $search_start;
            $timeMax = (clone $search_start)->modify('+1 year');

            $params = [
                'timeMin' => $timeMin->format(DateTime::RFC3339),
                'timeMax' => $timeMax->format(DateTime::RFC3339),
                'singleEvents' => true,
                'orderBy' => 'startTime',
                'maxResults' => 2500,
            ];

            $events = $service->events->listEvents($calendar_id, $params);

            foreach ($events->getItems() as $event) {
                $start = $event->getStart();
                $end = $event->getEnd();

                $start_date = $start->getDate() ?? substr($start->getDateTime(), 0, 10);
                $end_date = $end->getDate() ?? substr($end->getDateTime(), 0, 10);

                $start_dt = new DateTime($start_date);
                $end_dt = new DateTime($end_date);

                if ($start->getDate()) {
                    $end_dt->modify('-1 day');
                }

                $period = new DatePeriod($start_dt, new DateInterval('P1D'), $end_dt->modify('+1 day'));
                foreach ($period as $dt) {
                    $busy_days[$dt->format('Y-m-d')] = true;
                }
            }

            // Search for consecutive free days within current 1-year range
            $cursor = clone $search_start;
            while ($cursor < $timeMax) {
                $all_free = true;
                for ($i = 0; $i < $consecutive_days; $i++) {
                    $check_day = (clone $cursor)->modify("+$i days")->format('Y-m-d');
                    if (isset($busy_days[$check_day])) {
                        $all_free = false;
                        break;
                    }
                }

                if ($all_free) {
                    return $cursor->format('Y-m-d');
                }

                $cursor->modify('+1 day');
            }

            // If not found, continue search from next year
            $search_start->modify('+1 year');
        }

        return null; // fallback, though loop should always return or continue
    }

}