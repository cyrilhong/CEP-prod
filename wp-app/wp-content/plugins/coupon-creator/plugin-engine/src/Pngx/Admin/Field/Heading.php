<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Heading' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Heading
 * Heading Field
 */
class Pngx__Admin__Field__Heading {

	public static function display( $field = array(), $options_id = null ) {

		if ( ! empty( $options_id ) ) {
			if ( isset( $field['alert'] ) && ! empty( $field['alert'] ) ) {
				echo '</td></tr><tr valign="top"><td colspan="2"><span class="description">' . esc_html( $field['alert'] ) . '</span>';
			} else {
				echo '</td></tr><tr valign="top"><td colspan="2"><h4 class="pngx-fields-heading">' . esc_html( $field['desc'] ) . '</h4>';
			}
		} else {
			echo '<h4 class="pngx-fields-heading">' . esc_html( $field["desc"] ) . '</h4>';
		}

	}

}
