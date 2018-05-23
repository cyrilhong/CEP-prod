<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Variety' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Variety
 * Chooser Field for a variety of fields
 */
class Pngx__Admin__Field__Variety {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		global $pagenow;
		$selected = '';

		if ( ! empty( $options_id ) ) {
			$name     = $options_id;
			$selected = $options[ $field['id'] ] ? $options[ $field['id'] ] : $field['std'];
		} else {
			$name = $field['id'];

			// Set Meta Default
			if ( $meta ) {
				$selected = $meta;
			} elseif ( 'post-new.php' === $pagenow && isset( $field['value'] ) ) {
				$selected = $field['value'];
			} elseif ( Pngx__Main::instance()->doing_ajax ) {
				$selected = $field['value'];
			}


			if ( $meta ) {
				$selected = $meta;
			} elseif ( 'post-new.php' === $pagenow && isset( $field['value'] ) ) {
				$selected = $field['value'];
			} elseif ( Pngx__Main::instance()->doing_ajax ) {
				$selected = $field['value'];
			}

			$class     = isset( $field['class'] ) ? $field['class'] : '';
			$repeating = isset( $field['repeating'] ) ? '[]' : '';

			?>
			<div class="pngx-variety-selection">
				<div class="pngx-default-select pngx-default">
					<select
							id="<?php echo esc_attr( $field['id'] ); ?>"
							class="select pngx-variety-select <?php echo esc_attr( $class ); ?>"
							name="<?php echo esc_attr( $name ) . $repeating; ?>"
						<?php echo isset( $field['data'] ) ? Pngx__Admin__Fields::toggle( $field['data'], null ) : ''; ?>
					>
						<?php
						foreach ( $field['choices'] as $value => $label ) {

							$style = isset( $field['class'] ) && 'css-select' === $field['class'] ? 'style="' . esc_attr( $value ) . '"' : '';

							echo '<option ' . $style . ' value="' . esc_attr( $value ) . '"' . selected( $selected, $value, false ) . '>' . esc_attr( $label ) . '</option>';

						}
						?>
					</select>
				</div>
				<?php
				if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
					echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
				}
				?>
			</div>
			<div class="pngx-variety-fields-wrapper">
				<?php

				/**
				 * Filter Template Fields to get infomation to display in the Variety Field
				 */
				$fields = apply_filters( 'pngx_meta_template_fields', array() );

				global $post;

				if ( isset( $field['variety_choices'][ $selected ] ) ) {
					foreach ( $field['variety_choices'][ $selected ] as $label ) {

						if ( is_array( $label ) && isset( $label['open'] ) ) {
							?>
							<div class="<?php echo esc_html( $label['open'] ); ?>">
							<?php
							continue;
						}

						if ( is_array( $label ) && isset( $label['label'] ) ) {
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

						$post_id = '';
						if ( is_object( $post ) ) {
							$post_id = $post->ID;
						} elseif ( Pngx__Main::instance()->doing_ajax && isset( $_POST['post_id'] ) ) {
							$post_id = absint( $_POST['post_id'] );
						}

						$meta = get_post_meta( $post_id, $label, true );

						?>
						<div class="<?php echo isset( $fields[ $label ]['class'] ) ? esc_attr( $fields[ $label ]['class'] ) : ''; ?>">
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
				?>

			</div>
			<?php
		}

	}
}
