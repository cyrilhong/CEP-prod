<?php
/*
Plugin Name: Sigami Livereload
Plugin URI: https://github.com/sigami/sigami-livereload
Description: Adds livereload scripts on front end or admin. With options to change host and port.
Version: 1.0.7
Author: Miguel Sirvent
Author URI: http://wordpress.org/poxtron
Text Domain: sigami-livereload
Domain Path: /languages
*/

class  Sigami_Livereload {
	private static $options = null;
	private static $defaults = null;
	const option_name = 'sigami-livereload';

	static function hooks() {

		/** @see Sigami_Livereload::activation */
		register_activation_hook( __FILE__, __CLASS__ . '::activation' );

		/** @see Sigami_Livereload::uninstall */
		register_uninstall_hook( __FILE__, __CLASS__ . '::uninstall' );

		/** @see Sigami_Livereload::plugins_loaded */
		add_action( 'plugins_loaded', __CLASS__ . '::plugins_loaded' );

		/** @see Sigami_Livereload::init */
		add_action( 'init', __CLASS__ . '::init' );

		self::run_plugin();
	}

	/**
	 * Save default values of options to database.
	 */
	static function activation() {
		self::get_option();
	}

	/**
	 * Deletes options stored to database, works for multisite.
	 */
	static function uninstall() {
		if ( is_multisite() ) {
			$sites = wp_get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );
				delete_option( self::option_name );
			}
			restore_current_blog();
		} else {
			delete_option( self::option_name );
		}
	}

	/**
	 * Loads translation strings
	 */
	static function plugins_loaded() {
		load_plugin_textdomain( 'sigami-livereload', false, plugin_dir_path( __FILE__ ) . '\languages' );
	}

	/**
	 * Plugin dir url
	 *
	 * @return string $url plugin dir url
	 */
	static function url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Plugin dir path
	 *
	 * @return string $path plugin dir path
	 */
	static function path() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Get the stored default options.
	 *
	 * @return array|null $defaults Default array of options
	 */
	static function get_defaults() {
		if ( null === self::$defaults ) {
			self::$defaults = array(
				'port'        => '35729',
				'host'        => 'localhost',
				'in_footer'   => 'on',
				'in_admin'    => 0,
				'in_frontend' => 'on',
			);
		}

		return self::$defaults;
	}

	/**
	 * Get all the plugin options or a single one. This function will setup the default options if there are not
	 * installed on the site.
	 *
	 * @param null|string $option option name to be retrieved leave blank to get all.
	 *
	 * @return array|bool|null false when the option does not exsisists
	 */
	static function get_option( $option = null ) {
		if ( ! is_array( self::$options ) ) {
			$options  = get_option( self::option_name );
			$defaults = self::get_defaults();
			if ( is_array( $options ) ) {
				self::$options = $options;
			} else {
				update_option( self::option_name, $defaults, true );
				self::$options = $defaults;
			}
		}
		if ( $option ) {
			return isset( self::$options[ $option ] ) ? self::$options[ $option ] : false;
		} else {
			return self::$options;
		}
	}

	/**
	 * Hook to pre update options to sanitize the plugin option
	 */
	static function init() {
		add_filter( 'pre_update_option_' . self::option_name, __CLASS__ . '::pre_update_option' );
	}

	/**
	 * Sanitize the option array before it is saved to the database. Make sure all default options keys are present and
	 * make it impossible to add an option that is not declared on defaults.
	 *
	 * @param array $options options to be saved to the database
	 *
	 * @return array|null
	 */
	static function pre_update_option( $options ) {
		$defaults = self::get_defaults();
		if ( $options === $defaults ) {
			return $defaults;
		}
		foreach ( $defaults as $default_key => $default_value ) {
			if ( ! isset( $options[ $default_key ] ) ) {
				$options[ $default_key ] = ( ( $default_value == 'on' ) || ( $default_value == 0 ) ) ? 0 : $default_value;
			}
		}
		foreach ( $options as $option_key => $option_value ) {
			if ( ! isset( $defaults[ $option_key ] ) ) {
				unset( $options[ $option_key ] );
			}
		}



		return $options;
	}

	static function run_plugin() {
		$options   = self::get_option();
		if($options['in_frontend'] === 'on'){
			add_action('wp_enqueue_scripts',__CLASS__.'::enqueue_script');
		}
		if($options['in_admin'] ==='on'){
			add_action('admin_enqueue_scripts',__CLASS__.'::enqueue_script');
		}
	}

	static function enqueue_script() {
		$options   = self::get_option();
		$in_footer = ($options['in_footer'] === 'on');
		wp_enqueue_script( 'livereload', "//{$options['host']}:{$options['port']}/livereload.js", array(), '1.0', $in_footer );
	}

}

Sigami_Livereload::hooks();

if(is_admin()){
	require_once(__DIR__.'/sigami-livereload-admin.php');
	Sigami_Livereload_Admin::hooks();
}











