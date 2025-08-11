<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\DisplayDate;

/**
 * Generates the Gutenberg block displaying the next available date.
 * 
 * @since 1.0.0
 */
class BlockDate {

    /**
     * The path to the directory where the block info lives.
     * 
     * @var string
     */
    private $block_dir;

    /**
     * The url of the directory where the block info lives.
     * 
     * @var string
     */
    private $block_url;

    /**
     * Constructor.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        // Constructor logic
    }

    /**
     * Renders the block.
     * 
     * @since 1.0.0
     */
    public function render_date_block( $attributes = [] ) {
        
        // Optional: get a title attribute from block settings
        $title = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : '';
        $show_updated_date = isset( $attributes['showUpdatedDate'] ) ? (bool) $attributes['showUpdatedDate'] : true;
        $date_format = isset( $attributes['dateFormat'] ) ? sanitize_text_field( $attributes['dateFormat'] ) : 'short';
        $style = isset( $attributes['style'] ) ? sanitize_text_field( $attributes['style'] ) : 'simple';
        $next_date_label = isset( $attributes['availableDateLabel'] ) ? sanitize_text_field( $attributes['availableDateLabel'] ) : __( 'Next Available:', 'nextav' );
        $updated_date_label = isset( $attributes['updatedDateLabel'] ) ? sanitize_text_field( $attributes['updatedDateLabel'] ) : __( 'Next Available:', 'nextav' );
        
        // Use your NextDate class or other logic to get the date output
        $display_date = new DisplayDate;
        $next_date = $display_date->display( ['format' => $date_format ]);
        $updated_date = $display_date->display_updated( ['format' => $date_format, 'date_only' => true ]);

        // Define classes based on style
        $class = "nextav-block $style";

        // Build output HTML
        ob_start();
        ?>
        <div class="nextav-date-block <?php echo $class ?>">
            <?php if ( $title ) : ?>
                <h3><?php echo esc_html( $title ); ?></h3>
            <?php endif; ?>

            <div class="date-items">
                <p class="nextav-block-date date-item">
                    <span class="label"><?php echo esc_html( $next_date_label ); ?></span>
                    <?php echo esc_html( $next_date ); ?>
                </p>
                <?php if ( $show_updated_date ) : ?>
                    <p class="nextav-block-updated-date date-item">
                        <span class="label"><?php echo esc_html( $updated_date_label ); ?></span>
                        <?php echo esc_html( $updated_date ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}