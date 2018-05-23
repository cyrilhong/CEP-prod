<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Field__Title' ) ) {
	return;
}


/**
 * Class Pngx__Field__Title
 * Wysiwyg Field
 */
class Pngx__Field__Title {

	public static function display( $field = array(), $post_id = null, $meta = null, $template_fields = array(), $var = array() ) {

		$class = $field['display']['class'] ? $field['display']['class'] : '';
		$style = Pngx__Style__Linked::get_styles( $field, $post_id );
		$tags  = isset( $field['display']['tags'] ) ? $field['display']['tags'] : 'title';
		$wrap  = isset( $field['display']['wrap'] ) ? $field['display']['wrap'] : 'div';

		if ( empty( $meta ) ) {
			return;
		}
		?>

		<?php echo $wrap ? '<' . esc_attr( $wrap ) . ' class="' . esc_attr( $class ) . '" ' . wp_strip_all_tags( $style ) . '>' : ''; ?>

		<?php echo strip_tags( $meta, Pngx__Allowed_Tags::$tags() ); ?>

		<?php echo $wrap ? '</' . esc_attr( $wrap ) . '>' : ''; ?>

		<?php

	}

}
