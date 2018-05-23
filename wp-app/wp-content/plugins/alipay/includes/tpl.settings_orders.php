<?php
/*
 orders of settings page
*/

/*
ordid-订单自动ID-AUTO INCREMENT
proid-商品id---根据这个获取商品的其他信息-INT
series-序列号---VARCHAR(20)
aliacc-支付宝账号--VARCHAR(30)
buynum-购买数量-SMALLINT
email-客户邮箱-VARCHAR(30)
phone-客户电话-VARCHAR(20)
address-客户地址-VARCHAR(100)
remarks-备注-VARCHAR(255)
message-给卖家的留言--VARCHAR(255)
otime-下订单的时间戳-TIMESTAMP 
stime-购买成功时间戳-TIMESTAMP
status-交易状态-BOOLEAN

*/
require_once('cfg.config.php');


?>

<script type="text/javascript">
    var $ws_alipay_security_code = '<?php echo ws_alipay_security_code();?>';


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
            head: ['编号', '商品名称', '支付总金额', '购买数量', '支付账号', '下单时间', '交易状态'],
            colsWidth: ['5%', '5%', '20%', '10%', '10%', '15%', '15%', '10%', '5%', '5%'],
            fields_refer: ['orders|proid|ordid,proid,aliacc,buynum,status,otime,ordfee', 'products|proid|name']
        },
        num: {cols: 10},
        bln: {showMore: 1, showEdit: 0, showDelete: 1, showCopy: 0},
        pos: {primaryKey: 1, rowIndex: 0},
        currentKey: 0,
        fields: 'ordid,buynum,aliacc,status,otime,ordfee',	//this should be changed
        primaryKey: 'ordid',
        tabname: 'orders',//this should be changed AND MUST BE THE SAME AS THE DB.LOADER.PHP'S TABLE
        showFields: {
            'ordid': '',
            'name': '',
            'ordfee': '',
            'buynum': '',
            'aliacc': '',
            'otime': '',
            'status': 'ws_alipay_filter_ordstatus'
        },
        showLength: {
            'ordid': 10,
            'name': 10,
            'ordfee': 10,
            'buynum': 10,
            'aliacc': 20,
            'otime': '',
            'status': ''
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
            return a;
        }

    }


    var $ = jQuery.noConflict();
    jQuery(function() {//THE BEGINNING OF JQ

        ws_alipay_page_init();

    });//END OF JQ


</script>


<div id="ws_alipay_item_more" style="display:none">
    <div class="ws_alipay_item_more_main_wrap">
        <div class="ws_alipay_loading_more">
            <div class="ws_alipay_loading_bar"></div>
        </div>
        <div class="ws_alipay_item_more_wrap">

            <form action="" method="post" id="ws_alipay_table_more_form" class="ws_alipay_table_form">

                <?php


                $htmls = array(
                    array('proid', '商品编号', 'attrs' => array('readonly' => 'readonly')),
                    array('buynum', '购买数量', 'attrs' => array('readonly' => 'readonly')),
                    array('series', '商户订单号', 'attrs' => array('readonly' => 'readonly')),
                    array('platTradeNo', '平台订单号', 'attrs' => array('readonly' => 'readonly')),
                    array('paygate', '支付网关', 'attrs' => array('readonly' => 'readonly')),
                    array('aliacc', '支付账号', 'attrs' => array('readonly' => 'readonly')),
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
                $htmls = apply_filters('ws_alipay_orders_htmls', $htmls);
                echo ws_alipay_label_input_html($htmls, 'ws_alipay_orders_');

                wp_nonce_field('ws_alipay_edit');
                ?>



                <div class="clear"></div>
            </form>


        </div>
    </div>
</div>