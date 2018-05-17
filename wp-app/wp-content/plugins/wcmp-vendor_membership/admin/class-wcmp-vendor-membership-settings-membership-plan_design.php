<?php

class WCMP_Vendor_Plan_Design_Settings_Gneral {

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
                        "_plan_button_text" => array('title' => __('Subscribe Button Text', 'wcmp-vendor_membership'), 'type' => 'text', 'id' => '_plan_button_text', 'label_for' => '_plan_button_text', 'name' => '_plan_button_text', 'hints' => __('Enter subscribe button text', 'wcmp-vendor_membership'), 'desc' => __('Your business paypal email which will take the registration fee .', 'wcmp-vendor_membership')), // Text
                        "_subscribe_btn_color" => array('title' => __('Subscribe Button Color', 'wcmp-vendor_membership'), 'type' => 'colorpicker', 'id' => '_subscribe_btn_color', 'label_for' => '_subscribe_btn_color', 'name' => '_subscribe_btn_color', 'default' => '#FFFFFF', 'desc' => __('Choose your Subscribe Button color', 'wcmp-vendor_membership'), 'wcmp-vendor_membership'),
                        "_plan_header_color" => array('title' => __('Subscribe Header Color', 'wcmp-vendor_membership'), 'type' => 'colorpicker', 'id' => '_plan_header_color', 'label_for' => '_plan_header_color', 'name' => '_plan_header_color', 'default' => '#FFFFFF', 'desc' => __('Choose your Subscribe Header color', 'wcmp-vendor_membership'), 'wcmp-vendor_membership'),
                        "_plan_header_text_color" => array('title' => __('Subscribe Header Text Color', 'wcmp-vendor_membership'), 'type' => 'colorpicker', 'id' => '_plan_header_text_color', 'label_for' => '_plan_header_text_color', 'name' => '_plan_header_text_color', 'default' => '#FFFFFF', 'desc' => __('Choose your Subscribe Header Text color', 'wcmp-vendor_membership'), 'wcmp-vendor_membership'),
                        "_plan_header_body_color" => array('title' => __('Subscribe Body Color', 'wcmp-vendor_membership'), 'type' => 'colorpicker', 'id' => '_plan_header_body_color', 'label_for' => '_plan_header_body_color', 'name' => '_plan_header_body_color', 'default' => '#FFFFFF', 'desc' => __('Choose your Subscribe Body color', 'wcmp-vendor_membership'), 'wcmp-vendor_membership'),
                        "_plan_price_color" => array('title' => __('Price Color', 'wcmp-vendor_membership'), 'type' => 'colorpicker', 'id' => '_plan_price_color', 'label_for' => '_plan_price_color', 'name' => '_plan_price_color', 'default' => '#FFFFFF', 'desc' => __('Choose your Plan Price color', 'wcmp-vendor_membership'), 'wcmp-vendor_membership'),
                        "_plan_short_descr_color" => array('title' => __('Short Description Color', 'wcmp-vendor_membership'), 'type' => 'colorpicker', 'id' => '_plan_short_descr_color', 'label_for' => '_plan_short_descr_color', 'name' => '_plan_short_descr_color', 'default' => '#FFFFFF', 'desc' => __('Choose your Plan Short Description color', 'wcmp-vendor_membership'), 'wcmp-vendor_membership'),
                        "_plan_featured_color" => array('title' => __('Featured Text Color', 'wcmp-vendor_membership'), 'type' => 'colorpicker', 'id' => '_plan_featured_color', 'label_for' => '_plan_featured_color', 'name' => '_plan_featured_color', 'default' => '#FFFFFF', 'desc' => __('Choose your plan featured text color', 'wcmp-vendor_membership'), 'wcmp-vendor_membership'),
                    ),
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
    public function wcmp_membership_plan_design_settings_sanitize($input) {
        global $WCMP_Vendor_Membership;
        $new_input = array();
        $hasError = false;
        if (isset($input['_plan_button_text'])) {
            $new_input['_plan_button_text'] = sanitize_text_field($input['_plan_button_text']);
        }
        if (isset($input['_subscribe_btn_color'])) {
            $new_input['_subscribe_btn_color'] = sanitize_text_field($input['_subscribe_btn_color']);
        }
        if (isset($input['_plan_header_color'])) {
            $new_input['_plan_header_color'] = sanitize_text_field($input['_plan_header_color']);
        }
        if (isset($input['_plan_header_text_color'])) {
            $new_input['_plan_header_text_color'] = sanitize_text_field($input['_plan_header_text_color']);
        }
        if (isset($input['_plan_header_body_color'])) {
            $new_input['_plan_header_body_color'] = sanitize_text_field($input['_plan_header_body_color']);
        }
        if (isset($input['_plan_price_color'])) {
            $new_input['_plan_price_color'] = sanitize_text_field($input['_plan_price_color']);
        }
        if (isset($input['_plan_short_descr_color'])) {
            $new_input['_plan_short_descr_color'] = sanitize_text_field($input['_plan_short_descr_color']);
        }
        if (isset($input['_plan_featured_color'])) {
            $new_input['_plan_featured_color'] = sanitize_text_field($input['_plan_featured_color']);
        }
        
        if (!$hasError) {
            add_settings_error(
                    "wcmp_{$this->tab}_{$this->subsection}_settings_name", esc_attr("wcmp_{$this->tab}_{$this->subsection}_settings_admin_updated"), __('Membership Plan Design settings updated', 'wcmp-vendor_membership'), 'updated'
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

}
