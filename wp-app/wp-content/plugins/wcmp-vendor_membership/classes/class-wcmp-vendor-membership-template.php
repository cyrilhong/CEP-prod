<?php

class WCMP_Vendor_Membership_Template {

    public $template_url;

    public function __construct() {
        $this->template_url = 'wcmp-vendor-membership/';
        add_action('wcmp_vendor_membership_vendor_plan_list', array($this, 'output_WCMp_Vendor_Membership_vendor_plan'));
        add_action('wcmp_vendor_membership_vendor_plan_payment', array($this, 'output_WCMp_Vendor_Membership_vendor_plan_payment'));
        add_filter('template_include', array($this, 'output_WCMp_Vendor_Membership_vendor_type_template'));
    }

    public function output_WCMp_Vendor_Membership_vendor_plan() {
        $this->get_template('wcmp_vendor_membership_template_vendor_plan.php');
    }

    public function output_WCMp_Vendor_Membership_vendor_type_template($single_template) {
        global $post,$WCMP_Vendor_Membershippost;

        if ($post && $post->post_type == 'vendortype') {
            return $this->get_template('wcmp_vendor_membership_template_vendor_plan_single.php',array('post' => $post));
        } else {
            return $single_template;
        }
    }

    public function output_WCMp_Vendor_Membership_vendor_plan_payment($arrs) {
        $this->get_template('wcmp_vendor_membership_template_vendor_plan_payment.php', array('plan_id' => $arrs['plan_id']));
    }

    /**
     * Get other templates (e.g. product attributes) passing attributes and including the file.
     *
     * @access public
     * @param mixed $template_name
     * @param array $args (default: array())
     * @param string $template_path (default: '')
     * @param string $default_path (default: '')
     * @return void
     */
    public function get_template($template_name, $args = array(), $template_path = '', $default_path = '') {

        if ($args && is_array($args))
            extract($args);

        $located = $this->locate_template($template_name, $template_path, $default_path);

        include ($located);
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     * 		yourtheme		/	$template_path	/	$template_name
     * 		yourtheme		/	$template_name
     * 		$default_path	/	$template_name
     *
     * @access public
     * @param mixed $template_name
     * @param string $template_path (default: '')
     * @param string $default_path (default: '')
     * @return string
     */
    public function locate_template($template_name, $template_path = '', $default_path = '') {
        global $woocommerce, $WCMP_Vendor_Membership;

        if (!$template_path)
            $template_path = $this->template_url;
        if (!$default_path)
            $default_path = $WCMP_Vendor_Membership->plugin_path . '/templates/';

        // Look within passed path within the theme - this is priority
        $template = locate_template(array(trailingslashit($template_path) . $template_name, $template_name));

        // Get default template
        if (!$template)
            $template = $default_path . $template_name;

        // Return what we found
        return $template;
    }

    /**
     * Get template part (for templates like the shop-loop).
     *
     * @access public
     * @param mixed $slug
     * @param string $name (default: '')
     * @return void
     */
    public function get_template_part($slug, $name = '') {
        global $WCMP_Vendor_Membership;
        $template = '';

        // Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php
        if ($name)
            $template = $this->locate_template(array("{$slug}-{$name}.php", "{$this->template_url}{$slug}-{$name}.php"));

        // Get default slug-name.php
        if (!$template && $name && file_exists($WCMP_Vendor_Membership->plugin_path . "templates/{$slug}-{$name}.php"))
            $template = $WCMP_Vendor_Membership->plugin_path . "templates/{$slug}-{$name}.php";

        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
        if (!$template)
            $template = $this->locate_template(array("{$slug}.php", "{$this->template_url}{$slug}.php"));

        echo $template;

        if ($template)
            load_template($template, false);
    }

}
