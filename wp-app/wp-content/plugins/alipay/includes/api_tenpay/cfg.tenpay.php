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
//┴│　　　│　　　　　　　　　　　　　　▏
//┬∕　　　∕　　　　／▔▔▔＼　　　　 ∕
//*∕＿＿_／﹨　　　∕　　　　　 ＼　　／＼
//┬┴┬┴┬┴＼ 　　 ＼_　　　　　﹨／　　﹨
//┴┬┴┬┴┬┴ ＼＿＿＿＼　　　　 ﹨／▔＼﹨／▔＼
//date_default_timezone_set('PRC');
//$spname="财付通双接口测试";
//$partner = "";                                  	//财付通商户号
//$key = "";											//财付通密钥
//$return_url = "http://*/payReturnUrl.php";			//显示支付结果页面,*替换成payReturnUrl.php所在路径
//$notify_url = "http://*/payNotifyUrl.php";			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
$dirParent = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
require_once($dirParent . 'cfg.config.php');
require_once($dirParent . 'fnc.api_core.php');
//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
$tenpay_config['partner']    = ws_alipay_get_setting('tenpay_partnerid');
$tenpay_config['key']        = ws_alipay_get_setting('tenpay_key');
$tenpay_config['cs']         = WS_ALIPAY_CHARSET;
$tenpay_config['return_url'] = WS_ALIPAY_URL . "/includes/api_tenpay/inc.tenpay_return.php";
$tenpay_config['notify_url'] = WS_ALIPAY_URL . "/includes/api_tenpay/inc.tenpay_notify.php";

