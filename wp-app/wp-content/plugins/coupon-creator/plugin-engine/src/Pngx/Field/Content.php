<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Field__Content' ) ) {
	return;
}


/**
 * Class Pngx__Field__Content
 * Title
 */
class Pngx__Field__Content {

	public static function display( $field = array(), $post_id = null, $meta = null, $template_fields = array(), $var = array() ) {

		global $wp_version;

		$class = $field['display']['class'] ? $field['display']['class'] : '';
		$style = Pngx__Style__Linked::get_styles( $field, $post_id );
		$tags  = isset( $field['display']['tags'] ) ? $field['display']['tags'] : 'title';

		if ( ! empty( $var['repeating'] ) && ! empty( $var['value'] ) ) {
			$meta = $var['value'];
		}

		//Apply all the_content filters manually
		$meta = wptexturize( $meta );
		$meta = convert_smilies( $meta );

		/**
		 * Filter Front End Content for WPAutoP
		 *
		 * @param string $meta    content to display
		 * @param int    $post_id id of post
		 * @param array  $field   field attributes
		 */
		$meta = apply_filters( 'pngx_filter_content', $meta, $post_id, $field );

		$meta = shortcode_unautop( $meta );
		$meta = prepend_attachment( $meta );

		//Run Shortcodes
		$meta = do_shortcode( $meta );

		/**
		 * Filter Front End Content Fields
		 *
		 * @param string $meta    content to display
		 * @param int    $post_id id of post
		 * @param array  $field   field attributes
		 */
		$meta = apply_filters( 'pngx_filter_content_field_output', $meta, $post_id, $field );

		?>
		<div class="pngx-content <?php echo esc_attr( $class ); ?>" <?php echo wp_strip_all_tags( $style ); ?>>
			<?php echo strip_tags( $meta, Pngx__Allowed_Tags::$tags() ); ?>
		</div>
		<?php

	}

}
