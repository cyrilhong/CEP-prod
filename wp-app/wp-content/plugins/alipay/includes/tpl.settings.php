<?php
/*
 Template of settings page
*/
?>

    <div id="ws_alipay_main_wrap" tabindex="999">

        <?php include_once('tpl.tab.nav.php'); ?>
        <div id="ws_alipay_loading">Loading...</div>
        <div id="ws_alipay_child_wrap">
            <?php

            $ws_ac = isset($_GET['cpage']) ? $_GET['cpage'] : 'items';
            include_once("tpl.settings_{$ws_ac}.php");
            //		switch($ws_ac){
            //			case 'api':	include_once('tpl.settings_api.php');break;
            //			case 'items':	include_once('tpl.settings_items.php');break;
            //			case 'orders':	include_once('tpl.settings_orders.php');break;
            //			case 'members':	include_once('tpl.settings_members.php');break;
            //			case 'addtiem':	include_once('tpl.settings_addtiem.php');break;
            //			case 'templates':	include_once('tpl.settings_templates.php');break;
            //			case 'additem':	include_once('tpl.settings_addtiem.php');break;
            //			case 'addorder':	include_once('tpl.settings_addorder.php');break;
            //		}

            ?>
        </div>

        <?php include_once('tpl.settings_footer.php'); ?>

    </div>



<?php






?>