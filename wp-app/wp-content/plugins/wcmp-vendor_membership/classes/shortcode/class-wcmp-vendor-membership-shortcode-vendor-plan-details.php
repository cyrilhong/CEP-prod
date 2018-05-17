<?php

class WCMp_Vendor_Plan_Details {

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
        global $WCMp, $WCMP_Vendor_Membership;
        $WCMP_Vendor_Membership->nocache();
        $frontend_style_path = $WCMp->plugin_url . 'assets/frontend/css/';
        $frontend_style_path = str_replace(array('http:', 'https:'), '', $frontend_style_path);
        $suffix = defined('WCMP_SCRIPT_DEBUG') && WCMP_SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_style('wcmp_new_vandor_dashboard_css', $frontend_style_path . 'vendor_dashboard' . $suffix . '.css', array(), $WCMp->version);
        wp_enqueue_style('font-awesome', $frontend_style_path . 'font-awesome.min.css', array(), $WCMp->version);

        $frontend_script_path = $WCMp->plugin_url . 'assets/frontend/js/';
        $frontend_script_path = str_replace(array('http:', 'https:'), '', $frontend_script_path);
        $pluginURL = str_replace(array('http:', 'https:'), '', $WCMp->plugin_url);
        $suffix = defined('WCMP_SCRIPT_DEBUG') && WCMP_SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script('wcmp_new_vandor_dashboard_js', $frontend_script_path . '/vendor_dashboard' . $suffix . '.js', array('jquery'), $WCMp->version, true);
        $user = wp_get_current_user();
        if (is_user_logged_in()) {
            if (is_user_wcmp_vendor($user->ID)) {
                echo '<div class="wcmp_remove_div">';
                echo '<div class="wcmp_main_page">';
                $WCMp->template->get_template('vendor_dashboard_menu.php', array('selected_item' => 'plan_details'));
                $current_user = wp_get_current_user();
                if (isset($current_user->ID) && $current_user->ID != 0) {
                    $plan_id = get_user_meta($current_user->ID, 'vendor_group_id', true);
                    if (!empty($plan_id) && $plan_id != 0) {
                        $plan = get_post($plan_id);
                        ?>
                        <div class="wcmp_main_holder toside_fix">
                            <div class="wcmp_headding1">
                                <ul>
                                    <li><?php _e('Store Settings ', $WCMp->text_domain); ?></li>
                                    <li class="next"> < </li>
                                    <li><?php _e('Membership', $WCMp->text_domain); ?></li>
                                </ul>
                                <div class="clear"></div> 
                            </div>           
                            <?php
                            $WCMP_Vendor_Membership->template->get_template('myaccount/vendor-subscription.php', array('plan' => $plan, 'user_id' => get_current_user_id()));
                            ?>
                        </div>
                        <?php
                    }
                }
                echo '</div>';
                echo '</div>';
            } else {
                $WCMp->template->get_template('shortcode/non_vendor_dashboard.php');
            }
        }
    }

}
