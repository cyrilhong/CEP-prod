<?php

/**
 * Handles everything this plugin does on admin including: notifications, forms, options page register and save
 */
class  Sigami_Livereload_Admin extends Sigami_Livereload {
	const page_slug = 'livereload';
	const page_name = 'Livereload';

	static function hooks() {
		add_action( 'admin_menu', __CLASS__ . '::add_page' );
		add_action( 'admin_init', __CLASS__ . '::admin_init' );
	}

	/**
	 * Add WP Embed Facebook page to Settings
	 */
	static function add_page() {
		add_options_page( self::page_name, self::page_name, 'manage_options', self::page_slug, array(
			__CLASS__,
			'page'
		) );
	}

	/**
	 * Add template editor style to the embeds.
	 */
	static function admin_init() {
		register_setting( "Sigami_Livereload_Admin", self::option_name );
	}

	/**
	 * Render form sections
	 *
	 * @param string|bool $title
	 */
	static function section( $title = '' ) {
		if ( $title ) :
			if ( is_string( $title ) )
				echo "<h3>$title</h3>"
			?>
			<table>
			<tbody>
			<?php
		else :
			?>
			</tbody>
			</table>
			<?php
		endif;
	}

	/**
	 * Render form fields
	 *
	 * @param string $type  Type of input field
	 * @param string $name  Input name
	 * @param string $label Input Label
	 * @param array  $args
	 * @param array  $atts  Embed attributes
	 */
	static function field( $type, $name = '', $label = '', $args = array(), $atts = array() ) {
		/** @since 1.0.0 */
		$options    = apply_filters( self::option_name . '_field_options', self::get_option() );
		$attsString = '';
		if ( ! empty( $atts ) ) {
			foreach ( $atts as $att => $val ) {
				$attsString .= $att . '="' . $val . '" ';
			}
		}
		switch ( $type ) {
			case 'checkbox':
				$checked = ( $options[ $name ] === 'on' ) ? 'checked' : '';
				ob_start();
				?>
				<tr valign="middle">
					<th<?php
					?>><label
							for="<?php echo $name ?>"><?php echo $label ?></label></th>
					<td>
						<input type="checkbox" id="<?php echo $name ?>"
						       name="<?php echo self::option_name . "[$name]" ?>" <?php echo $checked ?> <?php echo $attsString ?>/>
					</td>
				</tr>
				<?php
				ob_end_flush();
				break;
			case 'select' :
				$option = $options[ $name ];
				ob_start();
				?>
				<tr valign="middle">
					<th><label for="<?php echo self::option_name . "[$name]" ?>"><?php echo $label ?></label></th>
					<td>
						<select name="<?php echo self::option_name . "[$name]" ?>" <?php echo $attsString ?>>
							<?php
							foreach ( $args as $value => $name ) :
								if ( is_numeric( $value ) ) {
									$value = $name;
								}
								?>
								<option
									value="<?php echo $name ?>" <?php echo $option == $value ? 'selected' : '' ?>><?php echo $name ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<?php
				ob_end_flush();
				break;
			case 'number' :
				ob_start();
				?>
				<tr valign="middle">
					<th><label for="<?php echo self::option_name . "[$name]" ?>"><?php echo $label ?></label></th>
					<td>
						<input id="<?php echo $name ?>"
						       type="<?php echo $type ?>"
						       name="<?php echo self::option_name . "[$name]" ?>" <?php echo isset( $args['required'] ) ? 'required' : '' ?>
						       value="<?php echo $options[ $name ] ?>" <?php echo $attsString ?>/>
					</td>
				</tr>
				<?php
				ob_end_flush();
				break;
			case 'string' :
				ob_start();
				?>
				<tr valign="middle">
					<th><?php echo $label ?></th>
					<td>
						<?php echo $name ?>
					</td>
				</tr>
				<?php
				ob_end_flush();
				break;
			default:
				ob_start();
				?>
				<tr valign="middle">
					<th><label for="<?php echo self::option_name . "[$name]" ?>"><?php echo $label ?></label></th>
					<td>
						<input id="<?php echo $name ?>"
						       type="<?php echo $type ?>"
						       name="<?php echo self::option_name . "[$name]" ?>" <?php echo isset( $args['required'] ) ? 'required' : '' ?>
						       value="<?php echo esc_attr( $options[ $name ] ) ?>" <?php echo $attsString ?>
						       class="regular-text"/>
					</td>
				</tr>
				<?php
				ob_end_flush();
				break;
		}
	}

	/**
	 * Renders the wp-admin settings page
	 */
	static function page() {
		if ( isset( $_POST['restore-data'] ) && wp_verify_nonce( $_POST['restore-data'], 'W7ziLKoLojka' ) ) {
			update_option( self::option_name, self::get_defaults(), true );
		}
		?>
		<div class="wrap">
			<h2>Livereload</h2>

			<form action="options.php" method="post">
				<?php settings_fields( 'Sigami_Livereload_Admin' ); ?>
				<section id="general" class="sections">
					<?php
					self::section( __( 'General options', 'sigami-livereload' ) );
					self::field( 'number', 'port', __( 'Port', 'sigami-livereload' ) );
					self::field( 'text', 'host', __( 'Host', 'sigami-livereload' ) );
					self::field( 'checkbox', 'in_frontend', __( 'In frontend', 'sigami-livereload' ) );
					self::field( 'checkbox', 'in_admin', __( 'In admin', 'sigami-livereload' ) );
					self::field( 'checkbox', 'in_footer', __( 'In footer', 'sigami-livereload' ) );
					self::section();
					?>
				</section>

				<?php submit_button(); ?>
			</form>
			<br>
			<form method="post"
			      onsubmit="return confirm('<?php _e( 'Restore default values?', 'sigami-livereload' ) ?>');">
				<input type="submit" name="restore" class="button"
				       value="<?php _e( 'Restore defaults', 'sigami-livereload' ) ?>"/>
				<br>
				<?php wp_nonce_field( 'W7ziLKoLojka', 'restore-data' ); ?>
				<br>
			</form>
		</div>
		<?php
	}

}

Sigami_Livereload_Admin::hooks();




