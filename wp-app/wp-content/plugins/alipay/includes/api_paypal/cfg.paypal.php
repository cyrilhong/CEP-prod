<?php
//┴┬┴┬／￣＼＿／￣＼
//┬┴┬┴▏　　▏▔▔▔▔＼
//┴┬┴／＼　／　　　　　　﹨
//┬┴∕　　　　　　　／　　　）
//┴┬▏　　　　　　　　●　　▏
//┬┴▏　　　　　　　　　　　▔█◤
//┴◢██◣　　　　　　 ＼＿＿／
//┬█████◣　　　　　　　／　　　　
//┴█████████████◣
//◢██████████████▆▄
//◢██████████████▆▄
//█◤◢██◣◥█████████◤＼
//◥◢████　████████◤　　 ＼
//┴█████　██████◤　　　　　 ﹨
//┬│　　　│█████◤　　　　　　　　▏
//┴│　　　│　　　　　　　　　　　　　　�?
//┬∕　　　∕　　　　／▔▔▔＼　　　　 �?
//*∕＿＿_／﹨　　　∕　　　　　 ＼　　／＼
//┬┴┬┴┬┴�?　　 ＼_　　　　　﹨／　　�?
//┴┬┴┬┴┬�?＼＿＿＿＼　　　　 ﹨／▔＼﹨／
//require_once( WS_ALIPAY_INC . 'cfg.config.php' );
$dirParent = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
require_once($dirParent . 'cfg.config.php');
//$url= dirname( 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI']);
$returnURL      = WS_ALIPAY_URL . "/includes/api_paypal/inc.paypal_return.php?currencyCodeType=USD&paymentType=Sale";
$cancelURL      = ""; //WS_ALIPAY_URL . "/includes/api_paypal/inc.paypal.php?paymentType=Sale" ;
$paypal_gateway = 'https://www.paypal.com/webscr&';
//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//接口账号
$paypal_config['API_USERNAME'] = ws_alipay_get_setting('paypal_account');
//接口密码
$paypal_config['API_PASSWORD'] = ws_alipay_get_setting('paypal_password');
//接口密钥
$paypal_config['API_SIGNATURE'] = ws_alipay_get_setting('paypal_key');
//接网网关
$paypal_config['PAYPAL_GATEWAY'] = $paypal_gateway;
//接口端口
$paypal_config['API_ENDPOINT'] = 'https://api-3t.paypal.com/nvp';
//支付网关
$paypal_config['PAYPAL_URL'] = $paypal_gateway . 'cmd=_express-checkout&token=';
//接口版本
$paypal_config['VERSION'] = '65.1';
//成功常量
$paypal_config['ACK_SUCCESS'] = 'SUCCESS';
//警告常量
$paypal_config['ACK_SUCCESS_WITH_WARNING'] = 'SUCCESSWITHWARNING';
//返回链接
$paypal_config['RETURN_URL'] = $returnURL;
//这里当作是展示页�?
$paypal_config['CANCEL_URL'] = $cancelURL;
//异步通知页面
$paypal_config['NOTIFY_URL'] = WS_ALIPAY_URL . "/includes/api_paypal/inc.paypal_notify.php";

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
