<?php
/**
 * The template for displaying wcmp-vendor-categorization plugin content.
 *
 * Override this template by copying it to yourtheme/wcmp-vendor-membership/wcmp-vendor-membership_template_vendor_plan_single.php
 *
 * @author 		dualcube
 * @package 	WCMp-Vendor-Catagorization/Templates
 * @version     0.0.1
 */
if (!defined('ABSPATH')) {
    // Exit if accessed directly
    exit;
}
get_header();
global $WCMP_Vendor_Membership, $WCMp;
$current_user = wp_get_current_user();
if (function_exists('is_user_wcmp_vendor')) {
    $is_vendor = is_user_wcmp_vendor($current_user);
} elseif (function_exists('is_user_wcmp_pending_vendor')) {
    $is_pending_vendor = is_user_wcmp_pending_vendor($current_user);
}
$global_settings = $WCMP_Vendor_Membership->get_global_settings();
$current_stylesheet = get_option('stylesheet');
$stylesheet_support = array('flatsome', 'flatsome-child', 'wyzi-business-finder', 'wyzi-business-finder-child');
$body_class = in_array($current_stylesheet, $stylesheet_support) ? 'container' : '';
?>
<div id="container">
    <div id="content" role="main">
        <?php
        // Start the loop.
        while (have_posts()) : the_post();
            // Include the page content template.
            ?>
            <div id="post-<?php the_ID(); ?>" <?php post_class($body_class); ?>>
                <div class="wcmp-plan-images">
                    <a href="<?php echo get_the_permalink(); ?>" itemprop="image" title=""><img width="300" height="300" src="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); ?>" class="attachment-shop_single size-shop_single wp-post-image" alt="" title="<?php echo the_title(); ?>"></a>
                </div> 
                <div class="summary entry-summary">

                    <h1 itemprop="name" class="product_title entry-title"><?php echo get_the_title(); ?></h1>
                    <hr />
                    <?php if (get_post_meta($post->ID, '_is_free_plan', true) != 'Enable') : ?>
                        <p class="wcmp-plan-price">
                            <?php
                            $_vendor_billing_field = get_post_meta($post->ID, '_vendor_billing_field', true);
                            if (isset($_vendor_billing_field['_initial_payment']) && !empty($_vendor_billing_field['_initial_payment']) && $_vendor_billing_field['_initial_payment'] > 0) {
                                echo __(' Initial Payment ', 'wcmp-vendor_membership');
                                echo get_woocommerce_currency_symbol();
                                echo number_format($_vendor_billing_field['_initial_payment'], 2);
                            }
                            if (isset($_vendor_billing_field['_is_recurring']) && $_vendor_billing_field['_is_recurring'] == 'yes') {
                                if (isset($_vendor_billing_field['_initial_payment']) && $_vendor_billing_field['_initial_payment'] > 0) {
                                    if ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Week') {
                                        echo __(' for First Week', 'wcmp-vendor_membership');
                                    }
                                    if ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'SemiMonth') {
                                        echo __(' for First 15 Days', 'wcmp-vendor_membership');
                                    }
                                    if ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Month') {
                                        echo __(' for First Month', 'wcmp-vendor_membership');
                                    } elseif ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Day') {
                                        echo __(' for First Day', 'wcmp-vendor_membership');
                                    } elseif ($_vendor_billing_field['_vendor_billing_amt_cycle'] == 'Year') {
                                        echo __(' for First Year', 'wcmp-vendor_membership');
                                    }

                                    echo __(' and Next ', 'wcmp-vendor_membership');
                                }
                                $billing_amt = isset($_vendor_billing_field['_vendor_billing_amt']) && !empty($_vendor_billing_field['_vendor_billing_amt']) ? $_vendor_billing_field['_vendor_billing_amt'] : 0;
                                if (isset($global_settings['display_method']) && !empty($global_settings['display_method']) && $global_settings['display_method'] == 'inclusive') {
                                    if (isset($_vendor_billing_field['_vendor_billing_tax_amt']) && !empty($_vendor_billing_field['_vendor_billing_tax_amt'])) {
                                        $billing_amt += $_vendor_billing_field['_vendor_billing_tax_amt'];
                                    }
                                }
                                echo get_woocommerce_currency_symbol() . number_format($billing_amt, 2) . ' per ' . $_vendor_billing_field['_vendor_billing_amt_cycle'];
                            } else {
                                if (isset($_vendor_billing_field['_initial_payment']) && $_vendor_billing_field['_initial_payment'] > 0) {
                                    echo __(' One Time', 'wcmp-vendor_membership');
                                }
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                    <div itemprop="description">
                        <?php echo get_the_content(); ?>
                    </div>
                    <h3 class=""><?php echo __('Features List', 'wcmp-vendor_membership'); ?></h3>
                    <ul>
                        <?php $_vender_featurelist = get_post_meta($post->ID, '_vender_featurelist', true); ?>
                        <?php
                        if (is_array($_vender_featurelist)) {
                            foreach ($_vender_featurelist as $flist) {
                                ?>
                                <li><?php echo $flist; ?></li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                    <?php $payment_page_url = get_option('wcmp_product_vendor_registration_page_id'); ?>
                    <form name="frm_subscribe_vandor_plan" method="post" action="<?php echo get_permalink($payment_page_url); ?>" >
                        <input type="hidden" name="vendor_plan_id" value="<?php echo $post->ID; ?>" />
                        <div class="subscription-container">

                            <?php
                            $button_text = __('Subscribe Now', 'wcmp-vendor_membership');
                            if (isset($_vendor_billing_field['_subscribe_button_text']) && $_vendor_billing_field['_subscribe_button_text'] != '') {
                                $button_text = $_vendor_billing_field['_subscribe_button_text'];
                            }
                            if ($current_user->ID != 0) {
                                $button_text = __('Upgrade Now', 'wcmp-vendor_membership');
                                if (isset($_vendor_billing_field['_subscribe_button_text_logged_in']) && $_vendor_billing_field['_subscribe_button_text_logged_in'] != '') {
                                    $button_text = $_vendor_billing_field['_subscribe_button_text_logged_in'];
                                }
                            }
                            if (isset($is_vendor) && $is_vendor != 0 && $is_vendor != '' && $is_vendor != false) {

                                $button_text = __('Upgrade Now', 'wcmp-vendor_membership');
                                if (isset($_vendor_billing_field['_subscribe_button_text_upgrade']) && $_vendor_billing_field['_subscribe_button_text_upgrade'] != '') {
                                    $button_text = $_vendor_billing_field['_subscribe_button_text_upgrade'];
                                }
                            }

                            if (current_user_can('manage_options')) {
                                ?>
                                <p style="color:red;">
                                    <?php echo __('Sorry You are logged in as Admin Please try with another account or logoff', 'wcmp-vendor_membership'); ?>
                                </p>

                                <?php
                            } else {
                                ?>

                                <input type="submit" value="<?php echo $button_text; ?>" name="vendor_plan_payment" class="button vendor_subscribe_now" />
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>

            <?php
        // End the loop.
        endwhile;
        ?>
    </div>
</div>

<?php
get_footer();
