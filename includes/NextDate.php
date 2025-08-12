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
     * Whether to include weekends.
     * 
     * @var bool
     */
    private $include_weekends;

    /**
     * The number of events to allow per day.
     * 
     * @var int
     */
    private $events_per_day;

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->google_cal = new GoogleCal;
        $this->consecutive_days = nextav_get_setting( 'general', 'free_days' ) ?: 7;
        $this->include_weekends = nextav_get_setting( 'general', 'include_weekends' ) === 'yes';
        $this->events_per_day = nextav_get_setting( 'general', 'events_per_day' ) ?? 1;
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

        // DELETE CACHE - TESTING
        delete_transient( $cache_key );

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
     * Counts events per day, marks day busy if event count >= $this->events_per_day.
     * 
     * @since 1.0.0
     */
    private function get_busy_days( $service, $calendar_id, DateTime $start ) {
        // Define the date range: 1 year starting from $start
        $timeMin = clone $start;
        $timeMax = ( clone $start )->modify( '+1 year' );

        // API parameters
        $params = [
            'timeMin' => $timeMin->format(DateTime::RFC3339),
            'timeMax' => $timeMax->format(DateTime::RFC3339),
            'singleEvents' => true,
            'orderBy' => 'startTime',
            'maxResults' => 2500,
        ];

        $events = $service->events->listEvents( $calendar_id, $params );

        // Instead of boolean, store count of events per day
        $events_count_per_day = [];

        foreach ( $events->getItems() as $event ) {
            $start = $event->getStart();
            $end = $event->getEnd();

            $start_date = $start->getDate() ?? substr($start->getDateTime(), 0, 10);
            $end_date   = $end->getDate() ?? substr($end->getDateTime(), 0, 10);

            $start_dt = new DateTime($start_date);
            $end_dt   = new DateTime($end_date);

            // Adjust for all-day events (end is non-inclusive)
            if ( $start->getDate() ) {
                $end_dt->modify('-1 day');
            }

            $period = new DatePeriod($start_dt, new DateInterval('P1D'), $end_dt->modify('+1 day'));

            foreach ( $period as $dt ) {
                $day_str = $dt->format('Y-m-d');

                if ( ! isset( $events_count_per_day[ $day_str ] ) ) {
                    $events_count_per_day[ $day_str ] = 0;
                }
                $events_count_per_day[ $day_str ]++;
            }
        }

        // Now create $busy_days array, only marking busy if count >= events_per_day
        $busy_days = [];
        foreach ( $events_count_per_day as $day => $count ) {
            if ( $count >= $this->events_per_day ) {
                $busy_days[ $day ] = true;
            }
        }

        return $busy_days;
    }

    /**
     * Finds the first date with the required number of consecutive free days.
     * Now treats days as busy only if count threshold met in get_busy_days.
     *
     * @since 1.0.0
     */
    private function find_consecutive_free_days( array $busy_days, DateTime $start ) {
        $cursor = clone $start;
        $end = (clone $start)->modify('+1 year');

        while ($cursor < $end) {

            // Skip weekend as possible start
            if ( ! $this->include_weekends && in_array( intval( $cursor->format('N') ), [6, 7], true ) ) {
                $cursor->modify( '+1 day' );
                continue;
            }

            $free_count = 0;
            $check_date = clone $cursor;

            while ( $free_count < $this->consecutive_days ) {

                if ( ! $this->include_weekends && in_array( intval( $check_date->format('N') ), [6, 7], true ) ) {
                    $check_date->modify( '+1 day' );
                    continue;
                }

                $day_str = $check_date->format( 'Y-m-d' );

                // Check if day is busy according to new logic
                if ( isset( $busy_days[ $day_str ] ) ) {
                    break;
                }

                $free_count++;
                $check_date->modify('+1 day');
            }

            if ( $free_count >= $this->consecutive_days ) {
                return $cursor;
            }

            $cursor->modify('+1 day');
        }

        return null;
    }

}