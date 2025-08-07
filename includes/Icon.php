<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Admin\Settings;

/**
 * Generates plugin icons.
 * 
 * Uses BuddyBoss or Font Awesome icons, depending on the enabled theme.
 *
 * @since 1.0.20
 */
class Icon {
     
    /**
     * The formatted icon html.
     * 
     * @var string
     */
     public $html;

    /**
     * The icon classes.
     * 
     * @var string
     */
    public $class;

    /**
     * The color of the icon.
     * 
     * @var string
     */
    public $color;

    /**
     * The array of icon data. 
     * 
     * @var array
     */
    private $icon_data;

    /**
     * Retrieves the icon data by key.
     * 
     * @since 1.0.20
     * 
     * @param   string  $key    The icon key.
     */
    private static function get_icon_data( $key ) {
        $data = [
            'admin-info' => [
                'bb-icon-class' => 'bb-icon-rf bb-icon-info',
                'fa-icon-class' => 'fa-solid fa-circle-info',
            ],
            'edit' => [
                'bb-icon-class' => 'bb-icon-l bb-icon-edit',
                'fa-icon-class' => 'fa-solid fa-pen-to-square',
            ],
            'question' => [
                'bb-icon-class' => 'bb-icon-f bb-icon-question',
                'fa-icon-class' => 'fa-solid fa-question',
            ],
            'check' => [
                'bb-icon-class' => 'bb-icon-check bb-icon-rf',
                'fa-icon-class' => 'fa-solid fa-circle-check',
                'color' => 'green',
            ],
            'x' => [
                'bb-icon-class' => 'bb-icon-times bb-icon-rf',
                'fa-icon-class' => 'fa-solid fa-circle-xmark',
                'color' => 'black',
            ],
            'eye' => [
                'bb-icon-class' => 'bb-icon-eye bb-icon-l',
                'fa-icon-class' => 'fa-regular fa-eye',
                'color' => 'gray',
            ],
            'eye-slash' => [
                'bb-icon-class' => 'bb-icon-eye-slash bb-icon-l',
                'fa-icon-class' => 'fa-regular fa-eye-slash',
                'color' => 'gray',
            ],
            'error' => [
                'bb-icon-class' => 'bb-icon-exclamation-triangle bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-triangle-exclamation',
                'color' => 'red',
            ],
            'ready' => [
                'bb-icon-class' => 'bb-icon-spinner bb-icon-l',
                'fa-icon-class' => 'fa fa-spinner',
                'color' => 'green',
            ],
            'default' => [
                'bb-icon-class' => 'bb-icon-circle bb-icon-l',
                'fa-icon-class' => 'fa-regular fa-circle',
                'color' => 'black',
            ],
            'info' => [
                'bb-icon-class' => 'bb-icon-info bb-icon-rl',
                'fa-icon-class' => 'fa-solid fa-circle-info',
                'color' => 'blue',
            ],
            'backward' => [
                'bb-icon-class' => 'bb-icon-backward bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-backward',
                'color' => 'gray'
            ],
            'clock' => [
                'bb-icon-class' => 'bb-icon-clock bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-clock',
            ],
            'paperclip' => [
                'bb-icon-class' => 'bb-icon-l bb-icon-paperclip',
                'fa-icon-class' => 'fa-solid fa-paperclip',
            ],
            'download' => [
                'bb-icon-class' => 'ms-download-icon bb-icon-download',
                'fa-icon-class' => 'ms-download-icon fa fa-download',
            ],
            'toggle_off' => [
                'bb-icon-class' => 'bb-icon-toggle-off bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-toggle-off',
            ],
            'toggle_on' => [
                'bb-icon-class' => 'bb-icon-toggle-on bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-toggle-on',
            ],
            'check' => [
                'bb-icon-class' => 'bb-icon-check bb-icon-rf',
                'fa-icon-class' => 'fa-solid fa-circle-check',
                'color' => 'green',
            ],
            'x' => [
                'bb-icon-class' => 'bb-icon-times bb-icon-rf',
                'fa-icon-class' => 'fa-solid fa-circle-xmark',
                'color' => 'black',
            ],
            'error' => [
                'bb-icon-class' => 'bb-icon-exclamation-triangle bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-triangle-exclamation',
                'color' => 'red',
            ],
            'ready' => [
                'bb-icon-class' => 'bb-icon-spinner bb-icon-l',
                'fa-icon-class' => 'fa fa-spinner',
                'color' => 'green',
            ],
            'square' => [
                'bb-icon-class' => 'bb-icon-stop bb-icon-l',
                'fa-icon-class' => 'fa-regular fa-square',
                'color' => 'green',
            ],
            'checkbox' => [
                'bb-icon-class' => 'bb-icon-checkbox bb-icon-l',
                'fa-icon-class' => 'fa-regular fa-square-checked',
                'color' => 'green',
            ],
            'rocket' => [
                'bb-icon-class' => 'bb-icon-rocket bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-rocket',
            ],
            'gear' => [
                'bb-icon-class' => 'bb-icon-cog bb-icon-l',
                'fa-icon-class' => 'fa-solid fa-gear',
            ],
            'default' => [
                'bb-icon-class' => 'bb-icon-circle bb-icon-l',
                'fa-icon-class' => 'fa-regular fa-circle',
                'color' => 'black',
            ],
        ];
        return $data[$key] ?? null;
    }
     
    /**
     * Constructor method.
     *
     * @since 0.1.0
     *
     * @param   string  $key    The identifying key of the icon.
     * @param   string  $color  Optional. The color of the icon.
     *                          Accepts 'blue', 'black', 'green', 'red', or 'gray'.
     */
    public function __construct( $key, $color = null ) {
        $this->key = $key;
        $this->color = $color;

        // Get the icon data by key
        $this->icon_data = self::get_icon_data( $key );

        // Build the icon
        if ( ! empty( $this->icon_data ) ) {            
            $this->class = $this->build_classes();
            $this->html = $this->build_icon();
        }
    }

    /**
     * Builds the icon from the key.
     * 
     * @since 1.0.20
     */
    private function build_icon() {
        // Build icon html
        return sprintf(
            '<i class="%s"></i>',
            $this->class
        );
    }

    /**
     * Builds the icon classes.
     * 
     * @since 1.0.25
     * 
     * @return  string  A string of class names.
     */
    private function build_classes() {
        // Define class type based on theme
        $class_type = nextav_buddyboss_theme() ? 'bb-icon-class' : 'fa-icon-class';

        $classes = [
            'nextav-icon', // general class
            $this->key, // icon key
            $class_type, // theme class
            $this->icon_data[$class_type] ?? '', // icon class
            $this->color_class() // color class
        ];

        return implode( ' ', $classes );
    }

    /**
     * Builds the color class.
     * 
     * @since 1.0.25
     * 
     * @return  string  The class defining the icon color.
     */
    private function color_class() {
        // Initialize
        $color = null;
        $color_class = '';

        // Check if the color was defined directly
        if ( ! empty( $this->color ) ) {
            $color = $this->color;
        
        // Otherwise check for icon-specific color
        } else if ( isset( $this->icon_data['color'] ) ) {
            $color = $this->icon_data['color'];
        }

        // Define the class if color exists
        if ( $color ) {
            $color_class = 'nextav-icon-color-' . $color;
        }

        // Return class
        return $color_class;
    }
}