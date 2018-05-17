<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Help' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Help
 * Help Class to display videos and text on meta and option tabs
 */
class Pngx__Admin__Help {

	//Help Fields array()
	protected $fields = array();

	/**
	 * Return Array of Help Fields
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->fields;
	}

	/**
	 * Array of All Help Fields
	 */
	protected function set_help_fields() {
		/*
		 * Sample Help Section
		 *
		 * Help Sections should aways start with a heading and end with end_list
		 *
		 * Choose either section or tab to place the content
		 */
		$this->fields['header_video_guides_content'] = array(  // unique id
			'section' => '', // options tab to place help content
			'tab'     => 'content', // meta tab to place help content
			'text'    => 'Content', // title for content section on help tab
			'type'    => 'heading' //field type heading only used in opening
		);
		$this->fields['video_creating']       = array( // unique id
			'section'  => '', //option tab to place help
			'tab'      => 'content', //meta tab to place help
			'text'     => 'Overview of Creating', //descriptive text for help
			'video_id' => 'I1v9HxdIsSE', //Youtube Video ID
			'type'     => 'video' //field type video
		);
		$this->fields['link_pro_hide_deal']           = array( // unique id
			'section' => '', //option tab to place help
			'tab'     => 'content', //meta tab to place help
			'text'    => 'Descriptive Text',//descriptive text for help
			'link'    => 'http://cctor.link/Ihoro', //helo link
			'pro'     => 'Pro', //Add Pro superscript for pro only feature
			'type'    => 'links' //field type links
		);
		$this->fields['video_end_list_content']      = array( // unique id
			'section' => '', // options tab close for this content
			'tab'     => 'content', // meta tab close for this content
			'type'    => 'end_list'
		);
	}

	/**
	 * Find string in multidimensional array
	 *
	 * Thanks to jwueller http://stackoverflow.com/a/4128377
	 *
	 * @param            $needle
	 * @param            $haystack
	 * @param bool|false $strict
	 *
	 * @return bool
	 */
	public function in_array_r( $needle, $haystack, $strict = false ) {
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && $this->in_array_r( $needle, $item, $strict ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Display Help Fields in Tab
	 *
	 * @param null $section
	 */
	public function display_help( $section = null, $page_screen_id = null, $class = null ) {

		if ( ! $section ) {
			return;
		}

		if ( 'all' != $section && ! $this->in_array_r( $section, $this->fields ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'all' != $section ) {

			if ( $page_screen_id == $screen->id ) {
				echo '</td></tr><tr valign="top"><td colspan="2">';
			}

			echo '<div class="' . esc_html( $class ) . ' pngx-meta-field-wrap pngx-section-help-container">';

			echo '<button aria-expanded="false" class="pngx-section-help-container-toggle" type="button">
					<span class="dashicons-before dashicons-editor-help">Help</span>
					<span class="dashicons toggle__arrow dashicons-arrow-down"></span>
				</button>';

			echo '<div class="pngx-section-help-slideout">';

			echo '<div class="pngx-meta-field-content video">';
			echo '<h4>' . __( 'Video Guides', 'plugin-engine' ) . '</h4>';
			echo '<ul>';
			foreach ( $this->fields as $help_field ) {

				if ( isset( $help_field['type'] ) && 'video' == $help_field['type'] ) {

					if ( $section == $help_field['tab'] || $section == $help_field['section'] ) {
						$this->help_fields_switch( $help_field, $section );
					}
				}

			}
			echo '</ul></div>';

			echo '<div class="pngx-meta-field-content text">';
			echo '<h4>' . __( 'Guides', 'plugin-engine' ) . '</h4>';
			echo '<ul>';
			foreach ( $this->fields as $help_field ) {

				if ( isset( $help_field['type'] ) && 'links' == $help_field['type'] ) {

					if ( $section == $help_field['tab'] || $section == $help_field['section'] ) {
						$this->help_fields_switch( $help_field, $section );
					}
				}

			}
			echo '</ul></div>';

			echo '</div></div>';

			if ( $page_screen_id == $screen->id ) {
				echo '</td></tr>';
			}

		} else {

			//All Fields Display for Help Tabs
			foreach ( $this->fields as $help_field ) {

				if ( isset( $help_field['type'] ) ) {

					if ( 'all' == $section ) {
						$this->help_fields_switch( $help_field, $section );
					}

				}
			}
		}

	}

	protected function help_fields_switch( $help_field = array(), $section = null ) {

		switch ( $help_field['type'] ) {

			case 'heading':
				?>

				<h4 class="pngx-heading"><?php echo esc_html( $help_field['text'] ); ?></h4>
				<ul>
				<?php break;

			case 'end_list':
				?>
				</ul>
				<?php break;

			case 'video':
				$rel = '';
				if ( 'all' == $section ) {
					$rel = 'how_to_videos';
				}
				$pro = '';
				if ( isset( $help_field['pro'] ) ) {
					$pro = '<sup class="promote-sup">' . esc_html( $help_field['pro'] ) . '</sup>';
				}
				?>
				<li><a class="pngx-support youtube_colorbox"
				       href="https://www.youtube.com/embed/<?php echo esc_html( $help_field['video_id'] ); ?>?hd=1&autohide=1&rel=0&showsearch=0&autoplay=1"
				       rel="<?php echo esc_attr( $rel ); ?>"><?php echo esc_html( $help_field['text'] ); ?></a><?php echo $pro; ?>
				</li>

				<?php break;

			case 'links':
				$pro = '';
				if ( isset( $help_field['pro'] ) ) {
					$pro = '<sup class="promote-sup">' . esc_html( $help_field['pro'] ) . '</sup>';
				}
				?>
				<li><a class="pngx-support" target="_blank"
				       href="<?php echo esc_url( $help_field['link'] ); ?>"><?php echo esc_html( $help_field['text'] ); ?></a><?php echo $pro; ?>
				</li>
				<?php break;

		}

	}
}