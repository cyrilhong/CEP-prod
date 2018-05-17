<?php

class WCMp_Vendor_IPN {

    public function __construct() {
        
    }

    /**
     * Output the demo shortcode.
     *
     * @access public
     * @param array $atts
     * @return void
     */
    public static function output($attr) {
        global $WCMP_Vendor_Membership;
        $WCMP_Vendor_Membership->nocache();
        $global_settings = $WCMP_Vendor_Membership->get_global_settings();
        $sandbox = false;
        $success_msg = isset($global_settings['_success_msg']) && !empty($global_settings['_failuare_msg']) ? $global_settings['_success_msg'] : __('You have successfully subscribed.', 'wcmp-vendor_membership');
        $failuare_msg = isset($global_settings['_failuare_msg']) && !empty($global_settings['_failuare_msg']) ? $global_settings['_failuare_msg'] : __('Your transaction failed.', 'wcmp-vendor_membership');
        if (isset($global_settings) && $global_settings['sandbox_enable'] == 'Enable') {
            $sandbox = true;
        }
        if (isset($_REQUEST['token'])) {
            if (!function_exists('wp_delete_user')) {
                require_once(ABSPATH . 'wp-admin/includes/user.php');
            }
            $token = $_REQUEST['token'];
            if ($sandbox) {
                if (isset($global_settings['api_username'])) {
                    $api_username = $global_settings['api_username'];
                }
                if (isset($global_settings['api_password'])) {
                    $api_password = $global_settings['api_password'];
                }
                if (isset($global_settings['api_signature'])) {
                    $api_signature = $global_settings['api_signature'];
                }
            } else {
                if (isset($global_settings['api_username_live'])) {
                    $api_username = $global_settings['api_username_live'];
                }
                if (isset($global_settings['api_password_live'])) {
                    $api_password = $global_settings['api_password_live'];
                }
                if (isset($global_settings['api_signature_live'])) {
                    $api_signature = $global_settings['api_signature_live'];
                }
            }
            $print_headers = false;
            $log_results = false;
            $log_path = $WCMP_Vendor_Membership->plugin_path . 'log/';
            $PayPalConfig = array(
                'Sandbox' => $sandbox,
                'APIUsername' => $api_username,
                'APIPassword' => $api_password,
                'APISignature' => $api_signature,
                'PrintHeaders' => $print_headers,
                'LogResults' => $log_results,
                'LogPath' => $log_path,
            );
            $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);
            $resArray = $PayPal->getExpressCheckout($token);
            $ack = strtoupper($resArray["ACK"]);
            $user_id = $_SESSION['user_id'];
            $plan_id = $_SESSION['plan_id'];
            $new_user = $_SESSION['new_user'];
            $user_pass = $_SESSION['user_pass'];
            if (isset($resArray["PAYERID"]) && ($ack == "SUCCESS" || $ack == "SUCESSWITHWARNING")) {
                $WCMP_Vendor_Membership->load_class('get-payments');
                $WCMp_Vendor_Membership_Get_Payments = new WCMp_Vendor_Membership_Get_Payments($user_id, $plan_id, '', $user_pass);
                $response = $WCMp_Vendor_Membership_Get_Payments->CreateRecurringPaymentsProfile($token, $resArray["PAYERID"], $new_user);
                if ($response) {
                    echo isset(get_post_meta($plan_id, '_vendor_message_field', true)['_success_msg']) && get_post_meta($plan_id, '_vendor_message_field', true)['_success_msg'] ? get_post_meta($plan_id, '_vendor_message_field', true)['_success_msg'] : $success_msg;
                } else {
                    echo isset(get_post_meta($plan_id, '_vendor_message_field', true)['_failuare_msg']) && get_post_meta($plan_id, '_vendor_message_field', true)['_failuare_msg'] ? get_post_meta($plan_id, '_vendor_message_field', true)['_failuare_msg'] : $failuare_msg;
                }
                unset($_SESSION['user_id']);
                unset($_SESSION['plan_id']);
                unset($_SESSION['new_user']);
                unset($_SESSION['user_pass']);
            } else {
                $user_id = $_SESSION['user_id'];
                if ($new_user) {
                    wp_delete_user((int) $user_id);
                }
                unset($_SESSION['user_id']);
                unset($_SESSION['plan_id']);
                unset($_SESSION['new_user']);
                unset($_SESSION['user_pass']);
                echo isset(get_post_meta($plan_id, '_vendor_message_field', true)['_failuare_msg']) && get_post_meta($plan_id, '_vendor_message_field', true)['_failuare_msg'] ? get_post_meta($plan_id, '_vendor_message_field', true)['_failuare_msg'] : $failuare_msg;
            }
        }
    }

}
