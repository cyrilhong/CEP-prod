<?php
/**
 * The template for displaying  wcmp-vendor-categorization plugin content.
 *
 * Override this template by copying it to yourtheme/wcmp-vendor-membership/wcmp-vendor-membership_template_vendor_plan.php
 *
 * @author 		dualcube
 * @package 	WCMp-Vendor-Catagorization/Templates
 * @version     0.0.1
 */
if (!defined('ABSPATH') && !function_exists('get_wcmp_vendor_settings')) {
    // Exit if accessed directly
    exit;
}
global $post, $WCMP_Vendor_Membership;
$global_settings = $WCMP_Vendor_Membership->get_global_settings();
$args = array(
    'posts_per_page' => -1,
    'offset' => 0,
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'meta_key' => 'is_visible_in_list',
    'meta_value' => 'Enable',
    'post_type' => 'vendortype',
    'post_status' => 'publish',
    'suppress_filters' => true
);
$posts_array = get_posts($args);
$plan_id = get_user_meta(get_current_user_id(), 'vendor_group_id', true) ? get_user_meta(get_current_user_id(), 'vendor_group_id', true) : '';
$recomended_count = 0;

?>
<div id="wvm_pricr" class="wvm_plans wvm_<?php echo count($posts_array); ?>_plans wvm_style_basic">
    <div class="">
        <?php foreach ($posts_array as $index => $postdata) { $vendor_billing_field = get_post_meta($postdata->ID, '_vendor_billing_field', true); ?>
        <div class="wvm_plan wvm_plan_<?php echo $index; ?> <?php if (get_post_meta($postdata->ID, 'mark_as_recommended', true) && $recomended_count==0) { echo 'wvm_recommended_plan'; } ?> ">
                <?php if (get_post_meta($postdata->ID, 'mark_as_recommended', true) && $recomended_count==0): ; ?>
                    <div class="recomended-batch"></div>
                <?php endif; ?>
                <div class="wvm_title wvm_title_<?php echo $index; ?>" style="background: <?php echo get_wcmp_vendor_settings('_plan_header_color', 'membership', 'plan_design', '#00000'); ?>; color: <?php echo get_wcmp_vendor_settings('_plan_header_text_color', 'membership', 'plan_design', '#00000'); ?>"><?php echo $postdata->post_title; ?>
                </div>
                <div class="wvm_head wvm_head_<?php echo $index; ?>" style="background: <?php echo get_wcmp_vendor_settings('_plan_header_body_color', 'membership', 'plan_design', '#00000'); ?>">
                        <?php if (get_post_meta($postdata->ID, '_is_free_plan', true) != 'Enable') : ?>
                        <div class="wvm_recurrence wvm_recurrence_<?php echo $index; ?>"><?php
                            echo is_wvm_recurring($postdata->ID, 'value');
                            if (is_wvm_recurring($postdata->ID)) {
                                $billing_amt = isset($vendor_billing_field['_vendor_billing_amt']) && !empty($vendor_billing_field['_vendor_billing_amt']) ? $vendor_billing_field['_vendor_billing_amt'] : 0;
                                $tax_amt = isset($vendor_billing_field['_vendor_billing_tax_amt']) && !empty($vendor_billing_field['_vendor_billing_tax_amt']) ? $vendor_billing_field['_vendor_billing_tax_amt'] : 0;
                                if (isset($global_settings['display_method']) && !empty($global_settings['display_method']) && $global_settings['display_method'] == 'inclusive') {
                                    $billing_amt += $tax_amt;
                                }
                                echo ' ' . get_woocommerce_currency_symbol() . $billing_amt;
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="wvm_price wvm_price_<?php echo $index; ?>" style="color: <?php echo get_wcmp_vendor_settings('_plan_price_color', 'membership', 'plan_design', '#00000'); ?>"><?php echo isset($vendor_billing_field['_initial_payment']) && !empty($vendor_billing_field['_initial_payment']) && (get_post_meta($postdata->ID, '_is_free_plan', true) != 'Enable') ? get_woocommerce_currency_symbol() . $vendor_billing_field['_initial_payment'] : __('Free', 'wcmp-vendor_membership'); ?></div>
                    <div style="color: <?php echo get_wcmp_vendor_settings('_plan_short_descr_color', 'membership', 'plan_design', '#00000'); ?>" class="wvm_subtitle wvm_subtitle_<?php echo $index; ?>"><?php echo isset($vendor_billing_field['_plan_short_desc']) ? $vendor_billing_field['_plan_short_desc'] : ''; ?></div>
                </div>
                <div class="wvm_features wvm_features_0">
                    <?php $_vender_featurelist = get_post_meta($postdata->ID, '_vender_featurelist', true); ?>
                    <?php if (is_array($_vender_featurelist)) : ?>
                    <?php foreach ($_vender_featurelist as $feature_count => $feature) { ?>
                            <div style="color:<?php echo get_wcmp_vendor_settings('_plan_featured_color', 'membership', 'plan_design', '#00000'); ?>" class="wvm_feature wvm_feature_<?php echo $index; ?>-<?php echo $feature_count; ?>"><?php echo $feature; ?></div>
                    <?php } ?>
                <?php endif; ?>
                </div>
                <?php
                if(get_post_meta($postdata->ID, 'mark_as_recommended', true) && $recomended_count==0){
                    $subscribe_btn_color = '';
                    $recomended_count++;
                } else{
                    $subscribe_btn_color = get_wcmp_vendor_settings('_subscribe_btn_color', 'membership', 'plan_design', '#000000');
                }
                ?>
                <a target="_self" href="<?php echo get_permalink($postdata->ID); ?>" <?php if (!empty($subscribe_btn_color)) {
                    echo 'style="background-color:' . $subscribe_btn_color . '"';
                } ?>  class="wvm_foot wvm_foot_0 <?php if ($plan_id == $postdata->ID) {
                    echo 'disabled';
                } ?>"><?php _e(get_wcmp_vendor_settings('_plan_button_text', 'membership', 'plan_design', 'Sign Up'), 'wcmp-vendor_membership'); ?></a>
            </div>
<?php } ?>
    </div>
</div>
<div style="clear:both;"></div>