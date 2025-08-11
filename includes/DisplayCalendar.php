<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleCal;
use NextAv\Includes\NextDate;

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
     * The next available date.
     * 
     * @var string
     */
    private $next_available;

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
     * The month to display.
     * In Y-m format.
     * 
     * @var string
     */
    private $month;
    
    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->cal = new GoogleCal;
        $this->next_available = ( new NextDate )->get_date();
        $this->contact_email = $this->get_email();
    }

    /**
     * Retrieves the contact email address.
     * 
     * @since 1.0.0
     */
    private function get_email() {
        $email = nextav_get_setting( 'general', 'contact_email' );
        if ( ! empty( $email ) ) {
            return $email;
        } else {
            return $email = get_option('admin_email');
        }
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
        $this->include_weekends     = filter_var( $atts['include_weekends'] ?? true, FILTER_VALIDATE_BOOLEAN ); // @todo not rendering events correctly
        $this->month                = $this->get_display_month( $atts );
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

        // Get month data
        $month_data = $this->get_month_data();
        $month_start = $month_data['start'];
        $month_end = $month_data['end'];

        // Fetch events
        $events = $this->fetch_events_for_month( $month_start, $month_end );

        // Prepare calendar data
        $calendar_data = $this->prepare_calendar_data( $month_start, $month_end, $events );

        // Add navigation info
        $calendar_data = $this->add_nav( $calendar_data, $month_start );

        // Output calendar
        return $this->render_calendar( $calendar_data, $atts );
    }

    /**
     * Retrieves the month data. 
     * 
     * @since 1.0.0
     * 
     * @return  array {
     *     @type    DateTime    $start  The starting month.
     *     @type    DateTime    $end    The ending month.
     * }
     */
    private function get_month_data() {

        // Get month start, default to current month
        try {
            $month_start = new DateTimeImmutable( $this->month . '-01T00:00:00' );
        } catch ( \Exception $e ) {
            $month_start = new DateTimeImmutable( date( 'Y-m-01T00:00:00' ) );
        }

        // Get month end
        $month_end = $month_start->modify( '+1 month' )->modify( '-1 second' );

        // Return array
        return [
            'start' => $month_start,
            'end'   => $month_end
        ];
    }

    /**
     * Adds the nav info to the calendar data.
     * 
     * @since 1.0.0
     * 
     * @param   array       $calendar_data  The calendar data to modify.
     * @param   DateTime    $month_start    The current month DateTime.
     * @return  array       The modified array of calendar data.
     */
    private function add_nav( $calendar_data, $month_start ) {
        $calendar_data['current_month'] = $month_start;
        $calendar_data['prev_month']    = $month_start->modify('-1 month')->format('Y-m');
        $calendar_data['next_month']    = $month_start->modify('+1 month')->format('Y-m');
        return $calendar_data;
    }

    /**
     * Retrieves the month to display.
     * 
     * @since 1.0.0
     * 
     * @param   array   $atts   Optional. The array of atts.
     */
    private function get_display_month( $atts = [] ) {

        // Return month if provided in atts
        if ( isset( $atts['month'] ) ) {
            // Check if $month matches "YYYY-MM" format strictly
            $d = DateTime::createFromFormat('Y-m', $this->month);
            // Validate parsing and ensure the formatted date matches input exactly (to avoid things like 2023-13 becoming 2024-01)
            if ( $d && $d->format('Y-m') === $this->month ) {
                return $this->month;
            }
        }

        // Use next_available month as default if it exists and is valid, else current month
        if ( ! empty( $this->next_available ) ) {
            try {
                $next_date = new DateTimeImmutable( $this->next_available );
                $default_month_str = $next_date->format('Y-m');
            } catch ( \Exception $e ) {
                // Invalid date format, fallback to current month
                $default_month_str = date( 'Y-m' );
            }
        } else {
            $default_month_str = date( 'Y-m' );
        }

        // Handle month navigation via query parameter or shortcode, with default from above
        return $_GET['cal_month'] ?? $atts['month'] ?? $default_month_str;
    }

    /**
     * Fetches Google Calendar events for a specific month.
     *
     * @param DateTimeImmutable $month_start The start date of the month (inclusive).
     * @param DateTimeImmutable $month_end   The end date of the month (exclusive).
     *
     * @return array The list of Google Calendar event objects.
     */
    protected function fetch_events_for_month( DateTimeImmutable $month_start, DateTimeImmutable $month_end ): array {
        $service     = $this->cal->google_service();
        $calendar_id = $this->cal->calendar_id;

        // Define event params
        $params = [
            'singleEvents' => true, // Expand recurring events into individual occurrences
            'orderBy'      => 'startTime', // Sort by start time
            'maxResults'   => 2500, // Maximum number of events to fetch
            'timeMin'      => $month_start->format( DateTime::RFC3339 ),
            'timeMax'      => $month_end->format( DateTime::RFC3339 ),
        ];

        // Get events list
        $events = $service->events->listEvents( $calendar_id, $params );

        // Return event items
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
    $start_day     = (int) $month_start->format('w'); // 0=Sun
    $days_in_month = (int) $month_end->format('j');

    $event_segments = [];
    $events_per_day = [];

    foreach ($events as $event) {
        $start_raw = $event->getStart()->getDateTime() ?? $event->getStart()->getDate();
        $end_raw   = $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate();

        $start = new DateTime($start_raw);
        $end   = new DateTime($end_raw);
        $end->modify('-1 second');

        // Clamp to month range
        $start = max($start, new DateTime($month_start->format('Y-m-d')));
        $end   = min($end, new DateTime($month_end->format('Y-m-d')));

        // --- NEW: Count events per day ---
        $day_cursor = clone $start;
        while ($day_cursor <= $end) {
            $day_num = (int) $day_cursor->format('j'); // day of month

            // Skip weekend if not included
            if ($this->include_weekends || (!in_array((int)$day_cursor->format('w'), [0,6]))) {
                if (!isset($events_per_day[$day_num])) {
                    $events_per_day[$day_num] = 0;
                }
                $events_per_day[$day_num]++;
            }

            $day_cursor->modify('+1 day');
        }

        // --- Existing segment logic remains ---
        if ($this->include_weekends) {
            $start_index = ((int) $start->format('j')) - 1 + $start_day;
            $end_index   = ((int) $end->format('j')) - 1 + $start_day;

            $current_index = $start_index;
            while ($current_index <= $end_index) {
                $week_day          = $current_index % 7;
                $days_left_in_week = 6 - $week_day;
                $segment_end       = min($current_index + $days_left_in_week, $end_index);
                $segment_span      = $segment_end - $current_index + 1;

                $event_segments[] = [
                    'start_cell' => $current_index,
                    'span_days'  => $segment_span,
                    'summary'    => $event->getSummary(),
                ];
                $current_index = $segment_end + 1;
            }
        } else {
            $current = clone $start;
            $iteration_safety = 0;
            while ($current <= $end) {
                if (++$iteration_safety > 1000) {
                    break; // Prevent infinite loop
                }

                if (in_array((int) $current->format('w'), [0, 6])) {
                    $current->modify('+1 day');
                    continue;
                }

                $start_cell = $this->weekday_index_from_date($month_start, $current);

                $segment_end = clone $current;
                $span_days   = 1;
                while ($segment_end < $end) {
                    $next_day = (clone $segment_end)->modify('+1 day');
                    if (in_array((int) $next_day->format('w'), [0, 6])) {
                        break;
                    }
                    $segment_end = $next_day;
                    $span_days++;
                    if ((int) $segment_end->format('w') === 5) {
                        break;
                    }
                }

                $event_segments[] = [
                    'start_cell' => $start_cell,
                    'span_days'  => $span_days,
                    'summary'    => $event->getSummary(),
                ];

                $current = (clone $segment_end)->modify('+1 day');
            }
        }
    }

    $event_segments = $this->assign_event_stacks_per_day($event_segments, $events_per_day);

    return [
        'start_day'      => $start_day,
        'days_in_month'  => $days_in_month,
        'event_segments' => $event_segments,
        'events_per_day' => $events_per_day,
    ];
}


/**
 * Calculates the cell index for a date in a weekday-only grid.
 */
private function weekday_index_from_date(DateTimeImmutable $month_start, DateTime $date): int {
    // If date is before month start, no weekdays have passed
    if ($date <= $month_start) {
        return 0;
    }

    $index = 0;
    $current = DateTime::createFromImmutable($month_start);
    $iteration_safety = 0;

    while ($current < $date) {
        if (++$iteration_safety > 1000) {
            // Prevent infinite loop
            break;
        }

        // Count weekdays only
        if (!in_array((int) $current->format('w'), [0, 6])) {
            $index++;
        }

        // Always move forward
        $current->modify('+1 day');
    }

    return $index;
}

/**
 * Assign vertical stacking index to overlapping events to avoid overlap.
 *
 * @param array $event_segments Each with keys 'start_cell' and 'span_days'
 * @param bool $include_weekends
 * @return array Event segments with added 'stack' index
 */
private function assign_event_stacks_per_day(array $event_segments, array $events_per_day): array {
    // We'll assign stack index only to first 2 events per day,
    // mark others with stack = -1 to skip rendering bars

    // Group events by start_cell (day)
    $grouped = [];
    foreach ($event_segments as $seg) {
        $day = $seg['start_cell'];
        $grouped[$day][] = $seg;
    }

    $result = [];
    foreach ($grouped as $day => $events) {
        // Sort events by start_cell if needed (should be already sorted)
        usort($events, function($a, $b) {
            return $a['start_cell'] <=> $b['start_cell'];
        });

        // Assign stack 0 or 1 for first 2 events
        for ($i = 0; $i < count($events); $i++) {
            if ($i < 2) {
                $events[$i]['stack'] = $i;
            } else {
                $events[$i]['stack'] = -1; // mark as "extra"
            }
            $result[] = $events[$i];
        }
    }

    return $result;
}


    /**
     * Renders the HTML for the monthly calendar with events.
     *
     * @param array $data {
     *     @type int   $start_day      Weekday index of the first day of the month.
     *     @type int   $days_in_month  Total number of days in the month.
     *     @type array $event_segments List of segmented events for display.
     * }
     * @param array $atts The original array of atts.
     *
     * @return string The HTML markup for the calendar grid.
     */
    protected function render_calendar( array $data, array $atts ): string {
        ob_start();
        extract( $data );

        $month_label = $current_month->format( 'F Y' );
        $show_prev = $this->show_past || $current_month > new DateTime( date( 'Y-m-01' ) );

        // Decide days and column count based on include_weekends
        $days = $this->include_weekends ? ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] : ['Mon','Tue','Wed','Thu','Fri'];
        $cols = count( $days );

        // Encode atts
        $json_atts = json_encode( $atts );

        ?>
            <div
            id="nextav-calendar"
            class="nextav-month-calendar"
            data-atts='<?php echo htmlspecialchars( $json_atts, ENT_QUOTES, 'UTF-8' ); ?>'
            data-month="<?php echo esc_attr( $this->month ); ?>"
            >
            <div class="nextav-calendar-nav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <a
                class="nextav-cal-nav-button prev <?= $show_prev ? '' : 'disabled' ?>"
                <?= $show_prev ? '' : 'aria-disabled="true" tabindex="-1"' ?>
                >&laquo; Prev</a>
                <strong><?= $month_label ?></strong>
                <a class="nextav-cal-nav-button next">Next &raquo;</a>
            </div>

            <div class="nextav-calendar-header" style="display: grid; grid-template-columns: repeat(<?= $cols ?>, 1fr);">
                <?php
                foreach ($days as $day) {
                    echo "<div>$day</div>";
                }
                ?>
            </div>

            <div class="nextav-calendar-body" style="display: grid; grid-template-columns: repeat(<?= $cols ?>, 1fr); grid-auto-rows: 120px; gap: 1px; position: relative;">
                <?php
                // Calculate day of week for first day (0=Sun..6=Sat)
                $first_day_w = (int) $current_month->format( 'w' );

                // If excluding weekends, adjust the number of empty cells before month start
                // Count how many weekend days fall before the 1st to skip them
                if (!$this->include_weekends) {
                    // Calculate how many weekend days to skip before first weekday
                    // We'll count empty cells only for weekdays (Mon=1..Fri=5)
                    $empty_cells = 0;
                    for ($i = 0; $i < $first_day_w; $i++) {
                        if (in_array($i, [1, 2, 3, 4, 5])) { // Mon-Fri
                            $empty_cells++;
                        }
                    }
                } else {
                    $empty_cells = $first_day_w;
                }

                // Output empty cells before first day
                for ($i = 0; $i < $empty_cells; $i++) {
                    echo '<div classnextav-day-cell empty"></div>';
                }

                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date_str = $current_month->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                    $weekday = (int)(new DateTime($date_str))->format('w');

                    // Skip weekend days if weekends not included
                    if (!$this->include_weekends && ($weekday === 0 || $weekday === 6)) {
                        continue;
                    }

                    // Check if this day matches next_available date
                    $is_next_available = $date_str === $this->next_available;
                    $highlight_class = $is_next_available ? ' highlight-available' : '';

                    echo '<div class="nextav-day-cell' . $highlight_class . '" data-day="'. $day .'" style="position: relative;">';
                    echo '<div class="nextav-date-number">' . $day . '</div>';

                    if ($is_next_available) {
                        $contact_email = htmlspecialchars($this->contact_email);
                        echo '<div class="nextav-available-badge">Next Available</div>';
                        echo '<a href="mailto:' . $contact_email . '" class="nextav-calendar-contact-button" style="position: absolute; bottom: 5px; left: 5px; font-size: 12px; text-decoration: none; display: inline-block;">Contact</a>';
                    }

                    echo '</div>';
                }

                // Keep track of how many event bars we've rendered per day
                $event_bars_rendered = [];

                // Render event bars
                foreach ($event_segments as $seg) {
                $start_cell = $seg['start_cell'];
                $span_days  = $seg['span_days'];
                $summary    = htmlspecialchars($seg['summary']);

                if ($this->include_weekends) {
                    $grid_column = ($start_cell % 7) + 1;
                    $grid_row    = floor($start_cell / 7) + 2;
                    $row_height  = 121;
                    $cell_width_percent = $this->include_weekends ? (100 / 7) : (100 / 5);
                    $width = ($span_days * $cell_width_percent) - 2; // subtract 1% for margin/padding buffer
                    $left = ($grid_column - 1) * $cell_width_percent;

                    $stack_index = $seg['stack'] ?? 0;
                    $vertical_offset = 30;
                    $top = ($grid_row - 2) * $row_height + 30 + ($stack_index * $vertical_offset);

                    // Calculate day of month from start_cell and start_day
                    $day_of_month = $start_cell - $start_day + 1;

                } else {
                    $week_number = floor($start_cell / 5);
                    $day_of_week = $start_cell % 5;

                    $grid_column = $day_of_week + 1;
                    $grid_row    = $week_number + 2;
                    $row_height  = 121;
                    $cell_width_percent = $this->include_weekends ? (100 / 7) : (100 / 5);
                    $width = ($span_days * $cell_width_percent) - 2; // subtract 1% for margin/padding buffer
                    $left = ($grid_column - 1) * $cell_width_percent;

                    $stack_index = $seg['stack'] ?? 0;
                    $vertical_offset = 24;
                    $top = ($grid_row - 2) * $row_height + 40 + ($stack_index * $vertical_offset);

                    $day_of_month = $this->date_from_weekday_index($current_month, $start_cell);
                }

                    // Initialize rendered count if not set
                    if (!isset($event_bars_rendered[$day_of_month])) {
                        $event_bars_rendered[$day_of_month] = 0;
                    }

                    // Maximum bars to show per day (e.g. 2)
                    $max_bars_per_day = 2;

                    // How many events on this day
                    $day_event_count = $events_per_day[$day_of_month] ?? 0;

                    // If we already reached max bars, skip rendering this event bar
                    if ($event_bars_rendered[$day_of_month] >= $max_bars_per_day) {
                        continue;
                    }

                    $color = $this->color_events ? $this->get_event_color($summary) : '#808080';

                    echo "<div class='nextav-event-bar' title='{$summary}' style='position: absolute; top: {$top}px; left: calc({$left}% + 5px); width: calc({$width}% - 10px); background-color: {$color};'>";

                    if ($this->show_event_name) {
                        echo $summary;
                    }

                    echo "</div>";

                    $event_bars_rendered[$day_of_month]++;

                    // If we've just rendered the max number of bars and there are more events, show "+X more"
                    if ($event_bars_rendered[$day_of_month] === $max_bars_per_day && $day_event_count > $max_bars_per_day) {
                        $extra_count = $day_event_count - $max_bars_per_day;
                        echo "<div class='nextav-more-events-note' style='font-size: 12px; color: #666; margin-top: 2px; position: absolute; top: ".($top + $vertical_offset)."px; left: calc({$left}% + 5px); width: calc({$width}% - 10px);'>+{$extra_count} more</div>";
                    }
                }

                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Converts a zero-based weekday-only grid index to the corresponding calendar day of the month.
     * 
     * This method maps an index that counts only weekdays (Monday through Friday),
     * skipping weekends, back to the actual day number within the given month.
     * 
     * For example, if $weekday_index is 0, this returns the first weekday of the month (usually 1).
     * If $weekday_index is 5, it returns the day number for the 6th weekday (skipping weekends).
     * 
     * @param DateTimeImmutable $month_start   The first day of the month as a DateTimeImmutable object.
     * @param int               $weekday_index Zero-based index counting only weekdays (Mon-Fri).
     * 
     * @return int The day of the month (1-31) that corresponds to the given weekday index.
     */
    private function date_from_weekday_index(DateTimeImmutable $month_start, int $weekday_index): int {
        $day = 1;
        $count = 0;
        $date = new DateTime($month_start->format('Y-m-01'));

        while ($count < $weekday_index) {
            $date->modify('+1 day');
            $w = (int) $date->format('w');
            if ($w !== 0 && $w !== 6) { // not weekend
                $count++;
            }
            $day = (int) $date->format('j');
        }
        return $day;
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