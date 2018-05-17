<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
if ( class_exists( 'Pngx__Admin__Meta' ) ) {
	return;
}


/**
 * Class Pngx__Admin__Meta
 * Create, Save, Display Custom Fields for a CPT
 *
 * @property array  post_type
 * @property string user_capability
 * @property array  tabs
 * @property array  fields
 */
class Pngx__Admin__Meta {

	protected static $instance;

	//tabs key and label
	protected static $tabs = array();

	//fields
	protected static $fields = array();

	//post type
	protected $post_type = array( 'pngx' );

	//user capability
	protected $user_capability = 'edit_post';

	/*
	* Construct
	*/
	public function __construct() {

		//Save Meta
		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );

		//JS Error Check
		add_action( 'pngx_meta_message', array( __CLASS__, 'get_js_error_check_msg' ) );
	}

	/**
	 * Javascript Conflict Message
	 *
	 * @return string
	 */
	public static function get_js_error_check_msg() {

		$js_troubleshoot_url = 'http://cctor.link/R7KRa';

		$js_msg = '<div class="javascript-conflict pngx-error"><p>' . sprintf( __( 'There maybe a javascript conflict preventing some features from working.  <a href="%s" target="_blank" >Please check this guide to narrow down the cause.</a>', 'plugin-engine' ), esc_url( $js_troubleshoot_url ) ) . '</p></div>';

		return $js_msg;

	}

	/*
	* Set Current Screen Variables
	*/
	public static function get_screen_variables() {

		global $pagenow, $typenow;
		$current_screen['pagenow'] = $pagenow;
		$current_screen['post']    = isset( $_GET['post'] ) ? $_GET['post'] : '';
		$current_screen['type']    = $typenow;

		if ( empty( $current_screen['type'] ) && ! empty( $current_screen['type'] ) ) {
			$current_screen['post_obj'] = get_post( $_GET['post'] );
			$current_screen['type']     = $current_screen['post_obj']->post_type;
		}

		return $current_screen;

	}

	/*
	* Get Post Type
	*/
	public static function get_post_types() {

		return self::instance()->post_type;

	}

	/*
	* Get User Capability
	*/
	public static function get_user_capability() {

		return self::instance()->user_capability;

	}

	/*
	* Set Tabs
	*/
	protected function set_tabs() {

		//CPT Fields Tabs
		$tabs['content'] = __( 'Content', 'plugin-engine' ); //set key and tab title

		$this->tabs = $tabs;

	}

	/*
	* Get Tabs
	*/
	public static function get_tabs() {

		return self::$tabs;

	}

	/*
	* Load Meta Box Functions
	*/
	public function set_fields() {

		$this->fields = Pngx__Meta__Fields::get_fields();
	}

	/*
	* Get Fields
	*/
	public static function get_fields() {

		return self::$fields;

	}

	/**
	 * Show Fields
	 *
	 * @param $post
	 * @param $metabox
	 */
	public static function display_fields( $post, $metabox ) {

		global $wp_version;

		wp_nonce_field( 'pngx_save_fields', 'pngx_nonce' );

		//Create Array of Tabs and Localize to Meta Script
		$tabs_array = array();

		foreach ( self::get_tabs() as $tab_slug => $tab ) {
			$tabs_array[ $tab ] = $tab_slug;
		}

		//Detect if we saved or tried to save to set the current tab.
		global $message;

		$tab_data = array(
			'tabs'           => $tabs_array,
			'update_message' => $message,
			'id'             => isset( $_GET['post'] ) ? absint( $_GET['post'] ) : '',
			'wp_version'     => $wp_version,
		);

		ob_start(); ?>

		<div class="main pngx-tabs" <?php echo Pngx__Admin__Fields::toggle( $tab_data, null ); ?> >

			<ul class="main pngx-tabs-nav">

				<?php //Create Tabs
				foreach ( self::get_tabs() as $tab_slug => $tab ) {
					echo '<li><a href="#' . esc_attr( $tab_slug ) . '">' . esc_attr( $tab ) . '</a></li>';
				}
				?>
			</ul>

			<?php foreach ( self::get_tabs() as $tab_slug => $tab ) {

				//set variable for template area
				$template_area = '';
				?>

				<div class="pngx-section-fields form-table">

					<h2 class="pngx-tab-heading-<?php echo esc_attr( $tab_slug ); ?>"><?php echo esc_attr( $tab ); ?></h2>

					<?php

					/**
					 * Hook to connect help section into a tab
					 *
					 * @parm $tab_slug string of current slug
					 */
					do_action( 'pngx_per_tab_help', $tab_slug );

					$fields = self::get_fields();

					foreach ( $fields as $field ) {

						if ( isset( $field['section'] ) && $field['type'] && $field['section'] === $metabox['id'] && ( isset( $field['tab'] ) && $tab_slug === $field['tab'] ) ) {

							// get value of this field if it exists for this post
							$meta = get_post_meta( $post->ID, $field['id'], true );

							//Wrap Class for Conditionals
							$wrapclass = isset( $field['wrapclass'] ) ? $field['wrapclass'] : '';

							//Template Wrap for AJAX
							if ( "template_start" === $field['type'] ) {
								//Start Template Section Wrap and set value for templates
								$template_select = get_post_meta( $post->ID, $wrapclass, true );
								/**
								 * Filter Default Template Name
								 */
								$default       = apply_filters( 'pngx-default-template', 'default' );
								$template_area = ! empty( $template_select ) ? $template_select : $default;
								?>
								<div class="pngx-meta-template-wrap template-wrap-<?php echo esc_html( $wrapclass ); ?>" >
								<?php
								continue;

							} elseif ( "template_end" === $field['type'] ) {
								//End Template Section Wrap
								$template_area = '';
								?>
								</div>
								<?php
								continue;
							}

							//if in template area only get fields with the template value
							if ( $template_area ) {
								$field_template = isset( $field['template'] ) ? $field['template'] : array();
								if ( ! in_array( $template_area, $field_template ) ) {
									continue;
								}

							} elseif ( ! $template_area && isset( $field['template'] ) ) {
								//if not template area set, but there is a template then do not display the field
								continue;
							}

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
								Pngx__Admin__Style__Linked::display_styles( $fields, $field, $post->ID );
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
									Pngx__Admin__Style__Linked::display_styles( $fields, $field, $post->ID );

									?>

								</div>
								<!-- end .pngx-meta-field.field-<?php echo esc_attr( $field['type'] ); ?>.field-<?php echo esc_attr( $field['id'] ); ?> -->

							</div> <!-- end .pngx-meta-field-wrap.field-wrap-<?php echo esc_attr( $field['type'] ); ?>.field-wrap-<?php echo esc_attr( $field['id'] ); ?>	-->

							<?php
						}//end if in section check

					} // end foreach fields?>

				</div>    <!-- end .pngx-section-fields.form-table -->

			<?php } // end foreach tabs
			?>

		</div>    <!-- end .pngx-tabs -->

		<?php echo ob_get_clean();
	}

	/*
	* Save Meta Fields
	*/
	public static function save_meta( $post_id, $post ) {

		//Autosave or no past variable then kick out
		if ( empty( $_POST ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}

		//Check if on the right post type
		if ( isset( $post->post_type ) && ! in_array( $post->post_type, self::get_post_types() ) ) {
			return;
		}

		//Check if the user can make edits
		if ( ! current_user_can( self::get_user_capability(), $post->ID ) ) {
			return;
		}

		//Verify Nonce
		if ( isset( $_POST['pngx_nonce'] ) && ! wp_verify_nonce( $_POST['pngx_nonce'], 'pngx_save_fields' ) && ( isset( $_POST['_inline_edit'] ) && ! wp_verify_nonce( $_POST['_inline_edit'], 'inlineeditnonce' ) ) ) {
			return;
		}

		/**
		 * Before Save Meta Fields
		 *
		 * @param array $_POST
		 *
		 */
		do_action( 'pngx_before_save_meta_fields', $_POST );

		//Save Date for each file
		foreach ( self::get_fields() as $option ) {

			/**
			 * Save Meta Fields
			 *
			 *
			 * @param int   $post_id
			 * @param array $option
			 *
			 */
			do_action( 'pngx_save_meta_fields', $post_id, $option );

			//handle check box saving
			if ( $option['type'] == 'checkbox' ) {

				$checkbox = get_post_meta( $post_id, $option['id'], true );

				if ( $checkbox && ! isset( $_POST[ $option['id'] ] ) ) {
					delete_post_meta( $post_id, $option['id'] );
				}

			}

			// Final Check if value should be saved then sanitize and save
			if ( isset( $_POST[ $option['id'] ] ) ) {
				//Send Input to Sanitize Class, will return sanitized input or no input if no sanitization method
				$sanitized = new Pngx__Sanitize( $option['type'], $_POST[ $option['id'] ], $option );

				$old = get_post_meta( $post_id, $option['id'], true );

				$new = $_POST[ $option['id'] ];

				if ( ! is_null( $new ) && $new != $old ) {
					update_post_meta( $post_id, $option['id'], $sanitized->result );
				} elseif ( '' == $new && $old ) {
					delete_post_meta( $post_id, $option['id'], $old );
				}

			}
		}

		/**
		 * After Save Meta Fields
		 *
		 * @param array $_POST
		 *
		 */
		do_action( 'pngx_after_save_meta_fields', $_POST );

	}

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Pngx__Admin__Meta
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}
}