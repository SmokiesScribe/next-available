<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\DisplayCalendar;

/**
 * Generates the Gutenberg block displaying the calendar.
 * 
 * @since 1.0.0
 */
class BlockCalendar {

    /**
     * The DisplayCalendar instance.
     * 
     * @var DisplayCalendar
     */
    private $calendar;

    /**
     * Constructor.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        // Initialize calendar
        $this->calendar = new DisplayCalendar;
    }

    /**
     * Renders the block.
     * 
     * @since 1.0.0
     * 
     * @param   array {
     *     An array of attributes from the javascript.
     * 
     *     @type    string  $style                 The calendar style
     *     @type    bool    $showEventName         Whether to show event name.
     *     @type    bool    $showEventDetails      Whether to show event details.
     *     @type    bool    $colorEvents           Whether to colorize events.
     *     @type    bool    $highlightAvailable    Whether to highlight available days.
     *     @type    bool    $showPast              Whether to show past months.
     *     @type    bool    $includeWeekends       Whether to include weekends.
     * }
     */
    public function render_block( $attributes = [] ) {

        // Extract atts from block settings
        $atts = $this->build_atts( $attributes );

        // Output calendar
        return $this->calendar->display( $atts );
    }

    /**
     * Builds the attributes array from the js properties.
     * 
     * @since 1.0.0
     * 
     * @param   array {
     *     An array of attributes from the javascript.
     * 
     *     @type    string  $style                 The calendar style
     *     @type    bool    $showEventName         Whether to show event name.
     *     @type    bool    $showEventDetails      Whether to show event details.
     *     @type    bool    $colorEvents           Whether to colorize events.
     *     @type    bool    $highlightAvailable    Whether to highlight available days.
     *     @type    bool    $showPast              Whether to show past months.
     *     @type    bool    $includeWeekends       Whether to include weekends.
     * }
     * 
     * @return   array {
     *     An optional array of attributes.
     * 
     *     @type    string  $style                  The calendar style
     *     @type    bool    $show_event_name        Whether to show event name.
     *     @type    bool    $show_event_details     Whether to show event details.
     *     @type    bool    $color_events           Whether to colorize events.
     *     @type    bool    $highlight_available    Whether to highlight available days.
     *     @type    bool    $show_past              Whether to show past months.
     *     @type    bool    $include_weekends       Whether to include weekends.
     * }
     */
    private function build_atts( $attributes ) {
        return [
            'style'              => isset( $attributes['style'] ) ? sanitize_text_field( $attributes['style'] ) : 'simple',
            'show_event_name'    => isset( $attributes['showEventName'] ) ? (bool) $attributes['showEventName'] : true,
            'show_event_details' => isset( $attributes['showEventDetails'] ) ? (bool) $attributes['showEventDetails'] : true,
            'color_events'       => isset( $attributes['colorEvents'] ) ? (bool) $attributes['colorEvents'] : true,
            'highlight_available'=> isset( $attributes['highlightAvailable'] ) ? (bool) $attributes['highlightAvailable'] : true,
            'show_past'          => isset( $attributes['showPast'] ) ? (bool) $attributes['showPast'] : false,
            'include_weekends'   => isset( $attributes['includeWeekends'] ) ? (bool) $attributes['includeWeekends'] : true,
        ];
    }
}