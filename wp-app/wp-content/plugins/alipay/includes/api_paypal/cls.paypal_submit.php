<?php
require_once(WS_ALIPAY_INC . 'fnc.api_core.php');
class PaypalSubmit
{

    /**
     * 构造提交表单HTML数据
     *
     * @param $para_temp     请求参数数组
     * @param $nvpStr        query参数组合t
     * @param $paypal_config 配置参数
     *
     * @return 提交表单HTML文本
     * 说明:在贝宝这里由于国内访问贝宝网站比较慢,由于贝宝的API机制,需要先获取令牌再提交到官网,因此为了更好的用户体验.在此换用为AJAX异步验证,验证成功后直接跳转到支付页面.
     */
    function buildForm($para_temp, $nvpStr, $paypal_config)
    {
        $sHtml = include_once('tpl.paypal_ajax.php');
        return '';
    }
}

