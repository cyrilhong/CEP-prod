<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/wcmp-vendor-membership/emails/plain/vendor-new-order.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $WCMp, $WCMP_Vendor_Membership;
echo $email_heading . "\n\n";
printf( __( 'Thanks %s for subscribing on %s for %s Plan', 'wcmp-vendor_membership' ), $display_name, esc_html( $blogname ) , esc_html($planname) ). "\n\n"; 

echo "****************************************************\n\n";

printf( __( "Your User name has been automatically generated: %s", 'wcmp-vendor_membership' ), esc_html( $username ) ). "\n\n"; 
if(isset($password) && !empty($password)) {
printf( __( "Your password has been automatically generated: %s", 'wcmp-vendor_membership' ), esc_html( $password ) ). "\n\n"; 
}
printf( __( 'You can access your account area to view your orders, subscription and change your password here: %s.', 'wcmp-vendor_membership' ), wc_get_page_permalink( 'myaccount' ) ). "\n\n";

echo "\n****************************************************\n\n";

echo apply_filters( 'wcmp_email_footer_text', get_option( 'wcmp_email_footer_text' ) );