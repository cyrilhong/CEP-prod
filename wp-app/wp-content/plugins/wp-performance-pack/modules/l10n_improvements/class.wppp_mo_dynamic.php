<?php
/**
 * WPPP_MO_Dynamic
 *
 * Class for dynamic loading and parsing of MO files
 * WPPP_MO_Dynamic only loads needed strings and uses hash tables, if present in MO file
 * additional caching can further improve performance
 *
 * Author: Bjoern Ahrens <bjoern@ahrens.net>
 * Author URI: http://www.bjoernahrens.de
 * Version: 2.0.5
 */


/**
 * Class holds information about a single MO file
 */
class WPPP_MO_Item {
	var $mofile = '';
	var	$fhandle = NULL;

	var $total = 0;
	var $originals = array();
	var $originals_table;
	var $translations_table;
	var $last_access;

	var $hash_table;
	var $hash_length = 0;

	function clear_reader () {
		if ( $this->fhandle !== NULL ) {
			fclose( $this->fhandle );
			$this->fhandle = NULL;
		}
	}
}

/**
 * Class for working with MO files
 * Translation entries are created dynamically.
 * Due to this export and save functions are not implemented.
 */
class WPPP_MO_dynamic extends Gettext_Translations {
	private $caching = false;
	private $modified = false;
	private $cachegroup;
	private $is_overloaded;

	protected $domain = '';
	protected $_nplurals = 2;
	protected $MOs = array();

	protected $translations = NULL;
	protected $base_translations = NULL; 

	function __construct( $domain, $caching = false, $cachegroup = '' ) {
		$this->domain = $domain;
		$this->caching = $caching;
		$this->is_overloaded = ( ( ini_get( "mbstring.func_overload" ) & 2 ) != 0 ) && function_exists( 'mb_substr' );
		if ( $caching ) {
			add_action ( 'shutdown', array( $this, 'save_to_cache' ) );
			add_action ( 'admin_init', array( $this, 'save_base_translations' ), 100 );
		}
		// Reader has to be destroyed befor any upgrades or else upgrade might fail, if a
		// reader is loaded (cannot delete old plugin/theme/etc. because a language file
		// is still opened).
		add_filter('upgrader_pre_install', array($this, 'clear_reader_before_upgrade'), 10, 2);
	}

	function get_byteorder( &$moitem ) {
		$bytes = fread( $moitem->fhandle, 4 );
		if ( 4 != $this->strlen( $bytes ) )
			return false;
		$magic = unpack( 'V', $bytes );
		$magic = reset( $magic );

		// The magic is 0x950412de
		// bug in PHP 5.0.2, see https://savannah.nongnu.org/bugs/?func=detailitem&item_id=10565
		$magic_little = (int) - 1794895138;
		$magic_little_64 = (int) 2500072158;
		// 0xde120495
		$magic_big = ((int) - 569244523) & 0xFFFFFFFF;
		if ($magic_little == $magic || $magic_little_64 == $magic) {
			return 'little';
		} else if ($magic_big == $magic) {
			return 'big';
		} else {
			return false;
		}
	}

	function unhook_and_close () {
		remove_action ( 'shutdown', array( $this, 'save_to_cache' ) );
		remove_action ( 'admin_init', array( $this, 'save_base_translations' ), 100 );
		foreach ( $this->MOs as $moitem ) {
			$moitem->clear_reader();
		}
		$this->MOs = array();
	}

	function __destruct() {
		foreach ( $this->MOs as $moitem ) {
			$moitem->clear_reader();
		}
	}

	function clear_reader_before_upgrade($return, $plugin) {
		// stripped down copy of class-wp-upgrader.php Plugin_Upgrader::deactivate_plugin_before_upgrade
		if ( is_wp_error($return) ) //Bypass.
			return $return;

		foreach ( $this->MOs as $moitem ) {
			$moitem->clear_reader();
		}
	}

	function get_current_url () {
		$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if ( ( $len = strlen( $_SERVER['QUERY_STRING'] ) ) > 0 ) {
			$current_url = substr ( $current_url, 0, strlen($current_url) - $len - 1 );
		}
		if ( substr( $current_url, -10 ) === '/wp-admin/' ) {
			$current_url .= 'index.php';
		}
		if ( isset( $_GET['page'] ) ) {
			$current_url .= '?page=' . $_GET['page'];
		}
		return $current_url;
	}

	function import_from_file( $filename ) {
		$moitem = new WPPP_MO_Item();
		$moitem->mofile = $filename;
		$this->MOs[] = $moitem;
		
		// because only a reference to the MO file is created, at this point there is no information if $filename is a valid MO file, so the return value is always true
		return true;
	}

	function save_base_translations () {
		if ( is_admin() && $this->translations !== NULL && $this->base_translations === NULL ) {
			$this->base_translations = $this->translations;
			$this->translations = array();
		}
	}

	private function cache_get ( $key, $cache_time ) {
		$t = wp_cache_get( $key, $this->cachegroup );
		if ( $t !== false && isset( $t['data'] ) ) {
			// check soft expire
			if ( $t['softexpire'] < time() ) {
				// update cache with new soft expire time
				$t['softexpire'] = time() + ( $cache_time - ( 5 * MINUTE_IN_SECONDS ) );
				wp_cache_replace( $key, $t, $this->cachegroup, $cache_time );
			}
			return json_decode( gzuncompress( $t['data'] ), true );
		}
		return NULL;
	}

	private function cache_set ( $key, $cache_time, $data ) {
		$t = array();
		$t['softexpire'] = time() + ( $cache_time - ( 5 * MINUTE_IN_SECONDS ) );
		$t['data'] = gzcompress( json_encode( $data ) );
		wp_cache_set( $key, $t, $this->cachegroup, $cache_time );
	}

	function import_domain_from_cache () {
		// build cache key from domain and request uri
		if ( $this->caching ) {
			if ( is_admin() ) {
				$this->base_translations = $this->cache_get( 'backend_' . $this->domain, HOUR_IN_SECONDS );
				$this->translations = $this->cache_get( 'backend_' . $this->domain . '_' . $this->get_current_url(), 30 * MINUTE_IN_SECONDS );
			} else {
				$this->translations = $this->cache_get( 'frontend_' . $this->domain, HOUR_IN_SECONDS );
			}
		}

		if ( $this->translations === NULL ) {
			$this->translations = array();
		}
	}

	function save_to_cache () {
		if ( $this->modified ) {
			if ( is_admin() ) {
				$this->cache_set( 'backend_' . $this->domain . '_' . $this->get_current_url(), 30 * MINUTE_IN_SECONDS, $this->translations ); // keep admin page cache for 30 minutes
				if ( count( $this->base_translations ) > 0 ) {
					$this->cache_set( 'backend_'.$this->domain, HOUR_IN_SECONDS, $this->base_translations ); // keep admin base cache for 60 minutes
				}
			} else {
				$this->cache_set( 'frontend_'.$this->domain, HOUR_IN_SECONDS, $this->translations ); // keep front end cache for 60 minutes
			}
		}
	}

	private function import_fail ( &$moitem ) {
		fclose( $moitem->fhandle );
		$moitem->fhandle = false;
		unset( $moitem->originals );
		unset( $moitem->originals_table );
		unset( $moitem->translations_table );
		unset( $moitem->hash_table );
		
		return false;
	}

	private function strlen( $string ) {
		if ( $this->is_overloaded ) {
			return mb_strlen( $string, 'ascii' );
		} else {
			return strlen( $string );
		}
	}

	function import_from_reader( &$moitem ) {
		if ( $moitem->fhandle !== NULL) {
			return ( $moitem->fhandle !== false );
		}

		$file_size = filesize( $moitem->mofile );
		$moitem->fhandle = fopen( $moitem->mofile, 'rb' );

		$endian_string = $this->get_byteorder( $moitem );
		if ( false === $endian_string ) {
			return $this->import_fail( $moitem );
		}
		$endian = ( 'big' == $endian_string ) ? 'N' : 'V';

		$header = fread( $moitem->fhandle, 24 );
		if ( $this->strlen( $header ) != 24 ) {
			return $this->import_fail( $moitem );
		}

		// parse header
		$header = unpack( "{$endian}revision/{$endian}total/{$endian}originals_lenghts_addr/{$endian}translations_lenghts_addr/{$endian}hash_length/{$endian}hash_addr", $header );
		if ( !is_array( $header ) ) {
			return $this->import_fail( $moitem );
		}
		extract( $header );

		// support revision 0 of MO format specs, only
		if ( $revision !== 0 ) {
			return $this->import_fail( $moitem );
		}

		$moitem->total = $total;

		// read hashtable
		$moitem->hash_length = $hash_length;
		if ( $hash_length > 0 ) {
			fseek( $moitem->fhandle, $hash_addr );
			$str = fread( $moitem->fhandle, $hash_length * 4 );
			if ( $this->strlen( $str ) != $hash_length * 4 ) {
				return $this->import_fail( $moitem );
			} 
			if ( class_exists ( 'SplFixedArray' ) )
				$moitem->hash_table = SplFixedArray::fromArray( unpack ( $endian.$hash_length, $str ), false );
			else
				$moitem->hash_table = array_slice( unpack ( $endian.$hash_length, $str ), 0 ); // force zero based index
		}

		// read originals' indices
		fseek( $moitem->fhandle, $originals_lenghts_addr );
		$originals_lengths_length = $translations_lenghts_addr - $originals_lenghts_addr;
		if ( $originals_lengths_length != $total * 8 ) {
			return $this->import_fail( $moitem );
		}
		$str = fread( $moitem->fhandle, $originals_lengths_length );
		if ( $this->strlen( $str ) != $originals_lengths_length ) {
			return $this->import_fail( $moitem );
		}
		if ( class_exists ( 'SplFixedArray' ) )
			$moitem->originals_table = SplFixedArray::fromArray( unpack ( $endian.($total * 2), $str ), false );
		else
			$moitem->originals_table = array_slice( unpack ( $endian.($total * 2), $str ), 0 ); // force zero based index

		// "sanity check" ( i.e. test for corrupted mo file )
		for ( $i = 0, $max = $total * 2; $i < $max; $i+=2 ) {
			if ( $moitem->originals_table[ $i + 1 ] > $file_size
				|| $moitem->originals_table[ $i + 1 ] + $moitem->originals_table[ $i ] > $file_size ) {
				return $this->import_fail( $moitem );
			}
		}

		// read translations' indices
		$translations_lenghts_length = $hash_addr - $translations_lenghts_addr;
		if ( $translations_lenghts_length != $total * 8 ) {
			return $this->import_fail( $moitem );
		}
		$str = fread( $moitem->fhandle, $translations_lenghts_length );
		if ( $this->strlen( $str ) != $translations_lenghts_length ) {
			return $this->import_fail( $moitem );
		}
		if ( class_exists ( 'SplFixedArray' ) )
			$moitem->translations_table = SplFixedArray::fromArray( unpack ( $endian.($total * 2), $str ), false );
		else
			$moitem->translations_table = array_slice( unpack ( $endian.($total * 2), $str ), 0 ); // force zero based index

		// "sanity check" ( i.e. test for corrupted mo file )
		for ( $i = 0, $max = $total * 2; $i < $max; $i+=2 ) {
			if ( $moitem->translations_table[ $i + 1 ] > $file_size
				|| $moitem->translations_table[ $i + 1 ] + $moitem->translations_table[ $i ] > $file_size ) {
				return $this->import_fail( $moitem );
			}
		}

		// read headers
		for ( $i = 0, $max = $total * 2; $i < $max; $i+=2 ) {
			// Search emtpy original. Usually should be first entry in MO file.
			$original = '';
			if ( $moitem->originals_table[$i] > 0 ) {
				fseek( $moitem->fhandle, $moitem->originals_table[$i+1] );
				$original = fread( $moitem->fhandle, $moitem->originals_table[$i] );

				$j = strpos( $original, 0 );
				if ( $j !== false )
					$original = substr( $original, 0, $i );
			}

			if ( $original === '' ) {
				$translation = '';
				if ( $moitem->translations_table[$i] > 0 ) {
					fseek( $moitem->fhandle, $moitem->translations_table[$i+1] );
					$translation = fread( $moitem->fhandle, $moitem->translations_table[$i] );
				}

				$this->set_headers( $this->make_headers( $translation ) );
			} else
				return true;
		}
		return true;
	}

	protected function search_translation ( $key ) {
		$hash_val = NULL;
		$key_len = strlen( $key );

		for ( $j = 0, $max = count( $this->MOs ); $j < $max; $j++ ) {
			$moitem = $this->MOs[$j];
			if ( $moitem->fhandle === NULL ) {
				if ( !$this->import_from_reader( $moitem ) ) {
					// Error reading MO file, so delete it from MO list to prevent subsequent access
					unset( $this->MOs[$j] );
					return false; // return or continue?
				}
			}

			if ( $moitem->hash_length > 0 ) {
				/* Use mo file hash table to search translation */

				// calculate hash value
				// hashpjw function by P.J. Weinberger from gettext hash-string.c
				// adapted to php and its quirkiness caused by missing unsigned ints and shift operators...
				if ( $hash_val === NULL) {
					$hash_val = 0;
					$chars = unpack( 'C*', $key ); // faster than accessing every single char by ord(char)
					foreach ( $chars as $char ) {
						$hash_val = ( $hash_val << 4 ) + $char;
						if( 0 !== ( $g = $hash_val & 0xF0000000 ) ){
							if ( $g < 0 )
								$hash_val ^= ( ( ($g & 0x7FFFFFFF) >> 24 ) | 0x80 ); // wordaround: php operator >> is arithmetic, not logic, so shifting negative values gives unexpected results. Cut sign bit, shift right, set sign bit again.
								/* 
								workaround based on this function (adapted to actual used parameters):
								
								function shr($var,$amt) {
									$mask = 0x40000000;
									if($var < 0) {
										$var &= 0x7FFFFFFF;
										$mask = $mask >> ($amt-1);
										return ($var >> $amt) | $mask;
									}
									return $var >> $amt;
								} 
								*/
							else
								$hash_val ^= ( $g >> 24 );
							$hash_val ^= $g;
						}
					}
				}

				// calculate hash table index and increment
				if ( $hash_val >= 0 ) {
					$idx = $hash_val % $moitem->hash_length;
					$incr = 1 + ($hash_val % ($moitem->hash_length - 2));
				} else {
					$hash_val = (float) sprintf('%u', $hash_val); // workaround php not knowing unsigned int - %u outputs $hval as unsigned, then cast to float 
					$idx = fmod( $hash_val, $moitem->hash_length);
					$incr = 1 + fmod ($hash_val, ($moitem->hash_length - 2));
				}

				$orig_idx = $moitem->hash_table[$idx];
				while ( $orig_idx != 0 ) {
					$orig_idx--; // index adjustment

					$pos = $orig_idx * 2;
					if ( $orig_idx < $moitem->total // orig_idx must be in range
						 && $moitem->originals_table[$pos] >= $key_len ) { // and original length must be equal or greater as key length (original can contain plural forms)

						// read original string
						$mo_original = '';
						if ( $moitem->originals_table[ $pos ] > 0 ) {
							fseek( $moitem->fhandle, $moitem->originals_table[ $pos + 1 ] );
							$mo_original = fread( $moitem->fhandle, $moitem->originals_table[ $pos ] );
						}

						if ( $moitem->originals_table[$pos] == $key_len
							 || ord( $mo_original{$key_len} ) == 0 ) {
							// strings can only match if they have the same length, no need to inspect otherwise

							if ( false !== ( $i = strpos( $mo_original, 0 ) ) )
								$cmpval = strncmp( $key, $mo_original, $i );
							else 
								$cmpval = strcmp( $key, $mo_original );

							if ( $cmpval === 0 ) {
								// key found, read translation string
								fseek( $moitem->fhandle, $moitem->translations_table[$pos+1] );
								$translation = fread( $moitem->fhandle, $moitem->translations_table[$pos] );
								if ( $j > 0 ) {
									// Assuming frequent subsequent translations from the same file resort MOs by access time to avoid unnecessary search in the wrong files.
									$moitem->last_access=time();
									usort( $this->MOs, function ($a, $b) {return ($b->last_access - $a->last_access);} );
								}
								return $translation;
							}
						}
					}

					if ($idx >= $moitem->hash_length - $incr)
						$idx -= ($moitem->hash_length - $incr);
					else
						$idx += $incr;
					$orig_idx = $moitem->hash_table[$idx];
				}
			} else {
				/* No hash-table, do binary search for matching originals entry */
				$left = 0;
				$right = $moitem->total-1;

				while ( $left <= $right ) {
					$pivot = $left + (int) ( ( $right - $left ) / 2 );
					$pos = $pivot * 2;

					if ( isset( $moitem->originals[$pivot] ) ) {
						$mo_original = $moitem->originals[$pivot];
					} else {
						// read and "cache" original string to improve performance of subsequent searches
						if ( $moitem->originals_table[$pos] > 0 ) {
							fseek( $moitem->fhandle, $moitem->originals_table[$pos+1] );
							$mo_original = fread( $moitem->fhandle, $moitem->originals_table[$pos] );
						} else {
							$mo_original = '';
						}
						$moitem->originals[$pivot] = $mo_original;
					}

					if ( false !== ( $i = strpos( $mo_original, 0 ) ) )
						$cmpval = strncmp( $key, $mo_original, $i );
					else
						$cmpval = strcmp( $key, $mo_original );

					if ( $cmpval === 0 ) {
						// key found read translation string
						fseek( $moitem->fhandle, $moitem->translations_table[$pos+1] );
						$translation = fread( $moitem->fhandle, $moitem->translations_table[$pos] );
						if ( $j > 0 ) {
							// Assuming frequent subsequent translations from the same file resort MOs by access time to avoid unnecessary search in the wrong files.
							$moitem->last_access=time();
							usort( $this->MOs, function ($a, $b) {return ($b->last_access - $a->last_access);} );
						}
						return $translation;
					} else if ( $cmpval < 0 ) {
						$right = $pivot - 1;
					} else { // if ($cmpval>0) 
						$left = $pivot + 1;
					}
				}
			}
		}
		// key not found
		return false;
	}

	function translate ($singular, $context = NULL) {
		if ( !isset ($singular{0} ) ) return $singular;

		if ( $context == NULL ) {
			$s = $singular;
		} else {
			$s = $context . "\x04" . $singular;
		}

		if ( $this->translations === NULL ) {
			$this->import_domain_from_cache();
		}

		if ( isset( $this->translations[$s] ) ) {
			$t = $this->translations[$s];
		} elseif ( isset ($this->base_translations[$s] ) ) {
			$t = $this->base_translations[$s];
		} else {
			if ( false !== ( $t = $this->search_translation( $s ) ) ) {
				$this->translations[$s] = $t;
				$this->modified = true;
			}
		}

		if ( $t !== false ) {
			if ( false !== ( $i = strpos( $t, 0 ) ) ) {
				return substr( $t, 0, $i );
			} else {
				return $t;
			}
		} else {
			$this->translations[$s] = $singular;
			$this->modified = true;
			return $singular;
		}
	}

	function translate_plural ($singular, $plural, $count, $context = null) {
		if ( !isset( $singular{0} ) ) return $singular;

		// Get the "default" return-value
		$default = ($count == 1 ? $singular : $plural);

		if ( $context == NULL ) {
			$s = $singular;
		} else {
			$s = $context . "\x04" . $singular;
		}

		if ( $this->translations === NULL ) {
			$this->import_domain_from_cache();
		}

		if ( isset( $this->translations[$s] ) ) {
			$t = $this->translations[$s];
		} elseif ( isset ($this->base_translations[$s] ) ) {
			$t = $this->base_translations[$s];
		} else {
			if ( false !== ( $t = $this->search_translation( $s ) ) ) {
				$this->translations[$s] = $t;
				$this->modified = true;
			}
		}

		if ( $t !== false ) {
			if ( false !== ( $i = strpos( $t, 0 ) ) ) {
				if ( $count == 1 ) {
					return substr ( $t, 0, $i );
				} else {
					// only one plural form is assumed - needs improvement
					return substr( $t, $i+1 );
				}
			} else {
				return $default;
			}
		} else {
			$this->translations[$s] = $singular . "\x0" . $plural;
			$this->modified = true;
			return $default;
		}
	}

	function merge_with( &$other ) {
		if ( $other instanceof WPPP_MO_dynamic ) {
			if ( $other->translations !== NULL ) {
				foreach( $other->translations as $key => $translation ) {
					$this->translations[$key] = $translation;
				}
			}
			if ( $other->base_translations !== NULL ) {
				foreach( $other->base_translations as $key => $translation ) {
					$this->base_translations[$key] = $translation;
				}
			}

			foreach ( $other->MOs as $moitem ) {
				$i = 0;
				$c = count( $this->MOs );
				$found = false;
				while ( !$found && ( $i < $c ) ) {
					$found = $this->MOs[$i]->mofile == $moitem->mofile;
					$i++;
				}
				if ( !$found )
					$this->MOs[] = $moitem;
			}
		}
	}

	function MO_file_loaded ( $mofile ) {
		foreach ($this->MOs as $moitem) {
			if ($moitem->mofile == $mofile) {
				return true;
			}
		}
		return false;
	}
}
?>