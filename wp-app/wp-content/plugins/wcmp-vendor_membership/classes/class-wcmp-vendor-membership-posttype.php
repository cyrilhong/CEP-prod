<?php

class WCMP_Vendor_Membership_Posttype {

    public function __construct() {
        $this->register_vendor_type();
        add_action('add_meta_boxes', array(&$this, 'add_vendortype_metaboxes'));
        add_action('save_post_vendortype', array(&$this, 'save_vendor_type_metadata'), 10, 2);
    }

    /**
     * Register post type vendortype 
     * @global class $WCMP_Vendor_Membership
     */
    function register_vendor_type() {
        global $WCMP_Vendor_Membership;
        $labels = array(
            'name' => _x('Vendor Type', 'post type general name', 'wcmp-vendor_membership'),
            'singular_name' => _x('Vendor Type', 'post type singular name', 'wcmp-vendor_membership'),
            'menu_name' => _x('Vendor Types', 'admin menu', 'wcmp-vendor_membership'),
            'name_admin_bar' => _x('Vendor Type', 'add new on admin bar', 'wcmp-vendor_membership'),
            'add_new' => _x('Add New', 'Vendor Type', 'wcmp-vendor_membership'),
            'add_new_item' => __('Add New Vendor Type', 'wcmp-vendor_membership'),
            'new_item' => __('New Vendor Type', 'wcmp-vendor_membership'),
            'edit_item' => __('Edit Vendor Type', 'wcmp-vendor_membership'),
            'view_item' => __('View Vendor Type', 'wcmp-vendor_membership'),
            'all_items' => __('All Vendor Type', 'wcmp-vendor_membership'),
            'search_items' => __('Search Vendor Type', 'wcmp-vendor_membership'),
            'parent_item_colon' => __('Parent Vendor Type:', 'wcmp-vendor_membership'),
            'not_found' => __('No vendor type found.', 'wcmp-vendor_membership'),
            'not_found_in_trash' => __('No vendor type found in Trash.', 'wcmp-vendor_membership')
        );

        $args = array(
            'labels' => $labels,
            'description' => __('Vendor Plans .', 'wcmp-vendor_membership'),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'membershipplan'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
            'menu_icon' => 'dashicons-groups'
        );
        if (!current_user_can('manage_options')) {
            $args['show_in_menu'] = false;
        }
        register_post_type('vendortype', $args);
        flush_rewrite_rules();
    }

    /**
     * Register Metaboxes for vendor type
     * @global type $WCMP_Vendor_Membership
     */
    public function add_vendortype_metaboxes() {
        global $WCMP_Vendor_Membership;
        $screens = array('vendortype');
        foreach ($screens as $screen) {
            /**
             * Metabox For Features List
             */
            add_meta_box(
                    'vendormeta_featurelist', __('Vendor Type Features List', 'wcmp-vendor_membership'), array($this, 'vendormeta_featurelist_callback'), $screen, 'normal', 'high'
            );
            /**
             * Metabox for Access Privilege
             */
            add_meta_box(
                    'vendormeta_access', __('Vendor Type Access Privilege', 'wcmp-vendor_membership'), array($this, 'vendormeta_access_callback'), $screen, 'normal', 'high'
            );
            /**
             * Billing Section Metabox
             */
            add_meta_box(
                    'vendormeta_billing_sec', __('Vendor Type Billing Section', 'wcmp-vendor_membership'), array($this, 'vendormeta_billing_callback'), $screen, 'normal', 'high'
            );
            /**
             * Message Section Metabox
             */
            add_meta_box(
                    'vendormeta_message_sec', __('Vendor Type Message Section', 'wcmp-vendor_membership'), array($this, 'vendormeta_message_callback'), $screen, 'normal', 'high'
            );
            /**
             * Plan Capabilities Section metabox
             */
            add_meta_box(
                    'vendormeta_capabilities_sec', __('Vendor Type Capabilities Section', 'wcmp-vendor_membership'), array($this, 'vendormeta_capabilities_callback'), $screen, 'normal', 'high'
            );
            /**
             * Vendor Limitation Metabox
             */
            add_meta_box(
                    'vendormeta_limitation_sec', __('Vendor Type Limitation Section', 'wcmp-vendor_membership'), array($this, 'vendormeta_limitation_callback'), $screen, 'normal', 'high'
            );
            /**
             * MEtabox for Visibilities Section
             */
            add_meta_box(
                    'vendormeta_visibility_sec', __('Visibilities Section', 'wcmp-vendor_membership'), array($this, 'vendormeta_visibility_callback'), $screen, 'side', 'high'
            );

            add_meta_box(
                    'vendormeta_product_cat_sec', __('Allowed Product categories', 'wcmp-vendor_membership'), array($this, 'vendormeta_product_cat_callback'), $screen, 'side', 'default'
            );
        }
    }

    /**
     * Featurelist metabox callback function
     * @global class $WCMP_Vendor_Membership
     * @global object $post
     */
    function vendormeta_featurelist_callback() {
        global $WCMP_Vendor_Membership, $post;
        $featurelists = get_post_meta($post->ID, '_vender_featurelist', true);
        ?>
        <div id="main_action_button">
            <input type="button" id="btAdd" value="<?php echo __('Add Feature', 'wcmp-vendor_membership'); ?>" class="bt" />
            <input type="button" id="btRemove" value="<?php echo __('Remove Feature', 'wcmp-vendor_membership'); ?>" class="bt" />
            <input type="button" id="btRemoveAll" value="<?php echo __('Remove All', 'wcmp-vendor_membership'); ?>" class="bt" /><br />
        </div>
        <div id="feature_list_product" class="feature_list_product" >
            <?php
            if (!empty($featurelists) && is_array($featurelists)) {
                foreach ($featurelists as $feature) {
                    echo '<input type="text" name="_vender_featurelist[]" value="' . $feature . '" class="widefat" style="margin:10px; border:1px solid #888; width:90%;" >';
                }
            }
            ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#main_action_button input[type='button']").click(function (e) {
                    var button_type = $(this).val();
                    if (button_type == '<?php echo __('Add Feature', 'wcmp-vendor_membership'); ?>') {
                        $("div#feature_list_product").append('<input type="text" name="_vender_featurelist[]" value="" class="widefat" style="margin:10px; border:1px solid #888; width:90%;" >');
                    } else if (button_type == '<?php echo __('Remove Feature', 'wcmp-vendor_membership'); ?>') {
                        $("div#feature_list_product input[type='text']").last().remove();
                    } else if (button_type == '<?php echo __('Remove All', 'wcmp-vendor_membership'); ?>') {
                        $("div#feature_list_product").empty();
                    } else {
                        alert("sorry Action not Found");
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Vendor access metabox callback function
     * @global type $WCMp
     * @global class $WCMP_Vendor_Membership
     * @global object $post
     */
    function vendormeta_access_callback() {
        global $WCMp, $WCMP_Vendor_Membership, $post;
        ?>
        <table class="form-table">
            <tbody>
                <?php $WCMp->wcmp_wp_fields->dc_generate_form_field($this->get_vendor_access_field($post->ID), array('in_table' => 1)); ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * vendor billing metabox callback function
     * @global class $WCMp
     * @global object $post
     */
    function vendormeta_billing_callback() {
        global $WCMp, $post;
        ?>
        <table class="form-table" id="vendor_billing_field_table">
            <tbody>
                <?php $WCMp->wcmp_wp_fields->dc_generate_form_field($this->get_vendor_billing_field($post->ID), array('in_table' => 1)); ?>
            </tbody>
        </table>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                var is_plan_free = $('#_is_free_plan').is(':checked');
                if (is_plan_free) {
                    $('#vendor_billing_field_table > tbody > tr').not(':first').hide();
                }
                $('#_is_free_plan').on('change', function () {
                    if ($(this).is(':checked')) {
                        $('#vendor_billing_field_table > tbody > tr').not(':first').hide();
                    } else {
                        $('#vendor_billing_field_table > tbody > tr').not(':first').show();
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Vendor message metabox callback function
     * @global class $WCMp
     * @global object $post
     */
    function vendormeta_message_callback() {
        global $WCMp, $post;
        ?>
        <table class="form-table">
            <tbody>
                <?php $WCMp->wcmp_wp_fields->dc_generate_form_field($this->get_vendor_message_field($post->ID), array('in_table' => 1)); ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * capability metabox callback function
     * @global class $WCMp
     * @global class $WCMP_Vendor_Membership
     * @global object $post
     */
    function vendormeta_capabilities_callback() {
        global $WCMp, $WCMP_Vendor_Membership, $post;
        $post_id = $post->ID;
        $capabilitiesdata_gen = get_post_meta($post_id, '_vendor_capabilities_field_gen', true);
        $wcmp_upload_product_data = $this->get_wcmp_capabilities_upload_product_data();
        $wcmp_order_report_export_data = $this->get_wcmp_capabilities_order_report_export_data();
        $wcmp_order_email_settings = $this->get_wcmp_capabilities_order_email_settings();
        $wcmp_miscellaneous = $this->get_wcmp_capabilities_miscellaneous();
        $wcmp_messages_support = $this->get_wcmp_capabilities_messages_support();
        $wcmp_policies_settings = $this->get_wcmp_capabilities_policies_settings();
        $wcmp_others = $this->get_wcmp_capabilities_others();
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('.check_all').click(function (e) {
                    var myid = this.value;
                    if (this.checked) {
                        $('#' + myid).find(':checkbox').each(function () {
                            $(this).attr('checked', true);
                        });
                    } else {
                        $('#' + myid).find(':checkbox').each(function () {
                            $(this).attr('checked', false);
                        });
                    }
                });
            });
        </script>
        <table class="form-table">
            <tbody>
                <tr>
                    <td><strong><?php echo __('Uploading Product Data ', $WCMp->text_domain) ?></strong></td>
                </tr>
                <tr>
                    <td id="cap_gen_id">
                        <p><input type="checkbox" name="chk_gen_id" value="cap_gen_id" class="check_all" /> <span style="font-weight:bold;"> <?php echo __('Check/Uncheck All', 'wcmp-vendor_membership'); ?></span> </p><br/>
                        <?php foreach ($wcmp_upload_product_data as $key => $value) { ?>

                            <span style="float:left;"></span><span><input type="checkbox" name="_vendor_capabilities_field_gen[]" value="<?php echo $key; ?>" <?php
                                if (is_array($capabilitiesdata_gen)) {
                                    if (in_array($key, $capabilitiesdata_gen)) {
                                        ?> checked="checked" <?php
                                                                              }
                                                                          }
                                                                          ?> ></span> <label title="<?php echo $value; ?>"><?php echo $value ?> </label><br>


                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td><strong><?php echo __('Order Export Data / Report Export Data', $WCMp->text_domain) ?></strong></td>
                </tr>
                <tr>
                    <td id="cap_spe_id">
                        <p><input type="checkbox" name="chk_spe_id" value="cap_spe_id" class="check_all" /> <span style="font-weight:bold;"> <?php echo __('Check/Uncheck All', 'wcmp-vendor_membership'); ?></span> </p><br/>
                        <?php foreach ($wcmp_order_report_export_data as $key => $value) { ?>

                            <span style="float:left;"></span><span><input type="checkbox" name="_vendor_capabilities_field_gen[]" value="<?php echo $key; ?>" <?php
                                if (is_array($capabilitiesdata_gen)) {
                                    if (in_array($key, $capabilitiesdata_gen)) {
                                        ?> checked="checked" <?php
                                                                              }
                                                                          }
                                                                          ?> ></span> <label title="<?php echo $value; ?>"><?php echo $value ?> </label><br>


                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td><strong><?php echo __('Order Email Settings for Vendor', $WCMp->text_domain) ?></strong></td>
                </tr>
                <tr>
                    <td id="cap_other_id">
                        <p><input type="checkbox" name="chk_other_id" value="cap_other_id" class="check_all" /> <span style="font-weight:bold;"> <?php echo __('Check/Uncheck All', 'wcmp-vendor_membership'); ?></span> </p><br/>
                        <?php foreach ($wcmp_order_email_settings as $key => $value) { ?>

                            <span style="float:left;"></span><span><input type="checkbox" name="_vendor_capabilities_field_gen[]" value="<?php echo $key; ?>" <?php
                                if (is_array($capabilitiesdata_gen)) {
                                    if (in_array($key, $capabilitiesdata_gen)) {
                                        ?> checked="checked" <?php
                                                                              }
                                                                          }
                                                                          ?> ></span> <label title="<?php echo $value; ?>"><?php echo $value ?> </label><br>


                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td><strong><?php echo __('Miscellaneous', $WCMp->text_domain) ?></strong></td>
                </tr>
                <tr>
                    <td id="cap_other_admin_id">
                        <p><input type="checkbox" name="chk_other_admin_id" value="cap_other_admin_id" class="check_all" /> <span style="font-weight:bold;"> <?php echo __('Check/Uncheck All', 'wcmp-vendor_membership'); ?></span> </p><br/>
                        <?php foreach ($wcmp_miscellaneous as $key => $value) { ?>

                            <span style="float:left;"></span><span><input type="checkbox" name="_vendor_capabilities_field_gen[]" value="<?php echo $key; ?>" <?php
                                if (is_array($capabilitiesdata_gen)) {
                                    if (in_array($key, $capabilitiesdata_gen)) {
                                        ?> checked="checked" <?php
                                                                              }
                                                                          }
                                                                          ?> ></span> <label title="<?php echo $value; ?>"><?php echo $value ?> </label><br>


                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td><strong><?php echo __('Messages & Customer Support Settings ', 'wcmp-vendor_membership') ?></strong></td>
                </tr>
                <tr>
                    <td id="chk_m_s_id">
                        <p><input type="checkbox" name="chk_m_s_id" value="chk_m_s_id" class="check_all" /> <span style="font-weight:bold;"> <?php echo __('Check/Uncheck All', 'wcmp-vendor_membership'); ?></span> </p><br/>
                        <?php foreach ($wcmp_messages_support as $key => $value) { ?>

                            <span style="float:left;"></span><span><input type="checkbox" name="_vendor_capabilities_field_gen[]" value="<?php echo $key; ?>" <?php
                                if (is_array($capabilitiesdata_gen)) {
                                    if (in_array($key, $capabilitiesdata_gen)) {
                                        ?> checked="checked" <?php
                                                                              }
                                                                          }
                                                                          ?> ></span> <label title="<?php echo $value; ?>"><?php echo $value ?> </label><br>


                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td><strong><?php echo __('Policies Settings ', $WCMp->text_domain) ?></strong></td>
                </tr>
                <tr>
                    <td id="chk_p_s_id">
                        <p><input type="checkbox" name="chk_p_s_id" value="chk_p_s_id" class="check_all" /> <span style="font-weight:bold;"> <?php echo __('Check/Uncheck All', 'wcmp-vendor_membership'); ?></span> </p><br/>
                        <?php foreach ($wcmp_policies_settings as $key => $value) { ?>

                            <span style="float:left;"></span><span><input type="checkbox" name="_vendor_capabilities_field_gen[]" value="<?php echo $key; ?>" <?php
                                if (is_array($capabilitiesdata_gen)) {
                                    if (in_array($key, $capabilitiesdata_gen)) {
                                        ?> checked="checked" <?php
                                                                              }
                                                                          }
                                                                          ?> ></span> <label title="<?php echo $value; ?>"><?php echo $value ?> </label><br>


                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <td><strong><?php echo __('Other Special Settings ', $WCMp->text_domain) ?></strong></td>
                </tr>
                <tr>
                    <td id="chk_o_s_s_id">
                        <p><input type="checkbox" name="chk_o_s_s_id" value="chk_o_s_s_id" class="check_all" /> <span style="font-weight:bold;"> <?php echo __('Check/Uncheck All', 'wcmp-vendor_membership'); ?></span> </p><br/>
                        <?php foreach ($wcmp_others as $key => $value) { ?>

                            <span style="float:left;"></span><span><input type="checkbox" name="_vendor_capabilities_field_gen[]" value="<?php echo $key; ?>" <?php
                                if (is_array($capabilitiesdata_gen)) {
                                    if (in_array($key, $capabilitiesdata_gen)) {
                                        ?> checked="checked" <?php
                                                                              }
                                                                          }
                                                                          ?> ></span> <label title="<?php echo $value; ?>"><?php echo $value ?> </label><br>


                        <?php } ?>
                    </td>
                </tr>
                <?php do_action('after_wcmp_vendor_membership_capabilities'); ?>

            </tbody>
        </table>
        <?php
    }

    /**
     * vendor limitation metabox callback function
     * @global class $WCMp
     * @global object $post
     */
    function vendormeta_limitation_callback() {
        global $WCMp, $post;
        ?>
        <table class="form-table">
            <tbody>

                <?php $WCMp->wcmp_wp_fields->dc_generate_form_field($this->get_vendor_limitation_field($post->ID), array('in_table' => 1)); ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Vendor visibility metabox callback function
     * @global class $WCMp
     * @global object $post
     */
    function vendormeta_visibility_callback() {
        global $WCMp, $post;
        $WCMp->wcmp_wp_fields->dc_generate_form_field($this->get_vendor_visibility_field($post->ID), array());
    }

    /**
     * limit product cat
     * @global object $WCMp
     * @global object $post
     */
    function vendormeta_product_cat_callback() {
        global $WCMp, $post;
        $tax_name = 'product_cat';
        $selected_cats = get_post_meta($post->ID,'_allowed_product_cats',true) ? get_post_meta($post->ID,'_allowed_product_cats',true) : array();
        ?>
        <div class="productcategorydiv">
            <ul id="<?php echo $tax_name; ?>checklist" class="categorychecklist form-no-clear">
                <label class="selectit"><input type="checkbox" id="allow_all_product_cat" <?php if(empty($selected_cats)){ echo 'checked=""'; } ?> /> All</label>
                <?php wp_terms_checklist($post->ID, array('taxonomy' => $tax_name, 'selected_cats' => $selected_cats)); ?>
            </ul>
        </div>
        <?php
    }

    /**
     * save vendor type meta data
     * @param int $post_id
     * @param object $post
     */
    public function save_vendor_type_metadata($post_id, $post) {
        if (isset($_POST['_vender_featurelist'])) {
            update_post_meta($post_id, '_vender_featurelist', $_POST['_vender_featurelist']);
        } else {
            update_post_meta($post_id, '_vender_featurelist', NULL);
        }
        if (isset($_POST['_vender_access'])) {
            update_post_meta($post_id, '_vender_access', $_POST['_vender_access']);
        } else {
            update_post_meta($post_id, '_vender_access', 'disable');
        }
        if (isset($_POST['_vendor_commission'])) {
            update_post_meta($post_id, '_vendor_commission', $_POST['_vendor_commission']);
        }
        if (isset($_POST['_is_free_plan'])) {
            update_post_meta($post_id, '_is_free_plan', $_POST['_is_free_plan']);
        } else {
            update_post_meta($post_id, '_is_free_plan', '');
        }

        if (isset($_POST['_initial_payment'])) {
            $_vendor_billing_field_arr['_initial_payment'] = $_POST['_initial_payment'];
        }
        if (isset($_POST['_is_recurring'])) {
            $_vendor_billing_field_arr['_is_recurring'] = $_POST['_is_recurring'];
        }
        if (isset($_POST['_vendor_billing_amt'])) {
            $_vendor_billing_field_arr['_vendor_billing_amt'] = $_POST['_vendor_billing_amt'];
        }
        if (isset($_POST['_vendor_billing_amt_cycle'])) {
            $_vendor_billing_field_arr['_vendor_billing_amt_cycle'] = $_POST['_vendor_billing_amt_cycle'];
        }
        if (isset($_POST['_vendor_billing_amt_cycle_limit'])) {
            $_vendor_billing_field_arr['_vendor_billing_amt_cycle_limit'] = $_POST['_vendor_billing_amt_cycle_limit'];
        }
        if (isset($_POST['_vendor_grace_period_days'])) {
            $_vendor_billing_field_arr['_vendor_grace_period_days'] = $_POST['_vendor_grace_period_days'];
        }
        if (isset($_POST['_status_after_grace'])) {
            $_vendor_billing_field_arr['_status_after_grace'] = $_POST['_status_after_grace'];
        }
        if (isset($_POST['_subscribe_button_text'])) {
            $_vendor_billing_field_arr['_subscribe_button_text'] = $_POST['_subscribe_button_text'];
        }
        if (isset($_POST['_subscribe_button_text_upgrade'])) {
            $_vendor_billing_field_arr['_subscribe_button_text_upgrade'] = $_POST['_subscribe_button_text_upgrade'];
        }
        if (isset($_POST['_subscribe_button_text_logged_in'])) {
            $_vendor_billing_field_arr['_subscribe_button_text_logged_in'] = $_POST['_subscribe_button_text_logged_in'];
        }
        if (isset($_POST['_plan_short_desc'])) {
            $_vendor_billing_field_arr['_plan_short_desc'] = $_POST['_plan_short_desc'];
        }
        if (isset($_POST['_max_attamped'])) {
            $_vendor_billing_field_arr['_max_attamped'] = $_POST['_max_attamped'];
        }
        if (isset($_POST['_is_trial'])) {
            $_vendor_billing_field_arr['_is_trial'] = $_POST['_is_trial'];
        }
        if (isset($_POST['_trial_amt'])) {
            $_vendor_billing_field_arr['_trial_amt'] = $_POST['_trial_amt'];
        }
        if (isset($_POST['_trial_amt_cycle'])) {
            $_vendor_billing_field_arr['_trial_amt_cycle'] = $_POST['_trial_amt_cycle'];
        }
        if (isset($_POST['_trial_amt_cycle_limit'])) {
            $_vendor_billing_field_arr['_trial_amt_cycle_limit'] = $_POST['_trial_amt_cycle_limit'];
        }
        if (isset($_POST['_vendor_billing_tax_amt'])) {
            $_vendor_billing_field_arr['_vendor_billing_tax_amt'] = $_POST['_vendor_billing_tax_amt'];
        }
        if (isset($_POST['_hide_product_after_grace'])) {
            $_vendor_billing_field_arr['_hide_product_after_grace'] = $_POST['_hide_product_after_grace'];
        }
        if (isset($_vendor_billing_field_arr) && (!empty($_vendor_billing_field_arr))) {
            update_post_meta($post_id, '_vendor_billing_field', $_vendor_billing_field_arr);
        }

        if (isset($_POST['_success_msg'])) {
            $_vendor_message_field_arr['_success_msg'] = $_POST['_success_msg'];
        }
        if (isset($_POST['_failuare_msg'])) {
            $_vendor_message_field_arr['_failuare_msg'] = $_POST['_failuare_msg'];
        }
        if (isset($_POST['_payment_due_msg'])) {
            $_vendor_message_field_arr['_payment_due_msg'] = $_POST['_payment_due_msg'];
        }
        if (isset($_POST['_upcoming_renew_reminder_msg'])) {
            $_vendor_message_field_arr['_upcoming_renew_reminder_msg'] = $_POST['_upcoming_renew_reminder_msg'];
        }
        if (isset($_vendor_message_field_arr) && (!empty($_vendor_message_field_arr))) {
            update_post_meta($post_id, '_vendor_message_field', $_vendor_message_field_arr);
        }
        $_vendor_capabilities_field_gen = isset($_POST['_vendor_capabilities_field_gen']) ? $_POST['_vendor_capabilities_field_gen'] : array();
        update_post_meta($post_id, '_vendor_capabilities_field_gen', $_vendor_capabilities_field_gen);
        if (isset($_POST['is_product_category_limitation'])) {
            $_vendor_limitation_field['is_product_category_limitation'] = $_POST['is_product_category_limitation'];
        }
        if (isset($_POST['_product_category_limit'])) {
            $_vendor_limitation_field['_product_category_limit'] = $_POST['_product_category_limit'];
        }
        if (isset($_POST['is_product_limitation'])) {
            $_vendor_limitation_field['is_product_limitation'] = $_POST['is_product_limitation'];
        }
        if (isset($_POST['_product_limit'])) {
            $_vendor_limitation_field['_product_limit'] = $_POST['_product_limit'];
        }
        if (isset($_vendor_limitation_field) && (!empty($_vendor_limitation_field))) {
            update_post_meta($post_id, '_vendor_limitation_field', $_vendor_limitation_field);
        }
        if (isset($_POST['is_visible_in_list'])) {
            update_post_meta($post_id, 'is_visible_in_list', $_POST['is_visible_in_list']);
        } else {
            update_post_meta($post_id, 'is_visible_in_list', '');
        }
        if (isset($_POST['mark_as_recommended'])) {
            update_post_meta($post_id, 'mark_as_recommended', $_POST['mark_as_recommended']);
        } else {
            update_post_meta($post_id, 'mark_as_recommended', '');
        }
        
        if(isset($_POST['tax_input'])){
            $allowed_product_cats = $_POST['tax_input']['product_cat'];
            update_post_meta($post_id, '_allowed_product_cats', $allowed_product_cats);
        } else{
            delete_post_meta($post_id, '_allowed_product_cats');
        }
    }

    /**
     * Fields for Vendor access metabox 
     * @global type $WCMP_Vendor_Membership
     * @param type $post_id
     * @return type
     */
    public function get_vendor_access_field($post_id) {
        global $WCMP_Vendor_Membership;
        $accesslists = get_post_meta($post_id, '_vender_access', 'Enable');
        $vendor_commission = get_post_meta($post_id, '_vendor_commission', true);
        $fields = apply_filters('dc_vendor_fields_cat_access', array(
            "_vender_access" => array(
                'label' => __('No admin approval required', 'wcmp-vendor_membership'),
                'type' => 'checkbox',
                'desc' => __('Direct Approval of Membership no need to approval of admin of this type of Vendor Type.', 'wcmp-vendor_membership'),
                'dfvalue' => $accesslists,
                'value' => 'Enable',
                'class' => 'user-profile-fields'
            ),
            '_vendor_commission' => array(
                'label' => __('Vendor commission', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Set global commission value for this membership type', 'wcmp-vendor_membership'),
                'value' => $vendor_commission,
                'class' => 'user-profile-fields'
            )
        ));
        return $fields;
    }

    /**
     * Fields for vendor billing
     * @global class $WCMP_Vendor_Membership
     * @global object $wp_roles
     * @param int $post_id
     * @return array
     */
    public function get_vendor_billing_field($post_id) {
        global $WCMP_Vendor_Membership, $wp_roles;
        $billingdata = get_post_meta($post_id, '_vendor_billing_field', true);
        $is_free_plan = get_post_meta($post_id, '_is_free_plan', true);
        $all_roles = $wp_roles->roles;
        $all_role = array('' => __('Please Select Role', 'wcmp-vendor_membership'));
        foreach ($all_roles as $key => $value) {
            $all_role[$key] = $value['name'];
        }
        $hideproduct = isset($billingdata['_hide_product_after_grace']) ? $billingdata['_hide_product_after_grace'] : '';
        $fields = apply_filters('dc_vendor_fields_cat_billing', array(
            '_is_free_plan' => array(
                'label' => __('Free plan', 'wcmp-vendor_membership'),
                'type' => 'checkbox',
                'desc' => __('Mark this subscription as free.', 'wcmp-vendor_membership'),
                'dfvalue' => $is_free_plan,
                'value' => 'Enable',
                'class' => 'user-profile-fields'
            ),
            "_initial_payment" => array(
                'label' => __('Initial payment (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter the Amount which will be charged at the time of subscribe/Registration.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_initial_payment']) ? $billingdata['_initial_payment'] : '',
                'class' => 'user-profile-fields'
            ),
            "_plan_short_desc" => array(
                'label' => __('Plan short description (required)', 'wcmp-vendor_membership'),
                'type' => 'textarea',
                'desc' => __('Enter the Plan Short Description for Billing Profile.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_plan_short_desc']) ? $billingdata['_plan_short_desc'] : '',
                'class' => 'user-profile-fields'
            ),
            "_is_trial" => array(
                'label' => __('Is trial', 'wcmp-vendor_membership'),
                'type' => 'select',
                'desc' => __('Select yes if you want to give this plan as trial for some days.', 'wcmp-vendor_membership'),
                'options' => array('' => __('Please Select', 'wcmp-vendor_membership'), 'no' => __('NO', 'wcmp-vendor_membership'), 'yes' => __('Yes', 'wcmp-vendor_membership')),
                'value' => isset($billingdata['_is_trial']) ? $billingdata['_is_trial'] : '',
                'class' => 'user-profile-fields'
            ),
            "_trial_amt" => array(
                'label' => __('Trial amount (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter Trial Amount.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_trial_amt']) ? $billingdata['_trial_amt'] : '',
                'class' => 'user-profile-fields'
            ),
            "_trial_amt_cycle" => array(
                'label' => __('Trial cycle duration', 'wcmp-vendor_membership'),
                'type' => 'select',
                'desc' => __('Select yes if this type of vendorship have recurring profile/payment.', 'wcmp-vendor_membership'),
                'options' => array('' => __('Select Duration', 'wcmp-vendor_membership'), 'Day' => 'Daily', 'Week' => __('Weekly', 'wcmp-vendor_membership'), 'SemiMonth' => __('SemiMonth', 'wcmp-vendor_membership'), 'Month' => __('Monthly', 'wcmp-vendor_membership'), 'Year' => __('Yearly', 'wcmp-vendor_membership')),
                'value' => isset($billingdata['_trial_amt_cycle']) ? $billingdata['_trial_amt_cycle'] : '',
                'class' => 'user-profile-fields'
            ),
            "_trial_amt_cycle_limit" => array(
                'label' => __('Trial cycle limit (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter the cycle limit never put 0 .', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_trial_amt_cycle_limit']) ? $billingdata['_trial_amt_cycle_limit'] : '',
                'class' => 'user-profile-fields'
            ),
            "_is_recurring" => array(
                'label' => __('Is recurring', 'wcmp-vendor_membership'),
                'type' => 'select',
                'desc' => __('Select yes if this type of vendorship have recurring profile/payment.', 'wcmp-vendor_membership'),
                'options' => array('' => __('Please Select', 'wcmp-vendor_membership'), 'no' => __('NO', 'wcmp-vendor_membership'), 'yes' => __('Yes', 'wcmp-vendor_membership')),
                'value' => isset($billingdata['_is_recurring']) ? $billingdata['_is_recurring'] : '',
                'class' => 'user-profile-fields'
            ),
            "_vendor_billing_amt" => array(
                'label' => __('Recurring payment amount (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter the Amount which will be charged recurring for subscription.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_vendor_billing_amt']) ? $billingdata['_vendor_billing_amt'] : '',
                'class' => 'user-profile-fields'
            ),
            "_vendor_billing_tax_amt" => array(
                'label' => __('TAX amount (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter the Amount which will be charged recurring as tax for subscription.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_vendor_billing_tax_amt']) ? $billingdata['_vendor_billing_tax_amt'] : '',
                'class' => 'user-profile-fields'
            ),
            "_vendor_billing_amt_cycle" => array(
                'label' => __('Recurring payment duration.', 'wcmp-vendor_membership'),
                'type' => 'select',
                'desc' => __('Select yes if this type of vendorship have recurring profile/payment', 'wcmp-vendor_membership'),
                'options' => array('' => __('Select Duration', 'wcmp-vendor_membership'), 'Day' => 'Daily', 'Week' => __('Weekly', 'wcmp-vendor_membership'), 'SemiMonth' => __('SemiMonthly', 'wcmp-vendor_membership'), 'Month' => __('Monthly', 'wcmp-vendor_membership'), 'Year' => __('Yearly', 'wcmp-vendor_membership')),
                'value' => isset($billingdata['_vendor_billing_amt_cycle']) ? $billingdata['_vendor_billing_amt_cycle'] : '',
                'class' => 'user-profile-fields'
            ),
            "_vendor_billing_amt_cycle_limit" => array(
                'label' => __('Billing cycle limit (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter the billing cycle limit enter for 0 for indefinite until cancellation.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_vendor_billing_amt_cycle_limit']) ? $billingdata['_vendor_billing_amt_cycle_limit'] : '',
                'class' => 'user-profile-fields'
            ),
            "_max_attamped" => array(
                'label' => __('Maximux attempt (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter maximux attempt payment fail', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_max_attamped']) ? $billingdata['_max_attamped'] : '',
                'class' => 'user-profile-fields'
            ),
            "_subscribe_button_text" => array(
                'label' => __('Subscribe button text', 'wcmp-vendor_membership'),
                'type' => 'text',
                'placeholder' => 'Subscribe Now',
                'desc' => __('Enter subscribe button text.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_subscribe_button_text']) ? $billingdata['_subscribe_button_text'] : 'Subscribe Now',
                'class' => 'user-profile-fields'
            ),
            "_subscribe_button_text_logged_in" => array(
                'label' => __('Upgrade button text for existing member/customers who want to sell his/her products', 'wcmp-vendor_membership'),
                'type' => 'text',
                'placeholder' => 'Become A Vendor',
                'desc' => __('Enter subscribe button text.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_subscribe_button_text_logged_in']) ? $billingdata['_subscribe_button_text_logged_in'] : 'Become A Vendor',
                'class' => 'user-profile-fields'
            ),
            "_subscribe_button_text_upgrade" => array(
                'label' => __('Upgrade button text for existing vendor', 'wcmp-vendor_membership'),
                'type' => 'text',
                'placeholder' => 'Upgrade Now',
                'desc' => __('Enter subscribe button text.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_subscribe_button_text_upgrade']) ? $billingdata['_subscribe_button_text_upgrade'] : 'Upgrade Now',
                'class' => 'user-profile-fields'
            ),
            "_vendor_grace_period_days" => array(
                'label' => __('Grace period in days (only number)', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter grace period in days when subscription when payment due for subscription.', 'wcmp-vendor_membership'),
                'value' => isset($billingdata['_vendor_grace_period_days']) ? $billingdata['_vendor_grace_period_days'] : '',
                'class' => 'user-profile-fields'
            ),
            "_status_after_grace" => array(
                'label' => __('Status after grace', 'wcmp-vendor_membership'),
                'type' => 'select',
                'desc' => __('Select the status when vendor completed his/her grace period', 'wcmp-vendor_membership'),
                'options' => $all_role,
                'value' => isset($billingdata['_status_after_grace']) ? $billingdata['_status_after_grace'] : '',
                'class' => 'user-profile-fields'
            ),
            '_hide_product_after_grace' => array(
                'label' => __('Hide product after grace', 'wcmp-vendor_membership'),
                'type' => 'checkbox',
                'desc' => __('Hide vendor product from shop after grace period', 'wcmp-vendor_membership'),
                'dfvalue' => $hideproduct,
                'value' => 'Enable',
                'class' => 'user-profile-fields'
            )
        ));
        return $fields;
    }

    /**
     * Fields For vendor message metabox
     * @global class $WCMP_Vendor_Membership
     * @param int $post_id
     * @return array
     */
    function get_vendor_message_field($post_id) {
        global $WCMP_Vendor_Membership;
        $messagedata = get_post_meta($post_id, '_vendor_message_field', true);

        $fields = apply_filters('dc_vendor_fields_cat_message', array(
            "_success_msg" => array(
                'label' => __('Success message', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter success message will comes after the successful registration.', 'wcmp-vendor_membership'),
                'value' => isset($messagedata['_success_msg']) ? $messagedata['_success_msg'] : '',
                'class' => 'widefat'
            ),
            "_failuare_msg" => array(
                'label' => __('Error message', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter unsuccess message will comes after the unsuccessful registration.', 'wcmp-vendor_membership'),
                'value' => isset($messagedata['_failuare_msg']) ? $messagedata['_failuare_msg'] : '',
                'class' => 'widefat'
            ),
            "_payment_due_msg" => array(
                'label' => __('Payment due message', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter  message will comes any payment due.', 'wcmp-vendor_membership'),
                'value' => isset($messagedata['_payment_due_msg']) ? $messagedata['_payment_due_msg'] : '',
                'class' => 'widefat'
            ),
            "_upcoming_renew_reminder_msg" => array(
                'label' => __('Upcoming renew message', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter reminder message for upcoming renew reminder.', 'wcmp-vendor_membership'),
                'value' => isset($messagedata['_upcoming_renew_reminder_msg']) ? $messagedata['_upcoming_renew_reminder_msg'] : '',
                'class' => 'widefat'
            )
        ));
        return $fields;
    }

    /**
     * Fields for vendor limitation
     * @global class $WCMP_Vendor_Membership
     * @param int $post_id
     * @return array
     */
    function get_vendor_limitation_field($post_id) {
        global $WCMP_Vendor_Membership;
        $limitationdata = get_post_meta($post_id, '_vendor_limitation_field', true);
        $fields = apply_filters('dc_vendor_fields_cat_limitation', array(
            "is_product_category_limitation" => array(
                'label' => __('Is product category limitation enable', 'wcmp-vendor_membership'),
                'type' => 'checkbox',
                'desc' => __('Check the checkbox if you want to product category uses limitation for this type of vendor.', 'wcmp-vendor_membership'),
                'dfvalue' => isset($limitationdata['is_product_category_limitation']) ? $limitationdata['is_product_category_limitation'] : '',
                'value' => 'Enable',
                'class' => 'user-profile-fields'
            ),
            "_product_category_limit" => array(
                'label' => __('Product category limit', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter number of category can use this type of vendor.', 'wcmp-vendor_membership'),
                'value' => isset($limitationdata['_product_category_limit']) ? $limitationdata['_product_category_limit'] : '',
                'class' => 'user-profile-fields'
            ),
            "is_product_limitation" => array(
                'label' => __('Is Product limitation enable', 'wcmp-vendor_membership'),
                'type' => 'checkbox',
                'desc' => __('Check the checkbox if you want to product upload limitation for this type of vendor.', 'wcmp-vendor_membership'),
                'dfvalue' => isset($limitationdata['is_product_limitation']) ? $limitationdata['is_product_limitation'] : '',
                'value' => 'Enable',
                'class' => 'user-profile-fields'
            ),
            "_product_limit" => array(
                'label' => __('Product upload limit', 'wcmp-vendor_membership'),
                'type' => 'text',
                'desc' => __('Enter number of product can upload this type of vendor.', 'wcmp-vendor_membership'),
                'value' => isset($limitationdata['_product_limit']) ? $limitationdata['_product_limit'] : '',
                'class' => 'user-profile-fields'
            )
        ));
        return $fields;
    }

    /**
     * Fields for visibility section metabox
     * @global class $WCMP_Vendor_Membership
     * @param int $post_id
     * @return array
     */
    public function get_vendor_visibility_field($post_id) {
        global $WCMP_Vendor_Membership;
        $visiblity = get_post_meta($post_id, 'is_visible_in_list', true);
        $mark_as_recommended = get_post_meta($post_id, 'mark_as_recommended', true);
        $fields = apply_filters('dc_vendor_fields_visibilities_access', array(
            "is_visible_in_list" => array(
                'label' => __('Show in the membership list', 'wcmp-vendor_membership'),
                'type' => 'checkbox',
                'desc' => __('Check this if you want to show this vendor type in your membership list.', 'wcmp-vendor_membership'),
                'dfvalue' => $visiblity,
                'value' => 'Enable',
                'class' => 'user-profile-fields'
            ),
            'mark_as_recommended' => array(
                'label' => __('Mark as recommended', 'wcmp-vendor_membership'),
                'type' => 'checkbox',
                'desc' => __('', 'wcmp-vendor_membership'),
                'value' => 'Enable',
                'dfvalue' => $mark_as_recommended,
                'class' => 'user-profile-fields'
            )
        ));
        return $fields;
    }

    /**
     * Upload products capability
     * @global class $WCMP_Vendor_Membership
     * @return array
     */
    public function get_wcmp_capabilities_upload_product_data() {
        global $WCMP_Vendor_Membership;
        $cap_list = apply_filters('wcmp_capabilities_upload_product_data', array(
            'is_submit_product' => __('Submit products', 'wcmp-vendor_membership'),
            'is_published_product' => __('Publish products', 'wcmp-vendor_membership'),
            'is_edit_delete_published_product' => __('Edit published products', 'wcmp-vendor_membership'),
            'is_upload_files' => __('Upload media files', 'wcmp-vendor_membership'),
            'is_submit_coupon' => __('Submit coupons', 'wcmp-vendor_membership'),
            'is_published_coupon' => __('Publish coupons', 'wcmp-vendor_membership'),
            'is_edit_delete_published_coupon' => __('Edit publish coupons', 'wcmp-vendor_membership')
        ));
        return $cap_list;
    }

    /**
     * Import export capability
     * @global class $WCMP_Vendor_Membership
     * @return array
     */
    public function get_wcmp_capabilities_order_report_export_data() {
        global $WCMP_Vendor_Membership;
        $cap_list = apply_filters('wcmp_capabilities_order_report_export_data', array(
            'is_order_csv_export' => __('Allow vendors to export orders.', 'wcmp-vendor_membership'),
            'is_order_show_email' => __('Customer name', 'wcmp-vendor_membership'),
            'show_customer_dtl' => __('E-mail and phone number', 'wcmp-vendor_membership'),
            'show_customer_billing' => __('Billing address', 'wcmp-vendor_membership'),
            'show_customer_shipping' => __('Shipping address', 'wcmp-vendor_membership')
        ));
        return $cap_list;
    }

    /**
     * Email capability
     * @global class $WCMP_Vendor_Membership
     * @return array
     */
    public function get_wcmp_capabilities_order_email_settings() {
        global $WCMP_Vendor_Membership;
        $cap_list = apply_filters('wcmp_capabilities_order_email_settings', array(
            'show_cust_name' => __('Name, phone no. and email', 'wcmp-vendor_membership'),
            'show_cust_billing_add' => __('Billing address', 'wcmp-vendor_membership'),
            'show_cust_shipping_add' => __('Shipping address', 'wcmp-vendor_membership'),
            'show_cust_order_calulations' => __('Order calculations', 'wcmp-vendor_membership')
        ));
        return $cap_list;
    }

    /**
     * Miscellaneous capability
     * @global class $WCMP_Vendor_Membership
     * @return array
     */
    public function get_wcmp_capabilities_miscellaneous() {
        global $WCMP_Vendor_Membership;
        $cap_list = apply_filters('wcmp_capabilities_miscellaneous', array(
            'is_vendor_view_comment' => __('View comment', 'wcmp-vendor_membership'),
            'is_vendor_submit_comment' => __('Submit comment', 'wcmp-vendor_membership'),
            'is_vendor_add_external_url' => __('Enable store url', 'wcmp-vendor_membership'),
            'is_hide_option_show' => __('Enable hide option for vendor', 'wcmp-vendor_membership')
        ));
        return $cap_list;
    }

    /**
     * Message support capability
     * @global class $WCMP_Vendor_Membership
     * @return array
     */
    public function get_wcmp_capabilities_messages_support() {
        global $WCMP_Vendor_Membership;
        $cap_list = apply_filters('wcmp_capabilities_messages', array(
            'can_vendor_add_message_on_email_and_thankyou_page' => __('Message to buyer', 'wcmp-vendor_membership'),
            'can_vendor_add_customer_support_details' => __('Vendor shop support', 'wcmp-vendor_membership')
        ));
        return $cap_list;
    }

    /**
     * Vendor Policies capability
     * @global class $WCMP_Vendor_Membership
     * @return array
     */
    public function get_wcmp_capabilities_policies_settings() {
        global $WCMP_Vendor_Membership;
        $cap_list = apply_filters('wcmp_capabilities_policies_settings', array(
            'can_vendor_edit_policy_tab_label' => __('Can vendor edit policy tab title', 'wcmp-vendor_membership'),
            'can_vendor_edit_cancellation_policy' => __('Can vendor edit cancellation/return/exchange policy', 'wcmp-vendor_membership'),
            'can_vendor_edit_refund_policy' => __('Can vendor edit refund policy', 'wcmp-vendor_membership'),
            'can_vendor_edit_shipping_policy' => __('Can vendor edit shipping policy', 'wcmp-vendor_membership')
        ));
        return $cap_list;
    }

    /**
     * WCMp Other capability
     * @global class $WCMP_Vendor_Membership
     * @return array
     */
    public function get_wcmp_capabilities_others() {
        global $WCMP_Vendor_Membership;
        $cap_list = apply_filters('wcmp_capabilities_others', array(
            'view_order' => __('View order', 'wcmp-vendor_membership'),
            'manage_shipping' => __('Manage shipping', 'wcmp-vendor_membership')
        ));
        return $cap_list;
    }

}
