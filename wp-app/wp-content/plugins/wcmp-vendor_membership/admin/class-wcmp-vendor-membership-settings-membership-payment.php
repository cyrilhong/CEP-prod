<?php

class WCMP_Vendor_Membership_Settings_Gneral {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $tab;
    private $subsection;

    /**
     * Start up
     */
    public function __construct($tab, $subsection) {
        $this->tab = $tab;
        $this->subsection = $subsection;
        $this->options = get_option("wcmp_{$this->tab}_{$this->subsection}_settings_name");
        $this->settings_page_init();
    }

    /**
     * Register and add settings
     */
    public function settings_page_init() {
        global $WCMp, $WCMP_Vendor_Membership;

        $settings_tab_options = array("tab" => "{$this->tab}",
            "ref" => &$this,
            "subsection" => "{$this->subsection}",
            "sections" => array(
                "default_settings_section" => array("title" => __('', 'wcmp-vendor_membership'), // Default Settings section
                    "fields" => array(
                        "_paypal_email" => array('title' => __('Paypal Email', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_paypal_email', 'label_for' => '_paypal_email', 'name' => '_paypal_email', 'hints' => __('Enter paypal email which is use to take the payment.', 'wcmp-vendor_membership'), 'desc' => __('Your business paypal email which will take the registration fee .', 'wcmp-vendor_membership')), // Text
                        "sandbox_enable" => array('title' => __('Sandbox Enable', 'wcmp-vendor_membership'), 'type' => 'checkbox', 'id' => 'sandbox_enable', 'label_for' => 'sandbox_enable', 'name' => 'sandbox_enable', 'value' => 'Enable'), // Text
                        "appplication_id" => array('title' => __('Paypal Application ID', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'appplication_id', 'label_for' => 'appplication_id', 'name' => 'appplication_id', 'hints' => __('For Sandbox use APP-80W284485P519543T as a application id.', 'wcmp-vendor_membership'), 'desc' => __('Enter Paypal Application Id Use APP-80W284485P519543T when sandbox mode is on.', 'wcmp-vendor_membership')), // Text
                        "display_method" => array('title' => __('Price Display in list Page', 'wcmp-vendor_membership'), 'type' => 'select', 'id' => 'display_method', 'label_for' => 'display_method', 'options' => array('' => __('Please Select Display Type', 'wcmp-vendor_membership'), 'inclusive' => __('Inclusive Taxes', 'wcmp-vendor_membership'), 'exclusive' => __('Exclusive Taxes', 'wcmp-vendor_membership')), 'name' => 'display_method', 'hints' => __('Choose which price do you want to show.', 'wcmp-vendor_membership'), 'desc' => __('Choose which price do you want to display in subscription list.', 'wcmp-vendor_membership')),
                    )
                ),
                "payment_settings_section_sandbox" => array("title" => __("Paypal Sandbox Details Section", 'wcmp-vendor_membership'), // Paypal section
                    "fields" => array(
                        "api_username" => array('title' => __('Sandbox API Username', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'api_username', 'label_for' => 'api_username', 'name' => 'api_username', 'hints' => __('Sandbox API Username you will get this credential from paypal.', 'wcmp-vendor_membership'), 'desc' => __('Enter Paypal Sandbox API Username.', 'wcmp-vendor_membership')), // Text
                        "api_password" => array('title' => __('Sandbox API Password', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'api_password', 'label_for' => 'api_password', 'name' => 'api_password', 'hints' => __('Sandbox API Password you will get this credential from paypal.', 'wcmp-vendor_membership'), 'desc' => __('Enter Paypal Sandbox API Password .', 'wcmp-vendor_membership')), // Text
                        "api_signature" => array('title' => __('Sandbox API Signature', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'api_signature', 'label_for' => 'api_signature', 'name' => 'api_signature', 'hints' => __('Sandbox API Signature you will get this credential from paypal.', 'wcmp-vendor_membership'), 'desc' => __('Enter Paypal Sandbox API Signature .', 'wcmp-vendor_membership')) // Text
                    )
                ),
                "payment_settings_section_live" => array("title" => __("Live Paypal Details Section", 'wcmp-vendor_membership'), // Paypal section
                    "fields" => array(
                        "api_username_live" => array('title' => __('API Username', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'api_username_live', 'label_for' => 'api_username_live', 'name' => 'api_username_live', 'hints' => __('API Username you will get this credential from paypal.', 'wcmp-vendor_membership'), 'desc' => __('Enter Paypal API Username.', 'wcmp-vendor_membership')), // Text
                        "api_password_live" => array('title' => __('API Password', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'api_password_live', 'label_for' => 'api_password_live', 'name' => 'api_password_live', 'hints' => __('API Password you will get this credential from paypal.', 'wcmp-vendor_membership'), 'desc' => __('Enter Paypal API Password .', 'wcmp-vendor_membership')), // Text
                        "api_signature_live" => array('title' => __('API Signature', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => 'api_signature_live', 'label_for' => 'api_signature_live', 'name' => 'api_signature_live', 'hints' => __('API Signature you will get this credential from paypal.', 'wcmp-vendor_membership'), 'desc' => __('Enter Paypal API Signature .', 'wcmp-vendor_membership')) // Text
                    )
                )
            )
        );

        $WCMp->admin->settings->settings_field_withsubtab_init(apply_filters("settings_{$this->tab}_{$this->subsection}_tab_options", $settings_tab_options));
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function wcmp_membership_payment_settings_sanitize($input) {
        global $WCMP_Vendor_Membership;
        $new_input = array();
        $hasError = false;
        if (isset($input['is_enable'])) {
            $new_input['is_enable'] = sanitize_text_field($input['is_enable']);
        }
//        if (isset($input['_success_msg'])) {
//            $new_input['_success_msg'] = sanitize_text_field($input['_success_msg']);
//        }
        if (isset($input['appplication_id'])) {
            $new_input['appplication_id'] = sanitize_text_field($input['appplication_id']);
        }
//        if (isset($input['_failuare_msg'])) {
//            $new_input['_failuare_msg'] = sanitize_text_field($input['_failuare_msg']);
//        }
//        if (isset($input['_payment_due_msg'])) {
//            $new_input['_payment_due_msg'] = sanitize_text_field($input['_payment_due_msg']);
//        }
//        if (isset($input['_upcoming_renew_reminder_msg'])) {
//            $new_input['_upcoming_renew_reminder_msg'] = sanitize_text_field($input['_upcoming_renew_reminder_msg']);
//        }
//        if (isset($input['_after_grace_pariod_msg'])) {
//            $new_input['_after_grace_pariod_msg'] = sanitize_text_field($input['_after_grace_pariod_msg']);
//        }
        if (isset($input['_paypal_email'])) {
            $new_input['_paypal_email'] = sanitize_text_field($input['_paypal_email']);
        }
        if (isset($input['sandbox_enable'])) {
            $new_input['sandbox_enable'] = sanitize_text_field($input['sandbox_enable']);
        }
        if (isset($input['api_username'])) {
            $new_input['api_username'] = sanitize_text_field($input['api_username']);
        }
        if (isset($input['api_password'])) {
            $new_input['api_password'] = sanitize_text_field($input['api_password']);
        }
        if (isset($input['api_signature'])) {
            $new_input['api_signature'] = sanitize_text_field($input['api_signature']);
        }
        if (isset($input['api_username_live'])) {
            $new_input['api_username_live'] = sanitize_text_field($input['api_username_live']);
        }
        if (isset($input['api_password_live'])) {
            $new_input['api_password_live'] = sanitize_text_field($input['api_password_live']);
        }
        if (isset($input['api_signature_live'])) {
            $new_input['api_signature_live'] = sanitize_text_field($input['api_signature_live']);
        }
        if (isset($input['_payment_due_reminder_duration'])) {
            $new_input['_payment_due_reminder_duration'] = absint($input['_payment_due_reminder_duration']);
        }
        if (isset($input['display_method'])) {
            $new_input['display_method'] = sanitize_text_field($input['display_method']);
        }
        if (isset($input['display_method_checkout'])) {
            $new_input['display_method_checkout'] = sanitize_text_field($input['display_method_checkout']);
        }
        if(!wp_next_scheduled( 'subscription_cron_start' ) ) {
            wp_schedule_event( time(), 'hourly', 'subscription_cron_start');
        }				

        if (!$hasError) {
            add_settings_error(
                    "wcmp_{$this->tab}_{$this->subsection}_settings_name", esc_attr("wcmp_{$this->tab}_{$this->subsection}_settings_admin_updated"), __('Membership Payment settings updated', 'wcmp-vendor_membership'), 'updated'
            );
        }
        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function default_settings_section_info() {
        global $WCMP_Vendor_Membership;
        //_e('Enter your default settings below', 'wcmp-vendor_membership');
    }

    /**
     * Print the Section text
     */
    public function message_settings_section_info() {
        global $WCMP_Vendor_Membership;
        _e('Enter global messages below', 'wcmp-vendor_membership');
    }

    /**
     * Print the Section text
     */
    public function payment_settings_section_sandbox_info() {
        global $WCMP_Vendor_Membership;
        _e('Enter paypal sandbox details below', 'wcmp-vendor_membership');
    }

    public function payment_settings_section_live_info() {
        global $WCMP_Vendor_Membership;
        _e('Enter paypal live details below', 'wcmp-vendor_membership');
    }

    /**
     * Print the Section text
     */
    public function notification_settings_section_info() {
        global $WCMP_Vendor_Membership;
        _e('Configure the notification settings below', 'wcmp-vendor_membership');
    }

}
