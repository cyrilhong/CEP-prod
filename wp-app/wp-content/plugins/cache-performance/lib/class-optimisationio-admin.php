<?php
/**
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Exit if accessed directly.
class Optimisationio_Admin {

	private $saved_sett = null;
	private $saved_db_info = null;

	public function __construct() {
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_links' ), 90 );
		add_action( 'init', array( $this, 'optimisation_cron' ) );
		add_filter( 'cron_schedules', array( $this, 'add_custom_interval' ) );
		add_action( 'optimisation_purge_time_event', array( $this, 'do_optimisation_purge_cron' ) );
		add_action( 'wp_ajax_optimise_db_ajx', array( $this, 'optimise_db_ajx' ) );
		add_action( 'wp_loaded', array( $this, 'exe_before_wpheader' ) );
		add_action( 'wp_ajax_optimisationio_clear_gravatar_cache', array( $this, 'ajax_clear_gravatars_cache' ) );
		add_action('wp_ajax_optimisationio_track_page_load_time', array( $this, 'ajax_track_page_load_time' ) );

		if ( ! is_admin() ) {
			$this->check_gravatars_cache();
			$this->check_logged_in_user_content_cache();
		}
	}

	public static function saved_settings(){
		if( null === $this->saved_sett ){
			$this->saved_sett = get_option( Optimisationio::OPTION_KEY . '_settings', array() );
		}
		return $this->saved_sett;
	}

	public function clear_saved_settings(){
		$this->saved_sett = null;
	}

	public function saved_optimised_db_info(){
		if( null === $this->saved_db_info ){
			$this->saved_db_info = get_option( Optimisationio::OPTION_KEY . '_dboptimisesetting', array() );
		}
		return $this->saved_db_info;
	}

	public function clear_saved_optimised_db_info(){
		$this->saved_db_info = null;
	}

	/**
	 * Add admin links
	 *
	 * @since   1.0.0
	 * @change  1.1.0
	 *
	 * @hook    mixed
	 *
	 * @param   object $wp_admin_bar Menu properties.
	 */
	 public static function add_admin_links( $wp_admin_bar ) {

		// Check user role.
		if ( ! is_admin_bar_showing() || ! apply_filters( 'user_can_clear_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

			// add a parent item
		$args = array(
			'id'    => 'cache',
			'title' => esc_html__( 'Cache', 'optimisationio' ),
			'parent' => 'top-secondary',
		);
		$wp_admin_bar->add_node( $args );

		// Add admin purge link.
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'clear-cache',
				'href'   => wp_nonce_url( add_query_arg( '_cache', 'clear' ), '_cache__clear_nonce' ),
				'parent' => 'cache',
				'title'  => '<span class="ab-item">' . esc_html__( 'Clear Cache', 'optimisationio' ) . '</span>',
				'meta'   => array(
					'title' => esc_html__( 'Clear Cache', 'optimisationio' ),
				),
			)
		);

		$purge_url = add_query_arg( array( 'nginx_helper_action' => 'purge', 'nginx_helper_urls' => 'all' ) );
		$nonced_url = wp_nonce_url( $purge_url, 'nginx_helper-purge_all' );
		$wp_admin_bar->add_menu(
			array( 
				'id' => 'nginx-helper-purge-all', 
				'href' => $nonced_url,
				'parent' => 'cache', 
				'title' => __( 'Purge Nginx Cache', 'optimisationio' ),
				'meta' => array( 'title' => __( 'Purge Nginx Cache', 'optimisationio' ), 
				), 
			) 
		);


		if ( ! is_admin() ) {
			// Add admin purge link.
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'clear-url-cache',
					'href'   => wp_nonce_url( add_query_arg( '_cache', 'clearurl' ), '_cache__clear_nonce' ),
					'parent' => 'cache',
					'title'  => '<span class="ab-item">' . esc_html__( 'Clear URL Cache', 'optimisationio' ) . '</span>',
					'meta'   => array(
						'title' => esc_html__( 'Clear URL Cache', 'optimisationio' ),
					),
				)
			);
		}
	}

	public function add_custom_interval( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly' ),
		);
		$schedules['daily'] = array(
			'interval' => 86499,
			'display'  => __( 'Once Daily' ),
		);
		$schedules['monthly'] = array(
			'interval' => 2592000,
			'display'  => __( 'Once Daily' ),
		);
		$schedules['fortnightly'] = array(
			'interval' => 1209600,
			'display'  => __( 'Once Fortnightly' ),
		);
		return $schedules;
	}

	public function optimisation_cron() {
		$settings = $this->saved_optimised_db_info();
		if ( isset( $settings['auto_optimise'] ) && 1 === $settings['auto_optimise'] ) {
			if ( ! wp_next_scheduled( 'optimisation_purge_time_event' ) ) {
				if ( isset( $settings['optimise_schedule_type'] ) ) {
					$time = time() * (3600 * 3);
					wp_schedule_event( $time, $settings['optimise_schedule_type'], 'optimisation_purge_time_event' );
				}
			}
		}
	}

	public function do_optimisation_purge_cron() {
		$this->optimise();
	}

	public function optimise() {
		global $wpdb;
		$db_name                 = $wpdb->prefix . 'wp_optimisation_sizes_info';
		$db_size_info_before     = $this->get_db_size_info();

		$db_size_before_optimise = $db_size_info_before['total_size'];
		$retention_enabled       = false;
		$retention_period        = 1;

		$settings = $this->saved_optimised_db_info();

		if ( isset( $settings['clean_draft_posts'] ) && $settings['clean_draft_posts'] ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status=%s", 'draft' ) );
		}

		if ( isset( $settings['clean_auto_draft_posts'] ) && $settings['clean_auto_draft_posts'] ) {
			if ( 'true' === $retention_enabled ) {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = %s AND post_modified < NOW() - INTERVAL %d WEEK;", 'auto-draft', $retention_period );
			} else {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' );
			}
			$autodraft = $wpdb->query( $clean );
		}

		if ( isset( $settings['clean_trash_posts'] ) && $settings['clean_trash_posts'] ) {
			if ( 'true' === $retention_enabled ) {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = '%s' AND post_modified < NOW() - INTERVAL %d WEEK;", 'trash', $retention_period );
			} else {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_status = '%s';", 'trash' );
			}
			$posttrash = $wpdb->query( $clean );
		}

		if ( isset( $settings['clean_post_revisions'] ) && $settings['clean_post_revisions'] ) {
			if ( $retention_enabled ) {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type = %s AND post_modified < NOW() - INTERVAL %d WEEK", 'revision', $retention_period );
			} else {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->posts WHERE post_type = %s", 'revision' );
			}

			$revisions = $wpdb->query( $clean );
		}

		if ( isset( $settings['clean_transient_options'] ) && $settings['clean_transient_options'] ) {
			$wpdb->query( "DELETE from $wpdb->options WHERE `option_name` like ('%\_transient\_%')" );
		}

		if ( isset( $settings['clean_trash_comments'] ) && $settings['clean_trash_comments'] ) {
			// TODO:  query trashed comments and cleanup metadata.
			if ( $retention_enabled ) {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_approved = %s AND comment_date < NOW() - INTERVAL %d WEEK", 'trash', $retention_period );
			} else {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_approved = %s", 'trash' );
			}
			$commentstrash = $wpdb->query( $clean );
		}

		if ( isset( $settings['clean_spam_comments'] ) && $settings['clean_spam_comments'] ) {
			if ( $retention_enabled ) {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_approved = %s AND comment_date < NOW() - INTERVAL %s WEEK", 'spam', $retention_period );
			} else {
				$clean = $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_approved = %s", 'spam' );
			}
			$comments = $wpdb->query( $clean );
		}

		if ( isset( $settings['clean_post_meta'] ) && $settings['clean_post_meta'] ) {
			$postmeta = $wpdb->query( 'DELETE pm FROM ' . $wpdb->postmeta . ' pm LEFT JOIN ' . $wpdb->posts . ' wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL' );
		}

		$table_string = null;

		$tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );

		foreach ( $tables as $table ) {
			if ( in_array( $table['Engine'], array( 'MyISAM', 'ISAM', 'HEAP', 'MEMORY', 'ARCHIVE' ), true ) ) {
				$table_string .= $table['Name'] . ',';
			} elseif ( 'InnoDB' === $table['Engine'] ) {
				$optimize = $wpdb->query( 'ALTER TABLE ' . $table['Name'] . ' ENGINE=InnoDB' );
			}
		}

		if ( $table_string ) {
			$table_string = rtrim( $table_string, ',' );
			$optimize     = $wpdb->query( 'OPTIMIZE TABLE ' . $table_string );
		}

		$db_size_info_after     = $this->get_db_size_info();
		$db_size_after_optimise = $db_size_info_after['total_size'];

		$exe = $wpdb->query( 'INSERT INTO ' . $db_name . ' (`size`, `optimised_size`) VALUES ( ' . $db_size_before_optimise . ', ' . $db_size_after_optimise . ' )' );
	}

	private function get_db_size_info() {
		global $wpdb;

		$ret = array(
			'usage_row'      => 0,
			'usage_data'     => 0,
			'usage_index'    => 0,
			'usage_overhead' => 0,
			'gain_size'      => 0,
			'total_size'     => 0,
		);

		$db_status = $wpdb->get_results( 'SHOW TABLE STATUS' );

		foreach ( $db_status as $s ) {

			$ret['usage_row'] += $s->Rows;
			$ret['usage_data'] += $s->Data_length;
			$ret['usage_index'] += $s->Index_length;

			if ( 'InnoDB' !== $s->Engine ) {
				$ret['usage_overhead'] += $s->Data_free;
				$ret['gain_size'] += $s->Data_free;
			}
		}

		$ret['total_size'] = $ret['usage_data'] + $ret['usage_index'];

		return $ret;
	}

	public static function cahe_enabler() {
		$selectoptions = Optimisationio_CacheEnabler::_minify_select();
		$settings = get_option( Optimisationio::OPTION_KEY . '_settings', array() );
		$data          = array(
			'settings' => $settings,
			'selectoptions' => $selectoptions,
			'cacheSize' => (Optimisationio_CacheEnabler::get_cache_size() / 1000) . ' Kb',
		);
		return $data;
	}

	public static function cdn_enabler() {
		$settings = get_option( Optimisationio::OPTION_KEY . '_cdnsettings', array() );
		$data     = array(
			'settings' => $settings,
		);
		return $data;
	}

	public static function optimise_db() {
		global $wpdb;
		$settings = get_option( Optimisationio::OPTION_KEY . '_dboptimisesetting', array() );

		// Gettting the database information.
		$info = array();

		$total_draft_post = $wpdb->get_row( "select count(*) as total_draft_post from  $wpdb->posts where post_status='draft'", ARRAY_A );
		$info['total_draft_post'] = $total_draft_post['total_draft_post'];

		$total_auto_draft = $wpdb->get_row( "SELECT count(*) as total_auto_draft FROM $wpdb->posts WHERE post_status = 'auto-draft'", ARRAY_A );
		$info['total_auto_draft'] = $total_auto_draft['total_auto_draft'];

		$total_trash = $wpdb->get_row( "SELECT count(*) as total_trash FROM $wpdb->posts WHERE post_status = 'trash'", ARRAY_A );
		$info['total_trash'] = $total_trash['total_trash'];

		$total_revision = $wpdb->get_row( "SELECT count(*) as total_revision FROM $wpdb->posts WHERE post_type = 'revision'", ARRAY_A );
		$info['total_revision'] = $total_revision['total_revision'];

		$total_invalid_post_meta = $wpdb->get_row( "SELECT count(*) as total_invalid_post_meta FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL;", ARRAY_A );
		$info['total_invalid_post_meta'] = $total_revision['total_invalid_post_meta'];

		$total_transient = $wpdb->get_row( "SELECT count(*) as total_transient FROM $wpdb->options where `option_name` like ('%\_transient\_%')", ARRAY_A );
		$info['total_transient'] = $total_transient['total_transient'];

		$total_trash_comments = $wpdb->get_row( "SELECT count(*) as total_trash_comments FROM $wpdb->comments WHERE comment_approved = 'trash'", ARRAY_A );
		$info['total_trash_comments'] = $total_trash_comments['total_trash_comments'];

		$total_spam_comments = $wpdb->get_row( "SELECT count(*) as total_spam_comments from $wpdb->comments WHERE comment_approved = 'spam'", ARRAY_A );
		$info['total_spam_comments'] = $total_spam_comments['total_spam_comments'];

		$total_non_innodb_tables = $wpdb->get_row( "SELECT count(*) as total_non_innodb_tables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $wpdb->dbname . "' AND ENGINE != 'InnoDB'", ARRAY_A );

		$total_innodb_tables = $wpdb->get_row( "SELECT count(*) as total_innodb_tables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $wpdb->dbname . "' AND ENGINE = 'InnoDB'", ARRAY_A );

		$info['total_non_innodb_tables'] = $total_non_innodb_tables['total_non_innodb_tables'];
		$info['total_innodb_tables'] = $total_innodb_tables['total_innodb_tables'];

		$data = array(
			'settings' => $settings,
			'info' => $info,
		);

		return $data;
	}

	public static function gravatars_cache() {
		$settings = get_option( Optimisationio::OPTION_KEY . '_gravatar_cache_settings', array() );
		$data     = array(
			'settings' => $settings,
		);
		return $data;
	}

	public static function cache_gravatars_number() {
		global $wpdb;
		$table = $wpdb->prefix . 'wp_cache_gravatars';
		$sql = "SELECT COUNT(*) AS count FROM " . $table . ";";
		$results = $wpdb->get_results($sql, ARRAY_A);
		return $results ? $results[0]['count']: 0;
	}

	private function check_gravatars_cache(){
		$settings = get_option( Optimisationio::OPTION_KEY . '_gravatar_cache_settings', array() );

		if( get_option( 'show_avatars' ) && isset( $settings['enable_cache_gravatars'] ) && $settings['enable_cache_gravatars'] ) {
			add_filter( 'get_avatar', array( $this, 'get_avatar' ), 10, 5 );
		}
	}

	public function get_avatar($avatar, $id_or_email, $size, $default, $alt){

		$src_val_pre = false;

		preg_match_all( '/(?:src|srcset)=[\'||"](.+?)[\'||"]/i', $avatar, $match );

		if( empty( $match ) ){
			return $avatar;
		}

    	$src_val_pre = $match[1][0];

    	if( ! $src_val_pre || empty( $src_val_pre ) ){
    		return $avatar;
    	}

    	$src_val = htmlspecialchars_decode( $src_val_pre );

    	if( stristr( $src_val, home_url() ) || 0 === strpos( $src_val, "/" ) ){	// Is not external link.
    		return $avatar;
    	}

    	$cache_avatar = $this->avatar_cache($src_val);

    	if( $cache_avatar ){
    		return $cache_avatar;
    	}

    	$srcset_val_pre = isset( $match[1][1] ) ? $match[1][1] : $srcset_val;

    	if ( $srcset_val_pre && ! empty( $srcset_val_pre ) ) {
    		$srcset_arr = explode(",", $srcset_val_pre);
    	}

    	$created_files = array();

    	$new_avatar_url = $this->download_avatar( $src_val );

    	if( is_wp_error( $new_avatar_url ) ){
    		return $avatar;
    	}

    	$created_files[] = basename( $new_avatar_url );

    	$new_srscet_images = array();

		foreach ($srcset_arr as $key => $value) {
			$tmp = explode(" ", $value);
			$new_srscet_images[ $value ] = $this->download_avatar( htmlspecialchars_decode( $value ) );
			if( is_wp_error( $new_srscet_images[ $value ] ) ){
	    		return $avatar;
	    	}
	    	$created_files[] = basename( $new_srscet_images[ $value ] );
	    	$new_srscet_images[ $value ] .= " " . $tmp[1];
		}

    	if( ! empty( $new_srscet_images ) ){
    		$srcset_val_pre_new = $srcset_val_pre;
    		foreach ($new_srscet_images as $key => $value) {
    			$srcset_val_pre_new = str_replace($key, $value, $srcset_val_pre_new);
    		}
    	}

    	$avatar = str_replace($src_val_pre, esc_attr( $new_avatar_url ), $avatar);
    	$avatar = str_replace($srcset_val_pre, esc_attr( $srcset_val_pre_new ), $avatar);

    	global $wpdb;
		$sql = $wpdb->prepare( "INSERT INTO " . $wpdb->prefix . "wp_cache_gravatars (url, avatar, files) VALUES(%s, %s, %s)", $src_val, $avatar, json_encode( $created_files ) );
		$wpdb->query($sql);

		return $avatar;
	}

	private function download_avatar( $remote_url ){

		global $wp_filesystem;

        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $upload_dir = wp_upload_dir();
		$dest_directory = $upload_dir['basedir'] . '/wp-disable-avatars-cache/';
		$dest_url = $upload_dir['baseurl'] . '/wp-disable-avatars-cache/';

        $tmp_file = wp_tempnam( basename( parse_url( $remote_url, PHP_URL_PATH ) ) );
        $response = wp_safe_remote_get( $remote_url, array( 'filename' => $tmp_file ) );

        $content_type = wp_remote_retrieve_header( $response, 'content-type' );

        $file_name = false;

        switch( $content_type ){
        	case 'image/jpg':
        	case 'image/jpeg':
        		$file_name = '.jpg';
        		break;
        	case 'image/png':
        		$file_name = '.png';
        		break;
        	case 'image/gif':
        		$file_name = '.gif';
        		break;
        }

        if( ! $file_name ){
        	unlink($tmp_file);
        	return new WP_Error( 'download_image', "Download remote image failed." );
        }

        $file_name = uniqid() . $file_name;

        $mirror = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $response ) );

        unlink($tmp_file);

        if ( $mirror['error'] || ! isset( $mirror['file'] ) || ! $mirror['file'] ) {
        	return new WP_Error( 'upload_fail', "Failed to upload remote image." );
        }

        if ( wp_mkdir_p( $dest_directory ) ){
        	if ( ! file_exists( trailingslashit( $dest_directory ) . $file_name ) ) {
				$move_response = $wp_filesystem->move( $mirror['file'] , $dest_directory . $file_name , true );
				if ( ! $move_response ) {
					return new WP_Error( 'upload_move', "Move temp file action failed." );
		        }
			}
        }

        return $dest_url . $file_name;
	}

	private function remove_old_avatars_cache(){
		global $wpdb, $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $settings = get_option( Optimisationio::OPTION_KEY . '_gravatar_cache_settings', array() );

        switch( $settings['clear_cache_every'] ){
			case 'one_hour': $compare_time = '-1 HOUR';
				break;
			case 'two_hour': $compare_time = '-2 HOUR';
				break;
			case 'six_hour': $compare_time = '-6 HOUR';
				break;
			case 'twelve_hour': $compare_time = '-12 HOUR';
				break;
			case 'one_day': $compare_time = '-1 DAY';
				break;
			case 'two_days': $compare_time = '-2 DAY';
				break;
			case 'one_week': $compare_time = '-1 WEEK';
				break;
			case 'two_weeks': $compare_time = '-2 WEEK';
				break;
			case 'one_month': $compare_time = '-1 MONTH';
				break;
		}

        $table = $wpdb->prefix . 'wp_cache_gravatars';
        $sql = "SELECT id,url,files FROM " . $table . " WHERE time < DATE_ADD(NOW(), INTERVAL " . $compare_time . ")";	// Get avatars data older than one day.

		$results = $wpdb->get_results($sql, ARRAY_A);

		if( $results ){
			$urls = array();
			foreach ($results as $key => $val) {

				$ids[] = $val['id'];

				$avatar_files = json_decode( $val['files'] );

		        if( count($avatar_files) ){
		        	$upload_dir = wp_upload_dir();
		        	$upload_dir = $upload_dir['basedir'] . '/wp-disable-avatars-cache/';
		        	foreach($avatar_files as $key => $filename){
    					$wp_filesystem->delete( $upload_dir . $filename );
		        	}
		        }
			}

			if( ! empty( $ids ) ){
				$wpdb->query( "DELETE FROM " . $table . " WHERE id IN (" . implode(",",$ids) . ")" );
				$wpdb->query( "ALTER TABLE " . $table . " AUTO_INCREMENT = 1" );
			}
		}
	}

	private function avatar_cache( $url ){
		$return = wp_cache_get( 'wp_disable_avatar_cache[' . $url . ']' );
		if ( false === $return ) {
			global $wpdb;
			$table = $wpdb->prefix . 'wp_cache_gravatars';
			$sql = $wpdb->prepare( "SELECT avatar,time FROM " . $table . " WHERE url=%s LIMIT 1", $url );
			$result = $wpdb->get_results($sql, ARRAY_A);

			if( $result ){

				$settings = get_option( Optimisationio::OPTION_KEY . '_gravatar_cache_settings', array() );

				switch( $settings['clear_cache_every'] ){
					case 'one_hour': $compare_time = HOUR_IN_SECONDS;
						break;
					case 'two_hour': $compare_time = HOUR_IN_SECONDS * 2;
						break;
					case 'six_hour': $compare_time = HOUR_IN_SECONDS * 6;
						break;
					case 'twelve_hour': $compare_time = HOUR_IN_SECONDS * 12;
						break;
					case 'two_days': $compare_time = DAY_IN_SECONDS * 2;
						break;
					case 'one_week': $compare_time = WEEK_IN_SECONDS;
						break;
					case 'two_weeks': $compare_time = WEEK_IN_SECONDS * 2;
						break;
					case 'one_month': $compare_time = MONTH_IN_SECONDS;
						break;
					case 'one_day':
					default:
						$compare_time = DAY_IN_SECONDS;
						break;
				}

				// Keep avatars cache for one day.
				if( $compare_time < current_time('timestamp') - strtotime( $result[0]['time'] ) ){
					$this->remove_old_avatars_cache();
				}
				else{
					$return = $result[0]['avatar'];
					wp_cache_set( 'wp_disable_avatar_cache[' . $url . ']', $return );
				}
			}
		}
		return $return;
	}

	public static function drop_avatars_cache_db_table(){
		global $wpdb;
		$table = $wpdb->prefix . 'wp_cache_gravatars';
    	$wpdb->query( "DROP TABLE IF EXISTS " . $table );
	}

	public function ajax_clear_gravatars_cache(){
		$post_request = $_POST;   // Input var okay.
		$ret = array( 'error' => 1 );
		if( wp_verify_nonce( $post_request['nonce'], 'optimisationio-cache-preformance-settings' ) ){
			self::clear_gravatars_cache();
			$ret['error'] = 0;
		}
		else{
			$ret['type'] = 'verification_fail';
		}
		wp_send_json( $ret );
	}

	public function ajax_track_page_load_time(){
		$p = $_POST;
		if( isset( $p['nonce'] ) && wp_verify_nonce( $p['nonce'], 'optimisationio-track-load-time-nonce' ) ){
			if( isset( $p['checked'] ) ){
				update_option( 'Optimisationio_track_page_load_time', '1' === $p['checked'] ? 1 : 0 );
			}
			else{
				$return = array( 'error' => 1, 'msg' => 'Invalid arguments' );
			}
		}
		else{
			$return = array( 'error' => 1, 'msg' => 'Non verified access' );
		}
		print_r( wp_json_encode( $return ) );
		die();
	}

	public static function clear_gravatars_cache(){

		global $wpdb, $wp_filesystem;

        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $wpdb->query( "TRUNCATE " . $wpdb->prefix . 'wp_cache_gravatars' );

        $upload_dir = wp_upload_dir();
        $wp_filesystem->delete( $upload_dir['basedir'] . '/wp-disable-avatars-cache', true );
	}

	private function check_logged_in_user_content_cache(){

		$settings = get_option( Optimisationio::OPTION_KEY . '_settings', array() );
		if( isset( $settings['cache_user_logged_in_content'] ) && 1 === (int) $settings['cache_user_logged_in_content'] ){
			require 'class-optimisationio-cache_loggedin_user_content.php';
			Optimisationio_Cache_Loggedin_User_Content::get_instance();
		}
	}

	private function sendemail( $to, $subject, $message, $from ) {
		$headers = 'From: ' . strip_tags( $from ) . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		@mail( $to, $subject, $message, $headers );
	}

	private function add_message( $msg, $type = 'success' ) {
		if ( 'success' === $type ) {
			printf( "<div class='updated'><p><strong>%s</strong></p></div>", $msg );
		} else {
			printf( "<div class='error'><p><strong>%s</strong></p></div>", $msg );
		}
	}

	private function redirect_url( $url ) {
		echo '<script>window.location.href="' . $url . '"</script>';
	}

	private function update_cache_enabler( $post_req ) {
		$options = array(
			'cache_expires'           => sanitize_text_field( $post_req['cache_expires'] ),
			'cache_new_post'          => $post_req['cache_new_post'] ? 1 : 0,
			'cache_new_comment'       => $post_req['cache_new_comment'] ? 1 : 0,
			'cache_webp'              => $post_req['cache_webp'] ? 1 : 0,
			'cache_compress'          => $post_req['cache_compress'] ? 1 : 0,
			'cache_user_logged_in_content' => $post_req['cache_user_logged_in_content'] ? 1 : 0,
			'enable_gzip_compression' => $post_req['enable_gzip_compression'] ? 1 : 0,
			'enable_leverage_browser_caching' => $post_req['enable_leverage_browser_caching'] ? 1 : 0,
			'excl_ids'                => sanitize_text_field( $post_req['excl_ids'] ),
			// 'minify_html'             => sanitize_text_field( $post_req['minify_html'] ),	// NOTE: Not used, keep it here for now.
		);

		$settings = update_option( Optimisationio::OPTION_KEY . '_settings', $options );

		$this->clear_saved_settings();

		if( $settings ){

			if ( $GLOBALS['is_apache'] && is_writable( ABSPATH . '.htaccess' ) ) {
				if ( $options['enable_gzip_compression'] ) {
					Optimisationio_CacheEnabler::htaccess_modify_gzip_content();
				}
				else{
					Optimisationio_CacheEnabler::remove_htaccess_modify_gzip_content();
				}

				if ( $options['enable_leverage_browser_caching'] ) {
					Optimisationio_CacheEnabler::htaccess_modify_leverage_browser_caching();
				}
				else{
					Optimisationio_CacheEnabler::remove_htaccess_modify_leverage_browser_caching();
				}
			}
		}
	}

	private function update_cdn_enabler($post_req) {
		$options = array(
			'cdn_root_url'            => sanitize_text_field( $post_req['cdn_root_url'] ),
			'cdn_file_extensions'     => sanitize_text_field( $post_req['cdn_file_extensions'] ),
			'cdn_css_root_url'        => sanitize_text_field( $post_req['cdn_css_root_url'] ),
			'cdn_css_file_extensions' => sanitize_text_field( $post_req['cdn_css_file_extensions'] ),
			'cdn_js_root_url'         => sanitize_text_field( $post_req['cdn_js_root_url'] ),
			'cdn_js_file_extensions'  => sanitize_text_field( $post_req['cdn_js_file_extensions'] ),
		);
		$settings = update_option( Optimisationio::OPTION_KEY . '_cdnsettings', $options );
	}

	private function update_gravatars_cache($post_req) {

		$previous_settings = get_option( Optimisationio::OPTION_KEY . '_gravatar_cache_settings', array() );

		$options = array(
			'enable_cache_gravatars' => (int) $post_req['enable_cache_gravatars'],
			'clear_cache_every'     => sanitize_text_field( $post_req['clear_cache_every'] ),
		);

		update_option( Optimisationio::OPTION_KEY . '_gravatar_cache_settings', $options );

		if( isset( $previous_settings['enable_cache_gravatars'] ) && $previous_settings['enable_cache_gravatars'] != $options['enable_cache_gravatars'] ){
			// Clear general Cache on enable/dsable gravatars cache.
			Optimisationio_CacheEnablerDisk::clear_cache();
		}
	}

	public function update_db_optimise($post_req, $force_schedule_clear = false) {

		$options = array(
			'clean_draft_posts'       => $post_req['clean_draft_posts'] ? 1 : 0,
			'clean_auto_draft_posts'  => $post_req['clean_auto_draft_posts'] ? 1 : 0,
			'clean_trash_posts'       => $post_req['clean_trash_posts'] ? 1 : 0,
			'clean_post_revisions'    => $post_req['clean_post_revisions'] ? 1 : 0,
			'clean_transient_options' => $post_req['clean_transient_options'] ? 1 : 0,
			'clean_trash_comments'    => $post_req['clean_trash_comments'] ? 1 : 0,
			'clean_spam_comments'     => $post_req['clean_spam_comments'] ? 1 : 0,
			'clean_post_meta'         => $post_req['clean_post_meta'] ? 1 : 0,
			'auto_optimise'           => $post_req['auto_optimise'] ? 1 : 0,
			'optimise_schedule_type'  => $post_req['optimise_schedule_type'] ? sanitize_text_field( $post_req['optimise_schedule_type'] ) : null
		);

		$settings = update_option( Optimisationio::OPTION_KEY . '_dboptimisesetting', $options );

		if ( $force_schedule_clear || ! $options['auto_optimise'] ) {
			wp_clear_scheduled_hook( 'optimisation_purge_time_event' );
		}

		/*$this->clear_saved_optimised_db_info();

		if ( 1 !== (int) $post_req['auto_optimise'] ) {
			$this->optimise();
			wp_clear_scheduled_hook( 'optimisation_purge_time_event' );
			$return['msg'] = 'Database optimisation is successfull';
		} else {
			$return['msg'] = 'Database optimisation settings saved successfully';
		}
		$return['status'] = 'success';*/
	}

	public function optimise_db_ajx(){

		$post_req = $_POST; // Input var okay.

		if( wp_verify_nonce( $post_req['optimisationio_cache_preformance_settings'], 'optimisationio-cache-preformance-settings' ) ){

			$this->update_db_optimise($post_req, true);
			$this->optimise();

			$ret['$post_req'] = $post_req;

			$ret['msg'] = __('Database optimisation is successfull', 'optimisationio');
			$ret['status'] = 'success';

			ob_start();
			self::optimise_db_fields();
			$ret['optimise_db_fields_content'] = ob_get_contents();
			ob_end_clean();
		}
		else{
			$ret['msg'] = __('Invalid nonce', 'optimisationio');
			$ret['status'] = 'error';
		}

		wp_send_json($ret);
	}

	public function exe_before_wpheader(){

		$server = $_SERVER;   // Input var okay.
		$post_req = 'post' === sanitize_key( $server['REQUEST_METHOD'] )  ? $_POST : array();   // Input var okay.

		if( isset( $post_req['optimisation_save_cache_performance_settings'] ) ){	// NOTE:Save ONLY on form submit. Deny access from another call (eg. AJAX).

			if( isset( $post_req['optimisationio_cache_preformance_settings'] ) && wp_verify_nonce( $post_req['optimisationio_cache_preformance_settings'], 'optimisationio-cache-preformance-settings' ) ) {
				$this->update_cache_enabler($post_req);
				$this->update_cdn_enabler($post_req);
				$this->update_db_optimise($post_req);
				$this->update_gravatars_cache($post_req);
			}
		}
	}

	private static function optimise_db_fields(){
		$optimise_db_sett = array();
		$optimise_db_data = Optimisationio_Admin::optimise_db();
		if( $optimise_db_data ){
			$optimise_db_sett = $optimise_db_data['settings'];
			$optimise_db_info = $optimise_db_data['info'];
			unset($optimise_db_data);
		}
		?>

		<div class="field">
			<div class="field-left">
				<h3 style="margin:0; font-size:1.1em;"><?php esc_html_e( 'CLEAN-UP OPTIONS', 'optimisationio' ); ?></h3>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean draft posts', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_draft_post'] > 0 ) ? $optimise_db_info['total_draft_post'] . ' ' . esc_html( 'draft posts found', 'optimisationio' ) : esc_html( 'No draft posts found', 'optimisationio' ); ?>
				</p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_draft_posts', isset( $optimise_db_sett['clean_draft_posts'] ) && $optimise_db_sett['clean_draft_posts']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean auto draft posts', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_auto_draft'] > 0 ) ? $optimise_db_info['total_auto_draft'] . ' ' . esc_html( 'auto draft posts found', 'optimisationio' ) : esc_html( 'No auto draft posts found', 'optimisationio' ); ?></p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_auto_draft_posts', isset( $optimise_db_sett['clean_auto_draft_posts'] ) && $optimise_db_sett['clean_auto_draft_posts']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean trash posts', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_trash'] > 0 ) ? $optimise_db_info['total_trash'] . ' ' . esc_html( 'trash posts found', 'optimisationio' ) : esc_html( 'No trash posts found', 'optimisationio' ); ?></p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_trash_posts', isset( $optimise_db_sett['clean_trash_posts'] ) && $optimise_db_sett['clean_trash_posts']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean post revisions', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_revision'] > 0 ) ? $optimise_db_info['total_revision'] . ' ' . esc_html( 'post revisions found', 'optimisationio' ) : esc_html( 'No post revisions found', 'optimisationio' ); ?></p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_post_revisions', isset( $optimise_db_sett['clean_post_revisions'] ) && $optimise_db_sett['clean_post_revisions']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean post meta data', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_invalid_post_meta'] > 0 ) ? $optimise_db_info['total_invalid_post_meta'] . ' ' . esc_html( 'post meta found', 'optimisationio' ) : esc_html( 'No post meta found', 'optimisationio' ); ?></p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_post_meta', isset( $optimise_db_sett['clean_post_meta'] ) && $optimise_db_sett['clean_post_meta']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean transient options', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_transient'] > 0 ) ? $optimise_db_info['total_transient'] . ' ' . esc_html( 'transient options found', 'optimisationio' ) : esc_html( 'No transient options found', 'optimisationio' ); ?></p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_transient_options', isset( $optimise_db_sett['clean_transient_options'] ) && $optimise_db_sett['clean_transient_options']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean trash comments', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_trash_comments'] > 0 ) ? $optimise_db_info['total_trash_comments'] . ' ' . esc_html( 'trash comments found', 'optimisationio' ) : esc_html( 'No trash comments found', 'optimisationio' ); ?></p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_trash_comments', isset( $optimise_db_sett['clean_trash_comments'] ) && $optimise_db_sett['clean_trash_comments']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Clean spam comments', 'optimisationio'); ?></div>
			<div class="field-right">
				<p class="checkbox-text"><?php echo ( $optimise_db_info['total_spam_comments'] > 0 ) ? $optimise_db_info['total_spam_comments'] . ' ' . esc_html( 'spam comments found', 'optimisationio' ) : esc_html( 'No spam comments found', 'optimisationio' ); ?></p>
				<?php Optimisationio_Dashboard::checkbox_component('clean_spam_comments', isset( $optimise_db_sett['clean_spam_comments'] ) && $optimise_db_sett['clean_spam_comments']); ?>
			</div>
		</div>

		<div class="field">
			<div class="field-left">
				<h3 style="margin:0; font-size:1.1em;"><?php esc_html_e( 'SCHEDULED OPTIMISATION', 'optimisationio' ); ?></h3>
			</div>
		</div>

		<div class="field">
			<div class="field-left"><?php esc_attr_e('Enable automatic database optimisation', 'optimisationio'); ?></div>
			<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('auto_optimise', isset( $optimise_db_sett['auto_optimise'] ) && $optimise_db_sett['auto_optimise']); ?></div>
		</div>

		<div class="field sub-field auto-optimise-group">
			<div class="field-left"><?php esc_html_e( 'Optimise database every', 'optimisationio'); ?></div>
			<div class="field-right">
				<?php
				$optimise_db_sett['optimise_schedule_type'] = isset( $optimise_db_sett['optimise_schedule_type'] )?$optimise_db_sett['optimise_schedule_type']:'daily'; ?>
				<select name="optimise_schedule_type">
					<option value="daily" <?php selected( $optimise_db_sett['optimise_schedule_type'], 'daily' ); ?>><?php esc_html_e( 'Day', 'optimisationio' ); ?></option>
					<option value="weekly" <?php selected( $optimise_db_sett['optimise_schedule_type'], 'weekly' ); ?>><?php esc_html_e( 'Week', 'optimisationio' ); ?></option>
					<option value="fortnightly" <?php selected( $optimise_db_sett['optimise_schedule_type'], 'fortnightly' ); ?>><?php esc_html_e( 'Twice month', 'optimisationio' ); ?></option>
					<option value="monthly" <?php selected( $optimise_db_sett['optimise_schedule_type'], 'monthly' ); ?>><?php esc_html_e( 'Month', 'optimisationio' ); ?></option>
				</select>
			</div>
		</div>

		<div class="field">
			<div class="field-left">
				<h3 style="margin:0; font-size:1.1em; font-weight:500;"><?php esc_html_e( 'ACTIONS', 'optimisationio' ); ?></h3>
			</div>
		</div>

		<div class="field">
			<div class="field-left">
				<p><?php printf( __( 'Tables using the INNODB engine (%1$s) will not be optimized', 'optimisationio' ), '<strong>' . $optimise_db_info['total_innodb_tables'] . '</strong>' ); ?></p>
			</div>
			<div class="field-right">
				<p><?php printf( __( 'Other tables will be optimized (%1$s)', 'optimisationio' ), '<strong>' . $optimise_db_info['total_non_innodb_tables'] . '</strong>' ); ?></p>
			</div>
		</div>

		<div class="field">
			<div class="field-left"></div>
			<div class="field-right">
				<button class="optimise-db-now button button-large"><?php esc_html_e("Optimise database now", "optimisationio"); ?></button>
			</div>
		</div>

		<div class="optimising-db-overlay"><div><div><?php esc_html_e( 'Optimising Database...', 'optimisationio' ); ?><img src="<?php echo esc_url( admin_url('images/spinner.gif') ); ?>" alt=""/></div></div></div>
		<?php
	}

	private static function gravatars_fields(){

		$gravatars_sett = array();
		$gravatars_cache_data = Optimisationio_Admin::gravatars_cache();

		if( $gravatars_cache_data ){
			$gravatars_sett = $gravatars_cache_data['settings'];
			unset($gravatars_cache_data);
		}

		$cache_refresh_options = array(
			'one_hour'	=> __('1 hour'),
			'two_hour'	=> __('2 hours'),
			'six_hour'	=> __('6 hours'),
			'twelve_hour' => __('12 hours'),
			'one_day'	=> __('1 day'),
			'two_days'	=> __('2 days'),
			'one_week'	=> __('1 week'),
			'two_weeks'	=> __('2 weeks'),
			'one_month'	=> __('1 month')
		);
		?>
		<div class="field">
			<div class="field-left"><?php esc_attr_e('Enable Gravatars cache', 'optimisationio'); ?> <br/><small>(<?php esc_html_e( 'Keep in server a copy of Gravatars', 'optimisationio' ); ?>)</small></div>
			<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('enable_cache_gravatars', isset( $gravatars_sett['enable_cache_gravatars'] ) && 1 === (int) $gravatars_sett['enable_cache_gravatars']); ?></div>
		</div>

		<div class="field sub-field gravatars-cache-group">
			<div class="field-left"><?php esc_html_e( 'Keep cache fresh for', 'optimisationio'); ?></div>
			<div class="field-right">
				<select name="clear_cache_every"> <?php
					foreach ($cache_refresh_options as $key => $val) {
						$def =
						( isset( $gravatars_sett['clear_cache_every'] ) && $key === $gravatars_sett['clear_cache_every'] )
						|| ( ! isset( $gravatars_sett['clear_cache_every'] ) && $key === 'one_day' ) ? 'selected="selected"': '';
						?><option value="<?php esc_attr_e($key); ?>" <?php echo $def; ?>><?php echo $val; ?></option><?php
					}
					?>
				</select>
			</div>
		</div>

		<div class="field sub-field gravatars-cache-group">
			<div class="field-left"></div>
			<div class="field-right">
				<span class="dis-ib" style="padding:0.4rem 1rem 0 0;"><strong class="cashed-gravatars-num"><?php echo self::cache_gravatars_number(); ?></strong> <?php esc_html_e( 'Gravatars found in cache', 'optimisationio' ); ?></span>
				<button class="clear-now-gravatars-cache button button-large"><?php esc_html_e("Clear now", "optimisationio"); ?></button>
			</div>
		</div>

		<div class="clearing-gravatars-cache-overlay"><div><div><?php esc_html_e( 'Clearing Gravatars Cache...', 'optimisationio' ); ?><img src="<?php echo esc_url( admin_url('images/spinner.gif') ); ?>" alt=""/></div></div></div>
		<?php
	}

	public static function addon_settings(){

		$caching_sett = array();
		$cdn_rewrite_sett = array();
		$optimise_db_info = array();

		$cahe_enabler_data = Optimisationio_Admin::cahe_enabler();
		if( $cahe_enabler_data ){
			$caching_sett = $cahe_enabler_data['settings'];
			unset($cahe_enabler_data);
		}

		$cdn_enabler_data = Optimisationio_Admin::cdn_enabler();
		if( $cdn_enabler_data ){
			$cdn_rewrite_sett = $cdn_enabler_data['settings'];
			unset($cdn_enabler_data);
		}

		?>
		<div class="addon-settings" data-sett-group="cache-performance">

			<form action="<?php echo esc_url( admin_url( 'admin.php?page=optimisationio-dashboard' ) ); ?>" method="post">

				<div class="addon-settings-tabs">
					<ul>
						<li data-tab-setting="caching" class="active"><?php esc_html_e('Caching', 'optimisationio'); ?></li>
						<li data-tab-setting="cdn-rewrite"><?php esc_html_e('CDN Rewrite', 'optimisationio'); ?></li>
						<li data-tab-setting="optimise-db"><?php esc_html_e('Optimise DB', 'optimisationio'); ?></li>
						<li data-tab-setting="gravatars"><?php esc_html_e('Gravatars', 'optimisationio'); ?></li>
						<li data-tab-setting="nginx-helper"><?php esc_html_e('NGINX Cache', 'optimisationio'); ?></li>
					</ul>
				</div>

				<div class="addon-settings-section">

					<div data-tab-setting="caching" class="addon-settings-content active">

						<div class="field">
							<div class="field-left va-top">
								<?php esc_html_e( 'Cache expiry', 'optimisationio' ); ?>
								<br/>
								<small>(<?php esc_html_e( 'Time in hours', 'optimisationio' ); ?>)</small>
							</div>
							<div class="field-right">
								<input type="text" name="cache_expires" value="<?php if ( isset( $caching_sett['cache_expires'] ) ) { echo $caching_sett['cache_expires']; } ?>" /><br/>
								<small class="dis-ib" style="padding-top:5px;"><?php echo esc_html_e('0 = never expires', 'optimisationio'); ?></small>
							</div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_attr_e('Compress cache', 'optimisationio'); ?> <br/><small>(<?php esc_html_e( 'Disable if you notice any weird issues in browser', 'optimisationio' ); ?>)</small></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('cache_compress', isset( $caching_sett['cache_compress'] ) && $caching_sett['cache_compress']); ?></div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_attr_e('New Posts', 'optimisationio'); ?> <br/><small>(<?php esc_html_e( 'Wipe cache when you publish a new post', 'optimisationio' ); ?>)</small></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('cache_new_post', isset( $caching_sett['cache_new_post'] ) && $caching_sett['cache_new_post']); ?></div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_attr_e('Enable Gzip Compression', 'optimisationio'); ?> <br/><small>(<?php esc_html_e( 'Only on Apache servers', 'optimisationio' ); ?>)</small></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('enable_gzip_compression', isset( $caching_sett['enable_gzip_compression'] ) && $caching_sett['enable_gzip_compression']); ?></div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_attr_e('Cache user logged in content', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('cache_user_logged_in_content', isset( $caching_sett['cache_user_logged_in_content'] ) && $caching_sett['cache_user_logged_in_content']); ?></div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_attr_e('Leverage browser caching', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('enable_leverage_browser_caching', isset( $caching_sett['enable_leverage_browser_caching'] ) && $caching_sett['enable_leverage_browser_caching']); ?></div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_attr_e('New comments', 'optimisationio'); ?> <br/><small>(<?php esc_html_e( 'Wipe Cache when new comments posted', 'optimisationio' ); ?>)</small></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('cache_new_comment', isset( $caching_sett['cache_new_comment'] ) && $caching_sett['cache_new_comment']); ?></div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_attr_e('WebP Images', 'optimisationio'); ?> <br/><small>(<?php printf( 'Cache WebP images. See %1$sOptimisation.io%2$s for more info', '<a href="https://optimisation.io/" target="_blank">', '</a>' ); ?>)</small></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('cache_webp', isset( $caching_sett['cache_webp'] ) && $caching_sett['cache_webp']); ?></div>
						</div>

						<div class="field">
							<div class="field-left va-top"><?php esc_html_e( 'Exclusions', 'optimisationio' ); ?></div>
							<div class="field-right">
								<input type="text" name="excl_ids" value="<?php if ( isset( $caching_sett['excl_ids'] ) ) { echo $caching_sett['excl_ids']; } ?>" /><br/>
								<small class="dis-ib" style="padding-top:5px;"><?php printf('%s Posts %s or %s Pages IDs %s separated by a', '<strong>', '</strong>', '<strong>', '</strong>' ); ?> <code>,</code></small>
							</div>
						</div>

					</div>

					<div data-tab-setting="cdn-rewrite" class="addon-settings-content">

						<div class="field">
							<div class="field-left"><?php esc_html_e('CDN Path', 'optimisationio'); ?></div>
							<div class="field-right">
								<input type="text" name="cdn_root_url" value="<?php echo isset( $cdn_rewrite_sett['cdn_root_url'] ) ? $cdn_rewrite_sett['cdn_root_url'] : ''; ?>" />
							</div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_html_e('CSS CDN Path', 'optimisationio'); ?></div>
							<div class="field-right">
								<input type="text" name="cdn_css_root_url" value="<?php echo isset( $cdn_rewrite_sett['cdn_css_root_url'] ) ? $cdn_rewrite_sett['cdn_css_root_url'] : ''; ?>"/>
							</div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_html_e('JS Files CDN Path', 'optimisationio'); ?></div>
							<div class="field-right">
								<input type="text" name="cdn_js_root_url" value="<?php echo isset( $cdn_rewrite_sett['cdn_js_root_url'] ) ? $cdn_rewrite_sett['cdn_js_root_url'] : ''; ?>"/>
							</div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_html_e('Image and file extensions', 'optimisationio'); ?></div>
							<div class="field-right">
								<input type="text" name="cdn_file_extensions" value="<?php echo isset( $cdn_rewrite_sett['cdn_file_extensions'] ) ? $cdn_rewrite_sett['cdn_file_extensions'] : ''; ?>"/>
							</div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_html_e('File Extensions for CSS', 'optimisationio'); ?></div>
							<div class="field-right">
								<input type="text" name="cdn_css_file_extensions" value="<?php echo isset( $cdn_rewrite_sett['cdn_css_file_extensions'] ) ? $cdn_rewrite_sett['cdn_css_file_extensions'] : ''; ?>"/>
							</div>
						</div>

						<div class="field">
							<div class="field-left"><?php esc_html_e('File Extensions for JS', 'optimisationio'); ?></div>
							<div class="field-right">
								<input type="text" name="cdn_js_file_extensions" value="<?php echo isset( $cdn_rewrite_sett['cdn_js_file_extensions'] ) ? $cdn_rewrite_sett['cdn_js_file_extensions'] : ''; ?>"/>
							</div>
						</div>

					</div>

					<div data-tab-setting="optimise-db" class="addon-settings-content">
						<?php self::optimise_db_fields(); ?>
					</div>

					<div data-tab-setting="gravatars" class="addon-settings-content">
						<?php self::gravatars_fields(); ?>
					</div>

					<div data-tab-setting="nginx-helper" class="addon-settings-content">
						<?php self::nginx_general_options_page(); ?>
					</div>

				</div>

				<div class="addon-settings-actions-section">
					<input type="submit" class="button button-primary button-large" name="optimisation_save_cache_performance_settings" value="<?php echo esc_attr("Save settings", "optimisationio"); ?>" />
				</div>

				<?php wp_nonce_field( 'optimisationio-cache-preformance-settings', 'optimisationio_cache_preformance_settings' ); ?>

			</form>
		</div>
		<?php
	}

	public static function nginx_general_options_page()
	{
		global $rt_wp_nginx_helper, $rt_wp_nginx_purger;

		$update = 0;
		$error_time = false;
		$error_log_filesize = false;
		$rt_wp_nginx_helper->options['enable_purge'] = (isset( $_POST['enable_purge'] ) and ( $_POST['enable_purge'] == 1) ) ? 1 : 0;
		$rt_wp_nginx_helper->options['cache_method'] = (isset( $_POST['cache_method'] ) ) ? $_POST['cache_method'] : 'enable_fastcgi';
		$rt_wp_nginx_helper->options['enable_map'] = (isset( $_POST['enable_map'] ) and ( $_POST['enable_map'] == 1) ) ? 1 : 0;
		$rt_wp_nginx_helper->options['enable_log'] = (isset( $_POST['enable_log'] ) and ( $_POST['enable_log'] == 1) ) ? 1 : 0;
		$rt_wp_nginx_helper->options['enable_stamp'] = (isset( $_POST['enable_stamp'] ) and ( $_POST['enable_stamp'] == 1) ) ? 1 : 0;

		if ( isset( $_POST['is_submit'] ) && ( $_POST['is_submit'] == 1 ) ) {
			if ( !(!is_network_admin() && is_multisite() ) ) {
				if ( $rt_wp_nginx_helper->options['enable_log'] ) {
					if ( isset( $_POST['log_level'] ) && !empty( $_POST['log_level'] ) && $_POST['log_level'] != '' ) {
						$rt_wp_nginx_helper->options['log_level'] = $_POST['log_level'];
					} else {
						$rt_wp_nginx_helper->options['log_level'] = 'INFO';
					}
					if ( isset( $_POST['log_filesize'] ) && !empty( $_POST['log_filesize'] ) && $_POST['log_filesize'] != '' ) {
						if ( (!is_numeric( $_POST['log_filesize'] ) ) || ( empty( $_POST['log_filesize'] ) ) ) {
							$error_log_filesize = __( 'Log file size must be a number', 'nginx-helper' );
						} else {
							$rt_wp_nginx_helper->options['log_filesize'] = $_POST['log_filesize'];
						}
					} else {
						$rt_wp_nginx_helper->options['log_filesize'] = 5;
					}
				}
				if ( $rt_wp_nginx_helper->options['enable_map'] ) {
					$rt_wp_nginx_helper->update_map();
				}
			}
			if ( isset( $_POST['enable_purge'] ) ) {
				$rt_wp_nginx_helper->options['purge_homepage_on_edit'] = ( isset( $_POST['purge_homepage_on_edit'] ) and ( $_POST['purge_homepage_on_edit'] == 1 ) ) ? 1 : 0;
				$rt_wp_nginx_helper->options['purge_homepage_on_del'] = ( isset( $_POST['purge_homepage_on_del'] ) and ( $_POST['purge_homepage_on_del'] == 1 ) ) ? 1 : 0;

				$rt_wp_nginx_helper->options['purge_archive_on_edit'] = ( isset( $_POST['purge_archive_on_edit'] ) and ( $_POST['purge_archive_on_edit'] == 1 ) ) ? 1 : 0;
				$rt_wp_nginx_helper->options['purge_archive_on_del'] = ( isset( $_POST['purge_archive_on_del'] ) and ( $_POST['purge_archive_on_del'] == 1 ) ) ? 1 : 0;

				$rt_wp_nginx_helper->options['purge_archive_on_new_comment'] = ( isset( $_POST['purge_archive_on_new_comment'] ) and ( $_POST['purge_archive_on_new_comment'] == 1 ) ) ? 1 : 0;
				$rt_wp_nginx_helper->options['purge_archive_on_deleted_comment'] = ( isset( $_POST['purge_archive_on_deleted_comment'] ) and ( $_POST['purge_archive_on_deleted_comment'] == 1 ) ) ? 1 : 0;

				$rt_wp_nginx_helper->options['purge_page_on_mod'] = ( isset( $_POST['purge_page_on_mod'] ) and ( $_POST['purge_page_on_mod'] == 1 ) ) ? 1 : 0;
				$rt_wp_nginx_helper->options['purge_page_on_new_comment'] = ( isset( $_POST['purge_page_on_new_comment'] ) and ( $_POST['purge_page_on_new_comment'] == 1 ) ) ? 1 : 0;
				$rt_wp_nginx_helper->options['purge_page_on_deleted_comment'] = ( isset( $_POST['purge_page_on_deleted_comment'] ) and ( $_POST['purge_page_on_deleted_comment'] == 1 ) ) ? 1 : 0;

				$rt_wp_nginx_helper->options['purge_method'] = ( isset( $_POST['purge_method'] ) ) ? $_POST['purge_method'] : 'get_request';

                $rt_wp_nginx_helper->options['purge_url'] = ( isset( $_POST['purge_url'] ) && ! empty( $_POST['purge_url'] ) ) ? esc_textarea( $_POST['purge_url'] ) : '';
			}
			if ( isset( $_POST['cache_method'] ) && $_POST['cache_method'] = "enable_redis" ) {
				$rt_wp_nginx_helper->options['redis_hostname'] = ( isset( $_POST['redis_hostname'] ) ) ? $_POST['redis_hostname'] : '127.0.0.1';
				$rt_wp_nginx_helper->options['redis_port'] = ( isset( $_POST['redis_port'] ) ) ? $_POST['redis_port'] : '6379';
				$rt_wp_nginx_helper->options['redis_prefix'] = ( isset( $_POST['redis_prefix'] ) ) ? $_POST['redis_prefix'] : 'nginx-cache:';
			}
			update_site_option( 'rt_wp_nginx_helper_options', $rt_wp_nginx_helper->options );
			$update = 1;
		}
		$rt_wp_nginx_helper->options = get_site_option( 'rt_wp_nginx_helper_options' );

		// set default purge method to fastcgi
		if ( empty( $rt_wp_nginx_helper->options['cache_method'] ) ) {
			$rt_wp_nginx_helper->options['cache_method'] = "enable_fastcgi";
		}
		/**
		 * Show Update Message
		 */
		if ( isset( $_POST['smart_http_expire_save'] ) ) {
			echo '<div class="updated"><p>' . __( 'Settings saved.', 'nginx-helper' ) . '</p></div>';
		}

		/**
		 * Check for single multiple with subdomain OR multiple with subdirectory site
		 */
		$nginx_setting_link = '#';
		if ( is_multisite() ) {
			if ( SUBDOMAIN_INSTALL == false ) {
				$nginx_setting_link = 'https://rtcamp.com/wordpress-nginx/tutorials/multisite/subdirectories/fastcgi-cache-with-purging/';
			} else {
				$nginx_setting_link = 'https://rtcamp.com/wordpress-nginx/tutorials/multisite/subdomains/fastcgi-cache-with-purging/';
			}
		} else {
			$nginx_setting_link = 'https://rtcamp.com/wordpress-nginx/tutorials/single-site/fastcgi-cache-with-purging/';
		}
		?>
			<div class="postbox">
				<h3 class="hndle">
					<span><?php _e( 'Purging Options', 'nginx-helper' ); ?></span>
				</h3>
				<div class="inside">
					<table class="form-table">
						<tr valign="top">
							<td>
								<input type="checkbox" value="1" id="enable_purge" name="enable_purge"<?php checked( $rt_wp_nginx_helper->options['enable_purge'], 1 ); ?> />
								<label for="enable_purge"><?php _e( 'Enable Purge', 'nginx-helper' ); ?></label>
							</td>
						</tr>
					</table>
				</div> <!-- End of .inside -->
			</div>
			<div class="postbox enable_purge"<?php echo ( $rt_wp_nginx_helper->options['enable_purge'] == false ) ? ' style="display: none;"' : ''; ?>>
				<h3 class="hndle">
					<span><?php _e( 'Caching Method', 'nginx-helper' ); ?></span>
				</h3>
				<?php if ( !(!is_network_admin() && is_multisite() ) ) { ?>
					<div class="inside">
						<input type="hidden" name="is_submit" value="1" />
						<table class="form-table">
							<tr valign="top">
								<td>
									<input type="radio" value="enable_fastcgi" id="cache_method_fastcgi" name="cache_method" <?php checked( $rt_wp_nginx_helper->options['cache_method'], "enable_fastcgi" ); ?> />
									<label for="cache_method_fastcgi">
										<?php printf( __( 'nginx Fastcgi cache (<a target="_blank" href="%s" title="External settings for nginx">requires external settings for nginx</a>)', 'nginx-helper' ), $nginx_setting_link ); ?>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<td>
									<input type="radio" value="enable_redis" id="cache_method_redis" name="cache_method" <?php checked( $rt_wp_nginx_helper->options['cache_method'], "enable_redis" ); ?> />
									<label for="cache_method_redis">
										<?php printf( __( 'Redis cache', 'nginx-helper' ) ); ?>
									</label>
								</td>
							</tr>
						</table>
					</div> <!-- End of .inside -->
				</div>
				<div class="enable_purge">
					<div class="postbox cache_method_fastcgi"<?php echo ( $rt_wp_nginx_helper->options['enable_purge'] == true && $rt_wp_nginx_helper->options['cache_method'] == "enable_fastcgi" ) ? '' : ' style="display: none;"'; ?>>
						<h3 class="hndle">
							<span><?php _e( 'Purge Method', 'nginx-helper' ); ?></span>
						</h3>
						<div class="inside">
							<table class="form-table rtnginx-table">
								<tr valign="top">
									<td>
										<fieldset>
											<legend class="screen-reader-text">
												<span>&nbsp;<?php _e( 'when a post/page/custom post is published.', 'nginx-helper' ); ?></span>
											</legend>
											<label for="purge_method_get_request">
												<input type="radio" value="get_request" id="purge_method_get_request" name="purge_method"<?php checked( isset( $rt_wp_nginx_helper->options['purge_method'] ) ? $rt_wp_nginx_helper->options['purge_method'] : 'get_request', 'get_request' ); ?>>
												&nbsp;<?php _e( 'Using a GET request to <strong>PURGE/url</strong> (Default option)', 'nginx-helper' ); ?><br />
												<small><?php _e( 'Uses the <strong><a href="https://github.com/FRiCKLE/ngx_cache_purge">ngx_cache_purge</a></strong> module. ', 'nginx-helper' ); ?></small>
											</label><br />
											<label for="purge_method_unlink_files">
												<input type="radio" value="unlink_files" id="purge_method_unlink_files" name="purge_method"<?php checked( isset( $rt_wp_nginx_helper->options['purge_method'] ) ? $rt_wp_nginx_helper->options['purge_method'] : '', 'unlink_files' ); ?>>
												&nbsp;<?php _e( 'Delete local server cache files', 'nginx-helper' ); ?><br />
												<small><?php _e( 'Checks for matching cache file in <strong>RT_WP_NGINX_HELPER_CACHE_PATH</strong>. Does not require any other modules. Requires that the cache be stored on the same server as WordPress. You must also be using the default nginx cache options (levels=1:2) and (fastcgi_cache_key "$scheme$request_method$host$request_uri"). ', 'nginx-helper' ); ?></small>

											</label><br />
										</fieldset>
									</td>
								</tr>
							</table>
						</div> <!-- End of .inside -->
					</div>
					<div class="postbox cache_method_redis"<?php echo ( $rt_wp_nginx_helper->options['enable_purge'] == true && $rt_wp_nginx_helper->options['cache_method'] == "enable_redis" ) ? '' : ' style="display: none;"'; ?>>
						<h3 class="hndle">
							<span><?php _e( 'Redis Settings', 'nginx-helper' ); ?></span>
						</h3>
						<div class="inside">
							<table class="form-table rtnginx-table">
								<?php
								$redis_hostname = ( empty( $rt_wp_nginx_helper->options['redis_hostname'] ) ) ? '127.0.0.1' : $rt_wp_nginx_helper->options['redis_hostname'];
								$redis_port = ( empty( $rt_wp_nginx_helper->options['redis_port'] ) ) ? '6379' : $rt_wp_nginx_helper->options['redis_port'];
								$redis_prefix = ( empty( $rt_wp_nginx_helper->options['redis_prefix'] ) ) ? 'nginx-cache:' : $rt_wp_nginx_helper->options['redis_prefix'];
								?>
								<tr>
									<th><label for="redis_hostname"><?php _e( 'Hostname', 'nginx-helper' ); ?></label></th>
									<td>
										<input id="redis_hostname" class="medium-text" type="text" name="redis_hostname" value="<?php echo $redis_hostname; ?>" />
									</td>
								</tr>
								<tr>
									<th><label for="redis_port"><?php _e( 'Port', 'nginx-helper' ); ?></label></th>
									<td>
										<input id="redis_port" class="medium-text" type="text" name="redis_port" value="<?php echo $redis_port; ?>" />
									</td>
								</tr>
								<tr>
									<th><label for="redis_prefix"><?php _e( 'Prefix', 'nginx-helper' ); ?></label></th>
									<td>
										<input id="redis_prefix" class="medium-text" type="text" name="redis_prefix" value="<?php echo $redis_prefix; ?>" />
									</td>
								</tr>
							</table>
						</div> <!-- End of .inside -->
					</div>
				</div>
				<div class="postbox enable_purge"<?php echo ( $rt_wp_nginx_helper->options['enable_purge'] == false ) ? ' style="display: none;"' : ''; ?>>
					<h3 class="hndle">
						<span><?php _e( 'Purging Conditions', 'nginx-helper' ); ?></span>
					</h3>
					<div class="inside">

						<table class="form-table rtnginx-table">
							<tr valign="top">
								<th scope="row"><h4><?php _e( 'Purge Homepage:', 'nginx-helper' ); ?></h4></th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when a post/page/custom post is modified or added.', 'nginx-helper' ); ?></span>
									</legend>
									<label for="purge_homepage_on_edit">
										<input type="checkbox" value="1" id="purge_homepage_on_edit" name="purge_homepage_on_edit"<?php checked( $rt_wp_nginx_helper->options['purge_homepage_on_edit'], 1 ); ?> />
										&nbsp;<?php _e( 'when a <strong>post</strong> (or page/custom post) is <strong>modified</strong> or <strong>added</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when an existing post/page/custom post is modified.', 'nginx-helper' ); ?></span>
									</legend>
									<label for="purge_homepage_on_del">
										<input type="checkbox" value="1" id="purge_homepage_on_del" name="purge_homepage_on_del"<?php checked( $rt_wp_nginx_helper->options['purge_homepage_on_del'], 1 ); ?> />
										&nbsp;<?php _e( 'when a <strong>published post</strong> (or page/custom post) is <strong>trashed</strong>.', 'nginx-helper' ); ?></label><br />
								</fieldset>
							</td>
							</tr>
						</table>
						<table class="form-table rtnginx-table">
							<tr valign="top">
								<th scope="row">
							<h4><?php _e( 'Purge Post/Page/Custom Post Type:', 'nginx-helper' ); ?></h4>
							</th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when a post/page/custom post is published.', 'nginx-helper' ); ?></span>
									</legend>
									<label for="purge_page_on_mod">
										<input type="checkbox" value="1" id="purge_page_on_mod" name="purge_page_on_mod"<?php checked( $rt_wp_nginx_helper->options['purge_page_on_mod'], 1 ); ?>>
										&nbsp;<?php _e( 'when a <strong>post</strong> is <strong>published</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when a comment is approved/published.', 'nginx-helper' ); ?></span>
									</legend>
									<label for="purge_page_on_new_comment">
										<input type="checkbox" value="1" id="purge_page_on_new_comment" name="purge_page_on_new_comment"<?php checked( $rt_wp_nginx_helper->options['purge_page_on_new_comment'], 1 ); ?>>
										&nbsp;<?php _e( 'when a <strong>comment</strong> is <strong>approved/published</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when a comment is unapproved/deleted.', 'nginx-helper' ); ?></span>
									</legend>
									<label for="purge_page_on_deleted_comment">
										<input type="checkbox" value="1" id="purge_page_on_deleted_comment" name="purge_page_on_deleted_comment"<?php checked( $rt_wp_nginx_helper->options['purge_page_on_deleted_comment'], 1 ); ?>>
										&nbsp;<?php _e( 'when a <strong>comment</strong> is <strong>unapproved/deleted</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>
							</td>
							</tr>
						</table>
						<table class="form-table rtnginx-table">
							<tr valign="top">
								<th scope="row">
							<h4><?php _e( 'Purge Archives:', 'nginx-helper' ); ?></h4>
							<small><?php _e( '(date, category, tag, author, custom taxonomies)', 'nginx-helper' ); ?></small>
							</th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when an post/page/custom post is modified or added.</span>', 'nginx-helper' ); ?>
									</legend>
									<label for="purge_archive_on_edit">
										<input type="checkbox" value="1" id="purge_archive_on_edit" name="purge_archive_on_edit"<?php checked( $rt_wp_nginx_helper->options['purge_archive_on_edit'], 1 ); ?> />
										&nbsp;<?php _e( 'when a <strong>post</strong> (or page/custom post) is <strong>modified</strong> or <strong>added</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when an existing post/page/custom post is trashed.</span>', 'nginx-helper' ); ?>
									</legend>
									<label for="purge_archive_on_del">
										<input type="checkbox" value="1" id="purge_archive_on_del" name="purge_archive_on_del"<?php checked( $rt_wp_nginx_helper->options['purge_archive_on_del'], 1 ); ?> />
										&nbsp;<?php _e( 'when a <strong>published post</strong> (or page/custom post) is <strong>trashed</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>
								<br />
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when a comment is approved/published.</span>', 'nginx-helper' ); ?>
									</legend>
									<label for="purge_archive_on_new_comment">
										<input type="checkbox" value="1" id="purge_archive_on_new_comment" name="purge_archive_on_new_comment"<?php checked( $rt_wp_nginx_helper->options['purge_archive_on_new_comment'], 1 ); ?> />
										&nbsp;<?php _e( 'when a <strong>comment</strong> is <strong>approved/published</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>
								<fieldset>
									<legend class="screen-reader-text">
										<span>&nbsp;<?php _e( 'when a comment is unapproved/deleted.</span>', 'nginx-helper' ); ?>
									</legend>
									<label for="purge_archive_on_deleted_comment">
										<input type="checkbox" value="1" id="purge_archive_on_deleted_comment" name="purge_archive_on_deleted_comment"<?php checked( $rt_wp_nginx_helper->options['purge_archive_on_deleted_comment'], 1 ); ?> />
										&nbsp;<?php _e( 'when a <strong>comment</strong> is <strong>unapproved/deleted</strong>.', 'nginx-helper' ); ?>
									</label><br />
								</fieldset>

							</td>
							</tr>
						</table>
                        <table class="form-table rtnginx-table">
							<tr valign="top">
								<th scope="row">
                                    <h4><?php _e( 'Custom Purge URL:', 'nginx-helper' ); ?></h4>
                                </th>
                                <td>
                                    <textarea rows="5"class="rt-purge_url" id="purge_url" name="purge_url"><?php echo isset( $rt_wp_nginx_helper->options['purge_url'] ) ? $rt_wp_nginx_helper->options['purge_url'] : '';?></textarea>
                                    <p class="description">
                                        Add one URL per line. URL should not contain domain name.
                                        <br>
                                        Eg: To purge http://example.com/sample-page/ add <strong>/sample-page/</strong> in above textarea.
                                        <br>
                                        '*' will only work with redis cache server.
                                    </p>
                                </td>
                            </tr>
                        </table>
					</div> <!-- End of .inside -->
				</div>
				<div class="postbox">
					<h3 class="hndle">
						<span><?php _e( 'Debug Options', 'nginx-helper' ); ?></span>
					</h3>
					<div class="inside">
						<input type="hidden" name="is_submit" value="1" />
						<table class="form-table">
							<?php if ( is_network_admin() ) { ?>
								<tr valign="top">
									<td>
										<input type="checkbox" value="1" id="enable_map" name="enable_map"<?php checked( $rt_wp_nginx_helper->options['enable_map'], 1 ); ?> />
										<label for="enable_map"><?php _e( 'Enable Nginx Map.', 'nginx-helper' ); ?></label>
									</td>
								</tr>
							<?php } ?>
							<tr valign="top">
								<td>
									<input type="checkbox" value="1" id="enable_log" name="enable_log"<?php checked( $rt_wp_nginx_helper->options['enable_log'], 1 ); ?> />
									<label for="enable_log"><?php _e( 'Enable Logging', 'nginx-helper' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<td>
									<input type="checkbox" value="1" id="enable_stamp" name="enable_stamp"<?php checked( $rt_wp_nginx_helper->options['enable_stamp'], 1 ); ?> />
									<label for="enable_stamp"><?php _e( 'Enable Nginx Timestamp in HTML', 'nginx-helper' ); ?></label>
								</td>
							</tr>
						</table>
					</div> <!-- End of .inside -->
				</div>
				<?php
			} // End of if ( !( !is_network_admin() && is_multisite() ) )


			if ( is_network_admin() ) {
				?>
				<div class="postbox enable_map"<?php echo ( $rt_wp_nginx_helper->options['enable_map'] == false ) ? ' style="display: none;"' : ''; ?>>
					<h3 class="hndle">
						<span><?php _e( 'Nginx Map', 'nginx-helper' ); ?></span>
					</h3>
					<div class="inside"><?php if ( !is_writable( $rt_wp_nginx_helper->functional_asset_path() . 'map.conf' ) ) { ?>
							<span class="error fade" style="display: block"><p><?php printf( __( 'Can\'t write on map file.<br /><br />Check you have write permission on <strong>%s</strong>', 'nginx-helper' ), $rt_wp_nginx_helper->functional_asset_path() . 'map.conf' ); ?></p></span><?php }
				?>

						<table class="form-table rtnginx-table">
							<tr>
								<th><?php _e( 'Nginx Map path to include in nginx settings<br /><small>(recommended)</small>', 'nginx-helper' ); ?></th>
								<td>
									<pre><?php echo $rt_wp_nginx_helper->functional_asset_path() . 'map.conf'; ?></pre>
								</td>
							</tr>
							<tr>
								<th><?php _e( 'Or,<br />Text to manually copy and paste in nginx settings<br /><small>(if your network is small and new sites are not added frequently)</small>', 'nginx-helper' ); ?></th>
								<td>
									<pre id="map"><?php echo $rt_wp_nginx_helper->get_map() ?></pre>
								</td>
							</tr>
						</table>
					</div> <!-- End of .inside -->
				</div>
			<?php } ?>

			<div class="postbox enable_log"<?php echo ( $rt_wp_nginx_helper->options['enable_log'] == false ) ? ' style="display: none;"' : ''; ?>>
				<h3 class="hndle">
					<span><?php _e( 'Logging Options', 'nginx-helper' ); ?></span>
				</h3>
				<div class="inside">
					<?php
					$path = $rt_wp_nginx_helper->functional_asset_path();
					if ( !is_dir( $path ) ) {
						mkdir( $path );
					}
					if ( !file_exists( $path . 'nginx.log' ) ) {
						$log = fopen( $path . 'nginx.log', 'w' );
						fclose( $log );
					}

					if ( !is_writable( $path . 'nginx.log' ) ) {
						?>
						<span class="error fade" style="display : block"><p><?php printf( __( 'Can\'t write on log file.<br /><br />Check you have write permission on <strong>%s</strong>', 'nginx-helper' ), $rt_wp_nginx_helper->functional_asset_path() . 'nginx.log' ); ?></p></span><?php }
					?>

					<table class="form-table rtnginx-table">
						<tbody>
							<tr>
								<th><label for="rt_wp_nginx_helper_logs_path"><?php _e( 'Logs path', 'nginx-helper' ); ?></label></th>
								<td><pre><?php echo $rt_wp_nginx_helper->functional_asset_path(); ?>nginx.log</pre></td>
							</tr>
							<tr>
								<th><label for="rt_wp_nginx_helper_logs_link"><?php _e( 'View Log', 'nginx-helper' ); ?></label></th>
								<td><a target="_blank" href="<?php echo $rt_wp_nginx_helper->functional_asset_url(); ?>nginx.log"><?php _e( 'Log', 'nginx-helper' ); ?></a></td>
							</tr>
							<tr>
								<th><label for="rt_wp_nginx_helper_log_level"><?php _e( 'Log level', 'nginx-helper' ); ?></label></th>
								<td>
									<select name="log_level">
										<option value="NONE"<?php selected( $rt_wp_nginx_helper->options['log_level'], 'NONE' ); ?>><?php _e( 'None', 'nginx-helper' ); ?></option>
										<option value="INFO"<?php selected( $rt_wp_nginx_helper->options['log_level'], 'INFO' ); ?>><?php _e( 'Info', 'nginx-helper' ); ?></option>
										<option value="WARNING"<?php selected( $rt_wp_nginx_helper->options['log_level'], 'WARNING' ); ?>><?php _e( 'Warning', 'nginx-helper' ); ?></option>
										<option value="ERROR"<?php selected( $rt_wp_nginx_helper->options['log_level'], 'ERROR' ); ?>><?php _e( 'Error', 'nginx-helper' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="log_filesize"><?php _e( 'Max log file size', 'nginx-helper' ); ?></label></th>
								<td>
									<input id="log_filesize" class="small-text" type="text" name="log_filesize" value="<?php echo $rt_wp_nginx_helper->options['log_filesize'] ?>" /> <?php
									_e( 'Mb', 'nginx-helper' );
									if ( $error_log_filesize ) {
										?>
										<p class="error fade" style="display: block;"><?php echo $error_log_filesize; ?></p><?php }
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</div> <!-- End of .inside -->
			</div>

			<script>
				jQuery( document ).ready( function() {
					var news_section = jQuery( '#latest_news' );
					if ( news_section.length > 0 ) {
						jQuery.get( news_url, function( data ) {
							news_section.find( '.inside' ).html( data );
						} );
					}

					jQuery( "form#purgeall a" ).click( function( e ) {
						if ( confirm( "Purging entire cache is not recommended. Would you like to continue ?" ) == true ) {
							// continue submitting form
						} else {
							e.preventDefault();
						}

					} );

					/**
					* Show OR Hide options on option checkbox
					* @param {type} selector Selector of Checkbox and PostBox
					*/
					function nginx_show_option( selector ) {
						jQuery( '#' + selector ).on( 'change', function() {
							if ( jQuery( this ).is( ':checked' ) ) {
								jQuery( '.' + selector ).show();
								if ( selector == "cache_method_redis" ) {
									jQuery( '.cache_method_fastcgi' ).hide();
								} else if ( selector == "cache_method_fastcgi" ) {
									jQuery( '.cache_method_redis' ).hide();
								}
							} else {
								jQuery( '.' + selector ).hide();
							}
						} );
					}
					/* Function call with parameter */
					nginx_show_option( 'cache_method_fastcgi' );
					nginx_show_option( 'cache_method_redis' );
					nginx_show_option( 'enable_map' );
					nginx_show_option( 'enable_log' );
					nginx_show_option( 'enable_purge' );
				} );
			</script>
		<?php
	}
}
