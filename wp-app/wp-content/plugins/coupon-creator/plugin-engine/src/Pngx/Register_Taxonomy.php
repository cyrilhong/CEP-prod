<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Register_Taxonomy' ) ) {
	return;
}


/**
 * Register Taxonomies with WordPress
 */
class Pngx__Register_Taxonomy {

	/**
	 * Generate Labels for Taxonomy
	 *
	 * @param $singular_tax_name
	 * @param $lc_singular_tax_name
	 * @param $plural_tax_name
	 * @param $lc_plural_tax_name
	 * @param $text_domain
	 *
	 * @return array
	 */
	public static function generate_taxonomy_labels( $singular_tax_name, $lc_singular_tax_name, $plural_tax_name, $lc_plural_tax_name, $text_domain ) {

		$labels = array(
			'name'                       => sprintf( esc_html__( '%s', $text_domain ), $singular_tax_name ),
			'singular_name'              => sprintf( esc_html__( '%s', $text_domain ), $plural_tax_name ),
			'search_items'               => sprintf( esc_html__( 'Search %s', $text_domain ), $plural_tax_name ),
			'all_items'                  => sprintf( esc_html__( 'All %s', $text_domain ), $plural_tax_name ),
			'parent_item'                => sprintf( esc_html__( 'Parent %s', $text_domain ), $plural_tax_name ),
			'parent_item_colon'          => sprintf( esc_html__( 'Parent %s:', $text_domain ), $plural_tax_name ),
			'edit_item'                  => sprintf( esc_html__( 'Edit %s', $text_domain ), $singular_tax_name ),
			'update_item'                => sprintf( esc_html__( 'Update %s', $text_domain ), $plural_tax_name ),
			'add_new_item'               => sprintf( esc_html__( 'Add New %s', $text_domain ), $singular_tax_name ),
			'new_item_name'              => sprintf( esc_html__( 'New %s Name', $text_domain ), $singular_tax_name ),
			'menu_name'                  => sprintf( esc_html__( '%s', $text_domain ), $plural_tax_name ),
			'popular_items'              => sprintf( esc_html__( 'Popular %s', $text_domain ), $plural_tax_name ),
			'separate_items_with_commas' => sprintf( esc_html__( 'Separate %s with commas', $text_domain ), $lc_plural_tax_name ),
			'add_or_remove_items'        => sprintf( esc_html__( 'Add or remove %s', $text_domain ), $lc_singular_tax_name ),
			'choose_from_most_used'      => sprintf( esc_html__( 'Choose from the most used %s', $text_domain ), $lc_plural_tax_name ),
		);

		return $labels;

	}


	/**
	 * Register Taxonomy with WordPress
	 *
	 * @param $taxonomy
	 * @param $post_types
	 * @param $labels
	 * @param $slug
	 * @param $updates
	 */
	public static function register_taxonomy( $taxonomy, $post_types, $labels, $slug, $updates ) {

		$args = Pngx__Main::merge_defaults( array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => false,
			'show_ui'           => true,
			'show_tagcloud'     => false,
			'show_admin_column' => false,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => $slug, 'with_front' => true ),
			'query_var'         => true
		), $updates );

		register_taxonomy( $taxonomy, array( $post_types ), $args );

	}

}