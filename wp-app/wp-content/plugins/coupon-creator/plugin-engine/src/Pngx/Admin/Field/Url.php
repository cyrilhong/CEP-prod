<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Url' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Url
 * Text Field
 */
class Pngx__Admin__Field__Url {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name  = $field['id'];
			$value = $meta;
		}

		$size      = isset( $field['size'] ) ? $field['size'] : 30;
		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$repeating = isset( $field['repeating'] ) ? '[]' : '';
		?>
		<input
			type="text"
			class="url <?php echo esc_attr( $class ); ?>"
			id="<?php echo esc_attr( $field['id'] ); ?>"
			name="<?php echo esc_attr( $name ) . $repeating; ?>"
			value="<?php echo esc_url( $value ); ?>"
			size="<?php echo absint( $size ); ?>"
		/>

		<?php

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}

	}

}