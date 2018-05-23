<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


/**
 * Class Pngx__Admin__Ajax
 *
 */
class Pngx__Admin__Ajax {


	public function __construct() {

		$this->start();

	}

	protected function start() {

		add_action( 'wp_ajax_pngx_templates', array( $this, 'load_templates' ) );

		add_action( 'wp_ajax_pngx_variety',  array( $this, 'load_variety' ) );

		add_action( 'wp_ajax_pngx_repeatable',  array( $this, 'load_repeatable' ) );

	}

	public function load_templates() {

		// End if not the correct action
		if ( ! isset( $_POST['action'] ) || 'pngx_templates' !== $_POST['action'] ) {
			wp_send_json_error( __( 'A Permission Error has occurred. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		// End if not correct nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'pngx_admin_' . $_POST['post_id'] ) ) {
			wp_send_json_error( __( 'Permission Error has occurred. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		if ( ! isset( $_POST['option'] ) ) {
			wp_send_json_error( __( 'No Template ID. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		Pngx__Main::instance()->doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		ob_start();

		/**
		 * Filter to Add All Fields for a Plugin
		 */
		$fields = apply_filters( 'pngx_meta_fields', array() );

		foreach ( $fields as $field ) {

			$field_template = isset( $field['template'] ) ? $field['template'] : array();

			if ( $field['type'] && in_array( $_POST['option'], $field_template ) ) {

				// get value of this field if it exists for this post
				$meta = get_post_meta( $_POST['post_id'], $field['id'], true );

				//Wrap Class for Conditionals
				$wrapclass = isset( $field['wrapclass'] ) ? $field['wrapclass'] : '';

				if ( 'wrap-start' === $field['type'] ) {
					?>
					<div class="pngx-meta-fields-wrap admin-field-wrap <?php echo esc_html( $wrapclass ); ?>" >
					<?php
					continue;

				} elseif ( "wrap-end" === $field['type'] ) {

					if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
						echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
					}

					// Display admin linked style fields
					Pngx__Admin__Style__Linked::display_styles( $fields, $field, $_POST['post_id'] );
					?>
					</div>
					<?php
					continue;
				}
				?>

				<div class="pngx-meta-field-wrap field-wrap-<?php echo esc_html( $field['type'] ); ?> field-wrap-<?php echo esc_html( $field['id'] ); ?> <?php echo esc_html( $wrapclass ); ?>"
					<?php echo isset( $field['toggle'] ) ? Pngx__Admin__Fields::toggle( $field['toggle'], esc_attr( $field['id'] ) ) : null; ?> >

					<div class="pngx-meta-field field-<?php echo esc_attr( $field['type'] ); ?> field-<?php echo esc_attr( $field['id'] ); ?>">

						<?php if ( isset( $field['label'] ) && ! empty( $field['label'] ) ) { ?>
							<label for="<?php echo esc_attr( $field['id'] ); ?>">
								<?php echo esc_attr( $field['label'] ); ?>
							</label>
						<?php } ?>

						<?php
						Pngx__Admin__Fields::display_field( $field, false, false, $meta, null );

						// Display admin linked style fields
						Pngx__Admin__Style__Linked::display_styles( $fields, $field, $_POST['post_id'] );

						?>

					</div>
					<!-- end .pngx-meta-field.field-<?php echo esc_attr( $field['type'] ); ?>.field-<?php echo esc_attr( $field['id'] ); ?> -->

				</div> <!-- end .pngx-meta-field-wrap.field-wrap-<?php echo esc_attr( $field['type'] ); ?>.field-wrap-<?php echo esc_attr( $field['id'] ); ?>	-->

				<?php
			}
		} // end foreach fields

		$template_fields = ob_get_contents();

		ob_end_clean();

		wp_send_json_success( json_encode( $template_fields ) );
	}

	public function load_variety() {

		//End if not the correct action
		if ( ! isset( $_POST['action'] ) || 'pngx_variety' != $_POST['action'] ) {
			wp_send_json_error( __( 'Permission Error has occurred. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		//End if not correct nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'pngx_admin_' . $_POST['post_id'] ) ) {
			wp_send_json_error( __( 'Permission Error has occurred. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		if ( ! isset( $_POST['field'] ) ) {
			wp_send_json_error( __( 'No Field ID. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		if ( ! isset( $_POST['option'] ) ) {
			wp_send_json_error( __( 'No Option ID. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		Pngx__Main::instance()->doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		ob_start();

		/**
		 * Filter to Add All Fields for a Plugin
		 */
		$fields = apply_filters( 'pngx_meta_fields', array() );

		if ( isset( $fields[ $_POST['field'] ]['variety_choices'][ $_POST['option'] ] ) ) {
			foreach ( $fields[ $_POST['field'] ]['variety_choices'][ $_POST['option'] ] as $label ) {

				if ( is_array( $label ) && isset( $label['open'] ) ) {
					?>
					<div class="<?php echo esc_html( $label['open'] ); ?>">
					<?php
					continue;
				}

				if ( is_array( $label ) && isset( $label['label'] ) && ! empty( $field['label'] ) ) {
					?>
					<label for="<?php echo esc_attr( $label['label'] ); ?>">
						<?php echo esc_attr( $label['label'] ); ?>
					</label>
					<?php
					continue;
				}

				if ( is_array( $label ) && isset( $label['description'] ) ) {
					?>
					<span class="description"><?php echo esc_html( $label['description'] ); ?></span>
					<?php
					continue;
				}

				if ( is_array( $label ) ) {
					continue;
				}

				if ( 'close' === $label ) {
					?>
					</div>
					<?php
					continue;
				}

				if ( ! isset( $fields[ $label ] ) ) {
					continue;
				}

				$meta = '';
				if ( isset( $_POST['post_id'] ) ) {
					$meta = get_post_meta( $_POST['post_id'], $label, true );
				}

				?>
				<div class="pngx-variety-field <?php echo isset( $fields[ $label ]['class'] ) ? esc_attr( $fields[ $label ]['class'] ) : ''; ?>">
					<?php

					if ( isset( $fields[ $label ]['label'] ) && ! empty( $fields[ $label ]['label'] ) ) { ?>
						<label for="<?php echo esc_attr( $fields[ $label ]['id'] ); ?>">
							<?php echo esc_attr( $fields[ $label ]['label'] ); ?>
						</label>
					<?php }

					Pngx__Admin__Fields::display_field( $fields[ $label ], false, false, $meta, null );

					?>
				</div>
				<?php

			}
		}

		$field = ob_get_contents();

		ob_end_clean();

		wp_send_json_success( json_encode( $field ) );
	}

	public function load_repeatable() {

		//End if not the correct action
		if ( ! isset( $_POST['action'] ) || 'pngx_repeatable' != $_POST['action'] ) {
			wp_send_json_error( __( 'Permission Error has occurred. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		//End if not correct nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'pngx_admin_rep_' . $_POST['post_id'] ) ) {
			wp_send_json_error( __( 'Permission Error has occurred. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		if ( ! isset( $_POST['field'] ) ) {
			wp_send_json_error( __( 'No Field ID. Please save, reload, and try again.', 'plugin-engine' ) );
		}

		if ( ! isset( $_POST['repeat_type'] ) ) {
			//wp_send_json_error( __( 'No Repeat Type Provided Provided.', 'plugin-engine' ) );
		}

		if ( ! isset( $_POST['section'] ) ) {
			wp_send_json_error( __( 'No Section Number Provided.', 'plugin-engine' ) );
		}

		if ( ! isset( $_POST['column'] ) ) {
			wp_send_json_error( __( 'No Column Number Provided.', 'plugin-engine' ) );
		}

		Pngx__Main::instance()->doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		ob_start();

		/**
		 * Filter to Add All Fields for a Plugin
		 */
		$fields = apply_filters( 'pngx_meta_fields', array() );

		if ( ! isset( $fields[ $_POST['field'] ] ) ) {
			wp_send_json_error( __( 'No Field Exists.', 'plugin-engine' ) );
		}

		$field = $fields[ $_POST['field'] ];

		//foreach ( $fields as $field ) {

		if ( ! isset( $repeat_obj ) ) {
			$repeat_obj = new Pngx__Repeater__Main( $_POST['name_id'], false, absint( $_POST['section'] ), absint( $_POST['column'] ) );
		}

		Pngx__Admin__Field__Repeatable::display_repeat_fields( $field['repeatable_fields'], $field, null, $repeat_obj, null );

		//}
		//} // end foreach fields

		$template_fields = ob_get_contents();

		ob_end_clean();

		wp_send_json_success( json_encode( $template_fields ) );

	}

}