<?php
header('Content-Type:text/html; charset=utf-8');
require_once('cls.paypal_service.php');
if (empty($_REQUEST)) {
    die();
}
$paypalService = new PaypalService($paypal_config);
if ($paypalService->verifyIPN($_REQUEST)) {
    if (strtolower($_REQUEST['payment_status']) == 'completed') {
        /////////////////////////////////////////////////////////////////////////////////////
        $arr_field = array(
            'invoice',
            'mc_fee',
            'payer_email',
            'payer_id',
            'payment_date'
        );
        $arr_rq    = ws_alipay_no_empty($arr_field, $_REQUEST);
        //规范传入参数
        $para_ret = array();
        //支付平台别名
        $para_ret['plat_name'] = 'PAYPAL';
        //交易状态
        $para_ret['status'] = 1;
        //商家内部订单号
        $para_ret['out_ordno'] = $arr_rq['invoice'];
        //支付平台订单号
        $para_ret['plat_ordno'] = '';
        //交易总额
        $para_ret['total_fee'] = $arr_rq['mc_fee'];
        //客户邮箱账号
        $para_ret['buyer_email'] = $arr_rq['payer_email'];
        //客户数字账号
        $para_ret['buyer_id'] = $arr_rq['payer_id'];
        //支付时间
        $para_ret['pay_time'] = date('Y-m-d H:i:s', strtotime($arr_rq['payment_date']));
        //处理返回参数
        require_once(WS_ALIPAY_INC . 'cls.return.php');
        $ins_ret = new wsAlipayReturn($para_ret);
        $ins_ret->returnProcess();
        /////////////////////////////////////////////////////////////////////////////////////
    }
}

