<?php

class WCMP_Vendor_Membership_Settings {

    private $tabs = array();
    private $options;
    private $tabsection_membership = array();

    /**
     * Start up
     */
    public function __construct() {
        // Admin menu
        global $WCMP_Vendor_Membership;
        //add_filter("wcmp_tabsection_vendor", array($this, 'add_vendor_categorization_tab'), 15, 1);
        add_filter('wcmp_tabs', array(&$this, 'add_membership_tab'), 15, 1);

        // Settings tabs
        add_action('settings_page_membership_tab_init', array(&$this, 'membership_general_tab_init'), 10, 1);
        add_action('settings_page_membership_payment_tab_init', array(&$this, 'membership_payment_tab_init'), 10, 2);
        add_action('settings_page_membership_plan_design_tab_init', array(&$this, 'membership_plan_design_tab_init'), 10, 2);
        add_filter('display_wcmp_sublink', array(&$this, 'display_wcmp_sublink_callback'), 10, 2);
        add_filter('wcmp_subtab', array(&$this, 'add_membership_subtab'), 10, 2);
        add_action('after_setup_wcmp_settings_page', array(&$this, 'after_settings_page_init'), 10, 1);
        $this->tabsection_membership = apply_filters('wcmp_tabsection_membership', array(
            'general' => __('General', 'wcmp-vendor_membership'),
            'payment' => __('Payment Settings', 'wcmp-vendor_membership'),
            'plan_design' => __('Template Design', 'wcmp-vendor_membership')
        ));
    }

    function add_membership_tab($tabs) {
        global $WCMP_Vendor_Membership;
        $tabs['membership'] = __('Membership', 'wcmp-vendor_membership');
        return $tabs;
    }

    function display_wcmp_sublink_callback($show, $tab) {
        if ($tab == 'membership') {
            $show = true;
        }
        return $show;
    }

    function add_membership_subtab($sublinks, $tab) {
        global $WCMP_Vendor_Membership;
        if ($tab == 'membership') {
            foreach ($this->tabsection_membership as $tabsection => $sectionname) {
                if (isset($_GET['tab_section'])) {
                    $current_section = $_GET['tab_section'];
                } else {
                    $current_section = 'general';
                }
                if ($tabsection == $current_section) {
                    if($tabsection == 'general'){
                        $sublinks[] = "<li><a class='current wcmp_sub_sction' href='?page=wcmp-setting-admin&tab=$tab'>$sectionname</a>  </li>";
                    } else{
                        $sublinks[] = "<li><a class='current wcmp_sub_sction' href='?page=wcmp-setting-admin&tab=$tab&tab_section=$tabsection'>$sectionname</a>  </li>";
                    }
                } else {
                    if($tabsection == 'general'){
                        $sublinks[] = "<li><a class='wcmp_sub_sction' href='?page=wcmp-setting-admin&tab=$tab'>$sectionname</a>  </li>";
                    } else{
                        $sublinks[] = "<li><a class='wcmp_sub_sction' href='?page=wcmp-setting-admin&tab=$tab&tab_section=$tabsection'>$sectionname</a>  </li>";
                    }
                }
            }
        }
        return $sublinks;
    }

    function after_settings_page_init($tab) {
        //echo $tab;die;
        if ($tab == 'membership') {
            foreach ($this->tabsection_membership as $tabsection => $sectionname) {
                if ($tabsection == 'general') {
                    
                } else {
                    do_action("settings_page_{$tab}_{$tabsection}_tab_init", $tab, $tabsection);
                }
            }
        }
    }

    function membership_general_tab_init($tab) {
        global $WCMP_Vendor_Membership;
        $WCMP_Vendor_Membership->admin->load_class("settings-{$tab}", $WCMP_Vendor_Membership->plugin_path, $WCMP_Vendor_Membership->token);
        new WCMP_Vendor_General_Settings_Gneral($tab);
    }

    function membership_payment_tab_init($tab, $subsection) {
        global $WCMP_Vendor_Membership;
        $WCMP_Vendor_Membership->admin->load_class("settings-{$tab}-{$subsection}", $WCMP_Vendor_Membership->plugin_path, $WCMP_Vendor_Membership->token);
        new WCMP_Vendor_Membership_Settings_Gneral($tab, $subsection);
    }

    function membership_plan_design_tab_init($tab, $subsection) {
        global $WCMP_Vendor_Membership;
        $WCMP_Vendor_Membership->admin->load_class("settings-{$tab}-{$subsection}", $WCMP_Vendor_Membership->plugin_path, $WCMP_Vendor_Membership->token);
        new WCMP_Vendor_Plan_Design_Settings_Gneral($tab, $subsection);
    }

}
