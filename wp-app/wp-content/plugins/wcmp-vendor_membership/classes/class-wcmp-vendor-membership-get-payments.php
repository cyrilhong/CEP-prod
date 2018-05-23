<?php

/**
 * Class WCMp Vendor Membership payment main class
 */
class WCMp_Vendor_Membership_Get_Payments {

    public $user_id;
    public $plan_id;
    public $global_settings;
    public $sandbox = false;
    public $payment_info;
    public $method;
    public $API_Endpoint = '';
    public $PAYPAL_URL = '';
    public $api_username;
    public $api_password;
    public $api_signature;
    public $user_pass;

    /**
     * Class construct
     * @global class $WCMP_Vendor_Membership
     * @param int $user_id
     * @param int $plan_id
     * @param string $method
     */
    public function __construct($user_id, $plan_id, $method = '', $user_pass='') {
        global $WCMP_Vendor_Membership;
        $this->user_id = (int) $user_id;
        $this->plan_id = (int) $plan_id;
        $this->method = $method;
        $this->user_pass = $user_pass;
        $this->payment_info = get_post_meta($plan_id, '_vendor_billing_field', true);
        $this->global_settings = $WCMP_Vendor_Membership->get_global_settings();
        if (isset($this->global_settings['sandbox_enable']) && $this->global_settings['sandbox_enable'] == 'Enable') {
            $this->sandbox = true;
        }
        if ($this->sandbox) {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
            $this->api_username = isset($this->global_settings['api_username']) && !empty($this->global_settings['api_username']) ? $this->global_settings['api_username'] : '';
            $this->api_password = isset($this->global_settings['api_password']) && !empty($this->global_settings['api_password']) ? $this->global_settings['api_password'] : '';
            $this->api_signature = isset($this->global_settings['api_signature']) && !empty($this->global_settings['api_signature']) ? $this->global_settings['api_signature'] : '';
        } else {
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
            $this->api_username = isset($this->global_settings['api_username_live']) && !empty($this->global_settings['api_username_live']) ? $this->global_settings['api_username_live'] : '';
            $this->api_password = isset($this->global_settings['api_password_live']) && !empty($this->global_settings['api_password_live']) ? $this->global_settings['api_password_live'] : '';
            $this->api_signature = isset($this->global_settings['api_signature_live']) && !empty($this->global_settings['api_signature_live']) ? $this->global_settings['api_signature_live'] : '';
        }
    }

    /**
     * Make payment 
     * @global class $WCMP_Vendor_Membership
     * @param boolean $new_user
     * @return boolean
     */
    public function create_payment($new_user = true) {
        global $WCMP_Vendor_Membership;
        if (!function_exists('wp_delete_user')) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
        }
        if ($this->method == 'paypal') {
            if ($this->get_request_url()) {
                $_SESSION['plan_id'] = $this->plan_id;
                $_SESSION['user_id'] = $this->user_id;
                $_SESSION['user_pass'] = $this->user_pass;
                $_SESSION['new_user'] = $new_user;
                wp_redirect($this->get_request_url());
                exit();
            } else {
                return false;
            }
        } else if ($this->method == 'card') {
            // do card payment		
            $c_holder = $_POST['c_holder'];
            $c_number = str_replace(' ', '', $_POST['c_number']);
            $c_type = $_POST['c_type'];
            $month_year_arr = explode('/', $_POST['c_month_year']);
            $c_month = trim($month_year_arr[0]);
            $c_year = trim($month_year_arr[1]);
            if (strlen($c_year) == 2) {
                $c_year += 2000;
            }
            if (strlen($c_month) == 1) {
                $c_month = '0' . $c_month;
            }
            $c_cvv = $_POST['c_cvv'];

            $global_settings = $this->global_settings;

            $billing = $this->payment_info;

            if (isset($billing['_is_trial']) && $billing['_is_trial'] == 'yes') {
                $_trial_amt = isset($billing['_trial_amt']) ? number_format($billing['_trial_amt'], 2) : '';
                $_trial_amt_cycle = isset($billing['_trial_amt_cycle']) ? $billing['_trial_amt_cycle'] : '';
                $_trial_amt_cycle_limit = isset($billing['_trial_amt_cycle_limit']) ? $billing['_trial_amt_cycle_limit'] : '';
                $trialbillingfrequency = 1;
            } else {
                $_trial_amt = '';
                $_trial_amt_cycle = '';
                $_trial_amt_cycle_limit = '';
                $trialbillingfrequency = '';
            }
            if (isset($billing['_is_recurring']) && $billing['_is_recurring'] == 'yes') {
                $_initial_payment = isset($billing['_initial_payment']) && $billing['_initial_payment'] ? number_format($billing['_initial_payment'], 2) : 0;
                $_vendor_billing_amt = isset($billing['_vendor_billing_amt']) && $billing['_vendor_billing_amt'] ? number_format($billing['_vendor_billing_amt'], 2) : 0;
                $_vendor_billing_amt_cycle = isset($billing['_vendor_billing_amt_cycle']) && $billing['_vendor_billing_amt_cycle'] ? $billing['_vendor_billing_amt_cycle'] : 0;
                $_vendor_billing_amt_cycle_limit = isset($billing['_vendor_billing_amt_cycle_limit']) && $billing['_vendor_billing_amt_cycle_limit'] ? $billing['_vendor_billing_amt_cycle_limit'] : 0;
                $_vendor_billing_freequency = 1;
                $_vendor_billing_amt_tax = isset($billing['_vendor_billing_amt_tax']) && $billing['_vendor_billing_amt_tax'] ? number_format($billing['_vendor_billing_amt_tax'], 2) : 0;
            } else {
                $_initial_payment = '';
                $_vendor_billing_amt = isset($billing['_initial_payment']) && $billing['_initial_payment'] ? number_format($billing['_initial_payment'], 2) : 0;
                $_vendor_billing_amt_cycle = 'Year';
                $_vendor_billing_amt_cycle_limit = 1;
                $_vendor_billing_freequency = 1;
            }

            $vendor_access = get_post_meta($this->plan_id, '_vender_access', true);

            if (!empty($this->api_username) && !empty($this->api_password) && !empty($this->api_signature)) {
                $PayPalConfig = array(
                    'Sandbox' => $this->sandbox,
                    'APIUsername' => $this->api_username,
                    'APIPassword' => $this->api_password,
                    'APISignature' => $this->api_signature,
                    'PrintHeaders' => false,
                    'LogResults' => false,
                    'LogPath' => $WCMP_Vendor_Membership->plugin_path . 'log/',
                );
                $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);

                $DaysTimestamp = strtotime('now');
                $Mo = date('m', $DaysTimestamp);
                $Day = date('d', $DaysTimestamp);
                $Year = date('Y', $DaysTimestamp);
                $StartDateGMT = $Year . '-' . $Mo . '-' . $Day . 'T00:00:00\Z';
                $ProfileDetails = array(
                    'subscribername' => $c_holder, // Full name of the person receiving the product or service paid for by the recurring payment.  32 char max.
                    'profilestartdate' => $StartDateGMT, // Required.  The date when the billing for this profile begins.  Must be a valid date in UTC/GMT format.
                    'profilereference' => ''                // The merchant's own unique invoice number or reference ID.  127 char max.
                );

                $ScheduleDetails = array(
                    'desc' => get_plan_description($this->plan_id) ? get_plan_description($this->plan_id) : 'WCMpRecurring', // Required.  Description of the recurring payment.  This field must match the corresponding billing agreement description included in SetExpressCheckout.
                    'maxfailedpayments' => $billing['_max_attamped'], // The number of scheduled payment periods that can fail before the profile is automatically suspended.  
                    'autobillamt' => 'AddToNextBilling'                   // This field indicates whether you would like PayPal to automatically bill the outstanding balance amount in the next billing cycle.  Values can be: NoAutoBill or AddToNextBilling
                );

                $BillingPeriod = array(
                    'is_trial' => $billing['_is_trial'],
                    'trialbillingperiod' => $_trial_amt_cycle,
                    'trialbillingfrequency' => $trialbillingfrequency,
                    'trialtotalbillingcycles' => $_trial_amt_cycle_limit,
                    'trialamt' => $_trial_amt,
                    'billingperiod' => $_vendor_billing_amt_cycle, // Required.  Unit for billing during this subscription period.  One of the following: Day, Week, SemiMonth, Month, Year
                    'billingfrequency' => $_vendor_billing_freequency, // Required.  Number of billing periods that make up one billing cycle.  The combination of billing freq. and billing period must be less than or equal to one year. 
                    'totalbillingcycles' => $_vendor_billing_amt_cycle_limit, // the number of billing cycles for the payment period (regular or trial).  For trial period it must be greater than 0.  For regular payments 0 means indefinite...until canceled.  
                    'amt' => $_vendor_billing_amt, // Required.  Billing amount for each billing cycle during the payment period.  This does not include shipping and tax. 
                    'currencycode' => get_woocommerce_currency(), // Required.  Three-letter currency code.
                    'shippingamt' => '', // Shipping amount for each billing cycle during the payment period.
                    'taxamt' => number_format($_vendor_billing_amt_tax, 2)                   // Tax amount for each billing cycle during the payment period.
                );

                $ActivationDetails = array(
                    'initamt' => $_initial_payment, // Initial non-recurring payment amount due immediatly upon profile creation.  Use an initial amount for enrolment or set-up fees.
                    'failedinitamtaction' => 'CancelOnFailure', // By default, PayPal will suspend the pending profile in the event that the initial payment fails.  You can override this.  Values are: ContinueOnFailure or CancelOnFailure
                );

                $CCDetails = array(
                    'creditcardtype' => $c_type, // Required. Type of credit card.  Visa, MasterCard, Discover, Amex, Maestro, Solo.  If Maestro or Solo, the currency code must be GBP.  In addition, either start date or issue number must be specified.
                    'acct' => $c_number, // Required.  Credit card number.  No spaces or punctuation.  
                    'expdate' => $c_month . $c_year, // Required.  Credit card expiration date.  Format is MMYYYY
                    'cvv2' => $c_cvv, // Requirements determined by your PayPal account settings.  Security digits for credit card.
                    'startdate' => '', // Month and year that Maestro or Solo card was issued.  MMYYYY
                    'issuenumber' => ''               // Issue number of Maestro or Solo card.  Two numeric digits max.
                );

                $PayPalRequestData = array(
                    'ProfileDetails' => $ProfileDetails,
                    'ScheduleDetails' => $ScheduleDetails,
                    'BillingPeriod' => $BillingPeriod,
                    'CCDetails' => $CCDetails,
                    'ActivationDetails' => $ActivationDetails
                );
                $PayPalResult = $PayPal->CreateRecurringPaymentsProfile($PayPalRequestData);
                //$PayPalResult = Array('PROFILEID' => "I-LURNH7C6971H", 'PROFILESTATUS' => 'PendingProfile', 'TRANSACTIONID' => '61686356XP007952P', 'TIMESTAMP' => '2016-11-28T13:15:25Z', 'CORRELATIONID' => '1f285163e9a77', 'ACK' => 'Success', 'VERSION' => '64', 'BUILD' => '24616352');
                //doVendorMembershipLOG('PAYPAL RESPONSE: ' . serialize($PayPalResult));
                if (isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                    $profile_id = $PayPalResult['PROFILEID'];
                    $transaction_id = $PayPalResult['TRANSACTIONID'];
                    $timestamp = $PayPalResult['TIMESTAMP'];
                    $corelation_id = $PayPalResult['CORRELATIONID'];
                    $PROFILESTATUS = $PayPalResult['PROFILESTATUS'];
                    $ACK = $PayPalResult['ACK'];
                    if (isset($PayPalResult['PROFILEID']) && !empty($PayPalResult['PROFILEID']) && isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                        if (isset($PayPalResult['PROFILESTATUS'])) {
                            $user_id = $this->user_id;
                            if (!is_wp_error($user_id)) {
                                $_vendor_limitation_field = get_post_meta($this->plan_id, '_vendor_limitation_field', true);
                                update_user_meta($user_id, 'wcmp_membership_user', 1);
                                $user = new WP_User($user_id);
                                if ($new_user) {
                                    $user->set_role('dc_pending_vendor');
                                }
                                update_user_meta($user_id, 'wcmp_vendor_plan_status', 'active');
                                update_user_meta($user_id, 'vendor_group_id', $this->plan_id);
                                update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                                update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                                update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                                update_user_meta($user_id, 'wcmp_vendor_subscription_TRANSACTIONID', $transaction_id);
                                update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                                update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                                update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                                if ($PayPalResult['PROFILESTATUS'] == 'ActiveProfile' && $vendor_access == 'Enable' && $new_user) {
                                    $user->set_role('dc_vendor');
                                } else if ($PayPalResult['PROFILESTATUS'] == 'ActiveProfile' && in_array('dc_vendor', $user->roles)) {
                                    $WCMP_Vendor_Membership->set_user_role_capabilities($this->user_id, 'dc_vendor', 'dc_vendor');
                                }
                                /*                                 * ****************************************** */
                                if (isset($billing['_is_trial']) && $billing['_is_trial'] == 'yes') {
                                    update_user_meta($user_id, '_is_trial', 1);
                                    update_user_meta($user_id, '_trial_amt', $_trial_amt);
                                    update_user_meta($user_id, '_trial_amt_cycle', $_trial_amt_cycle);
                                    update_user_meta($user_id, '_trial_amt_cycle_limit', $_trial_amt_cycle_limit);
                                    if (!empty($_trial_amt) && $_trial_amt > 0) {
                                        $cycle = cycle_to_day($_trial_amt_cycle);
                                        $date = date('Y-m-d');
                                        $date = strtotime($date);
                                        $date = strtotime("+" . $cycle . " day", $date);
                                        update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                    } else {
                                        $cycle = cycle_to_day($_trial_amt_cycle);
                                        $cycle = $cycle * $_trial_amt_cycle_limit;
                                        $date = date('Y-m-d');
                                        $date = strtotime($date);
                                        $date = strtotime("+" . $cycle . " day", $date);
                                        update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                    }
                                } else {
                                    $cycle = cycle_to_day($_vendor_billing_amt_cycle);
                                    $date = date('Y-m-d');
                                    $date = strtotime($date);
                                    $date = strtotime("+" . $cycle . " day", $date);
                                    update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                }
                                update_user_meta($user_id, '_initial_payment', $_initial_payment);
                                update_user_meta($user_id, '_vendor_billing_amt', $_vendor_billing_amt);
                                update_user_meta($user_id, '_vendor_billing_amt_cycle', $_vendor_billing_amt_cycle);
                                update_user_meta($user_id, '_vendor_billing_amt_cycle_limit', $_vendor_billing_amt_cycle_limit);
                                update_user_meta($user_id, '_vendor_billing_amt_tax', $_vendor_billing_amt_tax);
                                if (isset($billing['_is_recurring']) && $billing['_is_recurring'] == 'yes') {
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_type', 'requiring');
                                }
                                update_user_meta($user_id, '_vendor_grace_period_days', $billing['_vendor_grace_period_days']);
                                if ($new_user) {
                                    $email = WC()->mailer()->emails['WCMP_Vendor_Membership_Email_New_Subscription'];
                                    $email->trigger($user_id, $this->user_pass);
                                    $email_admin = WC()->mailer()->emails['WC_Email_Admin_New_Vendor_Account'];
                                    $email_admin->trigger($this->user_id, $this->user_pass, $this->user_pass);
                                }
                            }
                        }
                    }
                    return true;
                } else {
                    if ($new_user) {
                        wp_delete_user((int) $this->user_id);
                    }
                    return false;
                }
            } else {
                if ($new_user) {
                    wp_delete_user((int) $this->user_id);
                }
                return false;
            }
        } else if ($this->method == 'free') {
            $vendor_access = get_post_meta($this->plan_id, '_vender_access', true);
            update_user_meta($this->user_id, 'wcmp_membership_user', 1);
            update_user_meta($this->user_id, 'wcmp_vendor_plan_status', 'active');
            update_user_meta($this->user_id, 'vendor_group_id', $this->plan_id);
            update_user_meta($this->user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
            update_user_meta($this->user_id, 'wcmp_vendor_subscription_status', 'active');
            $_vendor_limitation_field = get_post_meta($this->plan_id, '_vendor_limitation_field', true);
            update_user_meta($this->user_id, '_vendor_limitation_field', $_vendor_limitation_field);
            $user = new WP_User($this->user_id);
            if ($new_user) {
                $user->set_role('dc_pending_vendor');
            }
            if ($vendor_access == 'Enable' && $new_user) {
                $user->set_role('dc_vendor');
            } else if (in_array('dc_vendor', $user->roles)) {
                $WCMP_Vendor_Membership->set_user_role_capabilities($this->user_id, 'dc_vendor', 'dc_vendor');
            }
            if ($new_user) {
                $email = WC()->mailer()->emails['WCMP_Vendor_Membership_Email_New_Subscription'];
                $email->trigger($this->user_id, $this->user_pass);
                $email_admin = WC()->mailer()->emails['WC_Email_Admin_New_Vendor_Account'];
                $email_admin->trigger($this->user_id, $this->user_pass, $this->user_pass);
            }
            return true;
        } else {
            if ($new_user) {
                wp_delete_user((int) $this->user_id);
            }
            return false;
        }
    }

    /**
     * Get request url for Express checkout
     * @global class $WCMP_Vendor_Membership
     * @return boolean
     */
    public function get_request_url() {
        global $WCMP_Vendor_Membership;
        $PayPalConfig = array(
            'Sandbox' => $this->sandbox,
            'APIUsername' => $this->api_username,
            'APIPassword' => $this->api_password,
            'APISignature' => $this->api_signature,
            'PrintHeaders' => false,
            'LogResults' => false,
            'LogPath' => $WCMP_Vendor_Membership->plugin_path . 'log/',
        );
        $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);
        if (!function_exists('get_wcmp_vendor_settings')) {
            return false;
        }
        $returnURL = get_permalink(get_wcmp_vendor_settings('vendor_membership_ipn', 'vendor', 'general'));
        $cancelURL = get_permalink(get_wcmp_vendor_settings('vendor_membership_ipn', 'vendor', 'general'));
        $args = array(
            'L_BILLINGTYPE0' => 'RecurringPayments',
            'L_BILLINGAGREEMENTDESCRIPTION0' => get_plan_description($this->plan_id) ? get_plan_description($this->plan_id) : 'WCMpRecurring'
        );
        $resArray = $PayPal->SetExpressCheckout($returnURL, $cancelURL, $args);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
            return $this->PAYPAL_URL . $resArray["TOKEN"];
        } else {
            return false;
        }
    }

    /**
     * Create recurring profile through express checkout
     * @global class $WCMP_Vendor_Membership
     * @param string $token
     * @param string $payerId
     * @param int $new_user
     * @return boolean
     */
    public function CreateRecurringPaymentsProfile($token, $payerId, $new_user = true) {
        global $WCMP_Vendor_Membership;
        if (!function_exists('wp_delete_user')) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
        }
        $global_settings = $this->global_settings;

        $billing = $this->payment_info;

        if (isset($billing['_is_trial']) && $billing['_is_trial'] == 'yes') {
            $_trial_amt = isset($billing['_trial_amt']) ? number_format($billing['_trial_amt'], 2) : '';
            $_trial_amt_cycle = isset($billing['_trial_amt_cycle']) ? $billing['_trial_amt_cycle'] : '';
            $_trial_amt_cycle_limit = isset($billing['_trial_amt_cycle_limit']) ? $billing['_trial_amt_cycle_limit'] : '';
            $trialbillingfrequency = 1;
        } else {
            $_trial_amt = '';
            $_trial_amt_cycle = '';
            $_trial_amt_cycle_limit = '';
            $trialbillingfrequency = '';
        }
        if (isset($billing['_is_recurring']) && $billing['_is_recurring'] == 'yes') {
            $_initial_payment = isset($billing['_initial_payment']) && $billing['_initial_payment'] ? number_format($billing['_initial_payment'], 2) : 0;
            $_vendor_billing_amt = isset($billing['_vendor_billing_amt']) && $billing['_vendor_billing_amt'] ? number_format($billing['_vendor_billing_amt'], 2) : 0;
            $_vendor_billing_amt_cycle = isset($billing['_vendor_billing_amt_cycle']) && $billing['_vendor_billing_amt_cycle'] ? $billing['_vendor_billing_amt_cycle'] : 0;
            $_vendor_billing_amt_cycle_limit = isset($billing['_vendor_billing_amt_cycle_limit']) && $billing['_vendor_billing_amt_cycle_limit'] ? $billing['_vendor_billing_amt_cycle_limit'] : 0;
            $_vendor_billing_freequency = 1;
            $_vendor_billing_amt_tax = isset($billing['_vendor_billing_amt_tax']) && $billing['_vendor_billing_amt_tax'] ? number_format($billing['_vendor_billing_amt_tax'], 2) : 0;
        } else {
            $_initial_payment = '';
            $_vendor_billing_amt = isset($billing['_initial_payment']) ? number_format($billing['_initial_payment'], 2) : '';
            $_vendor_billing_amt_cycle = 'Year';
            $_vendor_billing_amt_cycle_limit = 1;
            $_vendor_billing_freequency = 1;
            $_vendor_billing_amt_tax = 0;
        }

        $vendor_access = get_post_meta($this->plan_id, '_vender_access', true);

        if (!empty($this->api_username) && !empty($this->api_password) && !empty($this->api_signature)) {
            $PayPalConfig = array(
                'Sandbox' => $this->sandbox,
                'APIUsername' => $this->api_username,
                'APIPassword' => $this->api_password,
                'APISignature' => $this->api_signature,
                'PrintHeaders' => false,
                'LogResults' => false,
                'LogPath' => $WCMP_Vendor_Membership->plugin_path . 'log/',
            );
            $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);

            $DaysTimestamp = strtotime('now');
            $Mo = date('m', $DaysTimestamp);
            $Day = date('d', $DaysTimestamp);
            $Year = date('Y', $DaysTimestamp);
            $StartDateGMT = $Year . '-' . $Mo . '-' . $Day . 'T00:00:00\Z';
            $ProfileDetails = array(
                'profilestartdate' => $StartDateGMT, // Required.  The date when the billing for this profile begins.  Must be a valid date in UTC/GMT format.
            );

            $ScheduleDetails = array(
                'desc' => get_plan_description($this->plan_id) ? get_plan_description($this->plan_id) : 'WCMpRecurring', // Required.  Description of the recurring payment.  This field must match the corresponding billing agreement description included in SetExpressCheckout.
                'maxfailedpayments' => $billing['_max_attamped'], // The number of scheduled payment periods that can fail before the profile is automatically suspended.  
                'autobillamt' => 'AddToNextBilling'                   // This field indicates whether you would like PayPal to automatically bill the outstanding balance amount in the next billing cycle.  Values can be: NoAutoBill or AddToNextBilling
            );

            $BillingPeriod = array(
                'is_trial' => $billing['_is_trial'],
                'trialbillingperiod' => $_trial_amt_cycle,
                'trialbillingfrequency' => $trialbillingfrequency,
                'trialtotalbillingcycles' => $_trial_amt_cycle_limit,
                'trialamt' => $_trial_amt,
                'billingperiod' => $_vendor_billing_amt_cycle, // Required.  Unit for billing during this subscription period.  One of the following: Day, Week, SemiMonth, Month, Year
                'billingfrequency' => $_vendor_billing_freequency, // Required.  Number of billing periods that make up one billing cycle.  The combination of billing freq. and billing period must be less than or equal to one year. 
                'totalbillingcycles' => $_vendor_billing_amt_cycle_limit, // the number of billing cycles for the payment period (regular or trial).  For trial period it must be greater than 0.  For regular payments 0 means indefinite...until canceled.  
                'amt' => $_vendor_billing_amt, // Required.  Billing amount for each billing cycle during the payment period.  This does not include shipping and tax. 
                'currencycode' => get_woocommerce_currency(), // Required.  Three-letter currency code.
                'shippingamt' => '', // Shipping amount for each billing cycle during the payment period.
                'taxamt' => number_format($_vendor_billing_amt_tax, 2)                   // Tax amount for each billing cycle during the payment period.
            );

            $ActivationDetails = array(
                'initamt' => $_initial_payment, // Initial non-recurring payment amount due immediatly upon profile creation.  Use an initial amount for enrolment or set-up fees.
                'failedinitamtaction' => 'CancelOnFailure', // By default, PayPal will suspend the pending profile in the event that the initial payment fails.  You can override this.  Values are: ContinueOnFailure or CancelOnFailure
            );
            $PayPalRequestData = array(
                'AccessDetails' => array('TOKEN' => $token, 'PAYERID' => $payerId),
                'ProfileDetails' => $ProfileDetails,
                'ScheduleDetails' => $ScheduleDetails,
                'BillingPeriod' => $BillingPeriod,
                'ActivationDetails' => $ActivationDetails
            );
            $PayPalResult = $PayPal->CreateRecurringPaymentsProfile($PayPalRequestData);

            //doVendorMembershipLOG('PAYPAL RESPONSE: ' . serialize($PayPalResult));
            if (isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                $profile_id = $PayPalResult['PROFILEID'];
                $PayPalRequestData = array(
                    'PROFILEID' => $profile_id
                );
                $RecurringPaymentsProfileDetails = $PayPal->GetRecurringPaymentsProfileDetails($PayPalRequestData);
                if (isset($RecurringPaymentsProfileDetails['ACK']) && $RecurringPaymentsProfileDetails['ACK'] == 'Success') {
                    if (isset($RecurringPaymentsProfileDetails['STATUS'])) {
                        $subscription_status = $RecurringPaymentsProfileDetails['STATUS'];
                    }
                }
                $timestamp = $PayPalResult['TIMESTAMP'];
                $corelation_id = $PayPalResult['CORRELATIONID'];
                $PROFILESTATUS = $PayPalResult['PROFILESTATUS'];
                $ACK = $PayPalResult['ACK'];
                if (isset($PayPalResult['PROFILEID']) && !empty($PayPalResult['PROFILEID']) && isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                    if (isset($PayPalResult['PROFILESTATUS'])) {
                        $user_id = $this->user_id;
                        if (!is_wp_error($user_id)) {
                            $_vendor_limitation_field = get_post_meta($this->plan_id, '_vendor_limitation_field', true);
                            update_user_meta($user_id, 'wcmp_membership_user', 1);
                            $user = new WP_User($user_id);
                            if ($new_user) {
                                $user->set_role('dc_pending_vendor');
                            }
                            update_user_meta($user_id, 'wcmp_vendor_plan_status', $subscription_status);
                            update_user_meta($user_id, 'vendor_group_id', $this->plan_id);
                            update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                            update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                            update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                            update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                            update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                            update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                            if ($PROFILESTATUS == 'ActiveProfile' && $vendor_access == 'Enable' && $new_user) {
                                $user->set_role('dc_vendor');
                            } else if (in_array('dc_vendor', $user->roles)) {
                                $WCMP_Vendor_Membership->set_user_role_capabilities($this->user_id, 'dc_vendor', 'dc_vendor');
                            }
                            if (isset($billing['_is_trial']) && $billing['_is_trial'] == 'yes') {
                                update_user_meta($user_id, '_is_trial', 1);
                                update_user_meta($user_id, '_trial_amt', $_trial_amt);
                                update_user_meta($user_id, '_trial_amt_cycle', $_trial_amt_cycle);
                                update_user_meta($user_id, '_trial_amt_cycle_limit', $_trial_amt_cycle_limit);
                                if (!empty($_trial_amt) && $_trial_amt > 0) {
                                    $cycle = cycle_to_day($_trial_amt_cycle);
                                    $date = date('Y-m-d');
                                    $date = strtotime($date);
                                    $date = strtotime("+" . $cycle . " day", $date);
                                    update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                } else {
                                    $cycle = cycle_to_day($_trial_amt_cycle);
                                    $cycle = $cycle * $_trial_amt_cycle_limit;
                                    $date = date('Y-m-d');
                                    $date = strtotime($date);
                                    $date = strtotime("+" . $cycle . " day", $date);
                                    update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                }
                            } else {
                                $cycle = cycle_to_day($_vendor_billing_amt_cycle);
                                $date = date('Y-m-d');
                                $date = strtotime($date);
                                $date = strtotime("+" . $cycle . " day", $date);
                                update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                            }
                            update_user_meta($user_id, '_initial_payment', $_initial_payment);
                            update_user_meta($user_id, '_vendor_billing_amt', $_vendor_billing_amt);
                            update_user_meta($user_id, '_vendor_billing_amt_cycle', $_vendor_billing_amt_cycle);
                            update_user_meta($user_id, '_vendor_billing_amt_cycle_limit', $_vendor_billing_amt_cycle_limit);
                            update_user_meta($user_id, '_vendor_billing_amt_tax', $_vendor_billing_amt_tax);
                            update_user_meta($user_id, '_vendor_grace_period_days', $billing['_vendor_grace_period_days']);
                            if (isset($billing['_is_recurring']) && $billing['_is_recurring'] == 'yes') {
                                update_user_meta($user_id, 'wcmp_vendor_subscription_type', 'requiring');
                            }
                            if ($new_user) {
                                $email = WC()->mailer()->emails['WCMP_Vendor_Membership_Email_New_Subscription'];
                                $email->trigger($user_id, $this->user_pass);
                                $email_admin = WC()->mailer()->emails['WC_Email_Admin_New_Vendor_Account'];
                                $email_admin->trigger($this->user_id, $this->user_pass, $this->user_pass);
                            }
                        }
                    }
                }
                return true;
            } else {
                if ($new_user) {
                    wp_delete_user((int) $this->user_id);
                }
                return false;
            }
        } else {
            if ($new_user) {
                wp_delete_user((int) $this->user_id);
            }
            return false;
        }
    }

    /**
     * Update recurring profile
     * @global class $WCMP_Vendor_Membership
     * @param string $profile_id
     * @return boolean
     */
    public function UpdateRecurringPaymentProfile($profile_id) {
        global $WCMP_Vendor_Membership;
        if (empty($profile_id)) {
            return false;
        }
        $reqargs = array(
            'PROFILEID' => $profile_id,
            'CURRENCYCODE' => get_woocommerce_currency(),
            'MAXFAILEDPAYMENTS' => isset($this->payment_info['_max_attamped']) && !empty($this->payment_info['_max_attamped']) ? $this->payment_info['_max_attamped'] : 3,
        );

        if (isset($this->payment_info['_is_recurring']) && $this->payment_info['_is_recurring'] == 'yes') {
            $TOTALBILLINGAMT = isset($this->payment_info['_vendor_billing_amt']) ? number_format($this->payment_info['_vendor_billing_amt'], 2) : '';
            $TOTALBILLINGCYCLES = isset($this->payment_info['_vendor_billing_amt_cycle_limit']) ? $this->payment_info['_vendor_billing_amt_cycle_limit'] : 0;
            $TAXAMT = isset($this->payment_info['_vendor_billing_amt_tax']) ? number_format($this->payment_info['_vendor_billing_amt_tax'], 2) : 0;
            $reqargs['AMT'] = $TOTALBILLINGAMT;
            $reqargs['TOTALBILLINGCYCLES'] = $TOTALBILLINGCYCLES;
            $reqargs['TAXAMT'] = $TAXAMT;
        }
        if (isset($this->payment_info['_is_trial']) && $this->payment_info['_is_trial'] == 'yes') {
            $TRIALAMT = isset($this->payment_info['_trial_amt']) ? number_format($this->payment_info['_trial_amt'], 2) : '';
            $TRIALTOTALBILLINGCYCLES = isset($this->payment_info['_trial_amt_cycle_limit']) ? $this->payment_info['_trial_amt_cycle_limit'] : '';
            $reqargs['TRIALAMT'] = $TRIALAMT;
            $reqargs['TRIALTOTALBILLINGCYCLES'] = $TRIALTOTALBILLINGCYCLES;
        }
        if (!empty($this->api_username) && !empty($this->api_password) && !empty($this->api_signature)) {
            $PayPalConfig = array(
                'Sandbox' => $this->sandbox,
                'APIUsername' => $this->api_username,
                'APIPassword' => $this->api_password,
                'APISignature' => $this->api_signature,
                'PrintHeaders' => false,
                'LogResults' => false,
                'LogPath' => $WCMP_Vendor_Membership->plugin_path . 'log/',
            );
            $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);
            return $PayPal->UpdateRecurringPaymentsProfile($reqargs);
        } else {
            return false;
        }
    }

    /**
     * Cancel recurring profile
     * @global class $WCMP_Vendor_Membership
     * @param string $profile_id
     * @return boolean
     */
    public function CancelRecurringPaymentProfile($profile_id) {
        global $WCMP_Vendor_Membership;
        if (!empty($this->api_username) && !empty($this->api_password) && !empty($this->api_signature)) {
            $PayPalConfig = array(
                'Sandbox' => $this->sandbox,
                'APIUsername' => $this->api_username,
                'APIPassword' => $this->api_password,
                'APISignature' => $this->api_signature,
                'PrintHeaders' => false,
                'LogResults' => false,
                'LogPath' => $WCMP_Vendor_Membership->plugin_path . 'log/',
            );
            $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);
            $response = $PayPal->ManageRecurringPaymentsProfileStatus(array('PROFILEID' => $profile_id, 'ACTION' => 'Cancel'));
            return $response;
        } else {
            return false;
        }
    }

    private function assign_vendor_caps($user, $plan_id) {
        
    }

}
