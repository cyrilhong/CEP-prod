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
//require_once( WS_ALIPAY_INC . 'cfg.config.php' );
$dirParent = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
require_once($dirParent . 'cfg.config.php');
//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者id，
$aliapy_config['partner'] = ws_alipay_get_setting('alipay_partnerid');
//安全检验码
$aliapy_config['key'] = ws_alipay_get_setting('alipay_key');
//签约支付宝账号或卖家支付宝帐户
$aliapy_config['seller_email'] = ws_alipay_get_setting('alipay_account');
//页面跳转同步通知页面路径
$aliapy_config['return_url'] = WS_ALIPAY_URL . '/includes/api_alipay/inc.alipay_return.php';
//服务器异步通知页面路径，要用 http://格式的完整路径，不允许加?id=123这类自定义参数
$aliapy_config['notify_url'] = WS_ALIPAY_URL . '/includes/api_alipay/inc.alipay_notify.php';
//签名方式 不需修改
$aliapy_config['sign_type'] = 'MD5';
//字符编码格式 目前支持 gbk 或 utf-8
$aliapy_config['input_charset'] = WS_ALIPAY_CHARSET;
//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$aliapy_config['transport'] = 'http';

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
