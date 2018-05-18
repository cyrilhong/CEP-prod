<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Field__Wrap' ) ) {
	return;
}


/**
 * Class Pngx__Field__Wrap
 * Title
 */
class Pngx__Field__Wrap {

	public static function display_start( $field = array(), $post_id = null, $meta = null, $template_fields = array(), $var = array() ) {

		$class = $field['display']['class'] ? $field['display']['class'] : '';
		$style = Pngx__Style__Linked::get_styles( $field, $post_id );

		?>
		<div class="pngx-content-wrap <?php echo esc_attr( $class ); ?>" <?php echo wp_strip_all_tags( $style ); ?>>
		<?php

	}


	public static function display_end( $field = array(), $post_id = null, $meta = null, $template_fields = array(), $var = array() ) {

		?>
		</div>
		<?php

	}

}
