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
    'alipay_partnerid'  => array('label' => '支付宝接口账号'),
    'alipay_key'        => array('label' => '支付宝接口密钥'),
    'alipay_account'    => array('label' => '支付宝收款账号'),
    ////财付通
    array('type' => 'html', 'html' => '<p class="clear_5"></p>'),
    'tenpay_partnerid'  => array('label' => '财付通接口账号'),
    'tenpay_key'        => array('label' => '财付通接口密钥'),
    ////贝宝
    array('type' => 'html', 'html' => '<p class="clear_5"></p>'),
    'paypal_account'    => array('label' => 'PayPal接口账号'),
    'paypal_password'   => array('label' => 'PayPal接口密码'),
    'paypal_key'        => array('label' => 'PayPal接口密钥'),
    ////邮件设置
    array('type' => 'html', 'html' => '<h2>邮件通知设置</h2>'),
    //管理员邮箱
    'notify_email'      => array('label' => '管理员邮箱地址', 'default' => get_option('admin_email')),
    //买卖家订单付款通知
    'buyer_ord_notify'  => array('type' => 'select', 'label' => '买家订单通知', 'default' => '0'),
    'buyer_pay_notify'  => array('type' => 'select', 'label' => '买家付款通知(建议开启)', 'default' => '1'),
    'seller_ord_notify' => array('type' => 'select', 'label' => '卖家订单通知', 'default' => '0'),
    'seller_pay_notify' => array('type' => 'select', 'label' => '卖家付款通知(建议开启)', 'default' => '1'),
    //缺货提醒
    'pro_lack_notify'   => array('type' => 'select', 'label' => '管理员缺货通知(建议开启)', 'default' => '1'),
);





?>
<script type="text/javascript">
    $(function() {//BOJQ

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
                        alert("出现错误!");
                },
                error: function(err) {
                    alert(1);
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