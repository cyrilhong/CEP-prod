<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Fields' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Fields
 * Fields for Meta and Options
 */
class Pngx__Admin__Fields {

	/*
	* Toggle Field Data Setup
	*/
	public static function toggle( $toggle_fields, $id ) {

		if ( isset( $toggle_fields ) && is_array( $toggle_fields ) ) {

			$data = '';

			foreach ( $toggle_fields as $key => $toggle_data ) {
				$toggle = '';
				if ( 'field' == $key ) {
					$toggle = esc_html( $toggle_data ) . '#' . esc_attr( $id );
				} elseif ( 'group' == $key || 'show' == $key || 'update_message' == $key || 'type' == $key || 'connection' == $key ) {
					//handle options page update message in array
					if ( is_array( $toggle_data ) ) {
						if ( isset( $toggle_data[0]['code'] ) ) {
							$toggle = esc_html( $toggle_data[0]['code'] );
						}
					} else {
						$toggle = esc_html( $toggle_data );
					}
				} elseif ( 'id' == $key || 'wp_version' == $key || 'priority' == $key ) {
					$toggle = absint( $toggle_data );
				} elseif ( 'msg' == $key || 'tabs' == $key ) {
					$toggle = json_encode( $toggle_data, JSON_HEX_APOS );
				} else {
					$toggle = esc_html( $toggle_data );
				}

				$data .= 'data-toggle-' . esc_attr( $key ) . '=\'' . $toggle . '\' ';
			}

			return $data;

		}

		return false;
	}

	/*
	* Flush Permalink on Permalink Field Change
	*
	*/
	public static function flush_permalinks() {

		if ( true == get_option( 'pngx_permalink_change' ) ) {

			do_action( 'pngx_flush_permalinks' );

			flush_rewrite_rules();

			update_option( 'pngx_permalink_flush', date( 'l jS \of F Y h:i:s A' ) );
			update_option( 'pngx_permalink_change', false );

		}

	}

	/*
	* Display Individual Fields
	*/
	public static function display_field( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		/**
		 * Filter the Options Name ID for Display of Fields
		 *
		 * @parm string 'plugin_engine_options' name of field id
		 */
		$option_name = apply_filters( 'pngx_options_name_id', 'plugin_engine_options' );

		//Create Different name attribute for Option Fields and Not Meta Fields
		if ( $options && $options_id == $option_name ) {
			$options_id = $options_id . '[' . $field['id'] . ']';
		}

		if ( isset( $field['before'] ) && ! empty( $field['before'] ) ) {
			echo '<span class="before">' . $field['before'] . '</span>';
		}

		switch ( $field['type'] ) {

			case 'checkbox':

				Pngx__Admin__Field__Checkbox::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'color':

				Pngx__Admin__Field__Color::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'date':

				Pngx__Admin__Field__Date::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'heading':

				Pngx__Admin__Field__Heading::display( $field, $options_id );

				break;

			case 'hidden':

				Pngx__Admin__Field__Hidden::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'icon':

				Pngx__Admin__Field__Icon::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'image':

				Pngx__Admin__Field__Image::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'license':

				Pngx__Admin__Field__License::display( $field, $options_id );

				break;

			case 'license_status':

				Pngx__Admin__Field__License_Status::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'list':

				Pngx__Admin__Field__List::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'message':

				Pngx__Admin__Field__Message::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'number':

				Pngx__Admin__Field__Number::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'radio':

				Pngx__Admin__Field__Radio::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'select':

				Pngx__Admin__Field__Select::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'systeminfo':

				Pngx__Admin__Support::getInstance()->get_system_info_copy();
				echo Pngx__Admin__Support::getInstance()->formatted_support_stats();

				break;

			case 'template_chooser':

				Pngx__Admin__Field__Template::display();

				break;

			case 'text':

				Pngx__Admin__Field__Text::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'textarea':

				Pngx__Admin__Field__Textarea::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;


			case 'url':

				Pngx__Admin__Field__Url::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'variety':

				Pngx__Admin__Field__Variety::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;

			case 'wysiwyg':

				Pngx__Admin__Field__Wysiwyg::display( $field, $options, $options_id, $meta, $repeat_obj );

				break;
		}

		if ( has_filter( 'pngx_field_types' ) ) {
			/**
			 * Filter the Plugin Engine Fields for Meta and Options
			 *
			 * @param array $options current field being displayed.
			 * @param array $field   current value of option saved.
			 */
			apply_filters( 'pngx_field_types', $field, $options, $options_id, $meta, $repeat_obj );
		}

		if ( isset( $field['after'] ) && ! empty( $field['after'] ) ) {
			echo '<span class="after description">' . $field['after'] . '</span>';
		}

	}

}
