<?php
/**
 * The template for displaying vendor plan details content.
 *
 * Override this template by copying it to yourtheme/wcmp-vendor-membership/vendor-subscription.php
 *
 * @author 		dualcube
 * @package 	WCMp-Vendor-Membership/Templates
 * @version     0.0.1
 */
if (!defined('ABSPATH')) {
    exit;
}
global $WCMP_Vendor_Membership, $WCMp;

if ($plan) {
    $date_time_subscription = get_user_meta($user_id, 'vendor_plan_start_date_time', true);
    $status = get_user_meta($user_id, 'wcmp_vendor_plan_status', true);
    $trial_subscription = get_user_meta($user_id, '_is_trial', '');
    $trial_amount = get_user_meta($user_id, '_trial_amt', '0');
    $subscription_amount = get_user_meta($user_id, '_vendor_billing_amt', '0');
    if ($trial_subscription == '1' && $trial_amount > 0) {
        $due_amount = $trial_amount;
    } else {
        $due_amount = $subscription_amount;
    }
    $post_id = $plan->ID;
    $subscription_page_id = get_option('wcmp_vendor_categorization_plugin_subscription_page_id');
    ?>
    <div class="membership-details-container" style="padding: 10px;">
        <table class="shop_table shop_table_responsive my_account_orders membership-details">
            <thead>
                <tr>
                    <th class="subscription-type"><span class="nobr"><?php echo __("Subsription", 'wcmp-vendor_membership'); ?></span></th>
                    <th class="subscription-date"><span class="nobr"><?php echo __("Date", 'wcmp-vendor_membership'); ?></span></th>
                    <th class="subscription-status"><span class="nobr"><?php echo __("Status", 'wcmp-vendor_membership'); ?></span></th>
                    <th class="subscription-total"><span class="nobr"><?php echo __("Next Payment Date", 'wcmp-vendor_membership'); ?></span></th>
<!--                    <th class="subscription-freequency"><span class="nobr"><?php echo __("Next Payment Amount", 'wcmp-vendor_membership'); ?></span></th>-->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr class="order">
                    <td data-title="Order" class="order-number">
                        <a href="<?php echo !empty($post_id) ? get_permalink($post_id) : 'javascript:void(0)'; ?>"> <?php echo $plan->post_title; ?> </a>
                        <?php //echo $trial_subscription == '1' ? '(Trial)' : ''; ?>
                    </td>
                    <td data-title="Date" class="order-date">
                        <time title="<?php echo strtotime($date_time_subscription); ?>" datetime="<?php echo @date('Y-m-d', strtotime($date_time_subscription)); ?>"><?php echo date('F d, Y', strtotime($date_time_subscription)); ?></time>
                    </td>
                    <td data-title="Status" class="order-status">
                        <?php echo ucfirst($status); ?>
                    </td>
                    <td data-title="Next Payment Date" class="order-total">
                        <?php echo get_user_meta($user_id, '_next_payment_date', true) ? date('F d, Y', strtotime(get_user_meta($user_id, '_next_payment_date', true))) : 'N/A' ?>
                    </td>
                    <td data-title="Action" class="order-status">
                        <a href="<?php echo get_permalink(get_wcmp_vendor_settings('vendor_membership', 'vendor', 'general')); ?>">Upgrade / Downgrade</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
<?php } ?>

