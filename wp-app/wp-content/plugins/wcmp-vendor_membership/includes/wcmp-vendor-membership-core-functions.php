<?php
if (!function_exists('wvm_woocommerce_inactive_notice')) {

    function wvm_woocommerce_inactive_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__('%sWCMp Vendor Membership is inactive.%s The %sWooCommerce plugin%s must be active for the WCMp Vendor Membership to work. Please %sinstall & activate WooCommerce%s', WCMP_VENDOR_MEMBERSHIP_TEXT_DOMAIN), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url('plugin-install.php?tab=search&s=woocommerce') . '">', '&nbsp;&raquo;</a>'); ?></p>
        </div>
        <?php
    }

}

if (!function_exists('wvm_wcmp_inactive_notice')) {

    function wvm_wcmp_inactive_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__('%sWCMp Vendor Membership is inactive.%s The %sWC Marketplace%s must be active for the WCMp Vendor Membership to work. Please %sinstall & activate WC Marketplace%s', WCMP_VENDOR_MEMBERSHIP_TEXT_DOMAIN), '<strong>', '</strong>', '<a target="_blank" href="https://wordpress.org/plugins/dc-woocommerce-multi-vendor/">', '</a>', '<a href="' . admin_url('plugin-install.php?tab=search&s=wc+marketplace') . '">', '&nbsp;&raquo;</a>'); ?></p>
        </div>
        <?php
    }

}

if (!function_exists('is_vendor_membership_plan_payment_page')) {

    function is_vendor_membership_plan_payment_page() {
        $payment_page = false;
        $payment_page_id = get_option('wcmp_product_vendor_registration_page_id');
        if (!empty($payment_page_id)) {
            if (is_page($payment_page_id)) {
                $payment_page = true;
            }
        }
        return $payment_page;
    }

}

if (!function_exists('is_wvm_recurring')) {

    function is_wvm_recurring($postid, $get = 'key') {
        $vendor_billing_field = get_post_meta($postid, '_vendor_billing_field', true);
        if (!isset($vendor_billing_field['_is_recurring'])) {
            return false;
        } else if ($vendor_billing_field['_is_recurring'] == 'no') {
            return false;
        } else if ($vendor_billing_field['_is_recurring'] == 'yes') {
            if ($get == 'value') {
                switch ($vendor_billing_field['_vendor_billing_amt_cycle']) {
                    case 'Day':
                        $value = 'Daily';
                        break;
                    case 'Week':
                        $value = 'Weekly';
                        break;
                    case 'SemiMonth':
                        $value = 'SemiMonthly';
                        break;
                    case 'Month':
                        $value = 'Monthly';
                        break;
                    case 'Year':
                        $value = 'Yearly';
                        break;
                    default :
                        $value = '';
                }
                return $value;
            } else if ($get = 'sort') {
                switch ($vendor_billing_field['_vendor_billing_amt_cycle']) {
                    case 'Day':
                        $value = 'D';
                        break;
                    case 'Week':
                        $value = 'W';
                        break;
                    case 'SemiMonth':
                        $value = 'M';
                        break;
                    case 'Month':
                        $value = 'M';
                        break;
                    case 'Year':
                        $value = 'Y';
                        break;
                    default :
                        $value = '';
                }
                return $value;
            } else {
                return $vendor_billing_field['_vendor_billing_amt_cycle'];
            }
        }
    }

}

if (!function_exists('doVendorMembershipLOG')) {

    /**
     * Write to log file
     */
    function doVendorMembershipLOG($str) {
        global $WCMP_Vendor_Membership;
        $file = $WCMP_Vendor_Membership->plugin_path . 'log/vendormembership.log';
        if (file_exists($file)) {
            // Open the file to get existing content
            $current = file_get_contents($file);
            if ($current) {
                // Append a new content to the file
                $current .= "$str" . "\r\n";
                $current .= "-------------------------------------\r\n";
            } else {
                $current = "$str" . "\r\n";
                $current .= "-------------------------------------\r\n";
            }
            // Write the contents back to the file
            file_put_contents($file, $current);
        }
    }

}


if (!function_exists('wvm_validate_ipn')) {

    /**
     * Check PayPal IPN validity.
     */
    function wvm_validate_ipn($reqbody, $sandbox = false) {
        global $WCMP_Vendor_Membership;
        // Get received values from post data
        $validate_ipn = array('cmd' => '_notify-validate');
        $validate_ipn += wp_unslash($reqbody);
        //doVendorMembershipLOG('ipn_request_param: '.serialize($validate_ipn));
        // Send back post vars to paypal
        $params = array(
            'body' => $validate_ipn,
            'timeout' => 60,
            'httpversion' => '1.1',
            'compress' => false,
            'decompress' => false,
            'user-agent' => 'WCMP_Vendor_Membership/' . $WCMP_Vendor_Membership->version
        );

        // Post back to get a response.
        $response = wp_safe_remote_post($sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr', $params);
        //doVendorMembershipLOG('ipn_response_param: '.serialize($response));
        // Check to see if the request was valid.
        if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr($response['body'], 'VERIFIED')) {
            doVendorMembershipLOG('VALIDIPN');
            return true;
        }
        doVendorMembershipLOG('INVALIDIPN');
        return false;
    }

}
if (!function_exists('cycle_to_day')) {

    function cycle_to_day($cycle = NULL) {
        $count = 0;
        if ($cycle != NULL) {
            switch ($cycle) {
                case 'Day':
                    $count = 1;
                    break;
                case 'Week':
                    $count = 7;
                    break;
                case 'SemiMonth':
                    $count = 15;
                    break;
                case 'Month':
                    $count = 30;
                    break;
                case 'Year':
                    $count = 365;
                    break;
            }
        }
        return $count;
    }

}

if (!function_exists('wvm_delete_user_payment_meta')) {

    function wvm_delete_user_payment_meta($user_id) {
        if (isset($user_id) && !empty($user_id)) {
            delete_user_meta($user_id, '_vendor_limitation_field');
            delete_user_meta($user_id, '_is_trial');
            delete_user_meta($user_id, '_trial_amt');
            delete_user_meta($user_id, '_trial_amt_cycle');
            delete_user_meta($user_id, '_trial_amt_cycle_limit');
            delete_user_meta($user_id, '_next_payment_date');
            delete_user_meta($user_id, '_initial_payment');
            delete_user_meta($user_id, '_vendor_billing_amt');
        }
    }

}

if (!function_exists('get_plan_description')) {

    function get_plan_description($plan_id) {
        global $WCMP_Vendor_Membership;
        if(!$WCMP_Vendor_Membership){
            return;
        }
        $global_settings = $WCMP_Vendor_Membership->get_global_settings();
        $description = '';
        $_vendor_billing_field = get_post_meta($plan_id, '_vendor_billing_field', true);
        if (isset($_vendor_billing_field['_initial_payment']) && !empty($_vendor_billing_field['_initial_payment']) && $_vendor_billing_field['_initial_payment'] > 0) {
            $description .= __(' Initial Payment ', 'wcmp-vendor_membership');
            $description .= get_woocommerce_currency(). ' ';
            $description .= number_format($_vendor_billing_field['_initial_payment'], 2);
        }
        if (isset($_vendor_billing_field['_is_recurring']) && $_vendor_billing_field['_is_recurring'] == 'yes') {
            if (isset($_vendor_billing_field['_initial_payment']) && $_vendor_billing_field['_initial_payment'] > 0) {
                if ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Week') {
                    $description .= __(' for First Week', 'wcmp-vendor_membership');
                }
                if ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'SemiMonth') {
                    $description .= __(' for First 15 Days', 'wcmp-vendor_membership');
                }
                if ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Month') {
                    $description .= __(' for First Month', 'wcmp-vendor_membership');
                } elseif ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Day') {
                    $description .= __(' for First Day', 'wcmp-vendor_membership');
                } elseif ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Year') {
                    $description .=  __(' for First Year', 'wcmp-vendor_membership');
                }

                $description .= __(' and Next ', 'wcmp-vendor_membership');
            }
            $billing_amt = isset($_vendor_billing_field['_vendor_billing_amt']) && !empty($_vendor_billing_field['_vendor_billing_amt']) ? $_vendor_billing_field['_vendor_billing_amt'] : 0;
            if (isset($global_settings['display_method']) && !empty($global_settings['display_method']) && $global_settings['display_method'] == 'inclusive') {
                if (isset($_vendor_billing_field['_vendor_billing_tax_amt']) && !empty($_vendor_billing_field['_vendor_billing_tax_amt'])) {
                    $billing_amt += $_vendor_billing_field['_vendor_billing_tax_amt'];
                }
            }
            $description .= get_woocommerce_currency(). ' ' . number_format($billing_amt, 2) . ' per ' . $_vendor_billing_field['_vendor_billing_amt_cycle'];
        } else {
            if (isset($_vendor_billing_field['_initial_payment']) && $_vendor_billing_field['_initial_payment'] > 0) {
                $description .= __(' One Time', 'wcmp-vendor_membership');
            }
        }
        return $description;
    }

}
