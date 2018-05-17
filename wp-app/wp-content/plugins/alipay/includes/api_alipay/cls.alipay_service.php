<?php


require_once("cls.alipay_submit.php");
class AlipayService
{

    var $aliapy_config;

    var $alipay_gateway = 'https://mapi.alipay.com/gateway.do?';

    function __construct($aliapy_config)
    {
        $this->aliapy_config = $aliapy_config;
    }

    function AlipayService($aliapy_config)
    {
        $this->__construct($aliapy_config);
    }

    /**
     * 构造即时到帐接口
     * @param $para_temp 请求参数数组
     *
     * @return 表单提交HTML信息
     */
    function alipayForm($para_temp)
    {
        //生成表单提交HTML文本信息
        $alipaySubmit = new AlipaySubmit();
        $html_text    = $alipaySubmit->buildForm($para_temp, $this->alipay_gateway, "get", $this->aliapy_config);
        return $html_text;
    }

    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
     * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
     */
    function query_timestamp()
    {
        $url         = $this->alipay_gateway_new . "service=query_timestamp&partner=" . trim(
              $this->aliapy_config['partner']
          );
        $encrypt_key = "";
        $doc         = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
        $encrypt_key     = $itemEncrypt_key->item(0)->nodeValue;
        return $encrypt_key;
    }

    /**
     * 构造支付宝其他接口
     * @param $para_temp 请求参数数组
     *
     * @return 表单提交HTML信息/支付宝返回XML处理结果
     */
    function alipay_interface($para_temp, $redi_html = '')
    {
        //获取远程数据
        $alipaySubmit = new AlipaySubmit();
        $html_text    = $alipaySubmit->buildForm(
            $para_temp,
            $this->alipay_gateway_new,
            "get",
            $redi_html,
            $this->aliapy_config
        );
        return $html_text;
    }

    /**
     *
     * @param $para_temp 请求参数
     *
     * @return DOM
     *
     */
    function alipayGetXML($para_temp)
    {
        //获取远程数据
        $alipaySubmit = new AlipaySubmit();
        //2.构造模拟远程HTTP的POST请求，获取支付宝的返回XML处理结果:
        //注意：若要使用远程HTTP获取数据，必须开通SSL服务，该服务请找到php.ini配置文件设置开启，建议与您的网络管理员联系解决。
        $html_text = $alipaySubmit->sendPostInfo($para_temp, $this->alipay_gateway, $this->aliapy_config);
        return $html_text;
    }

    function send_goods_confirm_by_platform($para_temp)
    {
        //获取支付宝的返回XML处理结果
        $alipaySubmit = new AlipaySubmit();
        $myconfig     = $this->aliapy_config;
        unset($myconfig['seller_email']);
        unset($myconfig['return_url']);
        unset($myconfig['notify_url']);
        $html_text = $alipaySubmit->sendPostInfo($para_temp, $this->alipay_gateway, $myconfig);
        return $html_text;
    }
}

