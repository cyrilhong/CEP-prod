<?php
/*
 menu of settings page
*/


$menu_item = array(
    array('caption' => '商品仓库', 'name' => 'items'),
    //array('caption'=>'添加商品','name'=>'additem'),
    array('caption' => '订单管理', 'name' => 'orders'),
    //array('caption'=>'添加订单','name'=>'addorder'),
    //array('caption'=>'会员中心','name'=>'members'),
    array('caption' => '模版管理', 'name' => 'templates'),
    array('caption' => '常规设置', 'name' => 'api'),
);

?>

<ul id="ws_alipay_menu">
    <?php
    foreach ($menu_item as $value) {
        extract($value);
        echo "<li><a class=\"ws_alipay_menu_a\" href=\"?page=ws_alipay&cpage=$name\">$caption</a></li>";
    }
    echo '<li><a class="ws_alipay_menu_a_nojs" href="' . WS_ALIPAY_URL . '/readme.php' . '" target="_blank">帮助文档</a></li>';

    ?>

    <div class="clear"></div>
    <div id="ws_alipay_logo">因为信任,所以简单</div>
</ul>

