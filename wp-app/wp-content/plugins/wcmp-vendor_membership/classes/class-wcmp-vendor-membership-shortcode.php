<?php

class WCMP_Vendor_Membership_Shortcode {

    public $list_product;

    public function __construct() {
        add_shortcode('wcmp_vendor_plan', array($this, 'vendor_plan'));
        add_shortcode('wcmp_vendor_ipn', array($this, 'vendor_ipn'));
        add_shortcode('wcmp_vendor_membership_plan_details', array(&$this, 'vendor_plan_details'));
        remove_shortcode('vendor_registration');
        add_shortcode('vendor_registration', array(&$this, 'vendor_payment'));
        add_filter('the_title', array(&$this, 'change_registration_page_title'), 10, 2);
    }

    public function change_registration_page_title($title, $id) {
        global $WCMP_Vendor_Membership;
        if (is_page($id) && get_option('wcmp_product_vendor_registration_page_id') == $id && (isset($_SESSION['plan_id']) || isset($_POST['vendor_plan_id']))) {
            if (isset($_POST['vendor_plan_id'])) {
                $_SESSION['plan_id'] = $_POST['vendor_plan_id'];
            }
            $title = __('Become a ', 'wcmp-vendor_membership') . get_the_title($_SESSION['plan_id']) . __(' Member', 'wcmp-vendor_membership');
        }
        return $title;
    }

    public function vendor_plan($attr) {
        $this->load_class('vendor-plan');
        return $this->shortcode_wrapper(array('WCMp_Vendor_Plan', 'output'));
    }

    public function vendor_ipn($attr) {
        $this->load_class('vendor-ipn');
        return $this->shortcode_wrapper(array('WCMp_Vendor_IPN', 'output'));
    }

    public function vendor_payment($attr) {
        $this->load_class('vendor-payment');
        return $this->shortcode_wrapper(array('WCMp_Vendor_Payment', 'output'));
    }

    public function vendor_plan_details($attr) {
        $this->load_class('vendor-plan-details');
        return $this->shortcode_wrapper(array('WCMp_Vendor_Plan_Details', 'output'));
    }

    /**
     * Helper Functions
     */

    /**
     * Shortcode Wrapper
     *
     * @access public
     * @param mixed $function
     * @param array $atts (default: array())
     * @return string
     */
    public function shortcode_wrapper($function, $atts = array()) {
        ob_start();
        call_user_func($function, $atts);
        return ob_get_clean();
    }

    /**
     * Shortcode CLass Loader
     *
     * @access public
     * @param mixed $class_name
     * @return void
     */
    public function load_class($class_name = '') {
        global $WCMP_Vendor_Membership;
        if ('' != $class_name && '' != $WCMP_Vendor_Membership->token) {
            require_once ('shortcode/class-' . esc_attr($WCMP_Vendor_Membership->token) . '-shortcode-' . esc_attr($class_name) . '.php');
        }
    }

}
