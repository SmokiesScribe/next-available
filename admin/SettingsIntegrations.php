<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
        $cal = new GoogleCal;
        $redirect_url = $cal->redirect_url;
        return [
            'google_cal' => [
                'title' => __( 'Google Account', 'next-available' ),
                'description' => sprintf(
                    '%1$s <a href="%2$s">%3$s</a>',
                    __( 'Integrate with Google Calendar.', 'next-available' ),
                    $redirect_url,
                    __( 'Connect to your Google Calendar account.', 'next-available' )
                ),
                'fields' => [
                    'google_cal' => [
                        'label' => __( 'Connected Account', 'next-available' ),
                        'type' => 'display',
                        'content' => self::connected_account(),
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
            ],
            //'meta' => [
            //    'title' => __( 'Meta Ads Integration', 'next-available' ),
            //    'description' => __( 'Set up the API integration to send conversion events to Meta (Facebook).', 'next-available' ),
            //    'fields' => [
            //        'meta_access_token' => [
            //            'label' => __( 'Access Token', 'next-available' ),
            //            'type' => 'text',
            //            'description' => __( 'Enter your access token.', 'next-available' ),
            //        ],
            //        'meta_pixel_id' => [
            //            'label' => __( 'Pixel ID', 'next-available' ),
            //            'type' => 'text',
            //            'description' => __( 'Enter your pixel ID.', 'next-available' ),
            //        ],
            //    ],
            //],
        ];
    }

    /**
     * Builds the calendar list options.
     * 
     * @since 1.0.0
     */
    private static function cal_list() {
        $cal = new GoogleCal;
        return $cal->calendar_list();
    }

    /**
     * Outputs the connected account info.
     * 
     * @since 1.0.0
     */
    private static function connected_account() {
        $cal = new GoogleCal;
        return $cal->primary_name() ?? __( 'No account connected.', 'next-available' );
    }
}