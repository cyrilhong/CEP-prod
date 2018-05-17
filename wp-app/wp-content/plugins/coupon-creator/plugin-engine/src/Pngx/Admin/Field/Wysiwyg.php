<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Field__Wysiwyg' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Field__Wysiwyg
 * Visual Editor Field
 */
class Pngx__Admin__Field__Wysiwyg {

	public static function display( $field = array(), $options = array(), $options_id = null, $meta = null, $repeat_obj = null ) {

		if ( ! empty( $options_id ) ) {
			$name  = $options_id;
			$value = $options[ $field['id'] ];
		} else {
			$name  = $field['id'];
			$value = $meta;
		}

		global $post, $wp_version;

		$settings = array();
		if ( ! class_exists( '_WP_Editors' ) ) {
			require( ABSPATH . WPINC . '/class-wp-editor.php' );
		}
		$set = _WP_Editors::parse_settings( esc_attr( $field['id'] ), $settings );
		if ( ! current_user_can( 'upload_files' ) ) {
			$set['media_buttons'] = false;
		}
		if ( $set['media_buttons'] ) {
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'media-upload' );
			$post = get_post();
			if ( ! $post && ! empty( $GLOBALS['post_ID'] ) ) {
				$post = $GLOBALS['post_ID'];
			}
			wp_enqueue_media( array(
				'post' => $post
			) );
		}

		wp_enqueue_script( 'tiny_mce' );

		_WP_Editors::editor_settings( esc_attr( $field['id'] ), $set );

		// Only Localize Script Once Per Page
		if ( ! isset( $post->load_scripts ) ) {

			/**
			 * Filter Tiny MCE Buttons for PNGX Editor Script
			 *
			 * @param array() an    array of attributes to create the button
			 * @param         $post current post object
			 */
			$pngx_visual_editor_buttons = apply_filters( 'pngx_visual_editor_functions', array(), $post );

			/**
			 * Filter HTML Editor Buttons for PNGX Editor Script
			 *
			 * @param array() an    array of attributes to create the button
			 * @param         $post current post object
			 */
			$pngx_html_editor_buttons = apply_filters( 'pngx_html_editor_functions', array(), $post );

			/**
			 * Variables for WP Editor Script
			 */
			$pngx_editor_vars = array(
				'url'                   => get_home_url(),
				'includes_url'          => includes_url(),
				'visual_editor_buttons' => $pngx_visual_editor_buttons,
				'html_editor_buttons'   => $pngx_html_editor_buttons,
				'rich_editing'          => get_user_meta( get_current_user_id(), 'rich_editing', true ),
			);
			wp_localize_script( 'pngx-wp-editor', 'pngx_editor_vars', $pngx_editor_vars );


			$post->load_scripts = true;
		}

		$std       = isset( $field['std'] ) ? $field['std'] : '';
		$rows      = isset( $field['rows'] ) ? $field['rows'] : 12;
		$cols      = isset( $field['cols'] ) ? $field['cols'] : 50;
		$class     = isset( $field['class'] ) ? $field['class'] : '';
		$repeating = isset( $field['repeating'] ) ? '[]' : '';

		?>

        <textarea
                class="pngx-ajax-wp-editor <?php echo esc_attr( $class ); ?>"
                id="<?php echo esc_attr( $field['id'] ); ?>"
                name="<?php echo esc_attr( $name ) . $repeating; ?>"
                placeholder="<?php echo esc_attr( $std ); ?>"
                rows="<?php echo absint( $rows ); ?>"
                cols="<?php echo absint( $cols ); ?>"
			<?php echo isset( $field['data'] ) ? Pngx__Admin__Fields::toggle( $field['data'], null ) : ''; ?>
        ><?php
            if ( version_compare( $wp_version, '4.3', '<' ) ) {
                echo wp_htmledit_pre( $value );
            } else {
                echo format_for_editor( $value );
            }
        ?></textarea>
		<?php

		if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) {
			echo '<span class="description">' . esc_html( $field['desc'] ) . '</span>';
		}

	}

}
