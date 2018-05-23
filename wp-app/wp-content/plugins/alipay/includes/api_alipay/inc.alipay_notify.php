<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 
 * TRADE_FINISHED(表示交易已经成功结束，为普通即时到帐的交易状态成功标识);
 * TRADE_SUCCESS(表示交易已经成功结束，为高级即时到帐的交易状态成功标识);
 */
require_once("cfg.alipay.php");
require_once("cls.alipay_notify.php");
//计算得出通知验证结果`
$alipayNotify  = new AlipayNotify($aliapy_config);
$verify_result = $alipayNotify->verifyNotify();
if ($verify_result) { //验证成功
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //请在这里加上商户的业务逻辑程序代
    //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
    $out_trade_no = $_POST['out_trade_no']; //获取订单号
    $trade_no     = $_POST['trade_no']; //获取支付宝交易号
    $total_fee    = $_POST['total_fee']; //获取总价格
    if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
        /////////////////////////////////////////////////////////////////////////////////////
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
        $ins_ret->returnProcess();
        /////////////////////////////////////////////////////////////////////////////////////
        echo "success";
        //请不要修改或删除■■■■■■■■■■■■■■■■■■■■
    } else {
        echo "success"; //这里要打印success,因为这仅代表支付宝服务器已经发送出了通知
        //其他状态判断。普通即时到帐中，其他状态不用判断，直接打印success。■■■■■■■■■■
        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    }
    //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
    //验证失败
    echo ws_alipay_show_tip('VERIFY_FAILED', '');
}
