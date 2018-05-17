<?php

class WCMP_Vendor_General_Settings_Gneral {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $tab;

    /**
     * Start up
     */
    public function __construct($tab) {
        $this->tab = $tab;
        $this->options = get_option("wcmp_{$this->tab}_settings_name");
        $this->settings_page_init();
    }

    /**
     * Register and add settings
     */
    public function settings_page_init() {
        global $WCMp, $WCMP_Vendor_Membership;

        $settings_tab_options = array("tab" => "{$this->tab}",
            "ref" => &$this,
            "sections" => array(
                "message_settings_section" => array("title" => __("Global Message Section", 'wcmp-vendor_membership'), // Message section
                    "fields" => array(
                        "_success_msg" => array('title' => __('Success Message', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_success_msg', 'label_for' => '_success_msg', 'name' => '_success_msg', 'hints' => __('It will comes when a vendor registered successfully.', 'wcmp-vendor_membership'), 'desc' => __('Enter success message when vendor registered successfully.', 'wcmp-vendor_membership')), // Text
                        "_failure_msg" => array('title' => __('Failure  Message', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_failuare_msg', 'label_for' => '_failuare_msg', 'name' => '_failuare_msg', 'hints' => __('It will comes when a vendor registration failed .', 'wcmp-vendor_membership'), 'desc' => __('Enter success message when vendor registration failed.', 'wcmp-vendor_membership')), // Text
                        "_payment_due_msg" => array('title' => __('Payment Due Message', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_payment_due_msg', 'label_for' => '_payment_due_msg', 'name' => '_payment_due_msg', 'hints' => __('It will reminder when payment was due.', 'wcmp-vendor_membership'), 'desc' => __('Enter payment due message.', 'wcmp-vendor_membership')), // Text
                        "_upcoming_renew_reminder_msg" => array('title' => __('Upcoming Renew Reminder Message', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_upcoming_renew_reminder_msg', 'label_for' => '_upcoming_renew_reminder_msg', 'name' => '_upcoming_renew_reminder_msg', 'hints' => __('Enter upcoming renew reminder message.', 'wcmp-vendor_membership'), 'desc' => __('It will reminder for upcoming renew .', 'wcmp-vendor_membership')), // Text
                        "_after_grace_pariod_msg" => array('title' => __('Reminder Message After Grace Period', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_after_grace_pariod_msg', 'label_for' => '_after_grace_pariod_msg', 'name' => '_after_grace_pariod_msg', 'hints' => __('It will reminder for Subscription expiration.', 'wcmp-vendor_membership'), 'desc' => __('Enter reminder message when grace period expired.', 'wcmp-vendor_membership')) // Text
                    )
                ),
                "notification_settings_section" => array("title" => __("Notification Settings Section", 'wcmp-vendor_membership'), // Notification section
                    "fields" => array("_payment_due_reminder_duration" => array('title' => __('Payment Due Reminder duration', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_payment_due_reminder_duration', 'label_for' => '_payment_due_reminder_duration', 'name' => '_payment_due_reminder_duration', 'hints' => __('Enter number of days when system reminds for payment due only number.', 'wcmp-vendor_membership'), 'desc' => __('Enter number of days when system reminds for payment due only number.', 'wcmp-vendor_membership')), // Text
                    )
                )
            )
        );

        $WCMp->admin->settings->settings_field_init(apply_filters("settings_{$this->tab}_tab_options", $settings_tab_options));
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function wcmp_membership_settings_sanitize($input) {
        global $WCMP_Vendor_Membership;
        $new_input = array();
        $hasError = false;
        if (isset($input['_success_msg'])) {
            $new_input['_success_msg'] = sanitize_text_field($input['_success_msg']);
        }
        if (isset($input['_failuare_msg'])) {
            $new_input['_failuare_msg'] = sanitize_text_field($input['_failuare_msg']);
        }
        if (isset($input['_payment_due_msg'])) {
            $new_input['_payment_due_msg'] = sanitize_text_field($input['_payment_due_msg']);
        }
        if (isset($input['_upcoming_renew_reminder_msg'])) {
            $new_input['_upcoming_renew_reminder_msg'] = sanitize_text_field($input['_upcoming_renew_reminder_msg']);
        }
        if (isset($input['_after_grace_pariod_msg'])) {
            $new_input['_after_grace_pariod_msg'] = sanitize_text_field($input['_after_grace_pariod_msg']);
        }
        if (isset($input['display_method'])) {
            $new_input['display_method'] = sanitize_text_field($input['display_method']);
        }				

        if (!$hasError) {
            add_settings_error(
                    "wcmp_{$this->tab}_settings_name", esc_attr("wcmp_{$this->tab}_settings_admin_updated"), __('Membership General settings updated', 'wcmp-vendor_membership'), 'updated'
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
