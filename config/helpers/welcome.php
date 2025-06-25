<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use NextAv\Admin\WelcomeMessage;
/**
 * Initializes the WelcomeMessage.
 * 
 * @since 1.0.25
 */
function nextav_init_welcome_message() {
    if ( class_exists( WelcomeMessage::class ) ) {
        new WelcomeMessage;
    }
}
add_action( 'init', 'nextav_init_welcome_message' );