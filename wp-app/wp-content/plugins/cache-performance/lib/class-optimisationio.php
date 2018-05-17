<?php
class Optimisationio {

	private static $instance = false;

	const MIN_PHP_VERSION = '5.2.4';
	const MIN_WP_VERSION = '4.3';
	const TEXT_DOMAIN = 'optimisationio';
	const OPTION_KEY = 'Optimisationio_rev3a';
	const FILE = __FILE__;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 * Initializes the plugin by setting localization, filters, and
	 * administration functions.
	 */
	private function __construct() {
		
		if ( ! $this->test_host() ) {
			return;
		}

		if( ! class_exists('Optimisationio_Dashboard') ){
			require_once 'class-optimisationio-dashboard.php';
		}

		Optimisationio_Dashboard::init();

		new Optimisationio_Admin;
		new Optimisationio_CdnRewrite;

		add_action( 'init', array( $this, 'text_domain' ) );

		Optimisationio_CacheEnabler::get_instance();

		add_action( 'wp_ajax_optimisationio_page_load_time', array( $this, 'ajax_page_load_time' ) );
		add_action( 'wp_ajax_nopriv_optimisationio_page_load_time', array( $this, 'ajax_page_load_time' ) );

		if( 1 === (int) get_option( 'Optimisationio_track_page_load_time' ) ){
			add_action( 'wp_footer', array($this, 'measure_page_load_time') );	
		}
	}

	public function ajax_page_load_time(){

		$post_req = $_POST;	// Input var okay.

		$pages_load_time = get_option( 'wpperformance_rev3a_pages_load_time' );

		if( $pages_load_time ){
			$count = max( 0, $pages_load_time['count'] );
			$average = max( 1, $pages_load_time['average'] );

			$new_count = $count + 1;
			$new_average = ( $average * ( $count / $new_count ) ) + ( max( 1, $post_req['time'] ) * ( 1 / $new_count ) );
			
			$val = array(
				'count' => $new_count,
				'average' => $new_average,
			);

			update_option( 'wpperformance_rev3a_pages_load_time', $val );
		}
		else{
			$val = array(
				'count' => 1,
				'average' => max( 1, $post_req['time'] ),
			);

			update_option( 'wpperformance_rev3a_pages_load_time', $val );
		}

		wp_send_json( $val );
	}

	public function measure_page_load_time(){
		if( ! is_admin() ){?>
			<script type="text/javascript">
				window.onload = function(){
					setTimeout(function(){
						if('undefined' !== typeof performance && 'undefined'!== typeof performance.timing){
							var t = performance.timing;
							jQuery.ajax({
								type: 'post',
								url: <?php echo "'".admin_url('admin-ajax.php')."'"; ?>,
								data:{
									action: 'optimisationio_page_load_time',
									time: t.loadEventEnd - t.navigationStart
								}
							});
						}
					}, 0);
				};
			</script><?php
		}
	}

	public static function average_pages_load_time(){
		$data = get_option( 'wpperformance_rev3a_pages_load_time' );
		return $data && isset($data['average']) ? round($data['average']/1000,3).' s' : false;
	}

	/**
	 * Loads the plugin text domain for translation
	 */
	public function text_domain() {
		load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
		);
	}

	// -------------------------------------------------------------------------
	// Environment Checks
	// -------------------------------------------------------------------------
	/**
	 * Checks PHP and WordPress versions.
	 */
	private function test_host() {
		// Check if PHP is too old.
		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			// Display notice.
			add_action( 'admin_notices', array( &$this, 'php_version_error' ) );
			return false;
		}

		// Check if WordPress is too old.
		global $wp_version;
		if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( &$this, 'wp_version_error' ) );
			return false;
		}
		return true;
	}

	/**
	 * Displays a warning when installed on an old PHP version.
	 */
	public function php_version_error() {
		echo '<div class="error"><p><strong>';
		printf(
			'Error: %3$s requires PHP version %1$s or greater.<br/>' .
			'Your installed PHP version: %2$s',
			self::MIN_PHP_VERSION,
			PHP_VERSION,
			$this->get_plugin_name()
		);
		echo '</strong></p></div>';
	}

	/**
	 * Displays a warning when installed in an old Wordpress version.
	 */
	public function wp_version_error() {
		echo '<div class="error"><p><strong>';
		printf(
			'Error: %2$s requires WordPress version %1$s or greater.',
			self::MIN_WP_VERSION,
			$this->get_plugin_name()
		);
		echo '</strong></p></div>';
	}

	/**
	 * Get the name of this plugin.
	 *
	 * @return string The plugin name.
	 */
	private function get_plugin_name() {
		$data = get_plugin_data( self::FILE );
		return $data['Name'];
	}
}
