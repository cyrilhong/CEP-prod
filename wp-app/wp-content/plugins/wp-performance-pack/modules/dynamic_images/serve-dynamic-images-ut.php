<?php
/**
 * Serve intermediate images on demand. Is called via mod_rewrite rule.
 *
 * @author Björn Ahrens
 * @package WP Performance Pack
 * @since 1.1
 */

include( sprintf( "%s/class.wppp_serve_image.php", dirname( __FILE__ ) ) );

class WPPP_Serve_Image_UT extends WPPP_Serve_Image {
	function init() {
		define( 'WP_USES_THEMES', false );
		parent::init();
	}
}

$serve = new WPPP_Serve_Image_UT();
$serve->serve_image();
