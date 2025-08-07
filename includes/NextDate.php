<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use \DateTime;
use \DatePeriod;
use \DateInterval;
use NextAv\Includes\GoogleCal;

/**
 * Fetches the next available date from the Google calendar.
 * 
 * @since 1.0.0
 */
class NextDate {

    /**
     * The GoogleCal instance.
     * 
     * @var GoogleCal
     */
    private $google_cal;

    /**
     * The number of consecutive days required to be free.
     * 
     * @var int
     */
    private $consecutive_days;

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->google_cal = new GoogleCal;
        $this->consecutive_days = nextav_get_setting( 'general', 'free_days' ) ?: 7;
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
     * Defines the date cache key.
     * 
     * @since 1.0.0
     */
    private static function updated_cache_key() {
        return 'nextav_date_updated';
    }

    /**
     * Defines the next available date.
     * 
     * @since 1.0.0
     */
    public function get_date() {
        $cache_key = self::date_cache_key();

        // Check for cached date
        $cached = get_transient( $cache_key );
        if ( $cached ) return $cached;

        // Fetch if no cached
        $date = $this->fetch_date();

        // Cache and return if found
        if ( $date ) {
            self::cache_date( $date );
            return $date;
        }        
    }

    /**
     * Caches the newly retrieved date.
     * 
     * @since 1.0.0
     * 
     * @param   string  $date   The date to cache.
     */
    private static function cache_date( $date ) {
        // Get cache keys
        $cache_key = self::date_cache_key();
        $updated_cache_key = self::updated_cache_key();

        // Cache for 1 day
        set_transient( $cache_key, $date, DAY_IN_SECONDS );
        set_transient( $updated_cache_key, current_time('mysql'), DAY_IN_SECONDS );
    }

    /**
     * Fetches the next available date with the required number of consecutive free days.
     *
     * @since 1.0.0
     */
    public function fetch_date() {
        // Exit if no calendar id selected
        if ( ! $this->google_cal->calendar_id ) {
            return null;
        }

        // New Google Service instance
        $service = $this->google_cal->google_service();
        if ( ! $service ) {
            return null;
        }

        // Define day to start searching
        $search_start = new DateTime( 'today' );

        // Find matching date
        while ( true ) {
            $busy_days = $this->get_busy_days( $service, $this->google_cal->calendar_id, $search_start );

            $free_date = $this->find_consecutive_free_days( $busy_days, $search_start );
            if ( $free_date ) {
                return $free_date->format( 'Y-m-d' );
            }

            // Move to next year and try again
            $search_start->modify( '+1 year' );
        }

        return null;
    }

    /**
     * Retrieves all busy days in a one-year window starting from $start.
     * 
     * Queries the Google Calendar API to retrieve all events
     * and records each day that is occupied by an event as a "busy" day.
     * 
     * @since 1.0.0
     */
    private function get_busy_days( $service, $calendar_id, DateTime $start ) {
        // Define the date range: 1 year starting from $start
        $timeMin = clone $start;
        $timeMax = ( clone $start )->modify( '+1 year' );

        // Set up API parameters to retrieve individual events ordered by start time
        $params = [
            'timeMin' => $timeMin->format(DateTime::RFC3339),
            'timeMax' => $timeMax->format(DateTime::RFC3339),
            'singleEvents' => true,
            'orderBy' => 'startTime',
            'maxResults' => 2500,
        ];

        // Call the API to fetch events from the calendar
        $events = $service->events->listEvents( $calendar_id, $params );
        $busy_days = [];

        // Loop through each event and extract the start and end dates
        foreach ( $events->getItems() as $event ) {
            $start = $event->getStart();
            $end = $event->getEnd();

            // Get date or extract date portion from datetime
            $start_date = $start->getDate() ?? substr($start->getDateTime(), 0, 10);
            $end_date   = $end->getDate() ?? substr($end->getDateTime(), 0, 10);

            $start_dt = new DateTime($start_date);
            $end_dt   = new DateTime($end_date);

            // If it's an all-day event, subtract one day from the end (non-inclusive)
            if ( $start->getDate() ) {
                $end_dt->modify('-1 day');
            }

            // Create a range of busy days (inclusive)
            $period = new DatePeriod($start_dt, new DateInterval('P1D'), $end_dt->modify('+1 day'));
            foreach ( $period as $dt ) {
                $busy_days[ $dt->format('Y-m-d') ] = true;
            }
        }

        return $busy_days;
    }

    /**
     * Finds the first date with the required number of consecutive free days.
     * 
     * Scans through each day starting from $start and checks for a sequence
     * of $this->consecutive_days where none are marked as busy.
     * 
     * @since 1.0.0
     */
    private function find_consecutive_free_days( array $busy_days, DateTime $start ) {
        $cursor = clone $start;
        $end = ( clone $start )->modify( '+1 year' );

        // Iterate day by day through the year
        while ( $cursor < $end ) {
            $all_free = true;

            // Check the next N days for availability
            for ( $i = 0; $i < $this->consecutive_days; $i++ ) {
                $check_day = (clone $cursor)->modify("+$i days")->format('Y-m-d');
                if ( isset($busy_days[$check_day]) ) {
                    // At least one day is busy — break and move to next day
                    $all_free = false;
                    break;
                }
            }

            // Found a sequence of free days
            if ( $all_free ) {
                return $cursor;
            }

            // Move to the next day and repeat
            $cursor->modify('+1 day');
        }

        // No suitable window found in the given timeframe
        return null;
    }
}