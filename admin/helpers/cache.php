<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use NextAv\Admin\VersionCache;

/**
 * Initializes VersionCache.
 * 
 * @since 1.0.25
 */
function nextav_version_cache() {
    return VersionCache::get_instance();
}
add_action( 'init', 'nextav_version_cache' );