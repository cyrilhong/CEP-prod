<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Field__Icon' ) ) {
	return;
}


/**
 * Class Pngx__Field__Expiration
 * Text Field
 */
class Pngx__Field__Icon {

	public static function display( $field = array(), $post_id = null, $meta = null, $template_fields = array(), $var = array() ) {

		$class = $field['display']['class'] ? $field['display']['class'] : '';
		$style = Pngx__Style__Linked::get_styles( $field, $post_id );
		?>

		<div class="pngx-icon <?php echo esc_attr( $class ); ?>" <?php echo wp_strip_all_tags( $style ); ?>>

			<?php echo '<i class="fa ' . esc_html( $meta ) . '"></i>'; ?>

		</div>

		<?php

	}

}
