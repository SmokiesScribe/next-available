<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Generates an admin notice for connection errors.
 * Centralizes the logic to output only one admin notice.
 * 
 * @since 1.0.0
 */
class ConnectionNotice {

    /**
     * The array of notice messages.
     * 
     * @var array
     */
    private $messages;

    /**
     * Cnstructor method.
     * 
     * @since 1.0.0
     * 
     * @param   array   $messages   An array of messages to output in the admin notice.
     */
    public function __construct( $messages ) {
        $this->messages = (array) $messages;
        $this->output();
    }

    /**
     * Outputs the admin notice.
     * 
     * @since 1.0.0
     */
    public function output() {
        $args = $this->build_args();
        $this->build( $args );
    }

    /**
     * Builds the admin notice message from the array.
     * 
     * @since 1.0.0
     */
    private function build_message() {
        if ( empty( $this->messages ) ) {
            return;
        }

        // Only one message – return a simple paragraph
        if ( count( $this->messages ) === 1 ) {
            return '<p>' . esc_html( $this->messages[0] ) . '</p>';
        }

        // Multiple messages – return a list
        $list = '<ul>';
        foreach ( $this->messages as $message ) {
            $list .= '<li>' . esc_html( $message ) . '</li>';
        }
        $list .= '</ul>';

        return $list;
    }

    /**
     * Builds the args for the admin notice.
     * 
     * @since 1.0.0
     */
    private function build_args() {
        return [
            'key'       => 'connection_error',
            'message'   => $this->build_message(),
            'color'     => 'orange',
          //  'display'   => 'plugin'
        ];
    }

    /**
     * Outputs the admin notice.
     * 
     * @since 1.0.0
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
    private function build( $args ) {
        if ( ! $args['message'] ) return;
        nextav_admin_notice( $args );
    }
}