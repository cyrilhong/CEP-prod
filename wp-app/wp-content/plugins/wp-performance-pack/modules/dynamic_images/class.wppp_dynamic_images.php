<?php

class WPPP_Dynamic_Images extends WPPP_Module {
	protected static $options_default = array(
		'dynamic_images'				=> array( 'default' => false, 'type' => 'bool' ),
		'dynamic_images_nosave'			=> array( 'default' => false, 'type' => 'bool' ),
		'dynamic_images_cache'			=> array( 'default' => false, 'type' => 'bool' ),
		'dynamic_images_rthook'			=> array( 'default' => false, 'type' => 'bool' ),
		'dynamic_images_rthook_force'	=> array( 'default' => false, 'type' => 'bool' ),
		'dynamic_images_exif_thumbs'	=> array( 'default' => false, 'type' => 'bool' ),
		'dynimg_quality'				=> array( 'default' => 80,
												  'type' => 'int',
												  'min' => 10,
												  'max' => 100 ),
		'dynimg_serve_method'			=> array( 'default' => 'short_init',
												  'type' => 'enum',
												  'values' => array( 'short_init', 'use_themes' ) ),
		'exif_width'					=> array( 'default' => 320, 'type' => 'int' ),
		'exif_height'					=> array( 'default' => 320, 'type' => 'int' ),
	);

	public function load_renderer () {
		if ( $this->wppp->options['advanced_admin_view'] ) {
			return new WPPP_Dynamic_Images_Advanced ( $this->wppp );
		} else {
			return new WPPP_Dynamic_Images_Simple ( $this->wppp );
		}
	}

	public function is_available () {
		global $wp_rewrite;
		if ( is_multisite() ) {
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			return $wp_rewrite->using_mod_rewrite_permalinks() && $this->wppp->is_network;
		} else {
			return $wp_rewrite->using_mod_rewrite_permalinks();
		}
	}

	public function spawn_module () {
		return new WPPP_Dynamic_Images_Base ( $this->wppp );
	}

	public function validate_options ( &$input, $output ) {
		$output = parent::validate_options( $input, $output );

		if ( $this->wppp->options[ 'dynamic_images' ] !== $output[ 'dynamic_images' ] ) {
			$this->flush_rewrite_rules( $output[ 'dynamic_images' ], $output[ 'dynimg_serve_method' ] );
		}

		return $output;
	}
	
	public function flush_rewrite_rules ( $enabled, $method = false ) {} // Dummy
	
	public function tabName() { return __( 'Images', 'wp-performance-pack' );  }

	public function description() { return __( 'Improve WordPress image handling by creating intermediate images (thumbnails) on demand.', 'wp-performance-pack' ); }
}

?>