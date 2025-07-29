<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Defines the general settings.
 *
 * @since 1.0.0
 */
class SettingsGeneral {

    /**
     * Defines default General settings.
     * 
     * @since 1.0.0
     */
    public static function defaults() {
        return [
            'admin_info'            => 'enable',
            'enable_cta'            => 'enable',
            'primary_color'         => '#037AAD',
            'accent_color'          => '#067F06',
            'tertiary_color'        => '#0e5929',

            'free_days'             => 7,
            'date_format'           => 'F j, Y',
            'update_frequency'      => 3,
            'date_fallback'         => __( 'Contact for availability', 'next-available' )
        ];
    }
    
   /**
     * Defines the general settings.
     * 
     * @since 1.0.0
     */
    public static function settings() {
        return [
            'calculation' => [
                'title' => __('Calendar Settings', 'next-available'),
                'description' => __('Choose how to calculate the next available date.', 'next-available'),
                'fields' => [
                    'free_days' => [
                        'label' => __('Required Free Days', 'next-available'),
                        'type' => 'number',
                        'description' => __('Specify how many consecutive days must be free of scheduled events to be considered AVAILABLE.', 'next-available'),
                    ],
                    'update_frequency' => [
                        'label' => __('Update Frequency (days)', 'next-available'),
                        'type' => 'number',
                        'description' => __('Choose how often to update calendar data.', 'next-available'),
                    ],
                ],
            ],
            'display' => [
                'title' => __('Display Settings', 'next-available'),
                'description' => __('Adjust the default display settings.', 'next-available'),
                'fields' => [
                    'date_format' => [
                        'label' => __('Date Format', 'next-available'),
                        'type' => 'dropdown',
                        'options' => [
                            'Y-m-d' => __('2025-07-01 (ISO 8601 - YYYY-MM-DD)', 'next-available'),
                            'm/d/Y' => __('07/01/2025 (MM/DD/YYYY)', 'next-available'),
                            'd/m/Y' => __('01/07/2025 (DD/MM/YYYY)', 'next-available'),
                            'F j, Y' => __('July 1, 2025 (Month Day, Year)', 'next-available'),
                            'j F Y' => __('1 July 2025 (Day Month Year)', 'next-available'),
                            'D, M j, Y' => __('Tue, Jul 1, 2025 (Short weekday)', 'next-available'),
                            'l, F j, Y' => __('Tuesday, July 1, 2025 (Full weekday)', 'next-available'),
                        ],
                        'description' => __('Choose the default format for the displayed date. (The format can be specified in the shortcode.)', 'next-available'),
                    ],
                    'date_fallback' => [
                        'label' => __('Date Fallback', 'next-available'),
                        'type' => 'input',
                        'description' => __('Enter text to display in the event that your Calendar integration fails.', 'next-available'),
                    ],
                ]
            ],
        ];
    }
}