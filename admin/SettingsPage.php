<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Admin\Settings;

/**
 * Creates a single admin settings page.
 * 
 * @since 1.0.0
 */
class SettingsPage {
    
    /**
     * Setting data.
     * 
     * @var array Associative array of settings data.
     */
     private $data;
     
    /**
     * Key used to build slug.
     * 
     * @var string
     */
     private $key;
     
    /**
     * Name of settings group.
     * 
     * @var string
     */
     private $name;
    
    /**
     * Slug.
     * 
     * @var string.
     */
     private $slug;
     
    /**
     * Parent menu slug.
     * 
     * @var string.
     */
     private $parent_menu;
     
    /**
     * Page title.
     * 
     * @var string.
     */
     private $title;
     
    /**
     * Menu order.
     * 
     * @var int|null
     */
     private $menu_order;
     
    /**
     * Capability.
     * 
     * @var string Optional. Default 'manage_options'.
     */
     private $cap;

    /**
     * Constructor method.
     * 
     * @since 1.0.0
     */
    public function __construct( $args ) {

        // Extract data
        $this->key = $args['key'] ?? '';
        $this->title = $args['title'] ?? 'Settings';
        $this->name = $this->build_settings_name();
        
        // Get settings data
        $settings = new Settings( $args['key'] );
        $this->data = $settings->get_data();
        
        // Define hooks
        $this->define_hooks();
    }
    
    /**
     * Builds settings name.
     * 
     * @since 1.0.0
     */
    private function build_settings_name() {
        $key = str_replace('-', '_', $this->key );
        return 'nextav_' . $key . '_settings';
    }
    
    /**
     * Registers hooks.
     * 
     * @since 1.0.0
     */
    private function define_hooks() {
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Registers settings.
     * 
     * @since 1.0.0
     * @since 1.0.17 Use sanitization callback.
     */
    public function register_settings() {
        register_setting( $this->name . '_group', $this->name, [
            'sanitize_callback' => [ $this, 'sanitize_settings' ]
        ]);
    
        add_settings_section( $this->name . '_section', '', [ $this, 'section_callback' ], $this->name );
    } 

    /**
     * Sanitizes settings.
     *
     * Ensures that empty checkboxes are saved as an empty array.
     * 
     * @since 1.0.17
     *
     * @param array $input The raw settings input.
     * @return array Sanitized settings.
     */
    public function sanitize_settings( $input ) {

        // Loop through settings fields and ensure checkboxes are set to an empty array if no value is submitted
        foreach ( $this->data as $section_key => $section_data ) {

            foreach ( $section_data['fields'] as $field_id => $field_data ) {

                if ( $field_data['type'] === 'checkboxes' ) {

                    if ( ! isset( $input[ $field_id ] ) ) {
                        $input[ $field_id ] = [];
                    }
                }
            }
        }

        return $input;
    }

    /**
     * Renders the settings page.
     * 
     * @since 1.0.0
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( $this->title ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( $this->name . '_group' ); ?>
                <?php do_settings_sections( $this->name ); ?>
                <?php submit_button( __('Save Settings', 'buddyclients-free') ); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Renders the settings content.
     * 
     * @since 1.0.0
     */
     public function section_callback() {
        /**
         * Fires at the top of every BuddyClients settings page.
         *
         * @since 1.0.0
         *
         * @param string $settings_key  The key of the settings group.
         */
        do_action('nextav_before_settings', $this->key);
        
        // Make sure we have an array of settings data
        if (is_array($this->data)) {
            // Loop through settings data
            foreach ($this->data as $section_key => $section_data) {
                // Output section header
                $section_header = $this->section_group( $section_key, $section_data ) ?? '';
                echo wp_kses_post( $section_header );
            }
        // No settings data available
        } else {
            echo wp_kses_post( __('Not available.', 'buddyclients-free') );
        }
    }
    
    /**
     * Displays section group.
     * 
     * @since 1.0.0
     */
     public function section_group(string $section_key, $section_data) {
        ?>
        <div class="buddyclients-settings-section">
            <div class="buddyclients-settings-section-title-wrap">
                <h2 class="buddyclients-settings-section-title"><?php echo esc_html($section_data['title'] ?? ''); ?></h2>
                <p class="description"><?php echo wp_kses_post( $section_data['description'] ); ?></p>
                <hr class="buddyclients-settings-section-title-divider">
            </div>
            
            <?php $this->section_group_field($section_key, $section_data); ?>
            
        </div>
        <?php
    }

    /**
     * Displays individual field.
     * 
     * @since 1.0.0
     */
    public function section_group_field($section_key, $section_data) {
        // Initialize output
        $output = '';

        // Loop thorugh section fields
        foreach ( $section_data['fields'] as $field_id => $field_data ) {
            
            // Define field info
            $type = $field_data['type'];
            $settings_key = $this->key;
            
            // Get current field value
            $value = nextav_get_setting( $settings_key, $field_id );
            
            // Define output by field type
            switch ( $type ) {
                case 'display':
                    $output .= $this->display($type, $field_id, $field_data, $value);
                    break;
                case 'checkboxes':
                    $output .= $this->checkbox_field($type, $field_id, $field_data, $value);
                    break;
                case 'checkbox_table':
                    $output .= $this->checkbox_table($type, $field_id, $field_data, $value);
                    break;
                case 'dropdown':
                    $output .= $this->select_field($type, $field_id, $field_data, $value);
                    break;
                case 'text':
                case 'number':
                case 'date':
                case 'email':
                    $output .= $this->input_field($type, $field_id, $field_data, $value);
                    break;
                case 'stripe_input':
                    $output .= $this->stripe_input_field($type, $field_id, $field_data, $value);
                    break;
                case 'stripe_dropdown':
                    $output .= $this->stripe_select_field($type, $field_id, $field_data, $value);
                    break;
                case 'hidden':
                    $output .= $this->hidden_field($type, $field_id, $field_data, $value);
                    break;
                case 'color':
                    $output .= $this->color_field($field_id, $field_data, $value);
                    break;
                case 'page':
                    $output .= $this->select_field($type, $field_id, $field_data, $value);
                    break;
                case 'legal':
                    $output .= $this->legal_field($type, $field_id, $field_data, $value);
                    break;
                case 'copy':
                    $output .= $this->copy_field($type, $field_id, $field_data);
                    break;
                default:
                    $output .= $this->input_field('text', $field_id, $field_data, $value);
                    break;
            }
        }
        // Echo output
        echo wp_kses_post( $output );
    }
    
    /**
     * Displays content directly.
     *
     * @since 1.0.0
     */
    public function display($type, $field_id, $field_data, $value) {
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr( $this->name . '[' . $field_id . ']' ); ?>">
                <?php echo esc_html( $field_data['label'] ); ?>
            </label>
            <div class="buddyclients-admin-field-input-wrap">
                <?php echo wp_kses_post($field_data['content']); ?>
                <p class="description"><?php echo wp_kses_post( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renders a checkbox field.
     *
     * @since 1.0.0
     */
    public function checkbox_field($type, $field_id, $field_data, $value) {
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                <?php echo esc_html($field_data['label']); ?>
            </label>
            <div class="buddyclients-admin-field-input-wrap">
                <?php foreach ($field_data['options'] as $option_key => $option_label) : 
                    $checked = is_array($value) && in_array($option_key, $value) ? 'checked' : ''; ?>
                    <label>
                        <input type="checkbox" name="<?php echo esc_attr($this->name . '[' . $field_id . '][]'); ?>" 
                               value="<?php echo esc_attr($option_key); ?>" <?php echo esc_attr( $checked ); ?>>
                        <?php echo wp_kses_post( $option_label ); ?>
                    </label><br>
                <?php endforeach; ?>
                <p class="description"><?php echo wp_kses_post( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renders a checkbox field as a table.
     *
     * @since 1.0.0
     */
    public function checkbox_table($type, $field_id, $field_data, $value) {
        ?>
        <div class="buddyclients-admin-field">
            <table class="nextav-checkbox-table">
                <tbody>
                    <?php foreach ($field_data['options'] as $option_key => $option_label) : 
                        $required = in_array($option_key, ( $field_data['required_options'] ?? [] ));
                        $checked = is_array($value) && in_array($option_key, $value) || $required ? 'checked' : ''; ?>
                        <tr class="<?php echo $checked ? 'checked' : ''; ?> <?php echo $required ? 'required' : ''; ?>">
                            <td>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr($this->name . '[' . $field_id . '][]'); ?>" 
                                           value="<?php echo esc_attr($option_key); ?>" <?php echo esc_attr($checked); ?>>
                                    <?php echo esc_html($option_label); ?>
                                </label>
                            </td>
                            <td>
                                <p class="description">
                                    <?php echo $required ? 'Required. ' : ''; ?>
                                    <?php echo isset($field_data['descriptions'][$option_key]) ? $field_data['descriptions'][$option_key] : ''; ?>
                                </p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Renders a dropdown field.
     *
     * @since 1.0.0
     */
    public function select_field($type, $field_id, $field_data, $value) {
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                <?php echo esc_html($field_data['label']); ?>
            </label>
            <div class="buddyclients-admin-field-input-wrap">
                <select name="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                    <?php foreach ($field_data['options'] as $option_key => $option_label) : 
                        $selected = ($value == $option_key) ? ' selected' : ''; ?>
                        <option value="<?php echo esc_attr($option_key); ?>" <?php echo esc_attr($selected); ?>>
                            <?php echo esc_html($option_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($type === 'page') {
                    self::page_button($field_id, $field_data, $value);
                } ?>
                <p class="description"><?php echo wp_kses_post( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renders an input field.
     *
     * @since 1.0.0
     *
     * @param string $type Accepts 'text', 'date', 'number'.
     */
    public function input_field($type, $field_id, $field_data, $value) {
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                <?php echo esc_html($field_data['label']); ?>
            </label>
            <div class="buddyclients-admin-field-input-wrap">
                <input type="<?php echo esc_attr($type); ?>" 
                       name="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>" 
                       value="<?php echo esc_attr($value); ?>" />
                <p class="description"><?php echo wp_kses_post( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renders a Stripe key input field.
     *
     * @since 1.0.0
     */
    public function stripe_input_field( $type, $field_id, $field_data, $value ) {
        $icon = $this->validate_stripe_icon( 'field', $field_data, $value );
        
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                <?php echo esc_html($field_data['label']); ?>
            </label>
            <div class="buddyclients-admin-field-input-wrap">
                <input type="<?php echo esc_attr($type); ?>" 
                       name="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>" 
                       value="<?php echo esc_attr($value); ?>" />
                <?php echo wp_kses_post( $icon ); ?>
                <p class="description"><?php echo wp_kses_post( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renders a Stripe dropdown field.
     *
     * @since 1.0.0
     */
    public function stripe_select_field($type, $field_id, $field_data, $value) {
        $icon = $this->validate_stripe_icon( 'mode' );
        
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                <?php echo esc_html($field_data['label']); ?>
            </label>
            <div class="buddyclients-admin-field-input-wrap">
                <select name="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                    <?php foreach ($field_data['options'] as $option_key => $option_label) : 
                        $selected = ($value == $option_key) ? ' selected' : ''; ?>
                        <option value="<?php echo esc_attr($option_key); ?>" <?php echo esc_attr( $selected ); ?>>
                            <?php echo esc_html($option_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php echo wp_kses_post( $icon ); ?>
                <?php if ($type === 'page') {
                    self::page_button($field_id, $field_data, $value);
                } ?>
                <p class="description"><?php echo wp_kses_post( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Checks for Stripe validation and outputs an icon.
     * 
     * @since 1.0.15
     * 
     * @param   string  $type           The type of validation.
     *                                  Accepts 'mode' and 'field'.
     * @param   array   $field_data     The data for field validation.
     * 
     * @return  string  Icon html or empty string.
     */
    private function validate_stripe_icon( $type, $field_data = null, $value = null ) {
        // Initialize
        $icon = '';

        // Check for validate url param
        $param_manager = nextav_param_manager();
        $validate_param = $param_manager->get( 'validate' );

        // Make sure we're validating
        if ( $validate_param !== 'stripe' ) {
            return $icon;
        }

        // Validate full stripe mode
        if ( $type === 'mode' ) {
            $icon = nextav_stripe_mode_valid_icon();
        }

        // Validate field
        if ( $type === 'field' && is_array( $field_data ) && isset( $field_data['stripe_key'] ) ) {
            $icon = nextav_stripe_valid_icon( $field_data['stripe_key'], $field_data['stripe_mode'], $value );
        }
        return $icon;
    }
    
    /**
     * Renders a hidden field.
     *
     * @since 1.0.0
     */
    public function hidden_field($type, $field_id, $field_data, $value) {
        ?>
        <input type="hidden" name="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>" value="<?php echo esc_attr($value); ?>" />
        <?php
    }
    
    /**
     * Renders a color input field.
     *
     * @since 1.0.0
     */
    public function color_field($field_id, $field_data, $value) {
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>">
                <?php echo esc_html($field_data['label']); ?>
            </label>
            <div class="buddyclients-admin-field-input-wrap">
                <input type="color" name="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>" 
                       value="<?php echo esc_attr($value); ?>" class="color-field" />
                <p class="description"><?php echo wp_kses_post( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Renders a page dropdown field.
     * 
     * @since 1.0.0
     */
    public function page_button($field_id, $field_data, $value) {
        
        // Check if page is selected and published
        if ($value && get_post_status($value) === 'publish') {
            
            // Get page permalink
            $selected_page_permalink = ($value) ? get_permalink($value) : '#';
            
            // Create view page button
            $button = '<a href="' . esc_url($selected_page_permalink) . '" target="_blank"><button type="button" class="button button-secondary">' . __('View Page', 'buddyclients-free') . '</button></a>';
        } else {
            
            // Show create button
            $button = '<button onclick="buddycCreateNewPage({
                page_key: \'' . esc_js($field_id) . '\',
                settings_key: \'' . esc_js('pages') . '\',
                post_title: \'' . esc_js($field_data['post_title']) . '\',
                post_content: \'' . esc_js($field_data['post_content']) . '\',
                post_type: \'' . esc_js('page') . '\',
                post_status: \'' . esc_js('publish') . '\'
            });" type="button" class="button button-secondary">' . __('Create Page', 'buddyclients-free') . '</button>';
        }

        // Escape the entire button HTML
        echo wp_kses( $button, [
            'button' => [
                'onclick' => [],
                'type'    => [],
                'class'   => []
            ],
            'a' => [
                'href' => [],
                'target' => [],
                'class' => [],
                'rel' => []
            ],
        ]);
    }
    
    /**
     * Displays legal page field.
     * 
     * @since 1.0.0
     */
    public function legal_field($type, $field_id, $field_data, $value) {
        
        // Initialize
        $output = '';
        $view_button = '';
        $create_button = '';
        $edit_button = '';
    
        // Check if post exists
        $post = get_post($value);
        
        // If post exists, show view button
        if ( $post ) {
            $view_button = $post ? 
            '<a href="' . get_permalink($value) . '" target="_blank">
                <button type="button" class="button button-primary">' . 
                    /* translators: %s: label of the field */
                    sprintf( esc_html__('View Active %s', 'buddyclients-free'), $field_data['label'] ) . 
                '</button>
            </a>' : '';
        }
        
        // Continue editing button
        $draft_id = nextav_get_setting('legal', $field_id . '_draft');
        if ( $draft_id ) {
            $edit_button = '<a href="' . get_edit_post_link($draft_id) . '">
                <button type="button" class="button button-secondary">' . 
                    /* translators: %s: label of the field */
                    sprintf( esc_html__('Edit %s Draft', 'buddyclients-free'), $field_data['label'] ) . 
                '</button>
            </a>';
        } else {
            // Generate a nonce
            $create_nonce = wp_create_nonce( 'nextav_create_new_page_nonce' );
            
            // Build create page button
            $create_button = '<button onclick="buddycCreateNewPage({
                page_key: \'' . esc_js($field_id) . '\',
                settings_key: \'' . esc_js('legal') . '\',
                post_title: \'' . esc_js($field_data['label']) . '\',
                post_content: \'\',
                post_type: \'' . esc_js('nextav_legal') . '\',
                post_status: \'' . esc_js('draft') . '\',
                nonce: \'' . esc_js($create_nonce) . '\'
            });" type="button" class="button button-secondary">' . 
                /* translators: %s: label of the field */
                sprintf( esc_html__('Create New %s', 'buddyclients-free'), esc_html($field_data['label'])) . 
            '</button>';
        }
        
        // Get previous version and deadline
        $version_trans_message = '';
        $prev_version = nextav_get_setting('legal', $field_id . '_prev');
        if ( $prev_version ) {
            $curr_time = current_time('mysql');
            $publish_date = get_post_field('post_date', $value);
            $deadline_setting = nextav_get_setting('legal', 'legal_deadline');
            if ($deadline_setting !== '') {
                $deadline = gmdate('Y-m-d H:i:s', strtotime($publish_date . ' +' . $deadline_setting . ' days'));
                // Get the current date and time
                $current_datetime = gmdate('Y-m-d H:i:s');
                
                // Compare the deadline with the current date and time
                if ($deadline > $current_datetime) {
                    $human_readable_deadline = gmdate('F j, Y, g:i a', strtotime($deadline));
                    $version_trans_message = sprintf(
                        /* translators: %s: human-readable deadline */
                        /* translators: %s: label of the field */
                        esc_html__('Users have until %1$s to accept the new %2$s.', 'buddyclients-free'),
                        $human_readable_deadline,
                        $field_data['label']
                    );
                }
    
            } else {
                $version_trans_message = sprintf(
                    /* translators: %s: label of the field */
                    esc_html__('Users have forever to accept the new %s.', 'buddyclients-free'),
                    $field_data['label']
                );
            }
        }
        
        // Build output
        $output .= '<div class="buddyclients-admin-field">';
        $output .= '<label for="' . esc_attr($this->name . '[' . $field_id . ']') . '">' . esc_html($field_data['label']) . '</label>';
        $output .= '<div class="buddyclients-admin-field-input-wrap">';
        $output .= '<input type="hidden" name="' . esc_attr($this->name . '[' . $field_id . ']') . '" value="' . esc_attr($value) . '">';
    
        $output .= $view_button;
        $output .= $create_button;
        $output .= $edit_button;
        $output .= '<br>' . esc_html($version_trans_message);
        
        $output .= '</div>';
        $output .= '</div>';
            
        // Escape the entire output with allowed tags
        $allowed_html = [
            'div' => [
                'class' => [],
                'style' => []
            ],
            'label' => [
                'for' => []
            ],
            'input' => [
                'type' => [],
                'name' => [],
                'value' => [],
                'class' => []
            ],
            'a' => [
                'href' => [],
                'target' => []
            ],
            'button' => [
                'type' => [],
                'class' => [],
                'style' => [],
                'onclick' => []
            ],
            'br' => []
        ];

        // Output escaped HTML
        echo wp_kses( $output, $allowed_html );
    }
    
    /**
     * Displays copy-to-clipboard text.
     * 
     * @since 1.0.0
     */
    public function copy_field($type, $field_id, $field_data) {
        $allowed_html = [
            'div' => [
                'class' => [],
            ],
            'p' => [
                'id' => [],
                'class' => [],
            ],
            'input' => [
                'type' => [],
                'value' => [],
                'size' => [],
                'readonly' => [],
                'class' => [],
            ],
            'span' => [
                'class' => [],
                'onclick' => [],
            ],
        ];
        ?>
        <div class="buddyclients-admin-field">
            <label for="<?php echo esc_attr($this->name . '[' . $field_id . ']'); ?>"><?php echo wp_kses( $field_data['label'], $allowed_html ); ?></label>
            <div class="buddyclients-admin-field-input-wrap">
                <?php echo wp_kses( nextav_copy_to_clipboard($field_data['content'], $field_id), $allowed_html ); ?>
                <p class="description"><?php echo esc_html( $field_data['description'] ); ?></p>
            </div>
        </div>
        <?php
    }
}