<?php
session_start();
require_once('cls.paypal_service.php');
require_once('cfg.paypal.php');
$p = $ws_payto_para;
unset ($ws_payto_para);
$AMT = $p['price'] * $p['num'];
!empty($p['showurl']) || $p['showurl'] = get_bloginfo('url');
$parameter     = array(
    //''			=> '',
    //商品名
    'L_NAME0'    => $p['name'],
    //数量
    'L_QTY0'     => $p['num'],
    //商品价格
    'L_AMT0'     => $p['price'],
    //必须*总费用,包括运费和税金。必须带有两位小数，小数点必须是英文句号(.)，可选的千位分割符必须是英文逗号(,)
    'AMT'        => $AMT,
    //必须*该参数的值为客户确认订单和付款 或者结算协议的最终查看页面。
    'RETURNURL'  => $returnURL,
    //必须*付款或签订结算协议的最初页面。
    'CANCELURL'  => $p['showurl'],
    //交易币种
    //'CURRENCYCODE'		=> 'USD',
    //交易类型,默认Sale
    //'PAYMENTACTION'		=> 'Sale',
    //买家贝宝账号
    //'EMAIL'			=> '',
    //物品描述
    'DESC'       => $p['desc'],
    //备用字段<256
    //'CUSTOM'			=> '',
    //订单号
    'INVNUM'     => $p['ordno'], //'20111107712362565',
    //送货地址是否可信,1/0,默认0
    //'REQCONFIRMSHIPPING'			=> '',
    //不启用物流,默认 1/0,默认0,1表示无物流
    'NOSHIPPING' => '1',
    //覆盖贝宝默认送货地址,0/1,默认0,不覆盖
    //'ADDROVERRIDE'			=> '',
    //时间标记,您凭此向PayPal表明自己正通过"快速结账" 否功能处理这笔付款。该标记三小时后失效。请求和响应需相同
    //'TOKEN'			=> '',
    //页面语言
    //'LOCALECODE'			=> 'US',
    //页面样式,与官网上设置的名字相同
    //'PAGESTYLE'			=> '',
    //付款页面LOGO,建议为SSL服务器中的图片
    //'HDRIMG'			=> '',
    //设置付款页面标题周围的边框颜色,默认黑色,#123456
    //'HDRBORDERCOLOR'			=> '',
    //付款页面标题背景颜色,默认白色,#123456
    //'HDRBACKCOLOR'			=> '',
    //付款页面背景颜色,默认白色,#123456
    //'PAYFLOWCOLOR'			=> '',
    //渠道类型,Merchant：非竞拍卖家,eBayItem：eBay竞拍
    //'CHANNELTYPE'			=> '',
    //结账流程,Sole：用于竞拍的"快速结账",Mark：普通"快速结账"
    //'SOLUTIONTYPE'			=> '',
    //
    //'SHIPTONAME'			=> '',
    //
    //'SHIPTOSTREET'			=> '',
    //
    //'SHIPTOCITY'			=> '',
    //
    //'SHIPTOSTATE'			=> '',
    //国家或地区代码
    //'SHIPTOCOUNTRYCODE'			=> '',
    //
    //'SHIPTOZIP'			=> '',
    //
    //'SHIPTOSTREET2'			=> '',
    //
    //'PHONENUM'			=> '',
    //
);
$PaypalService = new PaypalService($paypal_config);
$html_text     = $PaypalService->paypalForm($parameter);
echo $html_text;


