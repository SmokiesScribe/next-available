<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'NextAv' ) ) {

	/**
	 * NextAv Main Class.
	 * 
	 * @since 1.0.0
	 * 
	 * @internal
	 */
	#[\AllowDynamicProperties]
	final class NextAv {
	    
		/**
		 * The single instance of the main class.
		 * 
		 * @since 1.0.0
		 */
		protected static $instance = null;
    	
    	/**
    	 * Generates the main NextAv instance.
    	 *
    	 * Ensures only one instance of NextAv is loaded.
    	 *
    	 * @since 1.0.0
    	 */
    	public static function instance() {
    		if ( is_null( self::$instance ) ) {
    			self::$instance = new self();
    		}
    		return self::$instance;
    	}
    	
    	/**
    	 * Prevents cloning.
    	 *
    	 * @since 1.0.0
    	 */
    	public function __clone() {
    		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'next-available' ), '1.0.0' );
    	}
    	/**
    	 * Prevents unserializing instances of this class.
    	 *
    	 * @since 1.0.0
    	 */
    	public function __wakeup() {
    		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'next-available' ), '1.0.0' );
    	}
    
    	/**
    	 * Constructor.
		 * 
		 * @since 1.0.0
    	 */
    	public function __construct() {
    		$this->constants();
    		$this->setup_globals();
    		$this->autoload();
    		$this->includes();
    	}
    
    	/** Private Methods *******************************************************/
    
    	/**
    	 * Bootstrap constants.
    	 *
    	 * @since 1.0.0
    	 */
    	private function constants() {
    	    
    	    // Plugin name
    		if ( ! defined( 'NEXTAV_PLUGIN_NAME' ) ) {
    			define( 'NEXTAV_PLUGIN_NAME', 'Next Available' );
    		}
    		// Path and URL.
    		if ( ! defined( 'NEXTAV_PLUGIN_DIR' ) ) {
    			define( 'NEXTAV_PLUGIN_DIR', plugin_dir_path(__FILE__) );
    		}
    
    		if ( ! defined( 'NEXTAV_PLUGIN_URL' ) ) {
    			define( 'NEXTAV_PLUGIN_URL', plugin_dir_url(__FILE__) );
    		}

			// Vendor dir
			if ( ! defined( 'NEXTAV_VENDOR_DIR' ) ) {
				define( 'NEXTAV_VENDOR_DIR', NEXTAV_PLUGIN_DIR . 'vendor' );
			}
		}
    
    	/**
    	 * Defines global variables.
    	 *
    	 * @since 1.0.0
    	 */
    	private function setup_globals() {
    		$this->file       = constant( 'NEXTAV_PLUGIN_FILE' );
    		$this->basename   = basename( constant( 'NEXTAV_PLUGIN_DIR' ) );
    		$this->plugin_dir = trailingslashit( constant( 'NEXTAV_PLUGIN_DIR' ) );
    		$this->plugin_url = constant( 'NEXTAV_PLUGIN_URL' );
    		$this->vendor_dir = NEXTAV_PLUGIN_DIR . '/vendor';
    	}
    	
    	/**
    	 * Includes and initializes the autoloader.
    	 * 
    	 * @since 0.4.3
    	 */
    	private function autoload() {
    		require_once( plugin_dir_path( __FILE__ ) . 'config/Autoloader.php' );
    		NextAv\Config\Autoloader::init();
    	}
    
    	/**
    	 * Includes required core files.
    	 *
    	 * @since 1.0.0
    	 */
    	private function includes() {
    	    
    		// Require settings function
    		require_once( plugin_dir_path( __FILE__ ) . 'includes/helpers/settings.php' );
    		
    		// Run activator
    		add_action( 'init', [$this, 'activate'] );
    		
    		// Require helpers
    		$this->require_helpers();
    		
    		// Initialize admin
    		$this->init_admin();
            
            // Define all hooks
            $this->define_hooks();
    	}
    	
    	/**
    	 * Initializes the Admin class.
    	 * 
    	 * @since 1.0.0
    	 */
    	public function init_admin() {
    	    NextAv\Admin\Admin::instance();
    	}
    	
        /**
         * Runs activation methods.
         * 
    	 * @since 1.0.0
    	 */
        public function activate() {
            NextAv\Config\Activator::activate();
        }
    	
        /**
         * Registers hooks and filters.
         *
         * @since 1.0.0
         */
        private function define_hooks() {
            
            // Global scripts and styles to wp and admin
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
            
            // Shortcodes
            add_action('wp', [$this, 'register_shortcodes']);        
        }
    
        /**
         * Enqueue global JavaScript files.
         *
         * @since 1.0.0
         */
        public function enqueue_scripts() {
            // Loading script
            $this->enqueue_asset( 'assets/js', 'loading.js' );

			// Load Font Awesome
			$this->enqueue_font_awesome();
								
            // All CSS
            $this->enqueue_assets('assets/css');
            
            // AlL JS
            $this->enqueue_assets('assets/js');
        }

		/**
		 * Registers and enqueues the Font Awesome stylesheet.
		 * 
		 * @since 1.0.20
		 */
		private function enqueue_font_awesome() {
			// Register the FontAwesome stylesheet
			wp_register_style(
				'font-awesome-stylesheet', 
				plugins_url('vendor/fortawesome/font-awesome/css/all.min.css', __FILE__), 
				array(), 
				'6.5.1'
			);

			// Enqueue the registered stylesheet
			wp_enqueue_style('font-awesome-stylesheet');
		}
        
        /**
         * Requires helper functions.
         *
         * @since 1.0.0
         */
        public function require_helpers() {
            $this->enqueue_assets('includes/helpers');
        }
        
        /**
         * Registers shortcodes.
         *
         * @since 1.0.0
         */
        public function register_shortcodes() {
            NextAv\Includes\Shortcodes::run();
        }
    
        /**
         * Enqueues all assets.
         *
         * @since 1.0.0
         *
         * @param string $dir The directory path where assets are located.
         */
        private function enqueue_assets( $dir ) {
            $asset_manager = new NextAv\Config\AssetManager( __FILE__, $dir );
            $asset_manager->run();
        }
        
        /**
         * Enqueues a single asset.
         *
         * @since 1.0.0
         *
         * @param   string  $dir        The directory path in which the asset is located.
         * @param   string  $file_name  The file name of the single asset to load.
         */
        private function enqueue_asset( $dir, $file_name ) {
            $asset_manager = new NextAv\Config\AssetManager( NEXTAV_PLUGIN_FILE, $dir, $file_name );
            $asset_manager->run();
        }
    }
}