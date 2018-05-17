<?php

/**
 * Plugin Name: SmilePay_Ezcat
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay Ezcat Pay (Payment)
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */

if(!defined("SMILEPAY_RESPONSE_DECLARE"))
{
    include "smilepay_respond.php";
}

add_action('plugins_loaded', 'SmilePayezcat_gateway_init', 80);



function SmilePayezcat_gateway_init() {
   if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

  

    class WC_SmilePayezcat extends WC_Payment_Gateway {
        public $title;
        public $description;
        public $dcvc;
        public $Rvg2c;
        public $Deadline_date;
        public $Order_OKmain;
        public $reurl;
        public $Verify_key;
       
        protected  static $init_action=false;


        public function __construct() {
            $this->id = 'smilepayezcat';
            $this->icon = apply_filters('woocommerce_SmilePayezcat_icon', plugins_url('log/ezcat.png', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('SmilePayezcat', 'woocommerce');
            // Load the form fields.
            $this->init_form_fields();
            // Load the settings.
            $this->init_settings();
			
            // Define user set variables
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->dcvc = $this->settings['dcvc'];
            $this->Rvg2c =  $this->settings['Rvg2c'];
			$this->Verify_key = $this->settings['Verify_key'];
			$this->Order_OKmain = $this->settings['Order_OKmain'];
			//$this->reurl = $this->settings['reurl'];
            $this->reurl =get_option('siteurl')."/?smilepay_respond";
            // Actions
            //add_action('init', array(&$this, 'check_SmilePayezcat_response'));
            
              
            if(!self::$init_action)
            {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action('woocommerce_thankyou_'.$this->id, array($this, 'thankyou_page')); 
                self::$init_action= true;
            }
           



            
        }
        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        public function init_form_fields() {  //後台設置欄位
		//	$urll=get_option('siteurl')."/?respond";
            $this->form_fields = array(
                'enabled' => array(
                    'title' => "啟用/關閉",
                    'type' => 'checkbox',
                    'label' => '速買配 黑貓貨到付現',
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => '標題',
                    'type' => 'text',
                    'description' =>'顧客在結帳時所顯示的付款方式和配送方式標題',
                    'default' => '速買配 黑貓貨到付現'
                ),
                'description' => array(
                    'title' =>'付款方式說明',
                    'type' => 'textarea',
                    'description' =>'顧客在選擇付款方式時所顯示的介紹文字',
                    'default' => "速買配 黑貓貨到付現 繳費"
                ),
                'dcvc' => array(
                    'title' => '商家代號',
                    'type' => 'text',
                    'description' =>'請填入您SmilePay商店代號',
                    'default' =>''
                ),
                'Rvg2c' => array(
                    'title' => '商家參數碼',
                    'type' => 'text',
                    'description' => '請填入您SmilePay商家參數碼',
                    'default' => ''
                ),
                'Verify_key' => array(
                    'title' => '商家檢查碼',
                    'type' => 'text',
                    'description' => '請填入您SmilePay商家檢查碼，檢查碼於商家後台「背景取號API」頁面中，請複製並貼入上方欄位',
                    'default' => ''
                ),				
                'Mid_smilepay' => array(
                    'title' => '商家驗證參數',
                    'type' => 'text',
                    'description' =>'請填入您SmilePay驗證碼，驗證碼於商家後台「基本資料管理」頁面中，請複製並貼入上方欄位，如不需驗證請保留空白',
                    'default' => ''
                ),				
				'Order_OKmain' => array(
                    'title' => '訂單成立後顯示訊息',
                    'type' => 'textarea',
                    'description' => '訂單成立顯示訊息',
                    'default' => ''
                ),
				
				'hiddtext' => array(
                    'title' => '黑貓貨到付現注意事項',
                    'type' => 'hidden',
                    'description' => '',
                    'default' =>''
                ),            
			);
        }
        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @access public
         * @return void
         */
       public function get_SmilePayezcat_args($order) {
            global $woocommerce;

            $paymethod = 'ezcat';
            $order_id = $order->get_id();
			$post_status = $order->post_status;


            $SmilePayezcat_args = array(
                "dcvc" => $this->dcvc,
                'Rvg2c' => $this->Rvg2c,
                "Verify_key" => $this->Verify_key,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
				"post_status" => $post_status,
				'Order_OKmain' => $this->Order_OKmain,
				'reurl' => $this->reurl,
				"amt" => round($order->get_total()),
            );
            $SmilePayezcat_args = apply_filters('woocommerce_SmilePayezcat_args', $SmilePayezcat_args);
            return $SmilePayezcat_args;
        }
		

       public function thankyou_page($order_id) {  //接收回傳參數驗證  與   ezcat取號
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
            $order = new WC_Order($order_id);
			// $order = &new WC_Order($order_id);

			$SmilePayezcat_args = $this->get_SmilePayezcat_args($order);
			$order_status=$SmilePayezcat_args['post_status'];
            $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();

           
            //重新結帳
            $smilepay_credit_pay_for_order =  WC()->session->get( 'smilepay_credit_pay_for_order');

		if($order_status=='wc-pending' ||$smilepay_credit_pay_for_order)
		{	
			
			$order->update_status('on-hold');		
			//根據當下頁面調整reurl是http或https
            /*if(is_ssl())
                $SmilePayezcat_args['reurl']=str_replace('http://','https://',$SmilePayezcat_args['reurl']);
            else
                $SmilePayezcat_args['reurl']=str_replace('https://','http://',$SmilePayezcat_args['reurl']);	
			//開啟ezcat
			if($SmilePayezcat_args['reurl']!=''){$Vk=$SmilePayezcat_args['reurl']."=".$order->get_order_key();}else{$Vk='';}
            

            if(is_ssl())
                $URL = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            else
			    $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            
            

           
            if($order->has_shipping_method(WC_Smilepayezcatp_Shipping_Method::SERVICE_TEMPERATURE_NORMAL_ID))
                $temperature="0001";
            elseif($order->has_shipping_method(WC_Smilepayezcatp_Shipping_Method::SERVICE_TEMPERATURE_FRIDGE_ID))
                $temperature="0002";
            elseif($order->has_shipping_method( WC_Smilepayezcatp_Shipping_Method::SERVICE_TEMPERATURE_FREEZE_ID))
                $temperature="0003";
            else
                $temperature="0001";

            
            $address = "";
            $address .= trim($order->get_shipping_city());
            $address .= trim($order->get_shipping_state());
            $address .= trim($order->get_shipping_address_1());
            $address .= trim($order->get_shipping_address_2());
            
            //<SmilePay>item add start
            /*$products="";
            foreach ($order->get_items() as $key => $lineItem) {
                $products .= $lineItem['name'] ."*" .$lineItem['quantity'] .",";
            
            }
            $products=trim($products,",");*/
            //<SmilePay>item add end
			/*$smilepay_gateway = 'https://ssl.smse.com.tw/api/sppayment.asp';
			$post_strall='Dcvc=' . $SmilePayezcat_args['dcvc'] .
						'&Pay_zg=81' .
                        '&Pay_subzg=TCAT' .
						'&Rvg2c=' .  $SmilePayezcat_args['Rvg2c'] .
                        '&Verify_key=' . $SmilePayezcat_args['Verify_key'].
						'&Od_sob=' .$SmilePayezcat_args['od_sob'].
						'&Pur_name=' .urlencode($pur_name ).
						'&Mobile_number=' .urlencode($order->get_billing_phone()).
						'&Email=' .urlencode($order->get_billing_email()).
						'&Data_id=' . $SmilePayezcat_args['od_sob'].
						'&Amount=' . $SmilePayezcat_args['amt'].
						'&Roturl_status=' . 'woook'.
						'&Roturl=' . urlencode($Vk).
						//'&MapRoturl=' . urlencode($URL).
                        "&temperature=". $temperature .
                        '&Address='. urlencode($address) .
						'&Remark=woocommerce';
                        //<SmilePay>item add start
					    //'&Remark='. $products;
                        //<SmilePay>item add end
            
            //重新結帳
            if( $smilepay_credit_pay_for_order)
            {
                WC()->session->__unset('smilepay_credit_pay_for_order');
            }
            
            $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $smilepay_gateway);
			curl_setopt($ch , CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_strall);
            
			$string = curl_exec($ch);
			curl_close($ch);
            $xml = simplexml_load_string($string);
			$Status = $xml->Status;
            
            if($xml === false)
            {
                $ordertext='速買配回應非預期格式';
                $order->add_order_note($ordertext,"0");
               // echo "<span style='color:red;'>" .'錯誤，未如預期，請與廠商聯繫'."</span>";
               // $order->update_status('cancelled');
            }
            
            if(!$string)
            {
                $ordertext= "<span style='color:red;'>" .'連線未如預期，請稍後嘗試，若持續無法解決請與廠商聯繫'."</span>";
                echo  $ordertext;
                $order->add_order_note($ordertext,"0");
                $order->update_status('cancelled');
            }
			elseif(strpos($string,"<SmilePay>")&&$Status=="1")
			{
                $T_Smseid = $xml->SmilePayNO;
                $ordertext = "速買配黑貓貨到付現<br>請勿刪除此資訊，速買配SmilePay追蹤碼：".$T_Smseid;
                //update_post_meta( $order_id, 'smilepay_ezcatp_smseid', $T_Smseid )	;

                $order->add_order_note($ordertext,"0");
                $order->update_status('on-hold');		
            }
            else
            {
                $T_Smseid = $xml->SmilePayNO;
                $ordertext = "訂單成立失敗，錯誤碼：".$xml->Status ."錯誤描述：".$xml->Desc;
                $order->add_order_note($ordertext,"0");
                $order->update_status('cancelled');
                $error_message= "<span style='color:red;'>" .  $this->title . "訂單成立失敗，請與商家聯繫" ."</span>";
                $order->add_order_note($error_message,"1");
                echo  $error_message;
            }*/

                			//訂單變成失敗中			
				
			//header("Location: $cardurl"); 
			//exit();	
		}		
		/*if($order_status=='wc-on-hold'&&$_REQUEST['Classif']=='T')
		{
			$T_Smseid=$_REQUEST['Smseid'];
			$T_Storename=b2u2($_REQUEST['Storename']);
			$T_Storeid=b2u2($_REQUEST['Storeid']);
            $T_Storeaddress = $_REQUEST['Storeaddress'];
            $title =b2u2( $this->title);



			$pay_ok="繳費方式：<font color=red>$title</font><br>取貨門市：<font color=red>".$T_Storename.$T_Storeid."</font><br>";
            $pay_ok .= "門市地址：<font color=red>".b2u2($T_Storeaddress)."</font><br>";
            $pay_ok .= "取貨人姓名：<font color=red>".b2u2($pur_name )."</font><br>";
            $pay_ok .= "取貨人電話：<font color=red>".b2u2($order->get_billing_phone())."</font><br>";
			
            $order->add_order_note(u2b2($pay_ok),"1");
			$ordertext="請勿刪除此資訊，速買配SmilePay追蹤碼：".$T_Smseid;
			$order->add_order_note(u2b2($ordertext),"0");
			echo u2b2("<font color='red' size='3'>注意：訂單已成立，待商家寄貨後，商品抵達門市將會以簡訊通知付款取貨</font><br><br>");
			echo u2b2("<h1>繳費資訊</h1>".$pay_ok);
			echo "<br><br>".$SmilePayezcat_args['Order_OKmain'];
			$cccurl="http://ssl.smse.com.tw/api/ezcatPayment.asp?smseid=".$T_Smseid."&dcvc=".$SmilePayezcat_args['dcvc']."&types=web&Verify_key=".$SmilePayezcat_args['Verify_key'];
            $cccurl_customer= get_admin_url()."post.php?Page=smilepay_ezcat_shipping_gen&order_id=$order_id&smseid=".$T_Smseid."&dcvc=".$SmilePayezcat_args['dcvc']."&Verify_key=".$SmilePayezcat_args['Verify_key'];
			//寫入訂單繳費資料-消費者
			$scvurls="點此產生交貨便服務單<br>*<a target='_blank' href='".$cccurl."'>交貨便服務單</a>*<br>*<a href='$cccurl_customer"."'>將交貨便服務單寫入備註</a>*";
			$order->add_order_note(u2b2($scvurls),"0");
            

          
		}
        else
        {
            echo "<font color='red' size='3'>如忘記<u>繳費資訊</u>請自上方選單列<u>帳號設定->訂單->檢視</u></font>";
        }*/
        echo "<br><br>".$SmilePayezcat_args['Order_OKmain'];
}
        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        public function process_payment($order_id) {
			$order = new WC_Order( $order_id );

            if(is_null($this->dcvc) || empty($this->dcvc) ||
               is_null($this->Verify_key) || empty($this->Verify_key) ||
               is_null($this->Rvg2c) || empty($this->Rvg2c)
                )
            {
                wc_add_notice("<br>速買配商家參數錯誤，請與商家聯繫<br>");
                return;
            }
			
			//$order->reduce_order_stock();
            wc_reduce_stock_levels( $order_id );
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
        }
    }
    /**
     * Add the gateway to WooCommerce
     *
     * @access public
     * @param array $methods
     * @package		WooCommerce/Classes/Payment
     * @return array
     */
    function add_SmilePayezcat_gateway($methods) {
        $methods[] = 'WC_SmilePayezcat';
        return $methods;
    }


  

    add_filter('woocommerce_payment_gateways', 'add_SmilePayezcat_gateway');


    if(is_admin())
    {
        global $pagenow;
        add_action('admin_notices', 'smilepay_ezcat_shipping_error_show' );
        
    }
}
    function smilepay_ezcat_shipping_error_show()
    {
        
        $error="";
        if(isset($_COOKIE['smilepay_ezcat_admin_shipping_error']))
        {
             $error= $_COOKIE['smilepay_ezcat_admin_shipping_error'];
             unset($_COOKIE['smilepay_ezcat_admin_shipping_error']);
             setcookie('smilepay_ezcat_admin_shipping_error', '', time() - 30 );
        }  
        if($error)
        {
            echo "<div class='notice notice-error'><p>$error</p></div>";
        }

    }




    function smilepay_ezcat_shipping_gen()
    {
        
        if(
            !isset($_GET['smseid'])  || !$_GET['smseid'] ||
            !isset($_GET['dcvc'])  || !$_GET['dcvc'] ||
            !isset($_GET['Verify_key'])  || !$_GET['Verify_key'] ||
            !isset($_GET['order_id'])  || !$_GET['order_id'] 
        )
        {
            setcookie("smilepay_ezcat_admin_shipping_error",  u2b2("錯誤,參數錯誤"), time()+30);
            wp_redirect($order_url);
            exit;
        }

        $order_id = $_GET['order_id'];
        $order_url =  get_admin_url()."post.php?post=$order_id&action=edit";
        include_once('smilepayezcatp.php');
        if(!class_exists('WC_Smilepayezcatp_Shipping_Method'))
            Smilepayezcatp_shipping_method_init();
        $smilepayezcatp_obj=new WC_Smilepayezcatp_Shipping_Method();
        if(! $smilepayezcatp_obj->isSelectedSmilepayezcatp($order_id))
        {
          
            setcookie("smilepay_ezcat_admin_shipping_error",  u2b2("錯誤,未找到該訂單"), time()+30);
            wp_redirect($order_url);
            exit;
            
        }

       


        $cccurl="https://ssl.smse.com.tw/api/ezcatPayment.asp";
        $postdata="types=xml&smseid=".$_GET['smseid']."&dcvc=".$_GET['dcvc']."&Verify_key=".$_GET['Verify_key'];

        $ch1 = curl_init();
	    curl_setopt($ch1, CURLOPT_URL, $cccurl);
	    curl_setopt($ch1, CURLOPT_VERBOSE, 1);
	    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
	    curl_setopt($ch1, CURLOPT_POST, 1);
	    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch1, CURLOPT_POSTFIELDS, $postdata);
	    $string_1 = curl_exec($ch1);
	    curl_close($ch1);
	    $xmls = simplexml_load_string($string_1);
	    $Status_1 = $xmls->Status;

        if( empty($xmls))
        {
            setcookie("smilepay_ezcat_admin_shipping_error", u2b2("取號錯誤"), time()+30);
            wp_redirect($order_url);
            exit;
        }


	    if($Status_1=="1")
	    {
             $order = new WC_Order($order_id);

             $notes=$order->get_customer_order_notes();
        
             $comment = u2b2("交貨便號碼：".$xmls->paymentno . $xmls->validationno."<br>*<a target='_blank' href='https://eservice.7-11.com.tw/E-Tracking/search.aspx'>查詢頁面</a>*");
            
             foreach($notes as $note)
             {
                 if($note->comment_content == $comment)
                 {    
                    setcookie("smilepay_ezcat_admin_shipping_error", u2b2("已經產生該備註"), time()+30);
                    wp_redirect($order_url);
                    exit;
                    return;
                 }
                   

             }
           
             $order->add_order_note($comment,"1");
          //   do_action( 'smilepay_ezcat_shippingpay_create_shipping_trick',$order_id, $xmls->paymentno . $xmls->validationno, $_GET['smseid'] );
             wp_redirect($order_url);
             exit;
        }
        else
        {
            setcookie("smilepay_ezcat_admin_shipping_error", u2b2(sprintf("錯誤,錯誤代碼:%s",$xmls->Status)), time()+30);
            wp_redirect($order_url);
            exit;
        }
      
    }



?>