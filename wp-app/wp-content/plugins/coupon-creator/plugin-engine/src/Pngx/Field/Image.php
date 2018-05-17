<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Field__Image' ) ) {
	return;
}


/**
 * Class Pngx__Field__Image
 * Wysiwyg Field
 */
class Pngx__Field__Image {

	public static function display( $field = array(), $post_id = null, $meta = null, $template_fields = array(), $var = array() ) {

		$class            = $field['display']['class'] ? $field['display']['class'] : '';
		$style            = Pngx__Style__Linked::get_styles( $field, $post_id );
		$display_img_size = $field['display']['image_size'] ? $field['display']['image_size'] : array();

		$img_size = 'full';
		if ( ! empty( $display_img_size['name'] ) ) {
			$img_size = $display_img_size['name'];
		}

		$image_id  = get_post_meta( $post_id, $field['id'], true );
		$image_id  = wp_get_attachment_image_src( $image_id, $img_size );
		$image_src = $image_id[0];

		if ( $image_src ) {
			?>
			<div class="pngx-image <?php echo esc_attr( $class ); ?>" <?php echo wp_strip_all_tags( $style ); ?>>
				<img src='<?php echo esc_url( $image_src ); ?>' alt='<?php echo get_the_title(); ?>' title='<?php echo get_the_title(); ?>'>
			</div>
			<?php
		}
	}
}
