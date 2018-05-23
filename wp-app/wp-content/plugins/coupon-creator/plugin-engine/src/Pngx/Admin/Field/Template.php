<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Template' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Template
 * Ajax Template Scripts
 */
class Pngx__Admin__Field__Template {

	public static function display() {

		global $post;

		wp_localize_script( 'pngx-load-template-ajax', 'pngx_admin_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			'nonce'   => wp_create_nonce( 'pngx_admin_' . $post->ID ),
			'post_id' => $post->ID
		) );

		wp_localize_script( 'pngx-admin', 'pngx_admin_repeatable_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
			'nonce'   => wp_create_nonce( 'pngx_admin_rep_' . $post->ID ),
			'post_id' => $post->ID
		) );

	}

}