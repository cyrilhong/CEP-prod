<?php
//die('OUT OF SERVICE');
require_once('cfg.alipay.php');
require_once('cls.alipay_service.php');
$parameter = array(
    "service"            => "create_direct_pay_by_user",
    //"service"			=> "create_partner_trade_by_buyer",
    "payment_type"       => "4",
    "partner"            => trim($aliapy_config['partner']),
    "_input_charset"     => trim(strtolower($aliapy_config['input_charset'])),
    "seller_email"       => trim($aliapy_config['seller_email']),
    "return_url"         => trim($aliapy_config['return_url']),
    "notify_url"         => trim($aliapy_config['notify_url']),
    //############################################################################
    'quantity'           => 1,
    'price'              => 0.01,
    //############################################################################
    "out_trade_no"       => '9999999999999999999',
    "subject"            => '0.01',
    "body"               => 'foo',
    //"total_fee"			=> $total_fee,
    "paymethod"          => '',
    "defaultbank"        => '',
    "anti_phishing_key"  => '',
    "exter_invoke_ip"    => '',
    "show_url"           => '',
    "extra_common_param" => '',
    "royalty_type"       => '',
    "royalty_parameters" => ''
);
$urler     = new AlipaySubmit();
$arr_para  = $urler->buildRequestPara($parameter, $aliapy_config);
//print_r( $arr_para );
$gateway = 'https://mapi.alipay.com/gateway.do?';
foreach ($arr_para as $key => $val) {
    $gateway .= "&$key=$val";
}
echo esc_html($gateway);

