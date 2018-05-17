<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Number' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Number
 * Number Field
 */
class Pngx__Admin__Field__Number {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		global $pagenow;

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name  = $field['id'];
			$value = $meta;
		}

		$size       = isset( $field['size'] ) ? $field['size'] : 30;
		$class      = isset( $field['class'] ) ? $field['class'] : '';
		$std        = isset( $field['std'] ) ? $field['std'] : '';
		$numbertype = isset( $field['numbertype'] ) ? $field['numbertype'] : '';
		$repeating  = isset( $field['repeating'] ) ? '[]' : '';

		if ( $pagenow == 'post-new.php' && $std && empty( $value ) ) {
			$value = $std;
		}

		echo '<input 
				type="number" 
				class="regular-number ' . esc_attr( $class ) . '"  
				id="' . esc_attr( $field['id'] ) . '" 
				name="' . esc_attr( $name ) . $repeating . '" 
				placeholder="' . esc_attr( $std ) . '" 
				value="' . esc_attr( $value ) . '" 
				min="0" 
				size="' . absint( $size ) . '"
				style="width:60px; padding-right:0;" 
			/> ' . esc_attr( $numbertype );

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}
	}
}
