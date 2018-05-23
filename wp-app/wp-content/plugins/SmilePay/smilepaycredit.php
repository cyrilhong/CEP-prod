<?php
//include "smilepay_respond.php";
/**
 * Plugin Name: SmilePay_Credit 
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay Credit 
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */
if(!defined("SMILEPAY_RESPONSE_DECLARE"))
{
    include "smilepay_respond.php";
}
add_action('plugins_loaded', 'SmilePaycredit_gateway_init', 0);
function SmilePaycredit_gateway_init() {
   if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_SmilePaycredit extends WC_Payment_Gateway {

        public $title;
        public $description;
        public $dcvc;
        public $Rvg2c;
        public $Order_OKmain;
        public $Verify_key;

        public function __construct() {
            $this->id = 'smilepaycredit';
            $this->icon = apply_filters('woocommerce_SmilePaycredit_icon', plugins_url('log/paycredit.jpg', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('SmilePaycredit', 'woocommerce');
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
            // Actions
            //add_action('init', array(&$this, 'check_SmilePaycredit_response'));
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
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __(u2bcr("啟用/關閉"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2bcr(' SmilePay 線上刷卡'), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2bcr('標題'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('顧客在結帳時所顯示的付款方式標題'), 'woocommerce'),
                    'default' => __(u2bcr('SmilePay 線上刷卡 繳費'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2bcr('付款方式說明'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bcr('顧客在選擇付款方式時所顯示的介紹文字'), 'woocommerce'),
                    'default' => __(u2bcr("SmilePay  線上刷卡 繳費"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2bcr('商家代號'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('請填入您SmilePay商店代號'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2bcr('商家參數碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('請填入您SmilePay商家參數碼'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2bcr('商家檢查碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('請填入您SmilePay商家檢查碼，檢查碼於商家後台「背景取號API」頁面中，請複製並貼入上方欄位'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
				'Order_OKmain' => array(
                    'title' => __(u2bcr('訂單成立後顯示訊息'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bcr('訂單成立顯示訊息'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
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
       public function get_SmilePaycredit_args($order) {
            global $woocommerce;

            $paymethod = 'credit';
            $order_id = $order->id;
			$post_status = $order->post_status;

            $SmilePaycredit_args = array(
                "dcvc" => $this->dcvc,
                'Rvg2c' => $this->Rvg2c,
                "payment_type" => $paymethod,
				"post_status" => $post_status,
                "od_sob" => $order_id,
                'Verify_key' => $this->Verify_key,				
				'Order_OKmain' => $this->Order_OKmain,
				"amt" => round($order->get_total()),
            );
            $SmilePaycredit_args = apply_filters('woocommerce_SmilePaycredit_args', $SmilePaycredit_args);
            return $SmilePaycredit_args;
        }
		
       public function thankyou_page($order_id) {  //接收回傳參數驗證  與   credit取號
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
            $order = new WC_Order($order_id);
			
			
			
			
			// $order = &new WC_Order($order_id);
			
			$SmilePaycredit_args = $this->get_SmilePaycredit_args($order);
			$order_status=$SmilePaycredit_args['post_status'];
			
            //重新結帳
            $smilepay_credit_pay_for_order =  WC()->session->get( 'smilepay_credit_pay_for_order');
        
               
		if($order_status=='wc-pending' || $smilepay_credit_pay_for_order)
		{
            //--物流start
			$shipping_method= $order->get_shipping_method();
			$getc2cu="N";
			if($_REQUEST['storeid']==''&&$_REQUEST['Classif']!='A')
			{
				//if($shipping_method==u2bcr("速買配7-11門市純取貨"))
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
				if($smilepay_c2cupobj && $smilepay_c2cupobj->isSelectedSmilepayC2Cup($order_id))
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
                    $b2c_store=$b2c_storeid.'/'.$b2c_storename.'/'.$b2c_storeaddress;
                    $b2c_run = true;
                } 
                //<b2c> end
                else 
                {
				    $c2c_storeaddress=$_REQUEST['storeaddress'];
				    $c2c_storename=$_REQUEST['storename'];
				    $c2c_storeid=$_REQUEST['storeid'];
				
				    $c2c_store=$c2c_storeid.'/'.$c2c_storename.'/'.$c2c_storeaddress;		
                }
                $getc2cu="Y";	
			}
			//--物流end
            //重新結帳
            if( $smilepay_credit_pay_for_order)
            {
                WC()->session->__unset('smilepay_credit_pay_for_order');
            }
                
            
			//訂單變成凍結中
			$order->update_status('on-hold');	
          	//開啟線上刷卡
			if(is_ssl())
                $URL = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            else
			    $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if($getc2cu=="Y")
			{
                if($b2c_run) //<b2c> start
                    $Address=$b2c_store;
                else    //<b2c> end
                    $Address=$c2c_store;
            }
			else
			{$Address=$order->get_billing_address_1();}

			$pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
		//	$c2c_store=$c2c_storeid.'/'.$c2c_storename.'/'.$c2c_storeaddress;
            //<SmilePay>item add start
            /*$products="";
            foreach ($order->get_items() as $key => $lineItem) {
                $products .= $lineItem['name'] ."*" .$lineItem['quantity'] .",";
            
            }
            $products=trim($products,",");*/
            //<SmilePay>item add end
			$cardurl =	'https://ssl.smse.com.tw/ezpos/mtmk_utf.asp?'.
						'Dcvc=' . $SmilePaycredit_args['dcvc'] .
						'&Rvg2c=' .$SmilePaycredit_args['Rvg2c'] .
						'&Pur_name=' .urlencode($pur_name).
						'&Tel_number=' .urlencode($order->get_billing_phone()).
						'&Address=' .urlencode($Address).
						'&Email=' .urlencode($order->get_billing_email()).
						'&Data_id=' .$SmilePaycredit_args['od_sob'].
						'&od_sob=' . $SmilePaycredit_args['od_sob'].
						'&Amount=' . $SmilePaycredit_args['amt'].
						'&Deadline_date=' . $Deadline_date.
						'&Roturl_status=' . 'woook'.
                        '&Remark=woocommerce';
						//<SmilePay>item add start
					    //'&Remark='. $products;
                        //<SmilePay>item add end
			$post_strall=$cardurl.'&Pay_zg=1'.'&Roturl=' . urlencode($URL);
			header("Location: $post_strall"); 
			exit();	
		}		
		if($order_status=='wc-on-hold'&&$_REQUEST['Classif']=='A')
		{
			$A_Amount=$_REQUEST['Amount'];
			$A_Data_id=$_REQUEST['Data_id'];
			$A_Response_id=$_REQUEST['Response_id'];
			$A_Errdesc=$_REQUEST['Errdesc'];
			$A_Smseid=$_REQUEST['Smseid'];
			$A_date=$_REQUEST['Process_date'];
			$A_time=$_REQUEST['Process_time'];
			$A_Address=$_REQUEST['Address'];

            $title = b2ucr($this->title);
			if($A_Response_id=='1'&&$SmilePaycredit_args['amt']==$A_Amount)
			{
               
        		$order->reduce_order_stock();			
				$pay_ok="繳費方式：<font color=red>$title</font><br>交易結果：<font color=red>授權成功</font></br>授權金額：<font color=red>".
							$A_Amount."</font></br>交易時間：<font color=red>".$A_date.$A_time."</font></br>";
				$order->add_order_note(u2bcr($pay_ok),"1");
				$ordertext="<h1>繳費資訊</h1>繳費方式：<font color=red>$title</font><br>交易結果：<font color=red>授權成功</font></br>授權金額：<font color=red>".
							$A_Amount."</font></br>交易時間：<font color=red>".$A_date." ".$A_time."</font></br>SmilePay追蹤碼：<font color=red>".$A_Smseid."</font>";
				$order->add_order_note(u2bcr($ordertext),"0");
				$order->update_status('processing');
				echo u2bcr($pay_ok);

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
				if($smilepay_c2cupobj && $smilepay_c2cupobj->isSelectedSmilepayC2Cup($order_id))
				{
					include "smilepay_function_c2c.php";
                    $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
					$post_str = 'Dcvc=' . $SmilePaycredit_args['dcvc'] .
							'&Rvg2c=' . $SmilePaycredit_args['Rvg2c'] .
							'&Pur_name=' .$pur_name.
							'&Tel_number=' .$order->get_billing_phone().
							'&Email=' .$order->get_billing_email().
							'&Data_id=' . $SmilePaycredit_args['od_sob'].
							'&od_sob=' . $SmilePaycredit_args['od_sob'].
							'&Amount=' . $SmilePaycredit_args['amt'].
							'&Roturl_status=' . 'woook'.
							'&Verify_key=' . $SmilePaycredit_args['Verify_key'].
							'&Remark=woocommerce';
					$c2c_store=u2bcr($A_Address);
                    
					$scvurls=repeatc2c($c2c_store,$post_str,$SmilePaycredit_args['dcvc'],$SmilePaycredit_args['Verify_key']);
					$order->add_order_note(u2bcr($scvurls),"0");

                    $c2c_store_array= explode("/",$c2c_store); 
                    if(!empty($c2c_store_array) && count($c2c_store_array)==3)
                    {
                        $c2c_storeid=$c2c_store_array[0];
                        $c2c_storename=$c2c_store_array[1];
                        $c2c_storeaddress=$c2c_store_array[2];
                    }
                    else
                    {
                        $c2c_storeid="";
                        $c2c_storename="";
                        $c2c_storeaddress="";
                    }
                    
					$scvurls_1="7-11取貨門市：<font color=red>".b2ucr($c2c_storename . $c2c_storeid)."</font><br>";
                    $scvurls_1 .= "門市地址：<font color=red>".b2ucr($c2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "取貨人姓名：<font color=red>".b2ucr($pur_name)."</font><br>";
                    $scvurls_1 .= "取貨人電話：<font color=red>".b2ucr($order->get_billing_phone())."</font><br>";

					$order->add_order_note(u2bcr($scvurls_1),"1");
                    echo u2bcr('<h1>取貨資訊</h1>' . $scvurls_1);
				}
                elseif($smilepay_b2cupobj && $smilepay_b2cupobj->isSelectedSmilepayB2Cup($order_id))
				{
					include "smilepay_function_b2c.php";
                    $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
					$post_str = 'Dcvc=' . $SmilePaycredit_args['dcvc'] .
							'&Rvg2c=' . $SmilePaycredit_args['Rvg2c'] .
							'&Pur_name=' .$pur_name.
							'&Tel_number=' .$order->get_billing_phone().
							'&Email=' .$order->get_billing_email().
							'&Data_id=' . $SmilePaycredit_args['od_sob'].
							'&od_sob=' . $SmilePaycredit_args['od_sob'].
							'&Amount=' . $SmilePaycredit_args['amt'].
							'&Roturl_status=' . 'woook'.
							'&Verify_key=' . $SmilePaycredit_args['Verify_key'].
							'&Remark=woocommerce';
					$b2c_store=u2bcr($A_Address);
                    
					$scvurls=repeatb2c($b2c_store,$post_str,$SmilePaycredit_args['dcvc'],$SmilePaycredit_args['Verify_key']);
					$order->add_order_note(u2bcr($scvurls),"0");

                    $b2c_store_array= explode("/",$b2c_store); 
                    if(!empty($b2c_store_array) && count($b2c_store_array)==3)
                    {
                        $b2c_storeid=$b2c_store_array[0];
                        $b2c_storename=$b2c_store_array[1];
                        $b2c_storeaddress=$b2c_store_array[2];
                    }
                    else
                    {
                        $c2c_storeid="";
                        $c2c_storename="";
                        $c2c_storeaddress="";
                    }
                    
					$scvurls_1="7-11取貨門市：<font color=red>".b2ucr($b2c_storename . $b2c_storeid)."</font><br>";
                    $scvurls_1 .= "門市地址：<font color=red>".b2ucr($b2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "取貨人姓名：<font color=red>".b2ucr($pur_name)."</font><br>";
                    $scvurls_1 .= "取貨人電話：<font color=red>".b2ucr($order->get_billing_phone())."</font><br>";

					$order->add_order_note(u2bcr($scvurls_1),"1");
                    echo u2bcr('<h1>取貨資訊</h1>' . $scvurls_1);
				}
				
			}
			else
			{
                if($order_status=='wc-failed')
                {
                    echo u2bcr("<font color='red' size='3'>如忘記<u>繳費資訊</u>請自上方選單列<u>帳號設定->訂單->檢視</u></font>");
                    return;
                }


				$pay_err="繳費方式：<font color=red>線上刷卡</font><br>交易結果：<font color=red>授權失敗</font></br>交易時間：<font color=red>".
							$A_date.$A_time."</font></br>失敗原因：<font color=red>".$A_Errdesc."</font></br>";
				$order->add_order_note(u2bcr($pay_err),"1");
				$ordertext="繳費方式：<font color=red>線上刷卡</font><br>交易結果：<font color=red>授權失敗</font></br>交易時間：<font color=red>".
							$A_date." ".$A_time."</font></br>失敗原因：<font color=red>".$A_Errdesc."</font></br>SmilePay追蹤碼：<font color=red>".$A_Smseid."</font>";
				$order->add_order_note(u2bcr($ordertext),"0");
				$order->update_status('failed');
				echo u2bcr($pay_err);
			}
			echo "<br><br>".$SmilePaycredit_args['Order_OKmain'];
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
               is_null($this->Rvg2c) || empty($this->Rvg2c)
                )
            {
                wc_add_notice(u2bcr("<br>速買配商家參數錯誤，請與商家聯繫<br>") , 'error' );
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
            */

            //重新結帳
            if(isset($_REQUEST['pay_for_order']) && $_REQUEST['pay_for_order']=='true')
            {
                WC()->session->set( 'smilepay_credit_pay_for_order', true );
            }

            /*if($smilepayc2cup_obj && $smilepayc2cup_obj->isSelectedSmilepayC2Cup($order_id))
			//if($shipping_method==u2bcr("速買配7-11門市純取貨"))
			{
				$username=$order->get_billing_first_name().$order->get_billing_last_name();
                if( !preg_match("/[^a-zA-Z0-9 ]/",$username))
                {
                    $limit_name_number =10;
                }
                else
                {
                    $limit_name_number =5;
                }
				if(substr($order->billing_phone,0,2)!="09"||mb_strlen($order->billing_phone)!=10||mb_strlen( $username, "utf-8")>$limit_name_number){
                    wc_add_notice( __(u2bcr("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if( $smilepayc2cp_obj && $smilepayc2cp_obj->isSelectedSmilepayC2Cp($order_id))
			{
                wc_add_notice( __(u2bcr("<br><font color='red'>配送方式選擇錯誤，不可選擇此配送方式</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
                exit;
            }
            //<b2c> start
            if($smilepayb2cup_obj && $smilepayb2cup_obj->isSelectedSmilepayB2Cup($order_id))
			{
				$username=$order->get_billing_first_name().$order->get_billing_last_name();
                if( !preg_match("/[^a-zA-Z0-9 ]/",$username))
                {
                    $limit_name_number =10;
                }
                else
                {
                    $limit_name_number =5;
                }
				if(substr($order->billing_phone,0,2)!="09"||mb_strlen($order->billing_phone)!=10||mb_strlen( $username, "utf-8")>$limit_name_number){
                    wc_add_notice( __(u2bcr("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if( $smilepayb2cp_obj && $smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))
			{
                wc_add_notice( __(u2bcr("<br><font color='red'>配送方式選擇錯誤，不可選擇此配送方式</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
                exit;
            }
            //<b2c> end
            */
			//--物流end*/
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
    function add_SmilePaycredit_gateway($methods) {
        $methods[] = 'WC_SmilePaycredit';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_SmilePaycredit_gateway');
}
if(!function_exists('u2bcr'))
{
    function u2bcr($text)//畫面輸出
    {	return iconv("big5","UTF-8",$text);}
}

if(!function_exists('b2ucr'))
{
    function b2ucr($text)//寫入資料庫
    {	return iconv("UTF-8","big5",$text);}
}
?>