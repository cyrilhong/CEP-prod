<?php
/**
 * @package realistic
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Sanitize text
function realistic_sanitize_text( $input ) {
    $allowed_html = array(
        'a' => array(
            'class' => array(),
            'style' => array(),
        ),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
        'i' => array(
            'class' => array(),
            'style' => array(),
        ),
        'img' => array(
            'src' => array(),
            'width' => array(),
            'height' => array(),
            'class' => array(),
            'style' => array(),
        ),
    );

	return wp_kses( $input, $allowed_html );
}

// Sanitize JavaScript
function realistic_sanitize_js( $input ) {
    $allowed_html = array(
        'script' => array(),
    );

	return wp_kses( $input, $allowed_html );
}

// Sanitize checkbox
function realistic_sanitize_checkbox( $input ) {
	if ( $input == 1 ) {
		return 1;
	} else {
		return '';
	}
}

// Sanitize integer
function realistic_sanitize_integer( $input ) {
	return intval( $input );
}

// Sanitize posint
function realistic_sanitize_posint( $input ) {
    return absint( $input );
}

// Sanitize Choices
function realistic_sanitize_choices( $input, $setting ) {
	global $wp_customize;
	$control = $wp_customize->get_control( $setting->id );

	if ( array_key_exists( $input, $control->choices ) ) {
		return $input;
	} else {
		return $setting->default;
	}
}

// Sanitize color ( Validate both HEX & RGBA colors )
function realistic_sanitize_color( $input ) {

    if ( preg_match( '/^#[a-f0-9]{6}$/i', $input ) || preg_match( '/\A^rgba\(([0]*[0-9]{1,2}|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])\s*,\s*([0]*[0-9]{1,2}|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])\s*,\s*([0]*[0-9]{1,2}|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])\s*,\s*([0-9]*\.?[0-9]+)\)$\z/im', $input ) ) {
        return $input;
    }

    return '';
}