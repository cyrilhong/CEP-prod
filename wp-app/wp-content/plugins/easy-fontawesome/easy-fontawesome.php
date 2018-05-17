<?php
/*
* Plugin Name: Easy FontAwesome
* Plugin URI: http://andrewgunn.xyz
* Description: Add FontAwesome css icons to your site.
* Version: 1.0
* Author: Andrew M. Gunn
* Author URI: http://andrewmgunn.com
* Text Domain: easy-fontawesome
* License: GPL2
****
*/
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );


interface I_EFA_Toolbox {
	function __construct();
	function EFA_flush_rewrite_rules();
	function EFA_register_scrolldepth();
}
/**
* Classes and interfaces
*/
class EFA_Toolbox {

	public function __construct() {

		register_activation_hook( __FILE__, array( $this, 'efa_flush_rewrite_rules' ));
		register_deactivation_hook( __FILE__, array( $this, 'efa_flush_rewrite_rules' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'efa_register_fontawesome' ) );

	}

	public function efa_flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	public function efa_register_fontawesome() {
		wp_register_style( 'fontawesome_scss', plugins_url( 'inc/fontawesome/scss/font-awesome.scss', __FILE__ ));
	    wp_register_style( 'fontawesome_css', plugins_url( 'inc/fontawesome/css/font-awesome.css', __FILE__ ));
	    wp_register_style( 'fontawesome_min_css', plugins_url( 'inc/fontawesome/css/font-awesome.min.css', __FILE__ ));
		
		wp_enqueue_style( 'fontawesome_scss' );
		wp_enqueue_style( 'fontawesome_css' );		
		wp_enqueue_style( 'fontawesome_min_css' );
	}

}

$efa = new EFA_Toolbox();
//$ece->ece_init();