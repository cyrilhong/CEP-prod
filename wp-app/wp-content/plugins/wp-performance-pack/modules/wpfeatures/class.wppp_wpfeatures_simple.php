<?php

class WPPP_WPFeatures_Simple extends WPPP_Admin_Renderer {
	public function add_help_tab () {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'	=> 'wppp_simple_features',
			'title'	=>	__( 'Overview', 'wp-performance-pack' ),
			'content'	=> '<p>' . __( 'Changing or disabling WordPress features is only available in advanced view.', 'wp-performance-pack' ) . '</p>',
		) );
	}

	public function render_options () {
	?>
		<h3 class="title"><?php _e( 'WordPress features', 'wp-performance-pack' );?></h3>

		<p><div class="ui-state-highlight ui-corner-all" style="padding:.5em"><span class="ui-icon ui-icon-info" style="float:left; margin-right:.3em;"></span>
			<?php _e( 'Changing or disabling WordPress features is only available in advanced view.', 'wp-performance-pack' ); ?>
		</div></p>

		<hr/>
	<?php
	}
}

?>