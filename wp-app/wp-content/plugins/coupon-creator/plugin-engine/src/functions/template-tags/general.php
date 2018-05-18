<?php
/**
 * Plugin Engine Functions
 */

//If Direct Access Kill the Script
if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) {
	die( 'Access denied.' );
}

if ( ! class_exists( 'Pngx__Main' ) ) {
	return;
}

/**
 * Sanitize URL for display on Front End
 *
 * @param $url
 *
 * @return mixed
 */
function pngx_sanitize_url( $url ) {

	$encode = array( '&amp;', '&#038;', '&' );
	$replacement = '%%PNGXAMP%%';
	$sanitized_url = $url;

	//replace ampersand before escaping to prevent breaking url
	$sanitized_url = str_replace( $encode, $replacement, $sanitized_url );
	$sanitized_url = esc_url( $sanitized_url );

	//add back ampersand after escaping
	return str_replace( $replacement, '&', $sanitized_url );
}