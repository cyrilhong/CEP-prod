<?php
/* *
 * 类名：PaypalService
 * 功能：paypal各接口构造类
 * 详细：构造paypal各接口请求参数
 */
$dirParent = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
require_once($dirParent . 'cfg.config.php');
require_once('cfg.paypal.php');
require_once('cls.paypal_submit.php');
if (!class_exists('PaypalService')):
    class PaypalService
    {

        //-----------------------请勿修改以上内容-----------------------------
        //Paypal密钥设置:
        private $password = '';

        private $key = '';

        //
        //-----------------------请勿修改以下内容----------------------------
        var $paypal_config;

        function __construct($paypal_config)
        {
            $this->PaypalService($paypal_config);
        }

        function paypalService($paypal_config)
        {
            $this->setPwd($paypal_config);
            $this->setKey($paypal_config);
            $this->paypal_config = $paypal_config;
        }

        /**
         * 设置密码, 优先使用数据库中的密码
         */
        private function setPwd($config)
        {
            if (!empty($config['API_PASSWORD'])) {
                $this->password = $config['API_PASSWORD'];
            }
        }

        /**
         * 设置密钥, 优先使用数据库中的密钥
         */
        private function setKey($config)
        {
            if (!empty($config['API_SIGNATURE'])) {
                $this->key = $config['API_SIGNATURE'];
            }
        }

        /**
         * 构造即时到帐接口
         * @param $para_temp 请求参数数组
         *
         * @return 表单提交HTML信息
         */
        function paypalForm($para_temp)
        {
            //验证参数,获取授权令牌
            $nvpStr = '&' . http_build_query($para_temp);
            //$ret = $this->hash_call( 'SetExpressCheckout', $nvpStr );
            //die($nvpStr);
            //header("location:{$this->paypal_config['PAYPAL_URL']}{$ret['TOKEN']}");
            //生成表单提交HTML文本信息
            $paypalSubmit = new paypalSubmit();
            $html_text    = $paypalSubmit->buildForm($para_temp, $nvpStr, $this->paypal_config);
            return $html_text;
        }

        /**
         * 构造paypal URI接口
         *
         * @param $para_temp 请求参数数组
         *
         * @return api uri
         */
        function paypal_interface($para_temp)
        {
            $nvpStr = '&' . http_build_query($para_temp);
            $ret    = $this->hash_call('SetExpressCheckout', $nvpStr);
            //$ret = $this->hash_call( 'DoExpressCheckoutPayment', $nvpStr );
            return $ret;
        }

        function hash_call($methodName, $nvpStr)
        {
            $version   = $this->paypal_config['VERSION'];
            $nvpheader = $this->nvpHeader();
            //setting the curl parameters.
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->paypal_config['API_ENDPOINT']);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            //turning off the server and peer verification(TrustManager Concept).
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            //in case of permission APIs send headers as HTTPheders
            if (!empty($AUTH_token) && !empty($this->paypal_config['API_SIGNATURE']) && !empty($AUTH_timestamp)) {
                $headers_array[] = "X-PP-AUTHORIZATION: " . $nvpheader;
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
                curl_setopt($ch, CURLOPT_HEADER, false);
            } else {
                $nvpStr = $nvpheader . $nvpStr;
            }
            //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
            //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php
            //if( USE_PROXY)
            //curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT);
            //check if version is included in $nvpStr else include the version.
            if (strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
                $nvpStr = "&VERSION=" . urlencode($version) . $nvpStr;
            }
            $nvpreq = "METHOD=" . urlencode($methodName) . $nvpStr;
            //setting the nvpreq as POST FIELD to curl
            curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
            //getting response from server
            $response = curl_exec($ch);
            //convrting NVPResponse to an Associative Array
            $nvpResArray             = $this->deformatNVP($response);
            $nvpReqArray             = $this->deformatNVP($nvpreq);
            $_SESSION['nvpReqArray'] = $nvpReqArray;
            if (curl_errno($ch)) {
                // moving to display page to display curl errors
                $_SESSION['curl_error_no']  = curl_errno($ch);
                $_SESSION['curl_error_msg'] = curl_error($ch);
                $location                   = "inc.paypal_error.php";
                header("Location: $location");
            } else {
                //closing the curl
                curl_close($ch);
            }
            return $nvpResArray;
        }

        function nvpHeader()
        {
            $API_UserName  = $this->paypal_config['API_USERNAME'];
            $API_Password  = $this->password;
            $API_Signature = $this->key;
            //$API_Password  = $this->paypal_config['API_PASSWORD'];
            //$API_Signature = $this->paypal_config['API_SIGNATURE'];
            $nvpHeaderStr = "&PWD=" . urlencode($API_Password) . "&USER=" . urlencode(
                  $API_UserName
              ) . "&SIGNATURE=" . urlencode($API_Signature);
            return $nvpHeaderStr;
        }

        function deformatNVP($nvpstr)
        {
            $intial   = 0;
            $nvpArray = array();
            while (strlen($nvpstr)) {
                //postion of Key
                $keypos = strpos($nvpstr, '=');
                //position of value
                $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);
                /*getting the Key and Value values and storing in a Associative Array*/
                $keyval = substr($nvpstr, $intial, $keypos);
                $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
                //decoding the respose
                $nvpArray[urldecode($keyval)] = urldecode($valval);
                $nvpstr                       = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
            }
            return $nvpArray;
        }

        function formAutorization($auth_token, $auth_signature, $auth_timestamp)
        {
            $authString = "token=" . $auth_token . ",signature=" . $auth_signature . ",timestamp=" . $auth_timestamp;
            return $authString;
        }

        function verifyIPN($arr_param)
        {
            $gateway   = $this->paypal_config['PAYPAL_GATEWAY'];
            $str_param = http_build_query($arr_param);
            $url       = $gateway . $str_param . '&cmd=_notify-validate';
            //$url = $gateway . $arr_param . '&cmd=_notify-validate';
            $ret = file_get_contents($url);
            return 'VERIFIED' == strtoupper(trim($ret));
        }
    }

endif;
if (isset($_REQUEST['nvpStr'])) {
    $methodName = $_REQUEST['methodName'];
    $nvpStr     = $_REQUEST['nvpStr'];
    echo ws_paypal_ajax_hash_call($methodName, $nvpStr);
}
//for ajax
function ws_paypal_ajax_hash_call($methodName, $nvpStr)
{
    global $paypal_config;
    $paypalService = new PaypalService($paypal_config);
    $ret           = $paypalService->hash_call($methodName, $nvpStr);
    $ret           = json_encode($ret);
    return $ret;
}

