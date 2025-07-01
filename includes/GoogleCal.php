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
     * The client secret.
     * 
     * @var string
     */
    private $client_secret;
    
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
        $this->client_secret = 'CLIENT_SECRET'; // pull from settings?
        $this->client_id = 'CLIENT_ID';
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

        $client->setClientId( $this->client_id );
        $client->setClientSecret( $this->client_secret );
        $client->setRedirectUri( $this->redirect_url );
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            $refresh_token = $client->getRefreshToken();
            if ($refresh_token) {
                $client->fetchAccessTokenWithRefreshToken($refresh_token);
                update_option('nextav_google_tokens', $client->getAccessToken());
            } else {
                wp_die('Refresh token missing, please reconnect.');
            }
        }
        
        return $client;
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
        $cached = get_option( $cache_key );
        if ( $cached ) return $cached;

        // Fetch if no cached
        $date = $this->fetch_date();

        // Cache fetched
        update_option( $cache_key, $date );
        update_option( 'nextav_date_updated', current_time('mysql') );

        return $date;
    }

    /**
     * Fetches the next available date.
     * 
     * @since 1.0.0
     */
    public function fetch_date() {
        $calendar_id = nextav_get_setting('integrations', 'calendar_id');
        $consecutive_days = nextav_get_setting('general', 'free_days') ?: 7;

        $consecutive_days = 40;

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