<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin notice.
 * 
 * @since 1.0.0
 */
class AdminNotice {

    /**
     * The notice message.
     * 
     * @var string
     */
    private $message;

    /**
     * The notice color ('red', 'orange', 'blue', or 'green').
     * Defaults to 'blue'.
     * 
     * @var string
     */
    private $color;

    /**
     * The repair link or links.
     * 
     * @var array
     */
    private $repair_link;

    /**
     * Whether the notice is dismissable.
     * 
     * @var bool
     */
    private $dismissable;
    
    /**
     * The text for the repair link or links.
     * 
     * @var array
     */
    private $repair_link_text;

    /**
     * Additional classes for the admin notice.
     * 
     * @var string
     */
    private $classes;

    /**
     * The icon key. 
     * 
     * @var string
     */
    private $icon_key;

    /**
     * The icon color. 
     * 'blue', 'black', 'green', 'red', or 'gray'.
     * 
     * @var string
     */
    private $icon_color;

    /**
     * The priority for the hook.
     * 
     * @var int
     */
    private $priority;

    /**
     * The ID of the admin notice.
     * 
     * @var string
     */
    private $ID;

    /**
     * Constructor method.
     * 
     * @since 0.1.0
     * 
     * @param   array   $args {
     *     An array of arguments for building the admin notice.
     * 
     *     @string      $key                  The unique key for the notice.
     *     @string      $repair_link          The link to the repair page.
     *     @string      $repair_link_text     Optional. The link text.
     *                                        Defaults to 'Repair'.
     *     @string      $message              The message to display in the notice.
     *     @bool        $dismissable          Optional. Whether the notice should be dismissable.
     *                                        Defaults to false.
     *     @string      $color                Optional. The color of the notice.
     *                                        Accepts 'green', 'blue', 'orange', 'red'.
     *                                        Defaults to blue.
     *     @string      $classes              Additional classes for the admin notice.
     *     @int         $priority             Optional. The priority for the hook.
     *                                        Defaults to 10.
     *     @array       $display              Optional. An array of places to display the notice.
     *                                        'sitewide', 'plugin', 'dashboard'
     * }
     */
    public function __construct( $args ) {
        $this->message = $args['message'] ?? null;
        $this->ID = self::build_id( $args['key'] ?? null );

        // Exit if no message
        if ( ! $this->message ) {
            return;
        }

        // Extract args
        $this->extract_args( $args );

        // Cache key based on the message
        $cache_key = 'nextav_admin_notice_' . md5( $this->message );

        // Check if the notice has already been shown
        if ( ! get_transient( $cache_key ) ) {
            // Build notice
            $this->define_hooks();

            // Set transient for 1 second
            set_transient( $cache_key, true, 1 );
        }
    }

    /**
     * Extracts args.
     * 
     * @since 0.1.0
     * 
     * @param   array   $args   See constructor.
     */
    private function extract_args( $args ) {
        $this->repair_link = $this->extract_repair_links( $args );
        $this->repair_link_text = isset( $args['repair_link_text'] ) ? (array) $args['repair_link_text'] : ['Repair'];
        $this->dismissable = $args['dismissable'] ?? false;
        $this->color = $args['color'] ?? 'blue';
        $this->classes = $args['classes'] ?? '';
        $this->icon_color = $args['icon_color'] ?? $this->default_icon( 'color' );
        $this->icon_key = $args['icon'] ?? $this->default_icon( 'key' );
        $this->priority = $args['priority'] ?? 10;
        $this->display = $args['display'] ?? $this->get_display();
    }

    /**
     * Builds the display options.
     * 
     * @since 1.0.25
     */
    private function get_display() {
        switch ( $this->color ) {
            case 'red':
                return ['sitewide'];
            case 'orange':
                return ['plugin', 'dashboard'];
            case 'blue':
                return ['plugin'];
            case 'green':
                return ['plugin'];
        }
    }

    /**
     * Checks whether we're on a display page.
     * 
     * @since 1.0.25
     */
    private function display_match() {
        $allowed_pages = $this->display;

        if ( empty( $allowed_pages ) ) {
            return false;
        }

        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        // Define admin page matches
        $page_matches = [
            'sitewide'   => true, // Always show if sitewide
            'dashboard'  => ( 'dashboard' === $screen->id || 'toplevel_page_nextav-dashboard' === $screen->id),
            'plugin'     => ( false !== strpos( $screen->id, 'nextav' ) ), // Adjust for your plugin slug
        ];

        // Check if the current page matches any allowed display setting
        foreach ( $allowed_pages as $page ) {
            if ( isset( $page_matches[ $page ] ) && $page_matches[ $page ] ) {
                return true;
            }
        }

        return false;
    }

    
    /**
     * Retrieves repair link or links.
     * 
     * @since 1.0.3
     * 
     * @param   array   $args   See constructor.
     */
    private function extract_repair_links( $args ) {
        // Initialize
        $repair_link = null;
        
        // Check if link is set
        if ( isset( $args['repair_link'] ) ) {
            
            // Normalize to an array
            $repair_links = (array) $args['repair_link'];
            
            // Apply admin_url to each item only if it's not a full URL
            $repair_link = array_map( function( $link ) {
                // Trim any spaces from the link to avoid issues
                $link = trim( $link );
                
                // Check if it's a valid full URL (starts with http:// or https://)
                if ( ! filter_var( $link, FILTER_VALIDATE_URL ) ) {
                    // If it's not a full URL, apply admin_url
                    return admin_url( $link );
                }
                
                // Return the link as-is if it's already a complete URL
                return $link;
            }, $repair_links );
        }
        
        return $repair_link;
    }

    /**
     * Hooks the notice to the admin notices.
     * 
     * @since 0.1.0
     */
    private function define_hooks() {
        add_action('admin_notices', [$this, 'build'], $this->priority);
    }

    /**
     * Builds the notice.
     * 
     * @since 0.1.0
     */
    public function build() {
        // Check if we're on a displayed page
        if ( ! $this->display_match() ) {
            return;
        }

        // Build the repair link html
        $repair_link = $this->build_repair_link();

        // Build the notice classes
        $classes = $this->build_classes();

        // Build the notice
        $notice = sprintf(
            '<div class="%1$s" id="%2$s">
                <span class="dashicons dashicons-nextav-dark nextav-admin-notice-bc-icon"></span>
                <div class="nextav-admin-notice-content">
                    <span class="nextav-admin-notice-icon">%3$s</span>
                    <div class="nextav-admin-notice-message">
                        %4$s
                        <div class="nextav-admin-notice-repair">%5$s</div>
                    </div>
                </div>
            </div>',
            $classes,
            $this->ID,
            $this->build_icon(),
            $this->message,
            $repair_link
        );

        // Escape and output notice
        $allowed_html = self::allowed_html();
        echo wp_kses( $notice, $allowed_html );
    }

    /**
     * Builds the admin notice branded header.
     * 
     * @since 1.0.25
     */
    private function build_header() {
        $content = '<span class="dashicons dashicons-nextav-dark"></span>';
        $content .= '<span class="nextav-admin-notice-header-title">nextav</span>';
        return $content;
    }

    /**
     * Builds the icon html.
     * 
     * @since 1.0.25
     */
    private function build_icon() {
        if ( ! empty( $this->icon_key ) ) {
            return nextav_icon( $this->icon_key, $this->icon_color );
        }
    }

    /**
     * Defines the default icon key for the notice color.
     * 
     * @since 1.0.25
     * 
     * @param   $type   string  The item to return ('key' or 'color').
     */
    private function default_icon( $type = 'key' ) {
        $key = '';
        $color = null;

        switch ( $this->color ) {
            case 'red':
                $key = 'error';
                $color = 'admin-red';
                break;
            case 'orange':
                $key = 'error';
                $color = 'admin-orange';
                break;
            case 'blue':
                $key = 'info';
                $color = 'admin-blue';
                break;
            case 'green':
                $key = 'check';
                $color = 'admin-green';
                break;
        }
        return $type === 'key' ? $key : $color;
    }

    /**
     * Defines classes for the admin notice.
     * 
     * @since 1.0.25
     */
    private function build_classes() {
        $classes = [
            'notice',
            'nextav-admin-notice',
            $this->notice_class( $this->color ),
            $this->dismissable ? 'is-dismissible' : '',
            $this->classes
        ];
        return implode( ' ', $classes );
    }

    /**
     * Builds the ID for the admin notice.
     * 
     * @since 1.0.25
     * 
     * @param   string  $key    The unique key for the admin notice.
     */
    public static function build_id( $key ) {
        if ( empty( $key ) ) return '';
        return sprintf(
            'nextav_admin_notice_%s',
            $key
        );
    }

    /**
     * Defines the allowed html.
     * 
     * @since 1.0.21
     */
    private static function allowed_html() {
        return [
            'div'       => ['class' => true, 'id' => true],
            'p'         => [],
            'a'         => ['href' => true, 'class' => true, 'target' => []],
            'i'         => ['class' => []],
            'ul'        => ['class' => []],
            'li'        => ['class' => []],
            'h2'        => ['class' => []],
            'h3'        => ['class' => []],
            'h4'        => ['class' => []],
            'span'      => ['class' => []],
            'strong'    => [],
        ];
    }

    /**
     * Builds the repair link html.
     * 
     * @since 0.1.0
     */
    private function build_repair_link() {
        $formatted_links = [];
        
        // Get current site's hostname
        $current_site = wp_parse_url( home_url(), PHP_URL_HOST );
        
        // Make sure the repair link exists and we are not on the repair page
        if ( $this->repair_link && ! $this->on_repair_page() ) {
            
            // Loop through repair links
            foreach ( $this->repair_link as $index => $repair_link ) {
                // Ensure there is corresponding text for the link (fallback to 'Repair' if text is missing)
                $repair_link_text = isset( $this->repair_link_text[ $index ] ) ? $this->repair_link_text[ $index ] : 'Repair';
                
                // Parse the repair link to get its hostname
                $parsed_link = wp_parse_url( $repair_link );
                $link_host = isset( $parsed_link['host'] ) ? $parsed_link['host'] : '';
                
                // Check if the link is external
                $is_external = $link_host && $link_host !== $current_site;

                // Build class
                $class = sprintf(
                    'nextav-repair-link-%s',
                    strtolower( str_replace( ' ', '-', $repair_link_text ) )
                );

                // Add class for admin info
                if ( $class === 'nextav-repair-link-disable-tips' ) {
                    $class .= ' nextav-dismiss-admin-btn';
                }
                
                // Build the anchor tag
                $formatted_links[] = sprintf(
                    '<a href="%1$s" class="%2$s"%3$s>%4$s</a>',
                    esc_url( $repair_link ),
                    esc_attr( $class ),
                    $is_external ? ' target="_blank" rel="noopener noreferrer"' : '',
                    esc_html( $repair_link_text )
                );
            }
        }
        
        return implode( ' | ', $formatted_links );
    }

    /**
     * Checks if we're on the repair page.
     * 
     * @since 0.1.0
     */
    private function on_repair_page() {
        // Exit if no repair link
        if ( ! $this->repair_link ) {
            return false;
        }
        $current_url = nextav_curr_url();
        foreach ( $this->repair_link as $repair_link ) {
            if ( basename( $current_url ) === basename( $repair_link ) ) {
                return true;
            }
        }
    }

    /**
     * Retrieves the notice class by color.
     * 
     * @since 0.1.0
     * 
     * @param   string  $color  Optional. The color for the class.
     *                          'red', 'orange', 'blue', or 'green'
     *                          Defaults to 'info'.
     */
    private function notice_class( $color = null ) {
        $classes = [
            'red'       => 'error',
            'orange'    => 'warning',
            'blue'      => 'info',
            'green'     => 'success'
        ];
        $class_type = $classes[$color] ?? 'info';
        return 'notice-' . $class_type;
    }

    /**
     * Dismisses an admin notice.
     * 
     * @since 1.0.27
     * 
     * @param   string  $notice_id  The ID of the notice to dismiss.
     */
    public static function dismiss( $notice_id ) {
        $key = $notice_id . '_dismissed';
        nextav_cache_set( $key, true, null, 'admin_notices', 'option' );
    }

    /**
     * Checks whether an admin notice is dimissed.
     * 
     * @since 1.0.27
     * 
     * @param   string  $key  The key of the notice.
     */
    public static function dismissed( $key ) {
        $notice_id = self::build_id( $key );
        $key = $notice_id . '_dismissed';
        return nextav_cache_get( $key, 'admin_notices', 'option' );
    }
}
