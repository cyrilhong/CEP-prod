<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Pngx__Main' ) ) {
	return;
}


/**
 * Main Plugin Engine class.
 */
class Pngx__Main {

	const VERSION    = '2.5.4.1';
	const OPTIONS_ID = 'plugin_engine_options';

	protected $plugin_context;
	protected $plugin_context_class;
	public    $doing_ajax = false;

	public $plugin_dir;
	public $plugin_path;
	public $plugin_url;
	public $resource_path;
	public $resource_url;
	public $vendor_path;
	public $vendor_url;

	/**
	 * constructor
	 */
	public function __construct( $context = null ) {

		if ( is_object( $context ) ) {
			$this->plugin_context       = $context;
			$this->plugin_context_class = get_class( $context );
		}

		$this->plugin_path   = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
		$this->plugin_dir    = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url    = plugins_url( $this->plugin_dir );
		$parent_plugin_dir   = trailingslashit( plugin_basename( $this->plugin_path ) );
		$this->plugin_url    = plugins_url( $parent_plugin_dir === $this->plugin_dir ? $this->plugin_dir : $parent_plugin_dir );
		$this->resource_path = $this->plugin_path . 'src/resources/';
		$this->resource_url  = $this->plugin_url . 'src/resources/';
		$this->vendor_path   = $this->plugin_path . 'vendor/';
		$this->vendor_url    = $this->plugin_url . 'vendor/';

		$this->load_text_domain( 'plugin-engine', basename( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/plugin-engine/languages/' );

		$this->init_autoloading();

		$this->add_hooks();

		$this->doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		/**
		 * Runs once all pngx libs are loaded and initial hooks are in place.
		 *
		 */
		do_action( 'plugin_engine_loaded' );

	}

	/**
	 * Get's the instantiated context of this class. I.e. the object that instantiated this one.
	 */
	public function context() {
		return $this->plugin_context;
	}

	/**
	 * Setup the autoloader for common files
	 */
	protected function init_autoloading() {
		if ( ! class_exists( 'Pngx__Autoloader' ) ) {
			require_once dirname( __FILE__ ) . '/Autoloader.php';
		}

		$prefixes   = array( 'Pngx__' => dirname( __FILE__ ) );
		$autoloader = Pngx__Autoloader::instance();
		$autoloader->register_prefixes( $prefixes );
		$autoloader->register_autoloader();

	}

	/**
	 * Get's the class name of the instantiated plugin context of this class. I.e. the class name of the object that
	 * instantiated this one.
	 */
	public function context_class() {
		return $this->plugin_context_class;
	}

	/**
	 * Adds core hooks
	 */
	public function add_hooks() {

		//Load Admin Class if in Admin Section
		if ( is_admin() ) {
			new Pngx__Admin__Main();
		}

		add_action( 'plugins_loaded', array( $this, 'pngx_plugins_loaded' ), PHP_INT_MAX );

	}

	/**
	 * A Helper method to load text domain
	 * First it tries to load the wp-content/languages translation then if falls to the
	 * try to load $dir language files
	 *
	 * @param string $domain The text domain that will be loaded
	 * @param string $dir    What directory should be used to try to load if the default doenst work
	 *
	 * @return bool  If it was able to load the text domain
	 */
	public function load_text_domain( $domain, $dir = false ) {

		// Added safety just in case this runs twice...
		if ( is_textdomain_loaded( $domain ) && ! is_a( $GLOBALS['l10n'][ $domain ], 'NOOP_Translations' ) ) {
			return true;
		}

		$locale = get_locale();
		$mofile = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo';

		/**
		 * Allows users to filter which file will be loaded for a given text domain
		 * Be careful when using this filter, it will apply across the whole plugin suite.
		 *
		 * @param string      $mofile The path for the .mo File
		 * @param string      $domain Which plugin domain we are trying to load
		 * @param string      $locale Which Language we will load
		 * @param string|bool $dir    If there was a custom directory passed on the method call
		 */
		$mofile = apply_filters( 'pngx_load_text_domain', $mofile, $domain, $locale, $dir );

		$loaded = load_plugin_textdomain( $domain, false, $mofile );

		if ( false !== $dir && ! $loaded ) {
			return load_plugin_textdomain( $domain, false, $dir );
		}

		return $loaded;
	}

	/**
	 * Returns the post types registered with plugin engine
	 */
	public static function get_post_types() {
		// we default the post type array to empty in plugin engine. Plugins like TEC add to it
		return apply_filters( 'pngx_post_types', array() );

	}

	/**
	 * Insert an array after a specified key within another array.
	 *
	 * @param $key
	 * @param $source_array
	 * @param $insert_array
	 *
	 * @return array
	 *
	 */
	public static function array_insert_after_key( $key, $source_array, $insert_array ) {
		if ( array_key_exists( $key, $source_array ) ) {
			$position     = array_search( $key, array_keys( $source_array ) ) + 1;
			$source_array = array_slice( $source_array, 0, $position, true ) + $insert_array + array_slice( $source_array, $position, null, true );
		} else {
			// If no key is found, then add it to the end of the array.
			$source_array += $insert_array;
		}

		return $source_array;
	}

	/**
	 * Insert an array immediately before a specified key within another array.
	 *
	 * @param $key
	 * @param $source_array
	 * @param $insert_array
	 *
	 * @return array
	 */
	public static function array_insert_before_key( $key, $source_array, $insert_array ) {
		if ( array_key_exists( $key, $source_array ) ) {
			$position     = array_search( $key, array_keys( $source_array ) );
			$source_array = array_slice( $source_array, 0, $position, true ) + $insert_array + array_slice( $source_array, $position, null, true );
		} else {
			// If no key is found, then add it to the end of the array.
			$source_array += $insert_array;
		}

		return $source_array;
	}

	/**
	 * Merge the Defaults with new values
	 *
	 * @param $defaults
	 * @param $updates
	 *
	 * @return array
	 */
	public static function merge_defaults( $defaults, $updates ) {

		$updates = (array) $updates;
		$out     = array();
		foreach ( $defaults as $name => $default ) {
			if ( array_key_exists( $name, $updates ) ) {
				$out[ $name ] = $updates[ $name ];
			} else {
				$out[ $name ] = $default;
			}
		}

		return $out;

	}

	/**
	 * Runs pngx_plugins_loaded action, should be hooked to the end of plugins_loaded
	 */
	public function pngx_plugins_loaded() {
		/**
		 * Runs after all plugins including Plugin Engine ones have loaded
		 *
		 */
		do_action( 'pngx_plugins_loaded' );

		$this->loadLibraries();
	}


	/**
	 * Load all the required library files.
	 */
	protected function loadLibraries() {

		//Core Functions
		require_once $this->plugin_path . 'src/functions/template-tags/general.php';

	}
	/**
	 * Static Singleton Factory Method
	 *
	 * @return Pngx__Main
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self;
		}

		return $instance;
	}
}