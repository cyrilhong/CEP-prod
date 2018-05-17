<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/wcmp-vendor-membership/emails/vendor-new-order.php
 *
 * @author 		dualcube
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
global  $WCMp, $WCMP_Vendor_Membership;
do_action( 'woocommerce_email_header', $email_heading );

?>

<p><?php printf( __( 'Thanks %s for subscribing on %s for %s Plan', 'wcmp-vendor_membership' ), $display_name, esc_html( $blogname ) , esc_html($planname) ); ?></p>


<p><?php printf( __( "Your User name has been automatically generated: <strong>%s</strong>", 'wcmp-vendor_membership' ), esc_html( $username ) ); ?></p>

<?php if(isset($password) && !empty($password)) { ?>
<p><?php printf( __( "Your password has been automatically generated: <strong>%s</strong>", 'wcmp-vendor_membership' ), esc_html( $password ) ); ?></p>
<?php } ?>


<p><?php printf( __( 'You can access your account area to view your orders, subscription and change your password here: %s.', 'wcmp-vendor_membership' ), wc_get_page_permalink( 'myaccount' ) ); ?></p>



<?php do_action( 'wcmp_email_footer' ); ?>