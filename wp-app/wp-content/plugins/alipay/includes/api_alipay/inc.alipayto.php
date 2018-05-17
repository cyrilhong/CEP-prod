<?php
header('Content-Type:text/html; charset=utf-8');
require_once('cls.alipay_service.php');
require_once('cfg.alipay.php');
$p = $ws_payto_para;
unset ($ws_payto_para);
/////////////////////////////////////////////////////////////////////////////////////
$alipay_service = ws_alipay_get_setting('alipay_service');
if (empty($alipay_service)) {
    $alipay_service = 'create_direct_pay_by_user';
}
//create_direct_pay_by_user:即时到帐
//create_partner_trade_by_buyer:担保交易
//trade_create_by_buyer:双接口
/////////////////////////////////////////////////////////////////////////////////////
//构造要请求的参数数组
$parameter = array(
    "service"            => $alipay_service,
    "payment_type"       => "1",
    "partner"            => trim($aliapy_config['partner']),
    "_input_charset"     => trim(strtolower($aliapy_config['input_charset'])),
    "seller_email"       => trim($aliapy_config['seller_email']),
    "return_url"         => trim($aliapy_config['return_url']),
    "notify_url"         => trim($aliapy_config['notify_url']),
    //############################################################################
    'quantity'           => $p['num'],
    'price'              => $p['price'],
    //############################################################################
    'royalty_type'       => "",
    'royalty_parameters' => "",
    //'out_bill_no'       => '201112142123',
    //############################################################################
    "out_trade_no"       => $p['ordno'],
    "subject"            => $p['name'],
    "body"               => $p['desc'],
    "paymethod"          => $p['paymethod'],
    "defaultbank"        => $p['bank'],
    "anti_phishing_key"  => $p['anti_phishing_key'],
    "exter_invoke_ip"    => $p['exter_invoke_ip'],
    "show_url"           => $p['showurl'],
    "extra_common_param" => $p['extra'],
    //"royalty_type"		=> $p['royalty_type'],
    //"royalty_parameters"=> $p['royalty_parameters'],
);
if (empty($orderInfo['ordname'])) {
    $orderInfo['ordname'] = '收货人姓名';
}
if (empty($orderInfo['address'])) {
    $orderInfo['address'] = '收货人地址';
}
if (empty($orderInfo['postcode'])) {
    $orderInfo['postcode'] = 123456;
}
if (empty($orderInfo['phone'])) {
    $orderInfo['phone'] = '15888888888';
}
if ($orderInfo['freight'] == 0) //卖家付运费
{
    $orderInfo['logistics_payment'] = 'SELLER_PAY';
} else {
    $orderInfo['logistics_payment'] = 'BUYER_PAY';
    if (empty($orderInfo['freight'])) {
        $orderInfo['freight'] = 0.00;
    } else {
        $parameter['price'] -= $orderInfo['freight'];
        if ($parameter['price'] < 0.01) {
            $parameter['price'] = 0.01;
        }
        $parameter['price'] = number_format($parameter['price'], 2);
    }
}
if ($alipay_service == 'create_partner_trade_by_buyer' || $alipay_service == 'trade_create_by_buyer') {
    if (empty($orderInfo['ordname'])) {
        $orderInfo['ordname'] == '会员';
    }
    if (empty($orderInfo['address'])) {
        $orderInfo['address'] == '收货人地址';
    }
    if (empty($orderInfo['postcode'])) {
        $orderInfo['postcode'] == '123456';
    }
    if (empty($orderInfo['phone'])) {
        $orderInfo['postcode'] == '13312341234';
    }
    $receive_name    = $orderInfo['ordname']; //收货人姓名，如：张三
    $receive_address = $orderInfo['address']; //收货人地址，如：XX省XXX市XXX区XXX路XXX小区XXX栋XXX单元XXX号
    $receive_zip     = $orderInfo['postcode']; //收货人邮编，如：123456
    $receive_phone   = "0571-88888888"; //收货人电话号码，如：0571-81234567
    $receive_mobile  = $orderInfo['phone']; //收货人手机号码，如：13312341234
    //$parameter['service']='trade_create_by_buyer';
    $parameter['logistics_fee']     = $orderInfo['freight'];
    $parameter['logistics_type']    = 'EXPRESS';
    $parameter['logistics_payment'] = $orderInfo['logistics_payment'];
    $parameter['receive_name']      = $receive_name;
    $parameter['receive_address']   = $receive_address;
    $parameter['receive_zip']       = $receive_zip;
    $parameter['receive_phone']     = $receive_phone;
    $parameter['receive_mobile']    = $receive_mobile;
}
//print_r($parameter);
//die();
$alipayService = new AlipayService($aliapy_config);
$html_text     = $alipayService->alipayForm($parameter);
echo '<title>页面跳转中...</title>';
echo $html_text;


