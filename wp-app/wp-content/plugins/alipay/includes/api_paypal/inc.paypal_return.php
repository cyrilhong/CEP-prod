<?php
header('Content-Type:text/html; charset=utf-8');
session_start();
ini_set('session.bug_compat_42', 0);
ini_set('session.bug_compat_warn', 0);
require_once('cls.paypal_service.php');
$token  = $_REQUEST['token'];
$token  = urlencode($_REQUEST['token']);
$nvpStr = "&TOKEN=" . $token;
//获取订单详情,获取支付令牌
$paypalService = new PaypalService($paypal_config);
$resArray      = $paypalService->hash_call('GetExpressCheckoutDetails', $nvpStr);
$detailArray   = $resArray;
$ack = strtoupper($resArray["ACK"]);
if ($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {
    $TotalAmount   = $resArray['AMT'] + $resArray['SHIPDISCAMT'];
    $token         = urlencode($_REQUEST['token']);
    $paymentAmount = urlencode($TotalAmount);
    $paymentType   = urlencode($_REQUEST['paymentType']);
    $currCodeType  = urlencode($_REQUEST['currencyCodeType']);
    $payerID       = urlencode($_REQUEST['PayerID']);
    $serverName    = urlencode($_SERVER ['SERVER_NAME']);
    $notifyUrl     = urlencode($paypal_config['NOTIFY_URL']);
    $nvpStr        = '&TOKEN=' . $token . '&PAYERID=' . $payerID . '&PAYMENTACTION=' . $paymentType . '&AMT=' . $paymentAmount . '&CURRENCYCODE=' . $currCodeType . '&NOTIFYURL=' . $notifyUrl . '&IPADDRESS=' . $serverName;
    //DO Pay
    $paypalService = new PaypalService($paypal_config);
    $resArray      = $paypalService->hash_call('DoExpressCheckoutPayment', $nvpStr);
    $ack           = strtoupper($resArray["ACK"]);
    //Redirecting to APIError.php to display errors.
    //10415是已经支付成功了
    if ($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING' && $resArray['L_ERRORCODE0'] !== '10415') {
        //支付失败
        $INFO = 'PAY_FAILED';
    } else {
        //支付成功
        $resArray = array_merge($resArray, $detailArray);
        /////////////////////////////////支付成功////////////////////////////////////
        $arr_field = array(
            'INVNUM',
            'TRANSACTIONID',
            'AMT',
            'EMAIL',
            'PAYERID',
            'TIMESTAMP'
        );
        $arr_rq    = ws_alipay_no_empty($arr_field, $resArray);
        //规范传入参数
        $para_ret = array();
        //支付平台别名
        $para_ret['plat_name'] = 'PAYPAL';
        //交易状态
        $para_ret['status'] = 1;
        //商家内部订单号
        $para_ret['out_ordno'] = $arr_rq['INVNUM'];
        //支付平台订单号
        $para_ret['plat_ordno'] = $arr_rq['TRANSACTIONID'];
        //交易总额
        $para_ret['total_fee'] = $arr_rq['AMT'];
        //客户邮箱账号
        $para_ret['buyer_email'] = $arr_rq['EMAIL'];
        //客户数字账号
        $para_ret['buyer_id'] = $arr_rq['PAYERID'];
        //支付时间
        $para_ret['pay_time'] = date('Y-m-d H:i:s', strtotime($arr_rq['TIMESTAMP']));
        //处理返回参数
        require_once(WS_ALIPAY_INC . 'cls.return.php');
        $ins_ret = new wsAlipayReturn($para_ret);
        $INFO    = $ins_ret->returnProcess();
    }
    /////////////////////////////////////////////////////////////////////////////////////
} else {
    //验证失败
    $INFO = 'VERIFY_FAILED';
}
isset($para_ret['out_ordno']) || $para_ret['out_ordno'] = '';
echo ws_alipay_show_tip($INFO, $para_ret['out_ordno']);



