<?php
if (!defined('ABSPATH')) {
    die();
}
require_once('cls.alipay_service.php');
//订单号
$trade_no = $trade_no;
//物流公司名称
$logistics_name = 'YUANTONG';
//物流发货单号
$invoice_no = '';
//物流发货时的运输类型，三个值可选：POST（平邮）、EXPRESS（快递）、EMS（EMS）
$transport_type = 'EXPRESS';
$parameter      = array(
    "service"        => "send_goods_confirm_by_platform",
    "partner"        => trim($aliapy_config['partner']),
    "_input_charset" => WS_ALIPAY_CHARSET,
    "trade_no"       => $trade_no,
    "logistics_name" => $logistics_name,
    "invoice_no"     => $invoice_no,
    "transport_type" => $transport_type
);
$alipayService  = new AlipayService($aliapy_config);
$doc            = $alipayService->send_goods_confirm_by_platform($parameter);
$response       = '';
if (!empty($doc->getElementsByTagName("is_success")->item(0)->nodeValue)) {
    //$response= $doc->getElementsByTagName( "response" )->item(0)->nodeValue;
    $response = $doc->getElementsByTagName("is_success")->item(0)->nodeValue;
    //$response= $doc->getElementsByTagName( "response" )->item(0);
}
//echo $response;
//die();
if ($response == 'T') {
    $send_flag_success = true;
} else {
    $send_flag_success = false;
}

