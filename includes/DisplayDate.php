<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\NextDate;

/**
 * Displays the next available date.
 * 
 * @since 1.0.0
 */
class DisplayDate {

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
        $next_date = new NextDate;
        $this->date = $next_date->get_date();
        $this->updated_date = $this->get_updated_date();
    }

    /**
     * Retrieves the date last updated.
     * 
     * @since 1.0.0
     */
    private function get_updated_date() {
        if ( ! $this->date ) return;
        return get_transient( 'nextav_date_updated' );
    }

    /**
     * Outputs the formatted date.
     * 
     * @since 1.0.0
     */
    public function display( $atts = [] ) {
        $format = $this->format( $atts );
        $date_string = '';

        if ( ! $this->date ) {
            $date_string = nextav_get_setting( 'general', 'date_fallback' );
        } else {
            $date_string = self::format_date( $this->date, $format );
        }

        return esc_html( $date_string );
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
        if ( ! $this->updated_date ) return;
        $format = $this->format( $atts );
        $formatted_date = self::format_date( $this->updated_date, $format );
        if ( isset( $atts['date_only'] ) && $atts['date_only'] ) {
            return $formatted_date;
        }
        return sprintf(
            '<p>%s %s</p>',
            esc_html__( 'Updated', 'next-available' ),
            esc_html( $formatted_date )
        );
    }
}