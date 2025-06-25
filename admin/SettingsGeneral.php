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
        ];
    }
    
   /**
     * Defines the general settings.
     * 
     * @since 1.0.0
     */
    public static function settings() {
        return [
            'style' => [
                'title' => __('Style Settings', 'next-available'),
                'description' => __('Adjust global buddyclients styles to match your brand.', 'next-available'),
                'fields' => [
                    'primary_color' => [
                        'label' => __('Primary Color', 'next-available'),
                        'type' => 'color',
                        'class' => 'color-field',
                        'description' => '',
                    ],
                    'accent_color' => [
                        'label' => __('Accent Color', 'next-available'),
                        'type' => 'color',
                        'description' => '',
                    ],
                    'tertiary_color' => [
                        'label' => __('Tertiary Color', 'next-available'),
                        'type' => 'color',
                        'description' => '',
                    ],
                ]
            ],
            'admin' => [
                'title' => __('Admin', 'next-available'),
                'description' => '',
                'fields' => [
                    'admin_info' => [
                        'label' => __('Info Messages', 'next-available'),
                        'type' => 'dropdown',
                        'options' => [
                            'disable' => __('Disable', 'next-available'),
                            'enable' => __('Enable', 'next-available'),
                        ],
                        'description' => __('Display plugin info messages in the admin area.', 'next-available'),
                    ],
                ],
            ],
        ];
    }
}