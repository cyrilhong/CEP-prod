<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Image' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Text
 * Image Field
 */
class Pngx__Admin__Field__Image {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		global $pagenow;

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name  = $field['id'];
			$value = $meta;
		}

		if ( 'post-new.php' == $pagenow && ! $value && isset( $field['std'] ) ) {
			$value = $field['std'];
		}

		$imagemsg  = isset( $field['imagemsg'] ) ? $field['imagemsg'] : '';
		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$repeating = isset( $field['repeating'] ) ? '[]' : '';
		$imagesrc  = '';

		if ( is_numeric( $value ) ) {
			$imagesrc     = wp_get_attachment_image_src( absint( $value ), 'medium' );
			$imagesrc     = $imagesrc[0];
			$imagedisplay = '<div style="display:none" id="' . esc_attr( $field['id'] ) . '" class="pngx-default-image pngx-image-wrap">' . esc_html( $imagemsg ) . '</div> <img src="' . $imagesrc . '" id="' . esc_attr( $field['id'] ) . '" class="pngx-image pngx-image-wrap-img" />';
		} else {
			$imagedisplay = '<div style="display:block" id="' . esc_attr( $field['id'] ) . '" class="pngx-default-image pngx-image-wrap">' . esc_html( $imagemsg ) . '</div> <img style="display:none" src="' . $imagesrc . '" id="' . esc_attr( $field['id'] ) . '" class="pngx-image pngx-image-wrap-img" />';
		}

		echo $imagedisplay . '<br>';

		?>

		<input
				class="pngx-upload-image <?php echo esc_attr( $class ); ?>"
				type="hidden" id="<?php echo esc_attr( $field['id'] ); ?>"
				name="<?php echo esc_attr( $name ) . $repeating; ?>"
				value="<?php echo esc_attr( $value ); ?>"
		/>

		<button id="<?php echo esc_attr( $field['id'] ); ?>" class="pngx-image-button" <?php echo isset( $field['function'] ) ? Pngx__Admin__Fields::toggle( $field['function'], $field['id'] ) : null; ?> >Upload Image</button>

		<small><a href="#" id="<?php echo esc_attr( $field['id'] ); ?>" class="pngx-clear-image">Remove Image</a></small>

		<?php
		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}

	}

}
