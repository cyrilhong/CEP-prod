<?php

class WCMP_Vendor_Membership_Cron {

    public function __construct() {
        add_action('subscription_cron_start', array($this, 'subscription_cron_callback'), 30);

        //$this->subscription_cron_callback();
        //doVendorMembershipLOG(serialize($this->get_profile('I-MDCVW020JYHX')));
        //print_r($this->get_profile('I-XJM4G8SAK5CM'));die;
    }

    public function subscription_cron_callback() {
        global $WCMP_Vendor_Membership;
        //doVendorMembershipLOG("Cron Run Start for change subscription status @ " . date('d/m/Y g:i:s A', time()));

        $args = array(
            'blog_id' => $GLOBALS['blog_id'],
            'role' => '',
            'role__in' => array('dc_vendor', 'dc_pending_vendor'),
            'role__not_in' => array(),
            'meta_key' => '',
            'meta_value' => '',
            'meta_compare' => '',
            'meta_query' => array(),
            'date_query' => array(),
            'include' => array(),
            'exclude' => array(),
            'orderby' => 'login',
            'order' => 'ASC',
            'offset' => '',
            'search' => '',
            'number' => '',
            'count_total' => false,
            'fields' => 'all',
            'who' => ''
        );
        $users = get_users($args);
        if (!empty($users) && is_array($users)) {
            foreach ($users as $user) {
                $user_id = $user->ID;
                $user_obj = new WP_User($user_id);
                $plan_id = get_user_meta($user_id, 'vendor_group_id', true);
                $vendor_billing_field = get_post_meta($plan_id, '_vendor_billing_field', true);
                $status_after_grace = isset($vendor_billing_field['_status_after_grace']) ? $vendor_billing_field['_status_after_grace'] : '';
                $vendor_grace_period_days = isset($vendor_billing_field['_vendor_grace_period_days']) ? $vendor_billing_field['_vendor_grace_period_days'] : 0;

                $grace_period_days = get_user_meta($user_id, '_vendor_grace_period_days', true);
                $next_payment_date = get_user_meta($user_id, '_next_payment_date', true);
                $membership_type = get_user_meta($user_id, 'wcmp_vendor_subscription_type', true);
                $profile_id = get_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', true);
                $RecurringPaymentsProfileDetails = array();
                if ($profile_id && !empty($membership_type)) {
                    $RecurringPaymentsProfileDetails = $this->get_profile($profile_id);
                    if (isset($RecurringPaymentsProfileDetails['ACK']) && $RecurringPaymentsProfileDetails['ACK'] == 'Success') {
                        if (isset($RecurringPaymentsProfileDetails['STATUS'])) {
                            $subscription_status = $RecurringPaymentsProfileDetails['STATUS'];
                            update_user_meta($user_id, 'wcmp_vendor_subscription_status', $subscription_status);
                            if ($subscription_status == 'Active') {
                                update_user_meta($user_id, 'wcmp_vendor_plan_status', 'Active');
                                $vendor_access = get_post_meta($plan_id, '_vender_access', true);
                                if ($vendor_access == 'Enable' && !in_array('dc_vendor', $user_obj->roles)) {
                                    $user_obj->set_role('dc_vendor');
                                }
                            } else {
                                update_user_meta($user_id, 'wcmp_vendor_plan_status', 'Inactive');
                            }
                            if (isset($RecurringPaymentsProfileDetails['NEXTBILLINGDATE'])) {
                                update_user_meta($user_id, '_next_payment_date', $RecurringPaymentsProfileDetails['NEXTBILLINGDATE']);
                            }
                        }
                    }
                }

                if (!empty($grace_period_days) && !empty($next_payment_date) && !empty($membership_type)) {
                    $today = date('Y-m-d');
                    $date1 = date_create($next_payment_date);
                    $date2 = date_create($today);
                    $diff = date_diff($date1, $date2);
                    $days_count = $diff->format("%R%a");
                    if ($days_count >= $vendor_grace_period_days) {
                        if (!empty($status_after_grace)) {
                            $user_obj->set_role($status_after_grace);
                        }
                        update_user_meta($user_id, 'wcmp_vendor_plan_status', 'Inactive');
                        $hide_product_after_grace = isset($vendor_billing_field['_hide_product_after_grace']) ? $vendor_billing_field['_hide_product_after_grace'] : '';
                        if (!empty($hide_product_after_grace) && $hide_product_after_grace == 'Enable') {
                            //hide this vendor product
                            $args = array(
                                'posts_per_page' => -1,
                                'offset' => 0,
                                'author' => $user_id,
                                'post_status' => 'publish',
                                'suppress_filters' => true
                            );
                            $vendor_products = get_posts($args);
                            foreach ($vendor_products as $vendor_product) {
                                wp_update_post(array('ID' => $vendor_product->ID, 'post_status' => 'private'));
                            }
                        }
                        do_action('after_wcmp_vendor_membership_grace_period', $user_id, $plan_id);
                    } elseif ($days_count > 0) {
                        update_user_meta($user_id, 'wcmp_vendor_plan_status', 'Expired');
                    }
                }
                do_action('after_wcmp_vendor_membership_corn', $user_id, $plan_id, $RecurringPaymentsProfileDetails);
            }
        }
    }

    function get_profile($profileid = '') {
        global $WCMP_Vendor_Membership;

        $global_settings = $WCMP_Vendor_Membership->get_global_settings();
        if (isset($global_settings['sandbox_enable']) && $global_settings['sandbox_enable'] == 'Enable') {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        if ($sandbox) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', '1');
        }
        if (isset($global_settings['appplication_id']) && $global_settings['appplication_id'] != '') {
            $application_id = $global_settings['appplication_id'];
        } else {
            $application_id = $sandbox ? 'APP-80W284485P519543T' : '';
        }
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
        if (isset($api_username) && isset($api_password) && isset($api_signature) && isset($log_path)) {
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
            if ($profileid != '') {
                $PayPalRequestData = array(
                    'PROFILEID' => $profileid
                );
                $PayPalResult = $PayPal->GetRecurringPaymentsProfileDetails($PayPalRequestData);
                return $PayPalResult;
            } else {
                return false;
            }
        }
    }

}
