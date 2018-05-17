<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Hidden' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Hidden
 * Hidden Field
 */
class Pngx__Admin__Field__Hidden {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name  = $field['id'];
			$value = $meta;
		}

		$class = isset( $field['class'] ) ? $field['class'] : '';

		echo '<input type="text" class="hidden ' . esc_attr( $class ) . '"  id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $name ) . '" placeholder="" value="' . esc_attr( $value ) . '" />';

	}

}
