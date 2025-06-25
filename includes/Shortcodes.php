<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Registers plugin shortcodes.
 * 
 * Defines and registers all shortcodes for the plugin.
 *
 * @since 1.0.0
 */
class Shortcodes {
    
    /**
     * Initializes the shortcode registration.
     *
     * @since 1.0.0
     */
    public static function run() {        
        // Not admin area or login
        if ( ! is_admin() && $GLOBALS['pagenow'] === 'index.php' ) {
            // Register shortcodes
            self::register();
        }
    }

    /**
     * Defines shortcodes data. 
     * 
     * @since 1.0.25
     */
    public static function shortcodes_data() {
        $data = [
            'booking' => [
                'shortcode' => 'nextav_booking_form',
                'class'     => BookingForm::class,
                'method'    => 'build_form'
            ],
        ];

        /**
         * Filters the shortcodes.
         *
         * @since 0.3.4
         *
         * @param array  $shortcodes    An associative array of shortcodes and callbacks.
         */
        $data = apply_filters( 'nextav_shortcodes', $data );

        return $data;
    }
    
    /**
     * Registers all shortcodes.
     * 
     * @since 1.0.0
     */
    public static function register() {
        foreach ( self::shortcodes_data() as $key => $data ) {
            if ( ! isset( $data['class'] ) || class_exists( $data['class'] ) ) {
                $callable = self::build_callable( $data );
                if ( is_callable( $callable ) ) {
                    add_shortcode( $data['shortcode'], $callable );
                }
            }
        }
    }

    /**
     * Builds the callable from the shortcode data.
     * 
     * @since 1.0.25
     *
     * @param array $data The data that includes the class, method, or function details.
     * @return callable|null The callable, or null if no valid callable can be constructed.
     */
    private static function build_callable( $data ) {
        if ( isset( $data['class'], $data['method'] ) ) {
            // Ensure the method is callable within the class.
            if ( method_exists( $data['class'], $data['method'] ) ) {
                return [new $data['class'], $data['method']];
            }
            return null; // Return null if the method doesn't exist in the class.
        } elseif ( isset( $data['function'] ) && is_callable( $data['function'] ) ) {
            return $data['function'];
        }
        
        return null; // Return null if no valid callable can be constructed.
    }

    /**
     * Formats a shortcode with brackets.
     * 
     * @since 1.0.27
     * 
     * @param   string  $shortcode  The shortcode to format with brackets.
     */
    private static function format_shortcode( $shortcode ) {
        return sprintf(
            '[%s]',
            $shortcode
        );
    }

    /**
     * Retrieves the shortcode by key.
     * 
     * @since 1.0.27
     * 
     * @param   string  $key    The shortcode key.
     */
    public static function get_shortcode( $key ) {
        $shortcodes_data = self::shortcodes_data();
        if ( isset( $shortcodes_data[$key] ) ) {
            return self::format_shortcode( $shortcodes_data[$key]['shortcode'] );
        }
    }

    /**
     * Retrieves all shortcodes.
     * 
     * @since 1.0.27
     */
    public static function get_all_shortcodes() {
        $shortcodes_data = self::shortcodes_data();
        $shortcodes = [];
        foreach ( $shortcodes_data as $key => $data ) {
            if ( isset( $data['shortcode'] ) ) {
                $shortcodes[] = self::format_shortcode( $data['shortcode'] );
            }
        }
        return $shortcodes;
    }

    /**
     * Checks whether a shortcode is present in the page content.
     * 
     * @since 1.0.27
     * 
     * @param   string  $shortcode_key  The shortcode key.
     */
    public static function shortcode_exists( $shortcode_key ) {
        $page_content = get_the_content();
        if ( empty( $page_content ) ) return false;
        
        $shortcode = self::get_shortcode( $shortcode_key );
        return strpos( $shortcode, $page_content ) !== false;
    }

    /**
     * Checks whether any plugin shortcode is present in the page content.
     * 
     * @since 1.0.27
     */
    public static function any_shortcode_exists() {
        $page_content = get_the_content();
        if ( empty( $page_content ) ) return false;

        // GEt all shortcodes
        $shortcodes = self::get_all_shortcodes();

        // Check for each shortcode
        foreach ( $shortcodes as $shortcode ) {
            if ( strpos( $shortcode, $page_content ) !== false ) {
                // Return true on first shortcode found
                return true;
            }
        }
        // No shortcodes found
        return false;
    }
}