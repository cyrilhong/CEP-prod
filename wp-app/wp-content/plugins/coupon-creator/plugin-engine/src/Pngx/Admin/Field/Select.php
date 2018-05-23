<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Select' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Select
 * Select Field
 */
class Pngx__Admin__Field__Select {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		global $pagenow;
		$selected = '';

		if ( ! empty( $options_id ) ) {
			$name     = $options_id;
			$selected = $options[ $field['id'] ] ? $options[ $field['id'] ] : $field['std'];
		} else {
			$name = $field['id'];

			//Set Meta Default
			if ( $meta ) {
				$selected = $meta;
			} elseif ( $pagenow == 'post-new.php' && isset( $field['value'] ) ) {
				$selected = $field['value'];
			}
		}


		if ( $meta ) {
			$selected = $meta;
		} elseif ( $pagenow == 'post-new.php' && isset( $field['value'] ) ) {
			$selected = $field['value'];
		}

		$class = isset( $field['class'] ) ? $field['class'] : '';

		if ( $repeat_obj ) {
			$name = $name = $repeat_obj->get_field_name( $name );
		}

		?>
		<div class="pngx-default-select pngx-default <?php echo esc_attr( $class ); ?>">
			<select
					id="<?php echo esc_attr( $field['id'] ); ?>"
					class="select"
					name="<?php echo esc_attr( $name ); ?>"
					<?php echo isset( $field['data'] ) ? Pngx__Admin__Fields::toggle( $field['data'], null ) : ''; ?>
			>
				<?php
				foreach ( $field['choices'] as $value => $label ) {

					$style = isset( $field['class'] ) && 'css-select' == $field['class'] ? 'style="' . esc_attr( $value ) . '"' : '';

					echo '<option ' . esc_textarea( $style ) . ' value="' . esc_attr( $value ) . '"' . selected( $selected, $value, false ) . '>' . esc_attr( $label ) . '</option>';

				}
				?>
			</select>
		</div>
		<?php

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}

	}

}
