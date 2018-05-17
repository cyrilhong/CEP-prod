<?php
header('Content-Type:text/html; charset=utf-8');
require_once('cfg.alipay.php');
require_once('cls.alipay_notify.php');
//计算得出通知验证结果
$alipayNotify  = new AlipayNotify($aliapy_config);
$verify_result = $alipayNotify->verifyReturn();
if ($verify_result) { //验证成功
    ////////////////////////////////////////////////////////////////////////////////////
    $out_trade_no = $_GET['out_trade_no']; //获取订单号
    $trade_no     = $_GET['trade_no']; //获取支付宝交易号
    $total_fee    = $_GET['total_fee']; //获取总价格
    if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS' || $_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
        //支付成功
        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
            require_once('inc.apipay_send_confirm.php');
        }
        $arr_field = array(
            'out_trade_no',
            'trade_no',
            'total_fee',
            'buyer_email',
            'buyer_id',
            'notify_time'
        );
        $arr_rq    = ws_alipay_no_empty($arr_field, $_REQUEST);
        //规范传入参数
        $para_ret = array();
        //支付平台别名
        $para_ret['plat_name'] = 'ALIPAY';
        //交易状态
        $para_ret['status'] = 1;
        //商家内部订单号
        $para_ret['out_ordno'] = $arr_rq['out_trade_no'];
        //支付平台订单号
        $para_ret['plat_ordno'] = $arr_rq['trade_no'];
        //交易总额
        $para_ret['total_fee'] = $arr_rq['total_fee'];
        //客户邮箱账号
        $para_ret['buyer_email'] = $arr_rq['buyer_email'];
        //客户数字账号
        $para_ret['buyer_id'] = $arr_rq['buyer_id'];
        //支付时间
        $para_ret['pay_time'] = $arr_rq['notify_time'];
        //处理返回参数
        require_once(WS_ALIPAY_INC . 'cls.return.php');
        $ins_ret = new wsAlipayReturn($para_ret);
        $INFO    = $ins_ret->returnProcess();
    } else {
        //支付失败
        $INFO = 'PAY_FAILED';
    }
    ////////////////////////////////////////////////////////////////////////////////////
} else {
    //验证失败
    $INFO = 'VERIFY_FAILED';
}
isset($para_ret['out_ordno']) || $para_ret['out_ordno'] = '';
echo ws_alipay_show_tip($INFO, $para_ret['out_ordno']);

