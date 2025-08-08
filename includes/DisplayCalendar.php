<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleCal;
use \DateTime;
use \DatePeriod;
use \DateInterval;
use \DateTimeImmutable;

/**
 * Displays the Google calendar.
 * 
 * @since 1.0.0
 */
class DisplayCalendar {

    /**
     * The GoogleCal instance.
     * 
     * @var GoogleCal
     */
    private $cal;

    /**
     * The style for the calendar.
     * 
     * @var string
     */
    private $style;

    /**
     * Whether to show the event name.
     * 
     * @var bool
     */
    private $show_event_name;

    /**
     * Whether to show the event details.
     * 
     * @var bool
     */
    private $show_event_details;

    /**
     * Whether to colorize events.
     * 
     * @var bool
     */
    private $color_events;

    /**
     * Whether to highlight available days.
     * 
     * @var bool
     */
    private $highlight_available;

    /**
     * Whether to show past months.
     * 
     * @var bool
     */
    private $show_past;

    /**
     * Whether to include weekends.
     * 
     * @var bool
     */
    private $include_weekends;
    
    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->cal = new GoogleCal;
    }

    /**
     * Extracts attributes and sets properties.
     * 
     * @since 1.0.0
     * 
     * @param   array   $atts {
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
    private function extract_atts( $atts = [] ) {
        $this->style                = $atts['style'] ?? 'month';
        $this->show_event_name      = filter_var( $atts['show_event_name'] ?? true, FILTER_VALIDATE_BOOLEAN );
        $this->show_event_details   = filter_var( $atts['show_event_details'] ?? true, FILTER_VALIDATE_BOOLEAN );
        $this->color_events         = filter_var( $atts['color_events'] ?? true, FILTER_VALIDATE_BOOLEAN );
        $this->highlight_available  = filter_var( $atts['highlight_available'] ?? true, FILTER_VALIDATE_BOOLEAN );
        $this->show_past            = filter_var( $atts['show_past'] ?? false, FILTER_VALIDATE_BOOLEAN );
        $this->include_weekends     = filter_var( $atts['include_weekends'] ?? true, FILTER_VALIDATE_BOOLEAN );
    }

    /**
     * Outputs the calendar.
     * 
     * @since 1.0.0
     * 
     * @param   array   $atts {
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
     * 
     * @return  string  The rendered calendar html.
     */
    public function display( $atts = [] ): string {

        // Extract atts
        $this->extract_atts( $atts );

        // Handle month navigation via query parameter or shortcode
        $month_str = $_GET['cal_month'] ?? $atts['month'] ?? date('Y-m');
        try {
            $month_start = new DateTimeImmutable($month_str . '-01T00:00:00');
        } catch (\Exception $e) {
            $month_start = new DateTimeImmutable(date('Y-m-01T00:00:00'));
        }
        $month_end = $month_start->modify('+1 month')->modify('-1 second');

        $events = $this->fetch_events_for_month($month_start, $month_end);

        $calendar_data = $this->prepare_calendar_data($month_start, $month_end, $events);

        // Add navigation info
        $calendar_data['current_month'] = $month_start;
        $calendar_data['prev_month']    = $month_start->modify('-1 month')->format('Y-m');
        $calendar_data['next_month']    = $month_start->modify('+1 month')->format('Y-m');
        
        return $this->render_calendar($calendar_data);
    }

    /**
     * Fetches Google Calendar events for a specific month.
     *
     * @param DateTimeImmutable $month_start The start date of the month (inclusive).
     * @param DateTimeImmutable $month_end   The end date of the month (exclusive).
     *
     * @return array The list of Google Calendar event objects.
     */
    protected function fetch_events_for_month(DateTimeImmutable $month_start, DateTimeImmutable $month_end): array {
        $service     = $this->cal->google_service();
        $calendar_id = $this->cal->calendar_id;

        $params = [
            'singleEvents' => true, // Expand recurring events into individual occurrences
            'orderBy'      => 'startTime', // Sort by start time
            'maxResults'   => 2500, // Maximum number of events to fetch
            'timeMin'      => $month_start->format(DateTime::RFC3339),
            'timeMax'      => $month_end->format(DateTime::RFC3339),
        ];

        $events = $service->events->listEvents($calendar_id, $params);

        return $events->getItems();
    }

    /**
     * Prepares the calendar grid data and event positioning for rendering.
     *
     * @param DateTimeImmutable $month_start Start of the month.
     * @param DateTimeImmutable $month_end   End of the month.
     * @param array             $events      List of Google Calendar event objects.
     *
     * @return array {
     *     @type int   $start_day      Weekday index (0 = Sunday) of the first day of the month.
     *     @type int   $days_in_month  Number of days in the month.
     *     @type array $event_segments Processed event data for placement in grid.
     * }
     */
    protected function prepare_calendar_data(DateTimeImmutable $month_start, DateTimeImmutable $month_end, array $events): array {
        $start_day     = (int) $month_start->format('w'); // Day of the week (0 = Sun, 6 = Sat)
        $days_in_month = (int) $month_end->format('j');   // Day of month (last day)

        $event_segments = [];

        foreach ( $events as $event ) {
            // Get raw start and end (may be date or dateTime)
            $start_raw = $event->getStart()->getDateTime() ?? $event->getStart()->getDate();
            $end_raw   = $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate();

            $start = new DateTime($start_raw);
            $end   = new DateTime($end_raw);
            $end->modify('-1 second'); // Adjust because Google end dates are exclusive

            // Clamp event dates within the bounds of the current month
            $start = max($start, new DateTime($month_start->format('Y-m-d')));
            $end   = min($end, new DateTime($month_end->format('Y-m-d')));

            // Calculate grid positions
            $start_index = ((int) $start->format('j')) - 1 + $start_day;
            $end_index   = ((int) $end->format('j')) - 1 + $start_day;

            // Break event into weekly segments
            $current_index = $start_index;
            while ( $current_index <= $end_index ) {
                $week_day          = $current_index % 7;
                $days_left_in_week = 6 - $week_day;
                $segment_end       = min($current_index + $days_left_in_week, $end_index);
                $segment_span      = $segment_end - $current_index + 1;

                $event_segments[] = [
                    'start_cell' => $current_index,         // Cell index where segment begins
                    'span_days'  => $segment_span,          // Number of days this segment spans
                    'summary'    => $event->getSummary(),   // Event title
                ];

                $current_index = $segment_end + 1; // Move to the next week
            }
        }

        return [
            'start_day'      => $start_day,
            'days_in_month'  => $days_in_month,
            'event_segments' => $event_segments,
        ];
    }

    /**
     * Renders the HTML for the monthly calendar with events.
     *
     * @param array $data {
     *     @type int   $start_day      Weekday index of the first day of the month.
     *     @type int   $days_in_month  Total number of days in the month.
     *     @type array $event_segments List of segmented events for display.
     * }
     *
     * @return string The HTML markup for the calendar grid.
     */
    protected function render_calendar(array $data): string {
        ob_start();
        extract($data);

        // Format month header
        $month_label = $current_month->format('F Y');

        // Check whether to show prev button
        $show_prev = $this->show_past || $current_month > new DateTime(date('Y-m-01'));

        ?>
        <div id="calendar" class="month-calendar">
            <div class="calendar-nav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <a
                href="<?= $show_prev ? "?cal_month=$prev_month#calendar" : '#' ?>"
                class="cal-nav-button <?= $show_prev ? '' : 'disabled' ?>"
                <?= $show_prev ? '' : 'aria-disabled="true" tabindex="-1"' ?>
                >&laquo; Prev</a>
                <strong><?= $month_label ?></strong>
                <a href="?cal_month=<?= $next_month ?>#calendar" class="cal-nav-button">Next &raquo;</a>
            </div>

            <div class="calendar-header">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>

            <div class="calendar-body" style="display: grid; grid-template-columns: repeat(7, 1fr); grid-auto-rows: 120px; gap: 1px; position: relative;">
                <?php
                // Empty cells before the 1st
                for ( $i = 0; $i < $start_day; $i++ ) {
                    echo '<div class="day-cell empty"></div>';
                }

                // Days of the month
                for ( $day = 1; $day <= $days_in_month; $day++ ) {
                    echo '<div class="day-cell" data-day="'. $day .'" style="position: relative;">';
                    echo '<div class="date-number">' . $day . '</div>';
                    echo '</div>';
                }

                // Render event bars
                foreach ( $event_segments as $seg ) {
                    $start_cell = $seg['start_cell'];
                    $span_days  = $seg['span_days'];
                    $summary    = htmlspecialchars($seg['summary']);

                    $grid_column = ($start_cell % 7) + 1;
                    $grid_row    = floor($start_cell / 7) + 2;

                    $row_height = 121;
                    $top        = ($grid_row - 2) * $row_height + 40;
                    $left       = ($grid_column - 1) * (100 / 7);
                    $width      = ($span_days * 100) / 7;

                    $color = $this->get_event_color($summary);

                    echo "<div class='event-bar' title='{$summary}' style='position: absolute; top: {$top}px; left: calc({$left}% + 5px); width: calc({$width}% - 10px); background-color: {$color};'>";
                    echo $summary;
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Retrieves the event color for a single event.
     * 
     * @since 1.0.0
     * 
     * @param   string  $key    The event summary or id.
     * @return  string  The hex code.
     */
    private function get_event_color( string $key ): string {
        // Generate a consistent color from the event key (summary or id)
        $hash = crc32( $key );
        // Pick 6 hex digits from hash
        $color = sprintf( '#%06X', $hash & 0xFFFFFF );
        
        // Optionally, limit to pleasant blues/greens by tweaking
        // For now, just return the raw color
        return $color;
    }





}