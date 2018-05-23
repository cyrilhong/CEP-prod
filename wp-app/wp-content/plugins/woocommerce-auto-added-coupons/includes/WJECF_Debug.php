<?php

defined('ABSPATH') or die();

/**
 * Debugging functions for wjecf
 *
 * @since 2.6.0
 */
class WJECF_Debug extends Abstract_WJECF_Plugin  {    

    private $enable_logging = false;
    
    public function __construct() {    
        $this->set_plugin_data( array(
            'description' => __( 'Debugging methods for WooCommerce Extended Coupon Features.', 'woocommerce-jos-autocoupon' ),
            'dependencies' => array(),
            'can_be_disabled' => false,
            'hidden' => false
        ) );
    }
    
    public function init_hook() {
        $this->enable_logging = true;

        add_action( 'wp_loaded', array( $this, 'handle_querystring' ), 90 );
        add_action( 'wp_footer', array( $this, 'render_log' ) ); //Log
    }
    
    public function init_admin_hook() {
        if ( current_user_can( 'manage_options' ) && $this->debug_mode() )
        {
            if ( $wjecf_admin = WJECF()->get_plugin('WJECF_Admin') ) {
                $msg = __( 'Debug mode is enabled. Please disable it when you\'re done debugging.', 'woocommerce-jos-autocoupon' );
                $wjecf_admin_settings = WJECF()->get_plugin('WJECF_Admin_Settings');
                if ($wjecf_admin_settings) {
                    $msg .= ' <a href="' . $wjecf_admin_settings    ->get_settings_page_url() . '">' . __( 'Go to settings page', 'woocommerce-jos-autocoupon' ) . '</a>';
                }                
                $wjecf_admin->enqueue_notice( $msg, 'notice-warning' );
            }
            add_action( 'wjecf_coupon_metabox_misc', array( $this, 'wjecf_coupon_metabox_misc' ), 20, 2 );
        }
    }
        
// ========================
// ACTION HOOKS
// ========================

    public function wjecf_coupon_metabox_misc( $thepostid, $post ) {            
        echo "<h3>" . esc_html( __( 'Debug', 'woocommerce-jos-autocoupon' ) ). "</h3>\n";

        $coupon_code = WJECF_Wrap( WJECF_WC()->get_coupon( $post ) )->get_code();
        $url = add_query_arg( array( 'wjecf_dump' => $coupon_code ), get_site_url() );
        $text = __( 'Coupon data as json', 'woocommerce-jos-autocoupon');
        echo '<p>';
        printf( '<a href="%s" target="_blank">%s</a>', $url, $text );
        echo '</p>';
    }

    /**
     * Output the log as html
     */
    public function render_log() {        
        if ( ! $this->debug_mode() && ! current_user_can( 'manage_options' ) ) return;        
        if ( ! WJECF()->get_session('wjecf_log') ) return;

        $this->log( 'debug', "Session: " . print_r( WJECF()->get_session(), true ) );
        $this->log( 'debug', 'Current coupons in cart: ' . implode( ", ", WC()->cart->applied_coupons ) );

        if ( $this->log_output ) {
            WJECF()->include_template( 'debug/log.php', array( 'log' => $this->log_output ) );
        }
    }    
    
    /**
     * Query argument 'wjecf_debug' toggles rendering of the debug-log for any user (only allowed when debug mode is enabled)
     * @return type
     */
    public function handle_querystring() {
        $this->handle_querystring_wjecf_log();
        $this->handle_querystring_wjecf_dump_coupon();
    }
    
    public function handle_querystring_wjecf_log() {
        // wjecf_log=1 / 0  Enable log on the frontend for guest users
        if ( isset( $_GET['wjecf_log'] ) ) {
            WJECF()->set_session( 'wjecf_log', $_GET['wjecf_log'] ? true : null );        
        }
    }
    
    public function handle_querystring_wjecf_dump_coupon() {
        // wjecf_dump_coupon=coupon_code    Dump the coupon data on the frontend
        if ( ! current_user_can( 'manage_options' ) && ! $this->debug_mode() ) return;
        
        if ( isset( $_GET['wjecf_dump'] ) ) {
            $coupon_code = $_GET['wjecf_dump'];
            $coupon = new WC_Coupon( $coupon_code );
            $wrap_coupon = WJECF_Wrap( $coupon );
            $coupon_id = $wrap_coupon->get_id();
            
            $array = array( 'result' => 'error' );
            if ( $coupon_id ) {                
                $meta = array();
                foreach( array_keys( get_post_meta( $coupon_id ) ) as $key ) {
                    $meta[$key] = $wrap_coupon->get_meta( $key );
                }
                ksort( $meta );
                $array['coupons'] = array(
                    'coupon_id' => $coupon_id,
                    'coupon_code' => $coupon->get_code(),
                    'meta' => $meta,
                );
                $array['result'] = 'ok';
            } else {
            }
            header( 'Content-Type: application/json' );
            echo json_encode( $array );
            die();
        }
    }

    /**
     * Is debug mode enabled?
     * 
     * @since 2.6.0
     * @return bool
     */
    public function debug_mode() {
        return WJECF()->get_option('debug_mode');
    }
    

// ========================
// LOGGING
// ========================

    private $debug_mode = false;
    private $log_output = array();

    /**
     * Log a message for debugging.
     * 
     * If debug_mode is false; messages with level 'debug' will be ignored.
     * 
     * @param string $level The level of the message. e.g. 'debug' or 'warning'
     * @param string $string The message to log
     * @param int $skip_backtrace Defaults to 0, amount of items to skip in backtrace to fetch class and method name
     */
    public function log( $level, $message = null, $skip_backtrace = 0 ) {
        if ( ! $this->enable_logging ) return;
        
        if ( ! $this->debug_mode() && $level === 'debug' ) return;

        //Backwards compatibility; $level was introduced in 2.4.4
        if ( $message == null ) {
            $message = $level;
            $level = 'debug';
        } else if ( is_int( $message ) && ! $this->is_valid_log_level( $level ) ) {
            $skip_backtrace  = $message;
            $message = $level;
            $level = 'debug';
        }

        if ( ! $this->debug_mode() && $level === 'debug' ) return;

        $nth = 1 + $skip_backtrace;
        $bt = debug_backtrace();
        $class = $bt[$nth]['class'];
        $function = $bt[$nth]['function'];

        $row = array(
            'level' => $level,
            'time' => time(),
            'class' => $class,
            'function' => $function,
            'filter' => current_filter(),
            'message' => $message,
        );

        $nice_str = $row['filter'] . '   ' . $row['class'] . '::' . $row['function'] . '   ' . $row['message'];

        //Since WC2.7
        if ( function_exists( 'wc_get_logger' ) ) {
            $logger = wc_get_logger();
            $context = array( 'source' => 'WooCommerce Extended Coupon Features' );
            $logger->log( $level, $nice_str, $context );
            if ( $level !== 'debug' ) error_log( 'WooCommerce Extended Coupon Features ' . $level . ': ' . $row['message'] );
        } else {
            //Legacy
            error_log( $level . ': ' . $nice_str );
        }

        $this->log_output[] = $row;
    }
    
    private function is_valid_log_level( $level ) {
        return in_array( $level, array( 'debug', 'informational', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ) );
    }

}