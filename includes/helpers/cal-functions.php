<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\GoogleAuthManager;

/**
 * Checks whether to bypass the proxy server.
 * 
 * @since 1.0.0
 * 
 * @return  bool    True if bypassing proxy, false if not.
 */
function nextav_bypass_proxy() {
    return GoogleAuthManager::bypass_proxy();
}