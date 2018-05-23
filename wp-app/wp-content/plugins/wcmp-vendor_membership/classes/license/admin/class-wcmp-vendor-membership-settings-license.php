<?php

class WCMP_Vendor_Membership_Settings_License {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $tab;
    private $api_manager_license_key;

    /**
     * Start up
     */
    public function __construct($tab) {
        global $WCMP_Vendor_Membership;

        $this->tab = $tab;
        $this->options = get_option("wcmp_{$this->tab}_settings_name");
        $this->settings_page_init();

        $this->api_manager_license_key = $WCMP_Vendor_Membership->license->api_manager_license_key;
    }

    /**
     * Register and add settings
     */
    public function settings_page_init() {
        global $WCMp,$WCMP_Vendor_Membership;
        $license_api_key = '';
        $license_activation_email = '';
        if (isset($this->options[$WCMP_Vendor_Membership->license->license_api_key])) {
            $license_api_key = $this->options[$WCMP_Vendor_Membership->license->license_api_key];
        }
        if (isset($this->options[$WCMP_Vendor_Membership->license->license_activation_email])) {
            $license_activation_email = $this->options[$WCMP_Vendor_Membership->license->license_activation_email];
        }
        if ($this->options && is_array($this->options) && $this->options[$WCMP_Vendor_Membership->license->license_api_key]) {
            $api_key_ico = "<span class='icon-pos'><img src='" . $WCMP_Vendor_Membership->plugin_url . "classes/license/assets/images/complete.png' title='' style='padding-bottom: 4px; vertical-align: middle; margin-right:3px;' /></span>";
        } else {
            $api_key_ico = "<span class='icon-pos'><img src='" . $WCMP_Vendor_Membership->plugin_url . "classes/license/assets/images/warn.png' title='' style='padding-bottom: 4px; vertical-align: middle; margin-right:3px;' /></span>";
        }

        if ($this->options && is_array($this->options) && $this->options[$WCMP_Vendor_Membership->license->license_activation_email]) {
            $api_email_ico = "<span class='icon-pos'><img src='" . $WCMP_Vendor_Membership->plugin_url . "classes/license/assets/images/complete.png' title='' style='padding-bottom: 4px; vertical-align: middle; margin-right:3px;' /></span>";
        } else {
            $api_email_ico = "<span class='icon-pos'><img src='" . $WCMP_Vendor_Membership->plugin_url . "classes/license/assets/images/warn.png' title='' style='padding-bottom: 4px; vertical-align: middle; margin-right:3px;' /></span>";
        }

        $settings_tab_options = array("tab" => "{$this->tab}",
            "ref" => &$this,
            "sections" => array(
                "activation_settings_section" => array("title" => __('License Activation', 'wcmp-vendor_membership'),
                    "fields" => array($WCMP_Vendor_Membership->license->license_api_key => array('title' => __('API License Key', 'wcmp-vendor_membership'), 'type' => 'text', 'value' => $license_api_key, 'id' => $WCMP_Vendor_Membership->license->license_api_key, 'name' => $WCMP_Vendor_Membership->license->license_api_key, 'desc' => $api_key_ico),
                        $WCMP_Vendor_Membership->license->license_activation_email => array('title' => __('API License email', 'wcmp-vendor_membership'), 'type' => 'text', 'value' => $license_activation_email, 'id' => $WCMP_Vendor_Membership->license->license_activation_email, 'label_for' => $WCMP_Vendor_Membership->license->license_activation_email, 'name' => $WCMP_Vendor_Membership->license->license_activation_email, 'desc' => $api_email_ico),
                    )
                ),
                "deactivation_settings_section" => array("title" => __('License Deactivation', 'wcmp-vendor_membership'),
                    "fields" => array($WCMP_Vendor_Membership->license->license_deactivate_checkbox => array('title' => __('Deactivate API License Key', 'wcmp-vendor_membership'), 'type' => 'checkbox', 'id' => $WCMP_Vendor_Membership->license->license_deactivate_checkbox, 'name' => $WCMP_Vendor_Membership->license->license_deactivate_checkbox, 'value' => 'on', 'desc' => __('Deactivates an API License Key so it can be used on another blog.', 'wcmp-vendor_membership'))
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
    public function wcmp_wcmp_vendor_membership_license_settings_sanitize($input) {
        global $WCMP_Vendor_Membership;

        // Load existing options, validate, and update with changes from input before returning
        $new_input = array();

        $hasError = false;

        $new_input[$WCMP_Vendor_Membership->license->license_api_key] = trim($input[$WCMP_Vendor_Membership->license->license_api_key]);
        $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = trim($input[$WCMP_Vendor_Membership->license->license_activation_email]);
        $new_input[$WCMP_Vendor_Membership->license->license_deactivate_checkbox] = ( $input[$WCMP_Vendor_Membership->license->license_deactivate_checkbox] == 'on' ? 'on' : 'off' );

        $api_email = trim($input[$WCMP_Vendor_Membership->license->license_activation_email]);
        $api_key = trim($input[$WCMP_Vendor_Membership->license->license_api_key]);

        if ($api_key == '') {
            add_settings_error(
                    "dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), __('Please insert your license key.', 'wcmp-vendor_membership'), 'error'
            );
            $hasError = true;
        }

        if ($api_email == '') {
            add_settings_error(
                    "dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), __('Please insert your license email.', 'wcmp-vendor_membership'), 'error'
            );
            $hasError = true;
        }

        if (!$hasError) {

            $activation_status = get_option($WCMP_Vendor_Membership->license->license_activated_key);
            $checkbox_status = get_option($WCMP_Vendor_Membership->license->license_deactivate_checkbox);
            $current_api_key = $this->options[$WCMP_Vendor_Membership->license->license_api_key];

            $args = array(
                'email' => $api_email,
                'licence_key' => $api_key,
            );

            if ('off' == $new_input[$WCMP_Vendor_Membership->license->license_deactivate_checkbox]) {

                // Plugin Activation
                if ($activation_status == 'Deactivated' || $activation_status == '' || $checkbox_status == 'on' || $current_api_key != $api_key) {

                    if ($current_api_key != $api_key)
                        $this->replace_license_key($current_api_key);

                    $activate_results = json_decode($this->api_manager_license_key->activate($args), true);

                    if ($activate_results['activated'] == true) {
                        add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), __('Plugin activated. ', 'wcmp-vendor_membership') . "{$activate_results['message']}.", 'updated');
                        update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Activated');
                        update_option($WCMP_Vendor_Membership->license->license_deactivate_checkbox, 'off');

                        $WCMP_Vendor_Membership->license->dc_plugin_tracker('license_activate', $api_key, $api_email);
                    }

                    if ($activate_results == false) {
                        add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), __('Connection failed to the License Key API server. Try again later.', 'wcmp-vendor_membership'), 'error');
                        $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                        $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                        update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                    }

                    if (isset($activate_results['code'])) {

                        switch ($activate_results['code']) {
                            case '100':
                                add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), "{$activate_results['error']}. {$activate_results['additional info']}", 'error');
                                $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                                $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                                update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                                break;
                            case '101':
                                add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), "{$activate_results['error']}. {$activate_results['additional info']}", 'error');
                                $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                                $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                                update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                                break;
                            case '102':
                                add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), "{$activate_results['error']}. {$activate_results['additional info']}", 'error');
                                $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                                $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                                update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                                break;
                            case '103':
                                add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), "{$activate_results['error']}. {$activate_results['additional info']}", 'error');
                                $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                                $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                                update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                                break;
                            case '104':
                                add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), "{$activate_results['error']}. {$activate_results['additional info']}", 'error');
                                $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                                $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                                update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                                break;
                            case '105':
                                add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), "{$activate_results['error']}. {$activate_results['additional info']}", 'error');
                                $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                                $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                                update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                                break;
                            case '106':
                                add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), "{$activate_results['error']}. {$activate_results['additional info']}", 'error');
                                $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                                $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                                update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                                break;
                        }
                    }
                } // End Plugin Activation
            } else {
                if ($activation_status == 'Activated') {
                    $reset = $this->api_manager_license_key->deactivate($args); // reset license key activation

                    if ($reset == true) {
                        $new_input[$WCMP_Vendor_Membership->license->license_api_key] = '';
                        $new_input[$WCMP_Vendor_Membership->license->license_activation_email] = '';
                        update_option($WCMP_Vendor_Membership->license->license_activated_key, 'Deactivated');
                        $WCMP_Vendor_Membership->license->dc_plugin_tracker('license_deactivate', $api_key, $api_email);

                        add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), __('Plugin license deactivated.', 'wcmp-vendor_membership'), 'updated');
                    }
                }
            }
        }

        unset($new_input[$WCMP_Vendor_Membership->license->license_deactivate_checkbox]);
        return $new_input;
    }

    // Deactivate the current license key before activating the new license key
    public function replace_license_key($current_api_key) {
        global $WCMP_Vendor_Membership;

        $args = array(
            'email' => $this->options[$WCMP_Vendor_Membership->license->license_activation_email],
            'licence_key' => $current_api_key,
        );

        $reset = $this->api_manager_license_key->deactivate($args); // reset license key activation

        if ($reset == true)
            return true;

        return add_settings_error("dc_{$this->tab}_settings_name", esc_attr("dc_{$this->tab}_settings_admin_error"), __('The license could not be deactivated. Use the License Deactivation tab to manually deactivate the license before activating a new license.', 'wcmp-vendor_membership'), 'updated');
    }

    /**
     * Print the Section text
     */
    public function activation_settings_section_info() {
        global $WCMP_Vendor_Membership;
        //_e('Enter your default settings below', 'wcmp-vendor_membership');
    }

    /**
     * Print the Section text
     */
    public function deactivation_settings_section_info() {
        global $WCMP_Vendor_Membership;
        //_e('Enter your custom settings below', 'wcmp-vendor_membership');
    }

}
