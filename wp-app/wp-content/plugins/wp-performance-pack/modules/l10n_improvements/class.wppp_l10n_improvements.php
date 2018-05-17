<?php

class WPPP_L10n_Improvements extends WPPP_Module {

	public static $jit_versions = array(
		'4.7.4'	=> '4.7.4',
		'4.7.5'	=> '4.7.4',
		'4.8'	=> '4.8',
		'4.8.1'	=> '4.8.1',
	);

	protected static $options_default = array(
		'use_mo_dynamic'				=> array( 'default' => true, 'type' => 'bool' ),
		'use_jit_localize'				=> array( 'default' => false, 'type' => 'bool' ),
		'disable_backend_translation'	=> array( 'default' => false, 'type' => 'bool' ),
		'dbt_allow_user_override'		=> array( 'default' => false, 'type' => 'bool' ),
		'dbt_user_default_translated'	=> array( 'default' => false, 'type' => 'bool' ),
		'use_native_gettext'			=> array( 'default' => false, 'type' => 'bool' ),
		'mo_caching'					=> array( 'default' => false, 'type' => 'bool' ),
	);

	public function get_default_options () { return static::$options_default; }

	public function is_available () { return true; }

	public function spawn_module () { 
		if ( is_admin() ) {
			return new WPPP_L10n_Improvements_Admin ( $this->wppp );
		} else {
			return new WPPP_L10n_Improvements_Base( $this->wppp );
		}
	}

	public function tabName() { return __( 'Localization', 'wp-performance-pack' ); }
	public function description() { return __( 'Improve performance of WordPress localization by using native gettext or alternative MO file reader, disabling back end translation and just in time localization of scripts.', 'wp-performance-pack' ); }
}

?>