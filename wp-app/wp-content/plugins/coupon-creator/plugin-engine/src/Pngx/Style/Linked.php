<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Style__Linked' ) ) {
	return;
}


/**
 * Class Pngx__Style__Linked
 * Front End Linked Style Fields
 */
class Pngx__Style__Linked {

	public static function get_styles( $field = array(), $post_id = null, $inline = true, $target = false ) {

		$style = '';

		if ( ! isset( $field['styles'] ) ) {
			return false;
		}

		if ( ! is_array( $field['styles'] ) ) {
			return false;
		}

		if ( $inline ) {
			$style = ' style=" ';
			foreach ( $field['styles'] as $type => $field_name ) {

				if ( 'font-color' === $type && $color = get_post_meta( $post_id, $field_name, true ) ) {
					$style .= 'color:' . $color . '; ';
				}

				if ( 'background-color' === $type && $color = get_post_meta( $post_id, $field_name, true ) ) {
					$style .= 'background-color:' . $color . '; ';
				}

				if ( 'background-color:hover' === $type && $color = get_post_meta( $post_id, $field_name, true ) ) {
					$style .= 'background-color:hover ' . $color . '; ';
				}

			}
			$style .= ' " ';
		} elseif ( ! $inline && is_array( $target ) ) {

			$style = '<style>';

			foreach ( $field['styles'] as $type => $field_name ) {

				if ( 'font-color' === $type && $color = get_post_meta( $post_id, $field_name, true ) ) {
					$style .= '.' . esc_attr( $target['wrap'] ) . absint( $post_id ) . ' .' . esc_attr( $target['selector'] ) . '{ color:' . esc_attr( $color ) . '; }';
				}

				if ( 'background-color' === $type && $color = get_post_meta( $post_id, $field_name, true ) ) {
					$style .= '.' . esc_attr( $target['wrap'] ) . absint( $post_id ) . ' .' . esc_attr( $target['selector'] ) . '{ background-color:' . esc_attr( $color ) . '; }';
				}

				//todo create a filter here and modify the output using that instead of adding specific styling
				if ( 'background-color-fold' === $type && $color = get_post_meta( $post_id, $field['styles']['background-color'], true ) ) {
					$style .= '.' . esc_attr( $target['wrap'] ) . absint( $post_id ) . ' .' . esc_attr( $target['selector'] ) . ':after { 
							border-left: solid 6px' . esc_attr( $color ) . '; 
							border-top: solid 6px' . esc_attr( $color ) . '; 
						}';

					$style .= '.' . esc_attr( $target['wrap'] ) . absint( $post_id ) . ' .' . esc_attr( $target['selector'] ) . '.revealed:after { 
							background: none;
							border: none;
							box-shadow: none;
						}';
				}

				if ( 'background-color:hover' === $type && $color = get_post_meta( $post_id, $field_name, true ) ) {
					$style .= '.' . esc_attr( $target['wrap'] ) . absint( $post_id ) . ' .' . esc_attr( $target['selector'] ) . ':hover' . '{ background-color:' . esc_attr( $color ) . '; }';
				}

			}
			$style .= '</style>';

		}

		return $style;

	}

}
