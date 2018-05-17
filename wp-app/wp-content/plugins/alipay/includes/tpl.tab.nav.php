<?php
/*
 menu of settings page
*/


$menu_item = array(
    array('caption' => '商品仓库', 'name' => 'products'),
    //array('caption'=>'添加商品','name'=>'additem'),
    array('caption' => '订单管理', 'name' => 'orders'),
    //array('caption'=>'添加订单','name'=>'addorder'),
    //array('caption'=>'会员中心','name'=>'members'),
    array('caption' => '模版管理', 'name' => 'templates'),
    array('caption' => '常规设置', 'name' => 'general'),
);


$user_item = array(
    array('caption' => '我的订单', 'name' => 'orders'),
);


$user = wp_get_current_user();
if ($user->has_cap('activate_plugins')) {
    $items = $menu_item;
} elseif (ws_alipay_get_setting('allow_user_see_order')) {
    $items = $user_item;
}

if (!$user->has_cap('activate_plugins') && !ws_alipay_get_setting('allow_user_see_order')) {
    wp_die('Permission Deny!');
}

?>



<ul id="ws_alipay_menu">
    <?php
    foreach ($items as $value) {
        extract($value);
        echo "<li><a class=\"ws_alipay_menu_a\" href=\"?page=ws_alipay&tab=$name\">$caption</a></li>";
    }

    if ($user->has_cap('activate_plugins')) {
        echo '<li><a class="ws_alipay_menu_a_nojs" href="' . WS_ALIPAY_URL . '/readme.php' . '" target="_blank">帮助文档</a></li>';
    }

    ?>

    <div class="clear"></div>
    <div id="ws_alipay_logo">因为信任,所以简单</div>

</ul>
<div class="clear"></div>

