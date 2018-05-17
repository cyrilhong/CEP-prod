<?php
/*
 Template of settings page
*/
require_once('cfg.config.php');


$ws_alipay_api_json = get_option('ws_alipay_settings_api');
$ws_alipay_api_arr = json_decode($ws_alipay_api_json, true);


$ws_alipay_api_fields = array(
    ////支付宝
    array('type' => 'html', 'html' => '<h2>网关账号设置</h2>'),
    'alipay_partnerid'     => array('label' => '支付宝接口账号'),
    'alipay_key'           => array('label' => '支付宝接口密钥'),
    'alipay_account'       => array('label' => '支付宝收款账号'),
    'alipay_service'       => array(
        'type'    => 'select',
        'label'   => '支付宝接口类型',
        'default' => '1',
        'option'  => array(
            array(
                'value' => 'create_direct_pay_by_user',
                'label' => '支付宝即时到账收款接口'
            ),
            array(
                'value' => 'create_partner_trade_by_buyer',
                'label' => '支付宝担保交易收款接口'
            ),
            array('value' => 'trade_create_by_buyer', 'label' => '支付宝双功能收款接口')
        )
    ),
    ////财付通
    array('type' => 'html', 'html' => '<p class="clear_5"></p>'),
    'tenpay_partnerid'     => array('label' => '财付通接口账号'),
    'tenpay_key'           => array('label' => '财付通接口密钥'),
    ////贝宝
    array('type' => 'html', 'html' => '<p class="clear_5"></p>'),
    'paypal_account'       => array('label' => 'PayPal接口账号'),
    'paypal_password'      => array('label' => 'PayPal接口密码'),
    'paypal_key'           => array('label' => 'PayPal接口密钥'),
    ////邮件设置
    array('type' => 'html', 'html' => '<h2>邮件通知设置</h2>'),
    //管理员邮箱
    'notify_email'         => array('label' => '管理员邮箱地址', 'default' => get_option('admin_email')),
    //买卖家订单付款通知
    'buyer_ord_notify'     => array('type' => 'select', 'label' => '买家订单通知', 'default' => '0'),
    'buyer_pay_notify'     => array('type' => 'select', 'label' => '买家付款通知(建议开启)', 'default' => '1'),
    'seller_ord_notify'    => array('type' => 'select', 'label' => '卖家订单通知', 'default' => '0'),
    'seller_pay_notify'    => array('type' => 'select', 'label' => '卖家付款通知(建议开启)', 'default' => '1'),
    //缺货提醒
    'pro_lack_notify'      => array('type' => 'select', 'label' => '管理员缺货通知(建议开启)', 'default' => '1'),
    array('type' => 'html', 'html' => '<h2>其他设置</h2>'),
    'link_support'         => array('type' => 'text', 'label' => '客服超链接(显示在邮件中)', 'default' => ''),
    'user_must_login'      => array('type' => 'select', 'label' => '购买商品必须登录', 'default' => '0'),
    'allow_user_see_order' => array('type' => 'select', 'label' => '允许登录用户看见自己的订单', 'default' => '0'),
    array('type' => 'html', 'html' => '<br/>'),
    array('type' => 'html', 'html' => '<br/>'),
    array(
        'type' => 'html',
        'html' => '<p style="line-height:2em;padding:10px;padding-bottom:0">【注1】尊重开源, 请保留支付页面页脚的版权信息! 插件使用过程的问题以及建议请在<a href="http://www.waisir.com/wp-alipay/" target="_blank">插件主页</a>留言或通过<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=PEtdVU9VTnxNTRJfU1E" target="_blank">邮件</a>反馈给我!当然,当插件给你带来了便利的同时别忘记了<a href="http://wordpress.org/support/register.php" target="_blank">登录WP</a>给它一个<a href="http://wordpress.org/extend/plugins/alipay/" target="_blank">评分</a>!感谢您的支持!</p>'
    ),
    array(
        'type' => 'html',
        'html' => '<p style="line-height:2em;padding:10px;padding-bottom:0">【注2】有朋友反映模版太少,模版DIY有困难的朋友,你可以将你想要的模版的样式截图并发送至我的邮箱中,我将其写成代码放到模版页面中供大家下载!</p>'
    ),
    array(
        'type' => 'html',
        'html' => '<p style="line-height:2em;padding:10px;padding-bottom:0">Copyright &copy; 2012-2013 <a href="http://www.waisir.com" target="_blank">歪世界</a> 保留所有权</p>'
    ),
);

///ws_alipay_get_setting('')


?>
<script type="text/javascript">
    jQuery(function($) {//BOJQ

        $('#ws_alipay_api_form').submit(function() {
            var $data = $('#ws_alipay_api_form').serialize();

            $.ajax({
                url: '../wp-content/plugins/alipay/includes/inc.dbloader.php',
                type: 'post',
                dataType: 'JSON',
                data: $data +
                  '&ws_security_check=<?php echo ws_alipay_security_code();?>' +
                  '&action=78013',
                success: function(data) {
                    if (data == '')
                        alert('保存成功');
                    else
                        alert("保存失败");
                }

            });
            return false;
        });

        $('.ws_alipay_api_div input[type=checkbox]').bind('change', function() {
            if ($(this).attr('checked')) {
                $(this).val('1');
            } else {
                $(this).val('0');
            }
            alert($(this).val())
        });

        $('.ws_alipay_api_div input[type=checkbox]').each(function() {
            if ($(this).val() == '1') {
                $(this).attr('checked', 'checked');
            }
            if ($(this).val() == '0') {
                $(this).removeAttr('checked');
            }
        });

    });//EOJQ
</script>
<style type="text/css">

</style>
<div class="wrap ws_alipay_main_wrap">
    <?php include_once('tpl.tab.nav.php'); ?>
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>常规设置</h2>

    <div class="ws_alipay_api_div postbox">
        <form action="" method="post" class="api_form" id="ws_alipay_api_form">
            <?php echo ws_alipay_input_html($ws_alipay_api_fields, $ws_alipay_api_arr); ?>
            <p class="clear_10"></p>

            <div class="newline"></div>
            <div class="newline"></div>
            <div class="newline"></div>

            <input type="submit" class="button-primary ws_update" id="ws_alipay_api_update" value="保存设置"/>
        </form>

        <div class="clear"></div>
        <div class="newline"></div>
    </div>
</div>