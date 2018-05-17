<META http-equiv=Content-Type content="text/html; charset=utf-8">
<?php

//---------------------------------------------------------
//财付通即时到帐支付页面回调示例，商户按照此文档进行开发即可
//---------------------------------------------------------
require_once("./classes/ResponseHandler.class.php");
require_once("./classes/function.php");
require_once("./cfg.tenpay.php");

//log_result("进入前台回调页面");


/* 创建支付应答对象 */
$resHandler = new ResponseHandler();
$resHandler->setKey($tenpay_config['key']);

//判断签名
if ($resHandler->isTenpaySign()) {
    //通知id
    $notify_id = $resHandler->getParameter("notify_id");
    //商户订单号
    $out_trade_no = $resHandler->getParameter("out_trade_no");
    //财付通订单号
    $transaction_id = $resHandler->getParameter("transaction_id");
    //金额,以分为单位
    $total_fee = $resHandler->getParameter("total_fee");
    //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
    $discount = $resHandler->getParameter("discount");
    //支付结果
    $trade_state = $resHandler->getParameter("trade_state");
    //交易模式,1即时到账
    $trade_mode = $resHandler->getParameter("trade_mode");
    if ("1" == $trade_mode) {
        if ("0" == $trade_state) {
            //echo "<br/>" . "即时到帐支付成功" . "<br/>";
            //规范传入参数
            $para_ret = array();
            //支付平台别名
            $para_ret['plat_name'] = 'TENPAY';
            //交易状态
            $para_ret['status'] = 1;
            //商家内部订单号
            $para_ret['out_ordno'] = $_REQUEST['out_trade_no'];
            //支付平台订单号
            $para_ret['plat_ordno'] = $_REQUEST['transaction_id'];
            //交易总额
            $para_ret['total_fee'] = floatval($_REQUEST['total_fee']) / 100;
            //客户邮箱账号
            $para_ret['buyer_email'] = '';
            //客户数字账号
            $para_ret['buyer_id'] = '';
            //支付时间
            $para_ret['pay_time'] = current_time('mysql');
            //处理返回参数
            require_once(WS_ALIPAY_INC . 'cls.return.php');
            $ins_ret = new wsAlipayReturn($para_ret);
            $INFO    = $ins_ret->returnProcess();
            $url     = ws_alipay_show_url($INFO, $para_ret['out_ordno']);
        } else {
            //当做不成功处理
            $INFO = 'PAY_FAILED';
        }
    }
} else {
    $INFO = 'VERIFY_FAILED';
    //echo $resHandler->getDebugInfo() . "<br>";
}

isset($para_ret['out_ordno']) || $para_ret['out_ordno'] = '';
echo ws_alipay_show_tip($INFO, $para_ret['out_ordno']);
die();