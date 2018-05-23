<?php
require_once("../fnc.api_core.php");
if (!class_exists('AlipayNotify')):
    class AlipayNotify
    {

        //-----------------------请勿修改以上内容------------------------------
        //支付宝密钥设置:
        private $key = '';

        //-----------------------请勿修改以下内容------------------------------
        var $https_verify_url = 'https://www.alipay.com/cooperate/gateway.do?service=notify_verify&';

        var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

        var $aliapy_config;

        function __construct($aliapy_config)
        {
            $this->aliapy_config = $aliapy_config;
        }

        function AlipayNotify($aliapy_config)
        {
            $this->__construct($aliapy_config);
        }

        //检测密钥是否已经安全
        function isKeySet()
        {
            return (empty($this->key)) ? false : true;
        }

        /**
         * 设置密钥, 优先使用数据库中的密钥
         */
        private function setKey($config)
        {
            if (!empty($config['key'])) {
                $this->key = $config['key'];
            }
        }

        /**
         * 针对notify_url验证消息是否是支付宝发出的合法消息
         *
         * @return 验证结果
         */
        function verifyNotify()
        {
            if (empty($_POST)) { //判断POST来的数组是否为空
                return false;
            } else {
                //生成签名结果
                $mysign = $this->getMysign($_POST);
                //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
                $responseTxt = 'true';
                if (!empty($_POST["notify_id"])) {
                    $responseTxt = $this->getResponse($_POST["notify_id"]);
                }
                //验证
                //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
                //mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
                if (preg_match("/true$/i", $responseTxt) && $mysign == $_POST["sign"]) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        /**
         * 针对return_url验证消息是否是支付宝发出的合法消息
         *
         * @return 验证结果
         */
        function verifyReturn()
        {
            if (empty($_GET)) { //判断POST来的数组是否为空
                return false;
            } else {
                //生成签名结果
                $mysign = $this->getMysign($_GET);
                //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
                $responseTxt = 'true';
                if (!empty($_GET["notify_id"])) {
                    $responseTxt = $this->getResponse($_GET["notify_id"]);
                }
                //写日志记录
                //$log_text = "responseTxt=".$responseTxt."\n notify_url_log:sign=".$_GET["sign"]."&mysign=".$mysign.",";
                //$log_text = $log_text.createLinkString($_GET);
                //logResult($log_text);
                //验证
                //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
                //mysign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
                if (preg_match("/true$/i", $responseTxt) && $mysign == $_GET["sign"]) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        /**
         * 根据反馈回来的信息，生成签名结果
         * @param $para_temp 通知返回来的参数数组
         *
         * @return 生成的签名结果
         */
        function getMysign($para_temp)
        {
            //除去待签名参数数组中的空值和签名参数
            $para_filter = ws_alipay_paraFilter($para_temp);
            //对待签名参数数组排序
            $para_sort = ws_alipay_argSort($para_filter);
            //设置密钥
            $this->setKey($this->aliapy_config);
            //生成签名结果
            //$mysign = buildMysign($para_sort, trim($this->aliapy_config['key']), strtoupper(trim($this->aliapy_config['sign_type'])));
            $mysign = $this->buildMysign(
                $para_sort,
                trim($this->key),
                strtoupper(trim($this->aliapy_config['sign_type']))
            );
            return $mysign;
        }

        /**
         * 生成签名结果
         * @param $sort_para 要签名的数组
         * @param $key       财付通交易安全校验码
         * @param $sign_type 签名类型 默认值：MD5
         *                   return 签名结果字符串
         */
        private function buildMysign($sort_para, $key, $sign_type = "MD5")
        {
            //把数组所有元素，按照"参数=参数值"的模式用"&"字符拼接成字符串
            $prestr = ws_alipay_createLinkstring($sort_para);
            //把拼接后的字符串再与安全校验码直接连接起来
            $prestr = $prestr . $key;
            //把最终的字符串签名，获得签名结果
            $mysgin = $this->sign($prestr, $sign_type);
            return $mysgin;
        }

        /**
         * 签名字符串
         * @param $prestr    需要签名的字符串
         * @param $sign_type 签名类型 默认值：MD5
         *                   return 签名结果
         */
        private function sign($prestr, $sign_type = 'MD5')
        {
            $sign = '';
            if ($sign_type == 'MD5') {
                $sign = md5($prestr);
            } elseif ($sign_type == 'DSA') {
                //DSA 签名方法待后续开发
                die("DSA 签名方法待后续开发，请先使用MD5签名方式");
            } else {
                die("财付通暂不支持" . $sign_type . "类型的签名方式");
            }
            return $sign;
        }

        /**
         * 获取远程服务器ATN结果,验证返回URL
         *
         * @param $notify_id 通知校验ID
         *
         * @return 服务器ATN结果
         * 验证结果集：
         * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
         * true 返回正确信息
         * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
         */
        function getResponse($notify_id)
        {
            $transport  = strtolower(trim($this->aliapy_config['transport']));
            $partner    = trim($this->aliapy_config['partner']);
            $veryfy_url = '';
            if ($transport == 'https') {
                $veryfy_url = $this->https_verify_url;
            } else {
                $veryfy_url = $this->http_verify_url;
            }
            $veryfy_url  = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
            $responseTxt = ws_alipay_getHttpResponse($veryfy_url);
            return $responseTxt;
        }
    }

endif;
