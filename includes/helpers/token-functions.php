<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleTokenManager;

/**
 * Updates the stored Google token data.
 * 
 * @since 1.0.0
 * 
 * @param   array   $token_data The array of token data to store.
 */
function nextav_update_token_data( $token_data ) {
    return GoogleTokenManager::update_token_data( $token_data );
}

/**
 * Updates the stored Google refresh token.
 * 
 * @since 1.0.0
 * 
 * @param   string   $refresh_token The refresh_token to store.
 */
function nextav_update_refresh_token( $refresh_token ) {
    return GoogleTokenManager::update_refresh_token( $refresh_token );
}

/**
 * Deletes the stored Google token data and refresh token.
 * 
 * @since 1.0.0
 */
function nextav_delete_tokens() {
    return GoogleTokenManager::delete_tokens();
}