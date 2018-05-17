<?php

class WCMp_Vendor_Payment {

    public function __construct() {
        
    }

    /**
     * Output the Credit Card Payment shortcode.
     *
     * @access public
     * @param array $atts
     * @return void
     */
    public static function output($attr) {
        global $WCMp,$WCMP_Vendor_Membership, $plan_meta;
        $WCMP_Vendor_Membership->nocache();
        if (isset($_POST['vendor_plan_payment'])) {
            $plan_meta = get_post_meta($_POST['vendor_plan_id']);
            $_SESSION['plan_meta'] = $plan_meta;
            $_SESSION['plan_id'] = $_POST['vendor_plan_id'];
        }
        $frontend_style_path = $WCMp->plugin_url . 'assets/frontend/css/';
        $frontend_style_path = str_replace(array('http:', 'https:'), '', $frontend_style_path);
        $suffix = defined('WCMP_SCRIPT_DEBUG') && WCMP_SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_style('wcmp_vandor_registration_css', $frontend_style_path . 'vendor-registration' . $suffix . '.css', array(), $WCMp->version);
        $vendor_plan_id = isset($_POST['vendor_plan_id']) ? $_POST['vendor_plan_id'] : ''; 
        do_action('wcmp_vendor_membership_vendor_plan_payment',array('plan_id' => $vendor_plan_id));
    }

}
