<?php

class WPPP_L10n_Improvements_Simple extends WPPP_Admin_Renderer {
	public function enqueue_scripts_and_styles() {
		parent::enqueue_scripts_and_styles();
	}

	public function add_help_tab () {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'	=> 'wppp_simple_l10n',
			'title'	=> __( 'Improve translation performance', 'wp-performance-pack' ),
			'content'	=>	'<p>' . __( "WPPP offers different levels of improving translation performance. <em>Stable</em> should work on any WordPress blog, <em>Fast</em> further improves performance, but JIT script localization might cause issues with some plugins (if you encounter any problems please report them in the support forums).", 'wp-performance-pack' ) . '</p>',
		) );
	}

	function l10n_detect_current_setting() {
		// off - all options turned off
		if ( !$this->wppp->options['use_mo_dynamic']
			&& !$this->wppp->options['use_jit_localize']
			&& !$this->wppp->options['disable_backend_translation']
			&& !$this->wppp->options['dbt_allow_user_override']
			&& !$this->wppp->options['use_native_gettext']
			&& !$this->wppp->options['mo_caching'] )
			return 0;

		// stable - mo-dynamic/native, caching
		if ( ( $this->wppp->options['use_mo_dynamic'] || $this->is_native_gettext_available() === 0 )
			&& !$this->wppp->options['use_jit_localize']
			&& !$this->wppp->options['disable_backend_translation']
			&& !$this->wppp->options['dbt_allow_user_override']
			&& ( $this->wppp->options['use_native_gettext'] || $this->is_native_gettext_available() !== 0 )
			&& ( $this->wppp->options['mo_caching'] || !$this->is_object_cache_installed() || $this->is_native_gettext_available() === 0 ) )
			return 1;

		// faster - mo-dynamic/native, caching, jit, disable backend, allow user override
		if ( ( $this->wppp->options['use_mo_dynamic'] || $this->is_native_gettext_available() === 0 )
			&& ( $this->wppp->options['use_jit_localize'] || !$this->is_jit_available() )
			&& $this->wppp->options['disable_backend_translation']
			&& $this->wppp->options['dbt_allow_user_override']
			&& ( $this->wppp->options['use_native_gettext'] || $this->is_native_gettext_available() !== 0 )
			&& ( $this->wppp->options['mo_caching'] || !$this->is_object_cache_installed() || $this->is_native_gettext_available() === 0 ) )
			return 2;

		// else custom 
		return 3;
	}

	function l10n_output_active_settings ( $level ) {
		echo '<ul>';
		if ( $level == 0 ) {
			// Off
			$this->e_li_error( __( 'All translation settings turned off.', 'wp-performance-pack' ) );
		} else if ( $level < 3 ) {
			// Stable and Speed
			if ( $this->is_native_gettext_available() == 0 ) {
				$this->e_li_check( __( 'Use gettext', 'wp-performance-pack' ) );
			} else {
				$this->e_li_error( __( 'Gettext not available.', 'wp-performance-pack' ) );
				$this->e_li_check( __( 'Use alternative MO reader', 'wp-performance-pack' ) );
				if ( $this->is_object_cache_installed() ) {
					$this->e_li_check( __( 'Use caching', 'wp-performance-pack' ) );
				} else {
					$this->e_li_error( __( 'No persistent object cache installed.', 'wp-performance-pack' ) );
				}
			}

			if ( $level > 1 ) {
				if ( $this->is_jit_available() ) {
					$this->e_li_check( __( 'Use JIT localize', 'wp-performance-pack' ) );
				} else {
					$this->e_li_error( __( 'JIT localize not available', 'wp-performance-pack' ) );
				}

				$this->e_li_check( __( 'Disable Backend translation', 'wp-performance-pack' ) . ' (' . __( 'Allow user override', 'wp-performance-pack' ) . ')' );
			}
		} else {
			// Custom
			if ( $this->wppp->options['use_native_gettext'] ) {
				$this->e_li_check( __( 'Use gettext', 'wp-performance-pack' ) );
			}
			if ( $this->wppp->options['use_mo_dynamic'] ) {
				$this->e_li_check( __( 'Use alternative MO reader', 'wp-performance-pack' ) );
			}
			if ( $this->wppp->options['mo_caching'] ) {
				$this->e_li_check( __( 'Use caching', 'wp-performance-pack' ) );
			}
			if ( $this->wppp->options['use_jit_localize'] ) {
				$this->e_li_check( __( 'Use JIT localize', 'wp-performance-pack' ) );
			}
			if ( $this->wppp->options['disable_backend_translation'] ) {
				$this->e_li_check( __( 'Disable back end translation', 'wp-performance-pack' ) . ( $this->wppp->options['dbt_allow_user_override'] ? ' (' . __( 'Allow user override', 'wp-performance-pack' ) . ')' : '' ) );
			}
		}
		echo '</ul>';
	}

	public function render_options() {
		$option_keys = array_keys( $this->wppp->get_options_default() );
		unset ( $option_keys [ array_search( 'advanced_admin_view', $option_keys ) ] );
		wp_localize_script( 'wppp-admin-script', 'wpppData', array( json_encode( array(
			'l10n' => array( 'current' => $this->l10n_detect_current_setting(),
							// sequence: stable, speed, current
							'settings' => array( 'use_mo_dynamic' => array(	$this->is_native_gettext_available() != 0,
																			$this->is_native_gettext_available() != 0,
																			$this->wppp->options['use_mo_dynamic'] ),
												'use_jit_localize' => array(	false,
																				$this->is_jit_available(),
																				$this->wppp->options['use_jit_localize'] ),
												'disable_backend_translation' => array(	false,
																						true,
																						$this->wppp->options['disable_backend_translation'] ),
												'dbt_allow_user_override' => array(	false,
																					true,
																					$this->wppp->options['dbt_allow_user_override'] ),
												'dbt_user_default_translated' => array(	true,
																						false,
																						$this->wppp->options['dbt_user_default_translated'] ),
												'use_native_gettext' => array(	$this->is_native_gettext_available() == 0,
																				$this->is_native_gettext_available() == 0,
																				$this->wppp->options['use_native_gettext'] ),
												'mo_caching' => array(	$this->is_native_gettext_available() != 0 && $this->is_object_cache_installed(),
																		$this->is_native_gettext_available() != 0 && $this->is_object_cache_installed(),
																		$this->wppp->options['mo_caching'] ),
							),
			),

			'labels' => array( 'Off' => __( 'Off', 'wp-performance-pack' ),
								'Stable' => __( 'Stable', 'wp-performance-pack' ),
								'Speed' => __( 'Speed', 'wp-performance-pack' ),
								'Custom' => __( 'Custom', 'wp-performance-pack' ), 
								'Webspace' => __( 'Webspace', 'wp-performance-pack' )
			),
		) ) ) );
		?>
		<input type="hidden" <?php $this->e_opt_name('use_mo_dynamic'); ?> value="<?php echo $this->wppp->options['use_mo_dynamic'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('use_jit_localize'); ?> value="<?php echo $this->wppp->options['use_jit_localize'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('disable_backend_translation'); ?> value="<?php echo $this->wppp->options['disable_backend_translation'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dbt_allow_user_override'); ?> value="<?php echo $this->wppp->options['dbt_allow_user_override'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('use_native_gettext'); ?> value="<?php echo $this->wppp->options['use_native_gettext'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('mo_caching'); ?> value="<?php echo $this->wppp->options['mo_caching'] ? 'true' : 'false' ?>" />

		<h3 class="title"><?php _e( 'Improve localization performance', 'wp-performance-pack' ); ?></h3>
		<table style="empty-cells:show; width:100%;">
			<tr>
				<td valign="top" style="width:9em;"><div id="l10n-slider" style="margin-top:1em; margin-bottom: 1em;"></div></td>
				<td valign="top" style="padding-left:2em;">
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Localization improvements turned off', 'wp-performance-pack' ); ?></h4>
						<?php $this->l10n_output_active_settings( 0 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Fast WordPress localization', 'wp-performance-pack' ); ?></h4>
						<p class="description"><?php _e( 'Safe settings that should work with any WordPress installation.', 'wp-performance-pack' );?></p>
						<?php $this->l10n_output_active_settings( 1 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Fastest WordPress localization', 'wp-performance-pack' ); ?></h4>
						<p class="description"><?php _e( 'Fastest localization settings. If any problems occur after activating, switch to stable setting.', 'wp-performance-pack' ); ?></p>
						<?php $this->l10n_output_active_settings( 2 ); ?>
					</div>
					<div class="wppp-l10n-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Custom settings', 'wp-performance-pack' ); ?></h4>
						<p class="description"><?php _e( 'Select your own settings. Customize via advanced view.', 'wp-performance-pack' ); ?></p>
						<?php $this->l10n_output_active_settings( 3 ); ?>
					</div>
				</td>
				<td valign="top" style="width:30%">
					<div class="wppp-l10n-hint" style="display:none"></div>
					<div class="wppp-l10n-hint" style="display:none">
						<?php 
							$native = $this->do_hint_gettext( false );
							if ( $native != 0 ) {
								$this->do_hint_caching();
							}
						?>
					</div>
					<div class="wppp-l10n-hint" style="display:none">
						<?php 
							$this->do_hint_gettext( false ); 
							if ( $native != 0 ) {
								$this->do_hint_caching();
							}
							$this->do_hint_jit( false );
						?>
					</div>
					<div class="wppp-l10n-hint" style="display:none"></div>
				</td>
			</tr>
		</table>
		<hr/>
	<?php
	}
}

?>