<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('WCMP_Vendor_Membership_Email_New_Subscription')) :

    /**
     * New Order Email
     *
     * An email sent to the vendors when a new order is received/paid for.
     *
     * @class 		WC_Email_New_Order
     * @version		2.0.0
     * @package		WooCommerce/Classes/Emails
     * @author 		WooThemes
     * @extends 	WC_Email
     */
    class WCMP_Vendor_Membership_Email_New_Subscription extends WC_Email {

        /**
         * Constructor
         */
        function __construct() {
            global $WCMP_Vendor_Membership;
            $this->id = 'vendor_membership_new_subscription';
            $this->title = __('New Vendor Subscription', 'wcmp-vendor_membership');
            $this->description = __('New Vendor Subscription notification are sent when user subscribe as vendor using vendor categorization.', 'wcmp-vendor_membership');

            $this->heading = __('New Vendor Subscription', 'wcmp-vendor_membership');
            $this->subject = __('[{site_title}] New Vendor Subscription ({vendor_username}) - {subscription_date}', 'wcmp-vendor_membership');

            $this->template_html = 'emails/new-vendor-subscription.php';
            $this->template_plain = 'emails/plain/new-vendor-subscription.php';
            $this->template_base = $WCMP_Vendor_Membership->plugin_path . 'templates/';
            // Call parent constructor
            parent::__construct();
        }

        /**
         * trigger function.
         *
         * @access public
         * @return void
         */
        function trigger($user_id, $password = 0) {
            $user = new WP_User($user_id);
            if (!empty($user) && $user->ID != 0) {
                $plan_id = get_user_meta($user_id, 'vendor_group_id', true);
                $plan = get_post($plan_id);
                $plan_name = $plan->post_title;
                $user_name = $user->user_login;
                $user_email = $user->user_email;
                $this->find[] = '{subscription_date}';
                $this->replace[] = date_i18n(wc_date_format(), strtotime(@date('Y-m-d H:i:s')));
                $this->find[] = '{vendor_username}';
                $this->replace[] = $user_name;
                $this->find[] = '{site_title}';
                $this->replace[] = esc_html($this->get_blogname());
                $this->username = $user_name;
                $this->password = $password;
                $this->recipient = $user_email;
                $this->plan_name = $plan_name;
                $this->user_id = $user_id;
                $this->user_display_name = $user->display_name;
                if (!$this->is_enabled() || !$this->get_recipient()) {
                    return;
                } else {
                    $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
                }
            }
        }

        /**
         * get_content_html function.
         *
         * @access public
         * @return string
         */
        function get_content_html() {
            ob_start();
            wc_get_template($this->template_html, array(
                'email_heading' => $this->get_heading(),
                'user_id' => $this->user_id,
                'planname' => $this->plan_name,
                'blogname' => $this->get_blogname(),
                'username' => $this->username,
                'password' => $this->password,
                'display_name' => $this->user_display_name,
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => $this
                    ),
                    'wcmp-vendor-membership/',$this->template_base
            );
            return ob_get_clean();
        }

        /**
         * get_content_plain function.
         *
         * @access public
         * @return string
         */
        function get_content_plain() {
            ob_start();
            wc_get_template($this->template_plain, array(
                'email_heading' => $this->get_heading(),
                'user_id' => $this->user_id,
                'planname' => $this->plan_name,
                'blogname' => $this->get_blogname(),
                'sent_to_admin' => true,
                'plain_text' => true,
                'email' => $this
                    )
            );
            return ob_get_clean();
        }

        /**
         * Initialise Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields() {
            global $WCMp, $WCMP_Vendor_Membership;
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'wcmp-vendor_membership'),
                    'type' => 'checkbox',
                    'label' => __('Enable this email notification.', 'wcmp-vendor_membership'),
                    'default' => 'yes'
                ),
                'subject' => array(
                    'title' => __('Subject', 'wcmp-vendor_membership'),
                    'type' => 'text',
                    'description' => sprintf(__('This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'wcmp-vendor_membership'), $this->subject),
                    'placeholder' => '',
                    'default' => ''
                ),
                'heading' => array(
                    'title' => __('Email Heading', 'wcmp-vendor_membership'),
                    'type' => 'text',
                    'description' => sprintf(__('This controls the main heading contained within the email notification. Leave it blank to use the default heading: <code>%s</code>.', 'wcmp-vendor_membership'), $this->heading),
                    'placeholder' => '',
                    'default' => ''
                ),
                'email_type' => array(
                    'title' => __('Email Type', 'wcmp-vendor_membership'),
                    'type' => 'select',
                    'description' => __('Choose which format of email to be sent.', 'wcmp-vendor_membership'),
                    'default' => 'html',
                    'class' => 'email_type',
                    'options' => array(
                        'plain' => __('Plain Text', 'wcmp-vendor_membership'),
                        'html' => __('HTML', 'wcmp-vendor_membership'),
                        'multipart' => __('Multipart', 'wcmp-vendor_membership'),
                    )
                )
            );
        }

    }

    endif;

return new WCMP_Vendor_Membership_Email_New_Subscription();
