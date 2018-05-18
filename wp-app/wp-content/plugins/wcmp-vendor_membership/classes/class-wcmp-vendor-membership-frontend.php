<?php

class WCMP_Vendor_Membership_Frontend {

    public function __construct() {
        global $WCMp;
        //enqueue scripts
        add_action('wp_enqueue_scripts', array(&$this, 'frontend_scripts'));
        //enqueue styles
        add_action('wp_enqueue_scripts', array(&$this, 'frontend_styles'));

        $this->WCMP_Vendor_Membership_frontend_function();
        if (isset($_POST['vendor_plan_id'])) {
            remove_action('template_redirect', array($WCMp->frontend, 'template_redirect'));
        }
        add_action('woocommerce_created_customer', array(&$this, 'save_filds_and_make_payment'), 10, 3);
        add_action('wcmp_vendor_dashboard_header', array(&$this, 'wcmp_vendor_dashboard_header'));
        add_action('wcmp_vendor_dashboard_membership-details_endpoint', array(&$this, 'wcmp_vendor_dashboard_membership_details_endpoint'));
        add_filter('is_visible_wcmp_frontend_product_cat', array(&$this, 'is_visible_wcmp_frontend_product_cat'), 10, 3);
    }

    function frontend_scripts() {
        global $WCMP_Vendor_Membership;
        $frontend_script_path = $WCMP_Vendor_Membership->plugin_url . 'assets/frontend/js/';
        $frontend_script_path = str_replace(array('http:', 'https:'), '', $frontend_script_path);
        $pluginURL = str_replace(array('http:', 'https:'), '', $WCMP_Vendor_Membership->plugin_url);
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script('vendor_plan_js', $frontend_script_path . 'frontend.js', array('jquery'), $WCMP_Vendor_Membership->version, true);
        //if(is_vendor_categorization_plan_payment_page()){
        wp_enqueue_script('vendor_plan_payment_js', $frontend_script_path . 'vendor-plan-payment.js', array('jquery'), $WCMP_Vendor_Membership->version, true);
        $vendor_plan_param = array(
            'ajax_url' => admin_url('admin-ajax.php')
        );
        wp_localize_script('vendor_plan_payment_js', 'vendor_plan_param', $vendor_plan_param);
    }

    function frontend_styles() {
        global $post, $WCMP_Vendor_Membership;
        $frontend_style_path = $WCMP_Vendor_Membership->plugin_url . 'assets/frontend/css/';
        $frontend_style_path = str_replace(array('http:', 'https:'), '', $frontend_style_path);
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style('vendor_plan_css', $frontend_style_path . 'frontend.css', array(), $WCMP_Vendor_Membership->version);
        if (is_vendor_membership_plan_payment_page()) {
            wp_enqueue_style('vendor_plan_payment_css', $frontend_style_path . 'vendor-plan-payment.css', array(), $WCMP_Vendor_Membership->version);
        }
        if ($post && $post->post_type == 'vendortype') {
            wp_enqueue_style('vendor_single_plan__css', $frontend_style_path . 'vendor-single-plan.css', array(), $WCMP_Vendor_Membership->version);
        }
    }

    public function wcmp_vendor_dashboard_header() {
        global $WCMp;
        if ($WCMp->endpoints->get_current_endpoint() == 'membership-details') {
            echo '<ul>';
            echo '<li>' . __('Store Settings ', $WCMp->text_domain) . '</li>';
            echo '<li class="next"> < </li>';
            echo '<li>' . __('Membership', $WCMp->text_domain) . '</li>';
            echo '</ul>';
        }
    }

    public function wcmp_vendor_dashboard_membership_details_endpoint() {
        global $WCMP_Vendor_Membership;
        $current_user = wp_get_current_user();
        if (isset($current_user->ID) && $current_user->ID != 0) {
            $plan_id = get_user_meta($current_user->ID, 'vendor_group_id', true);
            if (!empty($plan_id) && $plan_id != 0) {
                $plan = get_post($plan_id);
                $WCMP_Vendor_Membership->template->get_template('myaccount/vendor-subscription.php', array('plan' => $plan, 'user_id' => get_current_user_id()));
            }
        }
    }

    function save_filds_and_make_payment($customer_id, $new_customer_data, $password_generated) {
        global $WCMp, $WCMP_Vendor_Membership;
        $wcmp_vendor_fields = NULL;
        if (isset($_POST['wcmp_vendor_fields']) && isset($_POST['create_vendor_membership_payment'])) {

            if (isset($_FILES['wcmp_vendor_fields'])) {
                $attacment_files = $_FILES['wcmp_vendor_fields'];
                $files = array();
                $count = 0;
                if (!empty($attacment_files) && is_array($attacment_files)) {
                    foreach ($attacment_files['name'] as $key => $attacment) {
                        foreach ($attacment as $key_attacment => $value_attacment) {
                            $files[$count]['name'] = $value_attacment;
                            $files[$count]['type'] = $attacment_files['type'][$key][$key_attacment];
                            $files[$count]['tmp_name'] = $attacment_files['tmp_name'][$key][$key_attacment];
                            $files[$count]['error'] = $attacment_files['error'][$key][$key_attacment];
                            $files[$count]['size'] = $attacment_files['size'][$key][$key_attacment];
                            $files[$count]['field_key'] = $key;
                            $count++;
                        }
                    }
                }
                $upload_dir = wp_upload_dir();
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                if (!function_exists('wp_handle_upload')) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }
                foreach ($files as $file) {
                    $uploadedfile = $file;
                    $upload_overrides = array('test_form' => false);
                    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
                    if ($movefile && !isset($movefile['error'])) {
                        $filename = $movefile['file'];
                        $filetype = wp_check_filetype($filename, null);
                        $attachment = array(
                            'post_mime_type' => $filetype['type'],
                            'post_title' => $file['name'],
                            'post_content' => '',
                            'post_status' => 'inherit',
                            'guid' => $movefile['url']
                        );
                        $attach_id = wp_insert_attachment($attachment, $movefile['file']);
                        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        $_POST['wcmp_vendor_fields'][$file['field_key']]['value'][] = $attach_id;
                    }
                }
            }
            $wcmp_vendor_fields = $_POST['wcmp_vendor_fields'];
        }
        if (isset($_POST['create_vendor_membership_payment']) && isset($_POST['vendor_membership_plan_id'])) {

            $plan_id = $_POST['vendor_membership_plan_id'];
            $user_data = get_userdata($customer_id);
            $user_name = $user_data->user_login;
            $user_email = $user_data->user_email;
            // Create post object
            $my_post = array(
                'post_title' => $user_name,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'wcmp_vendorrequest'
            );

            // Insert the post into the database
            $register_vendor_post_id = wp_insert_post($my_post);
            update_post_meta($register_vendor_post_id, 'user_id', $customer_id);
            update_post_meta($register_vendor_post_id, 'username', $user_name);
            update_post_meta($register_vendor_post_id, 'email', $user_email);
            update_post_meta($register_vendor_post_id, 'wcmp_vendor_fields', $wcmp_vendor_fields);
            update_user_meta($customer_id, 'wcmp_vendor_registration_form_id', $register_vendor_post_id);
            if (isset($_POST['wvm_payment_method']) && !empty($_POST['wvm_payment_method'])) {
                $wvm_payment_method = $_POST['wvm_payment_method'];
            }
            if (get_post_meta($plan_id, '_is_free_plan', true) == 'Enable') {
                $wvm_payment_method = 'free';
            }
            $WCMP_Vendor_Membership->load_class('get-payments');
            $WCMp_Vendor_Membership_Get_Payments = new WCMp_Vendor_Membership_Get_Payments($customer_id, $plan_id, $wvm_payment_method, $new_customer_data['user_pass']);
            $status = $WCMp_Vendor_Membership_Get_Payments->create_payment();
            if ($status) {
                
            } else {
                if (function_exists('wcmp_vendor_registration_page_id')) {
                    wp_safe_redirect(get_permalink(wcmp_vendor_registration_page_id()) . '?haserror=1');
                    exit();
                }
            }
        }
    }

    function WCMP_Vendor_Membership_frontend_function() {
        $current_user = wp_get_current_user();
        $user = new WP_User($current_user->ID);
        $available_caps = array();
        //$available_caps = $user->get_role_caps();        
        $available_caps = get_user_meta($current_user->ID, 'wp_capabilities', true);

        $plan_id = get_user_meta($current_user->ID, 'vendor_group_id', true);
        if (empty($plan_id)) {
            return;
        }
        if (isset($available_caps['view_order'])) {
            add_filter('wcmp_vendor_dashboard_menu_vendor_orders_capability', '__return_true');
        } else {
            add_filter('wcmp_vendor_dashboard_menu_vendor_orders_capability', '__return_false');
        }

        if ($user->has_cap('manage_shipping')) {
            add_filter('wcmp_vendor_dashboard_menu_vendor_shipping_capability', '__return_true');
        } else {
            add_filter('wcmp_vendor_dashboard_menu_vendor_shipping_capability', '__return_false');
        }

        if ($user->has_cap('is_order_csv_export')) {
            add_filter('is_order_csv_export_button', '__return_true');
        } else {
            add_filter('is_order_csv_export_button', '__return_false');
        }
        if ($user->has_cap('is_show_email')) {
            add_filter('is_not_show_email_field', '__return_false');
        } else {
            add_filter('is_not_show_email_field', '__return_true');
        }
        if ($user->has_cap('show_customer_dtl')) {
            add_filter('is_not_show_customer_dtl_field', '__return_false');
        } else {
            add_filter('is_not_show_customer_dtl_field', '__return_true');
        }
        if ($user->has_cap('show_customer_billing')) {
            add_filter('is_not_show_customer_billing_field', '__return_false');
        } else {
            add_filter('is_not_show_customer_billing_field', '__return_true');
        }
        if ($user->has_cap('show_customer_shipping')) {
            add_filter('is_not_show_customer_shipping_field', '__return_false');
        } else {
            add_filter('is_not_show_customer_shipping_field', '__return_true');
        }
        /*         * **************************** Order Email Settings for Vendor ********************************** */
        if ($user->has_cap('show_cust_order_calulations')) {
            add_filter('show_cust_order_calulations_field', '__return_true');
        } else {
            add_filter('show_cust_order_calulations_field', '__return_false');
        }
        if ($user->has_cap('show_cust_add')) {
            add_filter('show_cust_add_field', '__return_true');
        } else {
            add_filter('show_cust_add_field', '__return_false');
        }
        if ($user->has_cap('show_cust_billing_add')) {
            add_filter('show_cust_billing_add_field', '__return_true');
        } else {
            add_filter('show_cust_billing_add_field', '__return_false');
        }
        if ($user->has_cap('show_cust_shipping_add')) {
            add_filter('show_cust_shipping_add_field', '__return_true');
        } else {
            add_filter('show_cust_shipping_add_field', '__return_false');
        }

        /*         * *******************       Miscellaneous           ************************* */
        if ($user->has_cap('is_vendor_view_comment')) {
            add_filter('is_vendor_view_comment_field', '__return_true');
        } else {
            add_filter('is_vendor_view_comment_field', '__return_false');
        }
        if ($user->has_cap('is_vendor_submit_comment')) {
            add_filter('is_vendor_submit_comment_field', '__return_true');
        } else {
            add_filter('is_vendor_submit_comment_field', '__return_false');
        }
        if ($user->has_cap('is_vendor_add_external_url')) {
            add_filter('is_vendor_add_external_url_field', '__return_true');
        } else {
            add_filter('is_vendor_add_external_url_field', '__return_false');
        }
        if ($user->has_cap('is_hide_option_show')) {
            add_filter('is_hide_option_show_enable', '__return_true');
        } else {
            add_filter('is_hide_option_show_enable', '__return_false');
        }

        /*         * *******************   Messages  & Support *********************************** */
        if ($user->has_cap('can_vendor_add_message_on_email_and_thankyou_page')) {
            add_filter('can_vendor_add_message_on_email_and_thankyou_page', '__return_true');
        } else {
            add_filter('can_vendor_add_message_on_email_and_thankyou_page', '__return_false');
        }
        if ($user->has_cap('is_customer_support_details')) {
            add_filter('is_customer_support_details', '__return_true');
        } else {
            add_filter('is_customer_support_details', '__return_false');
        }

        /*         * ****************************    Policies Settings    ****************************** */
        if ($user->has_cap('can_vendor_edit_policy_tab_label')) {
            add_filter('can_vendor_edit_policy_tab_label_field', '__return_true');
        } else {
            add_filter('can_vendor_edit_policy_tab_label_field', '__return_false');
        }
        if ($user->has_cap('can_vendor_edit_cancellation_policy')) {
            add_filter('can_vendor_edit_cancellation_policy_field', '__return_true');
        } else {
            add_filter('can_vendor_edit_cancellation_policy_field', '__return_false');
        }
        if ($user->has_cap('can_vendor_edit_refund_policy')) {
            add_filter('can_vendor_edit_refund_policy_field', '__return_true');
        } else {
            add_filter('can_vendor_edit_refund_policy_field', '__return_false');
        }
        if ($user->has_cap('can_vendor_edit_shipping_policy')) {
            add_filter('can_vendor_edit_shipping_policy_field', '__return_true');
        } else {
            add_filter('can_vendor_edit_shipping_policy_field', '__return_false');
        }
    }
    /**
     * Filter category list in WCMp frontend product manager
     * @param boolean $visible
     * @param int $term_id
     * @param string $taxonomy
     * @return boolean
     */
    public function is_visible_wcmp_frontend_product_cat($visible, $term_id, $taxonomy) {
        if ($taxonomy == 'product_cat') {
            $plan_id = get_user_meta(get_current_user_id(), 'vendor_group_id', true);
            if ($plan_id) {
                $allowed_product_cats = get_post_meta($plan_id, '_allowed_product_cats', true);
                if ($allowed_product_cats && is_array($allowed_product_cats)) {
                    if (!in_array($term_id, $allowed_product_cats)) {
                        $visible = false;
                    }
                }
            }
        }
        return $visible;
    }

}
