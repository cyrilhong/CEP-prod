<?php



/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin, 
 * rename it, and work inside the copy. If you modify this plugin directly and 
 * an update is released, your changes will be lost!
 * ========================================================================== */
/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */
class WSAlipay_Orders_List_Table extends WP_List_Table
{

    var $totals = array();

    var $mkey = 'ordid';

    var $mname = 'orders';

    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query().
     *
     * @var array
     **************************************************************************/
    /*var $example_data = array(
            array(
                'ID'        => 1,
                'title'     => '300',
                'rating'    => 'R',
                'director'  => 'Zach Snyder'
            ),
            array(
                'ID'        => 2,
                'title'     => 'Eyes Wide Shut',
                'rating'    => 'R',
                'director'  => 'Stanley Kubrick'
            ),
            array(
                'ID'        => 3,
                'title'     => 'Moulin Rouge!',
                'rating'    => 'PG-13',
                'director'  => 'Baz Luhrman'
            ),
            array(
                'ID'        => 4,
                'title'     => 'Snow White',
                'rating'    => 'G',
                'director'  => 'Walt Disney'
            ),
            array(
                'ID'        => 5,
                'title'     => 'Super 8',
                'rating'    => 'PG-13',
                'director'  => 'JJ Abrams'
            ),
            array(
                'ID'        => 6,
                'title'     => 'The Fountain',
                'rating'    => 'PG-13',
                'director'  => 'Darren Aronofsky'
            ),
            array(
                'ID'        => 7,
                'title'     => 'Watchmen',
                'rating'    => 'R',
                'director'  => 'Zach Snyder'
            )
        );*/
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct()
    {
        global $status, $page;
        //Set parent defaults
        parent::__construct(
            array(
                 'singular' => '订单', //singular name of the listed records
                 'plural'   => '订单', //plural name of the listed records
                 'ajax'     => false //does this table support ajax?
            )
        );
    }

    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item        A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     *
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'price':
            case 'buynum':
            case 'series':
            case 'otime':
            case 'status':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     *
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     *
     *
     * @see WP_List_Table::::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_name($item)
    {
        //Build row actions
        $actions = array(
            'edit'   => '<a href="' . add_query_arg(
                  array('tab' => $this->mname, 'action' => 'edit', $this->mkey => $item['ID'])
              ) . '">查看详情</a>',
            'delete' => '<a href="' . add_query_arg(
                  array('tab' => $this->mname, 'action' => 'delete', $this->mkey => $item['ID'])
              ) . '">删除</a>',
        );
        if (!current_user_can('level_10')) {
            unset($actions['delete']);
        }
        //Return the title contents
        return sprintf(
            '%1$s <span style="color:silver">(ID:%2$s,NO:%3$s)</span>%4$s',
            /*$1%s*/
            $item['name'],
            /*$2%s*/
            $item['proid'],
            /*$2%s*/
            $item['ordid'],
            /*$3%s*/
            $this->row_actions($actions)
        );
    }

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     *
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'], //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['ID'] //The value of the checkbox should be the record's id
        );
    }

    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    /* 		  case 'series':
            case 'otime':
            case 'num':
            case 'buynum':
            case 'status':*/
    function get_columns()
    {
        $columns = array(
            'cb'     => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'   => '商品名称',
            'price'  => '单价',
            'buynum' => '购买数量',
            'series' => '内部订单号',
            'otime'  => '下单时间',
            'status' => '交易状态',
        );
        return $columns;
    }

    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'   => array('name', false), //true means its already sorted
            'price'  => array('price', false),
            'buynum' => array('buynum', false),
            'series' => array('series', false),
            'otime'  => array('otime', true),
            'status' => array('status', false),
        );
        return $sortable_columns;
    }

    function get_views()
    {
        $status       = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $status_links = array();
        foreach ($this->totals as $type => $count) {
            if (!$count) {
                continue;
            }
            switch ($type) {
                case 'all':
                    $text = '全部 <span class="count">(' . $count . ')</span>';
                    break;
                case 'nopayment':
                    $text = '未付款 <span class="count">(' . $count . ')</span>';
                    break;
                case 'payed':
                    $text = '付款成功 <span class="count">(' . $count . ')</span>';
                    break;
            }
            $query = remove_query_arg('paged');
            if ('search' != $type) {
                $status_links[$type] = sprintf(
                    "<a href='%s' %s>%s</a>",
                    add_query_arg(array('filter' => $type), $query),
                    ($type == $status) ? ' class="current"' : '',
                    sprintf($text, number_format_i18n($count))
                );
            }
        }
        return $status_links;
    }

    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action()
    {
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {
            include_once('tpl.edit_order.php');
        } elseif ('edit' === $this->current_action()) {
            include_once('tpl.edit_order.php');
        }
    }

    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items()
    {
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        //$data = $this->example_data;
        /*
        array(
        array(
            'ID'        => 1,
            'title'     => '300',
            'rating'    => 'R',
            'director'  => 'Zach Snyder'
        ),
        array(
            'ID'        => 2,
            'title'     => 'Eyes Wide Shut',
            'rating'    => 'R',
            'director'  => 'Stanley Kubrick'
        ),
        array(
            'ID'        => 3,
            'title'     => 'Moulin Rouge!',
            'rating'    => 'PG-13',
            'director'  => 'Baz Luhrman'
        )
        )
                    */
        //-----------------------------------------------------------------------
        // get dataCONCAT('[zfb id=',proid,']')
        //-----------------------------------------------------------------------
        //$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $current_page = $this->get_pagenum();
        $start        = ($current_page - 1) * $per_page;
        $end          = $start + $per_page;
        global $wpdb;
        $data = $wpdb->get_results(
            "SELECT o.*,p.*,o.`ordid`as`ID` FROM `{$wpdb->wsaliorders}` as o INNER JOIN `{$wpdb->wsaliproducts}` as p ON o.`proid`=p.`proid`;",
            ARRAY_A
        );
        if (!ws_alipay_is_admin()) {
            global $user_ID;
            $meta = $wpdb->get_results(
                "SELECT `wsaliorders_id` FROM `{$wpdb->wsaliordersmeta}` WHERE `meta_key`='order_user_id' AND `meta_value`=$user_ID;",
                ARRAY_A
            );
            if (isset($meta[0])) {
                foreach ($meta as $item) {
                    $ids[] = $item['wsaliorders_id'];
                }
                $ids  = implode(',', $ids);
                $data = $wpdb->get_results(
                    "SELECT o.*,p.*,o.`ordid`as`ID` FROM `{$wpdb->wsaliorders}` as o INNER JOIN `{$wpdb->wsaliproducts}` as p ON o.`proid`=p.`proid` WHERE o.`ordid` IN ($ids);",
                    ARRAY_A
                );
            } else {
                $data = array();
            }
            foreach ($data as $k => $item) {
                $data[$k]['order_user_id'] = $user_ID;
            }
        }
        //$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->wsaliorders}`;");
        //die($count );
        foreach ($data as $k => $item) {
            if (isset($item['proid'])) {
                $data[$k]['protype'] = get_metadata($wpdb->wsaliproductsmetatype, $item['proid'], 'protype', 1);
            }
        }
        //print_r($data );
        //echo $data[0]['name'];
        //-----------------------------------------------------------------------
        // get data
        //-----------------------------------------------------------------------
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'otime'; //If no sort, default to title
            $order   = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result  = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);
        //$total_items = $count;
        /**
         * $this->tatals
         *
         *
         *
         *
         *
         */
        global $user_ID;
        // print_r($data);
        $userOrder = array();
        if (!ws_alipay_is_admin()) {
            foreach ($data as $k => $item) {
                if (isset($item['order_user_id']) && $item['order_user_id'] == $user_ID) {
                    $userOrder[$k] = $item;
                }
            }
            $data        = $userOrder;
            $total_items = count($data);
        }
        $this->totals['all']       = $total_items;
        $this->totals['nopayment'] = 0;
        $this->totals['payed']     = 0;
        foreach ($data as $k => $item) {
            if ($item['status'] == '0') {
                $data[$k]['status'] = '未付款';
                $this->totals['nopayment']++;
            }
            if ($item['status'] == '1') {
                $data[$k]['status'] = '付款成功';
                $this->totals['payed']++;
            }
        }
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        // print_r($data);
        $tmpData = array();
        if (isset($_GET['filter'])) {
            if ('all' == $_GET['filter']) {
                $tmpData = $data;
            }
            if ('nopayment' == $_GET['filter']) {
                foreach ($data as $k => $item) {
                    if (isset($item['status']) && '未付款' == $item['status']) {
                        $tmpData[$k] = $item;
                    }
                }
            } elseif ('payed' == $_GET['filter']) {
                foreach ($data as $k => $item) {
                    if (isset($item['status']) && '付款成功' == $item['status']) {
                        $tmpData[$k] = $item;
                    }
                }
            }
            $data = $tmpData;
        }
        $total_items = count($data);
        $data        = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(
            array(
                 'total_items' => $total_items, //WE have to calculate the total number of items
                 'per_page'    => $per_page, //WE have to determine how many items to show on a page
                 'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
            )
        );
    }
}
