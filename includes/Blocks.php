<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\BlockDate;
use NextAv\Includes\BlockCalendar;

/**
 * Initializez all Gutenberg blocks.
 * 
 * @since 1.0.0
 */
class Blocks {

    /**
     * The path to the directory where the block info lives.
     * 
     * @var string
     */
    private $blocks_dir;

    /**
     * The url of the directory where the block info lives.
     * 
     * @var string
     */
    private $blocks_url;

    /**
     * Constructor.
     * Defines properties and initiates the blocks.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        $this->blocks_dir = trailingslashit( NEXTAV_PLUGIN_DIR ) . 'blocks';
        $this->blocks_url = trailingslashit( NEXTAV_PLUGIN_URL ) . 'blocks';
        $this->init();
    }

    /**
     * Adds hook to initialize the blocks.
     * 
     * @since 1.0.0
     */
    private function init() {
        add_action( 'init', [ $this, 'register_blocks' ] );
    }

    /**
     * Defines all block data. 
     * 
     * @since 1.0.0
     */
    private static function block_data() {
        return [
            'date'  => [
                'callback_class'    => 'NextAv\Includes\BlockDate',
                'callback_method'   => 'render_date_block'
            ],
            'calendar'  => [
                'callback_class'    => 'NextAv\Includes\BlockCalendar',
                'callback_method'   => 'render_block'
            ]
        ];
    }

    /**
     * Registers all the blocks.
     * 
     * @since 1.0.0
     */
    public function register_blocks() {
        $block_data = self::block_data();
        foreach ( $block_data as $key => $block_args ) {
            $this->register_block( $key, $block_args );
        }
    }

    /**
     * Registers a single block.
     * 
     * @since 1.0.0
     * 
     * @param   string  $key         The block key.
     * @param   array       $block_args {
     *     @type string $rel_path        The relative path to the block dir.
     *     @type string $callback_class  The fully qualified class name.
     *     @type string $callback_method The method name to call.
     * }
     */
    public function register_block( $key, $block_args ) {

        // Build block path and url
        $block_path = $this->build_path( $key );
        $block_url = $this->build_url( $key );
        if ( ! $block_path || ! $block_url ) return;

        // Register the script
        $this->register_script( $key, $block_path, $block_url );

        // Get the callback
        $callable = $this->get_callback( $block_args );
        if ( ! $callable ) return;

        // Register the block
        register_block_type( $block_path, [
            'render_callback' => $callable,
        ] );
    }

    /**
     * Builds a callable from the given block args.
     * 
     * @since 1.0.0
     * 
     * @param   array       $block_args {
     *     @type string $callback_class  The fully qualified class name.
     *     @type string $callback_method The method name to call.
     * }
     * @return  callable|null The callable, or null on failure.
     */
    private function get_callback( array $block_args ) {

        // Make sure callback class and method proivded
        if ( empty( $block_args['callback_class'] ) || empty( $block_args['callback_method'] ) ) {
            return null;
        }

        // Retrieve callback info
        $callback_class  = $block_args['callback_class'];
        $callback_method = $block_args['callback_method'];

        // Make sure class exists
        if ( ! class_exists( $callback_class ) ) {
            return null;
        }

        // Instantiate class
        $instance = new $callback_class();

        // Make sure method exists
        if ( ! method_exists( $instance, $callback_method ) ) {
            return null;
        }

        // Build callable
        $callable = [ $instance, $callback_method ];

        // Return if callable
        return is_callable( $callable ) ? $callable : null;
    }

    /**
     * Builds a url from a relative path.
     * 
     * @since 1.0.0
     * 
     * @param   string  $key   The block key.
     * @return  string  The full url.
     */
    private function build_url( $key ) {
        $url = trailingslashit( $this->blocks_url ) . $key;
        return $url;
        return is_dir( $url ) ? $url : null;
    }

    /**
     * Builds a full path from a relative path.
     * 
     * @since 1.0.0
     * 
     * @param   string  $key   The block key.
     * @return  string  The full path.
     */
    private function build_path( $key ) {
        $path = trailingslashit( $this->blocks_dir ) . $key;
        return is_dir( $path ) ? $path : null;
    }

    /**
     * Registers the script.
     * 
     * @since 1.0.0
     * 
     * @param   string  $key        The block key.
     * @param   string  $block_path The path to the block dir. 
     * @param   string  $block_url  The url of the block dir.
     */
    private function register_script( $key, $block_path, $block_url ) {

        // Build script args
        $script_key = "nextav-$key-block-editor";
        $script_url = trailingslashit( $block_url ) . 'index.js';
        $classes = [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ];
        $version = filemtime( trailingslashit( $block_path ) . 'index.js' ); // version based on file modified time

        // Register block editor script
        wp_register_script(
            $script_key,
            $script_url,
            $classes,
            $version
        );
    }
}