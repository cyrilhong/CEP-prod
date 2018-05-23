<?php
/**
 * WooCommerce PDF Product Vouchers
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce PDF Product Vouchers to newer
 * versions in the future. If you wish to customize WooCommerce PDF Product Vouchers for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/ for more information.
 *
 * @package   WC-PDF-Product-Vouchers/Customizer
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;


/**
 * PDF Product Vouchers Customizer
 *
 * This class handles all the customizations for WP Customizer
 * for customizing the voucher templates. That's a lot of customization in
 * one sentence.
 *
 * @since 3.0.0
 */
class WC_PDF_Product_Vouchers_Customizer {


	/** @var array voucher image settings that should be filtered */
	private $preview_filter_settings = array();


	/**
	 * Initializes the voucher templates customizer
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_filter( 'pre_option_blogname', array( $this, 'adjust_customizer_title' ) );

		add_action( 'init', array( $this, 'init' ) );

		add_action( 'customize_preview_wc_voucher_template_voucher_primary_image',    array( $this, 'add_preview_filter' ) );
		add_action( 'customize_preview_wc_voucher_template_voucher_additional_image', array( $this, 'add_preview_filter' ) );
	}


	/**
	 * Initializes the Customizer class
	 *
	 * Hooked to `init` because `is_customize_preview()` is not available at plugin
	 * construction time (`plugins_loaded`).
	 *
	 * @since 3.0.0
	 */
	public function init() {

		// register PDF Voucher Templates customizer settings (always)
		add_filter( 'customize_register', array( $this, 'add_customizer_settings' ) );

		if ( is_customize_preview() ) {
			add_filter( 'get_post_status', array( $this, 'voucher_template_post_status' ), 10, 2 );
		}

		// load PDF Voucher Templates customizer content when invoked
		if ( $this->is_voucher_template_customizer() ) {

			// first remove stuff we don't need on our views...
			add_filter( 'customize_register', array( $this, 'remove_sections' ), 150 );

			// ...then add our stuff
			add_filter( 'customize_register', array( $this, 'add_customizer_sections' ), 200 );
			add_filter( 'customize_register', array( $this, 'add_customizer_controls' ), 200 );

			// enqueue Customizer scripts and styles
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'customizer_controls_scripts' ), 999 );
			add_action( 'customize_preview_init',             array( $this, 'customizer_preview_scripts' ), 999 );
			add_action( 'customize_controls_print_styles',    array( $this, 'customizer_styles' ), 999 );
		}
	}


	/**
	 * Filter voucher template post status in customizer preview
	 *
	 * Set post status from auto-draft to draft so that the auto-draft
	 * can be previewed.
	 *
	 * @since 3.0.0
	 * @param string $status post status
	 * @param \WP_Post $post the post object
	 * @return string filtered post status
	 */
	public function voucher_template_post_status( $status, $post ) {

		// allow previewing voucher template auto-drafts by faking the status to be draft
		if ( 'auto-draft' === $status && 'wc_voucher_template' === $post->post_type ) {
			$status = 'draft';
		}

		return $status;
	}


	/**
	 * Adds Customizer settings.
	 *
	 * @since 3.0.0
	 * @param \WP_Customize_Manager $wp_customize customizer instance
	 */
	public function add_customizer_settings( $wp_customize ) {
		global $wp_customize;

		// include custom settings
		require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/customizer/class-wc-pdf-product-vouchers-voucher-template-setting.php' );

		// =======
		// General
		// =======

		// Note: when changing the defaults here, adjust them in
		// WC_PDF_Product_Vouchers_Admin_Voucher_Templates::redirect_voucher_template_to_customizer() as well

		// template name (post_title)
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_post_title', array( 'validate_callback' => array( $this, 'validate_voucher_template_post_title' ) ) ) );

		// voucher type (either single- or multi-purpose)
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_type', array(
			'default' => 'single',
		) ) );

		// default font family
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_font_family', array(
			'default' => 'Helvetica',
		) ) );

		// default font size
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_font_size', array(
			'default' => 16,
		) ) );

		// default font weight
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_font_style_b' ) );

		// default font style (italics)
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_font_style_i' ) );

		// default text alignment
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_text_align', array(
			'default' => 'left',
		) ) );

		// default font color
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_font_color', array(
			'default' => '#000000',
		) ) );


		// ==========
		// Background
		// ==========

		// primary voucher image
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_primary_image' ) );

		// all voucher images
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_images' ) );

		// voucher image dpi
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_image_dpi', array(
			'default' => 300,
		) ) );

		// additional voucher image
		$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, 'wc_voucher_template_voucher_additional_image' ) );

		// ==============
		// Voucher Fields
		// ==============

		// build settings based on the voucher fields
		$voucher_fields = WC_Voucher_Template::get_voucher_fields();

		if ( ! empty( $voucher_fields ) ) {
			foreach ( $voucher_fields as $field_id => $attrs ) {

				$data_type = ! empty( $attrs['data_type'] ) ? $attrs['data_type'] : 'property';
				$settings  = WC_Voucher_Template::get_voucher_field_settings( $field_id, $data_type );

				if ( ! empty( $settings ) ) {

					$section_id = 'wc_voucher_template_' . $field_id;

					foreach ( $settings as $setting_key ) {

						$args = array();

						if ( 'font_size' === $setting_key ) {
							$args['default'] = 0;
						}

						$wp_customize->add_setting( new WC_PDF_Product_Vouchers_Voucher_Template_Setting( $wp_customize, $section_id . '_' . $setting_key, $args ) );
					}
				}
			}
		}
	}


	/**
	 * Removes unneeded Customizer sections
	 *
	 * @since 3.0.0
	 * @param \WP_Customize_Manager $wp_customize customizer instance
	 */
	public function remove_sections( $wp_customize ) {
		global $wp_customize;

		// remove all sections
		if ( $wp_customize instanceof WP_Customize_Manager && $sections = $wp_customize->sections() ) {

			foreach ( array_keys( $wp_customize->sections() ) as $section_id ) {
				$wp_customize->remove_section( $section_id );
			}
		}
	}


	/**
	 * Adds Customizer sections
	 *
	 * @since 3.0.0
	 * @param \WP_Customize_Manager $wp_customize customizer instance
	 */
	public function add_customizer_sections( $wp_customize ) {
		global $wp_customize;

		$wp_customize->add_section( 'wc_voucher_template_general', array(
			'title' => __( 'General', 'woocommerce-pdf-product-vouchers' ),
		) );

		$wp_customize->add_section( 'wc_voucher_template_voucher_images', array(
			'title' => __( 'Images', 'woocommerce-pdf-product-vouchers' ),
		) );

		// ==============
		// Voucher Fields
		// ==============

		// build sections based on the voucher fields
		$voucher_fields = WC_Voucher_Template::get_voucher_fields();

		if ( ! empty( $voucher_fields ) )	{
			foreach ( $voucher_fields as $field_id => $attrs ) {

				$wp_customize->add_section( 'wc_voucher_template_' . $field_id, array(
					'title' => $attrs['label'],
				) );
			}
		}
	}


	/**
	 * Adds Customizer controls
	 *
	 * @since 3.0.0
	 * @param \WP_Customize_Manager $wp_customize customizer instance
	 */
	public function add_customizer_controls( $wp_customize ) {
		global $wp_customize;

		// include custom controls
		require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/customizer/class-wc-pdf-product-vouchers-customize-font-style-control.php' );
		require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/customizer/class-wc-pdf-product-vouchers-customize-position-control.php' );
		require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/customizer/class-wc-pdf-product-vouchers-customize-voucher-image-control.php' );

		$wp_customize->register_control_type( 'WC_PDF_Product_Vouchers_Customize_Position_Control' );

		// ===================
		// General
		// ===================

		// template name (post_title)
		$wp_customize->add_control( 'wc_voucher_template_post_title', array(
			'type'        => 'text',
			'label'       => __( 'Template Name', 'woocommerce-pdf-product-vouchers' ),
			'section'     => 'wc_voucher_template_general',
			'input_attrs' => array(
				'placeholder' => __( 'Voucher Template Name', 'woocommerce-pdf-product-vouchers' ),
			),
		) );

		// voucher type
		$wp_customize->add_control( 'wc_voucher_template_voucher_type', array(
			'type'    => 'select',
			'label'   => __( 'Voucher type', 'woocommerce-pdf-product-vouchers' ),
			'section' => 'wc_voucher_template_general',
			'choices' => array(
				'single' => __( 'Single purpose (for a particular product / service)', 'woocommerce-pdf-product-vouchers' ),
				'multi'  => __( 'Multi-purpose (can be redeemed on any product / service)', 'woocommerce-pdf-product-vouchers' ),
			),
		) );

		$default_controls = WC_Voucher_Template::get_voucher_field_default_controls();

		// remove default option
		unset( $default_controls['font_family']['choices'][''] );

		// minimum voucher font size is 1px
		$default_controls['font_size']['input_attrs']['min'] = 1;

		$this->build_controls( $default_controls, array(
			'section_id'  => 'wc_voucher_template_general',
			'option_base' => 'wc_voucher_template_voucher'
		) );

		// ===================
		// Images
		// ===================

		$wp_customize->add_control( new WC_PDF_Product_Vouchers_Customize_Voucher_Image_Control( $wp_customize, 'wc_pdf_product_vouchers_voucher_image', array(
			'label'         => __( 'Voucher Image', 'woocommerce-pdf-product-vouchers' ),
			'section'       => 'wc_voucher_template_voucher_images',
			'settings' => array(
				'primary' => 'wc_voucher_template_voucher_primary_image',
				'uploads' => 'wc_voucher_template_voucher_images'
			),
			'removed'  => 'remove-image',
		) ) );

		$wp_customize->add_control( 'wc_pdf_product_vouchers_voucher_image_dpi', array(
			'type'        => 'number',
			'label'       => __( 'Image DPI', 'woocommerce-pdf-product-vouchers' ),
			'description' => __( 'Enter the DPI or resolution at which the voucher background was created. This affects the resolution at which voucher fields are added, so this value should match the image file.', 'woocommerce-pdf-product-vouchers' ),
			'section'     => 'wc_voucher_template_voucher_images',
			'settings'    => 'wc_voucher_template_voucher_image_dpi',
		) );

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'wc_voucher_template_voucher_additional_image', array(
			'label'         => __( 'Additional Image', 'woocommerce-pdf-product-vouchers' ),
			'section'       => 'wc_voucher_template_voucher_images',
			'description'   => __( 'Optional image with the same dimensions as the primary voucher image, that will be added as a second page to the voucher. Useful for terms or redemption instructions.', 'woocommerce-pdf-product-vouchers' ),
			'button_labels' => array(
				'select'       => __( 'Select image', 'woocommerce-pdf-product-vouchers' ),
				'change'       => __( 'Change image', 'woocommerce-pdf-product-vouchers' ),
				'remove'       => __( 'Remove', 'woocommerce-pdf-product-vouchers' ),
				'default'      => __( 'Default', 'woocommerce-pdf-product-vouchers' ),
				'placeholder'  => __( 'No image selected', 'woocommerce-pdf-product-vouchers' ),
				'frame_title'  => __( 'Select additional voucher image', 'woocommerce-pdf-product-vouchers' ),
				'frame_button' => __( 'Choose image', 'woocommerce-pdf-product-vouchers' ),
			),
		) ) );

		// ==============
		// Voucher Fields
		// ==============

		// build controls based on the voucher fields
		$voucher_fields = WC_Voucher_Template::get_voucher_fields();

		if ( ! empty( $voucher_fields ) )	{
			foreach ( $voucher_fields as $field_id => $attrs ) {

				$controls = WC_Voucher_Template::get_voucher_field_controls( $field_id, $attrs );

				$this->build_controls( $controls, array( 'field_id' => $field_id ) );
			}
		}
	}


	/**
	 * Builds controls for the template customizer screen
	 *
	 * @since 3.0.0
	 * @param array $controls an array of controls to add for a voucher field
	 * @param array $args {
	 *     an array of arguments
	 *
	 *     @type string $field_id voucher field id
	 *     @type string $section_id (optional) section ID for the controls, defaults to `wc_voucher_template_{$field_id}`
	 *     @type string $option_base (optional) option base used to construct the control ID, defaults to the section ID.
	 * }
	 */
	private function build_controls( $controls, $args ) {
		global $wp_customize;

		if ( empty( $controls ) ) {
			return;
		}

		// determine control section id
		if ( ! empty( $args['field_id'] ) && empty( $args['section_id'] ) ) {
			$args['section_id'] = 'wc_voucher_template_' . $args['field_id'];
		}

		// default option_base to section_id
		if ( empty( $args['option_base'] ) ) {
			$args['option_base'] = $args['section_id'];
		}

		if ( ! empty( $controls ) ) {

			foreach ( $controls as $control_key => $attrs ) {

				$attrs['section']          = $args['section_id'];
				$attrs['voucher_field_id'] = isset( $args['field_id'] ) ? $args['field_id'] : null;

				$control_id = $args['option_base'] . '_' . $control_key;

				// set up control setting bindings
				if ( ! empty( $attrs['settings'] ) ) {

					if ( is_string( $attrs['settings'] ) ) {

						$attrs['settings'] = $args['option_base'] . '_' . $attrs['settings'];

					} elseif ( is_array( $attrs['settings'] ) ) {

						foreach ( $attrs['settings'] as $k => $setting_key ) {
							$attrs['settings'][ $k ] = $args['option_base'] . '_' . $setting_key;
						}
					}
				}

				$type = isset( $attrs['type'] ) ? $attrs['type'] : null;

				// create the control
				switch ( $type ) {
					case 'wc_pdf_product_vouchers_font_style':
						$wp_customize->add_control( new WC_PDF_Product_Vouchers_Customize_Font_Style_Control( $wp_customize, $control_id, $attrs ) );
					break;

					case 'wc_pdf_product_vouchers_position':
						$wp_customize->add_control( new WC_PDF_Product_Vouchers_Customize_Position_Control( $wp_customize, $control_id, $attrs ) );
					break;

					case 'image':
						$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $control_id, $attrs ) );
					break;

					default:
						$wp_customize->add_control( $control_id, $attrs );
					break;
				}
			}
		}
	}


	/**
	 * Returns available font family options
	 *
	 * @since 3.0.0
	 * @param bool $include_default (optional) whether to include default option or not, defaults to true
	 * @return array
	 */
	private function get_font_family_options( $include_default = true ) {

		/**
		 * Allow actors to add additional font family options for voucher templates
		 *
		 * @since 3.0.0
		 * @param array $fonts associative array of font options
		 */
		$options = apply_filters( 'wc_pdf_product_vouchers_font_family_options', array(
			'Helvetica' => 'Helvetica',
			'Courier'   => 'Courier',
			'Times'     => 'Times',
		) );

		// include default as the first option
		if ( $include_default ) {
			$options = array( '' => __( '(Default)', 'woocommerce-pdf-product-vouchers' ) ) + $options;
		}

		return $options;
	}


	/**
	 * Adds customizer scripts for customizing controls
	 *
	 * @since 3.0.0
	 */
	public function customizer_controls_scripts() {

		// Prevent script caching in development environments
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? WC_PDF_Product_Vouchers::VERSION . '.' . time() : WC_PDF_Product_Vouchers::VERSION;

		wp_enqueue_script( 'woocommerce-pdf-product-vouchers-customizer-control-scripts', wc_pdf_product_vouchers()->get_plugin_url() . '/assets/js/admin/wc-pdf-product-vouchers-customizer-controls.min.js', array( 'jquery', 'wp-color-picker' ), $version, true );

		wp_localize_script( 'woocommerce-pdf-product-vouchers-customizer-control-scripts', 'woocommerce_vouchers_admin_customizer', array(
			'i18n' => array(
				'default'                => __( '(Default)', 'woocommerce-pdf-product-vouchers' ),
				'untitled_template'      => __( '(Untitled Voucher Template)', 'woocommerce-pdf-product-vouchers' ),
				'customizer_description' => __( 'The Customizer allows you to adjust your PDF voucher template with a live preview. No changes are permanent until you click Save.', 'woocommerce-pdf-product-vouchers' ),
			),
			'wc_voucher_template_id' => $this->get_currently_previewed_voucher_template_id(),
		) );

		// ensure core styles/scripts are loaded
		wp_enqueue_style( 'woocommerce-pdf-product-vouchers-customizer-control-styles', wc_pdf_product_vouchers()->get_plugin_url() . '/assets/css/admin/wc-pdf-product-vouchers-customizer-controls.min.css', array( 'wp-color-picker' ), $version );
	}


	/**
	 * Adds customizer scripts for previewing changes
	 *
	 * @since 3.0.0
	 */
	public function customizer_preview_scripts() {
		global $post;

		// Prevent script caching in development environments
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? WC_PDF_Product_Vouchers::VERSION . '.' . time() : WC_PDF_Product_Vouchers::VERSION;

		wp_enqueue_script( 'woocommerce-pdf-product-vouchers-customizer-preview-scripts',  wc_pdf_product_vouchers()->get_plugin_url() . '/assets/js/frontend/wc-pdf-product-vouchers-customizer-preview.min.js', array( 'jquery', 'customize-preview', 'imgareaselect' ), $version, true );
		wp_enqueue_style( 'woocommerce-pdf-product-vouchers-customizer-preview-styles',  wc_pdf_product_vouchers()->get_plugin_url() . '/assets/css/frontend/wc-pdf-product-vouchers-customizer-preview.min.css', array( 'imgareaselect' ), $version );

		// it's too early to get the current post, so we defer the localization to template_redirect,
		// which is the earliest that $post is available
		add_action( 'template_redirect', array( $this, 'localize_customizer_preview_scripts' ) );
	}


	/**
	 * Localizes customizer preview scripts
	 *
	 * @since 3.0.0
	 */
	public function localize_customizer_preview_scripts() {

		// get the primary image dimensions (if any) which are needed for the page script
		$attachment = null;
		$image_ids = get_post_meta( get_the_ID(), '_image_ids', true );

		if ( is_array( $image_ids ) && ! empty( $image_ids[0] ) ) {
			$attachment = wp_get_attachment_metadata( $image_ids[0] );
		}

		wp_localize_script( 'woocommerce-pdf-product-vouchers-customizer-preview-scripts', 'wc_voucher_template_preview', array(
			'voucher_fields' => array_keys( WC_Voucher_Template::get_voucher_fields() ),
			'css_config'     => WC_Voucher_Template::get_voucher_field_settings_css_config(),
		) );
	}


	/**
	 * Adds Customizer styles
	 *
	 * @since 3.0.0
	 */
	public function customizer_styles() {
		?>
		<style type="text/css">
			#accordion-panel-widgets,
			#accordion-panel-nav_menus {
				display: none !important;
				height: 0 !important;
				visibility: hidden !important;
			}
			span.wc-pdf-product-vouchers-range-index {
				color: #999999;
				display: block;
				font-style: italic;
				margin-top: 4px;
			}
		</style>
		<?php
	}


	/**
	 * Adjusts the `blogname` option for customizer
	 *
	 * Basically, we are faking the site title to be something else, namely the
	 * title of the voucher template we are currently customizing.
	 * Unfortunately, there seems to be no other, cleaner way to adjust the
	 * customizer title.
	 *
	 * @since 3.0.0
	 * @return string|false
	 */
	public function adjust_customizer_title() {

		if ( $this->is_voucher_template_customizer() ) {
			return $this->get_currently_previewed_voucher_template_name();
		}

		return false;
	}


	/**
	 * Returns the title of the voucher template being currently previewed
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_currently_previewed_voucher_template_name() {

		$title = null;

		if ( $id = $this->get_currently_previewed_voucher_template_id() ) {
			$title = get_the_title( $id );
		}

		if ( ! $title ) {
			$title = __( '(Untitled Voucher Template)', 'woocommerce-pdf-product-vouchers' );
		}

		return $title;
	}


	/**
	 * Returns the title of the voucher template being currently previewed
	 *
	 * @since 3.0.0
	 * @return int|null
	 */
	public function get_currently_previewed_voucher_template_id() {

		if ( $args = $this->get_preview_url_args() ) {
			return ! empty( $args['p'] ) ? (int) $args['p'] : null;
		}

		return null;
	}


	/**
	 * Checks if we are viewing the voucher template preview in Customizer
	 *
	 * This function can be used both in the admin and frontend.
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	private function is_voucher_template_customizer() {

		$is_preview = false;
		$args       = $this->get_preview_url_args();

		if ( ! empty( $args ) ) {

			if ( ! empty( $args['post_type'] ) && 'wc_voucher_template' === $args['post_type'] ) {
				$is_preview = true;
			}
			elseif ( ! empty( $args['p'] ) && 'wc_voucher_template' === get_post_type( $args['p'] ) ) {
				$is_preview = true;
			}
		}

		return $is_preview;
	}


	/**
	 * Returns current customizer preview URL query args, if available
	 *
	 * @since 3.0.0
	 * @return array
	 */
	private function get_preview_url_args() {
		global $pagenow;

		$args  = array();
		$query = null;

		// we're in admin on the customizer screen, and the URL is set in query vars
		if ( is_admin() && 'customize.php' === $pagenow && ! empty( $_GET['url'] ) ) {

			$query = parse_url( $_GET['url'], PHP_URL_QUERY );

		// we're in the frontend, loaded inside the customizer preview iframe
		} elseif ( is_customize_preview() ) {

			$query = $_SERVER['QUERY_STRING'];
		}

		if ( $query ) {
			parse_str( $query, $args );
		}

		return $args;
	}


	/**
	 * Adds customizer preview filter for voucher image
	 *
	 * Enables voucher primary image to support customizer changesets.
	 *
	 * @see WP_Customize_Setting::preview()
	 *
	 * @since 3.0.0
	 * @param \WP_Customize_Setting $setting a customizer setting instance
	 */
	public function add_preview_filter( WP_Customize_Setting $setting ) {

		// store a reference to the voucher image setting, so that
		// we can use access it in the filter itself
		$this->preview_filter_settings[ $setting->id ] = $setting;

		if ( 'wc_voucher_template_voucher_primary_image' == $setting->id ) {
			add_filter( 'wc_pdf_product_vouchers_voucher_template_image_id', array( $this, 'filter_voucher_primary_image_preview' ) );
		}

		if ( 'wc_voucher_template_voucher_additional_image' == $setting->id ) {
			add_filter( 'wc_pdf_product_vouchers_voucher_template_additional_image_id', array( $this, 'filter_voucher_additional_image_preview' ) );
		}

		if ( 'wc_voucher_template_voucher_logo' == $setting->id ) {
			add_filter( 'wc_pdf_product_vouchers_voucher_template_logo_image_id', array( $this, 'filter_voucher_logo_image_preview' ) );
		}
	}


	/**
	 * Filters voucher primary image value in customizer preview
	 *
	 * @see WP_Customize_Setting::_preview_filter()
	 *
	 * @since 3.0.0
	 * @param int $image_id original image id
	 * @return int filtered image id
	 */
	public function filter_voucher_primary_image_preview( $image_id ) {
		return $this->filter_voucher_image_preview( $image_id, 'wc_voucher_template_voucher_primary_image' );
	}


	/**
	 * Filters voucher additional image value in customizer preview
	 *
	 * @see WP_Customize_Setting::_preview_filter()
	 *
	 * @since 3.0.0
	 * @param int $image_id original image id
	 * @return int filtered image id
	 */
	public function filter_voucher_additional_image_preview( $image_id ) {
		return $this->filter_voucher_image_preview( $image_id, 'wc_voucher_template_voucher_additional_image' );
	}


	/**
	 * Filters voucher logo image value in customizer preview
	 *
	 * @see WP_Customize_Setting::_preview_filter()
	 *
	 * @since 3.0.0
	 * @param int $image_id original image id
	 * @return int filtered image id
	 */
	public function filter_voucher_logo_image_preview( $image_id ) {
		return $this->filter_voucher_image_preview( $image_id, 'wc_voucher_template_voucher_logo' );
	}


	/**
	 * Filters a voucher image ID for customizer preview
	 *
	 * @see WP_Customize_Setting::_preview_filter()
	 *
	 * @since 3.0.0
	 * @param int $image_id original image id
	 * @param string $type image type
	 * @return int filtered image id
	 */
	private function filter_voucher_image_preview( $image_id, $type ) {

		if ( isset( $this->preview_filter_settings[ $type ] ) ) {

			// apply any preview filters to the value
			$image_id = $this->preview_filter_settings[ $type ]->_preview_filter( $image_id );

			// primary image: the filtered value will be an array with the image id
			// as one of the keys. simply use the image id here
			if ( $image_id && is_array( $image_id ) && isset( $image_id['id'] ) ) {
				$image_id = $image_id['id'];
			}

			// additional image: the filtered value will be the image URL - turn it into
			// the image id
			if ( is_string( $image_id ) && SV_WC_Helper::str_starts_with( $image_id, 'http' ) ) {
				$image_id = attachment_url_to_postid( $image_id );
			}
		}

		return $image_id;
	}


	/**
	 * Validates voucher template post_title field
	 *
	 * @since 3.0.0
	 * @param \WP_Error $validity
	 * @param mixed $value field value
	 * @return \WP_Error $validity
	 */
	public function validate_voucher_template_post_title( $validity, $value ) {

		if ( ! $value ) {
			$validity->add( 'required', __( 'Please set a name for the template.', 'woocommerce-pdf-product-vouchers' ) );
		}

		return $validity;
	}

}
