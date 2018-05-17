<?php
/**
 *
 */

$server_req = $_SERVER;	// Input var okay.

// Check if request method is GET.
if ( ! isset( $server_req['REQUEST_METHOD'] ) || 'GET' !== $server_req['REQUEST_METHOD'] ) {	// Input var okay.
	return false;
}

$get_req = $_GET;	// Input var okay.

// Check if request with query strings.
if ( ! empty( $get_req ) && ! isset( $get_req['utm_source'], $get_req['utm_medium'], $get_req['utm_campaign'] ) ) {
	return false;
}

$cookie_data = $_COOKIE;	// Input var okay.

// Check cookie values.
if ( ! empty( $cookie_data ) ) {
	foreach ( $cookie_data as $k => $v ) {
		if ( preg_match( '/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k ) ) {
			return false;
		}
	}
}

$add = ! empty( $server_req['HTTPS'] ) && 'off' !== $server_req['HTTPS'] ? 'https://' : 'http://';

// Base path.
$path = sprintf(
	'%s%s%s%s',
	WP_CONTENT_DIR . '/cache/optimisationio',
	DIRECTORY_SEPARATOR,
	parse_url(
		$add . strtolower( $server_req['HTTP_HOST'] ),
		PHP_URL_HOST
	),
	parse_url(
		$server_req['REQUEST_URI'],
		PHP_URL_PATH
	)
);

// Add trailing slash.
$path = rtrim( $path, '/\\' ) . '/';

// Path to cached variants.
$path_html = $path . 'index.html';
$path_gzip = $path . 'index.html.gz';
$path_webp_html = $path . 'index-webp.html';
$path_webp_gzip = $path . 'index-webp.html.gz';

if ( is_readable( $path_html ) ) {

	// Set cache handler header.
	header( 'x-cache-handler: wp' );

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
	if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === filemtime( $path_html ) ) ) {
		header( $server_req['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
		exit;
	}

	// Check webp and deliver gzip webp file if support.
	if ( $http_accept && ( false !== strpos( $http_accept, 'webp' ) ) ) {
		if ( is_readable( $path_webp_gzip ) ) {
			header( 'Content-Encoding: gzip' );
			readfile( $path_webp_gzip );
			exit;
		} elseif ( is_readable( $path_webp_html ) ) {
			readfile( $path_webp_html );
			exit;
		}
	}

	// Check encoding and deliver gzip file if support.
	if ( $http_accept_encoding && ( false !== strpos( $http_accept_encoding, 'gzip' ) ) && is_readable( $path_gzip ) ) {
		header( 'Content-Encoding: gzip' );
		readfile( $path_gzip );
		exit;
	}

	// Deliver cached file (default).
	readfile( $path_html );
	exit;

} else {
	return false;
}// End if().
