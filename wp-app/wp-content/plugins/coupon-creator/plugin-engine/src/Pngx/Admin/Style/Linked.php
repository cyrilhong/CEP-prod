<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Style__Linked' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Style__Linked
 * Admin Linked Style Fields
 */
class Pngx__Admin__Style__Linked {

	public static function display_styles( $fields = array(), $field = array(), $post_id = null ) {

		// Display Linked Style Fields
		if ( isset( $field['styles'] ) && is_array( $field['styles'] ) ) {

			?>
			<div class="pngx-meta-field-styles field-<?php echo $field['type']; ?>-styles field-<?php echo $field['id']; ?>-styles">
				<div class="inline-style-title"><?php echo esc_html__( 'Field Styles', 'coupon-creator-add-ons' ); ?></div>
				<?php

				foreach ( $field['styles'] as $type => $field_name ) {

					if ( ! isset( $fields[ $field_name ] ) ) {
						continue;
					}

					if ( 'font-color' === $type || 'background-color' === $type || 'background-color:hover' === $type ) {
						$meta = get_post_meta( $post_id, $field_name, true );
						Pngx__Admin__Field__Color::display( $fields[ $field_name ], false, false, $meta );
					}

				}

				?>
			</div>
			<?php
		}

		return;

	}

}
