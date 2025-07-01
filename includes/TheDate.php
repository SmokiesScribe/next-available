<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleCal;

/**
 * Displays the next available date.
 * 
 * @since 1.0.0
 */
class TheDate {

    /**
     * The next available date.
     * 
     * @var
     */
    public $date;
    
    /**
     * Constructor method.
     * 
     * @since 1.0.0
     * 
     * @param   string  $url    Optional. The URL to modify.
     *                          Defaults to the current URL.
     */
    public function __construct() {
        $cal = new GoogleCal;
        $this->date = $cal->date();
        $this->updated_date = get_option( 'nextav_date_updated' );
    }

    /**
     * Outputs the formatted date.
     * 
     * @since 1.0.0
     */
    public function display( $atts ) {
        $format = $this->format( $atts );
        return self::format_date( $this->date, $format );
    }

    /**
     * Formats a date.
     * 
     * @since 1.0.0
     * 
     * @param   string  $date   The date to format.
     * @param   string  $format The format to use.
     */
    private static function format_date( $date, $format ) {
        // If $date is already a DateTime object:
        if ( $date instanceof \DateTime ) {
            return $date->format( $format );
        }

        // Otherwise, assume it's a string and try to create a DateTime object
        try {
            $date_obj = new \DateTime( $date );
            return $date_obj->format( $format );
        } catch ( \Exception $e ) {
            return 'Invalid date';
        }
    }

    /**
     * Defines the date format.
     * 
     * @since 1.0.0
     */
    private function format( $atts ) {
        $format = $atts['format'] ?? null;
        if ( ! $format ) {
            $format = nextav_get_setting( 'general', 'date_format' );
        }
        return $format;
    }

    /**
     * Outputs the formatted date the next available date was last updated.
     * 
     * @since 1.0.0
     */
    public function display_updated( $atts = [] ) {
        $format = $this->format( $atts );
        return self::format_date( $this->updated_date, $format );
    }
}