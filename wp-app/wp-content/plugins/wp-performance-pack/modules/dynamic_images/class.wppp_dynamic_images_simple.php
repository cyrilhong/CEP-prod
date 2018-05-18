<?php

class WPPP_Dynamic_Images_Simple extends WPPP_Admin_Renderer {
	public function enqueue_scripts_and_styles () {
		parent::enqueue_scripts_and_styles();
	}

	public function add_help_tab () {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'	=> 'wppp_simple_dynimg',
			'title'	=> __( 'Improve image handling', 'wp-performance-pack' ),
			'content'	=> '<p>' . __( "Improve image upload speed and web space usage using this setting. Creation of different image sizes will be delayed upon the first access to the respective image. <em>Fast</em> uses EXIF thumbs to create small image sizes, which might cause issues as EXIF thumbs and actual images might differ (depending on used image editing software). Use <em>Webspace</em> only for testing environments or if you are really low on webspace, as this option will slow down your blog because intermediate images don't get saved to disc.", 'wp-performance-pack' ) . '</p>',
		) );
	}

	function dynimg_detect_current_setting () {
		// off - all options turend off
		if ( !$this->wppp->options['dynamic_images'] ) {
			return 0;
		}
		
		// stable - dynimg enabled, image quality 80%
		if ( $this->wppp->options['dynamic_images']
			&& $this->wppp->options['dynimg_quality'] == 80
			&& !$this->wppp->options['dynamic_images_nosave']
			&& !$this->wppp->options['dynamic_images_rthook']
			&& !$this->wppp->options['dynamic_images_exif_thumbs'] ) {
			return 1;
		}

		// speed - same as stable, including exif
		if ( $this->wppp->options['dynamic_images']
			&& $this->wppp->options['dynimg_quality'] == 80
			&& !$this->wppp->options['dynamic_images_nosave']
			&& !$this->wppp->options['dynamic_images_rthook']
			&& $this->wppp->options['dynamic_images_exif_thumbs'] ) {
			return 2;
		}

		// webspace - same as speed, including no_save, cache and regen-integration
		if ( $this->wppp->options['dynamic_images']
			&& $this->wppp->options['dynimg_quality'] == 80
			&& $this->wppp->options['dynamic_images_nosave']
			&& ( $this->wppp->options['dynamic_images_cache'] || !$this->is_object_cache_installed() )
			&& ( $this->wppp->options['dynamic_images_rthook'] || !$this->is_regen_thumbs_available() )
			&& !$this->wppp->options['dynamic_images_rthook_force']
			&& ( $this->wppp->options['dynamic_images_exif_thumbs'] || !$this->is_exif_available() ) ) {
			return 3;
		}

		// else custom
		return 4;
	}

	function dynimg_output_active_settings ( $level ) {
		echo '<ul>';
		if ( $level == 0 ) {
			// Off
			$this->e_li_error( __( 'All improved image handling settings disabled.', 'wp-performance-pack' ) );
		} else {
			if ( !$this->is_dynamic_images_available() ) {
				$this->e_li_error( __( 'Pretty Permalinks must be enabled for improved image handling', 'wp-performance-pack' ) );
			} else {
				$this->e_li_check( __( 'Dynamic image resizing enabled', 'wp-performance-pack' ) );
				if ( $level < 4 ) {
					$this->e_li_check( __( 'Intermediate image quality set to 80%', 'wp-performance-pack' ) );
					if ( $level > 1 ) {
						if ( $this->is_exif_available() ) {
							$this->e_li_check( __( 'Use EXIF thumbnails if available.', 'wp-performance-pack' ) );
						} else {
							$this->e_li_error( __( 'EXIF extension not installed', 'wp-performance-pack' ) );
						}
						if ( $level > 2 ) {
							$this->e_li_check( __( "Don't save intermediate images", 'wp-performance-pack' ) );
							if ( $this->is_object_cache_installed() ) {
								$this->e_li_check( __( 'Use caching', 'wp-performance-pack' ) );
							} else {
								$this->e_li_error( __( 'No persistent object cache installed.', 'wp-performance-pack' ) );
							}
							if ( $this->is_regen_thumbs_available() ) {
								$this->e_li_check( __( 'Regenerate Thumbnails integration', 'wp-performance-pack' ) );
							} else {
								$this->e_li_error( __( 'No Regenerate Thumbnails plugin installed', 'wp-performance-pack' ) );
							}
						}
					}
				} else {
					// custom
					if ( $this->wppp->options['dynamic_images_nosave'] ) {
						$this->e_li_check( __( "Don't save intermediate images", 'wp-performance-pack' ) );
					}
					if ( $this->wppp->options['dynamic_images_cache'] ) {
						$this->e_li_check( __( 'Use caching', 'wp-performance-pack' ) );
					}
					if ( $this->wppp->options['dynamic_images_rthook'] ) {
						$this->e_li_check( __( 'Regenerate Thumbnails integration', 'wp-performance-pack' ) );
						if ( $this->wppp->options['dynamic_images_rthook_force'] ) {
							$this->e_li_check( __( 'Force delte all on Regenerate Thumbnails', 'wp-performance-pack' ) );
						}
					}
					if ( $this->wppp->options['dynamic_images_exif_thumbs'] ) {
						$this->e_li_check( __( 'Use EXIF thumbnails if available.', 'wp-performance-pack' ) );
					}
					$this->e_li_check( sprintf( __( 'Intermediate image quality set to %s%%', 'wp-performance-pack' ), $this->wppp->options['dynimg_quality'] ) );
					if ( $this->wppp->options['dyn_links'] ) {
						$this->e_li_check( __( 'Dynamic image links', 'wp-performance-pack' ) );
					}
				}
			}
		}
		echo '</ul>';
	}

	public function render_options () {
		$option_keys = array_keys( $this->wppp->get_options_default() );
		unset ( $option_keys [ array_search( 'advanced_admin_view', $option_keys ) ] );
		wp_localize_script( 'wppp-admin-script', 'wpppData', array( json_encode( array(
			'dynimg' => array( 'current' => $this->dynimg_detect_current_setting( $this ),
								// sequence: stable, speed, webspace, current, cdn_off, cdn_stable, cdn_speed, current
								'settings' => array(	'dynamic_images' => array(	$this->is_dynamic_images_available(),
																					$this->is_dynamic_images_available(),
																					$this->is_dynamic_images_available(),
																					$this->wppp->options['dynamic_images'] ),
														'dynamic_images_nosave' => array(	false,
																							false,
																							true,
																							$this->wppp->options['dynamic_images_nosave'] ),
														'dynamic_images_cache' => array(	false,
																							false,
																							$this->is_object_cache_installed(),
																							$this->wppp->options['dynamic_images_cache'] ),
														'dynamic_images_rthook' => array(	false,
																							false,
																							$this->is_regen_thumbs_available(),
																							$this->wppp->options['dynamic_images_rthook'] ),
														'dynamic_images_rthook_force' => array(	false,
																								false,
																								false,
																								$this->wppp->options['dynamic_images_rthook'] ),
														'dynamic_images_exif_thumbs' => array(	false,
																								$this->is_exif_available(),
																								$this->is_exif_available(),
																								$this->wppp->options['dynamic_images_exif_thumbs'] ),
														'dynimg_quality' => array(	80,
																					80,
																					80,
																					$this->wppp->options['dynimg_quality'] ),
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
		<input type="hidden" <?php $this->e_opt_name('dynamic_images'); ?> value="<?php echo $this->wppp->options['dynamic_images'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_nosave'); ?> value="<?php echo $this->wppp->options['dynamic_images_nosave'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_cache'); ?> value="<?php echo $this->wppp->options['dynamic_images_cache'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_rthook'); ?> value="<?php echo $this->wppp->options['dynamic_images_rthook'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_rthook_force'); ?> value="<?php echo $this->wppp->options['dynamic_images_rthook_force'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynamic_images_exif_thumbs'); ?> value="<?php echo $this->wppp->options['dynamic_images_exif_thumbs'] ? 'true' : 'false' ?>" />
		<input type="hidden" <?php $this->e_opt_name('dynimg_quality'); ?> value="<?php echo $this->wppp->options['dynimg_quality']; ?>" />

		<h3 class="title"><?php _e( 'Improve image handling', 'wp-performance-pack' );?></h3>
		<?php if ( $this->is_dynamic_images_available() ) : ?>
		<table style="empty-cells:show; width:100%;">
			<tr>
				<td valign="top" style="width:9em;"><div id="dynimg-slider" style="margin-top:1em; margin-bottom:1em;"></div></td>
				<td valign="top" style="padding-left:2em;">
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'All image handling improvements turned off', 'wp-performance-pack' );?></h4>
						<?php $this->dynimg_output_active_settings( 0 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster image upload', 'wp-performance-pack' );?></h4>
						<p class="description"><?php _e( 'Improved upload performance due to dynamically created intermediate images. Once created images are saved to disc and served directly.', 'wp-performance-pack' );?></p>
						<?php $this->dynimg_output_active_settings( 1 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster image upload and thumbnail creation', 'wp-performance-pack' );?></h4>
						<p class="description"><?php _e( 'Dynamically created intermediate images, use of EXIF thumbnails if available. <strong>Thumbnails may differ from actual image depending on EXIF thumbnail.</strong>', 'wp-performance-pack' );?></p>
						<?php $this->dynimg_output_active_settings( 2 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Faster upload / thumbnail creation creation and reduced disc space usage.', 'wp-performance-pack' );?></h4>
						<p class="description"><?php _e( '<strong>Without CDN not recommended for production sites!</strong><br/>Intermediate images are created on demand but are not saved to disc.', 'wp-performance-pack' );?></p>
						<?php $this->dynimg_output_active_settings( 3 ); ?>
					</div>
					<div class="wppp-dynimg-desc" style="display:none;">
						<h4 style="margin-top:0;"><?php _e( 'Custom settings', 'wp-performance-pack' ); ?></h4>
						<p class="description"><?php _e( 'Select your own settings. Customize via advanced view.', 'wp-performance-pack' ); ?></p>
						<?php $this->dynimg_output_active_settings( 4 ); ?>
					</div>
				</td>
				<td valign="top" style="width:30%">
					<div class="wppp-dynimg-hint" style="display:none"></div>
					<div class="wppp-dynimg-hint" style="display:none"></div>
					<div class="wppp-dynimg-hint" style="display:none">
						<?php $this->do_hint_exif( false ); ?>
					</div>
					<div class="wppp-dynimg-hint" style="display:none">
						<?php 
							$this->do_hint_exif( false ); 
							$this->do_hint_caching();
							$this->do_hint_regen_thumbs( false );
						?>
					</div>
					<div class="wppp-dynimg-hint" style="display:none"></div>
				</td>
			</tr>
		</table>
		<?php else : ?>
			<?php $this->do_hint_permalinks( true ); ?>
		<?php endif; ?>
		
		<hr/>
		<?php
	}
}

?>