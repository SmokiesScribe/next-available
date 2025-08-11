<?php
namespace NextAv\Config;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Manages assets.
 * 
 * Enqueues all styles and scripts in given directory.
 * 
 * @since 1.0.0
 */
class AssetManager {
	
	/**
	 * The directory path of scripts or styles.
	 *
	 * @var string
	 */
	protected $dir_path;
	
	/**
	 * The directory url of scripts or styles.
	 *
	 * @var string
	 */
	protected $dir_url;
	
	/**
	 * Optional. File to require.
	 *
	 * @var string
	 */
	protected $file;
	
	/**
	 * Formatted source name.
	 *
	 * @var string
	 */
	protected $source;

	/**
	 * Whether we are enqueuing assets for the admin area.
	 * Defaults to false.
	 * 
	 * @var bool
	 */
	protected $admin;

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 * 
	 * @param   string  $source_file    The class file name with extension.
	 * @param   string  $dir            The directory partial path.
	 * @param   string  $file           Optional. The specific file to load.
     * @param   bool    $admin  		Optional. Whether we are enqueuing admin scripts.
     *                          		Defaults to false.
	 */
	public function __construct( $source_file, $dir, $file = null, $admin = false ) {
	    
        // Define variables
		$this->dir_path = plugin_dir_path( $source_file ) . $dir;
		$this->dir_url = plugin_dir_url( $source_file ) . $dir;
		$this->file = $file ?? null;
		$this->admin = $admin;
		
		// Get source file name for handle
        $this->source = pathinfo( basename( $source_file ), PATHINFO_FILENAME );

		// Load CSS variables to front end and admin
		add_action( 'wp_enqueue_scripts', [$this, 'load_variables'] );
		add_action( 'admin_enqueue_scripts', [$this, 'load_variables'] );
	}

	/**
	 * Loads CSS variables.
	 * 
	 * @since 1.0.20
	 */
	public function load_variables() {
		if ( ! wp_style_is( 'nextav-css-variables', 'enqueued' ) ) {

			// Enqueue variables file
			wp_enqueue_style( 'nextav-css-variables', NEXTAV_PLUGIN_URL . 'assets/css/variables.css', [], NEXTAV_PLUGIN_VERSION );

			// Initialize core variables
			$css_variables = [
				'primary-color'		=> nextav_color( 'primary' ),
				'accent-color'		=> nextav_color( 'accent' ),
				'tertiary-color'	=> nextav_color( 'tertiary' ),
				'default-border'	=> 'solid 1px #e7e9ec',
				'primary-overlay'	=> nextav_hex_to_rgba( nextav_color( 'primary' ), 0.6 ),
				'accent-overlay'	=> nextav_hex_to_rgba( nextav_color( 'accent' ), 0.6 ),
				'brand-blue'		=> '#037AAD',
				'brand-blue-hover'  => '#005f8c',
				'brand-green'		=> '#067F06',
			];

			/**
			 * Filters custom CSS variables.
			 *
			 * @since 1.0.20
			 *
			 * @param array  $css_variables The associative array of css names and variables.
			 */
			$css_variables = apply_filters( 'nextav_css_variables', $css_variables );

			// Build custom css
			$custom_css = ":root {";

			// Make sure variables exist
			if ( ! empty( $css_variables ) ) {
				foreach ( $css_variables as $name => $value ) {
					$custom_css .= "--nextav-{$name}: {$value};";
				}

				// Close
				$custom_css .= "}";
			}		

			// Add variables as inline style
			wp_add_inline_style( 'nextav-css-variables', $custom_css );
		}
	}
	
	/**
	 * Retrieves files to load.
	 * 
	 * @since 1.0.0
	 */
	public function run() {
	    
        // Check if the directory exists
        if ( is_dir( $this->dir_path ) ) {
            
            // Use specific file if defined
            if ( $this->file ) {
                
                // Build file path
                $file_path = $this->dir_path . '/' . $this->file;
                
                // Make sure file exists
                if ( file_exists( $file_path ) ) {
                    $this->handle_file( $this->file );
                }
                
            // Else get all files in directory
            } else {
                
                // Get all files in the directory
                $files = scandir( $this->dir_path );
            
                // Skip . and ..
                $files = array_diff( $files, array( '.', '..' ) );
            
                // Handle each file
                foreach ( $files as $file ) {
                    $this->handle_file( $file );
                }                
            }
        }    
	}
	
	/**
	 * Handles file based on type.
	 * 
	 * @since 1.0.0
	 * 
	 * @param   array   $file   The file.
	 */
	private function handle_file( $file ) {
	    
	    // Extract file info
        $extension = pathinfo( $file, PATHINFO_EXTENSION );
        $file_name = pathinfo( $file, PATHINFO_FILENAME );
        
        // Handle files by type
        switch ( $extension ) {
            case 'css':
            case 'js':
                $this->enqueue( $file, $file_name, $extension );
                break;
            case 'php':
                $this->require_file( $file, $file_name, $extension );
                break;
            }
	}

	/**
	 * Enqueues Javascript and CSS files.
	 * 
	 * @since 1.0.20
	 * 
	 * @param   array   $file       File to enqueue.
	 * @param   string  $file_name  The file name without extension.
	 * @param   string  $extension  The file extension.
	 */
	private function enqueue( $file, $file_name, $extension ) {

		// Make sure the file matches admin or no
		if ( ! $this->admin_match( $file_name ) ) {
			return;
		}
	    
	    // Build script handle
        $handle = $this->build_handle( $file_name );
        
        // Build full url
        $file_url = $this->dir_url . '/' . $file;

		// Enqueue the file
		switch ( $extension ) {
			case 'js':
				$this->enqueue_js( $handle, $file_url, $file_name );
				break;
			case 'css':
				$this->enqueue_css( $handle, $file_url, $file_name );
				break;
			}
	}

	/**
	 * Checks whether the file is meant to load in the admin area
	 * and whether we are currently loading admin files.
	 * 
	 * @since 1.0.25
	 * 
	 * @param	string	$file_name	The name of the file to check.
	 * @return	bool	True if the file matches requirements, false if not.
	 */
	private function admin_match( $file_name ) {
		// Check whether the file name includes 'admin'
		$is_admin_file = strpos( $file_name, 'admin' ) !== false;

		// Check whether we're in the admin dir
		$is_admin_dir = strpos( $this->dir_path, '/admin/assets/' );

		// Admin file on front end
		if ( ! $this->admin && ( $is_admin_file || $is_admin_dir ) ) {
			return false;
		}

		// Front end file on admin
		if ( $this->admin && ( ! $is_admin_file && ! $is_admin_dir ) ) {
			return false;
		}

		// Five by five
		return true;
	}

	/**
	 * Enqueues and localizes Javascript file.
	 * 
	 * @since 1.0.15
	 * 
	 * @param   string  $handle     The script handle.
	 * @param   string  $file_url   The full file url.
	 * @param	string	$file_name	The file name without extension.
	 */
	private function enqueue_js( $handle, $file_url, $file_name ) {
		if ( ! wp_script_is( $handle, 'enqueued' ) ) {

			// Register script
			wp_register_script( $handle, $file_url, array( 'jquery' ), NEXTAV_PLUGIN_VERSION, true );

			// Enqueue script
			wp_enqueue_script( $handle );

			// Localize the script
			$this->localize_script( $file_name, $handle );
		}
	}

	/**
	 * Enqueues a CSS file.
	 * 
	 * @since 1.0.20
	 * 
	 * @param   string  $handle     The script handle.
	 * @param   string  $file_url   The full file url.
	 * @param	string	$file_name	The file name without extension.
	 */
	private function enqueue_css( $handle, $file_url, $file_name ) {
		if ( ! wp_style_is( $handle, 'enqueued' ) ) {			
			// Register the style
			wp_register_style( $handle, $file_url, array(), NEXTAV_PLUGIN_VERSION, 'all' );
			
			// Enqueue the style
			wp_enqueue_style( $handle );
		}		
	}

	/**
	 * Defines localization data.
	 * 
	 * @since 1.0.15
	 * 
	 * @param	string	$file_name	The name of the script file.
	 */
	private static function localization_info( $file_name ) {

		switch ( $file_name ) {
			case 'calendar-nav':
				return [];
		}
	}

	/**
	 * Localizes a javascript file.
	 * 
	 * @since 1.0.16
	 */
	public function localize_script( $file_name, $handle ) {
		// Fetch localization info
		$localization_info = self::localization_info( $file_name );

		// Check if localization info exists for the file
		if ( is_array( $localization_info ) ) {

			// Build nonce
			$localization_info['nonce'] = wp_create_nonce( $this->build_nonce_action( $file_name ) );
			$localization_info['nonceAction'] = $this->build_nonce_action( $file_name );
			$localization_info['fileName'] = $file_name;

			// Add ajax url
			$localization_info['ajaxurl'] = admin_url('admin-ajax.php');

			// Localize and pass data
			$data_name = $this->build_data_name( $file_name );
	        wp_localize_script( $handle, $data_name, $localization_info );
		}
	}
	
	/**
	 * Requires PHP files.
	 * 
	 * @since 1.0.0
	 * 
	 * @param   array   $file       File to enqueue.
	 * @param   string  $file_name  The file name without extension.
	 * @param   string  $extension  The file extension.
	 */
	 private function require_file( $file, $file_name, $extension ) {
	    $file_path = $this->dir_path . '/' . $file;
	    // Require php file
	    require_once($file_path);
	}
	
	/**
	 * Builds handle.
	 * 
	 * @since 1.0.0
	 * 
	 * @param   string  $file_name  The file name without extension.
	 */
	private function build_handle( $file_name ) {
	    return 'nextav-' . strtolower( $this->source ) . '-' . $file_name;
	}

	/**
	 * Builds a nonce action name.
	 * 
	 * @since 1.0.16
	 * 
	 * @param   string  $file_name  The file name without extension.
	 */
	private function build_nonce_action( $file_name ) {
		$action = strtolower( $file_name );
		$action = str_replace( '-', '_', $file_name );
		$action = 'nextav_' . $action;
		return $action;
	}

	/**
	 * Converts snake case to camel case.
	 * 
	 * @since 1.0.16
	 */
	private function build_data_name( $string ) {
		// Split the string by underscores
		$parts = explode( '-', $string );
		
		// Capitalize the first letter of each part except the first one
		$parts = array_map( 'ucfirst', $parts );
		
		// Make the first letter lowercase to follow camelCase
		$parts[0] = strtolower( $parts[0] );
		
		// Join the parts back together
		$data_name = implode( '', $parts );

		// Add suffix
		return $data_name . 'Data';
	}
}