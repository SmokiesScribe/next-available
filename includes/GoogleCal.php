<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use \GriffinVendor\Google\Service\Calendar as Google_Calendar;
use NextAv\Includes\GoogleClientFactory;
use NextAv\Includes\GoogleTokenManager;

/**
 * Handles the Google Calendar integration.
 * 
 * @since 1.0.0
 */
class GoogleCal {

    /**
     * The GoogleClientFactory instance.
     * 
     * @var GoogleClientFactory
     */
    private $google_client;

    /**
     * The GoogleTokenManager instance.
     * 
     * @var GoogleTokenManager
     */
    private $token_manager;

    /**
     * The selected calendar ID.
     * 
     * @var string
     */
    public $calendar_id;

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->google_client = new GoogleClientFactory;
        $this->token_manager = new GoogleTokenManager;
        $this->calendar_id = $this->get_calendar_id();
    }

    /**
     * Retrieves the currently selected calendar id.
     * 
     * @since 1.0.0
     */
    private function get_calendar_id() {
        $cal_id = nextav_get_setting( 'integrations', 'calendar_id' );
        return $cal_id;
    }

    /**
     * Builds the Google Client object.
     * 
     * @since 1.0.0
     * 
     * @return  Google_Client  The Google_Client object.
     */
    private function google_client() {
        // Validate token data
        $token_data = $this->token_manager->token_data;
        if ( ! $token_data ) return;

        // New Google Client
        $client = $this->google_client->make( $token_data );
        return $client;
    }

    /**
     * Builds the Google Calendar Service object. 
     * 
     * @since 1.0.0
     * 
     * @return  Google_Calendar    The Google_Calendar object.
     */
    public function google_service() {
        $client = $this->google_client();
        if ( ! $client ) return;
        return new Google_Calendar( $client );
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
}