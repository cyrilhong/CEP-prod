<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Pngx__Add_Capabilities' ) ) {
	return;
}


/**
 * Class Pngx__Add_Capabilities
 *
 * Adds Capabilities for a CPT
 */
class Pngx__Add_Capabilities {

	/**
	 * constructor
	 *
	 * @param $post_type
	 * @param $cap_plural
	 */
	public function __construct( $capability_type ) {

		//Administrator
		$caps['administrator'] = array(
			"read_{$capability_type}",
			"read_private_{$capability_type}s",
			"edit_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"delete_{$capability_type}",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
		);
		//Editor
		$caps['editor'] = array(
			"read_{$capability_type}",
			"read_private_{$capability_type}s",
			"edit_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"delete_{$capability_type}",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
		);
		//Author
		$caps['author'] = array(
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"delete_{$capability_type}s",
			"edit_{$capability_type}s",
			"publish_{$capability_type}s",
			"edit_published_{$capability_type}s",
			"delete_published_{$capability_type}s",
		);
		//Contributor
		$caps['contributor'] = array(
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"delete_{$capability_type}s",
			"edit_{$capability_type}s",

		);
		//Subscriber
		$caps['subscriber'] = array(
			"read_{$capability_type}",
		);

		$roles = array(
			get_role( 'administrator' ),
			get_role( 'editor' ),
			get_role( 'author' ),
			get_role( 'contributor' ),
			get_role( 'subscriber' ),
		);

		//Add Capabilities to Role if Role Exists
		foreach ( $roles as $role ) {

			$role_check = '';
			if ( is_object( $role ) ) {
				$role_check = get_role( $role->name );
			}

			if ( ! empty( $role_check ) ) {
				foreach ( $caps[ $role->name ] as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}

		//Set Option to Prevent this from Running Again
		update_option( $capability_type . '_capabilities_register', date( 'l jS \of F Y h:i:s A' ) );

	}

}