<?php
/**
 *
 */

if ( ! defined( 'ABSPATH' ) ) {		// Exit if accessed directly.
	exit;
}

/**
 * Optimisationio_CacheEnablerDisk
 *
 * @since 1.0.0
 */
class Optimisationio_CacheEnablerDisk {

	/**
	 * Cached filename settings
	 *
	 * @since  	1.0.7
	 *
	 * @var    string
	 */
	const FILE_HTML = 'index.html';
	const FILE_GZIP = 'index.html.gz';
	const FILE_WEBP_HTML = 'index-webp.html';
	const FILE_WEBP_GZIP = 'index-webp.html.gz';

	/**
	 * Permalink check
	 *
	 * @since   1.0.0
	 * @change  1.0.0
	 *
	 * @return  boolean  true if installed
	 */
	public static function is_permalink() {
		return get_option( 'permalink_structure' );
	}

	/**
	 * Store asset
	 *
	 * @since   1.0.0
	 * @change  1.0.0
	 *
	 * @param   string $data   content of the asset.
	 */
	public static function store_asset( $data ) {
		// Check if empty.
		if ( empty( $data ) ) { wp_die( 'Asset is empty.' ); }
		// Save asset.
		self::_create_files( $data );
	}

	/**
	 * Check asset
	 *
	 * @since   1.0.0
	 *
	 * @return  boolean  true if asset exists
	 */
	public static function check_asset() {
		return is_readable( self::_file_html() );
	}

	/**
	 * Check expiry
	 *
	 * @since   1.0.1
	 * @change  1.0.1
	 *
	 * @return  boolean  true if asset expired
	 */
	public static function check_expiry() {

		// Cache enabler options.
		$options = Optimisationio_CacheEnabler::$options;

		// Check if expires is active.
		if ( 0 === $options['cache_expires'] ) {
			return false;
		}

		$now = time();
		$expires_seconds = 3600 * $options['cache_expires'];

		// Check if asset has expired.
		if ( ( filemtime( self::_file_html() ) + $expires_seconds ) <= $now ) {
			return true;
		}

		return false;
	}

	/**
	 * Delete asset
	 *
	 * @since   1.0.0
	 *
	 * @param   string $url   url of cached asset.
	 */
	public static function delete_asset( $url ) {
		// Check if url empty.
		if ( empty( $url ) ) { wp_die( 'URL is empty.' ); }
		// Delete.
		self::_clear_dir( self::_file_path( $url ) );
	}

	/**
	 * Clear cache
	 *
	 * @since   1.0.0
	 */
	public static function clear_cache() {
		self::_clear_dir( OPTIMISATIONIO_CACHE_DIR );
	}

	/**
	 * Clear home cache
	 *
	 * @since   1.0.7
	 * @change  1.0.9
	 */
	public static function clear_home() {
		$path = sprintf(
			'%s%s%s%s',
			OPTIMISATIONIO_CACHE_DIR,
			DIRECTORY_SEPARATOR,
			preg_replace( '#^https?://#', '', get_option( 'siteurl' ) ),
			DIRECTORY_SEPARATOR
		);
		@unlink( $path . self::FILE_HTML );
		@unlink( $path . self::FILE_GZIP );
		@unlink( $path . self::FILE_WEBP_HTML );
		@unlink( $path . self::FILE_WEBP_GZIP );
	}

	/**
	 * Get asset
	 *
	 * @since   1.0.0
	 * @change  1.0.9
	 */
	public static function get_asset() {

		$server_req = $_SERVER; 	// Input var okay.

		// Set cache handler header.
		header( 'x-cache-handler: php' );

		// Get if-modified request headers.
		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = apache_request_headers();
			$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
			$http_accept = ( isset( $headers['Accept'] ) ) ? $headers['Accept'] : '';
			$http_accept_encoding = ( isset( $headers['Accept-Encoding'] ) ) ? $headers['Accept-Encoding'] : '';
		} else {
			$http_if_modified_since = ( isset( $server_req['HTTP_IF_MODIFIED_SINCE'] ) ) ? $server_req['HTTP_IF_MODIFIED_SINCE'] : '';
			$http_accept = ( isset( $server_req['HTTP_ACCEPT'] ) ) ? $server_req['HTTP_ACCEPT'] : '';
			$http_accept_encoding = ( isset( $server_req['HTTP_ACCEPT_ENCODING'] ) ) ? $server_req['HTTP_ACCEPT_ENCODING'] : '';
		}

		// Check modified since with cached file and return 304 if no difference.
		if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === filemtime( self::_file_html() ) ) ) {
			header( $server_req['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
			exit;
		}

		// Check webp and deliver gzip webp file if support.
		if ( $http_accept && ( false !== strpos( $http_accept, 'webp' ) ) ) {
			if ( is_readable( self::_file_webp_gzip() ) ) {
				header( 'Content-Encoding: gzip' );
				readfile( self::_file_webp_gzip() );
				exit;
			} elseif ( is_readable( self::_file_webp_html() ) ) {
				readfile( self::_file_webp_html() );
				exit;
			}
		}

		// Check encoding and deliver gzip file if support.
		if ( $http_accept_encoding && ( false !== strpos( $http_accept_encoding, 'gzip' ) ) && is_readable( self::_file_gzip() ) ) {
			header( 'Content-Encoding: gzip' );
			readfile( self::_file_gzip() );
			exit;
		}

		// Deliver cached file (default).
		readfile( self::_file_html() );
		exit;
	}

	/**
	 * Create signature
	 *
	 * @since   1.0.0
	 *
	 * @return  string  signature
	 */
	public static function _cache_signatur() {
		return sprintf( "\n\n<!-- %s @ %s", 'Cache for Wordpress Performance', date_i18n( 'd.m.Y H:i:s', current_time( 'timestamp' ) ) );
	}

	/**
	 * Create files
	 *
	 * @since   1.0.0
	 * @change  1.1.1
	 *
	 * @param   string $data  html content.
	 */
	public static function _create_files( $data ) {

		// Create folder.
		if ( ! wp_mkdir_p( self::_file_path() ) ) {
			wp_die( 'Unable to create directory.' );
		}

		// Get base signature.
		$cache_signature = self::_cache_signatur();

		// Cache enabler options.
		$options = Optimisationio_CacheEnabler::$options;

		// Create files.
		self::_create_file( self::_file_html(), $data . $cache_signature . ' (html) -->' );

		// Create pre-compressed file.
		if ( $options['cache_compress'] ) {
			self::_create_file( self::_file_gzip(), gzencode( $data . $cache_signature . ' (html gzip) -->', 9 ) );
		}

		// Create webp supported files.
		if ( $options['cache_webp'] ) {
			// Magic regex rule.
			$regex_rule = '#(?<=(?:(ref|src|set)=[\"\']))(?:http[s]?[^\"\']+)(\.png|\.jp[e]?g)(?:[^\"\']+)?(?=[\"\')])#';

			// Call the webp converter callback.
			$converted_data = preg_replace_callback( $regex_rule,'self::_convert_webp',$data );

			self::_create_file( self::_file_webp_html(), $converted_data . $cache_signature . ' (webp) -->' );

			// Create pre-compressed file.
			if ( $options['cache_compress'] ) {
				self::_create_file( self::_file_webp_gzip(), gzencode( $converted_data . $cache_signature . ' (webp gzip) -->', 9 ) );
			}
		}
	}

	/**
	 * Create file
	 *
	 * @since   1.0.0
	 *
	 * @param   string $file  file path.
	 * @param   string $data  content of the html.
	 */
	public static function _create_file( $file, $data ) {

		// Open file handler.
		if ( ! $handle = @fopen( $file, 'wb' ) ) {
			wp_die( 'Can not write to file.' );
		}

		// Write.
		@fwrite( $handle, $data );
		fclose( $handle );
		clearstatcache();

		// Set permissions.
		$stat = @stat( dirname( $file ) );
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;
		@chmod( $file, $perms );
		clearstatcache();
	}

	/**
	 * Clear directory
	 *
	 * @since   1.0.0
	 *
	 * @param   string $dir  directory
	 */
	public static function _clear_dir( $dir ) {

		// Remove slashes.
		$dir = untrailingslashit( $dir );

		// Check if dir.
		if ( ! is_dir( $dir ) ) {
			return;
		}

		// Get dir data.
		$objects = array_diff(
			scandir( $dir ),
			array( '..', '.' )
		);

		if ( empty( $objects ) ) {
			return;
		}

		foreach ( $objects as $object ) {
			// Full path.
			$object = $dir . DIRECTORY_SEPARATOR . $object;

			// Check if directory.
			if ( is_dir( $object ) ) {
				self::_clear_dir( $object );
			} else {
				unlink( $object );
			}
		}

		// Delete.
		@rmdir( $dir );

		// Clears file status cache.
		clearstatcache();
	}

	/**
	 * Get cache size
	 *
	 * @since   1.0.0
	 * @change  1.0.0
	 *
	 * @param   string $dir   folder path.
	 * @return  mixed   $size  size in bytes
	 */
	public static function cache_size( $dir = '.' ) {

		// Check if not dir.
		if ( ! is_dir( $dir ) ) {
			return;
		}

		// Get dir data.
		$objects = array_diff(
			scandir( $dir ),
			array( '..', '.' )
		);

		if ( empty( $objects ) ) {
			return;
		}

		$size = 0;

		foreach ( $objects as $object ) {
			// Full path.
			$object = $dir . DIRECTORY_SEPARATOR . $object;

			// Check if dir.
			if ( is_dir( $object ) ) {
				$size += self::cache_size( $object );
			} else {
				$size += filesize( $object );
			}
		}

		return $size;
	}

	/**
	 * Cache path
	 *
	 * @since   1.0.0
	 * @change  1.1.0
	 *
	 * @param   string $path  uri or permlink.
	 * @return  string  $diff  path to cached asset
	 */
	public static function _file_path( $path = null ) {
		$server_req = $_SERVER; 	// Input var okay.
		$add = ! empty( $server_req['HTTPS'] ) && 'off' !== $server_req['HTTPS'] ? 'https://' : 'http://';
		$path = sprintf(
			'%s%s%s%s',
			OPTIMISATIONIO_CACHE_DIR,
			DIRECTORY_SEPARATOR,
			wp_parse_url( $add . strtolower( $server_req['HTTP_HOST'] ), PHP_URL_HOST ),
			wp_parse_url( ( $path ? $path : $server_req['REQUEST_URI'] ), PHP_URL_PATH )
		);
		if ( is_file( $path ) > 0 ) {
			wp_die( 'Path is not valid.' );
		}
		return trailingslashit( $path );
	}

	/**
	 * Get file path
	 *
	 * @since   1.0.0
	 * @change  1.0.7
	 *
	 * @return  string  path to the html file
	 */
	public static function _file_html() {
		return self::_file_path() . self::FILE_HTML;
	}

	/**
	 * Get gzip file path
	 *
	 * @since   1.0.1
	 * @change  1.0.7
	 *
	 * @return  string  path to the gzipped html file
	 */
	public static function _file_gzip() {
		return self::_file_path() . self::FILE_GZIP;
	}

	/**
	 * Get webp file path
	 *
	 * @since   1.0.7
	 *
	 * @return  string  path to the webp html file
	 */
	public static function _file_webp_html() {
		return self::_file_path() . self::FILE_WEBP_HTML;
	}

	/**
	 * Get gzip webp file path
	 *
	 * @since   1.0.1
	 * @change  1.0.7
	 *
	 * @return  string  path to the webp gzipped html file
	 */
	public static function _file_webp_gzip() {
		return self::_file_path() . self::FILE_WEBP_GZIP;
	}

	/**
	 * Convert to webp
	 *
	 * @since   1.0.1
	 * @change  1.1.1
	 *
	 * @param   string $asset   [[Description]].
	 * @return  string  converted HTML file
	 */
	public static function _convert_webp( $asset ) {
		if ( 'src' === $asset[1] ) {
			return self::_convert_webp_src( $asset[0] );
		} elseif ( 'ref' === $asset[1] ) {
			return self::_convert_webp_src( $asset[0] );
		} elseif ( 'set' === $asset[1] ) {
			return self::_convert_webp_srcset( $asset[0] );
		}
		return $asset[0];
	}
	
	/**
	 * Convert src to webp source
	 *
	 * @since   1.0.1
	 * @change  1.1.0
	 *
	 * @param   string $src   [[Description]].
	 * @return  string  Converted src webp source.
	 */
	public static function _convert_webp_src( $src ) {
		$upload_dir = wp_upload_dir();
		$src_url = wp_parse_url( $upload_dir['baseurl'] );
		$upload_path = $src_url['path'];

		if ( false !== strpos( $src, $upload_path ) ) {

			$src_webp = str_replace( '.jpg', '.webp', $src );
			$src_webp = str_replace( '.jpeg', '.webp', $src_webp );
			$src_webp = str_replace( '.png', '.webp', $src_webp );

			$parts = explode( $upload_path, $src_webp );
			$relative_path = $parts[1];

			// Check if relative path is not empty and file exists.
			if ( ! empty( $relative_path ) && file_exists( $upload_dir['basedir'] . $relative_path ) ) {
				return $src_webp;
			} else {

				// Try appended webp extension.
				$src_webp_appended = $src . '.webp';
				$parts_appended = explode( $upload_path, $src_webp_appended );
				$relative_path_appended = $parts_appended[1];

				// Check if relative path is not empty and file exists.
				if ( ! empty( $relative_path_appended ) && file_exists( $upload_dir['basedir'] . $relative_path_appended ) ) {
					return $src_webp_appended;
				}
			}
		}

		return $src;
	}

	/**
	 * Convert srcset to webp source
	 *
	 * @since   1.0.8
	 * @change  1.1.0
	 *
	 * @param   string $srcset   [[Description]].
	 * @return  string  converted srcset webp source
	 */
	public static function _convert_webp_srcset( $srcset ) {

		$sizes = explode( ', ', $srcset );
		$upload_dir = wp_upload_dir();
		$src_url = wp_parse_url( $upload_dir['baseurl'] );
		$upload_path = $src_url['path'];
		$count_size = count( $size );

		for ( $i = 0; $i < $count_size; $i++ ) {

			if ( false !== strpos( $sizes[ $i ], $upload_path ) ) {

				$src_webp = str_replace( '.jpg', '.webp', $sizes[ $i ] );
				$src_webp = str_replace( '.jpeg', '.webp', $src_webp );
				$src_webp = str_replace( '.png', '.webp', $src_webp );

				$size_parts = explode( ' ', $src_webp );
				$parts = explode( $upload_path, $size_parts[0] );
				$relative_path = $parts[1];

				// Check if relative path is not empty and file exists.
				if ( ! empty( $relative_path ) && file_exists( $upload_dir['basedir'] . $relative_path ) ) {
					$sizes[ $i ] = $src_webp;
				} else {

					// Try appended webp extension.
					$size_parts_appended = explode( ' ', $sizes[ $i ] );
					$src_webp_appended = $size_parts_appended[0] . '.webp';
					$parts_appended = explode( $upload_path, $src_webp_appended );
					$relative_path_appended = $parts_appended[1];
					$src_webp_appended = $src_webp_appended . ' ' . $size_parts_appended[1];

					// Check if relative path is not empty and file exists.
					if ( ! empty( $relative_path_appended ) && file_exists( $upload_dir['basedir'] . $relative_path_appended ) ) {
						$sizes[ $i ] = $src_webp_appended;
					}
				}
			}
		}

		$srcset = implode( ', ', $sizes );

		return $srcset;
	}
}
