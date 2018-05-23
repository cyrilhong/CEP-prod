<?php
/** ************************ REGISTER THE TEST PAGE ****************************
 *******************************************************************************
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */
/*
function tt_add_menu_items(){
    add_menu_page('Example Plugin List Table', 'List Table Example', 'activate_plugins', 'tt_list_test', 'tt_render_list_page');
} add_action('admin_menu', 'tt_add_menu_items');
*/

/***************************** RENDER TEST PAGE ********************************
 *******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */

require_once('cls.listTable_products.php');


//Create an instance of our package class...
$testListTable = new WSAlipay_Products_List_Table();


//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();

if (isset($_GET['proid'])) {
    return;
}

?>

<div class="wrap ws_alipay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-index" class="icon32"><br/></div>
    <h2>商品仓库</h2>

    <div class="ws_alipay_toobar01">

        <a href="?page=ws_alipay&tab=products&action=edit&proid" class="button-secondary">添加商品</a>
    </div>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <style>

        .column-name{
            width:30%;
        }
        .column-num{
            text-align:left !important
        }

    </style>
    <form id="movies-filter" method="get">
        <?php $testListTable->search_box('搜索', 'pro_input_id');?><?php


        ?>
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo intval($_REQUEST['page']) ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>

</div>
