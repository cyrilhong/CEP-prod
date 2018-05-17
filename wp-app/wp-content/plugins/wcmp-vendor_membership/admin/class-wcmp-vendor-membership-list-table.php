<?php
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WCMp_Vendor_Membership_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct(array(
            'singular' => 'membership',
            'plural' => 'memberships',
            'ajax' => false
        ));
    }

    /**
     * Add all the Subscription field columns to the table.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     * @since 1.0
     */
    public function get_columns() {
        global $WCMP_Vendor_Membership;
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'status' => __('Status', 'wcmp-vendor_membership'),
            'title' => __('Membership', 'wcmp-vendor_membership'),
            'user' => __('User', 'wcmp-vendor_membership'),
            'start_date' => __('Start Date', 'wcmp-vendor_membership'),
            'trial_expiry_date' => __('Trial Period', 'wcmp-vendor_membership'),
            'vendor_billing_amt' => __('Billing Amount', 'wcmp-vendor_membership'),
            'billing_period' => __('Billing Period', 'wcmp-vendor_membership'),
            'next_payment_date' => __('Next Payment', 'wcmp-vendor_membership'),
        );

        return $columns;
    }

    /**
     * Make the table sortable by all columns and set the default sort field to be start_date.
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs' => array( 'data_values', bool )
     * @since 1.0
     */
    public function get_sortable_columns() {

        $sortable_columns = array(
            'title' => array('_order_item_name', false),
            'user' => array('user_display_name', false),
            'vendor_billing_amt' => array('vendor_billing_amt', false)
        );

        return $sortable_columns;
    }

    /**
     * Get, sort and filter subscriptions for display.
     *
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     * @since 1.0
     */
    function prepare_items() {
        global $WCMP_Vendor_Membership;
        $screen = get_current_screen();
        $per_page = $this->get_items_per_page($screen->get_option('per_page', 'option'), 10);
        $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
        $this->get_column_info();
        $status_to_show = ( isset($_GET['status']) ) ? $_GET['status'] : 'all';
        $query_args = array();
        if (isset($_GET['_Plan_id']) && !empty($_GET['_Plan_id'])) {
            $query_args = array(
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'vendor_group_id',
                        'value' => $_GET['_Plan_id'],
                        'compare' => '='
                    )
                )
            );
        }
        $this->items = $WCMP_Vendor_Membership->get_memberships($query_args);
        $total_items = count($this->items);
        $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page' => $per_page,
                    'total_pages' => ceil($total_items / $per_page)
                )
        );
    }

    function column_default($item, $column_name) {
        $column_content = '';
        switch ($column_name) {
            case 'status':
                $action_url = add_query_arg(
                        array(
                            'page' => $_REQUEST['page'],
                            'user' => $item['user_id'],
                            '_wpnonce' => wp_create_nonce('membership_key')
                        )
                );
                $actions = array();
                if (in_array('dc_pending_vendor', $item['role'])) {
                    $actions['status_approve'] = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg('action', 'approve', $action_url)), 'Approve');
                }
                if (in_array('dc_vendor', $item['role']) || in_array('dc_pending_vendor', $item['role'])) {
                    $actions['status_reject'] = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg('action', 'reject', $action_url)), 'Reject');
                }
                $column_content = sprintf('<mark class="%s">%s</mark> %s', sanitize_title($item[$column_name]), $item[$column_name], $this->row_actions($actions));
                break;
            default :
                $column_content = $item[$column_name];
                break;
        }
        return $column_content;
    }

    /**
     * Make sure the subscription key and user id are included in checkbox column.
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Markup to be placed inside the column <td>
     * @since 1.0
     */
    public function column_cb($item) {
        return sprintf('<input type="checkbox" class="membership_user_id" name="membership_user_id[]" value="%1$s" />', $item['user_id']);
    }

    /**
     * Display extra filter controls between bulk actions and pagination.
     *
     * @since 1.3.1
     */
    function extra_tablenav($which) {
        global $WCMP_Vendor_Membership;
        $args = array(
            'posts_per_page' => -1,
            'offset' => 0,
            'category' => '',
            'category_name' => '',
            'orderby' => 'date',
            'order' => 'DESC',
            'include' => '',
            'exclude' => '',
            'meta_key' => '',
            'meta_value' => '',
            'post_type' => 'vendortype',
            'post_mime_type' => '',
            'post_parent' => '',
            'author' => '',
            'author_name' => '',
            'post_status' => 'publish',
            'suppress_filters' => true
        );
        $posts_array = get_posts($args);
        if ('top' == $which) {
            $selectedtype = isset($_GET['_Plan_id']) ? $_GET['_Plan_id'] : '';
            ?>
            <div class="alignleft actions">
                <select id="dropdown_products_and_variations" name="_Plan_id" data-placeholder="<?php _e('Search for a plans&hellip;', 'wcmp-vendor_membership'); ?>" style="width: 240px">
                    <option value=""><?php _e('Show all palns', 'woocommerce-subscriptions') ?></option>
                    <?php foreach ($posts_array as $type): ?>
                        <option value="<?php echo absint($type->ID); ?>" <?php selected($selectedtype, $type->ID); ?>>
                            <?php echo $type->post_title ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button(__('Filter'), 'button', false, false, array('id' => 'post-query-submit')); ?>
            </div><?php
            }
        }

    }
    