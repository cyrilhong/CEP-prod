<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


/**
 * Blog Date and Time
 *
 *
 */
class Pngx__Date {

	public static function display_date( $format = false, $display_time = false, $custom_format = false ) {

		//Blog Time According to WordPress
		$blog_time = current_time( 'mysql' );

		//Display Time with Date using WordPress Setting Format
		$time = '';
		if ( $display_time ) {
			$time = ' ' . get_option( 'time_format' );
		}

		//Display Current Date and Time with Custom Format
		if ( $custom_format ) {
			return date( esc_attr( $custom_format ), strtotime( $blog_time ) );

			//Display Month First
		} elseif ( 0 == $format ) {
			return date( 'm/d/Y' . $time, strtotime( $blog_time ) );

			//Display Day First
		} elseif ( 1 == $format ) {
			return date( 'd/m/Y' . $time, strtotime( $blog_time ) );

			//Default to Display by WordPres Date Format
		} else {
			return date( get_option( 'date_format' ) . $time, strtotime( $blog_time ) );
		}


	}

}