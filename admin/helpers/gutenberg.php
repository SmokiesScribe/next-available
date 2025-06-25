<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Checks if we're on a gutenberg page.
 * 
 * @since 0.1.0
 * 
 * @return bool
 */
function nextav_gutenberg_editor() {
    global $current_screen;
    $current_screen = get_current_screen();
    if ($current_screen) {
        if ( method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor() ) {
            return true;
        } else {
            return false;
        }
    }
}