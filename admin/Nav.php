<?php
namespace NextAv\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use NextAv\Includes\ParamManager;

/**
 * Generates the admin nav tabs.
 * 
 * Defines and outputs nav tabs. Handles modifications to the admin menu.
 *
 * @since 1.0.0
 */
class Nav {
    
    /**
     * Defines the array of tab groups and links.
     * 
     * @since 1.0.0
     *
     * @var array
     * 
     * @TODO Update when integrations are stable.
     */
    private static function tabs() {
        
        $tabs = [
            'dashboard' => [
                __( 'All Bookings', 'nextav' )        => ['page'  => 'nextav-dashboard'],
                ''                                          => ['page'  => 'nextav-booked-services'],
                __( 'Overview', 'nextav' )            => ['page'  => 'nextav-bookings-dashboard'],
                __( 'Payments', 'nextav' )            => ['page'  => 'nextav-payments'],
                __( 'Users', 'nextav' )               => ['page'  => 'nextav-users'],
                __( 'Leads', 'nextav' )               => ['page'  => 'nextav-leads'],
            ],
            'settings' => [
                __( 'General', 'nextav' )            => ['page'  => 'nextav-general-settings'],
                __( 'Integrations', 'nextav' )        => ['page'  => 'nextav-integrations-settings'],
            ],
        ];

        
        /**
         * Filters the admin nav tabs.
         *
         * @since 0.3.2
         *
         * @param array  $callbacks An array of callables keyed by settings group.
         */
         $tabs = apply_filters( 'nextav_nav_tabs', $tabs );
         
         return $tabs;
    }

    /**
     * Initializes the nav tabs.
     *
     * @since 1.0.0
     */
    public static function run() {
        self::define_hooks();
    }

    /**
     * Defines hooks.
     *
     * @since 1.0.0
     */
    private static function define_hooks() {
        add_action('admin_notices', [self::class, 'display_nav_tabs'] );
        add_action('admin_init', [self::class, 'active_tab'] );
        add_action('nextav_admin', [self::class, 'open_menu'], 10, 1 );
        add_action('nextav_admin', [self::class, 'active_submenu'], 10, 1 );
    }
        
    /**
     * Displays navigation tabs.
     *
     * Outputs HTML for the navigation tabs based on the current active tab and group.
     *
     * @since 1.0.0
     * @updated 0.3.4
     */
    public static function display_nav_tabs() {
        // Exit if we're in the Gutenberg editor
        if (nextav_gutenberg_editor()) {
            return;
        }
        
        // Get tabs data
        $tabs_array = self::tabs();
        
        // Get the active tab
        $active_tab = self::matching_tab();
        
        // Initialize tabs HTML
        $tabs = '';

        // Handle params
        $group = nextav_get_param( 'group' );
        
        // Determine if an active tab was found and if the group exists
        if ( ($active_tab && isset($tabs_array[$active_tab['group']])) || $group ) {
            
            // Default to the active tab group if no group parameter
            $group = $group ?? $active_tab['group'];
            
            // Output BuddyClients header
            $tabs .= '<h1 class="wp-heading-inline"><span class="dashicons dashicons-nextav-dark"></span> Next Available</h1>';
            
            // Open tabs container
            $tabs .= '<h2 class="nav-tab-wrapper">';
            
            // Loop through all tabs in the active tab group
            foreach ($tabs_array[$group] as $tab_label => $tab_data) {
                
                // Skip tabs with no label
                if (!$tab_label) {
                    continue;
                }
                
                // Determine if this tab is active
                $active = $active_tab ? (($active_tab['label'] === $tab_label) ? 'nav-tab-active' : '') : '';
                
                // Build the tab link
                $link = self::tab_link($tab_data, $group);
    
                // Build the tab HTML
                $tabs .= '<a href="' . esc_url($link) . '" class="nav-tab nextav-nav-tab ' . esc_attr($active) . '">' . esc_html($tab_label) . '</a>';
            }
            
            // Close tabs container
            $tabs .= '</h2>';
        }
        
        // Escape the entire output with allowed HTML tags and attributes
        $allowed_html = [
            'h1' => ['class' => []],
            'span' => ['class' => []],
            'h2' => ['class' => []],
            'a' => [
                'href' => [],
                'class' => [],
                'target' => [],
            ],
        ];

        echo wp_kses( $tabs, $allowed_html );
    }

    
    /**
     * Retrieves the active tab on init.
     * 
     * @since 1.0.0
     */
    public static function active_tab() {
        
        // Get active tab
        $active_tab = self::matching_tab();
        
        // Check if matching tab found
        if ( $active_tab ) {
            /**
             * Fires on every BuddyClients admin page.
             *
             * @since 1.0.0
             *
             * @param array $active_tab {
             *     Array of info about currently active tab.
             *
             *     @type string $group The nav group to which the tab belongs.
             *     @type string $label The nav tab label.
             *     @type array $group_data The nav tab group data.
             * }
             */
            do_action('nextav_admin', $active_tab);
        }
    }
    
    /**
     * Builds the tab link.
     *
     * Constructs a URL for the tab based on its type (page, taxonomy, or post type) and available data.
     *
     * @since 1.0.0
     * @updated 0.3.4
     * 
     * @param   array   $tab_data       Array of tab data.
     * @param   string  $active_group   The group of the active tab.
     * @return  string  The tab link.
     */
    private static function tab_link( $tab_data, $active_group ) {
        
        // Define placeholder link
        $placeholder = admin_url('admin.php?page=nextav-upgrade&group=' . urlencode($active_group));
        foreach ( $tab_data as $key => $value ) {
            $placeholder .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        
        // Build the link to a page
        if (isset($tab_data['page'])) {
            // Make sure the page exists
            if (menu_page_url($tab_data['page'], false)) {
                return admin_url('admin.php?page=' . urlencode($tab_data['page']));
            } else {
                // Link to placeholder
                return $placeholder;
            }
        }
        // Build the link to a taxonomy
        elseif (isset($tab_data['taxonomy'])) {
            // Make sure the taxonomy exists
            if (taxonomy_exists($tab_data['taxonomy'])) {
                return admin_url('edit-tags.php?taxonomy=' . urlencode($tab_data['taxonomy']) . '&post_type=' . urlencode($tab_data['post_type']));
            } else {
                // Link to placeholder
                return $placeholder;
            }
        }
        // Build the link to a post type
        elseif (isset($tab_data['post_type'])) {
            // Make sure the post type exists
            if (post_type_exists($tab_data['post_type'])) {
                return admin_url('edit.php?post_type=' . urlencode($tab_data['post_type']));
            } else {
                // Link to placeholder
                return $placeholder;
            }
        }
    
        // Default return to placeholder if no valid data
        return $placeholder;
    }

    /**
     * Retrieves tab label and group.
     * 
     * Defaults to current url.
     * 
     * @since 1.0.0
     * 
     * @param ?string $link URL to check for match.
     */
     private static function matching_tab( $link = null ) {
        
        // Get link or current url params 
        $params = self::get_params( $link );     
        
        // Get tabs data
        $tabs_array = self::tabs();
        
        // Initialize match
        $matching_tab = false;
        
        // Loop through tabs data
        foreach ( $tabs_array as $tab_group => $group_data ) {
            
            // Loop through group tabs
            foreach ( $group_data as $tab_label => $tab_data ) {
                
                // Build current tab data
                $current_tab = [
                    'label' => $tab_label,
                    'group' => $tab_group,
                    'group_data' => $group_data,
                ];
                
                // Check for exact string match
                if ( isset( $tab_data['page'] ) && ( $tab_data['page'] === $link ) ) {
                    $matching_tab = $current_tab;
                    break;
                }
                
                // Check for page match
                if ( ( $params['page'] && isset( $tab_data['page'] ) ) && ( $tab_data['page'] === $params['page'] ) ) {
                    $matching_tab = $current_tab;
                    break;
                }
            
                // Check for tax match
                if ( ( isset( $tab_data['taxonomy'] ) && isset( $params['taxonomy'] ) ) && ( $tab_data['taxonomy'] == $params['taxonomy'] ) ) {
                    $matching_tab = $current_tab;
                    break;
                }
            
                // Check for post type match
                if ( ( ! isset( $params['taxonomy'] ) ) && ( isset( $tab_data['post_type'] ) && $params['post_type'] ) && ( $tab_data['post_type'] === $params['post_type'] ) ) {
                    $matching_tab = $current_tab;
                    break;
                }
                
                // Check for post ID and post type match
                if ( isset( $tab_data['post_type'] ) && isset( $params['post'] ) && self::extract_post_type( $params ) === $tab_data['post_type'] ) {
                    $matching_tab = $current_tab;
                    break;
                }
            }
        }
        
        return $matching_tab;
     }
     
    /**
     * Retrieves URL params.
     * 
     * @since 1.0.0
     * 
     * @param   ?string $link   Defaults to current url.
     */
    private static function get_params( $link = null ) {
        
        // Get all url params for provided link or curr url
        $array = nextav_get_all_params( $link );

        //echo 'Returned from nextav_get_all_params;';
        //nextav_print($array);
    
        // Extract parameters
        return [
            'page'      => $array['page'] ?? null,
            'post_type' => $array['post_type'] ?? null,
            'taxonomy'  => $array['taxonomy'] ?? null,
            'post'      => $array['post'] ?? null
        ];
    }
    
    /**
     * Retrieves post type param.
     * 
     * @since 1.0.0
     * 
     * @param   array  $array   The array of url components.
     */
    private static function extract_post_type( $array ) {
        if ( isset( $array['post'] ) ) {
            return get_post_type( $array['post'] );
        } else if ( isset( $array['post_type'] ) ) {
            return $array['post_type'];
        }
    }
    
    /**
     * Opens BuddyClients admin menu.
     * 
     * @since 1.0.0
     */
    public static function open_menu( $active_tab ) {
        global $menu;
    
        foreach ($menu as $key => $menu_item) {
            // Check if the menu item corresponds to 'nextav-settings'
            if ($menu_item[2] === 'nextav-dashboard') {
                // Add necessary classes to the top-level menu item
                $menu[$key][4] .= ' wp-has-current-submenu wp-menu-open';
            }
        }
    }
        
    /**
     * Makes submenu active.
     * 
     * @since 1.0.0
     */
    public static function active_submenu( $active_tab ) {
        global $submenu;
        
        // Check if the 'nextav-dashboard' menu item has submenus
        if ( isset( $submenu['nextav-dashboard'] ) ) {
            // Get the submenu items
            $submenu_items = &$submenu['nextav-dashboard']; // Use reference to modify original array
            
            // Loop through each submenu item
            foreach ( $submenu_items as $key => &$submenu_item )  { // Use reference to modify original array
                // Get the submenu item url
                $submenu_url = $submenu_item[2];

                if ( ! $submenu_url ) {
                    continue;
                }
                
                // Get submenu item data
                $submenu_tab = self::matching_tab( $submenu_url );
                
                if ( ! $submenu_tab || ! isset($submenu_tab['group'] ) ) {
                    continue;
                }
                
                // Check if the submenu item group is active
                if ( $active_tab['group'] == $submenu_tab['group'] ) {
                    $submenu_item[4] = 'current'; // Add the 'current' class to existing array element
                    $submenu_item[4] .= ' aria-current="page"'; // Add the aria-current attribute to the <a> elemen
                }
            }
        }
    }

}