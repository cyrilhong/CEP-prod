<?php
if (get_option('wcmp_vendor_membership_license_activated') != 'Activated') {
    add_action('admin_notices', 'WCMP_Vendor_Membership_License::license_inactive_notice');
}

class WCMP_Vendor_Membership_License {

    /**
     * Self Upgrade Values
     */
    // Base URL to the remote upgrade API server
    public $upgrade_url = WCMP_VENDOR_MEMBERSHIP_PLUGIN_SERVER_URL; // URL to access the Update API Manager.

    /**
     * @var string
     * This version is saved after an upgrade to compare this db version to $version
     */
    public $api_manager_license_version_name = 'wcmp-vendor-membership_license_version';

    /**
     * Data defaults
     * @var mixed
     */
    private $license_software_product_id;
    public $license_data_key;
    public $license_api_key;
    public $license_activation_email;
    public $license_product_id_key;
    public $license_instance_key;
    public $license_deactivate_checkbox_key;
    public $license_activated_key;
    public $license_deactivate_checkbox;
    public $license_options;
    public $license_plugin_name;
    public $license_product_id;
    public $license_renew_license_url;
    public $license_instance_id;
    public $license_domain;
    public $license_software_version;
    public $license_plugin_or_theme;
    public $license_plugin_or_theme_mode;
    public $license_update_version;
    public $license_update_check = 'wcmp_vendor_membership_update_check';

    /**
     * Used to send any extra information.
     * @var mixed array, object, string, etc.
     */
    public $license_extra;

    /**
     * @var The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {

        if (is_null(self::$_instance))
            self::$_instance = new self();

        return self::$_instance;
    }

    public function __construct() {
        global $WCMP_Vendor_Membership;

        if (is_admin()) {

            /**
             * Software Product ID is the product title string
             * This value must be unique, and it must match the API tab for the product in WooCommerce
             */
            $this->license_software_product_id = $WCMP_Vendor_Membership->token;

            /**
             * Set all data defaults here
             */
            $this->license_data_key = 'wcmp_' . str_replace('-', '_', esc_attr($WCMP_Vendor_Membership->token)) . '_license_settings_name';
            $this->license_api_key = 'api_key';
            $this->license_activation_email = 'activation_email';
            $this->license_product_id_key = 'wcmp_vendor_membership_license_product_id';
            $this->license_instance_key = 'wcmp_vendor_membership_license_instance';
            $this->license_deactivate_checkbox_key = 'wcmp_vendor_membership_license_deactivate_checkbox';
            $this->license_activated_key = 'wcmp_vendor_membership_license_activated';
            $this->license_deactivate_checkbox = 'deactivation_checkbox';

            /**
             * Set all software update data here
             */
            $this->license_options = get_option($this->license_data_key);
            $this->license_plugin_name = 'wcmp-vendor_membership/vendor_membership.php'; // same as plugin slug. if a theme use a theme name like 'twentyeleven'
            $this->license_product_id = get_option($this->license_product_id_key); // Software Title
            $this->license_renew_license_url = WCMP_VENDOR_MEMBERSHIP_PLUGIN_SERVER_URL . '/my-account'; // URL to renew a license
            $this->license_instance_id = get_option($this->license_instance_key); // Instance ID (unique to each blog activation)
            $this->license_domain = site_url(); // blog domain name
            $this->license_software_version = $WCMP_Vendor_Membership->version; // The software version
            $this->license_plugin_or_theme = 'plugin'; // 'theme' or 'plugin'
            $this->license_plugin_or_theme_mode = 'paid'; // 'paid' or 'free'

            if (!$this->license_product_id)
                $this->activation();

            // Performs activations and deactivations of API License Keys
            $this->load_class('key-api');
            $this->api_manager_license_key = new WCMP_Vendor_Membership_Key_Api();

            // Checks for software updatess
            $this->load_class('update');

            $options = get_option($this->license_data_key);

            /**
             * Check for software updates
             */
            if (!empty($options) && $options !== false) {

                new WCMP_Vendor_Membership_API_Manager_Update_API_Check(
                        $this->upgrade_url, $this->license_plugin_name, $this->license_product_id, $this->license_options[$this->license_api_key], $this->license_options[$this->license_activation_email], $this->license_renew_license_url, $this->license_instance_id, $this->license_domain, $this->license_software_version, $this->license_plugin_or_theme, 'wcmp-vendor_membership'
                );
            }

            // Admin menu with the license key and license email form
            add_action('admin_menu', array($this, 'add_menu'));
        }
    }

    // Add option page menu
    public function add_menu() {
        global $WCMP_Vendor_Membership;
        if (empty($GLOBALS['admin_page_hooks']['wcmp_licenses'])) {
            if (!class_exists('WCMp_License')) {
                require_once ($WCMP_Vendor_Membership->plugin_path . 'classes/license/admin/class-wcmp-license.php');
                new WCMp_License('wcmp_vendor_membership_license');
            }
        }
        add_filter('wcmp_license_tabs', array(&$this, 'license_new_tab'), 10, 1);
        add_action('settings_page_' . str_replace('-', '_', esc_attr($WCMP_Vendor_Membership->token)) . '_license_tab_init', array(&$this, 'license_tab_init'), 10, 1);
    }

    function license_new_tab($tabs) {
        global $WCMP_Vendor_Membership;
        $tabs[str_replace('-', '_', esc_attr($WCMP_Vendor_Membership->token)) . '_license'] = __('Vendor Membership', 'wcmp-vendor_membership');
        return $tabs;
    }

    function license_tab_init($tab) {
        $this->load_class_admin("settings-license");
        new WCMP_Vendor_Membership_Settings_License($tab);
    }

    function load_class($class_name = '') {
        global $WCMP_Vendor_Membership;
        if ('' != $class_name) {
            require_once ($WCMP_Vendor_Membership->plugin_path . '/classes/license/classes/class-' . esc_attr($WCMP_Vendor_Membership->token) . '-' . esc_attr($class_name) . '.php');
        } // End If Statement
    }

// End load_class()

    function load_class_admin($class_name = '') {
        global $WCMP_Vendor_Membership;
        if ('' != $class_name) {
            require_once ($WCMP_Vendor_Membership->plugin_path . '/classes/license/admin/class-' . esc_attr($WCMP_Vendor_Membership->token) . '-' . esc_attr($class_name) . '.php');
        } // End If Statement
    }

// End load_class()

    /**
     * Generate the default data arrays
     */
    public function activation() {
        global $wpdb, $WCMP_Vendor_Membership;

        $global_options = array(
            $this->license_api_key => '',
            $this->license_activation_email => '',
        );

        update_option($this->license_data_key, $global_options);
        // Generate a unique installation $instance id
        $this->load_class('api-manager-passwords');
        $WCMP_Vendor_Membership_API_Manager_Password = new WCMP_Vendor_Membership_API_Manager_Password();
        $instance = $WCMP_Vendor_Membership_API_Manager_Password->generate_password(12, false);

        $single_options = array(
            $this->license_product_id_key => $this->license_software_product_id,
            $this->license_instance_key => $instance,
            $this->license_deactivate_checkbox_key => 'on',
            $this->license_activated_key => 'Deactivated',
        );

        foreach ($single_options as $key => $value) {
            update_option($key, $value);
        }

        $curr_ver = get_option($this->api_manager_license_version_name);

        // checks if the current plugin version is lower than the version being installed
        if (version_compare($this->license_software_version, $curr_ver, '>')) {
            // update the version
            update_option($this->api_manager_license_version_name, $WCMP_Vendor_Membership->version);
        }

        //$this->dc_plugin_tracker('activation');
    }

    /**
     * Deletes all data if plugin deactivated
     * @return void
     */
    public function uninstall() {
        global $wpdb, $blog_id;


        $this->license_key_deactivation();

        // Remove options
        if (is_multisite()) {

            switch_to_blog($blog_id);

            foreach (array(
        $this->license_data_key,
        $this->license_product_id_key,
        $this->license_instance_key,
        $this->license_deactivate_checkbox_key,
        $this->license_activated_key,
            ) as $option) {

                delete_option($option);
            }

            restore_current_blog();
        } else {

            foreach (array(
        $this->license_data_key,
        $this->license_product_id_key,
        $this->license_instance_key,
        $this->license_deactivate_checkbox_key,
        $this->license_activated_key
            ) as $option) {

                delete_option($option);
            }
        }

        //$this->dc_plugin_tracker('deactivation');
    }

    /**
     * Deactivates the license on the API server
     * @return void
     */
    public function license_key_deactivation() {

        $activation_status = get_option($this->license_activated_key);

        $api_email = $this->license_options[$this->license_activation_email];
        $api_key = $this->license_options[$this->license_api_key];

        $args = array(
            'email' => $api_email,
            'licence_key' => $api_key,
        );

        if ($activation_status == 'Activated' && $api_key != '' && $api_email != '') {
            $this->api_manager_license_key->deactivate($args); // reset license key activation
        }
    }

    /**
     * Keep track of plugin status on API server
     */
    public function dc_plugin_tracker($status, $api_key = '', $api_email = '') {
        global $WCMP_Vendor_Membership;

        $api_url = add_query_arg('wc-api', 'dc-plugin-tracker', $this->upgrade_url);

        $license_plugin_or_theme_mode = ($this->license_plugin_or_theme_mode) ? $this->license_plugin_or_theme_mode : 'free';
        if ($api_email == '') {
            $api_email = ($license_plugin_or_theme_mode == 'paid') ? $this->license_options[$this->license_activation_email] : '';
        }
        if ($api_key == '') {
            $api_key = ($license_plugin_or_theme_mode == 'paid') ? $this->license_options[$this->license_api_key] : '';
        }


        $args = array(
            'request' => $status,
            'software_title' => $this->license_software_product_id,
            'software_type' => $this->license_plugin_or_theme,
            'software_mode' => $license_plugin_or_theme_mode,
            'software_version' => $this->license_software_version,
            'site_title' => get_bloginfo('name'),
            'site_url' => $this->license_domain,
            'site_ip' => $_SERVER['REMOTE_ADDR'],
            'site_admin' => get_bloginfo('admin_email'),
            'licence_key' => $api_key,
            'licence_email' => $api_email
        );

        $target_url = $api_url . '&' . http_build_query($args);

        $request = wp_remote_get($target_url, array('sslverify'   => false));

        $response = wp_remote_retrieve_body($request);
    }

    /**
     * Displays an inactive notice when the software is inactive.
     */
    public static function license_inactive_notice() {
        global $WCMP_Vendor_Membership;
        ?>
        <?php if (!current_user_can('manage_options')) return; ?>
        <?php if (isset($_GET['page']) && 'api_manager_license_dashboard' == $_GET['page']) return; ?>
        <div id="message" class="error settings-error notice is-dismissible">
            <p><?php printf(__('The Vendor Membership License Key has not been activated, so the plugin is inactive! %sClick here%s to activate the license key and the plugin.', 'wcmp-vendor_membership'), '<a href="' . esc_url(admin_url('admin.php?page=wcmp-license&tab=' . str_replace('-', '_', esc_attr($WCMP_Vendor_Membership->token)) . '_license')) . '">', '</a>'); ?></p>
        </div>
        <?php
    }

}

// End of class

function WCMP_Vendor_Membership_LICENSE() {
    return WCMP_Vendor_Membership_License::instance();
}
