<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Verifies a nonce from an AJAX request.
 *
 * @since 1.0.0
 *
 * @param   string  $action         The action suffix derived from the js file name.
 * @param   string  $nonce_field    The key in $_POST that holds the nonce value. Default 'nonce'.
 * @return  bool    True if valid, false otherwise.
 */
function nextav_verify_ajax_nonce( $action, $nonce_field = 'nonce' ) {

    $action_name = sprintf( 'nextav_%s', $action );
    $nonce = isset( $_POST[ $nonce_field ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ) ) : null;

    if ( ! $nonce ) {
        return false;
    }

    return wp_verify_nonce( $nonce, $action_name );
}
