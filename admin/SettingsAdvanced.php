<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleCal;

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
        $disconnect_url = $cal->disconnect_url;
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
        $cal = new GoogleCal;
        $redirect_url = $cal->redirect_url();
        return "<p><code>$redirect_url</code></p>";
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
     * @param   GoogleCal   $cal    The GoogleCal instance.
     */
    private static function connect_btns( $cal ) {
        if ( $cal->primary_name() ) {
            return self::disconnect_btn( $cal );
        } else {
            return self::connect_btn( $cal );
        }
    }

    /**
     * Outputs the connect button.
     * 
     * @since 1.0.0
     * 
     * @param   GoogleCal   $cal    The GoogleCal instance.
     */
    private static function connect_btn( $cal ) {
        $redirect_url = $cal->redirect_url;
        $text = __( 'Connect Google Account', 'next-available');
        return self::admin_btn( $redirect_url, $text, 'primary' );
    }

    /**
     * Outputs the disconnect button.
     * 
     * @since 1.0.0
     * 
     * @param   GoogleCal   $cal    The GoogleCal instance.
     */
    private static function disconnect_btn( $cal ) {
        // Make sure an account is connected
        if ( $cal->primary_name() ) {
            $disconnect_url = $cal->disconnect_url;
            $text = __( 'Disconnect Account', 'next-available');
            return self::admin_btn( $disconnect_url, $text, 'secondary' );
        }
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