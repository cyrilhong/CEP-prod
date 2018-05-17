<?php

class WCMp_Vendor_Membership_Install {

    public function __construct() {
        global $WCMP_Vendor_Membership;
        if (!get_option("wcmp_vendor_membership_plugin_page_install")) {
            $this->wcmp_vendor_membership_plugin_create_pages();
            update_option("wcmp_vendor_membership_plugin_page_install", 1);
        }
    }

    function wcmp_vendor_membership_plugin_create_pages() {
        global $WCMp,$WCMP_Vendor_Membership;
        if(!class_exists('WCMp_Install')){
            require_once ( $WCMp->plugin_path . 'includes/class-wcmp-install.php' );
        }
        $WCMp_Install = new WCMp_Install();
        $WCMp_Install->wcmp_product_vendor_plugin_create_page(esc_sql(_x('vendor-membership', 'page_slug', 'wcmp-vendor_membership')), 'wcmp_vendor_membership_page_id', __('Membership Plans', 'wcmp-vendor_membership'), '[wcmp_vendor_plan]');
        $WCMp_Install->wcmp_product_vendor_plugin_create_page(esc_sql(_x('vendor-membership-ipn', 'page_slug', 'wcmp-vendor_membership')), 'wcmp_vendor_membership_ipn_page_id', __('Vendor IPN', 'wcmp-vendor_membership'), '[wcmp_vendor_ipn]');
        //$WCMp_Install->wcmp_product_vendor_plugin_create_page(esc_sql(_x('vendor-membership-plan-details', 'page_slug', 'wcmp-vendor_membership')), 'wcmp_vendor_membership_plan_details_page_id', __('Membership', 'wcmp-vendor_membership'), '[wcmp_vendor_membership_plan_details]');
        update_wcmp_vendor_settings('vendor_membership', get_option('wcmp_vendor_membership_page_id'), 'vendor', 'general');
        update_wcmp_vendor_settings('vendor_membership_ipn', get_option('wcmp_vendor_membership_page_id'), 'vendor', 'general');
        //update_wcmp_vendor_settings('vendor_membership_plan_details', get_option('wcmp_vendor_membership_page_id'), 'vendor', 'general');
    }
}
