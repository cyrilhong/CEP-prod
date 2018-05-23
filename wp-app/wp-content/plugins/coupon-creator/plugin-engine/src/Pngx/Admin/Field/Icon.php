<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Icon' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Icon
 * Color Field
 */
class Pngx__Admin__Field__Icon {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {
		global $pagenow;
		$value = '';

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name = $field['id'];
			//Set Meta Default
			if ( $meta ) {
				$value = $meta;
			} elseif ( $pagenow == 'post-new.php' && isset( $field['value'] ) ) {
				$value = $field['value'];
			}
		}

		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$repeating = isset( $field['repeating'] ) ? '[]' : '';
		?>

		<!-- Button tag -->
		<button
				class="btn btn-default pngx-icon-picker <?php echo esc_attr( $class ); ?>"
				data-iconset="fontawesome"
				data-icon="<?php echo esc_attr( $value ); ?>"
				data-label-header="<?php echo sprintf( esc_html_x( '%1s - %2s', 'Icon Popup Header', 'plugin-engine' ), '{0}', '{1}' ); ?>"
				data-arrow-prev-icon-class="glyphicon glyphicon-circle-arrow-left"
				data-arrow-next-icon-class="glyphicon glyphicon-circle-arrow-right"
				data-rows="4"
				data-cols="6"
				data-placement="top"
				data-label-footer="<?php echo sprintf( esc_html_x( '%1s - %2s of %3s', 'Icon Popup Footer', 'plugin-engine' ), '{0}', '{1}', '{2}' ); ?>"
				data-search-text="<?php echo esc_html__( 'Search...', 'plugin-engine' ); ?>"
				name="<?php echo esc_attr( $name . $repeating ); ?>"
				role="iconpicker"
		>
		</button>

		<?php


	}

}

