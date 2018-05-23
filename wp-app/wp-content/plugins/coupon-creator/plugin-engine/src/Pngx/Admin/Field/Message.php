<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Message' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Message
 * Message Field
 */
class Pngx__Admin__Field__Message {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null ) {

		echo '<div class="pngx-message-field">' . strip_tags( $field['desc'], apply_filters( 'cctor_filter_terms_tags', '' ) ) . '</div>';

	}

}
