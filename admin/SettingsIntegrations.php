<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleAuthManager;
use NextAv\Includes\GoogleCal;

/**
 * Defines the general settings.
 *
 * @since 1.0.25
 */
class SettingsIntegrations {

    /**
     * Defines default Integrations settings.
     * 
     * @since 1.0.25
     */
    public static function defaults() {
        return [
            
        ];
    }
    
    /**
     * Defines integrations settings.
     * 
     * @since 1.0.25
     */
    public static function settings() {
        $auth_manager = new GoogleAuthManager;
        $cal = new GoogleCal;

        return [
            'google_cal' => [
                'title' => __( 'Google Account', 'next-available' ),
                'description' => __( 'Integrate with Google Calendar.', 'next-available' ),
                'fields' => [
                    'google_cal' => [
                        'label' => __( 'Connected Account', 'next-available' ),
                        'type' => 'display',
                        'content' => self::connected_account( $cal ),
                        'description' => __( '', 'next-available' ),
                    ],
                    'google_connect_btns' => [
                        'label' => __( '', 'next-available' ),
                        'type' => 'display',
                        'content' => self::connect_btns( $auth_manager, $cal ),
                        'description' => __( '', 'next-available' ),
                    ],
                ],
            ],
            'calendars' => [
                'title' => __( 'Calendars', 'next-available' ),
                'description' => 'Manage your Google Calendars.',
                'fields' => [
                    'calendar_id' => [
                        'label' => __( 'Google Calendar', 'next-available' ),
                        'type' => 'dropdown',
                        'options' => self::cal_list(),
                        'description' => __( 'Choose the Google Calendar from your connected account.', 'next-available' ),
                    ],
                ],
            ]
        ];
    }

    /**
     * Builds the calendar list options.
     * 
     * @since 1.0.0
     */
    private static function cal_list() {
        $cal = new GoogleCal;
        $cal_list = $cal->calendar_list();
        $empty_option = ! empty( $cal_list ) ? __( '— Select a calendar —', 'next-available' ) : __( '— No calendars available —', 'next-available' );
        return array_merge(
            [ '' => $empty_option ],
            is_array($cal_list) && !empty($cal_list) ? $cal_list : []
        );
    }

    /**
     * Outputs the connected account info.
     * 
     * @since 1.0.0
     * 
     * @param   GoogleCal   $cal    The GoogleCal instance.
     */
    private static function connected_account( $cal ) {
        return $cal->primary_name() ?? __( 'No account connected.', 'next-available' );
    }

    /**
     * Outputs the connect and disconnect buttons.
     * 
     * @since 1.0.0
     * 
     * @param   GoogleAuthManager   $auth_manager    The GoogleAuthManager instance.
     * @param   GoogleCal           $cal             The GoogleCale instance.
     */
    private static function connect_btns( $auth_manager, $cal ) {
        if ( $cal->primary_name() ) {
            return self::disconnect_btn( $auth_manager->disconnect_url );
        } else {
            return self::connect_btn( $auth_manager->connect_url );
        }
    }

    /**
     * Outputs the connect button.
     * 
     * @since 1.0.0
     * 
     * @param   string   $connect_url    The redirect url to connect to Google.
     */
    private static function connect_btn( $connect_url ) {
        $text = __( 'Connect Google Account', 'next-available');
        return self::admin_btn( $connect_url, $text, 'primary' );
    }

    /**
     * Outputs the disconnect button.
     * 
     * @since 1.0.0
     * 
     * @param   string   $disconnect_url    The redirect url to disconnect to Google.
     */
    private static function disconnect_btn( $disconnect_url ) {
        $text = __( 'Disconnect Account', 'next-available');
        return self::admin_btn( $disconnect_url, $text, 'secondary' );
    }

    /**
     * Outputs the html for an admin button. 
     * 
     * @since 1.0.0
     * 
     * @param   string  $url    The button url.
     * @param   string  $text   The button text.
     * @param   string  $type   The button type. 'primary' or 'secondary'.
     *                          Defaults to 'primary'.
     */
    private static function admin_btn( $url, $text, $type = 'primary' ) {
        return sprintf(
            '<a href="%1$s" class="button button-%2$s">%3$s</a>',
            $url,
            $type,
            $text
        );
    }
}