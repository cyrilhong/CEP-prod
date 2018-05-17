<?php

class WCMP_Vendor_Membership_Ajax {

    public function __construct() {
        add_action('wp_ajax_paypal_api_call_form_process', array($this, 'paypal_api_call_form_process'));
        add_action('wp_ajax_nopriv_paypal_api_call_form_process', array($this, 'paypal_api_call_form_process'));

        add_action('wp_ajax_paypal_api_call_cancel_process', array($this, 'paypal_api_call_cancel_process'));

        add_action('wp_ajax_state_for_country_ajax', array($this, 'get_state_lists'));
        add_action('wp_ajax_nopriv_state_for_country_ajax', array($this, 'get_state_lists'));
        add_action('wp_ajax_check_email_exits_or_not', array($this, 'check_email_exits_or_not'));
        add_action('wp_ajax_nopriv_check_email_exits_or_not', array($this, 'check_email_exits_or_not'));
    }

    function check_email_exits_or_not() {
        global $WCMP_Vendor_Membership;
        $email = $_POST['emailaddress'];
        $current_user = wp_get_current_user();
        if ($current_user->ID != 0) {
            if (!$current_user->user_email == $email) {
                if (email_exists($email)) {
                    echo "exits";
                }
            }
        } else {
            if (email_exists($email)) {
                echo "exits";
            }
        }
        die;
    }

    function get_state_lists() {
        global $WCMP_Vendor_Membership;
        $country_code = $_POST['country_code'];
        $WC_Countries = new WC_Countries();
        if ($country_code == '') {
            ?>
            <select name="wcmp_checkout_state" class="checkout-input wcmp_checkout_state" id="wcmp_checkout_state" >
                <option value=""><?php echo __('Please choose your State (Required)', 'wcmp-vendor_membership'); ?></option>				
            </select>			
            <?php
        } else {
            $states = $WC_Countries->get_states($country_code);
            if ($states) {
                ?>
                <select name="wcmp_checkout_state" class="checkout-input wcmp_checkout_state" id="wcmp_checkout_state" >
                    <option value=""><?php echo __('Please choose your State (Required)', 'wcmp-vendor_membership'); ?></option>
                    <?php
                    foreach ($states as $state_code => $state_name) {
                        ?>
                        <option value="<?php echo $state_code; ?>"><?php echo $state_name; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
            } else {
                ?>
                <input type="text" name="wcmp_checkout_state" id="wcmp_checkout_state" placeholder="<?php echo __('Please enter your State (Required)', 'wcmp-vendor_membership'); ?>" >				
                <?php
            }
        }
        die;
    }

    function paypal_api_call_form_process() {
        global $WCMP_Vendor_Membership;
        $current_user = wp_get_current_user();
        //require_once($WCMP_Vendor_Membership->plugin_path.'lib/paypal/autoload.php');		
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
        $user_type_for_registration = $_POST['user_type_for_registration'];
        $wcmp_checkout_Email = urlencode($_POST['wcmp_checkout_Email']);
        $wcmp_checkout_first_name = urlencode($_POST['wcmp_checkout_first_name']);
        $wcmp_checkout_last_name = urlencode($_POST['wcmp_checkout_last_name']);
        $wcmp_checkout_company = urlencode($_POST['wcmp_checkout_company']);
        $wcmp_checkout_address1 = urlencode($_POST['wcmp_checkout_address1']);
        $wcmp_checkout_address2 = urlencode($_POST['wcmp_checkout_address2']);
        $wcmp_checkout_country = urlencode($_POST['wcmp_checkout_country']);
        $wcmp_checkout_state = urlencode($_POST['wcmp_checkout_state']);
        $wcmp_checkout_city = urlencode($_POST['wcmp_checkout_city']);
        $wcmp_checkout_zip = urlencode($_POST['wcmp_checkout_zip']);
        $wcmp_checkout_phone = urlencode($_POST['wcmp_checkout_phone']);
        
        if (email_exists(urldecode($wcmp_checkout_Email)) && $current_user->ID == 0) {
            echo __("Email already exists please try with another email or login first", 'wcmp-vendor_membership');
            die;
        }

        $plan_id = $_SESSION['plan_id'];
        if (empty($plan_id)) {
            echo __('sorry session expired Please go throw the registration proccess <a href="' . get_permalink(get_option('wcmp_vendor_membership_page_id')) . '">click here</a>', 'wcmp-vendor_membership');
            die;
        }
        $global_settings = $WCMP_Vendor_Membership->get_global_settings();
        if (isset($_SESSION['plan_meta']['_vendor_billing_field'])) {
            $billing = unserialize($_SESSION['plan_meta']['_vendor_billing_field'][0]);
        }
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
            $_initial_payment = isset($billing['_initial_payment']) ? number_format($billing['_initial_payment'], 2) : '';
            $_vendor_billing_amt = isset($billing['_vendor_billing_amt']) ? number_format($billing['_vendor_billing_amt'], 2) : '';
            $_vendor_billing_amt_cycle = isset($billing['_vendor_billing_amt_cycle']) ? $billing['_vendor_billing_amt_cycle'] : '';
            $_vendor_billing_amt_cycle_limit = isset($billing['_vendor_billing_amt_cycle_limit']) ? $billing['_vendor_billing_amt_cycle_limit'] : 0;
            $_vendor_billing_freequency = 1;
            $_vendor_billing_amt_tax = isset($billing['_vendor_billing_amt_tax']) ? number_format($billing['_vendor_billing_amt_tax'], 2) : 0;
        } else {
            $_initial_payment = isset($billing['_initial_payment']) ? number_format($billing['_initial_payment'], 2) : '';
            $_vendor_billing_amt = isset($billing['_initial_payment']) ? number_format($billing['_initial_payment'], 2) : '';
            $_vendor_billing_amt_cycle = 'Year';
            $_vendor_billing_amt_cycle_limit = 1;
            $_vendor_billing_freequency = 1;
        }

        $vendor_access = get_post_meta($post_id, '_vender_access', true);

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
            if (isset($global_settings['api_username']))
                $api_username = $global_settings['api_username'];
            if (isset($global_settings['api_password']))
                $api_password = $global_settings['api_password'];
            if (isset($global_settings['api_signature']))
                $api_signature = $global_settings['api_signature'];
        }
        else {
            if (isset($global_settings['api_username_live']))
                $api_username = $global_settings['api_username_live'];
            if (isset($global_settings['api_password_live']))
                $api_password = $global_settings['api_password_live'];
            if (isset($global_settings['api_signature_live']))
                $api_signature = $global_settings['api_signature_live'];
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
            //$PayPal = new wcmp\PayPal\PayPal($PayPalConfig);
            $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);
            $CRPPFields = array('token' => ''); // Token returned from PayPal SetExpressCheckout.  Can also use token returned from SetCustomerBillingAgreement.
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
                'desc' => $billing['_plan_short_desc'], // Required.  Description of the recurring payment.  This field must match the corresponding billing agreement description included in SetExpressCheckout.
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

            $PayerInfo = array(
                'email' => $wcmp_checkout_Email, // Email address of payer.
                'payerid' => '', // Unique PayPal customer ID for payer.
                'payerstatus' => '', // Status of payer.  Values are verified or unverified
                'countrycode' => '', // Payer's country of residence in the form of the two letter code.
                'business' => $wcmp_checkout_company        // Payer's business name.
            );

            $PayerName = array(
                'salutation' => 'Dear', // Payer's salutation.  20 char max.
                'firstname' => $wcmp_checkout_first_name, // Payer's first name.  25 char max.
                'middlename' => '', // Payer's middle name.  25 char max.
                'lastname' => $wcmp_checkout_last_name, // Payer's last name.  25 char max.
                'suffix' => ''                 // Payer's suffix.  12 char max.
            );

            $BillingAddress = array(
                'street' => $wcmp_checkout_address1, // Required.  First street address.
                'street2' => $wcmp_checkout_address2, // Second street address.
                'city' => $wcmp_checkout_city, // Required.  Name of City.
                'state' => $wcmp_checkout_state, // Required. Name of State or Province.
                'countrycode' => $wcmp_checkout_country, // Required.  Country code.
                'zip' => $wcmp_checkout_zip, // Required.  Postal code of payer.
                'phonenum' => $wcmp_checkout_phone        // Phone Number of payer.  20 char max.
            );

            $ShippingAddress = array(
                'shiptoname' => $wcmp_checkout_first_name . ' ' . $wcmp_checkout_last_name, // Required if shipping is included.  Person's name associated with this address.  32 char max.
                'shiptostreet' => $wcmp_checkout_address1, // Required if shipping is included.  First street address.  100 char max.
                'shiptostreet2' => $wcmp_checkout_address2, // Second street address.  100 char max.
                'shiptocity' => $wcmp_checkout_city, // Required if shipping is included.  Name of city.  40 char max.
                'shiptostate' => $wcmp_checkout_state, // Required if shipping is included.  Name of state or province.  40 char max.
                'shiptozip' => $wcmp_checkout_zip, // Required if shipping is included.  Postal code of shipping address.  20 char max.
                'shiptocountrycode' => $wcmp_checkout_country, // Required if shipping is included.  Country code of shipping address.  2 char max.
                'shiptophonenum' => $wcmp_checkout_phone    // Phone number for shipping address.  20 char max.
            );

            $PayPalRequestData = array(
                'ProfileDetails' => $ProfileDetails,
                'ScheduleDetails' => $ScheduleDetails,
                'BillingPeriod' => $BillingPeriod,
                'CCDetails' => $CCDetails,
                'PayerInfo' => $PayerInfo,
                'PayerName' => $PayerName,
                'BillingAddress' => $BillingAddress,
                'ShippingAddress' => $ShippingAddress,
                'ActivationDetails' => $ActivationDetails
            );
            $PayPalResult = $PayPal->CreateRecurringPaymentsProfile($PayPalRequestData);
            //$PayPalResult = Array('PROFILEID' => "I-LURNH7C6971H", 'PROFILESTATUS' => 'PendingProfile', 'TRANSACTIONID' => '61686356XP007952P', 'TIMESTAMP' => '2016-11-28T13:15:25Z', 'CORRELATIONID' => '1f285163e9a77', 'ACK' => 'Success', 'VERSION' => '64', 'BUILD' => '24616352');
            if (isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                $profile_id = $PayPalResult['PROFILEID'];
                $transaction_id = $PayPalResult['TRANSACTIONID'];
                $timestamp = $PayPalResult['TIMESTAMP'];
                $corelation_id = $PayPalResult['CORRELATIONID'];
                $PROFILESTATUS = $PayPalResult['PROFILESTATUS'];
                $ACK = $PayPalResult['ACK'];
                if (isset($PayPalResult['PROFILEID']) && !empty($PayPalResult['PROFILEID']) && isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                    if (isset($PayPalResult['PROFILESTATUS'])) {
                        $current_user = wp_get_current_user();
                        if ($current_user->ID == 0) {
                            $i = 0;
                            //echo urldecode($wcmp_checkout_Email);die;
                            if (isset($wcmp_checkout_first_name) && !empty($wcmp_checkout_first_name) && isset($wcmp_checkout_Email) && !empty($wcmp_checkout_Email)) {
                                $username = strtolower($wcmp_checkout_first_name);
                                $length = 12;
                                $include_standard_special_chars = false;
                                $random_password = wp_generate_password($length, $include_standard_special_chars);
                                $j = 1;
                                while ($i == 0) {
                                    if (!$this->is_user_exits($username)) {
                                        $i = 1;
                                    } else {
                                        $username = $username . $j;
                                        $j++;
                                    }
                                }
                                $user_id = wp_create_user($username, $random_password, urldecode($wcmp_checkout_Email));
                                if (!is_wp_error($user_id)) {
                                    $userdata = array(
                                        'ID' => $user_id,
                                        'display_name' => $wcmp_checkout_first_name . ' ' . $wcmp_checkout_last_name,
                                        'nickname' => $username,
                                        'first_name' => $wcmp_checkout_first_name,
                                        'last_name' => $wcmp_checkout_last_name
                                    );
                                    wp_update_user($userdata);
                                    $accessmeta = get_post_meta($plan_id, '_vender_access', true);
                                    $_vendor_limitation_field = get_post_meta($plan_id, '_vendor_limitation_field', true);
                                    if ($PayPalResult['PROFILESTATUS'] == 'ActiveProfile') {
                                        update_user_meta($user_id, 'wcmp_vendor_plan_status', 'active');
                                        update_user_meta($user_id, 'vendor_group_id', $plan_id);
                                        update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_TRANSACTIONID', $transaction_id);
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                                        $user = new WP_User($user_id);
                                        if (isset($vendor_access) && $vendor_access == 'Enable') {
                                            $user->set_role('dc_vendor');
                                        } else {
                                            $user->set_role('dc_pending_vendor');
                                        }
                                        update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                                    } else {
                                        update_user_meta($user_id, 'vendor_group_id', $plan_id);
                                        update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_TRANSACTIONID', $transaction_id);
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                                        update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                                        $user = new WP_User($user_id);
                                        $user->set_role('dc_pending_vendor');
                                        update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                                    }
                                    /*                                     * ****************************************** */
                                    if (isset($billing['_is_trial']) && $billing['_is_trial'] == 'yes') {
                                        update_user_meta($user_id, '_is_trial', 1);
                                        update_user_meta($user_id, '_trial_amt', $_trial_amt);
                                        update_user_meta($user_id, '_trial_amt_cycle', $_trial_amt_cycle);
                                        update_user_meta($user_id, '_trial_amt_cycle_limit', $_trial_amt_cycle_limit);
                                        if (!empty($_trial_amt) && $_trial_amt > 0) {
                                            $cycle = $this->cycle_to_day($_trial_amt_cycle);
                                            $date = date('Y-m-d');
                                            $date = strtotime($date);
                                            $date = strtotime("+" . $cycle . " day", $date);
                                            update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                        } else {
                                            $cycle = $this->cycle_to_day($_trial_amt_cycle);
                                            $cycle = $cycle * $_trial_amt_cycle_limit;
                                            $date = date('Y-m-d');
                                            $date = strtotime($date);
                                            $date = strtotime("+" . $cycle . " day", $date);
                                            update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                        }
                                    } else {
                                        $cycle = $this->cycle_to_day($_vendor_billing_amt_cycle);
                                        $date = date('Y-m-d');
                                        $date = strtotime($date);
                                        $date = strtotime("+" . $cycle . " day", $date);
                                        update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                    }
                                    if (isset($billing['_is_recurring']) && $billing['_is_recurring'] == 'yes') {
                                        update_user_meta($user_id, '_initial_payment', $_initial_payment);
                                        update_user_meta($user_id, '_vendor_billing_amt', $_vendor_billing_amt);
                                        update_user_meta($user_id, '_vendor_billing_amt_cycle', $_vendor_billing_amt_cycle);
                                        update_user_meta($user_id, '_vendor_billing_amt_cycle_limit', $_vendor_billing_amt_cycle_limit);
                                        update_user_meta($user_id, '_vendor_billing_amt_tax', $_vendor_billing_amt_tax);
                                    }
                                    update_user_meta($user_id, '_vendor_grace_period_days', $billing['_vendor_grace_period_days']);

                                    /*    update billing info */
                                    update_user_meta($user_id, 'billing_first_name', $wcmp_checkout_first_name);
                                    update_user_meta($user_id, 'billing_last_name', $wcmp_checkout_last_name);
                                    update_user_meta($user_id, 'billing_company', $wcmp_checkout_company);
                                    update_user_meta($user_id, 'billing_address_1', $wcmp_checkout_address1);
                                    update_user_meta($user_id, 'billing_address_2', $wcmp_checkout_address2);
                                    update_user_meta($user_id, 'billing_city', $wcmp_checkout_city);
                                    update_user_meta($user_id, 'billing_postcode', $wcmp_checkout_zip);
                                    update_user_meta($user_id, 'billing_country', $wcmp_checkout_country);
                                    update_user_meta($user_id, 'billing_state', $wcmp_checkout_state);
                                    update_user_meta($user_id, 'billing_phone', $wcmp_checkout_phone);
                                    update_user_meta($user_id, 'billing_email', $wcmp_checkout_Email);

                                    $email = WC()->mailer()->emails['WCMP_Vendor_Membership_Email_New_Subscription'];
                                    $email->trigger($user_id, $random_password);
                                }
                            }
                        } else {
                            $user_id = $current_user->data->ID;
                            if (!is_wp_error($user_id)) {
                                $accessmeta = get_post_meta($plan_id, '_vender_access', true);
                                $_vendor_limitation_field = get_post_meta($plan_id, '_vendor_limitation_field', true);
                                if ($PayPalResult['PROFILESTATUS'] == 'ActiveProfile') {
                                    update_user_meta($user_id, 'wcmp_vendor_plan_status', 'active');
                                    update_user_meta($user_id, 'vendor_group_id', $plan_id);
                                    update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TRANSACTIONID', $transaction_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                                    $user = new WP_User($user_id);
                                    $user->set_role('dc_vendor');
                                    update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                                } else {
                                    update_user_meta($user_id, 'vendor_group_id', $plan_id);
                                    update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TRANSACTIONID', $transaction_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                                    $user = new WP_User($user_id);
                                    $user->set_role('dc_pending_vendor');
                                    update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                                }
                                /*                                 * ****************************************** */
                                if (isset($billing['_is_trial']) && $billing['_is_trial'] == 'yes') {
                                    update_user_meta($user_id, '_is_trial', 1);
                                    update_user_meta($user_id, '_trial_amt', $_trial_amt);
                                    update_user_meta($user_id, '_trial_amt_cycle', $_trial_amt_cycle);
                                    update_user_meta($user_id, '_trial_amt_cycle_limit', $_trial_amt_cycle_limit);
                                    if (!empty($_trial_amt) && $_trial_amt > 0) {
                                        $cycle = $this->cycle_to_day($_trial_amt_cycle);
                                        $date = date('Y-m-d');
                                        $date = strtotime($date);
                                        $date = strtotime("+" . $cycle . " day", $date);
                                        update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                    } else {
                                        $cycle = $this->cycle_to_day($_trial_amt_cycle);
                                        $cycle = $cycle * $_trial_amt_cycle_limit;
                                        $date = date('Y-m-d');
                                        $date = strtotime($date);
                                        $date = strtotime("+" . $cycle . " day", $date);
                                        update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                    }
                                } else {
                                    $cycle = $this->cycle_to_day($_vendor_billing_amt_cycle);
                                    $date = date('Y-m-d');
                                    $date = strtotime($date);
                                    $date = strtotime("+" . $cycle . " day", $date);
                                    update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));
                                }
                                if (isset($billing['_is_recurring']) && $billing['_is_recurring'] == 'yes') {
                                    update_user_meta($user_id, '_initial_payment', $_initial_payment);
                                    update_user_meta($user_id, '_vendor_billing_amt', $_vendor_billing_amt);
                                    update_user_meta($user_id, '_vendor_billing_amt_cycle', $_vendor_billing_amt_cycle);
                                    update_user_meta($user_id, '_vendor_billing_amt_cycle_limit', $_vendor_billing_amt_cycle_limit);
                                    update_user_meta($user_id, '_vendor_billing_amt_tax', $_vendor_billing_amt_tax);
                                }
                                update_user_meta($user_id, '_vendor_grace_period_days', $billing['_vendor_grace_period_days']);

                                /*    update billing info */
                                update_user_meta($user_id, 'billing_first_name', $wcmp_checkout_first_name);
                                update_user_meta($user_id, 'billing_last_name', $wcmp_checkout_last_name);
                                update_user_meta($user_id, 'billing_company', $wcmp_checkout_company);
                                update_user_meta($user_id, 'billing_address_1', $wcmp_checkout_address1);
                                update_user_meta($user_id, 'billing_address_2', $wcmp_checkout_address2);
                                update_user_meta($user_id, 'billing_city', $wcmp_checkout_city);
                                update_user_meta($user_id, 'billing_postcode', $wcmp_checkout_zip);
                                update_user_meta($user_id, 'billing_country', $wcmp_checkout_country);
                                update_user_meta($user_id, 'billing_state', $wcmp_checkout_state);
                                update_user_meta($user_id, 'billing_phone', $wcmp_checkout_phone);
                                update_user_meta($user_id, 'billing_email', $wcmp_checkout_Email);
                            }
                        }
                    }
                }
                $success_msg = isset($global_settings['_success_msg']) && !empty($global_settings['_failuare_msg']) ? $global_settings['_success_msg'] : __('You have successfully subscribe.', 'wcmp-vendor_membership');
                echo $success_msg;
                die;
            } else {
                $failuare_msg = isset($global_settings['_failuare_msg']) && !empty($global_settings['_failuare_msg']) ? $global_settings['_failuare_msg'] : __('Your transaction failed.', 'wcmp-vendor_membership');
                echo $failuare_msg;
                die;
            }
        } else {
            $failuare_msg = isset($global_settings['_failuare_msg']) && !empty($global_settings['_failuare_msg']) ? $global_settings['_failuare_msg'] : __('Your transaction failed.', 'wcmp-vendor_membership');
            echo $failuare_msg;
            die;
        }
        die;
    }

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

    function paypal_api_call_cancel_process() {
        global $WCMP_Vendor_Membership;
        $current_user = wp_get_current_user();
        if (isset($current_user->data->ID)) {
            $user_id = $current_user->data->ID;
        } else {
            exit;
        }
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
            if (isset($global_settings['api_username']))
                $api_username = $global_settings['api_username'];
            if (isset($global_settings['api_password']))
                $api_password = $global_settings['api_password'];
            if (isset($global_settings['api_signature']))
                $api_signature = $global_settings['api_signature'];
        }
        else {
            if (isset($global_settings['api_username_live']))
                $api_username = $global_settings['api_username_live'];
            if (isset($global_settings['api_password_live']))
                $api_password = $global_settings['api_password_live'];
            if (isset($global_settings['api_signature_live']))
                $api_signature = $global_settings['api_signature_live'];
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
            $PayPalRequestData = array(
                'PROFILEID' => get_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', TRUE),
                'ACTION' => 'cancel'
            );
            $PayPalResult = $PayPal->ManageRecurringPaymentsProfileStatus($PayPalRequestData);
            if (isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'cancel');
            }
        }
        die;
    }

    function upgrade_downgrade_profile() {
        global $WCMP_Vendor_Membership;

        $user_id = isset($current_user->data->ID) ? $current_user->data->ID : '';
        $wcmp_vendor_subscription_status = '';
        $class = 'new-subscriber';
        if (!empty($user_id)) {
            $wcmp_vendor_subscription_status = get_user_meta($user_id, 'wcmp_vendor_subscription_status', TRUE);
            $wcmp_vendor_subscription_PROFILEID = get_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', TRUE);
            if (!empty($wcmp_vendor_subscription_PROFILEID) && ($wcmp_vendor_subscription_status != 'cancel' || !empty($wcmp_vendor_subscription_status) )) {
                $class = 'new-subscriber';
            }
        }

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
        $user_type_for_registration = $_POST['user_type_for_registration'];
        $wcmp_checkout_Email = urlencode($_POST['wcmp_checkout_Email']);
        $wcmp_checkout_first_name = urlencode($_POST['wcmp_checkout_first_name']);
        $wcmp_checkout_last_name = urlencode($_POST['wcmp_checkout_last_name']);
        $wcmp_checkout_company = urlencode($_POST['wcmp_checkout_company']);
        $wcmp_checkout_address1 = urlencode($_POST['wcmp_checkout_address1']);
        $wcmp_checkout_address2 = urlencode($_POST['wcmp_checkout_address2']);
        $wcmp_checkout_country = urlencode($_POST['wcmp_checkout_country']);
        $wcmp_checkout_state = urlencode($_POST['wcmp_checkout_state']);
        $wcmp_checkout_city = urlencode($_POST['wcmp_checkout_city']);
        $wcmp_checkout_zip = urlencode($_POST['wcmp_checkout_zip']);
        $wcmp_checkout_phone = urlencode($_POST['wcmp_checkout_phone']);


        $plan_id = $_SESSION['plan_id'];
        $global_settings = $WCMP_Vendor_Membership->get_global_settings();
        if (isset($_SESSION['plan_meta']['_vendor_billing_field'])) {
            $billing = unserialize($_SESSION['plan_meta']['_vendor_billing_field'][0]);
        }
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
            $_initial_payment = isset($billing['_initial_payment']) ? number_format($billing['_initial_payment'], 2) : '';
            $_vendor_billing_amt = isset($billing['_vendor_billing_amt']) ? number_format($billing['_vendor_billing_amt'], 2) : '';
            $_vendor_billing_amt_cycle = isset($billing['_vendor_billing_amt_cycle']) ? $billing['_vendor_billing_amt_cycle'] : '';
            $_vendor_billing_amt_cycle_limit = isset($billing['_vendor_billing_amt_cycle_limit']) ? $billing['_vendor_billing_amt_cycle_limit'] : 0;
            $_vendor_billing_freequency = 1;
            $_vendor_billing_amt_tax = isset($billing['_vendor_billing_amt_tax']) ? number_format($billing['_vendor_billing_amt_tax'], 2) : 0;
        } else {
            $_initial_payment = isset($billing['_initial_payment']) ? number_format($billing['_initial_payment'], 2) : '';
            $_vendor_billing_amt = isset($billing['_initial_payment']) ? number_format($billing['_initial_payment'], 2) : '';
            $_vendor_billing_amt_cycle = 'Year';
            $_vendor_billing_amt_cycle_limit = 1;
            $_vendor_billing_freequency = 1;
        }

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
            //$PayPal = new wcmp\PayPal\PayPal($PayPalConfig);
            $PayPal = new WCMP_Vendor_Membership_Recurring_Payments($PayPalConfig);
            $CRPPFields = array('token' => ''); // Token returned from PayPal SetExpressCheckout.  Can also use token returned from SetCustomerBillingAgreement.
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
                'desc' => $billing['_plan_short_desc'], // Required.  Description of the recurring payment.  This field must match the corresponding billing agreement description included in SetExpressCheckout.
                'maxfailedpayments' => $billing['_max_attamped'], // The number of scheduled payment periods that can fail before the profile is automatically suspended.  
                'autobillamt' => 'AddToNextBilling'                   // This field indicates whether you would like PayPal to automatically bill the outstanding balance amount in the next billing cycle.  Values can be: NoAutoBill or AddToNextBilling
            );

            $BillingPeriod = array(
                'is_trial' => 'no',
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

            /* $ActivationDetails = array(
              'initamt' => $_initial_payment, 							          // Initial non-recurring payment amount due immediatly upon profile creation.  Use an initial amount for enrolment or set-up fees.
              'failedinitamtaction' => 'CancelOnFailure', 				    // By default, PayPal will suspend the pending profile in the event that the initial payment fails.  You can override this.  Values are: ContinueOnFailure or CancelOnFailure
              ); */

            $CCDetails = array(
                'creditcardtype' => $c_type, // Required. Type of credit card.  Visa, MasterCard, Discover, Amex, Maestro, Solo.  If Maestro or Solo, the currency code must be GBP.  In addition, either start date or issue number must be specified.
                'acct' => $c_number, // Required.  Credit card number.  No spaces or punctuation.  
                'expdate' => $c_month . $c_year, // Required.  Credit card expiration date.  Format is MMYYYY
                'cvv2' => $c_cvv, // Requirements determined by your PayPal account settings.  Security digits for credit card.
                'startdate' => '', // Month and year that Maestro or Solo card was issued.  MMYYYY
                'issuenumber' => ''               // Issue number of Maestro or Solo card.  Two numeric digits max.
            );

            $PayerInfo = array(
                'email' => $wcmp_checkout_Email, // Email address of payer.
                'payerid' => '', // Unique PayPal customer ID for payer.
                'payerstatus' => '', // Status of payer.  Values are verified or unverified
                'countrycode' => '', // Payer's country of residence in the form of the two letter code.
                'business' => $wcmp_checkout_company        // Payer's business name.
            );

            $PayerName = array(
                'salutation' => 'Dear', // Payer's salutation.  20 char max.
                'firstname' => $wcmp_checkout_first_name, // Payer's first name.  25 char max.
                'middlename' => '', // Payer's middle name.  25 char max.
                'lastname' => $wcmp_checkout_last_name, // Payer's last name.  25 char max.
                'suffix' => ''                 // Payer's suffix.  12 char max.
            );

            $BillingAddress = array(
                'street' => $wcmp_checkout_address1, // Required.  First street address.
                'street2' => $wcmp_checkout_address2, // Second street address.
                'city' => $wcmp_checkout_city, // Required.  Name of City.
                'state' => $wcmp_checkout_state, // Required. Name of State or Province.
                'countrycode' => $wcmp_checkout_country, // Required.  Country code.
                'zip' => $wcmp_checkout_zip, // Required.  Postal code of payer.
                'phonenum' => $wcmp_checkout_phone        // Phone Number of payer.  20 char max.
            );

            $ShippingAddress = array(
                'shiptoname' => $wcmp_checkout_first_name . ' ' . $wcmp_checkout_last_name, // Required if shipping is included.  Person's name associated with this address.  32 char max.
                'shiptostreet' => $wcmp_checkout_address1, // Required if shipping is included.  First street address.  100 char max.
                'shiptostreet2' => $wcmp_checkout_address2, // Second street address.  100 char max.
                'shiptocity' => $wcmp_checkout_city, // Required if shipping is included.  Name of city.  40 char max.
                'shiptostate' => $wcmp_checkout_state, // Required if shipping is included.  Name of state or province.  40 char max.
                'shiptozip' => $wcmp_checkout_zip, // Required if shipping is included.  Postal code of shipping address.  20 char max.
                'shiptocountrycode' => $wcmp_checkout_country, // Required if shipping is included.  Country code of shipping address.  2 char max.
                'shiptophonenum' => $wcmp_checkout_phone    // Phone number for shipping address.  20 char max.
            );

            $PayPalRequestData = array(
                'ProfileDetails' => $ProfileDetails,
                'ScheduleDetails' => $ScheduleDetails,
                'BillingPeriod' => $BillingPeriod,
                'CCDetails' => $CCDetails,
                'PayerInfo' => $PayerInfo,
                'PayerName' => $PayerName,
                'BillingAddress' => $BillingAddress,
                'ShippingAddress' => $ShippingAddress,
                'ActivationDetails' => $ActivationDetails
            );
            // Pass data into class for processing with PayPal and load the response array into $PayPalResult
            $PayPalResult = $PayPal->CreateRecurringPaymentsProfile($PayPalRequestData);
            // Write the contents of the response array to the screen for demo purposes.
            if (empty($PayPalResult['ERRORS'])) {
                $profile_id = $PayPalResult['PROFILEID'];
                $transaction_id = $PayPalResult['TRANSACTIONID'];
                $timestamp = $PayPalResult['TIMESTAMP'];
                $corelation_id = $PayPalResult['CORRELATIONID'];
                $PROFILESTATUS = $PayPalResult['PROFILESTATUS'];
                $ACK = $PayPalResult['ACK'];
                if (isset($PayPalResult['PROFILEID']) && !empty($PayPalResult['PROFILEID']) && isset($PayPalResult['ACK']) && $PayPalResult['ACK'] == 'Success') {
                    if (isset($PayPalResult['PROFILESTATUS'])) {
                        $current_user = wp_get_current_user();
                        if ($current_user->ID == 0) {
                            
                        } else {
                            $user_id = $current_user->data->ID;
                            if (!is_wp_error($user_id)) {
                                $accessmeta = get_post_meta($plan_id, '_vender_access', true);
                                $_vendor_limitation_field = get_post_meta($plan_id, '_vendor_limitation_field', true);
                                if ($PayPalResult['PROFILESTATUS'] == 'ActiveProfile') {
                                    update_user_meta($user_id, 'wcmp_vendor_plan_status', 'active');
                                    update_user_meta($user_id, 'vendor_group_id', $plan_id);
                                    update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TRANSACTIONID', $transaction_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                                    $user = new WP_User($user_id);
                                    $user->set_role('dc_vendor');
                                    update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                                } else {
                                    update_user_meta($user_id, 'vendor_group_id', $plan_id);
                                    update_user_meta($user_id, 'vendor_plan_start_date_time', @date('Y-m-d H:i:s'));
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_status', 'active');
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', $profile_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TRANSACTIONID', $transaction_id);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_TIMESTAMP', $timestamp);
                                    update_user_meta($user_id, 'wcmp_vendor_subscription_CORRELATIONID', $corelation_id);
                                    $user = new WP_User($user_id);
                                    $user->set_role('dc_pending_vendor');
                                    update_user_meta($user_id, '_vendor_limitation_field', $_vendor_limitation_field);
                                }
                                /*                                 * ****************************************** */
                                $cycle = $this->cycle_to_day($_vendor_billing_amt_cycle);
                                $date = date('Y-m-d');
                                $date = strtotime($date);
                                $date = strtotime("+" . $cycle . " day", $date);
                                update_user_meta($user_id, '_next_payment_date', date("Y-m-d", $date));

                                if (isset($billing['_is_recurring']) && $billing['_is_recurring'] == 'yes') {
                                    update_user_meta($user_id, '_initial_payment', $_initial_payment);
                                    update_user_meta($user_id, '_vendor_billing_amt', $_vendor_billing_amt);
                                    update_user_meta($user_id, '_vendor_billing_amt_cycle', $_vendor_billing_amt_cycle);
                                    update_user_meta($user_id, '_vendor_billing_amt_cycle_limit', $_vendor_billing_amt_cycle_limit);
                                    update_user_meta($user_id, '_vendor_billing_amt_tax', $_vendor_billing_amt_tax);
                                }
                                update_user_meta($user_id, '_vendor_grace_period_days', $billing['_vendor_grace_period_days']);

                                /*    update billing info */
                                update_user_meta($user_id, 'billing_first_name', $wcmp_checkout_first_name);
                                update_user_meta($user_id, 'billing_last_name', $wcmp_checkout_last_name);
                                update_user_meta($user_id, 'billing_company', $wcmp_checkout_company);
                                update_user_meta($user_id, 'billing_address_1', $wcmp_checkout_address1);
                                update_user_meta($user_id, 'billing_address_2', $wcmp_checkout_address2);
                                update_user_meta($user_id, 'billing_city', $wcmp_checkout_city);
                                update_user_meta($user_id, 'billing_postcode', $wcmp_checkout_zip);
                                update_user_meta($user_id, 'billing_country', $wcmp_checkout_country);
                                update_user_meta($user_id, 'billing_state', $wcmp_checkout_state);
                                update_user_meta($user_id, 'billing_phone', $wcmp_checkout_phone);
                                update_user_meta($user_id, 'billing_email', $wcmp_checkout_Email);

                                $email = WC()->mailer()->emails['WCMP_Vendor_Membership_Email_New_Subscription'];
                                $email->trigger($user_id, $random_password);
                            }
                        }
                    }
                }
                $success_msg = isset($global_settings['_success_msg']) ? $global_settings['_success_msg'] : __('You have successfully subscribe.', 'wcmp-vendor_membership');
                echo $success_msg;
            } else {
                $failuare_msg = isset($global_settings['_failuare_msg']) ? $global_settings['_failuare_msg'] : __('Your transaction failed.', 'wcmp-vendor_membership');
                echo $failuare_msg;
            }
        } else {
            echo __('Your transaction failed.', 'wcmp-vendor_membership');
        }
        exit;
    }

    function get_profile($profileid = '') {
        global $WCMP_Vendor_Membership;
        require_once($WCMP_Vendor_Membership->plugin_path . 'lib/paypal/autoload.php');
        $global_settings = $WCMP_Vendor_Membership->get_global_settings();
        if (isset($_POST['profileid'])) {
            $profileid = $_POST['profileid'];
        }
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
            if (isset($global_settings['api_username']))
                $api_username = $global_settings['api_username'];
            if (isset($global_settings['api_password']))
                $api_password = $global_settings['api_password'];
            if (isset($global_settings['api_signature']))
                $api_signature = $global_settings['api_signature'];
        }
        else {
            if (isset($global_settings['api_username_live']))
                $api_username = $global_settings['api_username_live'];
            if (isset($global_settings['api_password_live']))
                $api_password = $global_settings['api_password_live'];
            if (isset($global_settings['api_signature_live']))
                $api_signature = $global_settings['api_signature_live'];
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
            $PayPal = new wcmp\PayPal\PayPal($PayPalConfig);
            if ($profileid != '') {
                $GRPPDFields = array(
                    'profileid' => $profileid   // Profile ID of the profile you want to get details for.
                );
                $PayPalRequestData = array('GRPPDFields' => $GRPPDFields);
                $PayPalResult = $PayPal->GetRecurringPaymentsProfileDetails($PayPalRequestData);
                if (isset($_POST['action'])) {
                    // do the ajax stuff
                } else {
                    // do the calling function stuff					
                    return $PayPalResult;
                }
            } else {
                echo __('Enter Profile Id', 'wcmp-vendor_membership');
            }
        }
        die;
    }

    function is_user_exits($username) {
        global $WCMP_Vendor_Membership;
        return username_exists($username);
    }

    function is_user_email_exits($email) {
        global $WCMP_Vendor_Membership;
        return email_exists($email);
    }

}
