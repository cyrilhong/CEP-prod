<?php
/**
 * Plugin Name: SmilePay_Union 
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay Union 
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */
if(!defined("SMILEPAY_RESPONSE_DECLARE"))
{
    include "smilepay_respond.php";
}
add_action('plugins_loaded', 'SmilePayunion_gateway_init', 0);
function SmilePayunion_gateway_init() {
   if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
   

    function register_smilepay_union_waiting_paid_order_status() {
        register_post_status( 'wc-spunion-waiting', array(
        'label'                     => 'spunion-waiting',
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( u2bunion('等待刷卡回應(銀聯卡) <span class="count">(%s)</span>'), u2bunion('等待刷卡回應(銀聯卡)<span class="count">(%s)</span>') )
        ) );
    }
    add_action( 'init', 'register_smilepay_union_waiting_paid_order_status' );

    // Add to list of WC Order statuses
    function add_smilepay_union_waiting_paid_to_order_statuses( $order_statuses ) {

        $order_statuses['wc-spunion-waiting'] = u2bunion('等候刷卡回應(銀聯卡)');
        return  $order_statuses;
    }
    function smilepay_union_status_icon() {
	    
	    include_once("tpl/smilepay_union_icon.php");
    }

    add_filter( 'wc_order_statuses', 'add_smilepay_union_waiting_paid_to_order_statuses' );
    add_action( 'admin_head', 'smilepay_union_status_icon' );
 



    class WC_SmilePayunion extends WC_Payment_Gateway {

        public $title;
        public $description;
        public $dcvc;
        public $Rvg2c;
        public $Order_OKmain;
        public $Verify_key;

        public function __construct() {
            $this->id = 'smilepayunion';
            $this->icon = apply_filters('woocommerce_SmilePayunion_icon', plugins_url('log/2.png', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('SmilePayunion', 'woocommerce');
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
            $this->reurl =get_option('siteurl')."/?smilepay_respond";
            $this->Mid_smilepay = $this->settings['Mid_smilepay'];
            // Actions
            //add_action('init', array(&$this, 'check_SmilePayunion_response'));
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
                    'title' => __(u2bunion("啟用/關閉"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2bunion(' SmilePay 線上刷卡(銀聯卡)'), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2bunion('標題'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bunion('顧客在結帳時所顯示的付款方式標題'), 'woocommerce'),
                    'default' => __(u2bunion('SmilePay 線上刷卡(銀聯卡) 繳費'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2bunion('付款方式說明'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bunion('顧客在選擇付款方式時所顯示的介紹文字'), 'woocommerce'),
                    'default' => __(u2bunion("SmilePay  線上刷卡(銀聯卡) 繳費"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2bunion('商家代號'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bunion('請填入您SmilePay商店代號'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2bunion('商家參數碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bunion('請填入您SmilePay商家參數碼'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2bunion('商家檢查碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bunion('請填入您SmilePay商家檢查碼，檢查碼於商家後台「背景取號API」頁面中，請複製並貼入上方欄位'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),		
                'Mid_smilepay' => array(
                    'title' => __(u2bunion('商家驗證參數'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bunion('請填入您SmilePa驗證碼，驗證碼於商家後台「基本資料管理」頁面中，請複製並貼入上方欄位，如不需驗證請保留空白'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),					
				'Order_OKmain' => array(
                    'title' => __(u2bunion('訂單成立後顯示訊息'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bunion('訂單成立顯示訊息'), 'woocommerce'),
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
       public function get_SmilePayunion_args($order) {
            global $woocommerce;

            $paymethod = 'union';
            $order_id = $order->id;
			$post_status = $order->post_status;

            $SmilePayunion_args = array(
                "dcvc" => $this->dcvc,
                'Rvg2c' => $this->Rvg2c,
                "payment_type" => $paymethod,
				"post_status" => $post_status,
                "od_sob" => $order_id,
                'Verify_key' => $this->Verify_key,	
                'Mid_smilepay' => $this->Mid_smilepay,			
				'Order_OKmain' => $this->Order_OKmain,
                'reurl'=>$this->reurl,
				"amt" => round($order->get_total()),
            );
            $SmilePayunion_args = apply_filters('woocommerce_SmilePayunion_args', $SmilePayunion_args);
            return $SmilePayunion_args;
        }
		
       public function thankyou_page($order_id) {  //接收回傳參數驗證  與   credit取號
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
            $order = new WC_Order($order_id);
			
			
			// $order = &new WC_Order($order_id);
			
			$SmilePayunion_args = $this->get_SmilePayunion_args($order);
			$order_status=$SmilePayunion_args['post_status'];
			
            //重新結帳
            $smilepay_union_pay_for_order =  WC()->session->get( 'smilepay_union_pay_for_order');
       
		if($order_status=='wc-pending' || $smilepay_union_pay_for_order)
		{
            //--物流start
			$shipping_method= $order->get_shipping_method();
			$getc2cu="N";
			if($_REQUEST['storeid']=='')
			{
				//if($shipping_method==u2bunion("速買配7-11門市純取貨"))
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
				}//<b2c> start
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
                }
				$getc2cu="Y";	
                if($b2c_run )
                    $b2c_store =$b2c_storeid.'/'.$b2c_storename.'/'.$b2c_storeaddress;	
                else
				    $c2c_store=$c2c_storeid.'/'.$c2c_storename.'/'.$c2c_storeaddress;	
               
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
			        $post_str = 'Dcvc=' . $SmilePayunion_args['dcvc'] .
							'&Rvg2c=' . $SmilePayunion_args['Rvg2c'] .
							'&Pur_name=' .$pur_name.
							'&Tel_number=' .$order->get_billing_phone().
							'&Email=' .$order->get_billing_email().
							'&Data_id=' . $SmilePayunion_args['od_sob'].
							'&od_sob=' . $SmilePayunion_args['od_sob'].
							'&Amount=' . $SmilePayunion_args['amt'].
							'&Roturl_status=' . 'woook'.
							'&Verify_key=' . $SmilePayunion_args['Verify_key'].
							'&Remark=woocommerce';
				   
                    
				    $scvurls=repeatc2c($c2c_store,$post_str,$SmilePayunion_args['dcvc'],$SmilePayunion_args['Verify_key']);
				    $order->add_order_note(u2bunion($scvurls),"0");

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
                    update_post_meta( $order_id, 'smilepay_c2c_store', $c2c_store )	;//save order

				    $scvurls_1="7-11取貨門市：<font color=red>".b2uunion($c2c_storename . $c2c_storeid)."</font><br>";
                    $scvurls_1 .= "門市地址：<font color=red>".b2uunion($c2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "取貨人姓名：<font color=red>".b2uunion($pur_name)."</font><br>";
                    $scvurls_1 .= "取貨人電話：<font color=red>".b2uunion($order->get_billing_phone())."</font><br>";
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
			        $order->add_order_note(u2bunion($scvurls_1),"1");
                    //echo u2bunion('<h1>取貨資訊</h1>' . $scvurls_1);
                }
                elseif($smilepay_b2cupobj && $smilepay_b2cupobj->isSelectedSmilepayB2Cup($order_id))
                {
                    include "smilepay_function_b2c.php";
                    $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
			        $post_str = 'Dcvc=' . $SmilePayunion_args['dcvc'] .
							'&Rvg2c=' . $SmilePayunion_args['Rvg2c'] .
							'&Pur_name=' .$pur_name.
							'&Tel_number=' .$order->get_billing_phone().
							'&Email=' .$order->get_billing_email().
							'&Data_id=' . $SmilePayunion_args['od_sob'].
							'&od_sob=' . $SmilePayunion_args['od_sob'].
							'&Amount=' . $SmilePayunion_args['amt'].
							'&Roturl_status=' . 'woook'.
							'&Verify_key=' . $SmilePayunion_args['Verify_key'].
							'&Remark=woocommerce';
				   
                    
				    $scvurls=repeatb2c($b2c_store,$post_str,$SmilePayunion_args['dcvc'],$SmilePayunion_args['Verify_key']);
				    $order->add_order_note(u2bunion($scvurls),"0");

                    $b2c_store_array= explode("/",$b2c_store); 
                    if(!empty($b2c_store_array) && count($b2c_store_array)==3)
                    {
                        $b2c_storeid=$b2c_store_array[0];
                        $b2c_storename=$b2c_store_array[1];
                        $b2c_storeaddress=$b2c_store_array[2];
                    }
                    else
                    {
                        $b2c_storeid="";
                        $b2c_storename="";
                        $b2c_storeaddress="";
                    }
                    update_post_meta( $order_id, 'smilepay_b2c_store', $b2c_store )	;//save order

				    $scvurls_1="7-11取貨門市：<font color=red>".b2uunion($b2c_storename . $b2c_storeid)."</font><br>";
                    $scvurls_1 .= "門市地址：<font color=red>".b2uunion($b2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "取貨人姓名：<font color=red>".b2uunion($pur_name)."</font><br>";
                    $scvurls_1 .= "取貨人電話：<font color=red>".b2uunion($order->get_billing_phone())."</font><br>";
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
			        $order->add_order_note(u2bunion($scvurls_1),"1");
                }

			}
			//--物流end
            //重新結帳
            if( $smilepay_union_pay_for_order)
            {
                WC()->session->__unset('smilepay_union_pay_for_order');
            }
                
            
			//訂單變成凍結中
			$order->update_status('on-hold');	
            //$order->update_status('process');
          	//開啟線上刷卡
			
            if($SmilePayunion_args['reurl']!='')
            {
                $Vk=$SmilePayunion_args['reurl']."=".$order->get_order_key();
            }
            else
            {
                $Vk='';
            }
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
            
             //<SmilePay>item add start
            /*$products="";
            foreach ($order->get_items() as $key => $lineItem) {
                $products .= $lineItem['name'] ."*" .$lineItem['quantity'] .",";
            
            }
            $products=trim($products,",");*/
            //<SmilePay>item add end


			$pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
			//$c2c_store=$c2c_storeid.'/'.$c2c_storename.'/'.$c2c_storeaddress;
			$cardurl =	'https://ssl.smse.com.tw/ezpos/mtmk_utf.asp?'.
						'Dcvc=' . $SmilePayunion_args['dcvc'] .
						'&Rvg2c=' .$SmilePayunion_args['Rvg2c'] .
						'&Pur_name=' .urlencode($pur_name).
						'&Tel_number=' .urlencode($order->get_billing_phone()).
						'&Address=' .urlencode($Address).
						'&Email=' .urlencode($order->get_billing_email()).
						'&Data_id=' .$SmilePayunion_args['od_sob'].
						'&od_sob=' . $SmilePayunion_args['od_sob'].
						'&Amount=' . $SmilePayunion_args['amt'].
						'&Deadline_date=' . $Deadline_date.
						'&Roturl_status=' . 'woook'.
                        '&unpRoturl=' . urlencode($URL) .
						'&Remark=woocommerce';
                        //<SmilePay>item add start
					    //'&Remark='. $products;
                        //<SmilePay>item add end

			$post_strall=$cardurl.'&Pay_zg=11'.'&Roturl=' . urlencode($Vk);
			header("Location: $post_strall"); 
            //header("Location: " .$URL  ); 
			exit();	
		}		

        //$smilepay_union_last_order_id =  WC()->session->get( 'smilepay_union_last_order_id');
        
         
        if($order_status=='wc-on-hold')
		{

            if(isset($_REQUEST['Smseid']))
                $A_Smseid=$_REQUEST['Smseid'];
            else
                $A_Smseid="";
            if(isset($_REQUEST['Process_date']))
			    $A_date=$_REQUEST['Process_date'];
            else
                $A_date="";
            if(isset($_REQUEST['Process_time']))
			    $A_time=$_REQUEST['Process_time'];
            else
                $A_time="";

            if(isset($_REQUEST['Response_id']))
                $A_Response_id=$_REQUEST['Response_id'];
            else
                $A_Response_id=" ";

            if(isset($_REQUEST['Errdesc']))
		        $A_Errdesc=$_REQUEST['Errdesc'];
            else
                $A_Errdesc="";
          
			if( $A_Response_id == "0" )
			{
                 

				$pay_err="繳費方式：<font color=red>線上刷卡</font><br>交易結果：<font color=red>授權失敗</font></br>交易時間：<font color=red>".
							$A_date.$A_time."</font></br>失敗原因：<font color=red>".$A_Errdesc."</font></br>";
				$order->add_order_note(u2bunion($pay_err),"1");
				$ordertext="繳費方式：<font color=red>線上刷卡</font><br>交易結果：<font color=red>授權失敗</font></br>交易時間：<font color=red>".
							$A_date." ".$A_time."</font></br>失敗原因：<font color=red>".$A_Errdesc."</font></br>SmilePay追蹤碼：<font color=red>".$A_Smseid."</font>";
				$order->add_order_note(u2bunion($ordertext),"0");
				$order->update_status('failed');
				echo u2bunion($pay_err);

               
                    
                
            }
            else
            {
                $title = b2uunion($this->title);
                $ordertext="<h1>繳費資訊</h1>繳費方式：<font color=red>$title</font><br>交易結果：<font color=red>等待刷卡回應</font></br>";
                $order->add_order_note(u2bunion($ordertext),"1");
                $ordertext .= "<font color=red>等候時間約15分鐘，請於該時間後查詢此訂單，若未回應請與商家聯繫</font></br>";
                $order->update_status('spunion-waiting');
                echo u2bunion($ordertext);

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
                    $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
                    $c2c_store=get_post_meta( $order_id, 'smilepay_c2c_store', true );
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
                    
				    $scvurls_1="7-11取貨門市：<font color=red>".b2uunion($c2c_storename . $c2c_storeid)."</font><br>";
                    $scvurls_1 .= "門市地址：<font color=red>".b2uunion($c2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "取貨人姓名：<font color=red>".b2uunion($pur_name)."</font><br>";
                    $scvurls_1 .= "取貨人電話：<font color=red>".b2uunion($order->get_billing_phone())."</font><br>";
                    echo u2bunion('<h1>取貨資訊</h1>' . $scvurls_1);
                

                }
                elseif($smilepay_b2cupobj && $smilepay_b2cupobj->isSelectedSmilepayB2Cup($order_id))
                {
                    $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
                    $b2c_store=get_post_meta( $order_id, 'smilepay_b2c_store', true );
                    $b2c_store_array= explode("/",$b2c_store); 
                    if(!empty($b2c_store_array) && count($b2c_store_array)==3)
                    {
                        $b2c_storeid=$b2c_store_array[0];
                        $b2c_storename=$b2c_store_array[1];
                        $b2c_storeaddress=$b2c_store_array[2];
                    }
                    else
                    {
                        $b2c_storeid="";
                        $b2c_storename="";
                        $b2c_storeaddress="";
                    }
                    
				    $scvurls_1="7-11取貨門市：<font color=red>".b2uunion($b2c_storename . $b2c_storeid)."</font><br>";
                    $scvurls_1 .= "門市地址：<font color=red>".b2uunion($b2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "取貨人姓名：<font color=red>".b2uunion($pur_name)."</font><br>";
                    $scvurls_1 .= "取貨人電話：<font color=red>".b2uunion($order->get_billing_phone())."</font><br>";
                    echo u2bunion('<h1>取貨資訊</h1>' . $scvurls_1);
                    
                }

            }



           
		}
        elseif($order_status=='wc-process') 
        {
                $title = b2uunion($this->title);
                $ordertext="<h1>繳費資訊</h1>繳費方式：<font color=red>$title</font><br>交易結果：<font color=red>等待刷卡回應</font></br>";
                $order->add_order_note(u2bunion($ordertext),"1");
                $ordertext .= "<font color=red>等候時間約15分鐘，請於該時間後查詢此訂單，若未回應請與商家聯繫</font></br>";
                echo u2bunion($ordertext);

                if(file_exists("smilepayc2cup.php"))
                    include_once("smilepayc2cup.php");
                $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
                if(class_exists('WC_Smilepayc2cu_Shipping_Method'))
                    $smilepay_c2cupobj= new WC_Smilepayc2cu_Shipping_Method();
                else
                    $smilepay_c2cupobj= null;
                //檢查物流是否使用純取貨
		        if($smilepay_c2cupobj && $smilepay_c2cupobj->isSelectedSmilepayC2Cup($order_id))
                {
                    $c2c_store=get_post_meta( $order_id, 'smilepay_c2c_store', true );
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
                    
				    $scvurls_1="7-11取貨門市：<font color=red>".b2uunion($c2c_storename . $c2c_storeid)."</font><br>";
                    $scvurls_1 .= "門市地址：<font color=red>".b2uunion($c2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "取貨人姓名：<font color=red>".b2uunion($pur_name)."</font><br>";
                    $scvurls_1 .= "取貨人電話：<font color=red>".b2uunion($order->get_billing_phone())."</font><br>";
                    echo u2bunion('<h1>取貨資訊</h1>' . $scvurls_1);

                }
        }
        else //if($order_status=='wc-spunion-waiting')
        {
             echo u2bunion("<font color='red' size='3'>如忘記<u>繳費資訊</u>請自上方選單列<u>帳號設定->訂單->檢視</u></font>");
        }
       
            //WC()->session->set( 'smilepay_union_last_order_id', $order_id );
		echo "<br><br>".$SmilePayunion_args['Order_OKmain'];
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
                wc_add_notice(u2bunion("<br>速買配商家參數錯誤，請與商家聯繫<br>") , 'error' );
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
            //<b2c> end*/
            //重新結帳
            if(isset($_REQUEST['pay_for_order']) && $_REQUEST['pay_for_order']=='true')
            {
                WC()->session->set( 'smilepay_union_pay_for_order', true );
            }

           /* if($smilepayc2cup_obj && $smilepayc2cup_obj->isSelectedSmilepayC2Cup($order_id))
			//if($shipping_method==u2bunion("速買配7-11門市純取貨"))
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
                    wc_add_notice( __(u2bunion("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if( $smilepayc2cp_obj && $smilepayc2cp_obj->isSelectedSmilepayC2Cp($order_id))
			//if($shipping_method==u2bunion("速買配7-11門市取貨付款"))
			{
                wc_add_notice( __(u2bunion("<br><font color='red'>配送方式選擇錯誤，不可選擇此配送方式</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
                exit;
            }
            //<b2c> start
            if($smilepayb2cup_obj && $smilepayb2cup_obj->isSelectedSmilepayB2Cup($order_id))
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
                    wc_add_notice( __(u2bunion("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if( $smilepayb2cp_obj && $smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))
			{
                wc_add_notice( __(u2bunion("<br><font color='red'>配送方式選擇錯誤，不可選擇此配送方式</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
                exit;
            }
            //<b2c> end
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
    function add_SmilePayunion_gateway($methods) {
        $methods[] = 'WC_SmilePayunion';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_SmilePayunion_gateway');
}
function u2bunion($text)//畫面輸出
{	return iconv("big5","UTF-8",$text);}
function b2uunion($text)//寫入資料庫
{	return iconv("UTF-8","big5",$text);}
?>