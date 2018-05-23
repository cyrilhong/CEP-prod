<?php
//If Direct Access Kill the Script
if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) {
	die( 'Access denied.' );
}

if ( class_exists( 'Pngx__Sanitize' ) ) {
	return;
}


/**
 * Class Pngx__Sanitize
 */
class Pngx__Sanitize {

	/**
	 * the field's type
	 *
	 * @var mixed
	 */
	private $type;

	/**
	 * the field's value
	 *
	 * @var mixed
	 */
	private $input;

	/**
	 * field variables
	 *
	 * @var array
	 */
	private $option;

	/**
	 * the result object of the validation
	 *
	 * @var stdClass
	 */
	public $result;

	/**
	 * constructer
	 *
	 * @param $type
	 * @param $input
	 * @param $option
	 */
	function __construct( $type, $input, $option ) {

		$this->type   = $type;
		$this->input  = $input;
		$this->option = $option;

		//Return Sanitized Input only if a method exists to sanitize the field type
		if ( !empty( $this->option['sanitize'] ) && method_exists( $this, 'sanitize_' . $this->option['sanitize'] ) && is_callable( array(
				$this,
				'sanitize_' . $this->option['sanitize']
			) )
		) {

			//result
			$this->result = $this->{'sanitize_' . $this->option['sanitize']}();

		} elseif ( method_exists( $this, 'sanitize_' . $this->type ) && is_callable( array(
				$this,
				'sanitize_' . $this->type
			) )
		) {

			//result
			$this->result = $this->{'sanitize_' . $this->type}();

		} else {

			$this->result = false;
		}

	}

	/**
	 * Icon Sanitize
	 *
	 * @return string
	 */
	private function sanitize_icon() {

		return $this->sanitize_text();

	}

	/**
	 * License Key Sanitize
	 *
	 * @return string
	 */
	private function sanitize_license() {

		return $this->sanitize_text();

	}

	/*
	* License Status Sanitize
	 *
	 * @return string
	 */
	private function sanitize_license_status() {

		return $this->sanitize_text();

	}

	/**
	 * Sanitize Text
	 *
	 * @return string
	 */
	private function sanitize_text() {

		return sanitize_text_field( trim( $this->input ) );

	}

	/**
	 * Sanitize Titles with some html
	 *
	 * @return string
	 */
	private function sanitize_titles() {

		$terms_tags = array(
			'b'      => array(),
			'br'     => array(),
			'em'     => array(),
			'i'      => array(),
			'span'   => array(),
			'strong' => array(),
			'sub'    => array(),
			'sup'    => array(),
		);

		$input = wp_kses( $this->input, $terms_tags );

		return $input;
	}

	/**
	 * Sanitize Textarea
	 *
	 * @return string
	 */
	private function sanitize_textarea() {

		if ( isset( $this->option['class'] ) && "code" != $this->option['class'] ) {

			global $allowedtags;
			$input = wp_kses( $this->input, $allowedtags );

		} else {
			$input = wp_kses_post( $this->input );
		}

		return $input;
	}

	/**
	 * Wysiwyg Sanitize
	 *
	 * @return string
	 */
	private function sanitize_wysiwyg() {

		if ( current_user_can( 'unfiltered_html' ) ) {
			$input = $this->input;
		} else {
			global $allowedtags;
			$input = wp_kses( $this->input, $allowedtags );
		}

		return $input;

	}

	/**
	 * Sanitize urls
	 *
	 * @return string
	 */
	private function sanitize_url() {

		return esc_url_raw( $this->input );

	}

	/**
	 * Select Sanitize
	 *
	 * @return mixed|string
	 */
	private function sanitize_select() {

		return $this->sanitize_enum();

	}

	/**
	 * Select Variety Select
	 *
	 * @return mixed|string
	 */
	private function sanitize_variety() {

		return $this->sanitize_enum();

	}

	/**
	 * Select Page Sanitize
	 *
	 * @return mixed|string
	 */
	private function sanitize_selectpage() {

		return $this->sanitize_enum();

	}

	/**
	 * Radio Sanitize
	 *
	 * @return mixed|string
	 */
	private function sanitize_radio() {

		return $this->sanitize_enum();

	}

	/**
	 * Select and Radio Sanitize
	 *
	 * @return mixed|string
	 */
	private function sanitize_enum() {

		if ( array_key_exists( $this->input, $this->option['choices'] ) ) {
			$this->input = sanitize_text_field( $this->input );
		}

		return $this->input;
	}

	/**
	 * Checkbox Sanitize
	 *
	 * @return bool|mixed|string
	 */
	private function sanitize_checkbox() {
		if ( $this->input ) {
			$this->input = '1';
		} else {
			$this->input = false;
		}

		return $this->input;
	}

	/**
	 * Sanitize Date
	 *
	 * @return mixed
	 */
	private function sanitize_date() {

		$this->input = preg_replace( "([^0-9/])", "", $this->input );

		return $this->input;
	}

	/**
	 * Color Sanitize
	 *
	 * @return bool|mixed
	 */
	private function sanitize_color() {

		// If string does not start with 'rgba', then treat as hex
		// sanitize the hex color and finally convert hex to rgba
		if ( false === strpos( $this->input, 'rgba' ) ) {
			$this->validate_hex( $this->input );

			return $this->input;
		}

		if ( $this->validate_rgba( $this->input ) ) {
			return $this->input;
		}

		return false;
	}

	/**
	 * Hex Sanitize
	 *
	 * @param $hex
	 *
	 * @return bool
	 */
	private function validate_hex( $hex ) {
		$hex = trim( $hex );
		if ( 0 === strpos( $hex, '#' ) ) {
			$hex = substr( $hex, 1 );
		} elseif ( 0 === strpos( $hex, '%23' ) ) {
			$hex = substr( $hex, 3 );
		}
		if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Validate RGBA Inputs
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	private function validate_rgba( $rgba ) {

		// If empty or an array return transparent
		if ( empty( $rgba ) || is_array( $rgba ) ) {
			$this->input = '';

			return false;
		}
		$red  = $green = $blue = $alpha = '';
		$rgba = str_replace( ' ', '', $rgba );

		sscanf( $rgba, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );
		$this->input = 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';

		return true;

	}

	/**
	 * Image ID Sanitize
	 *
	 * @return string
	 */
	private function sanitize_image() {

		return $this->sanitize_absint();

	}

	/**
	 * Pro Image ID Sanitize
	 *
	 * @return string
	 */
	private function sanitize_proimage() {

		return $this->sanitize_absint();

	}

	/**
	 * Numbers ID Sanitize
	 *
	 * @return string
	 */
	private function sanitize_number() {

		return $this->sanitize_absint();

	}

	/**
	 * Numbers ID Sanitize
	 *
	 * @return string
	 */
	private function sanitize_dimensions() {

		return $this->sanitize_absint();

	}

	/**
	 * A 32bit absolute integer method, returns as String
	 *
	 * @return bool|mixed
	 */
	private function sanitize_absint() {
		// If it's not numeric we forget about it
		if ( ! is_numeric( $this->input ) ) {
			return false;
		}

		$this->input = preg_replace( '/[^0-9]/', '', $this->input );

		// After the Replace return false if Empty
		if ( empty( $this->input ) ) {
			return false;
		}

		// After that it should be good to ship!
		return $this->input;
	}

	/**
	 * Sanitize Google Analytics
	 *
	 * @return bool|mixed
	 */
	private function sanitize_ga_analytics() {

		$this->input = trim( esc_html( $this->input ) );
		// en dash to minus, prevents issue with code copied from web with "fancy" dash
		$this->input = str_replace( '�', '-', $this->input );

		if ( ! preg_match( '|^UA-\d{4,}-\d+$|', $this->input ) ) {

			return false;

		} else {

			return $this->input;

		}

	}

} //end