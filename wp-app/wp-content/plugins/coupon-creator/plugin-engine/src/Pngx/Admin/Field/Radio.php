<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Radio' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Radio
 * Radio Field
 */
class Pngx__Admin__Field__Radio {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		global $pagenow;
		$selected = '';

		if ( ! empty( $options_id ) ) {
			$name     = $options_id;
			$selected = $options[ $field['id'] ];
		} else {
			$name = $field['id'];

			//Set Meta Default
			if ( $meta ) {
				$selected = $meta;
			} elseif ( $pagenow == 'post-new.php' && isset( $field['value'] ) ) {
				$selected = $field['value'];
			}
		}

		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$repeating = isset( $field['repeating'] ) ? '[]' : '';

		$i = 0;
		foreach ( $field['choices'] as $value => $label ) {
			echo '<input type="radio" class="radio ' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . $repeating . '" id="' . $field['id'] . $i . '" value="' . esc_attr( $value ) . '" ' . checked( $selected, $value, false ) . '>
			<label for="' . $field['id'] . $i . '">' . esc_attr( $label ) . '</label>';
			if ( $i < count( $options ) - 1 ) {
				echo '<br>';
			}
			$i ++;
		}

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}


	}

}
