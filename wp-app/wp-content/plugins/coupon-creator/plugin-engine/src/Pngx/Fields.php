<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Fields' ) ) {
	return;
}


/**
 * Class Pngx__Fields
 * Front End Fields for Meta and Options
 */
class Pngx__Fields {

	/**
	 * Display Individual Fields
	 *
	 * @param array $field     meta field attributes
	 * @param int   $post_id single post id
	 */
	public static function display_field( $field = array(), $post_id = null, $template_fields = array(), $var = array() ) {

		//Only display fields with display index
		if ( ! isset( $field['display'] ) ) {
			return false;
		}

		// get value of this field if it exists for this post
		$meta = get_post_meta( $post_id, $field['id'], true );

		switch ( $field['display']['type'] ) {

			case 'content':

				Pngx__Field__Content::display( $field, $post_id, $meta, $template_fields, $var );

				break;

			case 'icon':

				Pngx__Field__Icon::display( $field, $post_id, $meta, $template_fields, $var );

				break;

			case 'image':

				Pngx__Field__Image::display( $field, $post_id, $meta, $template_fields, $var );

				break;

			case 'title':

				Pngx__Field__Title::display( $field, $post_id, $meta, $template_fields, $var );

				break;


			case 'variety':

				Pngx__Field__Variety::display( $field, $post_id, $meta, $template_fields, $var );

				break;

			case 'wrap-start':

				Pngx__Field__Wrap::display_start( $field, $post_id, $meta, $template_fields, $var );

				break;

			case 'wrap-end':

				Pngx__Field__Wrap::display_end( $field, $post_id, $meta, $template_fields, $var );

				break;
		}

		if ( has_filter( 'pngx_front_field_types' ) ) {
			/**
			 * Filter the Plugin Engine Fields for Front End
			 *
			 * @param array $field     current field attributes
			 * @param array $post_id current id
			 * @param array $meta      current value of field
			 */
			apply_filters( 'pngx_front_field_types', $field, $post_id, $meta, $template_fields, $var );
		}

	}

}
