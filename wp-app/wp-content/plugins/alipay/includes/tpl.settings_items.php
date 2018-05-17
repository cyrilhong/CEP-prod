<?php
/*
 items of settings page
*/

/*
name-商品名称
price-商品价格
num-商品剩余数量
description-商品描述
shortcode-短代码

*/
require_once('cfg.config.php');





?>

<script type="text/javascript">
    var $ws_alipay_security_code = '<?php echo ws_alipay_security_code();?>';
    var $ws_alipay_cartUrl = '<?php echo WS_ALIPAY_URL . '/includes/tpl.cart.php';?>';

    var $this = {
        prefix: 'ws_alipay_table_',
        css: {nav: 'ws_alipay_table_nav'},
        id: {
            hotkey: 'ws_alipay_main_wrap',
            mainWrap: 'ws_alipay_child_wrap',
            formMore: 'ws_alipay_table_more_form',
            table: 'ws_table_id',
            tableParent: 'ws_alipay_child_wrap'
        },
        class: {table: 'widefat ws_alipay_table'},
        arr: {
            head: ['商品名称', '商品价格', '剩余数量', '商品描述', '短代码'],
            colsWidth: ['5%', '15%', '10%', '10%', '25%', '15%', '5%', '5%', '5%']
        },
        num: {cols: 9},
        pos: {primaryKey: 5, rowIndex: 0},
        bln: {showMore: 0, showEdit: 1, showDelete: 1, showCopy: 1},
        currentKey: 0,
        fields: 'name,price,num,description,proid',	//this should be changed
        primaryKey: 'proid',
        tabname: 'products',//this should be changed AND MUST BE THE SAME AS THE DB.LOADER.PHP'S TABLE
        showFields: {
            name: '',
            price: '',
            num: '',
            description: '',
            proid: '[zfb id=[proid]]'
        },
        showLength: {
            name: 10,
            price: 10,
            num: 10,
            description: 25,
            proid: ''
        },
        getClass: function(afix) {
            return '.' + this.prefix + afix;
        },
        getClassNoDot: function(afix) {
            return  this.prefix + afix;
        },
        getId: function(afix) {
            return '#' + this.prefix + afix;
        },
        getIdNoDot: function(afix) {
            return  this.prefix + afix;
        },
        getPriKey: function(a) {
            a = a.substr(8)
            a = a.substr(0, a.length - 1)
            return a
        }

    }

    var $ = jQuery.noConflict();
    jQuery(function() {//THE BEGINNING OF JQ

        ws_alipay_page_init();


    });//END OF JQ


</script>

<style>
    /*select{width:0px;height:0px}*/
</style>


<input type="button" value="添加商品" id="ws_alipay_table_add"/>

<div id="ws_alipay_item_more" style="display:none">
    <div class="ws_alipay_item_more_main_wrap">
        <div class="ws_alipay_loading_more">
            <div class="ws_alipay_loading_bar"></div>
        </div>
        <div class="ws_alipay_item_more_wrap">


            <form action="" method="post" id="ws_alipay_table_more_form" class="ws_alipay_table_form">

                <?php


                $htmls = array(
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
                    array(
                        'pricePerDay',
                        '每日单价',
                        'type'  => 'hidden',
                        'attrs' => array('class' => 'ws_alipay_multiPrice')
                    ),
                    array(
                        'pricePerWeek',
                        '每周单价',
                        'type'  => 'hidden',
                        'attrs' => array('class' => 'ws_alipay_multiPrice')
                    ),
                    array(
                        'pricePerMonth',
                        '每月单价',
                        'type'  => 'hidden',
                        'attrs' => array('class' => 'ws_alipay_multiPrice')
                    ),
                    array(
                        'pricePerQuarter',
                        '每季单价',
                        'type'  => 'hidden',
                        'attrs' => array('class' => 'ws_alipay_multiPrice')
                    ),
                    array(
                        'pricePerYear',
                        '每年单价',
                        'type'  => 'hidden',
                        'attrs' => array('class' => 'ws_alipay_multiPrice')
                    ),
                    array('description', '商品描述'),
                    array('weight', '商品净重(kg)'),
                    array('snum', '已售数量'),
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
<textarea name="autosrc" style="float:right;display:block;width:97.5%;min-width:97.5%;max-width:97.5%;min-height:70px;margin-left:2.5%" class="ws_alipay_select_autosend_rel"></textarea>
</div>'
                    ),
                    array('', '商品快捷链接', 'attrs' => array('class' => 'ws_alipay_prolink', 'title' => '双击打开')),
                );


                //add_filter( 'ws_alipay_products_0', 'ws_alipay_products_0_fn',10,1);

                function ws_alipay_products_0_fn($item)
                {
                    return array(
                        $item,
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
                        )
                    );
                }


                $htmls = apply_filters('ws_alipay_products_htmls', $htmls);


                //SO  MANY FILTERS IN FUNCTION ws_alipay_label_input_html()
                //SUCH AS:ws_alipay_products_price, ws_alipay_products_3
                echo ws_alipay_label_input_html($htmls, 'ws_alipay_products_');




                ?>

                <input type="submit" class="button-primary" value="更新"/>

                <div class="clear"></div>
            </form>

        </div>
    </div>
</div>
