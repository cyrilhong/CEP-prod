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

<div class="wrap">

    <div id="icon-users" class="icon32"><br/></div>
    <h2>List Table Test</h2>

    <div
      style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p>This page demonstrates the use of the <tt><a href="http://codex.wordpress.org/Class_Reference/WP_List_Table"
                                                        target="_blank" style="text-decoration:none;">WP_List_Table</a></tt>
            class in plugins.</p>

        <p>For a detailed explanation of using the <tt><a
                  href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank"
                  style="text-decoration:none;">WP_List_Table</a></tt>
            class in your own plugins, you can view this file <a
              href="/wp-admin/plugin-editor.php?plugin=table-test/table-test.php" style="text-decoration:none;">in the
                Plugin Editor</a> or simply open <tt style="color:gray;"><?php echo __FILE__ ?></tt> in the PHP editor
            of your choice.</p>

        <p>Additional class details are available on the <a
              href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank"
              style="text-decoration:none;">WordPress Codex</a>.</p>
    </div>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <style>

        .column-title{
            width:25%;
        }
        .column-num{
            text-align:left !important
        }

    </style>
    <form id="movies-filter" method="get">
        <?php $testListTable->search_box('搜索', 'pro_input_id');?><?php


        $testListTable->months_dropdown('post');
        $testListTable->view_switcher('excerpt');

        ?>
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo intval($_REQUEST['page']) ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>

</div>
