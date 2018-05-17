<?php
/**
 *
 */

error_reporting( 1 );

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Optimisationio_CacheEnabler
 *
 * @since 1.0.0
 */
class Optimisationio_CacheEnabler {

	private static $instance = false;

	/**
	 * Plugin options
	 *
	 * @since  1.0.0
	 * @var    array
	 */
	public static $options;

	/**
	 * Disk cache object
	 *
	 * @since  1.0.0
	 * @var    object
	 */
	public static $disk;

	/**
	 * Minify default settings
	 *
	 * @since  1.0.0
	 * @var    integer
	 */
	const MINIFY_DISABLED  = 0;
	const MINIFY_HTML_ONLY = 1;
	const MINIFY_HTML_JS   = 2;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 * @change  1.2.0
	 */
	public function __construct() {

		// Set default vars.
		self::_set_default_vars();

		// Register publish hook.
		add_action( 'init', array( __CLASS__, 'register_publish_hooks' ), 99 );

		// Clear cache hooks.
		add_action( 'ce_clear_post_cache', array( __CLASS__, 'clear_page_cache_by_post_id' ) );
		add_action( 'ce_clear_cache', array( __CLASS__, 'clear_total_cache' ) );
		add_action( '_core_updated_successfully', array( __CLASS__, 'clear_total_cache' ) );
		add_action( 'switch_theme', array( __CLASS__, 'clear_total_cache' ) );
		add_action( 'wp_trash_post', array( __CLASS__, 'clear_total_cache' ) );
		add_action( 'autoptimize_action_cachepurged', array( __CLASS__, 'clear_total_cache' ) );

		// Add admin clear link.
		add_action( 'init', array( __CLASS__, 'process_clear_request' ) );

		// Admin.
		if ( is_admin() ) {

			add_action( 'wpmu_new_blog', array( __CLASS__, 'install_later' ) );
			add_action( 'delete_blog', array( __CLASS__, 'uninstall_later' ) );

			add_action( 'admin_init', array( __CLASS__, 'register_textdomain' ) );
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

			add_action( 'transition_comment_status', array( __CLASS__, 'change_comment' ), 10, 3 );
			add_action( 'comment_post', array( __CLASS__, 'comment_post' ), 99, 2 );
			add_action( 'edit_comment', array( __CLASS__, 'edit_comment' ) );

			add_filter( 'dashboard_glance_items', array( __CLASS__, 'add_dashboard_count' ) );
			add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'add_clear_dropdown' ) );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
			add_filter( 'plugin_action_links_' . OPTIMISATIONIO_CDN_ENABLER_BASE, array( __CLASS__, 'action_links' ) );

			// Warnings and notices.
			add_action( 'admin_notices', array( __CLASS__, 'warning_is_permalink' ) );
			add_action( 'admin_notices', array( __CLASS__, 'requirements_check' ) );
		} else {
			add_action( 'pre_comment_approved', array( __CLASS__, 'new_comment' ), 99, 2 );
			// Caching.
			add_action( 'template_redirect', array( __CLASS__, 'handle_cache' ), 0 );
		}// End if().
	}

	/**
	 * Deactivation hook
	 *
	 * @since   1.0.0
	 * @change  1.1.1
	 */
	public static function on_deactivation() {
		self::clear_total_cache( true );

		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			// Unset WP_CACHE.
			self::_set_wp_cache( false );
		}

		if ( $GLOBALS['is_apache'] && is_writable( ABSPATH . '.htaccess' ) ) {

			$settings = get_option( Optimisationio::OPTION_KEY . '_settings' );

			if ( 1 === $settings['enable_gzip_compression'] ) {
				self::remove_htaccess_modify_gzip_content();
			}
			if ( 1 === $settings['enable_leverage_browser_caching'] ) {
				self::remove_htaccess_modify_leverage_browser_caching();
			}
		}

		// Delete advanced cache file.
		unlink( WP_CONTENT_DIR . '/advanced-cache.php' );
	}

	/**
	 * Activation hook
	 *
	 * @since   1.0.0
	 * @change  1.1.1
	 */
	public static function on_activation() {
		$get_req = $_GET;	// Input var okay.
		// Multisite and network.
		if ( is_multisite() && ! empty( $get_req['networkwide'] ) ) {
			// Blog ids.
			$ids = self::_get_blog_ids();

			// Switch to blog.
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::_install_backend();
			}

			// Restore blog.
			restore_current_blog();

		} else {
			self::_install_backend();
		}

		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			// Set WP_CACHE.
			self::_set_wp_cache( true );
		}

		// Copy advanced cache file.
		copy( OPTIMISATIONIO_CDN_ENABLER_DIR . '/advanced-cache.php', WP_CONTENT_DIR . '/advanced-cache.php' );

		if ( $GLOBALS['is_apache'] && is_writable( ABSPATH . '.htaccess' ) ) {

			$settings = get_option( Optimisationio::OPTION_KEY . '_settings' );

			if ( 1 === $settings['enable_gzip_compression'] ) {
				self::htaccess_modify_gzip_content();
			}

			if ( 1 === $settings['enable_leverage_browser_caching'] ) {
				self::htaccess_modify_leverage_browser_caching();
			}
		}
	}

	public static function remove_htaccess_modify_gzip_content() {
		$rules         = '';
		$htaccess_file = ABSPATH . '.htaccess';
		if ( is_writable( $htaccess_file ) ) {
			$ftmp = file_get_contents( $htaccess_file );

			// Remove the WP Optimisation marker.
			$ftmp = preg_replace( '/## BEGIN WP Optimisation gzip(.*)## END WP Optimisation gzip/isU', '', $ftmp );

			// Remove empty spacings.
			$ftmp = str_replace( "\n\n", "\n", $ftmp );
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

			$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
			return $direct_filesystem->put_contents( $htaccess_file, $rules . $ftmp, $chmod );
		}
	}

	public static function remove_htaccess_modify_leverage_browser_caching() {
		$rules         = '';
		$htaccess_file = ABSPATH . '.htaccess';
		if ( is_writable( $htaccess_file ) ) {
			$ftmp = file_get_contents( $htaccess_file );

			// Remove the WP Optimisation marker.
			$ftmp = preg_replace( '/## BEGIN WP Optimisation Header Caching(.*)## END WP Optimisation Header Caching/isU', '', $ftmp );

			// Remove empty spacings.
			$ftmp = str_replace( "\n\n", "\n", $ftmp );
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

			$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
			return $direct_filesystem->put_contents( $htaccess_file, $rules . $ftmp, $chmod );
		}
	}

	public static function htaccess_modify_gzip_content() {
		$rules         = '';
		$htaccess_file = ABSPATH . '.htaccess';

		if ( is_writable( $htaccess_file ) ) {

			// Get content of .htaccess file.
			$ftmp = file_get_contents( $htaccess_file );

			// Remove the WP Optimisation marker.
			$ftmp = preg_replace( '/## BEGIN WP Optimisation gzip(.*)## END WP Optimisation gzip/isU', '', $ftmp );

			// Remove empty spacings.
			$ftmp = str_replace( "\n\n", "\n", $ftmp );

			$rules = '## BEGIN WP Optimisation gzip' . PHP_EOL;
			$rules .= '<IfModule mod_deflate.c>' . PHP_EOL;
			$rules .= '## Compress HTML, CSS, JavaScript, Text, XML and fonts' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/javascript' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/rss+xml' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/vnd.ms-fontobject' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/x-font' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/x-font-opentype' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/x-font-otf' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/x-font-truetype' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/x-font-ttf' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/x-javascript' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/xhtml+xml' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE application/xml' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE font/opentype' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE font/otf' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE font/ttf' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE image/svg+xml' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE image/x-icon' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE text/css' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE text/html' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE text/javascript' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE text/plain' . PHP_EOL;
			$rules .= 'AddOutputFilterByType DEFLATE text/xml' . PHP_EOL;
			$rules .= '## Remove browser bugs (only needed for really old browsers)' . PHP_EOL;
			$rules .= 'BrowserMatch ^Mozilla/4 gzip-only-text/html' . PHP_EOL;
			$rules .= 'BrowserMatch ^Mozilla/4\.0[678] no-gzip' . PHP_EOL;
			$rules .= 'BrowserMatch \bMSIE !no-gzip !gzip-only-text/html' . PHP_EOL;
			$rules .= 'Header append Vary User-Agent' . PHP_EOL;
			$rules .= '</IfModule>' . PHP_EOL;
			$rules .= '## END WP Optimisation gzip' . PHP_EOL;

			// Update the .htacces file.
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

			$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
			return $direct_filesystem->put_contents( $htaccess_file, $rules . $ftmp, $chmod );
		}// End if().
	}

	public static function htaccess_modify_leverage_browser_caching() {
		$rules         = '';
		$htaccess_file = ABSPATH . '.htaccess';

		if ( is_writable( $htaccess_file ) ) {

			// Get content of .htaccess file.
			$ftmp = file_get_contents( $htaccess_file );

			// Remove the WP Optimisation marker.
			$ftmp = preg_replace( '/## BEGIN WP Optimisation Header Caching(.*)## END WP Optimisation Header Caching/isU', '', $ftmp );

			// Remove empty spacings.
			$ftmp = str_replace( "\n\n", "\n", $ftmp );

			$rules = '## BEGIN WP Optimisation Header Caching' . PHP_EOL;
			$rules .= 'ExpiresActive On' . PHP_EOL;
			$rules .= 'ExpiresByType image/jpg "access 1 year"' . PHP_EOL;
			$rules .= 'ExpiresByType image/jpeg "access 1 year"' . PHP_EOL;
			$rules .= 'ExpiresByType image/gif "access 1 year"' . PHP_EOL;
			$rules .= 'ExpiresByType image/png "access 1 year"' . PHP_EOL;
			$rules .= 'ExpiresByType text/css "access 1 month"' . PHP_EOL;
			$rules .= 'ExpiresByType application/pdf "access 1 month"' . PHP_EOL;
			$rules .= 'ExpiresByType application/javascript "access 1 month"' . PHP_EOL;
			$rules .= 'ExpiresByType application/x-javascript "access 1 month"' . PHP_EOL;
			$rules .= 'ExpiresByType application/x-shockwave-flash "access 1 month"' . PHP_EOL;
			$rules .= 'ExpiresByType image/x-icon "access 1 year"' . PHP_EOL;
			$rules .= 'ExpiresDefault "access 2 days"' . PHP_EOL;
			$rules .= '## END WP Optimisation Header Caching' . PHP_EOL;

			// Update the .htacces file.
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

			$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
			return $direct_filesystem->put_contents( $htaccess_file, $rules . $ftmp, $chmod );
		}// End if().
	}

	/**
	 * Install on multisite setup
	 *
	 * @since   1.0.0
	 */
	public static function install_later( $id ) {

		// Check if multisite setup.
		if ( ! is_plugin_active_for_network( OPTIMISATIONIO_CDN_ENABLER_BASE ) ) {
			return;
		}

		// Switch to blog.
		switch_to_blog( $id );

		// Installation.
		self::_install_backend();

		// Restore.
		restore_current_blog();
	}

	/**
	 * installation options
	 *
	 * @since   1.0.0
	 */
	public static function _install_backend() {

		add_option( 'cache', array() );

		// Clear.
		self::clear_total_cache( true );
	}

	/**
	 * installation WP_CACHE (advanced cache)
	 *
	 * @since   1.1.1
	 */
	public static function _set_wp_cache( $wp_cache_value = true ) {
		$wp_config_file = ABSPATH . 'wp-config.php';

		if ( file_exists( $wp_config_file ) && is_writable( $wp_config_file ) ) {
			// Get wp config as array.
			$wp_config = file( $wp_config_file );

			if ( $wp_cache_value ) {
				$wp_cache_ce_line = "define('WP_CACHE', true); // Added by Optimisation.io\r\n";
			} else {
				$wp_cache_ce_line = '';
			}

			$found_wp_cache = false;

			foreach ( $wp_config as &$line ) {
				if ( preg_match( '/^\s*define\s*\(\s*[\'\"]WP_CACHE[\'\"]\s*,\s*(.*)\s*\)/', $line ) ) {
					$line           = $wp_cache_ce_line;
					$found_wp_cache = true;
					break;
				}
			}

			// Add wp cache ce line if not found yet.
			if ( ! $found_wp_cache ) {
				array_shift( $wp_config );
				array_unshift( $wp_config, "<?php\r\n", $wp_cache_ce_line );
			}

			// Write wp-config.php file.
			$fh = @fopen( $wp_config_file, 'w' );
			foreach ( $wp_config as $ln ) {
				@fwrite( $fh, $ln );
			}

			@fclose( $fh );
		}
	}

	/**
	 * Uninstall per multisite blog
	 *
	 * @since   1.0.0
	 */
	public static function on_uninstall() {

		global $wpdb;

		$get_req = $_GET;	// Input var okay.

		// Multisite and network.
		if ( is_multisite() && ! empty( $get_req['networkwide'] ) ) {
			// Legacy blog.
			$old = $wpdb->blogid;

			// Blog id.
			$ids = self::_get_blog_ids();

			// Uninstall per blog.
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::_uninstall_backend();
			}

			// Restore.
			switch_to_blog( $old );
		} else {
			self::_uninstall_backend();
		}
	}

	/**
	 * Uninstall for multisite and network
	 *
	 * @param  ineger $id [[Description]].
	 * @since   1.0.0
	 */
	public static function uninstall_later( $id ) {

		// Check if network plugin.
		if ( ! is_plugin_active_for_network( OPTIMISATIONIO_CDN_ENABLER_BASE ) ) {
			return;
		}

		// Switch.
		switch_to_blog( $id );

		// Uninstall.
		self::_uninstall_backend();

		// Restore.
		restore_current_blog();
	}

	/**
	 * Uninstall
	 *
	 * @since   1.0.0
	 */
	public static function _uninstall_backend() {

		// Delete options.
		delete_option( 'cache' );

		// Clear cache.
		self::clear_total_cache( true );
	}

	/**
	 * Get blog ids
	 *
	 * @since   1.0.0
	 *
	 * @return  array  blog ids array
	 */
	public static function _get_blog_ids() {
		global $wpdb;
		return $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
	}

	/**
	 * Set default vars
	 *
	 * @since   1.0.0
	 */
	public static function _set_default_vars() {

		// Get options.
		self::$options = self::_get_options();

		// Disk cache.
		if ( Optimisationio_CacheEnablerDisk::is_permalink() ) {
			self::$disk = new Optimisationio_CacheEnablerDisk;
		}
	}

	/**
	 * Get options
	 *
	 * @since   1.0.0
	 * @change  1.1.0
	 *
	 * @return  array  options array
	 */
	public static function _get_options() {

		return wp_parse_args(
			get_option( Optimisationio::OPTION_KEY . '_settings' ),
			array(
				'cache_expires'     => 0,
				'cache_new_post'    => 0,
				'cache_new_comment' => 0,
				'cache_compress'    => 0,
				'cache_user_logged_in_content' => 0,
				'cache_webp'        => 0,
				'excl_ids'          => '',
				'minify_html'       => self::MINIFY_DISABLED,
			)
		);
	}

	/**
	 * Warning if no custom permlinks
	 *
	 * @since   1.0.0
	 */
	public static function warning_is_permalink() {
		if ( ! Optimisationio_CacheEnablerDisk::is_permalink() && current_user_can( 'manage_options' ) ) {?>
			<div class="error">
				<p><?php printf( esc_html( 'The <b>%1$s</b> plugin requires a custom permalink structure to start caching properly. Please go to <a href="%2$s">Permalink</a> to enable it.', 'optimisationio' ), 'Optimisationio', admin_url( 'options-permalink.php' ) );?></p>
			</div>
		<?php
		}
	}

	/**
	 * Add action links
	 *
	 * @since   1.0.0
	 *
	 * @param   array $data  existing links.
	 * @return  array  $data  appended links
	 */
	public static function action_links( $data ) {
		// Check user role.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $data;
		}
		return array_merge( $data, array(
			sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=optimisationio-dashboard' ), esc_html__( 'Settings', 'optimisationio' ) ),
		) );
	}

	/**
	 * Cache enabler meta links
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $input  existing links.
	 * @param   string $page   page.
	 * @return  array   $data   appended links
	 */
	public static function row_meta( $input, $page ) {
		// Check permissions.
		if ( OPTIMISATIONIO_CDN_ENABLER_BASE !== $page ) {
			return $input;
		}
		return array_merge( $input, array() );
	}

	/**
	 * Add dashboard cache size count
	 *
	 * @since   1.0.0
	 * @change  1.1.0
	 *
	 * @param   array $items  initial array with dashboard items.
	 * @return  array  $items  merged array with dashboard items
	 */
	public static function add_dashboard_count( $items = array() ) {

		// Check user role.
		if ( ! current_user_can( 'manage_options' ) ) {
			return $items;
		}

		// Get cache size.
		$size = self::get_cache_size();

		// Display items.
		$items[] = sprintf(
			'<a href="%s" title="%s">%s %s</a>',
			add_query_arg(
				array(
					'page' => 'optimisationio',
				),
				admin_url( 'options-general.php' )
			),
			esc_html__( 'Disk Cache', 'optimisationio' ),
			(empty( $size ) ? esc_html__( 'Empty', 'optimisationio' ) : size_format( $size )),
			esc_html__( 'Cache Size', 'optimisationio' )
		);

		return $items;
	}

	/**
	 * Get cache size
	 *
	 * @since   1.0.0
	 *
	 * @param   integer $size  cache size (bytes).
	 */
	public static function get_cache_size() {

		$size = get_transient( 'cache_size' );
		if ( false === $size ) {

			if( ! isset( self::$disk ) || null === self::$disk ){
				self::_set_default_vars();
			}

			$size = (int) self::$disk->cache_size( OPTIMISATIONIO_CACHE_DIR );
			// Set transient.
			set_transient( 'cache_size', $size, 15 * MINUTE_IN_SECONDS );
		}
		return $size;
	}

	public static function get_optimisation_info(){
		global $wpdb;
		return $wpdb->get_row( "SELECT size, optimised_size, size - optimised_size AS saving FROM " . $wpdb->prefix. "wp_optimisation_sizes_info ORDER BY date DESC LIMIT 1" );
	}

	/**
	 * Process clear request
	 *
	 * @since   1.0.0
	 * @change  1.1.0
	 *
	 * @param   array $data  array of metadata.
	 */
	public static function process_clear_request( $data ) {

		$get_req = $_GET;	// Input var okay.

		// Check if clear request.
		if ( empty( $get_req['_cache'] ) || ('clear' !== $get_req['_cache'] && 'clearurl' !== $get_req['_cache']) ) {
			return;
		}

		// Validate nonce.
		if ( empty( $get_req['_wpnonce'] ) || ! wp_verify_nonce( $get_req['_wpnonce'], '_cache__clear_nonce' ) ) {
			return;
		}

		// Check user role.
		if ( ! is_admin_bar_showing() || ! apply_filters( 'user_can_clear_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		// Load if network.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Set clear url w/o query string.
		$clear_url = preg_replace( '/\?.*/', '', home_url( add_query_arg( null, null ) ) );

		// Multisite and network setup.
		if ( is_multisite() && is_plugin_active_for_network( OPTIMISATIONIO_CDN_ENABLER_BASE ) ) {

			if ( is_network_admin() ) {

				// legacy blog.
				$legacy = $GLOBALS['wpdb']->blogid;

				// Blog ids.
				$ids = self::_get_blog_ids();

				// Switch blogs.
				foreach ( $ids as $id ) {
					switch_to_blog( $id );
					self::clear_page_cache_by_url( home_url() );
				}

				// Restore.
				switch_to_blog( $legacy );

				// Clear notice.
				if ( is_admin() ) {
					add_action( 'network_admin_notices', array( __CLASS__, 'clear_notice' ) );
				}
			} else {
				if ( 'clearurl' === $get_req['_cache'] ) {
					// Clear specific multisite url cache.
					self::clear_page_cache_by_url( $clear_url );
				} else {
					// Clear specific multisite cache.
					self::clear_page_cache_by_url( home_url() );

					// Clear notice.
					if ( is_admin() ) {
						add_action( 'admin_notices', array( __CLASS__, 'clear_notice' ) );
					}
				}
			}// End if().
		} else {
			if ( 'clearurl' === $get_req['_cache'] ) {
				// Clear url cache.
				self::clear_page_cache_by_url( $clear_url );
			} else {
				// Clear cache.
				self::clear_total_cache();

				// Clear notice.
				if ( is_admin() ) {
					add_action( 'admin_notices', array( __CLASS__, 'clear_notice' ) );
				}
			}
		}// End if().

		if ( ! is_admin() ) {
			wp_safe_redirect( remove_query_arg( '_cache', wp_get_referer() ) );
			exit();
		}
		else{
			wp_safe_redirect( wp_get_referer() );
			exit();
		}
	}

	/**
	 * Notification after clear cache
	 *
	 * @since   1.0.0
	 * @change  1.0.0
	 *
	 * @hook    mixed  user_can_clear_cache
	 */
	public static function clear_notice() {
		// Check if admin.
		if ( ! is_admin_bar_showing() || ! apply_filters( 'user_can_clear_cache', current_user_can( 'manage_options' ) ) ) {
			return false;
		}
		echo sprintf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html__( 'The cache has been cleared.', 'optimisationio' ) );
	}

	/**
	 * Clear cache if post comment
	 *
	 * @since   1.2.0
	 *
	 * @param   integer $id  id of the comment.
	 * @param   mixed   $approved  approval status.
	 */
	public static function comment_post( $id, $approved ) {
		// Check if comment is approved.
		if ( 1 === $approved ) {
			if ( self::$options['cache_new_comment'] ) {
				self::clear_total_cache();
			} else {
				self::clear_page_cache_by_post_id( get_comment( $id )->comment_post_ID );
			}
		}
	}

	/**
	 * Clear cache if edit comment
	 *
	 * @since   1.0.0
	 *
	 * @param   integer $id  id of the comment.
	 */
	public static function edit_comment( $id ) {
		// Clear complete cache if option enabled.
		if ( self::$options['cache_new_comment'] ) {
			self::clear_total_cache();
		} else {
			self::clear_page_cache_by_post_id(
				get_comment( $id )->comment_post_ID
			);
		}
	}

	/**
	 * Clear cache if new comment
	 *
	 * @since   1.0.0
	 *
	 * @param   mixed $approved  approval status.
	 * @param   array $comment .
	 * @return  mixed  $approved  approval status
	 */
	public static function new_comment( $approved, $comment ) {
		// Check if comment is approved.
		if ( 1 === $approved ) {
			if ( self::$options['cache_new_comment'] ) {
				self::clear_total_cache();
			} else {
				self::clear_page_cache_by_post_id( $comment['comment_post_ID'] );
			}
		}
		return $approved;
	}

	/**
	 * Clear cache if comment changes
	 *
	 * @since   1.0.0
	 *
	 * @param   string $after_status .
	 * @param   string $before_status .
	 * @param   object $comment .
	 */
	public static function change_comment( $after_status, $before_status, $comment ) {
		// Check if changes occured.
		if ( $after_status !== $before_status ) {
			if ( self::$options['cache_new_comment'] ) {
				self::clear_total_cache();
			} else {
				self::clear_page_cache_by_post_id( $comment->comment_post_ID );
			}
		}
	}

	/**
	 * Register publish hooks for custom post types
	 *
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public static function register_publish_hooks() {

		// Get post types.
		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);

		// Check if empty.
		if ( empty( $post_types ) ) {
			return;
		}

		// Post type actions.
		foreach ( $post_types as $post_type ) {
			add_action(
				'publish_' . $post_type,
			array( __CLASS__, 'publish_post_types' ), 10, 2 );
			add_action(
				'publish_future_' . $post_type,
				array( __CLASS__, 'clear_total_cache' )
			);
		}
	}

	/**
	 * Delete post type cache on post updates
	 *
	 * @since   1.0.0
	 * @change  1.0.7
	 *
	 * @param   integer $post_id  Post ID.
	 * @param   object  $post  Post object.
	 */
	public static function publish_post_types( $post_id, $post ) {
		$post_req = $_POST; 	// Input var okay.

		// Check if post id || post is empty.
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Check post status.
		if ( ! in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
			return;
		}

		// Purge cache if clean post on update.
		if ( ! isset( $post_req['_clear_post_cache_on_update'] ) ) {

			// Clear complete cache if option enabled.
			if ( self::$options['cache_new_post'] ) {
				return self::clear_total_cache();
			} else {
				return self::clear_home_page_cache();
			}
		}

		// Validate nonce.
		if ( ! isset( $post_req[ '_cache__status_nonce_' . $post_id ] ) || ! wp_verify_nonce( $post_req[ '_cache__status_nonce_' . $post_id ], OPTIMISATIONIO_CDN_ENABLER_BASE ) ) {
			return;
		}

		// Validate user role.
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		// Save as integer.
		$clear_post_cache = (int) $post_req['_clear_post_cache_on_update'];

		// Save user metadata.
		update_user_meta( get_current_user_id(), '_clear_post_cache_on_update', $clear_post_cache );

		// Purge complete cache || specific post.
		if ( $clear_post_cache ) {
			self::clear_page_cache_by_post_id( $post_id );
		} else {
			self::clear_total_cache();
		}
	}

	/**
	 * Clear page cache by post id
	 *
	 * @since   1.0.0
	 *
	 * @param   integer $post_id  Post ID.
	 */
	public static function clear_page_cache_by_post_id( $post_id ) {
		$post_id = (int) $post_id;
		// Is int.
		if ( ! $post_id ) { return; }
		// Clear cache by URL.
		self::clear_page_cache_by_url( get_permalink( $post_id ) );
	}

	/**
	 * Clear page cache by url
	 *
	 * @since   1.0.0
	 *
	 * @param  string $url  url of a page.
	 */
	public static function clear_page_cache_by_url( $url ) {
		// Validate string.
		if ( $url !== (string) $url ) { return; }
		call_user_func( array( self::$disk, 'delete_asset' ), $url );
	}

	/**
	 * Clear home page cache
	 *
	 * @since   1.0.7
	 */
	public static function clear_home_page_cache() {
		call_user_func( array( self::$disk, 'clear_home' ) );
	}

	/**
	 * Explode on comma
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input  input string.
	 * @return  array           array of strings
	 */
	public static function _preg_split( $input ) {
		return (array) preg_split( '/,/', $input, -1, PREG_SPLIT_NO_EMPTY );
	}

	/**
	 * Check if index.php
	 *
	 * @since   1.0.0
	 *
	 * @return  boolean  true if index.php
	 */
	public static function _is_index() {
		$server_req = $_SERVER;	// Input var okay.
		return 'index.php' !== basename( $server_req['SCRIPT_NAME'] );
	}

	/**
	 * Check if mobile
	 *
	 * @since   1.0.0
	 *
	 * @return  boolean  true if mobile
	 */
	public static function _is_mobile() {
		return (strpos( TEMPLATEPATH, 'wptouch' ) || strpos( TEMPLATEPATH, 'carrington' ) || strpos( TEMPLATEPATH, 'jetpack' ) || strpos( TEMPLATEPATH, 'handheld' ));
	}

	/**
	 * Check if logged in
	 *
	 * @since   1.0.0
	 *
	 * @return  boolean  true if logged in || cookie set
	 */
	public static function _is_logged_in() {

		$cookie_val = $_COOKIE;	// Input var okay.

		// Check if logged in.
		if ( is_user_logged_in() ) {
			return true;
		}

		// Check cookie.
		if ( empty( $cookie_val ) ) {
			return false;
		}

		// Check cookie values.
		foreach ( $cookie_val as $k => $v ) {
			if ( preg_match( '/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k ) ) {
				return true;
			}
		}
	}

	/**
	 * Check to bypass the cache
	 *
	 * @since   1.0.0
	 * @change  1.0.7
	 *
	 * @return  boolean  true if exception
	 *
	 * @hook    boolean  bypass cache
	 */
	public static function _bypass_cache() {

		$get_req = $_GET; // Input var okay.
		$erver_req = $_SERVER; // Input var okay.

		// Bypass cache hook.
		if ( apply_filters( 'bypass_cache', false ) ) {
			return true;
		}

		// Conditional tags.
		if ( self::_is_index() || is_search() || is_404() || is_feed() || is_trackback() || is_robots() || is_preview() || post_password_required() ) {
			return true;
		}

		// DONOTCACHEPAGE check e.g. woocommerce.
		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
			return true;
		}

		$options = get_option( Optimisationio::OPTION_KEY . '_settings', array() );

		// Request method GET.
		if ( ! isset( $erver_req['REQUEST_METHOD'] ) || 'GET' !== $erver_req['REQUEST_METHOD'] ) {
			return true;
		}

		// Request with query strings.
		if ( ! empty( $get_req ) && ! isset( $get_req['utm_source'], $get_req['utm_medium'], $get_req['utm_campaign'] ) && get_option( 'permalink_structure' ) ) {
			return true;
		}

		// If logged in.
		if ( self::_is_logged_in() ) {
			return true;
		}

		// If mobile request.
		if ( self::_is_mobile() ) {
			return true;
		}

		// If post id excluded.
		if ( $options['excl_ids'] && is_singular() ) {
			if ( in_array( $GLOBALS['wp_query']->get_queried_object_id(), self::_preg_split( $options['excl_ids'] ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Minify html
	 *
	 * @since   1.0.0
	 *
	 * @param   string $data  minify request data.
	 * @return  string  $data  minify response data
	 *
	 * @hook    array   cache_minify_ignore_tags
	 */
	public static function _minify_cache( $data ) {
		// Check if disabled.
		if ( ! self::$options['minify_html'] ) {
			return $data;
		}

		// Strlen limit.
		if ( strlen( $data ) > 700000 ) {
			return $data;
		}

		// Ignore this tags.
		$ignore_tags = (array) apply_filters( 'cache_minify_ignore_tags', array( 'textarea', 'pre' ) );

		// Ignore JS if selected.
		if ( self::MINIFY_HTML_JS !== self::$options['minify_html'] ) {
			$ignore_tags[] = 'script';
		}

		// Return of no ignore tags.
		if ( ! $ignore_tags ) {
			return $data;
		}

		// Stringify.
		$ignore_regex = implode( '|', $ignore_tags );

		// Regex minification.
		$cleaned = preg_replace(
			array( '/<!--[^\[><](.*?)-->/s', '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:' . $ignore_regex . ')\b))*+)(?:<(?>' . $ignore_regex . ')\b|\z))#' ),
			array( '', ' ' ),
			$data
		);

		// Something went wrong.
		if ( strlen( $cleaned ) <= 1 ) {
			return $data;
		}

		return $cleaned;
	}

	/**
	 * Clear complete cache
	 *
	 * @since   1.0.0
	 */
	public static function clear_total_cache() {
		// Clear disk cache.
		Optimisationio_CacheEnablerDisk::clear_cache();
		// Delete transient.
		delete_transient( 'cache_size' );
	}

	/**
	 * Set cache
	 *
	 * @since   1.0.0
	 *
	 * @param   string $data  content of a page.
	 * @return  string  $data  content of a page
	 */
	public static function set_cache( $data ) {
		// Check if empty.
		if ( empty( $data ) ) {
			return '';
		}
		// Store as asset.
		call_user_func( array( self::$disk, 'store_asset' ), self::_minify_cache( $data ) );
		return $data;
	}

	/**
	 * Handle cache
	 *
	 * @since   1.0.0
	 * @change  1.0.1
	 */
	public static function handle_cache() {

		// Bypass cache.
		if ( self::_bypass_cache() ) {
			return;
		}

		// Get asset cache status.
		$cached = call_user_func(
			array( self::$disk, 'check_asset' )
		);

		// Check if cache empty.
		if ( empty( $cached ) ) {
			ob_start( 'Optimisationio_CacheEnabler::set_cache' );
			return;
		}

		// Get expiry status.
		$expired = call_user_func(
			array( self::$disk, 'check_expiry' )
		);

		// Check if expired.
		if ( $expired ) {
			ob_start( 'Optimisationio_CacheEnabler::set_cache' );
			return;
		}

		// Return cached asset.
		call_user_func(
			array( self::$disk, 'get_asset' )
		);
	}

	/**
	 * Add clear option dropdown on post publish widget
	 *
	 * @since   1.0.0
	 */
	public static function add_clear_dropdown() {

		// On published post page only.
		if ( empty( $GLOBALS['pagenow'] ) || 'post.php' !== $GLOBALS['pagenow'] || empty( $GLOBALS['post'] ) || ! is_object( $GLOBALS['post'] ) || 'publish' !== $GLOBALS['post']->post_status ) {
			return;
		}

		// Check user role.
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		// Validate nonce.
		wp_nonce_field( OPTIMISATIONIO_CDN_ENABLER_BASE, '_cache__status_nonce_' . $GLOBALS['post']->ID );

		// Get current action.
		$current_action = (int) get_user_meta(
			get_current_user_id(),
			'_clear_post_cache_on_update',
			true
		);

		// Init variables.
		$dropdown_options  = '';
		$available_options = array(
			esc_html__( 'Completely', 'optimisationio' ),
			esc_html__( 'Page specific', 'optimisationio' ),
		);

		// Set dropdown options.
		foreach ( $available_options as $key => $value ) {
			$dropdown_options .= sprintf( '<option value="%1$d" %3$s>%2$s</option>', $key, $value, selected( $key, $current_action, false ) );
		}

		// Output drowdown.
		echo sprintf(
			'<div class="misc-pub-section" style="border-top:1px solid #eee">
                <label for="cache_action">
                    %1$s: <span id="output-cache-action">%2$s</span>
                </label>
                <a href="#" class="edit-cache-action hide-if-no-js">%3$s</a>

                <div class="hide-if-js">
                    <select name="_clear_post_cache_on_update" id="cache_action">
                        %4$s
                    </select>

                    <a href="#" class="save-cache-action hide-if-no-js button">%5$s</a>
                     <a href="#" class="cancel-cache-action hide-if-no-js button-cancel">%6$s</a>
                 </div>
            </div>',
			esc_html__( 'Clear cache', 'optimisationio' ),
			$available_options[ $current_action ],
			esc_html__( 'Edit' ),
			$dropdown_options,
			esc_html__( 'OK' ),
			esc_html__( 'Cancel' )
		);
	}

	/**
	 * Minify caching dropdown
	 *
	 * @since   1.0.0
	 *
	 * @return  array    Key => value array
	 */
	public static function _minify_select() {
		return array(
			self::MINIFY_DISABLED  => esc_html__( 'Disabled', 'optimisationio' ),
			self::MINIFY_HTML_ONLY => esc_html__( 'HTML', 'optimisationio' ),
			self::MINIFY_HTML_JS   => esc_html__( 'HTML & Inline JS', 'optimisationio' ),
		);
	}

	/**
	 * Check plugin requirements
	 *
	 * @since   1.1.0
	 */
	public static function requirements_check() {

		// Cache enabler options.
		$options = self::$options;

		// WordPress version check.
		if ( version_compare( $GLOBALS['wp_version'], OPTIMISATIONIO_CDN_ENABLER_MIN_WP . 'alpha', '<' ) ) {
			show_message(
				sprintf(
					'<div class="error"><p>%s</p></div>',
					sprintf( esc_html( 'The <b>%1$s</b> is optimized for WordPress %2$s. Please disable the plugin || upgrade your WordPress installation (recommended).', 'optimisationio' ), 'Cache Enabler', OPTIMISATIONIO_CDN_ENABLER_MIN_WP )
				)
			);
		}

		// Permission check.
		if ( file_exists( OPTIMISATIONIO_CACHE_DIR ) && ! is_writable( OPTIMISATIONIO_CACHE_DIR ) ) {
			show_message(
				sprintf(
					'<div class="error"><p>%s</p></div>',
					sprintf( esc_html( 'The <b>%1$s</b> requires write permissions %2$s on %3$s. Please <a href="%4$s" target="_blank">change the permissions</a>.', 'optimisationio' ),
						'Cache Enabler',
						'<code>755</code>',
						'<code>wp-content/cache</code>',
						'http://codex.wordpress.org/Changing_File_Permissions',
						OPTIMISATIONIO_CDN_ENABLER_MIN_WP
					)
				)
			);
		}

		// Autoptimize minification check.
		if ( defined( 'AUTOPTIMIZE_PLUGIN_DIR' ) && $options['minify_html'] ) {
			show_message( sprintf( '<div class="error"><p>%s</p></div>', sprintf( esc_html( 'The <b>%1$s</b> plugin is already active. Please disable minification in the <b>%2$s</b> settings.', 'optimisationio' ), 'Autoptimize', 'optimisationio' ) ) );
		}
	}

	/**
	 * Register textdomain
	 *
	 * @since   1.0.0
	 */
	public static function register_textdomain() {
		load_plugin_textdomain( 'optimisationio', false, 'optimisationio/lang' );
	}

	/**
	 * Register settings
	 *
	 * @since   1.0.0
	 */
	public static function register_settings() {
		register_setting( 'optimisationio', 'cache', array( __CLASS__, 'validate_settings' ) );
	}

	/**
	 * Validate settings
	 *
	 * @since   1.0.0
	 * @change  1.0.9
	 *
	 * @param   array $data  array form data.
	 * @return  array         array form data valid
	 */
	public static function validate_settings( $data ) {

		// Check if empty.
		if ( empty( $data ) ) {
			return;
		}

		// Clear complete cache.
		self::clear_total_cache( true );

		return array(
			'cache_expires'     => (int) $data['cache_expires'],
			'cache_new_post'    => (int) ( ! empty( $data['cache_new_post'] )),
			'cache_new_comment' => (int) ( ! empty( $data['cache_new_comment'] )),
			'cache_webp'        => (int) ( ! empty( $data['cache_webp'] )),
			'cache_compress'    => (int) ( ! empty( $data['cache_compress'] )),
			'cache_user_logged_in_content' => (int) ( ! empty( $data['cache_user_logged_in_content'] )),
			'excl_ids'          => (string) sanitize_text_field( @$data['excl_ids'] ),
			'minify_html'       => (int) $data['minify_html'],
		);
	}
}
