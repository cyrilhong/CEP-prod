<?php
//include "smilepay_respond.php";
/**
 * Plugin Name: SmilePay_CSV 
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay CSV 
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */
if(!defined("SMILEPAY_RESPONSE_DECLARE"))
{
    include "smilepay_respond.php";
}
add_action('plugins_loaded', 'SmilePaycsv_gateway_init', 0);
function SmilePaycsv_gateway_init() {

	if( !empty($_GET['csvpage']) && $_GET['csvpage']!="")
	{
		include "csv.php";
		printpage();
	}

   if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    class WC_SmilePaycsv extends WC_Payment_Gateway {
        public $title;
        public $description;
        public $dcvc;
        public $Rvg2c;
        public $Deadline_date;
        public $Order_OKmain;
        public $reurl;
        public $Verify_key;

        public function __construct() {
            $this->id = 'smilepaycsv';
            $this->icon = apply_filters('woocommerce_SmilePaycsv_icon', plugins_url('log/code.jpg', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('SmilePaycsv', 'woocommerce');
            // Load the form fields.
            $this->init_form_fields();
            // Load the settings.
            $this->init_settings();
			
            // Define user set variables
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->dcvc = $this->settings['dcvc'];
            $this->Rvg2c =  $this->settings['Rvg2c'];
            $this->Deadline_date = $this->settings['Deadline_date'];
            $this->Order_OKmain = $this->settings['Order_OKmain'];
            $this->shop_phone = $this->settings['shop_phone'];
            //$this->reurl = $this->settings['reurl'];
            $this->reurl =get_option('siteurl')."/?smilepay_respond";
            $this->Verify_key = $this->settings['Verify_key'];
            $this->Mid_smilepay = $this->settings['Mid_smilepay'];
            // Actions
            //add_action('init', array(&$this, 'check_SmilePaycsv_response'));
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_'.$this->id, array($this, 'thankyou_page')); 
        }
        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        public function init_form_fields() {  //後台設置欄位
			//$urll=get_option('siteurl')."/?respond";
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __(u2bc("啟用/關閉"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2bc(' SmilePay 超商條碼代收'), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2bc('標題'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('顧客在結帳時所顯示的付款方式標題'), 'woocommerce'),
                    'default' => __(u2bc('SmilePay 超商條碼代收 繳費'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2bc('付款方式說明'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bc('顧客在選擇付款方式時所顯示的介紹文字'), 'woocommerce'),
                    'default' => __(u2bc("SmilePay  超商條碼代收 繳費"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2bc('商家代號'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('請填入您SmilePay商店代號'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2bc('商家參數碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('請填入您SmilePay商家參數碼'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2bc('商家檢查碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('請填入您SmilePay商家檢查碼，檢查碼於商家後台「背景取號API」頁面中，請複製並貼入上方欄位'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Mid_smilepay' => array(
                    'title' => __(u2bc('商家驗證參數'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('請填入您SmilePa驗證碼，驗證碼於商家後台「基本資料管理」頁面中，請複製並貼入上方欄位，如不需驗證請保留空白'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
				'Deadline_date' => array(
                    'title' => __(u2bc('繳款截止期限'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('繳款截止期限，預設值為50日，請填入1~50，不得大於50日'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'shop_phone' => array(
                    'title' => __(u2bc('繳費單客服專線'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('請填入客服專線'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
				'Order_OKmain' => array(
                    'title' => __(u2bc('下單成功顯示訊息'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bc('下單成功顯示訊息'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
			/*	'reurl' => array(
                    'title' => __(u2bc('交易完成回送位置'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bc('交易完成回送位置，請將下列文字複製到上方框內(如不須回送請留空白)</br><font color=red ue size=+1>'.$urll.'</font>', 'woocommerce')),
                    'default' => __('', 'woocommerce')
                )*/
            );
        }
        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @access public
         * @return void
         */
       public function get_SmilePaycsv_args($order) {
            global $woocommerce;

            $paymethod = 'csv';
            $order_id = $order->id;
			$post_status = $order->post_status;

            $SmilePaycsv_args = array(
                "dcvc" => $this->dcvc,
                'Rvg2c' => $this->Rvg2c,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
				"post_status" => $post_status,
                'reurl' => $this->reurl,
                'Verify_key' => $this->Verify_key,
                'Mid_smilepay' => $this->Mid_smilepay,
				'Deadline_date' => $this->Deadline_date,
				'shop_phone' => $this->shop_phone,
				'Order_OKmain' => $this->Order_OKmain,
				"amt" => round($order->get_total()),
            );
            $SmilePaycsv_args = apply_filters('woocommerce_SmilePaycsv_args', $SmilePaycsv_args);
            return $SmilePaycsv_args;
        }
		
       public function thankyou_page($order_id) {  //接收回傳參數驗證  與   csv取號
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
            // $order = &new WC_Order($order_id);
			$order = new WC_Order($order_id);
			
			
			
			$SmilePaycsv_args = $this->get_SmilePaycsv_args($order);

			//判斷是否取號	
			$thepostid = $SmilePaycsv_args['od_sob'];
			$order_status=$SmilePaycsv_args['post_status'];
		
		if($order_status=='wc-pending')
		{
	        //--物流start
			$shipping_method= $order->get_shipping_method();
			$getb2cu="N";
			if($_REQUEST['storeid']=='')
			{
				//if($shipping_method==u2bc("速買配7-11門市純取貨"))
                if(file_exists("smilepayc2cup.php"))
				    include_once("smilepayc2cup.php");
                if(class_exists('WC_Smilepayc2cu_Shipping_Method'))
                    $smilepay_c2cupobj= new WC_Smilepayc2cu_Shipping_Method();
                else
                    $smilepay_c2cupobj= null;
                //<b2c> start
                if(file_exists("smilepayb2cup.php"))
                    include_once("smilepayb2cup.php");
                if(class_exists('WC_Smilepayb2cu_Shipping_Method'))
                    $smilepay_b2cupobj = new WC_Smilepayb2cu_Shipping_Method();
                else
                    $smilepay_b2cupobj = null;
                //<b2c> end
                //檢查物流是否使用純取貨
				if( $smilepay_c2cupobj && $smilepay_c2cupobj->isSelectedSmilepayC2Cup($order_id))
                {
					if(is_ssl())
                        $URL = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    else
			            $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    //Mobile map start
                    $useragent=$_SERVER['HTTP_USER_AGENT'];
                    if( preg_match('/Android|iPad|iPhone/i',$useragent))
                    {
                            $mappurl="https://emap.presco.com.tw/c2cemapm-u.ashx?eshopid=870&servicetype=1&url=".urlencode($URL);
                          
                    }
                    if(!isset($mappurl))//Mobile map end
					    $mappurl="https://emap.presco.com.tw/c2cemap.ashx?eshopid=870&servicetype=1&url=".urlencode($URL);
					header("Location: $mappurl"); 
					exit();	
				}
                //<b2c> start
                elseif(  $smilepay_b2cupobj && $smilepay_b2cupobj->isSelectedSmilepayB2Cup($order_id))
                {


              
					if(is_ssl())
                        $URL = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    else
			            $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    //Mobile map start
                    //if( !is_null(WC()->session->get('smilepay_711_c2c_shippingunpay_mobile_map')) && WC()->session->get('smilepay_711_c2c_shippingunpay_mobile_map') =='yes')
                    //{
                       $useragent=$_SERVER['HTTP_USER_AGENT'];
                        if( preg_match('/Android|iPad|iPhone/i',$useragent)  )
                        {
                            $mappurl="https://emap.presco.com.tw/emapmobileu.ashx?eshopid=870&servicetype=1&tempvar=b2c&url=".urlencode($URL);
                          
                        }
                        
                    //}
                   
                    if(!isset($mappurl))//Mobile map end
					    $mappurl="https://emap.presco.com.tw/emapu8.ashx?eshopid=870&servicetype=1&tempvar=b2c&url=".urlencode($URL);
                   
					header("Location: $mappurl"); 
					exit();	
				}
                //<b2c> end
			}
			else
			{
                
                //<b2c> start
                $b2c_run=false;
                if($_REQUEST['tempvar'] == 'b2c')
                {
                    $b2c_storeaddress=$_REQUEST['address'];
				    $b2c_storename=$_REQUEST['storename'];
				    $b2c_storeid=$_REQUEST['storeid'];
                    $b2c_run = true;
                } 
                //<b2c> end
                else 
                {
                    $c2c_storeaddress=$_REQUEST['storeaddress'];
				    $c2c_storename=$_REQUEST['storename'];
				    $c2c_storeid=$_REQUEST['storeid'];
                }
				
				$getc2cu="Y";			
			}
			//--物流end
			if(is_numeric($SmilePaycsv_args['Deadline_date'])&&(int)$SmilePaycsv_args['Deadline_date']<51&&(int)$SmilePaycsv_args['Deadline_date']>0)
			{$Deadline_date_value=$SmilePaycsv_args['Deadline_date'];}
			else
			{$Deadline_date_value=50;}

			$Deadline_date = date("Y/m/d",mktime(0,0,0,date("m"),date("d")+$Deadline_date_value,date("Y")));
            //根據當下頁面調整reurl是http或https
            if(is_ssl())
                $SmilePaycsv_args['reurl']=str_replace('http://','https://',$SmilePaycsv_args['reurl']);
            else
                $SmilePaycsv_args['reurl']=str_replace('https://','http://',$SmilePaycsv_args['reurl']);		
          	//取號
			if($SmilePaycsv_args['reurl']!=''){$Vk=$SmilePaycsv_args['reurl']."=".$order->get_order_key();}else{$Vk='';}
		  	$pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
            $smilepay_gateway = 'https://ssl.smse.com.tw/api/sppayment.asp';

            //<SmilePay>item add start
            /*$products="";
            foreach ($order->get_items() as $key => $lineItem) {
                $products .= $lineItem['name'] ."*" .$lineItem['quantity'] .",";
            
            }
            $products=trim($products,",");*/
            //<SmilePay>item add end
			$post_str = 'Dcvc=' . $SmilePaycsv_args['dcvc'] .
					'&Rvg2c=' . $SmilePaycsv_args['Rvg2c'] .
					'&Pur_name=' .$pur_name.
					'&Tel_number=' .$order->get_billing_phone().
                    '&Mobile_number=' .$order->get_billing_phone().
					'&Address=' .$order->get_billing_address_1().
					'&Email=' .$order->get_billing_email().
					'&Data_id=' . $SmilePaycsv_args['od_sob'].
					'&od_sob=' . $SmilePaycsv_args['od_sob'].
					'&Amount=' . $SmilePaycsv_args['amt'].
					'&Deadline_date=' . $Deadline_date.
					'&Roturl_status=' . 'woook'.
					'&Verify_key=' . $SmilePaycsv_args['Verify_key'].
                    '&Remark=woocommerce';
					 //<SmilePay>item add start
					//'&Remark='. $products;
                     //<SmilePay>item add end
			
			$post_strall=$post_str.'&Pay_zg=3'.'&Roturl=' . $Vk;
					
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $smilepay_gateway);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_strall);
			$string = curl_exec($ch);
			curl_close($ch);
			
			//寫入備註欄
			$xml = simplexml_load_string($string);
			$Status = $xml->Status;
			if($Status=="1")
			{
				//寫入訂單繳費資料-消費者
                $title = b2uc($this->title);
				$scvurl="*<a target='_blank' href='".get_option('siteurl')."?csvpage=".
					$SmilePaycsv_args['od_sob'].'!'.$xml->Barcode1.'!'.$xml->Barcode2.'!'.$xml->Barcode3.'!'.
					$xml->Amount.'!'.$SmilePaycsv_args['shop_phone'].'!'.$xml->PayEndDate."'>開啟繳費單</a>*";
				$ordertext="繳費方式：<font color=red>$title</font><br><font color=red>".$scvurl."</font><br>繳費期限：<font color=red>".$xml->PayEndDate."</font></br>如超過繳費期限請勿繳費</br>";
				$order->add_order_note(u2bc($ordertext),"1");
				$ordertext="請勿刪除此資訊，速買配SmilePay追蹤碼：".$xml->SmilePayNO;
				$order->add_order_note(u2bc($ordertext),"0");
				//修改訂單狀態	
				$order->update_status( 'on-hold' );	
				//網頁顯示內容
				echo u2bc("<font color='red' size='3'>注意：訂單已成立，尚未付款，請依繳款資料盡速付款</font>");
				$showtext= "<br><br><h1>繳費資訊</h1>繳費方式：<font color='red' size='3'>$title </font></td></tr>";
				$showtext=$showtext."<br><font color='red' size='3'>".$scvurl."</font>";
				$showtext=$showtext."<br>繳費期限：<font color='red' size='3'>".$xml->PayEndDate."</font>";
				echo u2bc($showtext);
				echo u2bc("<br><font color='red' size='3'>如忘記<u>繳費資訊</u>請自上方選單列<u>帳號設定->訂單->檢視</u></font>");
				echo "<br><br>".$SmilePaycsv_args['Order_OKmain'];
				//--物流start
				if($getc2cu=="Y")
				{
                    if( $b2c_run) //<b2c> start 
                    {
                        include "smilepay_function_b2c.php";
					    $b2c_store=$b2c_storeid.'/'.$b2c_storename.'/'.$b2c_storeaddress;					
                        //<smilepay> modify address start 
                        if( !empty($b2c_storeid) && !empty($b2c_storename) && !empty($b2c_storeaddress))
                        {
                            $shipping_addr= $order->get_shipping_address_1();
                            $store_addr=$b2c_storeid ." " .$b2c_storename ." " .$b2c_storeaddress;
                            if($store_addr != $shipping_addr)
                            {
                             $order->set_shipping_address_1($store_addr);
                             $order->save();
                            }
                        }
                        //<smilepay> modify address end		
					    $scvurls=repeatb2c($b2c_store,$post_str,$SmilePayatm_args['dcvc'],$SmilePayatm_args['Verify_key']);
					    $order->add_order_note(u2bc($scvurls),"0");
					    $scvurls_1="7-11取貨門市：<font color=red>".b2uc($b2c_storename.$b2c_storeid)."</font><br>";
                        $scvurls_1 .= "門市地址：<font color=red>".b2uc($b2c_storeaddress)."</font><br>";
                        $scvurls_1 .= "取貨人姓名：<font color=red>".b2uc($pur_name)."</font><br>";
                        $scvurls_1 .= "取貨人電話：<font color=red>".b2uc($order->get_billing_phone())."</font><br>";
                        
                    }
                    else //<b2c> end
                    {
					    include "smilepay_function_c2c.php";
					    $c2c_store=$c2c_storeid.'/'.$c2c_storename.'/'.$c2c_storeaddress;
                        //<smilepay> modify address start 
                        if( !empty($c2c_storeid) && !empty($c2c_storename) && !empty($c2c_storeaddress))
                        {
                            $shipping_addr= $order->get_shipping_address_1();
                            $store_addr=$c2c_storeid ." " .$c2c_storename ." " .$c2c_storeaddress;
                            if($store_addr != $shipping_addr)
                            {
                             $order->set_shipping_address_1($store_addr);
                             $order->save();
                            }
                        }
                        //<smilepay> modify address end							
					    $scvurls=repeatc2c($c2c_store,$post_str,$SmilePaycsv_args['dcvc'],$SmilePaycsv_args['Verify_key']);
					    $order->add_order_note(u2bc($scvurls),"0");
					    $scvurls_1="7-11取貨門市：<font color=red>".b2uc($c2c_storename.$c2c_storeid)."</font><br>";
                        $scvurls_1 .= "門市地址：<font color=red>".b2uc($c2c_storeaddress)."</font><br>";
                        $scvurls_1 .= "取貨人姓名：<font color=red>".b2uc($pur_name)."</font><br>";
                        $scvurls_1 .= "取貨人電話：<font color=red>".b2uc($order->get_billing_phone())."</font><br>";
                        
                    }
					$order->add_order_note(u2bc($scvurls_1),"1");
					echo u2bc('<h1>取貨資訊</h1>' . $scvurls_1);
				}
				//--物流end
			}
			else
			{
				if($Status!="")
				{	$ordererror="取號失敗</br>錯誤代碼：".$xml->Status."</br>內容原因：".$xml->Desc."</br>記錄時間：".date("Y-m-d H:i:s",mktime(date(H)+8));	}
				else
				{	$ordererror="取號失敗</br>內容原因：連線異常</br>記錄時間：".date("Y-m-d H:i:s",mktime(date(H)+8));	}
				$order->add_order_note(u2bc($ordererror),"1");
				$order->update_status( 'cancelled' );
				echo u2bc($ordererror);			
			}
		}
		else
		{
			echo u2bc("<font color='red' size='3'>如忘記<u>繳費資訊</u>請自上方選單列<u>帳號設定->訂單->檢視</u></font>");
		}			


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
                wc_add_notice(u2bc("<br>速買配商家參數錯誤，請與商家聯繫<br>") , 'error' );
                return;
            }
			/*//--物流start
			$shipping_method= $order->get_shipping_method();
            if(file_exists('smilepayc2cp.php'))
                include_once('smilepayc2cp.php');
            if(file_exists('smilepayc2cup.php'))
                include_once('smilepayc2cup.php');
            if( class_exists('WC_Smilepayc2cp_Shipping_Method'))
                $smilepayc2cp_obj=new WC_Smilepayc2cp_Shipping_Method();
            else
                $smilepayc2cp_obj= null;
            if( class_exists('WC_Smilepayc2cu_Shipping_Method'))
                $smilepayc2cup_obj=new WC_Smilepayc2cu_Shipping_Method();
            else
                $smilepayc2cup_obj= null;

            //<b2c> start
            if(file_exists('smilepayb2cp.php'))
                include_once('smilepayb2cp.php');
            if(file_exists('smilepayb2cup.php'))
                include_once('smilepayc2cup.php');
            if( class_exists('WC_Smilepayb2cp_Shipping_Method'))
                $smilepayb2cp_obj=new WC_Smilepayb2cp_Shipping_Method();
            else
                $smilepayb2cp_obj= null;
            if( class_exists('WC_Smilepayb2cu_Shipping_Method'))
                $smilepayb2cup_obj=new WC_Smilepayb2cu_Shipping_Method();
            else
                $smilepayb2cup_obj= null;
            //<b2c> end

            if( $smilepayc2cup_obj && $smilepayc2cup_obj->isSelectedSmilepayC2Cup($order_id))
			//if($shipping_method==u2bc("速買配7-11門市純取貨"))
			{
				$username=$order->billing_first_name.$order->billing_last_name;
                if( !preg_match("/[^a-zA-Z0-9 ]/",$username))
                {
                    $limit_name_number =10;
                }
                else
                {
                    $limit_name_number =5;
                }
				if(substr($order->billing_phone,0,2)!="09"||mb_strlen($order->billing_phone)!=10||mb_strlen( $username, "utf-8")> $limit_name_number){
                    wc_add_notice( __(u2bc("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if($smilepayc2cp_obj && $smilepayc2cp_obj->isSelectedSmilepayC2Cp($order_id))
			//if($shipping_method==u2bc("速買配7-11門市取貨付款"))
			{
                    wc_add_notice( __(u2bc("<br><font color='red'>配送方式選擇錯誤，不可選擇此配送方式</font><br>"), 'woothemes') . $error_message, 'error' );
                    return;
                    exit;
            }
            //<b2c> start
            if( $smilepayb2cup_obj && $smilepayb2cup_obj->isSelectedSmilepayB2Cup($order_id))
			{
				$username=$order->billing_first_name.$order->billing_last_name;
                if( !preg_match("/[^a-zA-Z0-9 ]/",$username))
                {
                    $limit_name_number =10;
                }
                else
                {
                    $limit_name_number =5;
                }
				if(substr($order->billing_phone,0,2)!="09"||mb_strlen($order->billing_phone)!=10||mb_strlen( $username, "utf-8")>$limit_name_number){
                    wc_add_notice( __(u2bc("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if($smilepayb2cp_obj && $smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))
			{
                    wc_add_notice( __(u2bc("<br><font color='red'>配送方式選擇錯誤，不可選擇此配送方式</font><br>"), 'woothemes') . $error_message, 'error' );
                    return;
                    exit;
            }
             //<b2c> end
			//--物流end*/
			//$order->reduce_order_stock();
            wc_reduce_stock_levels( $order_id );
			WC()->cart->empty_cart();
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
        }

        /**
         * Payment form on checkout page
         *
         * @access public
         * @return void
         */

    }
    /**
     * Add the gateway to WooCommerce
     *
     * @access public
     * @param array $methods
     * @package		WooCommerce/Classes/Payment
     * @return array
     */
    function add_SmilePaycsv_gateway($methods) {
        $methods[] = 'WC_SmilePaycsv';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_SmilePaycsv_gateway');
}
function u2bc($text)//畫面輸出
{	return iconv("big5","UTF-8",$text);}
function b2uc($text)//寫入資料庫
{	return iconv("UTF-8","big5",$text);}
?>