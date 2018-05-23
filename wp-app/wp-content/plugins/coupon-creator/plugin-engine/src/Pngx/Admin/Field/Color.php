<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Color' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Color
 * Color Field
 */
class Pngx__Admin__Field__Color {

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

		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$std       = isset( $field['std'] ) ? $field['std'] : '';
		$alpha     = isset( $field['alpha'] ) && 'true' === $field['alpha'] ? true : false;
		$repeating = isset( $field['repeating'] ) ? '[]' : '';

		?><div class="pngx-color-picker-wrap">
			<?php
			if ( isset( $field['inside_label'] ) && '' !== $field['inside_label'] ) {
				?>
				<div class="pngx-inside-label"><?php echo esc_html( $field['inside_label'] ); ?></div>
				<?php
			}
			?>
			<input
					type="text"
					class="pngx-color-picker <?php echo esc_attr( $class ); ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					name="<?php echo esc_attr( $name . $repeating ); ?>"
					placeholder="<?php echo esc_attr( $std ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					data-default-color="<?php echo esc_attr( $std ); ?>"
					data-alpha="<?php echo esc_attr( $alpha ); ?>"
			/>
		</div><?php

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			?>
			<span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
			<?php
		}

	}

}

