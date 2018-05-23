<?php

class WCMP_Vendor_Membership {

    public $plugin_url;
    public $plugin_path;
    public $version;
    public $token;
    public $text_domain;
    public $shortcode;
    public $admin;
    public $frontend;
    public $template;
    public $ajax;
    private $file;
    public $settings;
    public $license;
    public $post_type;
    public $corn;

    public function __construct($file) {

        $this->file = $file;
        $this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
        $this->plugin_path = trailingslashit(dirname($file));
        $this->token = WCMP_VENDOR_MEMBERSHIP_PLUGIN_TOKEN;
        $this->text_domain = WCMP_VENDOR_MEMBERSHIP_TEXT_DOMAIN;
        $this->version = WCMP_VENDOR_MEMBERSHIP_PLUGIN_VERSION;

        add_action('init', array(&$this, 'init'));
        add_action('wcmp_set_user_role', array(&$this, 'set_user_role_capabilities'), 10, 3);
        add_action('wcmp_add_user_role', array(&$this, 'add_user_role_capabilities'), 40, 2);
        add_filter('woocommerce_email_classes', array(&$this, 'wcmp_vendor_membership_email_classes'));
        add_filter('wp_insert_post_data', array($this, 'filter_post_data'), '99', 2);
        add_action('delete_user', array(&$this, 'delete_associated_registration_form_and_paypal_profile'));
        add_filter('wcmp_get_commission_amount', array(&$this, 'wcmp_vendor_membership_get_commission_amount'), 10, 6);

        add_action('wcmp_init', array(&$this, 'init_after_wcmp_load'));
    }

    /**
     * initilize plugin on WP init
     */
    function init() {
        // Init Text Domain
        $this->load_plugin_textdomain();

        // Init ajax
        if (defined('DOING_AJAX')) {
            $this->load_class('ajax');
            $this->ajax = new WCMP_Vendor_Membership_Ajax();
        }

        if (is_admin()) {
            $this->load_class('admin');
            $this->admin = new WCMP_Vendor_Membership_Admin();
        }

        if (!is_admin() || defined('DOING_AJAX')) {
            $this->load_class('frontend');
            $this->frontend = new WCMP_Vendor_Membership_Frontend();

            // init shortcode
            $this->load_class('shortcode');
            $this->shortcode = new WCMP_Vendor_Membership_Shortcode();

            // init templates
            $this->load_class('template');
            $this->template = new WCMP_Vendor_Membership_Template();
        }
        $this->load_class('posttype');
        $this->post_type = new WCMP_Vendor_Membership_Posttype();
        // DC License Activation
        if (is_admin()) {
            $this->load_class('license');
            $this->license = WCMP_Vendor_Membership_LICENSE();
        }
        $this->load_class('recurring-payments');
        $this->load_class('cron');
        $this->cron = new WCMP_Vendor_Membership_Cron();

        $this->do_membership_plan_upgrade();
    }

    function init_after_wcmp_load() {
        add_action('settings_vendor_general_tab_options', array(&$this, 'add_membership_endpoint_option'));
        add_filter('settings_vendor_general_tab_new_input', array(&$this, 'save_vendor_membership_endpoint_option'), 10, 2);
        add_filter('wcmp_endpoints_query_vars', array(&$this, 'add_wcmp_endpoints_query_vars'));
        add_filter('wcmp_vendor_dashboard_nav', array(&$this, 'add_tab_to_vendor_dashboard'));
    }

    public function add_membership_endpoint_option($settings_tab_options) {
        global $WCMP_Vendor_Membership;
        $settings_tab_options['sections']['wcmp_vendor_general_settings_endpoint_ssection']['fields']['wcmp_vendor_membership_endpoint'] = array('title' => __('Vendor University', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'wcmp_vendor_membership_endpoint', 'label_for' => 'wcmp_vendor_membership_endpoint', 'name' => 'wcmp_vendor_membership_endpoint', 'hints' => __('Set endpoint for vendor membership page', 'wcmp-vendor_membership'), 'placeholder' => 'membership-details');
        return $settings_tab_options;
    }

    public function save_vendor_membership_endpoint_option($new_input, $input) {
        if (isset($input['wcmp_vendor_membership_endpoint']) && !empty($input['wcmp_vendor_membership_endpoint'])) {
            $new_input['wcmp_vendor_membership_endpoint'] = sanitize_text_field($input['wcmp_vendor_membership_endpoint']);
        }
        return $new_input;
    }

    public function add_wcmp_endpoints_query_vars($endpoints) {
        global $WCMP_Vendor_Membership;
        $endpoints['membership-details'] = array(
            'label' => __('Membership', 'wcmp-vendor_membership'),
            'endpoint' => get_wcmp_vendor_settings('wcmp_vendor_membership_endpoint', 'vendor', 'general', 'membership-details')
        );
        if (!get_option('vendor_membership_added')) {
            flush_rewrite_rules();
            update_option('vendor_membership_added', 1);
        }
        return $endpoints;
    }

    public function add_tab_to_vendor_dashboard($nav) {
        global $WCMP_Vendor_Membership;
        $nav['store-settings']['submenu']['membership-details'] = array(
            'label' => __('Membership', 'wcmp-vendor_membership')
            , 'url' => wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_membership_endpoint', 'vendor', 'general', 'membership-details'))
            , 'capability' => apply_filters('wcmp_vendor_dashboard_menu_membership-details_capability', true)
            , 'position' => 50
            , 'link_target' => '_self'
        );
        return $nav;
    }

    /*
     * delete vendor registartion form data
     */

    function delete_associated_registration_form_and_paypal_profile($user_id) {
        global $WCMP_Vendor_Membership;
        $wcmp_vendor_registration_form_id = get_user_meta($user_id, 'wcmp_vendor_registration_form_id', true);
        if ($wcmp_vendor_registration_form_id) {
            wp_delete_post($wcmp_vendor_registration_form_id);
        }
        $profile_id = get_user_meta($user_id, 'wcmp_vendor_subscription_PROFILEID', true);
        if (!empty($profile_id)) {
            $WCMP_Vendor_Membership->load_class('get-payments');
            $WCMp_Vendor_Membership_Get_Payments = new WCMp_Vendor_Membership_Get_Payments($user_id, 0);
            $WCMp_Vendor_Membership_Get_Payments->CancelRecurringPaymentProfile($profile_id);
        }
    }

    /**
     * Set vendor user role and associate capabilities
     *
     * @access public
     * @param user_id, new role, old role
     * @return void
     */
    public function set_user_role_capabilities($user_id, $new_role, $old_role) {
        global $WCMp, $WCMP_Vendor_Membership;
        update_user_meta($user_id, 'wcmp_vendor_plan_status', 'active');
        $user = new WP_User($user_id);
        $capabilities_arr = array();
        $plan_id = get_user_meta($user_id, 'vendor_group_id', true);
        $plan_status = get_user_meta($user_id, 'wcmp_vendor_plan_status', true);
        if (!empty($plan_id)) {
            if (strtolower($plan_status) == 'active') {
                $plan_meta = get_post_meta($plan_id);
                $_vendor_capabilities_field_gen = unserialize($plan_meta['_vendor_capabilities_field_gen'][0]);
                if (!empty($_vendor_capabilities_field_gen) && is_array($_vendor_capabilities_field_gen)) {
                    $capabilities_arr = array_merge($capabilities_arr, $_vendor_capabilities_field_gen);
                }
                if ($user_id && $new_role == 'dc_vendor') {
                    $caps = $this->get_vendor_caps();
                    foreach ($caps as $cap) {
                        $user->remove_cap($cap);
                    }
                    if (!empty($capabilities_arr) && is_array($capabilities_arr)) {
                        if (in_array('is_submit_product', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_submit_product')) {
                            update_user_meta($user_id, '_vendor_submit_product', 'Enable');
                            $caps = array();
                            $caps[] = "edit_product";
                            $caps[] = "delete_product";
                            $caps[] = "edit_products";
                            $caps[] = "delete_products";

                            if (in_array('is_published_product', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_published_product')) {
                                $caps[] = "publish_products";
                                if (in_array('is_edit_delete_published_product', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_edit_delete_published_product')) {
                                    $caps[] = "edit_published_products";
                                    $caps[] = "delete_published_products";
                                }
                            }
                            $capabilities_arr = array_merge($capabilities_arr, $caps);
                        } else {
                            delete_user_meta($user_id, '_vendor_submit_product');
                        }
                        if (in_array('is_submit_coupon', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_submit_coupon')) {
                            update_user_meta($user_id, '_vendor_submit_coupon', 'Enable');
                            $caps = array();
                            $caps[] = 'edit_shop_coupons';
                            $caps[] = 'read_shop_coupons';
                            $caps[] = 'delete_shop_coupons';

                            if (in_array('is_published_coupon', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_published_coupon')) {
                                $caps[] = "publish_shop_coupons";
                                if (in_array('is_edit_delete_published_coupon', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_edit_delete_published_coupon')) {
                                    $caps[] = 'edit_published_shop_coupons';
                                    $caps[] = 'delete_published_shop_coupons';
                                }
                            }
                            $capabilities_arr = array_merge($capabilities_arr, $caps);
                        } else {
                            delete_user_meta($user_id, '_vendor_submit_coupon');
                        }
                        if (in_array('is_upload_files', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_upload_files')) {
                            $caps = array();
                            $caps[] = "upload_files";
                            $capabilities_arr = array_merge($capabilities_arr, $caps);
                        }
                        $capabilities_arr[] = "read_product";
                        $capabilities_arr[] = "read_shop_coupon";
                        $capabilities_arr[] = "assign_product_terms";
                        foreach ($capabilities_arr as $capability) {
                            $user->add_cap($capability);
                        }
                    }
                    $vendor = get_wcmp_vendor($user_id);
                    $vendor->generate_term();
                }
                do_action('wcmp_membership_set_user_role', $user_id, $new_role, $old_role, $plan_id);
            }
        }
    }

    /**
     * Upgrade Membership plan 
     */
    public function do_membership_plan_upgrade() {
        if (is_user_logged_in() && !is_admin() && isset($_POST['do_vendor_membership_upgrade'])) {
            $plan_id = isset($_POST['vendor_membership_plan_id']) ? $_POST['vendor_membership_plan_id'] : '';
            $profile_id = isset($_POST['profile_id']) ? $_POST['profile_id'] : '';
            $payment_method = isset($_POST['wvm_payment_method']) ? $_POST['wvm_payment_method'] : '';
            if (get_post_meta($plan_id, '_is_free_plan', true) == 'Enable') {
                $payment_method = 'free';
            }
            $user_id = get_current_user_id();
            $this->load_class('get-payments');
            $WCMp_Vendor_Membership_Get_Payments = new WCMp_Vendor_Membership_Get_Payments($user_id, $plan_id, $payment_method);
            if (!empty($plan_id) && !empty($profile_id)) {
                $WCMp_Vendor_Membership_Get_Payments->CancelRecurringPaymentProfile($profile_id);
            }
            if (get_user_meta($user_id, 'vendor_group_id', true)) {
                $status = $WCMp_Vendor_Membership_Get_Payments->create_payment(false);
            } else{
                $status = $WCMp_Vendor_Membership_Get_Payments->create_payment();
            }
            if ($status) {
                wp_safe_redirect(get_permalink(wcmp_vendor_dashboard_page_id()));
                exit();
            } else {
                wp_safe_redirect(get_permalink(wcmp_vendor_registration_page_id()) . '?haserror=1');
                exit();
            }
        }
    }

    /**
     * override vendor commition filter
     * @access public
     * @param array $commission, $item_id
     * @param string $product_id, $vendor_id, $variation_id, $item_id
     * @return array $commission
     */
    public function wcmp_vendor_membership_get_commission_amount($commission, $product_id, $vendor_id, $variation_id, $item_id, $order) {
        global $WCMp;

        $data = array();
        if ($product_id > 0 && $vendor_id > 0) {
            $vendor_idd = wc_get_order_item_meta($item_id, '_vendor_id', true);
            if ($vendor_idd) {
                $vendor = get_wcmp_vendor($vendor_idd);
            } else {
                $vendor = get_wcmp_product_vendors($product_id);
            }
            $plan_id = get_user_meta($vendor->id, 'vendor_group_id', true);
            $plan_wise_commission = get_post_meta($plan_id, '_vendor_commission', true) ? get_post_meta($plan_id, '_vendor_commission', true) : 0;
            if ($vendor->term_id == $vendor_id && !empty($plan_id)) {
                if ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {

                    if ($variation_id > 0) {
                        $data['commission_val'] = get_post_meta($variation_id, '_product_vendors_commission_percentage', true);
                        $data['commission_fixed'] = get_post_meta($variation_id, '_product_vendors_commission_fixed_per_trans', true);
                        if (empty($data)) {
                            $data['commission_val'] = get_post_meta($product_id, '_commission_percentage_per_product', true);
                            $data['commission_fixed'] = get_post_meta($product_id, '_commission_fixed_with_percentage', true);
                        }
                    } else {
                        $data['commission_val'] = get_post_meta($product_id, '_commission_percentage_per_product', true);
                        $data['commission_fixed'] = get_post_meta($product_id, '_commission_fixed_with_percentage', true);
                    }
                    if (!empty($data['commission_val'])) {
                        return $data; // Use product commission percentage first
                    } else {
                        if ($plan_wise_commission > 0) {
                            return array('commission_val' => $plan_wise_commission, 'commission_fixed' => $WCMp->vendor_caps->payment_cap['fixed_with_percentage']);
                        }
                        $vendor_commission_percentage = 0;
                        $vendor_commission_percentage = get_user_meta($vendor->id, '_vendor_commission_percentage', true);
                        $vendor_commission_fixed_with_percentage = 0;
                        $vendor_commission_fixed_with_percentage = get_user_meta($vendor->id, '_vendor_commission_fixed_with_percentage', true);
                        if ($vendor_commission_percentage > 0) {
                            return array('commission_val' => $vendor_commission_percentage, 'commission_fixed' => $vendor_commission_fixed_with_percentage); // Use vendor user commission percentage 
                        } else {
                            if (isset($WCMp->vendor_caps->payment_cap['default_percentage'])) {
                                return array('commission_val' => $WCMp->vendor_caps->payment_cap['default_percentage'], 'commission_fixed' => $WCMp->vendor_caps->payment_cap['fixed_with_percentage']);
                            } else
                                return false;
                        }
                    }
                } else if ($WCMp->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {

                    if ($variation_id > 0) {
                        $data['commission_val'] = get_post_meta($variation_id, '_product_vendors_commission_percentage', true);
                        $data['commission_fixed'] = get_post_meta($variation_id, '_product_vendors_commission_fixed_per_qty', true);
                        if (!$data) {
                            $data['commission_val'] = get_post_meta($product_id, '_commission_percentage_per_product', true);
                            $data['commission_fixed'] = get_post_meta($product_id, '_commission_fixed_with_percentage_qty', true);
                        }
                    } else {
                        $data['commission_val'] = get_post_meta($product_id, '_commission_percentage_per_product', true);
                        $data['commission_fixed'] = get_post_meta($product_id, '_commission_fixed_with_percentage_qty', true);
                    }
                    if (!empty($data['commission_val'])) {
                        return $data; // Use product commission percentage first
                    } else {
                        if ($plan_wise_commission > 0) {
                            return array('commission_val' => $plan_wise_commission, 'commission_fixed' => $WCMp->vendor_caps->payment_cap['fixed_with_percentage_qty']);
                        }
                        $vendor_commission_percentage = 0;
                        $vendor_commission_fixed_with_percentage = 0;
                        $vendor_commission_percentage = get_user_meta($vendor->id, '_vendor_commission_percentage', true);
                        $vendor_commission_fixed_with_percentage = get_user_meta($vendor->id, '_vendor_commission_fixed_with_percentage_qty', true);
                        if ($vendor_commission_percentage > 0) {
                            return array('commission_val' => $vendor_commission_percentage, 'commission_fixed' => $vendor_commission_fixed_with_percentage); // Use vendor user commission percentage 
                        } else {
                            if (isset($WCMp->vendor_caps->payment_cap['default_percentage'])) {
                                return array('commission_val' => $WCMp->vendor_caps->payment_cap['default_percentage'], 'commission_fixed' => $WCMp->vendor_caps->payment_cap['fixed_with_percentage_qty']);
                            } else
                                return false;
                        }
                    }
                } else {
                    if ($variation_id > 0) {
                        $data['commission_val'] = get_post_meta($variation_id, '_product_vendors_commission', true);
                        if (!$data) {
                            $data['commission_val'] = get_post_meta($product_id, '_commission_per_product', true);
                        }
                    } else {
                        $data['commission_val'] = get_post_meta($product_id, '_commission_per_product', true);
                    }
                    if (!empty($data['commission_val'])) {
                        return $data; // Use product commission percentage first
                    } else {
                        if ($plan_wise_commission > 0) {
                            return array('commission_val' => $plan_wise_commission);
                        }
                        $vendor_commission = get_user_meta($vendor->id, '_vendor_commission', true);
                        if ($vendor_commission) {
                            return array('commission_val' => $vendor_commission); // Use vendor user commission percentage 
                        } else {
                            return isset($WCMp->vendor_caps->payment_cap['default_commission']) ? array('commission_val' => $WCMp->vendor_caps->payment_cap['default_commission']) : false; // Use default commission
                        }
                    }
                }
            } else {
                return $commission;
            }
        }
        return $commission;
    }

    /**
     * Add vendor user  capabilities associate with role
     *
     * @access public
     * @param user_id, new role
     * @return void
     */
    public function add_user_role_capabilities($user_id, $new_role) {
        global $WCMp, $WCMP_Vendor_Membership;
        $user = new WP_User($user_id);
        $capabilities_arr = array();
        $plan_id = get_user_meta($user_id, 'vendor_group_id', true);
        $plan_status = get_user_meta($user_id, 'wcmp_vendor_plan_status', true);
        if (!empty($plan_id)) {
            if ($plan_status == 'active') {
                $plan_meta = get_post_meta($plan_id);
                $_vendor_capabilities_field_gen = unserialize($plan_meta['_vendor_capabilities_field_gen'][0]);
                if (!empty($_vendor_capabilities_field_gen) && is_array($_vendor_capabilities_field_gen)) {
                    $capabilities_arr = array_merge($capabilities_arr, $_vendor_capabilities_field_gen);
                }

                if ($user_id && $new_role == 'dc_vendor') {
                    $caps = $this->get_vendor_caps();
                    foreach ($caps as $cap) {
                        $user->remove_cap($cap);
                    }
                    if (!empty($capabilities_arr) && is_array($capabilities_arr)) {
                        if (in_array('is_submit_product', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_submit_product')) {
                            update_user_meta($user_id, '_vendor_submit_product', 'Enable');
                            $caps = array();
                            $caps[] = "edit_product";
                            $caps[] = "delete_product";
                            $caps[] = "edit_products";
                            $caps[] = "delete_products";
                            if (in_array('is_published_product', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_published_product')) {
                                $caps[] = "publish_products";
                                if (in_array('is_edit_delete_published_product', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_edit_delete_published_product')) {
                                    $caps[] = "edit_published_products";
                                    $caps[] = "delete_published_products";
                                }
                            }
                            $capabilities_arr = array_merge($capabilities_arr, $caps);
                        } else {
                            delete_user_meta($user_id, '_vendor_submit_product');
                        }

                        if (in_array('is_submit_coupon', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_submit_coupon')) {
                            update_user_meta($user_id, '_vendor_submit_coupon', 'Enable');
                            $caps = array();
                            $caps[] = 'edit_shop_coupons';
                            $caps[] = 'read_shop_coupons';
                            $caps[] = 'delete_shop_coupons';

                            if (in_array('is_published_coupon', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_published_coupon')) {
                                $caps[] = "publish_shop_coupons";
                                if (in_array('is_edit_delete_published_coupon', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_edit_delete_published_coupon')) {
                                    $caps[] = 'edit_published_shop_coupons';
                                    $caps[] = 'delete_published_shop_coupons';
                                }
                            }
                            $capabilities_arr = array_merge($capabilities_arr, $caps);
                        } else {
                            delete_user_meta($user_id, '_vendor_submit_coupon');
                        }

                        if (in_array('is_upload_files', $capabilities_arr) && $WCMp->vendor_caps->vendor_capabilities_settings('is_upload_files')) {
                            $caps = array();
                            $caps[] = "upload_files";
                            $capabilities_arr = array_merge($capabilities_arr, $caps);
                        }
                        $capabilities_arr[] = "read_product";
                        $capabilities_arr[] = "read_shop_coupon";
                        $capabilities_arr[] = "assign_product_terms";
                        foreach ($capabilities_arr as $capability) {
                            if (!$user->has_cap($capability)) {
                                $user->add_cap($capability);
                            }
                        }
                    }
                }

                do_action('wcmp_membership_add_user_role', $user_id, $new_role, $plan_id);
            }
        }
    }

    /*
     * new WCMp vendor membership email template
     */

    public function wcmp_vendor_membership_email_classes($emails) {
        include( 'emails/class-wcmp-vendor-membership-email-new-subscription.php' );

        $emails['WCMP_Vendor_Membership_Email_New_Subscription'] = new WCMP_Vendor_Membership_Email_New_Subscription();

        return $emails;
    }

    /**
     * Set up array of vendor capabilities
     *
     * @access private
     * @param int $user_id
     * @return arr Vendor capabilities
     */
    private function get_vendor_caps() {
        $caps = array(
            'is_submit_product',
            'is_published_product',
            'is_order_csv_export',
            'is_order_show_email',
            'show_customer_dtl',
            'show_customer_billing',
            'show_customer_shipping',
            'show_cust_name',
            'show_cust_billing_add',
            'show_cust_shipping_add',
            'show_cust_order_calulations',
            'is_upload_files',
            'is_submit_coupon',
            'is_published_coupon',
            'is_vendor_view_comment',
            'is_vendor_submit_comment',
            'is_vendor_add_external_url',
            'is_hide_option_show',
            'can_vendor_add_message_on_email_and_thankyou_page',
            'can_vendor_add_customer_support_details',
            'can_vendor_edit_policy_tab_label',
            'can_vendor_edit_cancellation_policy',
            'can_vendor_edit_refund_policy',
            'can_vendor_edit_shipping_policy',
            'view_order',
            'manage_shipping'
        );
        $caps[] = "assign_product_terms";
        $caps[] = "upload_files";
        $caps[] = "edit_product";
        $caps[] = "delete_product";
        $caps[] = "edit_products";
        $caps[] = "delete_published_products";
        $caps[] = "delete_products";
        $caps[] = "edit_published_products";
        $caps[] = "publish_products";
        $caps[] = "read_product";
        $caps[] = "read_shop_coupon";
        $caps[] = "publish_shop_coupons";
        $caps[] = "edit_shop_coupon";
        $caps[] = 'edit_shop_coupons';
        $caps[] = 'read_shop_coupons';
        $caps[] = "delete_shop_coupon";
        $caps[] = 'delete_shop_coupons';
        $caps[] = 'edit_published_shop_coupons';
        $caps[] = 'delete_published_shop_coupons';
        return apply_filters('vendor_catagorization_capabilities', $caps);
    }

    /**
     * Product Category Limitation
     * @param array $data
     * @return $data
     */
    function filter_post_data($data, $postarr) {
        global $wpdb;
        if (is_user_wcmp_vendor(get_current_user_id()) && empty($postarr['ID'])) {
            $current_user = wp_get_current_user();
            $vendor_limitation_field = get_user_meta($current_user->ID, '_vendor_limitation_field', true);
            $cat_count = 0;
            if (isset($vendor_limitation_field['is_product_category_limitation']) && $vendor_limitation_field['is_product_category_limitation'] == 'Enable') {
                $num_cat = isset($postarr['tax_input']['product_cat']) ? $postarr['tax_input']['product_cat'] : array();
                foreach ($num_cat as $key => $n_c) {
                    if ($n_c == '' || $n_c == '0') {
                        unset($num_cat[$key]);
                    }
                }
                $count = $vendor_limitation_field['_product_category_limit'];
                $cat_count += (int) count($num_cat);
                if (count($num_cat) > $count) {
                    $error = "Category Limit Exceed.";
                    set_transient(get_current_user_id() . 'missingfield', $error);
                    $data['post_status'] = 'trash';
                    wp_die(__('Category Limit Exceed. Please upgrade your Membership.'));
                } else {
                    $args = array(
                        'posts_per_page' => -1,
                        'offset' => 0,
                        'category' => '',
                        'category_name' => '',
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'include' => '',
                        'exclude' => '',
                        'meta_key' => '',
                        'meta_value' => '',
                        'post_type' => 'product',
                        'post_mime_type' => '',
                        'post_parent' => '',
                        'author' => get_current_user_id(),
                        'author_name' => '',
                        'post_status' => 'publish, pending, private',
                        'suppress_filters' => true
                    );
                    $posts_array = get_posts($args);
                    $post_id = array();
                    if (!empty($posts_array) && is_array($posts_array)) {
                        foreach ($posts_array as $post) {
                            $term_list = wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'ids'));
                            $cat_count += (int) count($term_list);
                        }
                    }
                    if ($cat_count > $count) {
                        $data['post_status'] = 'trash';
                        wp_die(__('Category Limit Exceed. Please upgrade your Membership.'));
                    }
                }
            }

            if (isset($vendor_limitation_field['is_product_limitation']) && $vendor_limitation_field['is_product_limitation'] == 'Enable') {
                $count = $vendor_limitation_field['_product_limit'];
                $args = array(
                    'posts_per_page' => -1,
                    'offset' => 0,
                    'category' => '',
                    'category_name' => '',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'include' => '',
                    'exclude' => '',
                    'meta_key' => '',
                    'meta_value' => '',
                    'post_type' => 'product',
                    'post_mime_type' => '',
                    'post_parent' => '',
                    'author' => get_current_user_id(),
                    'author_name' => '',
                    'post_status' => 'publish, pending, private',
                    'suppress_filters' => true
                );
                $posts_array = get_posts($args);
                $productcount = count($posts_array);
                if ($productcount >= $count) {
                    $data['post_status'] = 'trash';
                    $error = "Product Limit Exceed.";
                    set_transient(get_current_user_id() . 'missingfield', $error);
                    wp_die(__('Product Limit Exceed. Please upgrade your Membership.'));
                }
            }
        }
        return $data;
    }

    /**
     * get memberships for wp tist table
     * @param array $membership_query
     * @return array
     */
    public function get_memberships($membership_query = array()) {
        $args = array(
            'blog_id' => $GLOBALS['blog_id'],
            'role' => '',
            'role__in' => array('dc_vendor', 'dc_pending_vendor', 'dc_rejected_vendor'),
            'role__not_in' => array(),
            'meta_key' => 'wcmp_membership_user',
            'meta_value' => '1',
            'meta_compare' => '=',
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
        $args = array_merge($args, $membership_query);
        $users = get_users($args);
        $membership_users = array();
        foreach ($users as $user) {
            $user_id = $user->ID;
            $membership_users[] = array(
                'user_id' => $user_id,
                'role' => $user->roles,
                'status' => get_user_meta($user_id, 'wcmp_vendor_plan_status', true),
                'title' => get_the_title(get_user_meta($user_id, 'vendor_group_id', true)),
                'user' => $user->data->display_name,
                'start_date' => get_user_meta($user_id, 'vendor_plan_start_date_time', true),
                'trial_expiry_date' => get_user_meta($user_id, '_trial_amt_cycle_limit', true) . ' ' . get_user_meta($user_id, '_trial_amt_cycle', true),
                'vendor_billing_amt' => get_user_meta($user_id, '_vendor_billing_amt', true) ? get_woocommerce_currency_symbol() . get_user_meta($user_id, '_vendor_billing_amt', true) : get_woocommerce_currency_symbol() . '0.00', // get_woocommerce_currency_symbol() . get_user_meta($user_id, '_vendor_billing_amt', true),
                'billing_period' => get_user_meta($user_id, 'wcmp_vendor_subscription_type', true) ? get_user_meta($user_id, '_vendor_billing_amt_cycle', true) : '',
                'next_payment_date' => get_user_meta($user_id, '_next_payment_date', true) && get_user_meta($user_id, 'wcmp_vendor_subscription_type', true) ? date('Y-m-d', strtotime(get_user_meta($user_id, '_next_payment_date', true))) : '',
            );
        }
        return $membership_users;
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present
     *
     * @access public
     * @return void
     */
    public function load_plugin_textdomain() {
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, 'wcmp-vendor_membership');
        load_textdomain('wcmp-vendor_membership', WP_LANG_DIR . '/wcmp-vendor_membership/wcmp_vendor_membership-' . $locale . '.mo');
        load_plugin_textdomain('wcmp-vendor_membership', false, plugin_basename(dirname(dirname(__FILE__))) . '/languages');
    }

    public function load_class($class_name = '') {
        if ('' != $class_name && '' != $this->token) {
            require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
        } // End If Statement
    }

    /**
     * get global settings.
     *
     * @access public
     * @return array
     */
    public function get_global_settings() {
        $message_settings = (array) get_option('wcmp_membership_settings_name', array());
        $payment_settings = (array) get_option('wcmp_membership_payment_settings_name', array());
        $global_settings = array_merge($message_settings, $payment_settings);
        return $global_settings;
    }

// End load_class()

    /**
     * Install upon activation.
     *
     * @access public
     * @return void
     */
    static function activate_wcmp_vendor_membership() {
        global $WCMP_Vendor_Membership;
        // License Activation
        $WCMP_Vendor_Membership->load_class('license');
        WCMP_Vendor_Membership_LICENSE()->activation();

        $WCMP_Vendor_Membership->load_class('install');
        new WCMp_Vendor_Membership_Install();

        update_option('wcmp_vendor_membership_installed', 1);
    }

    /**
     * UnInstall upon deactivation.
     *
     * @access public
     * @return void
     */
    static function deactivate_wcmp_vendor_membership() {
        global $WCMP_Vendor_Membership;
        delete_option('wcmp_vendor_membership_installed');
        delete_option('wcmp_vendor_membership_plugin_page_install');

        // License Deactivation
        $WCMP_Vendor_Membership->load_class('license');
        WCMP_Vendor_Membership_LICENSE()->uninstall();
    }

    /** Cache Helpers ******************************************************** */

    /**
     * Sets a constant preventing some caching plugins from caching a page. Used on dynamic pages
     *
     * @access public
     * @return void
     */
    public function nocache() {
        if (!defined('DONOTCACHEPAGE'))
            define("DONOTCACHEPAGE", "true");
        // WP Super Cache constant
    }

}
