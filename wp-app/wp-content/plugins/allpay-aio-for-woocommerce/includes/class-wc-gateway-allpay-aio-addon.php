<?php
if (!class_exists('Innvext_Allpay_AIO_Addon')) {

    class Innvext_Allpay_AIO_Addon {

        /**
         * Init Actions
         */
        public static function init() {
            add_action('woocommerce_order_details_after_order_table', array(__CLASS__, 'display_order_info'));
            add_action('woocommerce_view_order', array(__CLASS__, 'view_order'));

            // WC API action http://localhost/wc-api/update_allpay_aio_order
            add_action('woocommerce_api_update_allpay_aio_order', array(__CLASS__, 'cancel_unpaid_orders'));

            add_action('allpay_aio_auto_update_order', array(__CLASS__, 'cancel_unpaid_orders'));
            add_action('init', array(__CLASS__, 'auto_update_order_schedule'));

            // schedule the event
            add_filter('cron_schedules', array(__CLASS__, 'add_cancel_order_schedule'));
        }

        /**
         * Display allpay info in order detail
         *
         * @param object $order
         * @return void
         */
        static function display_order_info($order) {
            if ($order->get_payment_method() !== 'innovext_allpay_aio')
                return;

            $order_id = $order->get_id();
            $return_code = get_post_meta($order_id, '_RtnCode', true);
            $payment_type = get_post_meta($order_id, '_PaymentType', true);
            $payment_type_label = WC_innovext_allpay_aio::parse_payment_type($payment_type);
            $merchant_trade_no = get_post_meta($order_id, '_MerchantTradeNo', true);
            $label = WC_innovext_allpay_aio::get_allpay_ecpay_label($order_id);
            if ($return_code === '1') {
                ?>
                <h3><?php echo $label ?><?php _e('Payment Details', 'allpay-aio-for-woocommerce'); ?></h3>
                <table id="allpay-received-payment" class="shop_table" >
                    <tr><th><?php _e('Payment ID', 'allpay-aio-for-woocommerce'); ?></th><td><?php echo $merchant_trade_no ?></td></tr>
                    <tr><th><?php _e('Payment Type', 'allpay-aio-for-woocommerce'); ?></th><td><?php _e($payment_type_label, 'allpay-aio-for-woocommerce'); ?></td></tr>
                    <tr><th><?php _e('Payment Status', 'allpay-aio-for-woocommerce'); ?></th><td><?php _e('Payment Received', 'allpay-aio-for-woocommerce'); ?></td></tr>
                </table>

            <?php
            }

            if (($return_code === '2' || $return_code === '10100073') && $order->get_status() !== 'cancelled') {
                ?>
                <h3><?php echo $label ?><?php _e('Payment Details', 'allpay-aio-for-woocommerce'); ?></h3>
                <table id="allpay-awaiting-payment" class="shop_table" >
                    <tr><th><?php _e('Payment ID', 'allpay-aio-for-woocommerce'); ?></th><td><?php echo $merchant_trade_no ?></td></tr>
                    <tr><th><?php _e('Payment Type', 'allpay-aio-for-woocommerce'); ?></th><td><?php echo $payment_type_label ?></td></tr>
                    <tr><th><?php _e('Payment Status', 'allpay-aio-for-woocommerce'); ?></th><td><?php _e('Pending Payment', 'allpay-aio-for-woocommerce'); ?></td></tr>
                </table>
                <?php
            }
        }

        /**
         * Display allpay order received/on-hold description in view order page
         *
         * @param object $order
         * @return void
         */
        static function view_order($order_id) {

            $order = new WC_Order($order_id);

            if ($order->get_payment_method() !== 'innovext_allpay_aio') {
                return;
            }

            // Get order instructions
            $allpay_aio_settings = get_option('woocommerce_innovext_allpay_aio_settings');

            if ($allpay_aio_settings) {
                $allpay_instruction_succ = $allpay_aio_settings['instruction_succ'];
                $allpay_instruction_on_hold = $allpay_aio_settings['instruction_on_hold'];
                $return_code = get_post_meta($order_id, '_RtnCode', true);
            } else {
                return;
            }

            if ($return_code === '1' && $order->get_status() == 'processing') {
                if ($allpay_instruction_succ) {
                    echo '<div style="background-color:#C8DFFB;padding: 10px 15px;border: 1px solid #A5B4D2;border-radius: 2px;">';
                    echo wpautop(wptexturize($allpay_instruction_succ));
                    echo '</div>';
                }
            } elseif (( $return_code === '2' || $return_code === '10100073' ) && $order->get_status() == 'on-hold') {
                if ($allpay_instruction_on_hold) {
                    echo '<div style="background-color: #FFF9E8;padding: 10px 15px;border: 1px solid #E0B23E;border-radius: 2px;">';
                    echo wpautop(wptexturize($allpay_instruction_on_hold));
                    echo '</div>';
                }
            }
        }

        /**
         * Check the orders if their Allpay AIO orderstatus payment deadline is expired,
         * 
         * @return void
         */
        static function cancel_unpaid_orders() {

            $args = array(
                'post_type' => 'shop_order',
                'post_status' => array('wc-on-hold'),
                'order' => 'ASC', // make sure update from the oldest order
                'orderby' => 'date',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_payment_method',
                        'value' => array(
                            'innovext_allpay_aio',
                        ),
                        'compare' => 'IN'
                    ),
                )
            );

            $posts = get_posts($args);

            if (empty($posts)) {
                return;
            }

            foreach ($posts as $post) {

                $order_id = $post->ID;
                $expiredate = get_post_meta($order_id, '_ExpireDate', true);

                if (empty($expiredate)) {
                    continue;
                }

                $order = new WC_Order($order_id);
                $parse_date = date_parse($expiredate);

                // the format of ATM expire date is YYYY-mm-dd, which doesn't contain time.
                // so we need to +1 day to make sure the order is expired at the end of the given day
                if (isset($parse_date['hour']) && $parse_date['hour'] === false) {
                    $expiredate = $expiredate . '+1 day';
                }

                $deadline_timestamp = strtotime($expiredate);
                $now_timestamp = strtotime('now');

                // if expired, cancel the WC order
                if ($now_timestamp > $deadline_timestamp) {
                    $order->update_status('cancelled', '訂單超過繳費期限');
                }
            }
            // die(); // use die to get the output
        }

        /**
         * Add cron schedules interval
         * 
         * @return void
         */
        static function add_cancel_order_schedule($schedules) {

            $allpay_aio_settings = get_option('woocommerce_innovext_allpay_aio_settings');

            if (isset($allpay_aio_settings['cron_frequency_min']) && !empty($allpay_aio_settings['cron_frequency_min'])) {
                $cron_frequency_min = $allpay_aio_settings['cron_frequency_min'];
            } else {
                $cron_frequency_min = 360;
            }

            $schedules['woocommerce_allpay_aio_cron_frequency'] = array(
                'interval' => $cron_frequency_min * 60, // X minutes * 60 seconds, default is 1 hour
                'display' => __('歐付寶/綠界全方位金流檢查過期訂單')
            );

            return $schedules;
        }

        /**
         * Schedule the event
         * 
         * @return void
         */
        static function auto_update_order_schedule() {

            $allpay_aio_settings = get_option('woocommerce_innovext_allpay_aio_settings');

            if (isset($allpay_aio_settings['cron_frequency_min']) && $allpay_aio_settings['cron_frequency_min'] > 0) {
                $enable_cron = 'yes';
            } else {
                $enable_cron = 'no';
            }

            if ($enable_cron != 'yes') {
                return;
            }

            if (function_exists('wp_next_scheduled') && function_exists('wp_schedule_event')) {
                $now_timestmp = strtotime('now');
                if (!wp_next_scheduled('allpay_aio_auto_update_order')) {
                    wp_schedule_event($now_timestmp, 'woocommerce_allpay_aio_cron_frequency', 'allpay_aio_auto_update_order');
                }
            }
        }

    }

    Innvext_Allpay_AIO_Addon::init();
}
