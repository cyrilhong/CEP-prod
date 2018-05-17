<?php

class WPPP_Dynamic_Images_Advanced extends WPPP_Admin_Renderer {
	public function enqueue_scripts_and_styles () {
		parent::enqueue_scripts_and_styles();
	}

	public function add_help_tab () {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_dynimg',
			'title'	=> __( 'Overview', 'wp-performance-pack' ),
			'content'	=> '<p>' . __( "When using dynamic image resizing images don't get resized on upload. Instead resizing is done when an intermediate image size is first requested. This can significantly improve upload speed. Once created, the image can get saved and is then subsequently served directly.", 'wp-performance-pack' ) . '</p>'
							. '<p>' . __ ( "Not saving intermediate images is only recommended for testing environments or when using caching or CDN for both front and back end.", 'wp-performance-pack' ) . '</p>'
		) );
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_exif',
			'title'	=> __( 'EXIF thumbs', 'wp-performance-pack' ),
			'content'	=> '<p>' . __( "Usage of EXIF thumbs for thumbnail creation improves peformance when creating small thumbs. If the intermediate image size is smaller than the size of the EXIF thumbnail, the EXIF image will be used for resizing instead of the full image, which is way faster and less memory intense. But be aware that EXIF thumbs might differ from the actual image, depending on the editing software used to create the image.", 'wp-performance-pack' ) . '</p>'
		) );
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_servemethod',
			'title'	=> __( 'Serve method', 'wp-performance-pack' ),
			'content'	=> '<p>' . __( "Dynamic images can be served using one of two methods which differ in how much of WordPress will be loaded. <em>SHORTINIT</em> will only load the WordPress core, no themes or plugins and is the faster of both options. <em>WP_USE_THEMES</em> will load core and plugins, but not the theme files. That way other image processing plugins will be loaded and used when images get resized (if they are based on WP_Image_Editor).", 'wp-performance-pack' ) . '</p>'
		) );
		$screen->add_help_tab( array(
			'id'	=> 'wppp_advanced_regenthumbs',
			'title'	=> __( 'Regenerate Thumbnails integration', 'wp-performance-pack' ),
			'content'	=> '<p>' . __( "Using this feature you can clean up old and unused intermediate images. WPPP will hook into one of the supported plugins and instead of recreating intermediate images they will get deleted.", 'wp-performance-pack' ) . '</p>'
		) );	}

	public function render_options () {
		wp_localize_script( 'wppp-admin-script', 'wpppData', array (
			'dynimg-quality' => $this->wppp->options['dynimg_quality'],
		));
		?>
		<input type="hidden" <?php $this->e_opt_name('dynimg_quality'); ?> value="<?php echo $this->wppp->options['dynimg_quality']; ?>" />

		<h3 class="title"><?php _e( 'Improve image handling', 'wp-performance-pack' ); ?></h3>
		<table class="form-table" style="clear:none">
			<tr valign="top">
				<th scope="row"><?php _e( 'Dynamic image resizing', 'wp-performance-pack' ); ?></th>
				<td>
					<?php $this->e_switchButton( 'dynamic_images', !$this->is_dynamic_images_available() ); ?>
					<p class="description"><?php _e( "Create intermediate images on demand, not on upload. If you deactive this option after some time of usage you might have to recreate thumbnails using a plugin like Regenerate Thumbnails.", 'wp-performance-pack' ); ?></p>
					<?php $this->do_hint_permalinks( true ); ?>
					<br/>
					<p><?php _e( 'Serve image method:', 'wp-performance-pack' ); ?>
						<select <?php $this->e_opt_name( 'dynimg_serve_method' ); ?> value="short_init">
							<option value="short_init" <?php echo ( $this->wppp->options[ 'dynimg_serve_method' ] === 'short_init' ) ? 'selected="selected"' : ''; ?>>Fast</option>
							<option value="use_themes" <?php echo ( $this->wppp->options[ 'dynimg_serve_method' ] === 'use_themes' ) ? 'selected="selected"' : ''; ?>>Compatible</option>
						</select>
					</p>
					<p class="description"><?php _e( 'Use <em>Fast</em> for faster image serving. No plugins will be loaded when serving images using this method. Use <em>Compatible</em> to make dynamic images compatible with other installed (image) plugins.', 'wp-performance-pack' ); ?></p>
					<br/>
					<?php $this->e_checkbox( 'dynamic_images_nosave', 'dynamic_images_nosave', __( "Don't save images to disc", 'wp-performance-pack' ), !$this->is_dynamic_images_available() ); ?></th>
					<p class="description"><?php _e( 'Dynamically recreate intermediate images on each request.', 'wp-performance-pack' ); ?></p>
					<br/>
					<?php $this->e_checkbox( 'dynimg-cache', 'dynamic_images_cache', __( 'Use caching', 'wp-performance-pack' ), !$this->is_dynamic_images_available() ); ?>
					<p class="description"><?php printf( __( "Cache intermediate images using Use WordPress' Object Cache API. Only applied if %s is activated.", 'wp-performance-pack' ), '"' . __( "Don't save intermediate images", 'wp-performance-pack' ) . '"' ) ; ?></p>
					<?php $this->do_hint_caching(); ?>
					<br/>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Use EXIF thumbnails', 'wp-performance-pack' ); ?></th>
				<td>
					<?php $this->e_switchButton( 'dynamic_images_exif_thumbs', !$this->is_exif_available() ); ?>
					<p class="description"><?php _e( 'If available use EXIF thumbnail to create intermediate images smaller than the specified size. <strong>Note that, depending on image editing software, the EXIF thumbnail might differ from the actual image!</strong>', 'wp-performance-pack'); ?></p>
					<br/>
					<p><?php _e( 'Use EXIF thumbs for image sizes up to the following dimensions:' ,'wp-performance-pack' ); ?></p>
					<p>Width: <input type="text" <?php $this->e_opt_name( 'exif_width' ); ?> value="<?php echo $this->wppp->options['exif_width']; ?>" size="8"> Height: <input type="text" <?php $this->e_opt_name( 'exif_height' ); ?> value="<?php echo $this->wppp->options['exif_height']; ?>" size="8"></p>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Image quality', 'wp-performance-pack' );?></th>
				<td>
					<div id="dynimg-quality-slider" style="width:25em; margin-bottom:2em;"></div>
					<p class="description"><?php _e( 'Quality setting for newly created intermediate images.', 'wp-performance-pack' );?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Regenerate Thumbnails integration', 'wp-performance-pack' );?></th>
				<td>
					<?php $this->e_switchButton( 'dynamic_images_rthook', !$this->is_regen_thumbs_available() || !$this->is_dynamic_images_available() ); ?>
					<p class="description"><?php _e( 'Integrate into thumbnail regeneration plugins to delete existing intermediate images.', 'wp-performance-pack' ); ?></p>
					<?php $this->do_hint_regen_thumbs( false ); ?>
					<br/>
					<?php $this->e_checkbox( 'dynimg-rtforce', 'dynamic_images_rthook_force', __( 'Force delete of all potential thumbnails', 'wp-performance-pack' ), !$this->is_regen_thumbs_available() || !$this->is_dynamic_images_available() ); ?>
					<p class="description"><?php _e( 'Delete all potential intermediate images (i.e. those matching the pattern "<em>imagefilename-*x*.ext</em>") while regenerating. <strong>Use with care as this option might delete files that are no thumbnails!</strong>', 'wp-performance-pack' );?></p>
				</td>
			</tr>
		</table>

		<hr/>
		<?php
	}
}

?>