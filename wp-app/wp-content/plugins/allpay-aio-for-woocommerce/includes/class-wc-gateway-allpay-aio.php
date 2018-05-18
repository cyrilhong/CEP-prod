<?php
/**
 * WC_innovext_allpay_aio class.
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class WC_innovext_allpay_aio extends WC_Payment_Gateway {

    // define Allpay checkout url
    const ALLPAY_CHECKOUT_PRODUCTION = 'https://payment.allpay.com.tw/Cashier/AioCheckOut';
    const ALLPAY_CHECKOUT_TEST = 'https://payment-stage.allpay.com.tw/Cashier/AioCheckOut';
    // define Allpay inquery url
    const ALLPAY_INQUERY_PRODUCTION = 'https://payment.allpay.com.tw/Cashier/QueryTradeInfo/V3';
    const ALLPAY_INQUERY_TEST = 'https://payment-stage.allpay.com.tw/Cashier/QueryTradeInfo/V3';
    // define Allpay checkout url
    const ECPAY_CHECKOUT_PRODUCTION = 'https://payment.ecpay.com.tw/Cashier/AioCheckOut';
    const ECPAY_CHECKOUT_TEST = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut';
    // define Ecpay inquery url
    const ECPAY_INQUERY_PRODUCTION = 'https://payment.ecpay.com.tw/Cashier/QueryTradeInfo/V3';
    const ECPAY_INQUERY_TEST = 'https://payment-stage.ecpay.com.tw/Cashier/QueryTradeInfo/V3';

    /** @var bool Whether or not logging is enabled */
    public static $log_enabled = false;

    /** @var WC_Logger Logger instance */
    public static $log = false;

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->id = 'innovext_allpay_aio';
        $this->has_fields = false;
        $this->method_title = '歐付寶/綠界全方位金流';

        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option('title');
        $this->pay_use = $this->get_option('pay_use', '1');
        $this->description = $this->get_option('description');
        $this->instruction_succ = $this->get_option('instruction_succ');
        $this->instruction_on_hold = $this->get_option('instruction_on_hold');
        $this->expire_date = $this->get_option('ExpireDate');
        $this->exclude_payments = $this->get_option('exclude_payments');
        //make sure the prefix does not contain TS
        $this->order_prefix = str_replace('TS', '', $this->get_option('order_prefix'));
        $this->min_amount = $this->get_option('min_amount');
        $this->reduce_stock = $this->get_option('reduce_stock');
        $this->english = $this->get_option('english');
        $this->query_trade_info = $this->get_option('query_allpay_trade_info');
        $this->payment_method_label = $this->get_option('payment_method_label');
        $this->mer_id = $this->get_option('MerchantID');
        $this->hash_key = $this->get_option('hash_key');
        $this->hash_iv = $this->get_option('hash_iv');
        $this->testmode = $this->get_option('testmode');
        $this->admin_mode = $this->get_option('admin_mode');
        $this->debug = $this->get_option('debug');

        if ($this->pay_use !== '2') {
            $this->icon = apply_filters('inno_woocommerce_allpay_icon', plugins_url('../icon/allpay-logo.png', __FILE__));
        } else {
            $this->icon = apply_filters('inno_woocommerce_ecpay_icon', plugins_url('../icon/ecpay-logo.png', __FILE__));
        }

        $this->payment_type = 'aio';
        $this->notify_url = WC()->api_request_url('WC_innovext_allpay_aio');

        $this->allpay_checkout_gateway = ( $this->testmode == 'yes' ) ? self::ALLPAY_CHECKOUT_TEST : self::ALLPAY_CHECKOUT_PRODUCTION;
        $this->ecpay_checkout_gateway = ( $this->testmode == 'yes' ) ? self::ECPAY_CHECKOUT_TEST : self::ECPAY_CHECKOUT_PRODUCTION;

        $this->allpay_inquery_url = ( $this->testmode == 'yes' ) ? self::ALLPAY_INQUERY_TEST : self::ALLPAY_INQUERY_PRODUCTION;
        $this->ecpay_inquery_url = ( $this->testmode == 'yes' ) ? self::ECPAY_INQUERY_TEST : self::ECPAY_INQUERY_PRODUCTION;

        self::$log_enabled = $this->debug;

        if ('yes' == $this->testmode) {
            $this->title .= '|測試';
        }

        if ('yes' == $this->admin_mode) {
            $this->title .= '|管理員';
        }

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_api_wc_' . $this->id, array($this, 'check_allpay_response'));
        add_action('valid_allpay_ipn_request', array($this, 'successful_request'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'allpay_admin_order_data_after_billing_address'), 10, 1);

        //add_action( 'woocommerce_thankyou_'. $this->id, array( $this, 'thankyou_page' ) );
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'thankyou_order_received_text'), 10, 2);

        // add query order metabox
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_query_trade_info_meta_box'), 10, 1);

        // Customer Emails
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 2);
    }

    /**
     * Logging method.
     * @param string $message
     * @param array $array
     */
    public static function log($message, $array = '') {
        if (self::$log_enabled) {
            if (empty(self::$log)) {
                self::$log = new WC_Logger();
            }
            if ($array && is_array($array)) {
                ob_start();
                print_r($array);
                $stringify_array = ob_get_clean();
                $message .= PHP_EOL . $stringify_array;
            }
            self::$log->add('allpay_aio', $message);
        }
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
        $this->form_fields = require( 'allpay-ecpay-settings.php' );
    }

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     *
     * @access public
     * @return void
     */
    public function admin_options() {

        echo '<h3>歐付寶/綠界全方位金流</h3>';
        $this->display_allpay_admin_notice();

        echo '<table class="form-table">';
        // Generate the HTML For the settings form.
        $this->generate_settings_html();
        echo '</table>';
    }

    /**
     * Display admin notice if option is set to admin mode or test mode
     *
     * @access public
     * @return void
     */
    function display_allpay_admin_notice() {

        if ($this->testmode == 'yes') {
            echo '<div class="error">';
            echo '<p>現正開啟測試模式</p>';
            echo '</div>';
        }

        if ($this->admin_mode == 'yes') {
            echo '<div class="error">';
            echo '<p>現正開啟管理員模式</p>';
            echo '</div>';
        }
    }

    /**
     * Get allpay Args for passing to allpay
     *
     * @access public
     * @param mixed $order
     * @return array
     */
    function get_allpay_args($order) {

        $this->log('Generating payment form for order #' . $order->get_id() . '. Notify URL: ' . $this->notify_url);

        $total_amount = round($order->get_total());

        // Get order items
        if (sizeof($order->get_items()) > 0) {

            $item_names = '';

            foreach ($order->get_items() as $item) {
                if ($item['qty']) {

                    /**
                     * because the product line will be splitted by # after submmited to allpay,
                     * so we have to replace the hash '#' to muilti byte one if the product title
                     * contains #. And also convert special characters to prevent any error.
                     */
                    $item_name = self::convert_special_char_to_multibyte($item['name']);
                    $item_names .= $item_name . ' x ' . $item['qty'] . '#';
                }
            }
            $item_names = rtrim($item_names, '#');
        }

        // get excluded payment methods
        $ignore_payment = '';
        if ($this->exclude_payments) {
            foreach ($this->exclude_payments as $exclude_payment) {
                $ignore_payment .= $exclude_payment . '#';
            }
            $ignore_payment = rtrim($ignore_payment, '#');
        }

        $expire_date = $this->expire_date;

        $this->log('Item Name : ' . $item_names);

        $mer_id = $this->mer_id;
        $payment_type = $this->payment_type;
        $return_url = $this->get_return_url($order);
        $notify_url = $this->notify_url;
        $english = $this->english;

        $allpay_args = array(
            'MerchantID' => $mer_id,
            'MerchantTradeNo' => $this->generate_merchant_trade_no($order->get_id()),
            'MerchantTradeDate' => current_time('Y/m/d H:i:s'),
            'PaymentType' => $payment_type,
            'TotalAmount' => $total_amount,
            'TradeDesc' => get_bloginfo('name'),
            'ItemName' => $item_names,
            'ChoosePayment' => 'ALL',
            'ReturnURL' => $notify_url, // reply url
            'PaymentInfoURL' => $notify_url, // CVS, Barcode reply url
            'ClientBackURL' => $return_url,
            'OrderResultURL' => $return_url, // return to store
        );

        // set ignore payment
        if ($ignore_payment && $english == 'no') {
            $allpay_args['IgnorePayment'] = $ignore_payment;
        }
        if ($english == 'yes' || ($english == 'wpml' && defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE == 'en')) {
            $allpay_args['Language'] = 'ENG';
            $allpay_args['ChoosePayment'] = 'Credit';
        }

        // set expire date
        if ($expire_date > 0 && $expire_date <= 60) {
            $allpay_args['ExpireDate'] = $this->expire_date;
        }

        $allpay_args = apply_filters('innovext_allpay_aio_args', $allpay_args);

        return $allpay_args;
    }

    /**
     * Given an order ID, generate mergance trade no using the order ID
     * Format {{prefix}}TS{{TIME_STAMP}}TS{{order_id}}
     * @param type $order_id
     */
    function generate_merchant_trade_no($order_id) {
        $time_stamp = (string) time();
        $unique_no = $this->order_prefix . $order_id . 'TS' . strrev($time_stamp);
        return substr($unique_no, 0, 20);
    }

    /**
     * Convert special character to multibyte
     *
     * @param  $string the string contains any character include special character
     * @return string
     */
    static function convert_special_char_to_multibyte($string) {

        $char = array('~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '=', '+');
        $mb_char = array('～', '！', '＠', '＃', '＄', '％', '︿', '＆', '＊', '（', '）', '—', '＝', '＋');

        return str_replace($char, $mb_char, $string);
    }

    /**
     * Output for the order received page.
     *
     * @access public
     * @param mixed $order_id
     * @return void
     */
    function thankyou_page($order_id) {

        $return_code = get_post_meta($order_id, '_RtnCode', true);

        if ($return_code) {
            if ($return_code === '1') {
                if ($this->instruction_succ) {
                    echo wpautop(wptexturize($this->instruction_succ));
                }
            } elseif (( $return_code === '2' || $return_code === '10100073')) {
                if ($this->instruction_on_hold) {
                    echo wpautop(wptexturize($this->instruction_on_hold));
                }
            } else {
                echo '<p style="padding: 5px 15px;background-color: #FFD0D0;border: 1px solid #E30000;">錯誤代碼 : ' . $return_code . '</p>';
            }
        } else {
            echo '<p style="padding: 5px 15px;background-color: #FFD0D0;border: 1px solid #E30000;">發生錯誤，無法取得訂單狀態，若您已付款，請通知商店管理員。</p>';
        }
    }

    /**
     * Thank you order received text
     * 
     * @param  string $message the original message
     * @param  object $order   WC Order
     * @return mixed
     */
    function thankyou_order_received_text($text, $order) {

        $order_id = $order->get_id();

        $return_code = get_post_meta($order_id, '_RtnCode', true);

        if ($order->get_payment_method() != 'innovext_allpay_aio') {
            return $text;
        }

        if ($return_code) {
            if ($return_code === '1' && $this->instruction_succ) {
                return wpautop(wptexturize($this->instruction_succ));
            } elseif (( $return_code === '2' || $return_code === '10100073')) {
                if ($this->instruction_on_hold) {
                    return wpautop(wptexturize($this->instruction_on_hold));
                }
            } else {
                return '<p style="padding: 5px 15px;background-color: #FFD0D0;border: 1px solid #E30000;">錯誤代碼 : ' . $return_code . '</p>';
            }
        } else {
            return '<p style="padding: 5px 15px;background-color: #FFD0D0;border: 1px solid #E30000;">發生錯誤，無法取得訂單狀態，若您已付款，請通知商店管理員。</p>';
        }

        return $text;
    }

    /**
     * Get the check mac value.
     *
     * @param array $args
     * @return string
     */
    function get_the_check_mac_value($args) {

        $hash_key = $this->hash_key;
        $hash_iv = $this->hash_iv;
        $check_mac_value = self::get_check_mac_value($args, $hash_key, $hash_iv);

        return $check_mac_value;
    }

    /**
     * Get check mac value. Check mac value is the validation mechanism of Allpay/ECpay
     * to check the post value from/to Allpay/ECpay to prevent the value been falsified.
     *
     * @param array $args
     * @param string $hash_key
     * @param string $hash_iv
     * @param string $hash_algo hash algorithm, md5|sha256...etc
     * @return string || boolean
     */
    static function get_check_mac_value($args, $hash_key, $hash_iv, $hash_algo = 'md5') {

        if (empty($args) || empty($hash_key) || empty($hash_iv)) {
            return false;
        }

        ksort($args, SORT_STRING | SORT_FLAG_CASE);
        $args_hash_key = array_merge(array('HashKey' => $hash_key), $args, array('HashIV' => $hash_iv));

        $args_string = '';
        foreach ($args_hash_key as $v => $k) {
            $args_string .= $v . '=' . $k . '&';
        }

        $args_string = rtrim($args_string, "&");
        $args_urlencode = urlencode($args_string);
        $args_urlencode = self::inno_special_character_decode($args_urlencode);
        $args_to_lower = strtolower($args_urlencode);
        $check_mac_value = strtoupper(hash($hash_algo, $args_to_lower));

        return $check_mac_value;
    }

    /**
     * Special character decode, Allpay/ECpay uses .Net to encode the url, but some
     * of the speicial characters are not encoded. To make sure the check mac value
     * is correct, we need to transform them back.
     * 
     * @param type $string
     * @return string
     */
    static function inno_special_character_decode($string) {
        $char = array('%28', '%29', '%5b', '%5d');
        $mb_char = array('(', ')', '[', ']');

        return str_replace($char, $mb_char, $string);
    }

    /**
     * Generate the allpay button link (POST method)
     *
     * @access public
     * @param string $order_id
     * @return string
     */
    function generate_allpay_form($order_id) {

        global $woocommerce;

        $order = new WC_Order($order_id);
        $allpay_args = $this->get_allpay_args($order);
        $check_mac_value = $this->get_the_check_mac_value($allpay_args);
        $allpay_args['CheckMacValue'] = $check_mac_value;

        $this->log('POST to Allpay Args : ', $allpay_args);

        // save pay use type - 1:Allpay, 2:Ecpay
        $pay_use = $this->pay_use;
        update_post_meta($order_id, '_PayUse', $pay_use);

        if ($pay_use !== '2') {
            $gateway_url = $this->allpay_checkout_gateway;
        } else {
            $gateway_url = $this->ecpay_checkout_gateway;
        }

        wc_enqueue_js('
			$.blockUI({
					message: "感謝您的訂購，接下來畫面將導向付款頁面",
					baseZ: 99999,
					overlayCSS:
					{
						background: "#fff",
						opacity: 0.6
					},
					css: {
						padding:        "20px",
						zindex:         "9999999",
						textAlign:      "center",
						color:          "#555",
						border:         "3px solid #aaa",
						backgroundColor:"#fff",
						cursor:         "wait",
						lineHeight:     "24px",
					}
				});
			jQuery("#allpay_payment_form").submit();
		');

        $output = '<form method="POST" id="allpay_payment_form" action="' . $gateway_url . '">';
        foreach ($allpay_args as $k => $v) {
            $output .= '<input type="hidden" name="' . $k . '" value="' . $v . '" >';
        }
        $output .= '</form>';

        return $output;
    }

    /**
     * Output for the order received page.
     *
     * @access public
     * @return void
     */
    function receipt_page($order_id) {

        global $woocommerce;

        echo '<p>感謝您的訂購，接下來將導向付款頁面，請稍後</p>';

        // Clear cart
        $woocommerce->cart->empty_cart();

        $order = new WC_Order($order_id);

        echo $this->generate_allpay_form($order_id);
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @return void
     */
    function email_instructions($order, $sent_to_admin) {

        if ($order->get_payment_method() !== $this->id) {
            return;
        }

        $order_id = $order->get_id();
        $payment_type = get_post_meta($order_id, '_PaymentType', true);

        if ($payment_type) {
            $payment_type_label = self::parse_payment_type($payment_type);
        }

        if ($order->get_status() == 'processing') {

            if ($sent_to_admin) {
                echo "已收到付款，付款方式 - " . $payment_type_label;
            } else {
                echo wpautop(wptexturize($this->instruction_succ)) . PHP_EOL;
            }
        } elseif ($order->get_status() == 'on-hold') {

            if ($sent_to_admin) {
                echo "尚未付款，付款方式 - " . $payment_type_label;
            } else {
                echo wpautop(wptexturize($this->instruction_on_hold)) . PHP_EOL;
                // vaccount detail, cvs detail
                $this->print_order_meta($order, $payment_type_label);
            }
        }
    }

    /**
     * Print order meta for webatm, atm, cvs in email notification
     *
     * @access public
     * @return void
     */
    function print_order_meta($order, $payment_type_label) {
        $order_id = $order->get_id();
        $return_code = get_post_meta($order_id, '_RtnCode', true);
        $payment_method = get_post_meta($order_id, '_allpay_payment_method', true);
        $bool_is_web_bank = false;
        $bool_is_cvs = false;
        if ('2' == $return_code) {
            $bool_is_web_bank = true;
        }

        if (('10100073' == $return_code && 'CVS' == $payment_method)) {
            $bool_is_cvs = true;
        }

        if ($bool_is_cvs || $bool_is_web_bank):
            ?>
            <h2>付款資訊</h2>
            <table class="shop_table order_details allpay_details">
                <tbody>
                    <tr><th>付款方式</th><td><?php echo $payment_type_label ?></td></tr>
                    <?php if ($bool_is_web_bank): ?>
                        <tr><th>銀行代碼</th><td><?php echo get_post_meta($order_id, '_payment_bank_code', true); ?></td></tr>
                        <tr><th>繳費帳號</th><td><?php echo get_post_meta($order_id, '_payment_bank_vaccount', true); ?></td></tr>
                    <?php else: ?>
                        <tr><th>繳費代碼</th><td><?php echo get_post_meta($order_id, '_allpay_cvs_payment_no', true); ?></td></tr>
                    <?php endif; ?>
                    <tr><th>繳費期限</th><td><?php echo get_post_meta($order_id, '_ExpireDate', true); ?></td></tr>
                </tbody>
            </table>
            <?php
        endif;
    }

    /**
     * Check allpay response
     *
     * @access public
     * @return void
     */
    function check_allpay_response() {

        @ob_clean();
        $ipn_response = !empty($_POST) ? $_POST : false;
        $is_valid_request = @$this->check_ipn_response_is_valid($ipn_response);

        if ($is_valid_request) {
            header('HTTP/1.1 200 OK');
            do_action("valid_allpay_ipn_request", $ipn_response);
        } else {
            die("0|ErrorMessage");
        }
    }

    /**
     * Receive ipn response from allpay, update order status if succeeded.
     *
     * @param string $ipn_response , encrypted string
     * @return void
     */
    function successful_request($ipn_response) {
        $MerchantTradeNo = $ipn_response['MerchantTradeNo'];
        $order_id = $this->parse_merchant_trade_no($MerchantTradeNo);
        $this->log('successful request');
        $this->log('successful request for all pay with MerchantTradeNo:' . $MerchantTradeNo . ', Order ID:' . $order_id . ', RTN Code:' . $ipn_response['RtnCode']);
// save the woocommerce transacetion id, same as MerchantTradeNo
        update_post_meta($order_id, '_transaction_id', $MerchantTradeNo);
        update_post_meta($order_id, '_MerchantTradeNo', $MerchantTradeNo);

        $order = new WC_Order($order_id);
        $this->log('wc order created');
        $return_code = $ipn_response['RtnCode'];

//$old_return_code = get_post_meta( $order_id, '_RtnCode', true ); // if previous is 2 or 10100073

        $payment_type = $ipn_response['PaymentType'];
        $payment_method = explode('_', $ipn_response['PaymentType']);

        if ($payment_type) {
            update_post_meta($order_id, '_PaymentType', $payment_type);
        }

        if ($return_code) {
            update_post_meta($order_id, '_RtnCode', $return_code);
        }

        $this->log('successful-request : ', $ipn_response);

// parse allpay ipn payment type label
        $payment_type_label = self::parse_payment_type($payment_type);

        $payment_method_title = get_post_meta($order_id, '_payment_method_title', true);

// change payment method label
        if ($this->payment_method_label == 'main_label') {
            $change_payment_type_label = self::parse_payment_type($payment_type, true);
        } else if ($this->payment_method_label == 'detailed_label') {
            $change_payment_type_label = self::parse_payment_type($payment_type);
        } else {
            $change_payment_type_label = $this->title; // default method title
        }

        if ($this->payment_method_label != 'default' && $payment_method_title != $change_payment_type_label) {
            if ('yes' == $this->testmode) {
                $change_payment_type_label .= '|測試';
            }
            if ('yes' == $this->admin_mode) {
                $change_payment_type_label .= '|管理員';
            }
            update_post_meta($order_id, '_payment_method_title', $change_payment_type_label);
        }

        do_action('innovext_allpay_before_process_rtn_code', $ipn_response, $order_id);

        $pay_use = get_post_meta($order_id, '_PayUse', true);

//		if( empty( $pay_use ) ) {
// get the payment gateway type, 1:Allpay 2:ECpay
        $query_trade_info_args = $this->get_query_trade_info_args($order_id);
        $this->log('Get query trade info args :', $query_trade_info_args);
//		}

        if (isset($query_trade_info_args['PayUse']) && $query_trade_info_args['PayUse']) {
            $pay_use = $query_trade_info_args['PayUse'];
            update_post_meta($order_id, '_PayUse', $pay_use);
        }

// return code === 1 means the payment has been completed
        if ($return_code === '1') {

            $order->payment_complete();
            $order->add_order_note('已收到顧客付款，付款方式 "' . $payment_type_label . '"');

            die("1|OK");
        } elseif ($return_code === '2' || $return_code === '10100073') {

            /**
             * return code '2' means the customer choosed the ATM, '10100073'
             * means convenient store. the orders will be waiting for the payment.
             * If the customer completed the payment, the allpay system will
             * send the post value containing return code '1' to notify the WC API.
             */
// save the ExpireDate to let admin know when will the order be expired
            update_post_meta($order_id, '_ExpireDate', $ipn_response['ExpireDate']);
            update_post_meta($order_id, '_allpay_payment_method', $payment_method[0]);

//save payment details
            if ($return_code === '2') {
//$this->email_return_code = '2';
//$this->email_bank_code = $ipn_response['BankCode'];
//$this->email_vaccount = $ipn_response['vAccount'];
//$this->email_expire_date = $ipn_response['ExpireDate'];
//save in the database
                update_post_meta($order_id, '_payment_bank_code', $ipn_response['BankCode']);
                update_post_meta($order_id, '_payment_bank_vaccount', $ipn_response['vAccount']);
            } else if ($return_code === '10100073' && $payment_method[0] === 'CVS') {
//$this->email_return_code = '10100073';
//$this->email_payment_method = $payment_method[0];
//$this->email_payment_no = $ipn_response['PaymentNo'];
//$this->email_expire_date = $ipn_response['ExpireDate'];
//save payment in the database
                update_post_meta($order_id, '_allpay_cvs_payment_no', $ipn_response['PaymentNo']);
            } else if ($return_code === '10100073' && $payment_method[0] === 'APPBARCODE') {
//$this->email_return_code = '10100073';
//$this->email_payment_method = $payment_method[0];
//$this->email_payment_type_label = $payment_type_label;
                $this->email_expire_date = $ipn_response['ExpireDate'];
            }


            $order->update_status('on-hold', '已選擇付款方式 "' . $payment_type_label . '" ，等待顧客付款。');

            die("1|OK");
        } else {
            $return_message = $ipn_response['RtnMsg'];
            $return_code_msg = $return_code . ' - ' . $return_message;
            $return_code_msg = apply_filters('innovext_allpay_cancel_order_message', $return_code_msg, $ipn_response);
            $this->log('error, return code: ' . $return_code);
            $order->update_status('failed', '付款錯誤，錯誤代碼 : ' . $return_code_msg);
            die("0|ErrorMessage");
        }
    }

    /**
     * Parse Allpay merchant trade number to WooCommerce order id
     * 
     * @param string $mer_trade_no
     * @return string
     */
    function parse_merchant_trade_no($mer_trade_no) {
        //remove order prefix
        $mer_trade_no = str_replace($this->order_prefix, '', $mer_trade_no);

        //new merchant trade no which has TimeStamp
        if (strpos($mer_trade_no, 'TS') !== false) {
            $order_id_arr = explode('TS', $mer_trade_no);
            $mer_trade_no = $order_id_arr[0];
        }

        return intval($mer_trade_no);
    }

    /**
     * Compare the returned allpay CheckMacValue with local CheckMacValue
     *
     * @param array $ipn_response
     * @return bool
     */
    function check_ipn_response_is_valid($ipn_response) {
        $ipn_check_mac_value = $ipn_response['CheckMacValue'];

        unset($ipn_response['CheckMacValue']);

        $my_check_mac_value = $this->get_the_check_mac_value($ipn_response);

        $this->log('ipn check mac:' . $ipn_check_mac_value . ' my_check_mac_value:' . $my_check_mac_value);

        if ($ipn_check_mac_value == $my_check_mac_value) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Display allpay info in admin order detail
     *
     * @param object $order
     * @return void
     */
    function allpay_admin_order_data_after_billing_address($order) {

        if ($order->get_payment_method() != 'innovext_allpay_aio' || $order->get_status() == 'cancelled') {
            return;
        }

        $order_id = $order->get_id();
        $payment_type = get_post_meta($order_id, '_PaymentType', true);
        $pay_use = get_post_meta($order_id, '_PayUse', true);
        $payment_type_label = self::parse_payment_type($payment_type);
        $return_code = get_post_meta($order_id, '_RtnCode', true);
        $merchant_trade_no = get_post_meta($order_id, '_MerchantTradeNo', true);
        $expire_date = get_post_meta($order_id, '_ExpireDate', true);

        echo '<h4>' . __('Payment Details', 'allpay-aio-for-woocommerce') . '</h4>';
        echo '<table class="allpay-aio-info">';

        if ($pay_use) {
            $pay_use_label = '歐付寶';
            if ($pay_use == '2') {
                $pay_use_label = '綠界';
            }
            echo '<tr><th>金流</th><td>' . $pay_use_label . '</td></tr>';
        }

        if ($merchant_trade_no) {
            echo '<tr><th>' . __('Payment ID', 'allpay-aio-for-woocommerce') . '</th><td>' . $merchant_trade_no . '</td></tr>';
        }

        if ($payment_type) {
            echo '<tr><th>' . __('Payment Type', 'allpay-aio-for-woocommerce') . '</th><td>' . $payment_type_label . '</td></tr>';
        }

        if ($return_code) {
            echo '<tr><th>' . __('Payment Status', 'allpay-aio-for-woocommerce') . '</th><td>';
            if ($return_code === '1') {
                _e('Payment Received', 'allpay-aio-for-woocommerce');
            } elseif ($return_code === '2' || $return_code === '10100073') {
                _e('Pending Payment', 'allpay-aio-for-woocommerce');
            }
            echo '</td></tr>';
        }

        if ($expire_date) {
            echo '<tr><th>繳費期限</th><td>' . $expire_date . '</td></tr>';
        }

        echo '</table>';
    }

    /**
     * Add meta box for allpay query info
     *
     * @param object $order
     * @return void
     */
    function add_query_trade_info_meta_box($order) {

        if ($order->get_payment_method() != 'innovext_allpay_aio') {
            return;
        }

        if ($this->query_trade_info != 'yes') {
            return;
        }

        add_meta_box(
                'allpay-queried-order-info', '訂單資訊', array(&$this, 'display_query_trade_info'), 'shop_order', 'advanced', 'high'
        );
    }

    /**
     * Display allpay trade info callback
     *
     * @param object $order
     * @return void
     */
    function display_query_trade_info($post) {

        $order_id = $post->ID;

        $queried_args = $this->get_query_trade_info_args($order_id);

        if (!$queried_args) {
            echo '無交易資訊';
            return;
        }

// ignore check mac value which is unnecessary to display
        if (isset($queried_args['CheckMacValue'])) {
            unset($queried_args['CheckMacValue']);
        }

// parse the payment type to Chinese
        if (isset($queried_args['PaymentType'])) {
            $payment_type = $queried_args['PaymentType'];
            $queried_args['PaymentType'] = self::parse_payment_type($payment_type);
        }

        echo '<table>';
        foreach ($queried_args as $k => $v) {
            $label = self::parse_query_trade_info_args_label($k);
            echo '<tr><th>' . $label . '</th><td>' . $v . '</td></tr>';
        }
        echo '<tr><th>備註</th><td>交易狀態代碼請由歐付寶/綠界廠商後台查尋。</td></tr>';
        echo '</table>';
    }

    /**
     * Get allpay query trade info args label, translate english args label to chinese
     * @param string $label
     * @return mixed
     */
    static function parse_query_trade_info_args_label($label) {

        $args = array(
            'HandlingCharge' => '手續費合計',
            'ItemName' => '商品名稱',
            'MerchantID' => '商店代號',
            'MerchantTradeNo' => '訂單編號',
            'PaymentDate' => '交易日期',
            'PaymentType' => '付款方式',
            'PaymentTypeChargeFee' => '通路費',
            'TradeAmt' => '交易金額',
            'TradeDate' => '訂單成立時間',
            'TradeNo' => '交易編號',
            'TradeStatus' => '交易狀態',
            'CheckMacValue' => '檢查碼',
        );

        if ($label) {
            if (isset($args[$label])) {
                return $args[$label];
            } else {
                return $label;
            }
        } else {
            return $args;
        }
    }

    /**
     * Get allpay query trade info wrapper
     *
     * @param string $order_id
     * @return mixed
     */
    function get_query_trade_info_args($order_id) {

        $mer_id = $this->mer_id;
        $mer_trade_no = get_post_meta($order_id, '_MerchantTradeNo', true);

        if (empty($mer_trade_no)) {
            return false;
        }

        $time_stamp = time();

        $args = array(
            'MerchantID' => $mer_id,
            'MerchantTradeNo' => $mer_trade_no,
            'TimeStamp' => $time_stamp
        );

        $check_mac_value = $this->get_the_check_mac_value($args);
        $args = array_merge($args, array('CheckMacValue' => $check_mac_value));

        $pay_use = get_post_meta($order_id, '_PayUse', true);

        if ($pay_use === '2') {
            $form_action = $this->ecpay_inquery_url;
        } else {
            $form_action = $this->allpay_inquery_url;
        }

        $result = self::post_get_data($args, $form_action);
        $result_args = wp_parse_args($result);

        return $result_args;
    }

    /**
     * Get POST data
     * 
     * @param type $args
     * @param type $form_action
     * @return boolean
     */
    static function post_get_data($args, $form_action) {

        $ch = curl_init();
        $post_data = http_build_query($args);
        curl_setopt($ch, CURLOPT_URL, $form_action);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);

        if (empty($output)) {
            return false;
        }

        return $output;
    }

    /**
     * Get Allpay or Ecpay label
     * 
     * @param string $order_id
     */
    static function get_allpay_ecpay_label($order_id) {
        $pay_use = get_post_meta($order_id, '_PayUse', true);
        if ($pay_use === '2') {
            $label = __('ECPay', 'allpay-aio-for-woocommerce');
        } else {
            $label = __('AllPay', 'allpay-aio-for-woocommerce');
        }
        return $label;
    }

    /**
     * Get main payment type args
     * 
     * @param string $method
     * @return string||array
     */
    static function get_main_payment_type_args($method = '') {

        $methods = array(
            'Credit' => '信用卡',
            'WebATM' => '網路ATM',
            'ATM' => 'ATM自動櫃員機',
            'CVS' => '超商代碼',
            'BARCODE' => '超商條碼',
            'Alipay' => '支付寶',
            'Tenpay' => '財付通',
            'TopUpUsed' => '儲值消費',
            'APPBARCODE' => '全家條碼立即儲',
        );

        if ($method) {
            if (array_key_exists($method, $methods)) {
                return $methods[$method];
            } else {
                return $method;
            }
        } else {
            return $methods;
        }
    }

    /**
     * Get payment type args
     *
     * @param string $method choose method
     * @return array
     */
    static function get_payment_type_args($method = '') {

        $payment_type_args = require( 'payment-type-args.php' );

        if ($method) {
            if (array_key_exists($method, $payment_type_args)) {
                return $payment_type_args[$method];
            } else {
                return $method;
            }
        } else {
            return $payment_type_args;
        }
    }

    /**
     * Parse allpay payment type string to chinese eg: WebATM_TAISHIN to 台新WebATM
     * If specified $main_payment_type, will return the main payment type args
     *
     * @param  string $payment_type Payment type
     * @param  bool   $main_payment_type Main payment type 
     * @return string
     */
    static function parse_payment_type($payment_type, $main_payment_type = false) {

        $payment_type_arr = explode('_', $payment_type, 2);

        if (!isset($payment_type_arr[0]) || !isset($payment_type_arr[1])) {
            return $payment_type;
        }

        $method = $payment_type_arr[0];
        $agent = $payment_type_arr[1];

        if ($main_payment_type) {
            return self::get_main_payment_type_args($method);
        }

        $payment_type_args = apply_filters('inno_allpay_get_payment_type_args', self::get_payment_type_args());

        if (isset($payment_type_args[$method][$agent])) {
            return $payment_type_args[$method][$agent];
        }

        return $payment_type;
    }

    /**
     * Process the payment and redirect to pay page
     *
     * @access public
     * @param int $order_id
     * @return array
     */
    function process_payment($order_id) {

        $order = new WC_Order($order_id);

        if ($this->reduce_stock == 'default') {

            if (function_exists('wc_reduce_stock_levels')) {
                //graceful update
                wc_reduce_stock_levels($order_id);
            } else {
                $order->reduce_order_stock();
            }
        }
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    /**
     * Payment form on checkout page
     *
     * @access public
     * @return void
     */
    function payment_fields() {

        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
    }

    /**
     * Is available. Put some condition here to turn on or off the availability on checkout.
     *
     * @access public
     * @return bool
     */
    function is_available() {

        global $woocommerce;

// admin mode
        if ($this->admin_mode == 'yes' && !current_user_can('manage_woocommerce')) {
            return false;
        }

        $subtotal = $woocommerce->cart->subtotal;
        $min_amount = $this->min_amount;

        $shop_page_url = get_permalink(wc_get_page_id('shop'));
        $return_to = apply_filters('woocommerce_continue_shopping_redirect', $shop_page_url);

// amount condition
        if ($min_amount > 0 && $min_amount > $subtotal) {
            return false;
        }

        return parent::is_available();
    }

}
