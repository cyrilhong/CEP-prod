<?php

class WCMP_Vendor_Membership_Admin {

    public $settings;
    public $option_prefix = 'wcmp-vendor_membership';
    public $membership_list_table;

    public function __construct() {
        //admin script and style
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'));
        add_filter("settings_vendor_general_tab_options", array($this, "wcmp_vendor_membership_settings_option"));
        add_action('admin_menu', array(&$this, 'add_plan_submenu_page'));
        $this->load_class('settings');
        $this->settings = new WCMP_Vendor_Membership_Settings();
        add_action('admin_init', array(&$this, 'init_wcmp_vendor_membership_admin'));
        add_filter("settings_vendor_general_tab_new_input", array($this, "vendor_membership_settings_sanitize"), 10, 2);
    }

    public function init_wcmp_vendor_membership_admin() {
        global $WCMP_Vendor_Membership;
        if (is_user_wcmp_vendor(get_current_user_id()) && get_user_meta(get_current_user_id(), 'vendor_group_id', true)) {
            remove_meta_box('product_catdiv', 'product', 'side');
            add_meta_box(
                    'product_catdiv', __('Product categories', 'wcmp-vendor_membership'), array($this, 'vendor_product_cat_callback'), 'product', 'side', 'default'
            );
            add_action('save_post_product', array(&$this, 'save_post_product'),10, 2);
        }
        
    }

    public function vendor_product_cat_callback() {
        global $WCMP_Vendor_Membership, $post;
        $tax_name = 'product_cat';
        $terms = wp_list_pluck(wp_get_object_terms($post->ID, $tax_name), 'term_id');
        $categorys = get_terms(array('taxonomy' => $tax_name));
        $plan_id = get_user_meta(get_current_user_id(), 'vendor_group_id', true);
        if ($plan_id) {
            $allowed_product_cats = get_post_meta($plan_id, '_allowed_product_cats', true);
            if ($allowed_product_cats && is_array($allowed_product_cats)) {
                foreach ($categorys as $index => $category) {
                    if (!in_array($category->term_id, $allowed_product_cats)) {
                        unset($categorys[$index]);
                    }
                }
            }
        }
        ?>
        <div class="productcategorydiv">
            <ul id="<?php echo $tax_name; ?>checklist" class="categorychecklist form-no-clear">
                <input type="hidden" name="wcmp_vendor_product_cat" />
                <?php
                $walker = new Walker_Category_Checklist;
                $args = array();
                $args['selected_cats'] = $terms;
                echo call_user_func_array(array($walker, 'walk'), array($categorys, 0, $args));
                ?>
            </ul>
        </div>
        <style type="text/css">
            .productcategorydiv .categorychecklist ul.children {
                padding-left: 18px;
            }
        </style>
        <?php
    }

    public function save_post_product($product_id, $product) {
        if (isset($_POST['wcmp_vendor_product_cat'])) {
            wp_set_object_terms($product_id, array(), 'product_cat');
            if (isset($_POST['post_category'])) {
                $selected_cat = $_POST['post_category'];
                if ($selected_cat && is_array($selected_cat)) {
                    foreach ($selected_cat as $cat_id) {
                        wp_set_object_terms($product_id, (int) $cat_id, 'product_cat', true);
                    }
                }
            }
        }
    }

    function load_class($class_name = '') {
        global $WCMP_Vendor_Membership;
        if ('' != $class_name) {
            require_once ($WCMP_Vendor_Membership->plugin_path . '/admin/class-' . esc_attr($WCMP_Vendor_Membership->token) . '-' . esc_attr($class_name) . '.php');
        } // End If Statement
    }

    function wcmp_vendor_membership_settings_option($settings_tab_options) {
        global $WCMp, $WCMP_Vendor_Membership;
        $pages = get_pages();
        $woocommerce_pages = array(wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount'));
        foreach ($pages as $page) {
            if (!in_array($page->ID, $woocommerce_pages)) {
                $pages_array[$page->ID] = $page->post_title;
            }
        }

        if (get_option('wcmp_vendor_membership_page_id') && !get_wcmp_vendor_settings('vendor_membership', 'vendor', 'general')) {
            update_wcmp_vendor_settings('vendor_membership', get_option('wcmp_vendor_membership_page_id'), 'vendor', 'general');
        }
        if (get_option('wcmp_vendor_membership_ipn_page_id') && !get_wcmp_vendor_settings('vendor_membership_ipn', 'vendor', 'general')) {
            update_wcmp_vendor_settings('vendor_membership_ipn', get_option('wcmp_vendor_membership_ipn_page_id'), 'vendor', 'general');
        }

        $settings_tab_options["sections"]["wcmp_pages_section"]["fields"]["vendor_membership"] = array('title' => __('Vendor Membership', 'wcmp-vendor_membership'), 'type' => 'select', 'options' => $pages_array, 'hints' => __('Choose your preferred page for Vendor Membership List.', 'wcmp-vendor_membership'));
        $settings_tab_options["sections"]["wcmp_pages_section"]["fields"]["vendor_membership_ipn"] = array('title' => __('Vendor Membership IPN', 'wcmp-vendor_membership'), 'type' => 'select', 'options' => $pages_array, 'hints' => __('Choose your preferred page for Vendor Membership IPN.', 'wcmp-vendor_membership'));
        //$settings_tab_options["sections"]["wcmp_pages_section"]["fields"]["vendor_membership_plan_details"] = array('title' => __('Vendor Membership Details', 'wcmp-vendor_membership'), 'type' => 'select', 'options' => $pages_array, 'hints' => __('Choose your preferred page for Vendor Membership Details.', 'wcmp-vendor_membership'));
        return $settings_tab_options;
    }
    
    public function vendor_membership_settings_sanitize($new_input, $input){
        global $WCMp, $WCMP_Vendor_Membership;
        if (isset($input['vendor_membership'])) {
            $new_input['vendor_membership'] = sanitize_text_field($input['vendor_membership']);
        }

        if (isset($input['vendor_membership_ipn'])) {
            $new_input['vendor_membership_ipn'] = sanitize_text_field($input['vendor_membership_ipn']);
        }
        return $new_input;
    }

    /**
     * Admin Scripts
     */
    public function enqueue_admin_script() {
        global $WCMP_Vendor_Membership;
        $screen = get_current_screen();
        // Enqueue admin script and stylesheet from here
        if (in_array($screen->id, array('vendortype_page_memberships'))) :
            wp_enqueue_style('admin_css', $WCMP_Vendor_Membership->plugin_url . 'assets/admin/css/admin.css', array(), $WCMP_Vendor_Membership->version);
        endif;

        if (in_array($screen->id, array('vendortype'))) {
            wp_enqueue_style('vendor_type_css', $WCMP_Vendor_Membership->plugin_url . 'assets/admin/css/vendor_type.css', array(), $WCMP_Vendor_Membership->version);
            wp_enqueue_script('vendor_type_js', $WCMP_Vendor_Membership->plugin_url . 'assets/admin/js/vendor_type.js', array('jquery'), $WCMP_Vendor_Membership->version, true);
        }
    }

    public function add_plan_submenu_page() {
        global $WCMP_Vendor_Membership;
        $page_hook = add_submenu_page('edit.php?post_type=vendortype', __('Memberships', 'wcmp-vendor_membership'), __('Memberships', 'wcmp-vendor_membership'), 'manage_woocommerce', 'memberships', array(&$this, 'memberships_management_page'));
        add_action("load-$page_hook", array(&$this, 'membership_option'));
        add_action("load-$page_hook", array(&$this, 'get_membership_list_table'));
    }

    public function membership_option() {
        global $WCMP_Vendor_Membership;
        add_screen_option('per_page', array(
            'label' => __('Memberships', 'wcmp-vendor_membership'),
            'default' => 10,
            'option' => $this->option_prefix . '_admin_per_page')
        );
    }

    public function get_membership_list_table() {
        if (!isset($this->subscriptions_list_table)) {
            if (!class_exists('WCMp_Vendor_Membership_List_Table')) {
                $this->load_class('list-table');
                $this->membership_list_table = new WCMp_Vendor_Membership_List_Table();
            }
        }
        if (isset($_GET['action']) && isset($_GET['user']) && $_GET['action'] == 'approve' && !empty($_GET['user'])) {
            $user_id = $_GET['user'];
            $user_obj = new WP_User($user_id);
            $user_obj->set_role('dc_vendor');
        } else if (isset($_GET['action']) && isset($_GET['user']) && $_GET['action'] == 'reject' && !empty($_GET['user'])) {
            $user_id = $_GET['user'];
            $user_obj = new WP_User($user_id);
            $user_obj->set_role('dc_rejected_vendor');
        }
        return $this->membership_list_table;
    }

    public function memberships_management_page() {
        global $WCMP_Vendor_Membership;
        $membership_table = $this->get_membership_list_table();
        $membership_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Manage Memberships', 'wcmp-vendor_membership'); ?></h1>
            <form id="subscriptions-filter" action="" method="get">
                <input type="hidden" name="post_type" value="vendortype" />
                <input type="hidden" name="page" value="memberships" /> 
                <?php $membership_table->display(); ?>
            </form>
        </div>
        <?php
    }

}
