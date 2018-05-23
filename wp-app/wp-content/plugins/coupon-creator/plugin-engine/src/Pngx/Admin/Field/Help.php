<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Help' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Help
 * Help Fields
 */
class Pngx__Admin__Field__Help {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null ) {

		if ( ! empty( $options_id ) ) {
			$tab       = $field['section'];
			$screen_id = 'plugin_engine_options_plugin-engine-options';
		} else {
			$tab       = $field['tab'];
			$screen_id = '';
		}

		if ( 'pmgx_all_help' == $field['id'] ) {
			$help_class = new Pngx__Admin__Help();
			$help_class->display_help( 'all', false, 'pngx' );

			//Return as this is only showing all the help documents
			return;
		}

		//Display Help Per Tab
		$help_class = new Pngx__Admin__Help();
		$help_class->display_help( $tab, $screen_id, 'pngx' );

	}

}
