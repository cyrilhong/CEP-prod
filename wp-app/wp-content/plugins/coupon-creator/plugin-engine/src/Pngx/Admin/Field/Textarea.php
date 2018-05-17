<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Textarea' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Textarea
 * Textarea Field
 */
class Pngx__Admin__Field__Textarea {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name  = $field['id'];
			$value = $meta;
		}
		global $wp_version;

		$rows      = isset( $field['rows'] ) ? $field['rows'] : 12;
		$cols      = isset( $field['cols'] ) ? $field['cols'] : 50;
		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$std       = isset( $field['std'] ) ? $field['std'] : '';
		$repeating = '';
		if ( $repeat_obj ) {
			$repeating = $repeat_obj->get_current_sec_col() . '[]';
		}

		if ( version_compare( $wp_version, '4.3', '<' ) ) {
			echo '<textarea class="' . esc_attr( $class ) . '" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $name . $repeating ). '" placeholder="' . esc_attr( $std ) . '" rows="' . absint( $rows ) . '" cols="' . absint( $cols ) . '">' . wp_htmledit_pre( $value ) . '</textarea>';
		} else {
			echo '<textarea class="' . esc_attr( $class ) . '" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $name  . $repeating ) . '" placeholder="' . esc_attr( $std ) . '" rows="' . absint( $rows ) . '" cols="' . absint( $cols ) . '">' . format_for_editor( $value ) . '</textarea>';
		}

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}

	}

}
