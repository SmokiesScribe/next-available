<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleCal;
use NextAv\Includes\GoogleAuthManager;

/**
 * Defines the advanced settings.
 *
 * @since 1.0.25
 */
class SettingsAdvanced {

    /**
     * Defines default Integrations settings.
     * 
     * @since 1.0.25
     */
    public static function defaults() {
        return [
            'bypass_proxy'  => 'no'
        ];
    }
    
    /**
     * Defines integrations settings.
     * 
     * @since 1.0.25
     */
    public static function settings() {
        return [
            'bypass_proxy' => [
                'title' => __( 'Bypass Proxy Server', 'next-available' ),
                'description' => __( 'Connect directly to your Google Cloud account to bypass the proxy server.', 'next-available' ),
                'fields' => [
                    'bypass' => [
                        'label' => __( 'Bypass Proxy', 'next-available' ),
                        'type' => 'dropdown',
                        'options' => [
                            'no'    => 'Use Proxy (recommended)',
                            'yes'   => 'Bypass Proxy'
                        ],
                        'description' => __( '', 'next-available' ),
                    ],
                    'user_google_client_id' => [
                        'label' => __( 'Client ID', 'next-available' ),
                        'type' => 'input',
                        'description' => __( 'Enter your Google client ID.', 'next-available' ),
                    ],
                    'user_google_client_secret' => [
                        'label' => __( 'Client Secret', 'next-available' ),
                        'type' => 'input',
                        'description' => __( 'Enter your Google client secret.', 'next-available' ),
                    ],
                    'user_google_redirect_uri' => [
                        'label' => __( 'Client Secret', 'next-available' ),
                        'type' => 'display',
                        'content' => self::redirect_url(),
                        'description' => __( 'Add this url to your list of authorized redirect URLs.', 'next-available' ),
                    ],
                ],
            ],
        ];
    }

    /**
     * Outputs the Google auth redirect url.
     * 
     * @since 1.0.0
     */
    private static function redirect_url() {
        $auth_manager = new GoogleAuthManager;
        $connect_url = $auth_manager->connect_url;
        return "<p><code>$connect_url</code></p>";
    }
}