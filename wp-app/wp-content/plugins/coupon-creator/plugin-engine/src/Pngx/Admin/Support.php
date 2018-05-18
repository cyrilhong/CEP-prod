<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Admin Support Info
 * Based of Modern Tribe, Revisr, and EDD System Info
 * the-events-calendar/common/src/Tribe/Support.php
 * revisr/classes/class-revisr-admin.php
 * easy-digital-downloads/includes/admin/tools.php
 */
if ( ! class_exists( 'Pngx__Admin__Support' ) ) {

	class Pngx__Admin__Support {

		public static $support;


		private function __construct() {

		}

		public function get_system_info_copy() {
			echo '<div class="pngx-system-info-copy" >
						<button data-clipboard-action = "copy" class="pngx-system-info-copy-btn" data-clipboard-target = ".pngx-support-stats" >
							<span class="dashicons dashicons-clipboard license-btn" ></span > ' . __( 'Copy to clipboard', 'tribe - common' ) . ' 
						</button >
					</div >';
		}

		/**
		 * Collect system information for support
		 *
		 * @return array of system data for support
		 */
		public function get_support_stats() {

			$systeminfo = array(
				'Site'                   => $this->get_site_info(),
				'WordPress'              => $this->get_wp_info(),
				'System'                 => $this->get_site_server_info(),
				'User'                   => $this->get_user_info(),
				'Plugin Engine Settings' => $this->get_pngx_info(),
				'Active Plugins'         => $this->get_plugins(),
				'Inactive Plugins'       => $this->get_plugins( true ),
				'Network Plugins'        => $this->get_network_plugins(),
				'MU Plugins'             => $this->get_mu_plugins(),
			);

			$systeminfo = apply_filters( 'pngx-support-info', $systeminfo );

			return $systeminfo;
		}

		/**
		 * Render system information into a pretty output
		 *
		 * @return string pretty HTML
		 */
		public function formatted_support_stats() {
			$systeminfo = $this->get_support_stats();
			$output     = '';
			$output .= '<dl class="pngx-support-stats">';
			foreach ( $systeminfo as $k => $v ) {

				switch ( $k ) {
					case 'name' :
					case 'email' :
						continue 2;
						break;
					case 'url' :
						$v = sprintf( '<a href="%s">%s</a>', $v, $v );
						break;
				}

				if ( is_array( $v ) ) {
					$keys             = array_keys( $v );
					$key              = array_shift( $keys );
					$is_numeric_array = is_numeric( $key );
					unset( $keys );
					unset( $key );
				}

				$output .= sprintf( '<dt>%s</dt>', $k );
				if ( empty( $v ) ) {
					$output .= '<dd class="support-stats-null">-</dd>';
				} elseif ( is_bool( $v ) ) {
					$output .= sprintf( '<dd class="support-stats-bool">%s</dd>', $v );
				} elseif ( is_string( $v ) ) {
					$output .= sprintf( '<dd class="support-stats-string">%s</dd>', $v );
				} elseif ( is_array( $v ) && $is_numeric_array ) {
					$output .= sprintf( '<dd class="support-stats-array"><ul><li>%s</li></ul></dd>', join( '</li><li>', $v ) );
				} else {
					$formatted_v = array();
					foreach ( $v as $obj_key => $obj_val ) {
						if ( is_array( $obj_val ) ) {
							$formatted_v[] = sprintf( '<li>%s = <pre>%s</pre></li>', $obj_key, print_r( $obj_val, true ) );
						} else {
							$formatted_v[] = sprintf( '<li>%s = %s</li>', $obj_key, $obj_val );
						}
					}
					$v = join( "\n", $formatted_v );
					$output .= sprintf( '<dd class="support-stats-object"><ul>%s</ul></dd>', print_r( $v, true ) );
				}
			}
			$output .= '</dl>';

			return $output;
		}


		/**
		 * Get a basic overview of site info
		 *
		 * @return array
		 */
		public function get_site_info() {
			global $wpdb;

			// Get theme info from EDD, thanks!
			$theme_data   = wp_get_theme();
			$theme        = $theme_data->Name . ' ' . $theme_data->Version;
			$parent_theme = $theme_data->Template;
			if ( ! empty( $parent_theme ) ) {
				$parent_theme_data = wp_get_theme( $parent_theme );
				$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
			}

			$site                 = array();
			$site['Active Theme'] = $theme;
			if ( $parent_theme !== $theme ) {
				$site['Parent Theme'] = $parent_theme;
			}
			$site['Character Set'] = get_option( 'blog_charset' );
			$site['Home URL']      = get_home_url();
			$site['Site URL']      = get_site_url();
			$site['Site Language'] = get_option( 'WPLANG' ) ? get_option( 'WPLANG' ) : esc_html__( 'English', 'plugin-engine' );
			$site['Table Prefix']  = 'Length: ' . strlen( $wpdb->prefix );

			/**
			 * Filter the Site Infomation for System Infomation
			 *
			 * @param $site array an array of site infomation
			 */
			$site = apply_filters( 'pngx-filter-system-info-site-infomation', $site );

			return $site;

		}

		/**
		 * Return WordPress Settings and Defaults
		 *
		 * @return array
		 */
		public function get_wp_info() {

			$wordpress                           = array();
			$wordpress['WordPress version']      = get_bloginfo( 'version' );
			$wordpress['ABSPATH']                = ABSPATH;
			$wordpress['Memory Limit']           = WP_MEMORY_LIMIT;
			$wordpress['Multisite']              = is_multisite();
			$wordpress['Permalink Structure']    = ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' );
			$wordpress['Registered Post Status'] = implode( ', ', get_post_stati() );
			$wordpress['Show On Front']          = get_option( 'show_on_front' );
			// Only show page specs if frontpage is set to 'page', from EDD, thanks!
			if ( get_option( 'show_on_front' ) == 'page' ) {
				$front_page_id               = get_option( 'page_on_front' );
				$blog_page_id                = get_option( 'page_for_posts' );
				$wordpress['Page On Front']  = ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' );
				$wordpress['Page For Posts'] = ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' );
			}
			$wordpress['Server Timezone'] = date_default_timezone_get();
			$wordpress['WP Date Format']  = get_option( 'date_format' );
			$wordpress['WP GMT Offset']   = get_option( 'gmt_offset' ) ? ' ' . get_option( 'gmt_offset' ) : esc_html__( 'Unknown or not set', 'plugin-engine' );
			$wordpress['WP Time Format']  = get_option( 'time_format' );
			$wordpress['WP Timezone']     = get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : esc_html__( 'Unknown or not set', 'plugin-engine' );
			$wordpress['Week Starts On']  = get_option( 'start_of_week' );
			$wordpress['WP_DEBUG']        = ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' );


			/**
			 * Filter the WordPress Settings for System Information
			 *
			 * @param $site array an array of WordPress settings
			 */
			$wordpress = apply_filters( 'pngx-filter-system-info-wordpress-infomation', $wordpress );

			return $wordpress;

		}

		/**
		 * Get basic server, database and php information
		 *
		 * @return array
		 */
		public function get_site_server_info() {
			global $wpdb;

			//Server Information
			$server = array();

			// OS
			$os                         = $this->get_os();
			$server['Operating System'] = $os['name'];
			$server['SAPI']             = php_sapi_name();
			$server['Server User']      = $this->get_user();
			$server['Server Software']  = $_SERVER['SERVER_SOFTWARE'];

			//MYSQL
			$server['MySQL Version']      = $wpdb->db_version();
			$server['MySQL Install Path'] = $this->guess_path( 'mysql' );

			//PHP Info
			$server['PHP version'] = phpversion();

			if ( function_exists( 'exec' ) ) {
				$exec = 'Enabled';
			} else {
				$exec = 'Disabled';
			}
			$server['Exec Enabled'] = $exec;
			$php_vars               = array(
				'display_errors',
				'log_errors',
				'max_execution_time',
				'max_input_vars',
				'memory_limit',
				'post_max_size',
				'safe_mode',
				'upload_max_filesize',
			);
			foreach ( $php_vars as $php_var ) {
				if ( isset( $wpdb->qm_php_vars ) && isset( $wpdb->qm_php_vars[ $php_var ] ) ) {
					$val = $wpdb->qm_php_vars[ $php_var ];
				} else {
					$val = ini_get( $php_var );
				}
				$server[ $php_var ] = $val;
			}

			/**
			 * Filter the Server Settings for Server Information
			 *
			 * @param $site array an array of Server settings
			 */
			$server = apply_filters( 'pngx-filter-system-info-server-infomation', $server );

			return $server;

		}

		/**
		 * Get Basic Infomation about Current User
		 *
		 * @return array
		 */
		public function get_user_info() {

			if ( ! class_exists( 'Pngx_Browser' ) ) {
				require_once Pngx__Main::instance()->vendor_path . 'browser/browser.php';
			}

			$browser = new Pngx_Browser();

			$user                           = wp_get_current_user();
			$user_info                      = array();
			$user_info['Name']              = $user->display_name;
			$user_info['Email']             = $user->user_email;
			$user_info['Browser Name']      = $browser->_browser_name;
			$user_info['Browser Version']   = $browser->_version;
			$user_info['User Agent String'] = $browser->_agent;
			$user_info['Platform']          = $browser->_platform;

			/**
			 * Filter the User Information for System Information
			 *
			 * @param $site array an array of User Information
			 */
			$user_info = apply_filters( 'pngx-filter-system-info-user-infomation', $user_info );

			return $user_info;

		}

		/**
		 * Get Infomation about the Plugin Engine
		 *
		 * @return array
		 */
		public function get_pngx_info() {

			$pngx                                  = array();
			$pngx['Plugin Engine Library Dir']     = $GLOBALS['plugin-engine-info']['dir'];
			$pngx['Plugin Engine Library Version'] = $GLOBALS['plugin-engine-info']['version'];
			$pngx['Plugin Engine Permalink Flush'] = get_option( 'pngx_permalink_flush' );

			/**
			 * Filter the Plugin Engine Settings for System Information
			 *
			 * @param $site array an array of Plugin Engine Settings
			 */
			$pngx = apply_filters( 'pngx-filter-system-info-pngx-settings', $pngx );

			return $pngx;

		}


		/**
		 * Add License Keys to System Info
		 *
		 * @return array|mixed
		 */
		public function get_key() {

			$keys = apply_filters( 'pngx-system-info-license-keys', array() );
			//Obfuscate the License Keys for Security
			if ( is_array( $keys ) && ! empty( $keys ) ) {
				$secure_keys = array();
				foreach ( $keys as $plugin => $license ) {

					if ( isset( $license['key'] ) ) {
						$license['key'] = preg_replace( '/^(.{4}).*(.{4})$/', '$1' . str_repeat( '#', 32 ) . '$2', $license['key'] );
					}
					if ( is_array( $license ) ) {
						$secure_keys[ $plugin ] = implode( ', ', $license );
					}
				}

				$keys = $secure_keys;
			}

			/**
			 * Filter the License Keys for System Information
			 *
			 * @param $site array an array of License Keys
			 */
			$keys = apply_filters( 'pngx-filter-system-info-license-keys', $keys );

			return $keys;

		}


		/**
		 * Get the Plugin Options added to filters
		 *
		 * @return array
		 */
		public function get_plugin_settings() {

			/**
			 * Add saved options to System Info for plugin engine using plugins
			 *
			 *
			 * @param array array() an array of fields to display in option tabs.
			 *
			 */
			$options = apply_filters( 'pngx-system-info-options', array() );

			/**
			 * Filter the options fields for System Info
			 *
			 *
			 * @param array array() an array of fields to display in option tabs.
			 *
			 */
			$fields = apply_filters( 'pngx-option-fields', array() );

			$settings = array();

			//Setup Settings to a human readable titles
			if ( is_array( $options ) && is_array( $fields ) ) {

				foreach ( $options as $k => $v ) {

					if ( is_array( $v ) ) {

						foreach ( $v as $key => $value ) {

							if ( isset( $fields[ $key ]['title'] ) ) {
								$settings[ $fields[ $key ]['title'] ] = esc_textarea( $value );
							}

						}

					}
				}
			}


			ksort( $settings );

			/**
			 * Filter the Plugin Settings for System Information
			 *
			 * @param $site array an array of Plugin Settings
			 */
			$settings = apply_filters( 'pngx-filter-system-info-plugin-settings', $settings );

			return $settings;

		}


		/**
		 * Get List of Active or Inactive Plugins
		 *
		 * @param bool $inactive
		 *
		 * @return array
		 */
		public function get_plugins( $inactive = false ) {

			// Get plugins that have an update
			$updates        = get_plugin_updates();
			$all_plugins    = get_plugins();
			$active_plugins = get_option( 'active_plugins', array() );
			$plugins        = array();

			foreach ( $all_plugins as $plugin_path => $p ) {

				if ( $inactive ) {
					if ( in_array( $plugin_path, $active_plugins ) ) {
						continue;
					}
				} else {
					if ( ! in_array( $plugin_path, $active_plugins ) ) {
						continue;
					}
				}

				$plugin = $p['Name'];
				if ( ! empty( $p['Version'] ) ) {
					$plugin .= sprintf( ' version %s', $p['Version'] );
				}
				if ( ! empty( $p['Author'] ) ) {
					$plugin .= sprintf( ' by %s', $p['Author'] );
				}
				if ( ! empty( $p['AuthorURI'] ) ) {
					$plugin .= sprintf( ' (%s)', $p['AuthorURI'] );
				}

				$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';

				$plugins[] = $plugin . $update;

			}

			/**
			 * Filter the Active and In Active Plugins for System Information
			 *
			 * @param $site array an array of Active and In Active Plugins
			 */
			$plugins = apply_filters( 'pngx-filter-system-info-active-inactive-plugins', $plugins );

			return $plugins;

		}

		/**
		 * Get List of Network Active Plugins
		 *
		 * @return array
		 */
		public function get_network_plugins() {

			$network_plugins = array();
			if ( is_multisite() && function_exists( 'get_plugin_data' ) ) {
				$plugins_raw = wp_get_active_network_plugins();
				foreach ( $plugins_raw as $k => $v ) {
					$plugin_details = get_plugin_data( $v );
					$plugin         = $plugin_details['Name'];
					if ( ! empty( $plugin_details['Version'] ) ) {
						$plugin .= sprintf( ' version %s', $plugin_details['Version'] );
					}
					if ( ! empty( $plugin_details['Author'] ) ) {
						$plugin .= sprintf( ' by %s', $plugin_details['Author'] );
					}
					if ( ! empty( $plugin_details['AuthorURI'] ) ) {
						$plugin .= sprintf( '(%s)', $plugin_details['AuthorURI'] );
					}
					$network_plugins[] = $plugin;
				}
			}


			/**
			 * Filter the Network Plugins for System Information
			 *
			 * @param $site array an array of Newtork Plugins
			 */
			$network_plugins = apply_filters( 'pngx-filter-system-info-network-plugins', $network_plugins );

			return $network_plugins;

		}


		/**
		 * Get List of Must Use Plugins
		 *
		 * @return array
		 */
		public function get_mu_plugins() {

			$mu_plugins = array();
			if ( function_exists( 'get_mu_plugins' ) ) {
				$mu_plugins_raw = get_mu_plugins();
				foreach ( $mu_plugins_raw as $k => $v ) {
					$plugin = $v['Name'];
					if ( ! empty( $v['Version'] ) ) {
						$plugin .= sprintf( ' version %s', $v['Version'] );
					}
					if ( ! empty( $v['Author'] ) ) {
						$plugin .= sprintf( ' by %s', $v['Author'] );
					}
					if ( ! empty( $v['AuthorURI'] ) ) {
						$plugin .= sprintf( '(%s)', $v['AuthorURI'] );
					}
					$mu_plugins[] = $plugin;
				}
			}


			/**
			 * Filter the Must Use Plugins for System Information
			 *
			 * @param $site array an array of Must Use Plugins
			 */
			$mu_plugins = apply_filters( 'pngx-filter-system-info-mu-plugins', $mu_plugins );

			return $mu_plugins;
		}

		/**
		 * Determines the current operating system.
		 * Coding from WordPress Plugin revisr/classes/class-revisr-compatibility.php
		 *
		 * @access public
		 * @return array
		 */
		public function get_os() {
			$os         = array();
			$uname      = php_uname( 's' );
			$os['code'] = strtoupper( substr( $uname, 0, 3 ) );
			$os['name'] = $uname;

			return $os;
		}

		/**
		 * Gets the user running this PHP process.
		 * Coding from WordPress Plugin revisr/classes/class-revisr-compatibility.php
		 *
		 * @access public
		 * @return string
		 */
		public function get_user() {
			if ( function_exists( 'exec' ) ) {
				return exec( 'whoami' );
			}

			return __( 'Unknown', 'plugin-engine' );
		}

		/**
		 * Tries to guess the install path to the provided program.
		 * Coding from WordPress Plugin revisr/classes/class-revisr-compatibility.php
		 *
		 * @access public
		 *
		 * @param  string $program The program to check for.
		 *
		 * @return string
		 */
		public function guess_path( $program ) {
			$os      = $this->get_os();
			$program = $this->escapeshellarg( $program );

			if ( $os['code'] !== 'WIN' ) {
				$path = exec( "which $program" );
			} else {
				$path = exec( "where $program" );
			}

			if ( $path ) {
				return $path;
			} else {
				return __( 'Not Found', 'plugin-engine' );
			}
		}

		/**
		 * Escapes a shell arguement.
		 * Coding from WordPress Plugin revisr/classes/class-revisr-admin.php
		 *
		 * @access public
		 *
		 * @param  string $string The string to escape.
		 *
		 * @return string $string The escaped string.
		 */
		public function escapeshellarg( $string ) {
			$os = $this->get_os();
			if ( 'WIN' !== $os['code'] ) {
				return escapeshellarg( $string );
			} else {
				// Windows-friendly workaround.
				return '"' . str_replace( "'", "'\\''", $string ) . '"';
			}
		}

		/****************** SINGLETON GUTS ******************/

		/**
		 * Enforce Singleton Pattern
		 */
		private static $instance;


		public static function getInstance() {
			if ( null == self::$instance ) {
				$instance       = new self;
				self::$instance = $instance;
			}

			return self::$instance;
		}
	}


}
