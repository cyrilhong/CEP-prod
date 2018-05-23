<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


/**
 * Register Admin Assets for Plugin Engine
 *
 *
 */
class Pngx__Admin__Assets {

	/*
	* Register Assets
	*/
	public static function register_assets() {

		// @formatter:off
		wp_register_style(
			'pngx-bootstrap-iconpicker',
			Pngx__Main::instance()->vendor_url . 'bootstrap-iconpicker/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css',
			false,
			filemtime( Pngx__Main::instance()->vendor_path . 'bootstrap-iconpicker/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css' )
		);
		wp_register_style(
			'pngx-font-awesome',
			Pngx__Main::instance()->vendor_url . 'bootstrap-iconpicker/icon-fonts/font-awesome-4.3.0/css/font-awesome.css',
			false,
			filemtime( Pngx__Main::instance()->vendor_path . 'bootstrap-iconpicker/icon-fonts/font-awesome-4.3.0/css/font-awesome.css' )
		);
		wp_register_style(
			'pngx-colorbox',
			Pngx__Main::instance()->vendor_url . 'colorbox/colorbox.css',
			false,
			filemtime( Pngx__Main::instance()->vendor_path . 'colorbox/colorbox.css' )
		);
		wp_register_style(
			'pngx-admin',
			Pngx__Main::instance()->resource_url . 'css/pngx-admin.css',
			array( 'pngx-colorbox', 'pngx-bootstrap-iconpicker','pngx-font-awesome', 'pngx-bootstrap-iconpicker' ),
			filemtime( Pngx__Main::instance()->resource_path . 'css/pngx-admin.css' )
		);

		wp_register_script(
			'pngx-clipboard',
			Pngx__Main::instance()->vendor_url . 'clipboard/clipboard.js',
			array(),
			filemtime( Pngx__Main::instance()->vendor_path . 'clipboard/clipboard.js' ),
			true
		);
		wp_register_script(
			'pngx-bootstrap',
			Pngx__Main::instance()->vendor_url . 'bootstrap-iconpicker/bootstrap-3.2.0/js/bootstrap.min.js',
			array(),
			filemtime( Pngx__Main::instance()->vendor_path . 'bootstrap-iconpicker/bootstrap-3.2.0/js/bootstrap.min.js' ),
			true
		);
		wp_register_script(
			'pngx-bootstrap-iconpicker-fontawesome',
			Pngx__Main::instance()->vendor_url . 'bootstrap-iconpicker/bootstrap-iconpicker/js/iconset/iconset-fontawesome-4.3.0.min.js',
			array(),
			filemtime( Pngx__Main::instance()->vendor_path . 'bootstrap-iconpicker/bootstrap-iconpicker/js/iconset/iconset-fontawesome-4.3.0.min.js' ),
			true
		);
		wp_register_script(
			'pngx-bootstrap-iconpicker',
			Pngx__Main::instance()->vendor_url . 'bootstrap-iconpicker/bootstrap-iconpicker/js/bootstrap-iconpicker.min.js',
			array(),
			filemtime( Pngx__Main::instance()->vendor_path . 'bootstrap-iconpicker/bootstrap-iconpicker/js/bootstrap-iconpicker.min.js' ),
			true
		);

		wp_register_script(
			'pngx-color-picker-alpha',
			Pngx__Main::instance()->vendor_url . 'wp-color-picker-alpha/wp-color-picker-alpha.min.js',
			array(),
			filemtime( Pngx__Main::instance()->vendor_path . 'wp-color-picker-alpha/wp-color-picker-alpha.min.js' ),
			true
		);

		wp_register_script(
			'pngx-wp-editor',
			Pngx__Main::instance()->resource_url . 'js/wp_editor.js',
			array(),
			filemtime( Pngx__Main::instance()->resource_path . 'js/wp_editor.js' ),
			true
		);

		wp_register_script(
			'pngx-load-template-ajax',
			Pngx__Main::instance()->resource_url . 'js/templates.js',
			array(),
			filemtime( Pngx__Main::instance()->resource_path . 'js/templates.js' ),
			true
		);
		wp_register_script(
			'pngx-colorbox',
			Pngx__Main::instance()->vendor_url . 'colorbox/jquery.colorbox-min.js',
			array( 'jquery' ),
			filemtime( Pngx__Main::instance()->vendor_path . 'colorbox/jquery.colorbox-min.js' ),
			true
		);
		wp_register_script(
			'pngx-admin',
			Pngx__Main::instance()->resource_url . 'js/pngx-admin.js',
			array( 'pngx-clipboard', 'pngx-colorbox', 'pngx-wp-editor', 'pngx-load-template-ajax', 'pngx-color-picker-alpha', 'pngx-bootstrap', 'pngx-bootstrap-iconpicker-fontawesome', 'pngx-bootstrap-iconpicker', 'jquery-ui-tabs' ),
			filemtime( Pngx__Main::instance()->resource_path . 'js/pngx-admin.js' ),
			true
		);

		wp_localize_script( 'pngx-admin', 'pngx_admin', array(
			'ajaxurl'    => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			'sysinfo_optin_nonce'   => wp_create_nonce( 'sysinfo_optin_nonce' ),
			'clipboard_btn_text'    => __( 'Copy to clipboard', 'tribe-common' ),
			'clipboard_copied_text' => __( 'System info copied', 'tribe-common' ),
			'clipboard_fail_text'   => __( 'Press "Cmd + C" to copy', 'tribe-common' ),
		) );
		// @formatter:on

		/**
		 * Hook to Register New Scripts or Styles for the Admin
		 */
		do_action( 'pngx_admin_scripts_styles' );

	}

	/*
		* Register Assets
		*/
		public static function register_plugin_list_assets() {

			// @formatter:off
		/*	wp_register_style(
				'pngx-bootstrap-iconpicker',
				Pngx__Main::instance()->vendor_url . 'bootstrap-iconpicker/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css',
				false,
				filemtime( Pngx__Main::instance()->vendor_path . 'bootstrap-iconpicker/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css' )
			);*/
			wp_register_script(
				'pngx-license',
				Pngx__Main::instance()->resource_url . 'js/pngx-license.js',
				array( 'jquery-ui-tabs' ),
				filemtime( Pngx__Main::instance()->resource_path . 'js/pngx-license.js' ),
				true
			);

			wp_localize_script( 'pngx-license', 'pngx_license', array(
				'ajaxurl'   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
				'nonce'     => wp_create_nonce( 'pngx_license_updates' ),
			) );
			// @formatter:on

			/**
			 * Hook to Register New Scripts or Styles for the Admin
			 */
			do_action( 'pngx_admin_scripts_styles' );

		}

	/*
	* Enqueue Plugin Engine Assets
	*/
	public static function load_assets() {

	}

	/*
	* Detect if External Asset is Available
	*/
	public static function detect_external_asset( $file ) {

		$file_headers = @get_headers( $file );
		if ( false === $file_headers || 'HTTP/1.0 404 Not Found' == $file_headers[0] ) {
			return false;
		}

		return true;

	}

}