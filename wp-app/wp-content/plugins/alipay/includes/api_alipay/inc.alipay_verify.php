<?php
header('content-type:text/html;charset=utf-8');
include_once('cls.alipay_service.php');
$aliapy_config['partner']   = '2088202561898065';
$aliapy_config['key']       = '';
$aliapy_config['sign_type'] = 'MD5';
$parameter['service']       = 'create_direct_pay_by_user';
$parameter['partner']       = trim($aliapy_config['partner']);
$alipayService              = new AlipayService($aliapy_config);
$DOM                        = $alipayService->alipayGetXML($parameter);
$html                       = @$DOM->getElementsByTagName('html')->item(0)->nodeValue;
$err                        = array();
$err['ILLEGAL_PARTNER']     = '合作者身份不合法';
$err['ILLEGAL_SIGN']        = '合作者密钥不合法';
//ILLEGAL_SERVICE
foreach ($err as $key => $val) {
    if (strpos($html, $key)) {
        die($val);
    }
}
echo '验证通过';

