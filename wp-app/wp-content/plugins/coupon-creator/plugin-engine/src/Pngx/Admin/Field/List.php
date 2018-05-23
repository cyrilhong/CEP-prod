<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__List' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__List
 * Select Field
 */
class Pngx__Admin__Field__List {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		$class = isset( $field['class'] ) ? $field['class'] : '';

		if ( ! empty( $options_id ) ) {
			echo '</td></tr><tr valign="top"><td colspan="2">';
		}

		echo '<ul id="' . esc_attr( $field['id'] ) . '" class="pngx-list ' . esc_attr( $class ) . '">';

		foreach ( $field['choices'] as $value => $label ) {

			echo '<li>' . strip_tags( $label, '<span><br><b><strong><em><i><a><img>' ) . '</li>';

		}

		echo '</ul>';

	}

}
