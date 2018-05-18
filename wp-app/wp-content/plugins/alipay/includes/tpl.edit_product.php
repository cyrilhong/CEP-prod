<?php

defined('ABSPATH') || die();

global $wpdb;
global $ws_alipay_table_products;


//-----------------------------------------------------------------------
// upadte
//----------------------------------------------------------------------- 	
if (isset($_POST['submit'])) {
    unset($_POST['_wpnonce']);
    unset($_POST['submit']);
    unset($_POST['_wp_http_referer']);
    //$metas = array();
    foreach ($_POST as $k => $v) {
        if (!in_array($k, $ws_alipay_table_products)) {
            unset($_POST[$k]);
            //$metas[$k] = $v;
            update_metadata($wpdb->wsaliproductsmetatype, intval($_REQUEST['proid']), $k, $v);
        }
    }
    $wpdb->update($wpdb->wsaliproducts, $_POST, array('proid' => intval($_REQUEST['proid'])));
}

//-----------------------------------------------------------------------
//insert
//----------------------------------------------------------------------- 
if (empty($_POST) && empty($_GET['proid'])) {
    $wpdb->insert($wpdb->wsaliproducts, array('name' => '未命名'));
    //echo $wpdb->insert_id;
    $_GET['proid'] = $wpdb->insert_id;
    $wpdb->show_errors();
}

//-----------------------------------------------------------------------
//delete
//----------------------------------------------------------------------- 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['proid']) {
    $_GET['proid'] = esc_sql($_GET['proid']);
    if (isset($_GET['sure'])) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->wsaliproducts} WHERE `proid`=%d;", $_GET['proid']));
        return;
    }

    ?>
    <div style="margin:30px;">删除后数据不可恢复, 你确定要这样做吗?
        <a class="button-primary" href="<?php echo add_query_arg(array('sure' => '')); ?>">确定</a>
        <a class="button-secondary" href="<?php echo admin_url('options-general.php?page=ws_alipay'); ?>">取消</a>

    </div>
    <?php
    return;
}


$_GET['proid'] = esc_sql($_GET['proid']);

//-----------------------------------------------------------------------
//get data
//----------------------------------------------------------------------- 
$data = $wpdb->get_results("SELECT * FROM {$wpdb->wsaliproducts} WHERE `proid`={$_GET['proid']} LIMIT 1;", ARRAY_A);
$meta = $wpdb->get_results(
    "SELECT `meta_key`,`meta_value` FROM {$wpdb->wsaliproductsmeta} WHERE `wsaliproducts_id`={$_GET['proid']};"
);

foreach ($meta as $k => $item) {
    $data[0][$item->meta_key] = $item->meta_value;
}


//$data = array_merge($data,$meta);

//print_r($data);

if (isset($data[0])) {
    $data = $data[0];
}
$data['buylink'] = get_bloginfo('url') . '/wp-content/plugins/alipay/includes/tpl.cart.php?proid=' . $data['proid'];

if (!isset($data['autosrc'])) {
    $data['autosrc'] = '';
}

$htmls = array(
    array('proid', '商品编号', 'type' => 'hidden'),
    array('name', '商品名称'),
    array(
        'protype',
        '商品类型',
        'type'    => 'select',
        'options' => array(
            'CUSTOM'  => '普通实物',
            'VIRTUAL' => '普通虚拟',
            'ADP'     => '广告位',
            'LINK'    => '友情链接'
        ),
        'attrs'   => array('class' => 'ws_alipay_select_protype')
    ),
    array('price', '商品价格'),
    array('pricePerDay', '每日单价', 'type' => 'hidden', 'attrs' => array('class' => 'ws_alipay_multiPrice')),
    array('pricePerWeek', '每周单价', 'type' => 'hidden', 'attrs' => array('class' => 'ws_alipay_multiPrice')),
    array('pricePerMonth', '每月单价', 'type' => 'hidden', 'attrs' => array('class' => 'ws_alipay_multiPrice')),
    array('pricePerQuarter', '每季单价', 'type' => 'hidden', 'attrs' => array('class' => 'ws_alipay_multiPrice')),
    array('pricePerYear', '每年单价', 'type' => 'hidden', 'attrs' => array('class' => 'ws_alipay_multiPrice')),
    array('description', '商品描述'),
    array('weight', '商品净重(kg)'),
    array('snum', '已售数量', 'attrs' => array('readonly' => 'readonly')),
    array('num', '剩余数量'),
    array('images', '商品图片地址'),
    array('download', '下载链接'),
    array('zipcode', '解压密码'),
    array('tags', '商品标签(,)'),
    array(
        'spfre',
        '买家承担运费',
        'type'    => 'select',
        'options' => array(0 => '否', 1 => '是'),
        'attrs'   => array('class' => 'ws_alipay_select_spfre')
    ),
    array('freight', '运费价格', 'attrs' => array('class' => 'ws_alipay_select_spfre_rel')),
    array('location', '商品所在地'),
    array('atime', '商品添加日期', 'attrs' => array('readonly' => 'readonly')),
    array('btime', '商品上架时间'),
    array('etime', '商品下架时间'),
    array(
        'promote',
        '开启促销',
        'type'    => 'select',
        'options' => array(0 => '关闭', 1 => '开启'),
        'attrs'   => array('class' => 'ws_alipay_select_promote')
    ),
    array(
        'protime',
        '开启每日促销',
        'type'    => 'select',
        'options' => array(0 => '关闭', 1 => '开启'),
        'attrs'   => array('class' => 'ws_alipay_select_protime 				ws_alipay_select_promote_rel')
    ),
    array(
        'probdate',
        '促销开始日期',
        'attrs' => array('class' => 'ws_alipay_select_promote_rel ws_alipay_select_promote_rel')
    ),
    array(
        'probtime',
        '促销开始时间',
        'attrs' => array('class' => 'ws_alipay_select_protime_rel ws_alipay_select_promote_rel')
    ),
    array(
        'proedate',
        '促销结束日期',
        'attrs' => array('class' => 'ws_alipay_select_promote_rel ws_alipay_select_promote_rel')
    ),
    array(
        'proetime',
        '促销结束时间',
        'attrs' => array('class' => 'ws_alipay_select_protime_rel ws_alipay_select_promote_rel')
    ),
    array(
        'discountb',
        '促销折扣',
        'type'    => 'select',
        'options' => array(0 => '关闭', 1 => '开启'),
        'attrs'   => array('class' => 'ws_alipay_select_discountb ws_alipay_select_promote_rel')
    ),
    array(
        'discount',
        '折扣比率',
        'attrs' => array('class' => 'ws_alipay_select_discountb_rel ws_alipay_select_promote_rel')
    ),
    array('tplid', '模版选择'),
    array(
        'autosend',
        '启用自动货源列表',
        'type'    => 'select',
        'options' => array(0 => '关闭', 1 => '开启'),
        'attrs'   => array('class' => 'ws_alipay_select_autosend')
    ),
    array('autosep', '货源分隔符', 'attrs' => array('class' => 'ws_alipay_select_autosend_rel')),
    array(
        'autosrc',
        'html' => '<div style="float:none;clear:both;width:100%;">
<label for="autosrc" style="float:left;padding-left:2.5%;width:100%">虚拟物品货源&nbsp;&nbsp;&nbsp;&nbsp;(如果货源文本是每行一个条目,请将\'货源分隔符\'留空。一旦设置了分隔符，下面的货源文件就应该用该分隔符分隔)</label>
<textarea name="autosrc" style="float:right;display:block;width:97.5%;min-width:97.5%;max-width:97.5%;min-height:70px;margin-left:2.5%" class="ws_alipay_select_autosend_rel">' . $data['autosrc'] . '</textarea>
</div>'
    ),
    array('buylink', '商品快捷链接', 'attrs' => array('class' => 'ws_alipay_prolink', 'title' => '双击打开')),
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
    <h2>编辑商品</h2>
    <?php include_once('tpl.tab.nav.php'); ?>


    <div id="ws_alipay_item_more" style="display:block">
        <div class="ws_alipay_item_more_main_wrap">

            <div class="ws_alipay_item_more_wrap">

                <?php

                ?>
                <form action="<?php echo admin_url(
                    'options-general.php?page=ws_alipay&action=edit&proid=' . $_GET['proid']
                ); ?>" method="post" id="ws_alipay_table_more_form" class="ws_alipay_table_form">
                    <?php


                    echo ws_alipay_label_input_html_with_data($htmls, 'ws_alipay_products_', $data);

                    wp_nonce_field('ws_alipay_edit');
                    ?>


                    <input type="submit" name="submit" class="button-primary" value="更新"/>

                    <div class="clear"></div>
                </form>

            </div>
        </div>
    </div>
</div>