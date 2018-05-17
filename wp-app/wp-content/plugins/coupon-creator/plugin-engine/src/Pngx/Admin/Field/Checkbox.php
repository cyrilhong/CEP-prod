<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Checkbox' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Checkbox
 * Checkbox Field
 */
class Pngx__Admin__Field__Checkbox {

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


		if ( $meta ) {
			$selected = $meta;
		} elseif ( $pagenow == 'post-new.php' && isset( $field['value'] ) ) {
			$selected = $field['value'];
		}

		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$std       = isset( $field['std'] ) ? $field['std'] : '';
		$repeating = isset( $field['repeating'] ) ? '[]' : '';

		?>
		<input
				type="checkbox"
				class="checkbox <?php echo esc_attr( $class ); ?>"
				id="<?php echo esc_attr( $field['id'] ); ?>"
				name="<?php echo esc_attr( $name . $repeating ); ?>"
				placeholder="<?php echo esc_attr( $std ); ?>"
				value="1"
				<?php echo checked( $selected, 1, false ); ?>
		/>

		<?php
		if ( isset( $field['label'] ) ) {
			?>
			<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			<?php
		}

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			?>
			<span class="checkbox-description"><?php echo wp_kses_post( $field['desc'] ); ?></span>
			<?php
		}
	}

}
