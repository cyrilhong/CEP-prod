<?php

class WPPP_CDN_Support extends WPPP_Module {
	protected static $options_default = array(
		'cdn'				=> array( 'default' => false,
									  'type' => 'enum',
									  'values' => array( false, 'coralcdn', 'maxcdn', 'customcdn' ) ),
		'cdnurl'			=> array( 'default' => '', 'type' => 'string' ),
		'cdn_images'		=> array( 'default' => 'both',
									  'type' => 'enum',
									  'values' => array( 'both', 'front', 'back' ) ),
		'dyn_links' 		=> array( 'default' => false, 'type' => 'bool' ),
		'dyn_links_subst'	=> array( 'default' => false, 'type' => 'bool' ),
	);

	public function load_renderer () {
		if ( $this->wppp->options['advanced_admin_view'] ) {
			return new WPPP_CDN_Support_Advanced( $this->wppp );
		} else {
			return new WPPP_CDN_Support_Simple( $this->wppp );
		}
	}

	//public function get_default_options () { return static::$options_default; }

	function is_available () { return true; } // always available

	function spawn_module () {
		return new WPPP_CDN_Support_Base( $this->wppp );
	}

	function validate_options ( &$input, $output ) {
		// option: cdnurl
		if ( isset( $input['cdnurl'] ) ) {
			$cdnurl = trim( sanitize_text_field( $input['cdnurl'] ) );
			unset( $input['cdnurl'] );
		} else {
			$cdnurl = '';
		}
		if ( !empty( $cdnurl ) ) {
			$scheme = parse_url( $cdnurl, PHP_URL_SCHEME );
			if ( empty( $scheme ) ) {
				$cdnurl = 'http://' . $cdnurl;
			}
		}

		$output = parent::validate_options( $input, $output );

		// postprocessing of values
		if ( $output['cdn'] !== 'customcdn' 
			&& $output['cdn'] !== 'maxcdn' )  {
			$output['cdnurl'] = '';
		} else
			$output['cdnurl'] = $cdnurl;

		delete_transient( 'wppp_cdntest' ); // cdn settings might have changed, so delete last test result
		return $output;
	}

	public function tabName() { return __( 'CDN', 'wp-performance-pack' ); }
	public function description() { return __( 'Serve images through CDN. Applies to images on both Frontend (what visitors see) and Backend (your Dashboard).', 'wp-performance-pack' ); }
}

?>