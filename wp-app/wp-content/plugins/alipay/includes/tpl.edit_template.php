<?php

defined('ABSPATH') || die();

global $wpdb;
global $ws_alipay_table_templates;


//-----------------------------------------------------------------------
// upadte
//----------------------------------------------------------------------- 	
if (isset($_POST['submit'])) {
    //unset($_POST['_wpnonce']);
    //unset($_POST['_wp_http_referer']);
    foreach ($_POST as $k => $v) {
        if (!in_array($k, $ws_alipay_table_templates)) {
            unset($_POST[$k]);
        }
    }
    $wpdb->update($wpdb->wsalitemplates, $_POST, array('tplid' => intval($_REQUEST['tplid'])));
}

//-----------------------------------------------------------------------
//insert
//----------------------------------------------------------------------- 
if (empty($_POST) && empty($_GET['tplid'])) {
    $wpdb->insert($wpdb->wsalitemplates, array('tplname' => '未命名模版'));
    //echo $wpdb->insert_id;
    $_GET['tplid'] = $wpdb->insert_id;
}

//-----------------------------------------------------------------------
//delete
//----------------------------------------------------------------------- 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['tplid']) {
    $_GET['tplid'] = esc_sql($_GET['tplid']);
    if (isset($_GET['sure'])) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->wsalitemplates} WHERE `tplid`=%d;", $_GET['tplid']));
        return;
    }

    ?>
    <div style="margin:30px;">删除后数据不可恢复, 你确定要这样做吗?
        <a class="button-primary" href="<?php echo admin_url(
            'options-general.php?page=ws_alipay&action=delete&tab=templates&tplid=' . $_GET['tplid'] . '&sure'
        ); ?>">确定</a>
        <a class="button-secondary" href="<?php echo admin_url('options-general.php?page=ws_alipay'); ?>">取消</a>

    </div>
    <?php
    return;
}


$_GET['tplid'] = esc_sql($_GET['tplid']);

//-----------------------------------------------------------------------
//get data
//----------------------------------------------------------------------- 
$data = $wpdb->get_results("SELECT * FROM {$wpdb->wsalitemplates} WHERE `tplid`={$_GET['tplid']} LIMIT 1;", ARRAY_A);
//print_r($data);

if (isset($data[0])) {
    $data = $data[0];
}


$htmls = array(
    array(
        'tplid',
        '模版编号',
        'attrs' => array('style' => 'width:30%;margin:auto auto 10px 20px', 'readonly' => 'readonly')
    ),
    array('tplname', '模版名称', 'attrs' => array('style' => 'width:30%;margin:auto auto 10px 20px')),
    array('tpldescription', '模版描述', 'attrs' => array('style' => 'width:70%;margin:auto auto 10px 20px')),
    array(
        'tplcss',
        '模版CSS代码:(请自行在代码中添加&lt;style&gt;标签,可以使用链接关系)',
        'type'  => 'textarea',
        'attrs' => array('class' => 'ws_alipay_tpl_css')
    ),
    array(
        'tplhtml',
        '模版HTML代码:(请直接在&lt;div&gt;标签下写代码)',
        'type'  => 'textarea',
        'attrs' => array('class' => 'ws_alipay_tpl_html')
    ),
    array(
        'tpljs',
        '模版javascript代码:(请自行在代码中添加&lt;script&gt;标签,可以使用脚本路径)',
        'type'  => 'textarea',
        'attrs' => array('class' => 'ws_alipay_tpl_js')
    ),
);
$htmls = apply_filters('ws_alipay_templates_htmls', $htmls);


?>

<div class="wrap ws_alipay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-edit-pages" class="icon32"><br/></div>
    <h2>编辑模版</h2>


    <div id="ws_alipay_item_more" style="display:block">
        <div class="ws_alipay_item_more_main_wrap">
            <div class="ws_alipay_item_more_wrap">

                <form action="<?php echo admin_url(
                    'options-general.php?page=ws_alipay&tab=templates&action=edit&tplid=' . $_GET['tplid']
                ); ?>" method="post" id="ws_alipay_table_more_form" class="ws_alipay_table_form_templates">
                    <?php
                    echo ws_alipay_label_input_html_with_data($htmls, 'ws_alipay_templates_', $data);

                    wp_nonce_field('ws_alipay_edit');
                    ?>
                    <div class="clear"></div>


                    <input type="submit" name="submit" class="button-primary" value="更新"/>

                    <div class="clear"></div>

                </form>


            </div>
        </div>
    </div>
</div>