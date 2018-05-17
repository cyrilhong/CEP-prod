<?php
/**
 * Base class to serve intermediate images on demand.
 *
 * @author Björn Ahrens
 * @package WP Performance Pack
 * @since 2.0
 */
class PseudoSemaphore {
	private $lockfile = '';
	private $uuid = '';
	private $queuefile = '';
	private $maxval = 5;
	private $lf = null;
	private	$slots = Array();
	private $entered = false;

	private function UUIDv4() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
						// 32 bits for "time_low"
						mt_rand(0, 0xffff), mt_rand(0, 0xffff),

						// 16 bits for "time_mid"
						mt_rand(0, 0xffff),

						// 16 bits for "time_hi_and_version",
						// four most significant bits holds version number 4
						mt_rand(0, 0x0fff) | 0x4000,

						// 16 bits, 8 bits for "clk_seq_hi_res",
						// 8 bits for "clk_seq_low",
						// two most significant bits holds zero and one for variant DCE1.1
						mt_rand(0, 0x3fff) | 0x8000,

						// 48 bits for "node"
						mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
					);
	}

	function __construct( $filename, $maxvalue ) {
		$this->uuid = $this->UUIDv4();
		$this->lockfile = sys_get_temp_dir() . '/' . $filename . '.' . getmypid() . '.lock';
		$this->queuefile = sys_get_temp_dir() . '/' . $filename . '.' . getmypid() . '.queue';
		$this->maxval = $maxvalue;
	}

	function __destruct() {
		if ( $this->entered )
			$this->leave();
	}

	private function readCount() {
		$this->lf = fopen( $this->lockfile, 'c+' );
		if ( !flock( $this->lf, LOCK_EX ) ) {
			fclose( $this->lf );
			throw new \Exception( 'Lock error!' );
		} else {
			$this->slots = fgetcsv( $this->lf );
			$count = 0;
			for ( $i = 0; $i < count( $this->slots ); $i++ ) {
				if ( !empty( $this->slots[ $i ] ) ) {
					$row = explode( ';', $this->slots[ $i ] );
					if ( time() - intval( $row[ 1 ] ) > 20 ) {
						$this->slots[ $i ] = '';
					} else
						$count++;
				}
			}
			return $count;
		}
	}

	private function setSlot() {
		if ( count( $this->slots ) < $this->maxval ) {
			$this->slots[ count( $this->slots ) ] = $this->uuid . ';' . time();
		} else {
			$i = 0;
			while ( ( $i < count( $this->slots ) ) && ( !empty( $this->slots[ $i ] ) ) )
				$i++;
			if ( $i >= $this->maxval )
				throw new \Exception( 'Slot error!' );
			$this->slots[ $i ] = $this->uuid . ';' . time();
		}
		ftruncate( $this->lf, 0 );
		rewind( $this->lf );
		fputcsv( $this->lf, $this->slots );
		fflush( $this->lf );
		flock( $this->lf, LOCK_UN );
		fclose( $this->lf );
	}

	private function clearSlot() {
		for ( $i = 0; $i < count( $this->slots ); $i++ ) {
			if ( strncmp( $this->slots[ $i ], $this->uuid, 36 ) == 0 ) {
				$this->slots[ $i ] = '';
				break;
			}
		}
		ftruncate( $this->lf, 0 );
		rewind( $this->lf );
		fputcsv( $this->lf, $this->slots );
		fflush( $this->lf );
		flock( $this->lf, LOCK_UN );
		fclose( $this->lf );
		$this->lf = null;
	}

	function closeCount() {
		flock( $this->lf, LOCK_UN );
		fclose( $this->lf );
		$this->lf = null;
	}

	private function readQueue() {
		if ( file_exists( $this->queuefile ) )  {
			$qf = fopen( $this->queuefile, 'r' );
			$queue = fgetcsv( $qf );
			fclose( $qf );
			return $queue;
		} else
			return Array();
	}

	private function writeQueue( $queue ) {
		$qf = fopen( $this->queuefile, 'c+' );
		if ( $qf !== false ) {
			ftruncate( $qf, 0 );
			if ( is_array( $queue ) && count( $queue ) > 0 ) {
				rewind( $qf );
				fputcsv( $qf, $queue );
			}
			fflush( $qf );
			fclose( $qf );
			return true;
		}
		return false;
	}

	function enter() {
		$lockcount = $this->readCount();
		if ( $lockcount < $this->maxval ) {
			$this->setSlot();
			$this->entered = true;
		} else {
			$queue = $this->readQueue();
			$queue[] = $this->uuid . ';' . time();
			$this->writeQueue( $queue );
			$this->closeCount();

			$dowait = true;
			while( $dowait ) {
				//     1000000 <- 1 second
				//      500000 <- 0,5 seconds
				//       50000 <- 0,05 seconds
				usleep( 100000 );
				$lockcount = $this->readCount();
				if ( $lockcount < $this->maxval ) {
					$queue = $this->readQueue();
					if ( is_array( $queue ) && count( $queue ) > 0 ) {
						if ( strncmp( $queue[ 0 ], $this->uuid, 36 ) == 0 ) {
							// this is next in queue
							array_shift( $queue );
							$this->writeQueue( $queue );
							$this->setSlot();
							$dowait = false;
							$this->entered = true;
						} else {
							$row = explode( ';', $queue[ 0 ] );
							if ( time() - intval( $row[ 1 ] ) > 20 ) {
								// first queue item ttl expired
								array_shift( $queue );
								$this->writeQueue( $queue );
							}
							// wait
							$this->closeCount();
						}
					} else {
						$this->writeQueue( false );
						$this->setSlot();
						$dowait = false;
						$this->entered = true;
					}
					unset( $queue );
				} else
					$this->closeCount();
			}
		}
	}

	function leave() {
		$lockcount = $this->readCount();
		$this->clearSlot();
		$this->entered = false;
	}
}

class WPPP_Serve_Image {
	protected $filename = null;
	protected $localfilename = null;
	protected $localfiletime = 0;
	protected $width = 0;
	protected $height = 0;
	protected $wppp = null;
	protected $sema = null;

	function __construct() {
		define( 'WPPP_SERVING_IMAGE', true ); // this is to prevent unnecessary actions and filters being registered by wppp
	}

	/*
	 * Check request header for modified request
	 */
	function check_cache_headers() {
		$this->get_local_filename();
		// Getting headers sent by the client.
		$headers = apache_request_headers(); 
		// Checking if the client is validating his cache and if it is current.
		if ( isset( $headers[ 'If-Modified-Since' ] ) && ( strtotime( $headers[ 'If-Modified-Since' ] ) == $this->localfiletime ) ) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $this->localfilename ) ) . ' GMT', true, 304 );
			exit();
		}
	}

	function check_wp_cache() {
		if ( $this->wppp->options['dynamic_images_cache'] && ( false !== ( $data = wp_cache_get( $this->localfilename . $this->width . 'x' . $this->height, 'wppp' ) ) ) ) {
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $this->localfiletime ) . ' GMT', true, 200 );
			header( 'Content-Length: ' . count( $data[ 'data' ] ) );
			header( 'Content-Type: ' . $data[ 'mimetype' ], true, 200 );
			echo $data[ 'data' ];
			exit;
		}
	}

	/*
	 * Exit with 404 and prevent browser caching (which shouldn't happen anyway) 
	 * so image loading is retried in case of an error
	 */
	function exit404( $message ) {
		header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );
		header( 'Cache-Control: no-cache, must-revalidate' );	// HTTP/1.1
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );		// past date
		echo $message;
		if ( $this->sema !== null ) {
			$this->sema->leave();
			$this->sema = null;
		}
		exit();
	}

	function filter_wp_image_editor ( $editors ) {
		if ( $this->wppp->options['dynamic_images_exif_thumbs']  ) {
			include( sprintf( "%s/class.wp-image-editor-gd-exif.php", dirname( __FILE__ ) ) );
			include( sprintf( "%s/class.wp-image-editor-imagick-exif.php", dirname( __FILE__ ) ) );
			array_unshift( $editors, 'WP_Image_Editor_Imagick_EXIF', 'WP_Image_Editor_GD_EXIF' );
		}
		return $editors;
	}

	function get_local_filename() {
		if ( $this->localfilename === null ) {
			$uploads_dir 			= wp_upload_dir();
			$temp 					= parse_url( $uploads_dir['baseurl'] );
			$upload_path 			= $temp['path'];
			$findfile 				= str_replace( $upload_path, '', $this->filename );
			$this->localfilename	= $uploads_dir['basedir'] . $findfile;
			$this->localfiletime	= filemtime( $this->localfilename );
			if ( !file_exists( $this->localfilename ) )
				$this->exit404( 'File "' . $this->localfilename . '" not found' );
		}
	}

	function init() {
		if ( !preg_match( '/(.*)-([0-9]+)x([0-9]+)?\.(jpeg|jpg|png|gif)/i', $_SERVER[ 'REQUEST_URI' ], $matches ) )
			$this->exit404( 'No file match' );

		$this->filename = urldecode( $matches[ 1 ] . '.' . $matches[ 4 ] );
		$this->width = $matches[ 2 ];
		$this->height = $matches[ 3 ];

		// search and load wp-load.php
		$folder = dirname( __FILE__ );
		while ( $folder != dirname( $folder ) ) {
			if ( file_exists( $folder . '/wp-load.php' ) ) {
				break;
			} else {
				$folder = dirname( $folder );
			}
		}
		require( $folder . '/wp-load.php' ); // will fail if while loop didn't find wp-load.php
		unset( $folder );

		add_filter( 'wp_image_editors', array ( $this, 'filter_wp_image_editor' ), 1000, 1 ); // set to very low priority, so it is hopefully called last as this overrides previously registered editors
	}

	function load_wppp() {
		global $wp_performance_pack;
		$this->wppp = $wp_performance_pack;
		$this->wppp->load_options();
		if ( $this->wppp->options[ 'dynamic_images' ] !== true )
			$this->exit404( 'WPPP dynamic images deactivated for this site' );
	}

	/*
	 * Get attachment ID for image url
	 * Source: http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
	 */
	function get_attachment_id_from_file( $attachment_filename = '' ) {
		global $wpdb;
		$attachment_id = false;
		if ( '' !== $attachment_filename ) {
			$upload_dir_paths = wp_upload_dir();
			$basepath = $upload_dir_paths[ 'basedir' ];
			if ( false !== strpos( $attachment_filename, $basepath ) ) {
				$attachment_filename = str_replace( $basepath . '/', '', $attachment_filename );
				$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_filename ) );
			}
		}
		return $attachment_id;
	}


	/*
	 * Called before actual resizing - used to load EWWW when using SHORTINIT
	 */
	function prepare_resize() {
	}

	function serve_image() {
		$this->init();
		$this->load_wppp();
		$this->check_cache_headers();
		$this->check_wp_cache();

		if ( !function_exists( '__' ) ) {
			function __( $text, $domain = 'default' ) {
				return	$text;
			}
		}

		$except = null;
		$this->sema = null;//new PseudoSemaphore( 'wppp_dynimg_lock', 2 );
		//$this->sema->enter();
		try {
			// get defined image sizes - no way to get them all here, because
			// this would require to initialize the template and all plugins
			// that's why they are stored as an option
			$image = wp_get_image_editor( $this->localfilename );
			if ( is_wp_error( $image ) )
				$this->exit404( 'Error loading image' );
			$imgsize = $image->get_size();

			// test, if the requested image size matches any of the saved sizes. WPPP only serves "known" image sizes to prevent filling up server space
			$sizes = get_option( 'wppp_dynimg_sizes' );
			$the_size = '';
			foreach ( $sizes as $size => $size_data ) {
				// always check, even if size is in meta data, as the size could have changed since it was saved to meta data
				$new_size = image_resize_dimensions( $imgsize[ 'width' ], $imgsize[ 'height' ], $size_data[ 'width' ], $size_data[ 'height' ], $size_data[ 'crop' ] );
				if ( ( abs( $new_size[ 4 ] - $this->width ) <= 1 ) && ( abs( $new_size[ 5 ] - $this->height ) <= 1 ) ) {
					// allow size to vary by one pixel to catch rounding differences in size calculation 
					$the_size = $size;
					$crop = $size_data[ 'crop' ];
					break;
				}
			}
			if ( $the_size === '' )
				$this->exit404( 'Unknown image size' );
			unset( $sizes );

			$data = null;
			$image->set_quality( $this->wppp->options[ 'dynimg_quality' ] );
			$image->resize( $this->width, $this->height, $crop );
			if ( !$this->wppp->options[ 'dynamic_images_nosave' ] ) {
				// first add the generated size to the images meta data so the intermediate 
				// image gets deleted, when the image is deleted

				$attachment_id = $this->get_attachment_id_from_file( $this->localfilename );
				if ( $attachment_id !== false ) {
					$attachment_meta = wp_get_attachment_metadata( $attachment_id );
					if ( $attachment_meta ) {
						$newfile = $image->generate_filename( $this->width . 'x' . $this->height );

						// save the image to disc and update metadata
						$size = $image->save( $newfile );
						if ( !is_wp_error( $size ) ) {
							$attachment_meta[ 'sizes' ][ $the_size ] = array(
								'file'	=> wp_basename( apply_filters( 'image_make_intermediate_size', $newfile ) ),
								'width'     => $size[ 'width' ],
								'height'    => $size[ 'height' ],
								'mime-type' => $size[ 'mime-type' ],
							);
							wp_update_attachment_metadata( $attachment_id, $attachment_meta );
						}
					}
				}
			} else {
				if ( $this->wppp->options[ 'dynamic_images_cache' ] ) {
					$data = array();
					// get image mime type - WP_Image_Editor has functions for this, but they are all protected :(
					// so use the code from get_mime_type directly
					$mime_types = wp_get_mime_types();
					$extension = strtolower( pathinfo( $this->filename, PATHINFO_EXTENSION ) );
					$extensions = array_keys( $mime_types );
					foreach( $extensions as $_extension ) {
						if ( strtolower( $_extension ) == $extension ) { //preg_match( "/{$extension}/i", $_extension ) ) {
							$data['mimetype'] = $mime_types[ $_extension ];
							break;
						}
					}
					ob_start();
					$image->stream();
					unset( $image );
					$data['data'] = ob_get_contents(); // read from buffer
					ob_end_clean();
					if ( count( $data[ 'data' ] ) <= 64 * 1024 )
						wp_cache_set( $this->localfilename . $this->width . 'x' . $this->height, $data, 'wppp', 24 * HOUR_IN_SECONDS );
				}

				// if intermediate images are not saved, explicitly set cache headers for browser caching
				header( 'Cache-Control: max-age=' . 24 * HOUR_IN_SECONDS );
				header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 7 * 24 * HOUR_IN_SECONDS ) . ' GMT' );
			}
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $this->localfiletime ) . ' GMT', true, 200 );
			if ( $data === null )
				$image->stream();
			else
				echo $data[ 'data' ];

		} catch ( Exception $e ) {
			$exept = $e;
		}
		unset( $image );
		if ( $this->sema !== null ) {
			$this->sema->leave();
			$this->sema = null;
		}
		if ( $except !== null )
			throw $except;
	}
}