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
            'enable_recaptcha'      => 'disable',
            'recaptcha_threshold'   => '0.5',
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
                'title' => __( 'Google Calendar', 'next-available' ),
                'description' => sprintf(
                    '%1$s <a href="%2$s">%3$s</a>',
                    __( 'Integrate with Google Calendar.', 'next-available' ),
                    $redirect_url,
                    __( 'Connect to your Google Calendar.', 'next-available' )
                ),
                'fields' => [
                    'enable_recaptcha' => [
                        'label' => __( 'Enable reCAPTCHA', 'next-available' ),
                        'type' => 'dropdown',
                        'options' => [
                            'disable' => __( 'Disable', 'next-available' ),
                            'enable'    => __( 'Enable', 'next-available' ),
                        ],
                        'description' => __( 'Enable reCAPTCHA to protect your forms from spam.', 'next-available' ),
                    ],
                    'recaptcha_site_key' => [
                        'label' => __( 'Site Key', 'next-available' ),
                        'type' => 'text',
                        'description' => __( 'Enter your site key.', 'next-available' ),
                    ],
                    'recaptcha_secret_key' => [
                        'label' => __( 'Secret Key', 'next-available' ),
                        'type' => 'text',
                        'description' => __( 'Enter your secret key.', 'next-available' ),
                    ],
                    'recaptcha_threshold' => [
                        'label' => __( 'reCAPTCHA Threshold', 'next-available' ),
                        'type' => 'dropdown',
                        'options' => [
                            '0.9'     => sprintf( '0.9 - %s',
                                                __( 'Most sensitive', 'next-available' )
                                            ),
                            '0.8'     => '0.8',
                            '0.7'     => '0.7',
                            '0.6'     => '0.6',
                            '0.5'     => sprintf( '0.5 - %s',
                                                __( 'Default', 'next-available' )
                                            ),
                            '0.4'     => '0.4',
                            '0.3'     => '0.3',
                            '0.2'     => '0.2',
                            '0.1'     => sprintf( '0.1 - %s',
                                                __( 'Least sensitive', 'next-available' )
                                            ),
                        ],
                        'description' => __( 'Determine how sensitive the spam filter should be. Thresholds above 0.5 may block valid submissions.', 'next-available' ),
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
}