<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Text' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Text
 * Text Field
 */
class Pngx__Admin__Field__Text {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name  = $field['id'];
			$value = $meta;
			if ( ! $value && isset( $field['value'] ) ) {
				$value = $field['value'];
			}
		}

		$size      = isset( $field['size'] ) ? $field['size'] : 30;
		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$std       = isset( $field['std'] ) ? $field['std'] : '';
		$condition = isset( $field['condition'] ) ? $field['condition'] : '';

		if ( $repeat_obj ) {
			$name = $repeat_obj->get_field_name( $name );
		}

		if ( isset( $field['alert'] ) && '' != $field['alert'] && 1 == $condition ) {
			echo '<div class="pngx-error">&nbsp;&nbsp;' . esc_html( $field['alert'] ) . '</div>';
		}

		echo '<input type="text" id="' . esc_attr( $field['id'] ) . '" class="regular-text ' . esc_attr( $class ) . '"  name="' . esc_attr( $name ) . '" placeholder="' . esc_attr( $std ) . '" value="' . esc_attr( $value ) . '" size="' . absint( $size ) . '" />';

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}

	}

}
