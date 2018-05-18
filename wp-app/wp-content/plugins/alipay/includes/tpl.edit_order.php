<?php

defined('ABSPATH') || die();

global $wpdb;
global $ws_alipay_table_wsaliorders;


//-----------------------------------------------------------------------
// upadte
//----------------------------------------------------------------------- 	
if (isset($_POST['submit'])) {
    //unset($_POST['_wpnonce']);
    //unset($_POST['_wp_http_referer']);
    foreach ($_POST as $k => $v) {
        if (!in_array($k, $ws_alipay_table_wsaliorders)) {
            unset($_POST[$k]);
        }
    }
    $wpdb->update($wpdb->wsaliorders, $_POST, array('ordid' => intval($_REQUEST['ordid'])));
}

//-----------------------------------------------------------------------
//insert
//----------------------------------------------------------------------- 
if (empty($_POST) && empty($_GET['ordid'])) {
    //$wpdb->insert($wpdb->wsaliwsaliorders,array('name'=>'未命名'));
    //echo $wpdb->insert_id;
    //$_GET['ordid'] = $wpdb->insert_id;
}

//-----------------------------------------------------------------------
//delete
//----------------------------------------------------------------------- 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['ordid']) {
    $user = wp_get_current_user();
    if (!$user->has_cap('activate_plugins')) {
        die();
    }
    ?>
    <div style="margin:30px;">删除后数据不可恢复, 你确定要这样做吗?
        <a class="button-primary" href="<?php echo admin_url(
            'options-general.php?page=ws_alipay&action=delete&tab=orders&ordid=' . $_GET['ordid'] . '&sure'
        ); ?>">确定</a>
        <a class="button-secondary"
           href="<?php echo admin_url('options-general.php?page=ws_alipay&tab=orders'); ?>">取消</a>

    </div>
    <?php
    return;
}


$_GET['ordid'] = esc_sql($_GET['ordid']);

//-----------------------------------------------------------------------
//get data
//----------------------------------------------------------------------- 
$data = $wpdb->get_results("SELECT * FROM {$wpdb->wsaliorders} WHERE `ordid`={$_GET['ordid']} LIMIT 1;", ARRAY_A);
//print_r($data);


if (isset($data[0])) {
    $data = $data[0];
}

$ordermeta = $wpdb->get_results(
    "SELECT * FROM {$wpdb->wsaliordersmeta} WHERE `wsaliorders_id`={$_GET['ordid']};",
    ARRAY_A
);

foreach ($ordermeta as $meta) {
    $data[$meta['meta_key']] = $meta['meta_value'];
}


$htmls = array(
    array('proid', '商品编号', 'attrs' => array('readonly' => 'readonly')),
    array('buynum', '购买数量', 'attrs' => array('readonly' => 'readonly')),
    array('series', '商户订单号', 'attrs' => array('readonly' => 'readonly')),
    array('platTradeNo', '平台订单号', 'attrs' => array('readonly' => 'readonly')),
    array('paygate', '支付网关', 'attrs' => array('readonly' => 'readonly')),
    array('aliacc', '支付账号', 'attrs' => array('readonly' => 'readonly')),
    array('username', '用户名', 'attrs' => array('readonly' => 'readonly')),
    array('ordname', '收件人姓名', 'attrs' => array('readonly' => 'readonly')),
    array('email', '收件人邮箱', 'attrs' => array('readonly' => 'readonly')),
    array('phone', '收件人电话', 'attrs' => array('readonly' => 'readonly')),
    array('postcode', '收件人邮编', 'attrs' => array('readonly' => 'readonly')),
    array('address', '收件人地址', 'attrs' => array('readonly' => 'readonly')),
    array('remarks', '备注信息', 'attrs' => array('readonly' => 'readonly')),
    array('message', '客户留言', 'attrs' => array('readonly' => 'readonly')),
    array('otime', '下单时间', 'attrs' => array('readonly' => 'readonly')),
    array('stime', '付款时间', 'attrs' => array('readonly' => 'readonly')),
    array('referer', '展示页面', 'attrs' => array('readonly' => 'readonly')),
    array(
        'status',
        '交易状态',
        'type'    => 'select',
        'options' => array(0 => '待付款', 1 => '已付款'),
        'attrs'   => array('disabled' => 'disabled')
    ),
    array(
        'emailsend',
        '自动发货',
        'type'    => 'select',
        'options' => array(0 => '未发货', 1 => '已发货'),
        'attrs'   => array('disabled' => 'disabled')
    ),
    array(
        'sendsrc',
        '所发货源',
        'type'  => 'textarea',
        'attrs' => array('class' => 'areatotext', 'disabled' => 'disabled')
    ),
);

$translate = array();
foreach ($htmls as $item) {
    if (isset($item[0]) && isset($item[1])) {
        $translate[$item[0]] = $item[1];
    }
}

$translate['proid'] = '商品编号';


foreach ($data as $k => $item) {
    break;
    ?>

    <div class="item"><label for="<?php echo $k; ?>" class="lbl"><?php if (isset($translate[$k])) {
                echo $translate[$k];
            } else {
                echo $k;
            } ?>：</label><input class="txt" type="text" name="<?php echo $k; ?>"
                                value="<?php echo $item; ?>"/></div>

<?php
}
?>

<div class="wrap ws_alipay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>

    <div id="icon-edit-pages" class="icon32"><br/></div>
    <h2>浏览订单</h2>


    <div id="ws_alipay_item_more" style="display:block">
        <div class="ws_alipay_item_more_main_wrap">

            <div class="ws_alipay_item_more_wrap">


                <form action="" method="post" id="ws_alipay_table_more_form" class="ws_alipay_table_form">
                    <?php


                    echo ws_alipay_label_input_html_with_data($htmls, 'ws_alipay_products_', $data);

                    wp_nonce_field('tpl.edit_product.php');
                    ?>

                    <!--<input type="submit" class="button-primary" value="更新" />-->
                    <div class="clear"></div>
                </form>

            </div>
        </div>
    </div>
</div>