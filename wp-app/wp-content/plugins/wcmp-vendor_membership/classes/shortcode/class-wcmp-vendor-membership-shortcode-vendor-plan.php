<?php

class WCMp_Vendor_Plan {

    public function __construct() {
        
    }

    /**
     * Output the demo shortcode.
     *
     * @access public
     * @param array $atts
     * @return void
     */
    public static function output($attr) {
        global $WCMP_Vendor_Membership;
        $WCMP_Vendor_Membership->nocache();
        wp_enqueue_style('wvm-paln-list', $WCMP_Vendor_Membership->plugin_url . 'assets/frontend/css/wvm_paln_list.css',array(),$WCMP_Vendor_Membership->version);
        do_action('wcmp_vendor_membership_vendor_plan_list');
    }

}
