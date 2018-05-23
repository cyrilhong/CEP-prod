<?php
require_once(WS_ALIPAY_INC . 'fnc.api_core.php');
if (!class_exists('AlipaySubmit')):

    class AlipaySubmit
    {

        //-----------------------请勿修改以上内容------------------------------
        //支付宝密钥设置:
        private $key = '';

        //-----------------------请勿修改以下内容-----------------------------
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
         * 生成要请求给支付宝的参数数组
         * @param $para_temp     请求前的参数数组
         * @param $aliapy_config 基本配置信息数组
         *
         * @return 要请求的参数数组
         */
        public function buildRequestPara($para_temp, $aliapy_config)
        {
            //除去待签名参数数组中的空值和签名参数
            $para_filter = ws_alipay_paraFilter($para_temp);
            //对待签名参数数组排序
            $para_sort = ws_alipay_argSort($para_filter);
            //设置密钥
            $this->setKey($aliapy_config);
            //生成签名结果
            //$mysign = buildMysign($para_sort, trim($aliapy_config['key']), strtoupper(trim($aliapy_config['sign_type'])));
            $mysign = $this->buildMysign($para_sort, trim($this->key), strtoupper(trim($aliapy_config['sign_type'])));
            //签名结果与签名方式加入请求提交参数组中
            $para_sort['sign']      = $mysign;
            $para_sort['sign_type'] = strtoupper(trim($aliapy_config['sign_type']));
            return $para_sort;
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
                die("暂不支持" . $sign_type . "类型的签名方式");
            }
            return $sign;
        }

        /**
         * 生成要请求给支付宝的参数数组
         * @param $para_temp     请求前的参数数组
         * @param $aliapy_config 基本配置信息数组
         *
         * @return 要请求的参数数组字符串
         */
        function buildRequestParaToString($para_temp, $aliapy_config)
        {
            //待请求参数数组
            $para = $this->buildRequestPara($para_temp, $aliapy_config);
            //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
            $request_data = ws_alipay_createLinkstring($para);
            //$request_data = createLinkstringUrlencode($para);
            return $request_data;
        }

        /**
         * 构造提交表单HTML数据
         *
         * @param $para_temp   请求参数数组
         * @param $gateway     网关地址
         * @param $method      提交方式。两个值可选：post、get
         * @param $button_name 确认按钮显示文字
         *
         * @return 提交表单HTML文本
         */
        function buildForm($para_temp, $gateway, $method, $aliapy_config)
        {
            //待请求参数数组
            $para  = $this->buildRequestPara($para_temp, $aliapy_config);
            $sHtml = '';
            $sHtml .= "<form id='alipaysubmit' name='alipaysubmit' action='" . $gateway . "_input_charset=" . trim(
                  strtolower($aliapy_config['input_charset'])
              ) . "' method='" . $method . "'>";
            while (list ($key, $val) = each($para)) {
                $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
            }
            $sHtml .= "</form>";
            $sHtml .= "<script>document.forms['alipaysubmit'].submit();</script" . ">";
            return $sHtml;
        }

        /**
         * 构造模拟远程HTTP的POST请求，获取支付宝的返回XML处理结果
         * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
         *
         * @param $para_temp     请求参数数组
         * @param $gateway       网关地址
         * @param $aliapy_config 基本配置信息数组
         *
         * @return 支付宝返回XML处理结果
         */
        function sendPostInfo($para_temp, $gateway, $aliapy_config)
        {
            $xml_str = '';
            //待请求参数数组字符串
            $request_data = $this->buildRequestParaToString($para_temp, $aliapy_config);
            //请求的url完整链接
            $url = $gateway . $request_data;
            //远程获取数据
            $xml_data = ws_alipay_getHttpResponse($url);
            //$xml_data = getHttpResponseCURL( $url );
            //die($xml_data);
            //解析XML
            $doc = new DOMDocument();
            @$doc->loadHTML($xml_data);
            return $doc;
        }
    }
endif;

