<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Field__Variety' ) ) {
	return;
}


/**
 * Class Pngx__Field__Variety
 * Text Field
 */
class Pngx__Field__Variety {

	public static function display( $field = array(), $post_id = null, $meta = null, $template_fields = array(), $var = array() ) {

		if ( ! isset( $field['variety_choices'][ $meta ] ) ) {
			return;
		}

		$class = $field['display']['class'] ? $field['display']['class'] : '';
		$style = '';
		if ( ! isset( $field['style-target'] ) ) {
			$style = Pngx__Style__Linked::get_styles( $field, $post_id );
		}

		?>

		<div class="pngx-variety <?php echo esc_attr( $class ); ?>" <?php echo wp_strip_all_tags( $style ); ?>>
			<?php
			foreach ( $field['variety_choices'][ $meta ] as $variety_fields ) {

				if ( ! is_array( $variety_fields ) && isset( $template_fields[ $variety_fields ] ) ) {

					Pngx__Fields::display_field( $template_fields[ $variety_fields ], $post_id, $template_fields, $var );

				}

			}
			?>
		</div>
		<?php

	}

}
