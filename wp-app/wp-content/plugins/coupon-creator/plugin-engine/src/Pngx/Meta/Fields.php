<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Meta__Fields' ) ) {
	return;
}


class Pngx__Meta__Fields {

	//fields id prefix
	public static $fields_prefix = 'pngx_';

	/*
	* Get Fields ID Prefix
	*/
	public static function get_fields_prefix() {
		return self::$fields_prefix;
	}

	/**
	 * Set Meta Fields
	 *
	 * @return mixed
	 */
	public static function get_fields() {

		//Prefix for fields id
		$prefix = self::get_fields_prefix();

		//Sample Field Array
		$fields[ $prefix . 'heading_deal' ] = array(
			'id'        => $prefix . 'heading_deal', //id
			'title'     => 'PNGX Field Title', //Label  __( 'use translation', 'plugin-engine' )
			'desc'      => 'PNGX Field Description',//description or header __( 'use translation', 'plugin-engine' )
			'type'      => 'heading',//field type
			'section'   => 'plugin_engine_meta_box',//meta box
			'tab'       => 'content',//tab
			'condition' => 'pngx-img',//optional condition used in some fields
			'class'     => 'pngx-img',//optional field class
			'wrapclass' => 'pngx-img',//optional wrap css class
			'toggle'    => array()//field toggle infomation based on value or selection
		);

		return $fields;

	}

}