<?php

/**
 * Plugin Name: SmilePay_B2C 
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay B2C 
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */

if(!defined("SMILEPAY_RESPONSE_DECLARE"))
{
    include "smilepay_respond.php";
}

add_action('plugins_loaded', 'SmilePayb2c_gateway_init', 80);



function SmilePayb2c_gateway_init() {
   if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

  

    class WC_SmilePayb2c extends WC_Payment_Gateway {
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
            $this->id = 'smilepayb2c';
            $this->icon = apply_filters('woocommerce_SmilePayb2c_icon', plugins_url('log/smilepay.png', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('SmilePayb2c', 'woocommerce');
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
            //add_action('init', array(&$this, 'check_SmilePayb2c_response'));
            
              
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
                    'title' => __(u2bb2("啟用/關閉"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2bb2(' SmilePay 7-11超商取貨付款'), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2bb2('標題'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('顧客在結帳時所顯示的付款方式和配送方式標題'), 'woocommerce'),
                    'default' => __(u2bb2('SmilePay 7-11超商取貨付款'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2bb2('付款方式說明'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bb2('顧客在選擇付款方式時所顯示的介紹文字'), 'woocommerce'),
                    'default' => __(u2bb2("SmilePay  超商取貨付款 繳費"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2bb2('商家代號'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('請填入您SmilePay商店代號'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2bb2('商家參數碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('請填入您SmilePay商家參數碼'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2bb2('商家檢查碼'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('請填入您SmilePay商家檢查碼，檢查碼於商家後台「背景取號API」頁面中，請複製並貼入上方欄位'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
                'Mid_smilepay' => array(
                    'title' => __(u2bb2('商家驗證參數'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('請填入您SmilePa驗證碼，驗證碼於商家後台「基本資料管理」頁面中，請複製並貼入上方欄位，如不需驗證請保留空白'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
				'Order_OKmain' => array(
                    'title' => __(u2bb2('訂單成立後顯示訊息'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bb2('訂單成立顯示訊息'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
				/*'reurl' => array(
                    'title' => __(u2bb2('交易完成回送位置'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('交易完成回送位置，請將下列文字複製到上方框內(如不須回送請留空白)</br><font color=red ue size=+1>'.$urll.'</font>', 'woocommerce')),
                    'default' => __('', 'woocommerce')
                ),*/
				'hiddtext' => array(
                    'title' => __(u2bb2('超商取貨付款注意事項'), 'woocommerce'),
                    'type' => 'hidden',
                    'description' => __(u2bb2("使用超商取貨付款功能，需注意以下事項：
												<br>※<font color='red'>使用此模組必須開啟【速買配7-11門市取貨付款】配送模組，請確認啟用</font>												
												<br>1.請先至SmilePay商家後台開啟取貨付款功能*<a target='_blank' href='http://www.smilepay.net/RVG.ASP'>商家後台</a>*
												<br>2.*寄貨商品名稱*為訂單名稱，並非商品明細，包裹上<font color='red'>無標註此資料</font>。
												<br>3.帳單資訊中<font color='red'>聯絡電話</font>必須為<font color='red'>手機號碼</font>，商品到門市時會以簡訊通知。
												<br>4.消費者成立訂單後，請至SmilePay商家後台中取得<font color='red'>交貨便代碼</font>。
												<br>5.消費者成立訂單後，可至訂單備註中點選<font color='red'>開啟交貨便服務單</font>。																										
												<br>6.請將<font color='red'>交貨便代碼</font>手動新增至商品備註，以利消費者查詢物流狀態。
												<br>7.更多說明請參閱，<font color='red'>SmilePay網站說明</font>與<font color='red'>WooCommerce模組說明文件</font>。
												"), 'woocommerce'),
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
       public function get_SmilePayb2c_args($order) {
            global $woocommerce;

            $paymethod = 'b2c';
            $order_id = $order->get_id();
			$post_status = $order->post_status;


            $SmilePayb2c_args = array(
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
            $SmilePayb2c_args = apply_filters('woocommerce_SmilePayb2c_args', $SmilePayb2c_args);
            return $SmilePayb2c_args;
        }
		
       public function thankyou_page($order_id) {  //接收回傳參數驗證  與   b2c取號
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
            $order = new WC_Order($order_id);
			// $order = &new WC_Order($order_id);

			$SmilePayb2c_args = $this->get_SmilePayb2c_args($order);
			$order_status=$SmilePayb2c_args['post_status'];
            $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();

           //重新結帳
            $smilepay_credit_pay_for_order =  WC()->session->get( 'smilepay_credit_pay_for_order');
     

		if($order_status=='wc-pending'||$smilepay_credit_pay_for_order)
		{	
			//判斷電話是否正確,姓名
			/*if(substr($order->billing_phone,0,2)!="09"||mb_strlen($order->billing_phone)!=10){
				$order->update_status('cancelled');
				echo u2bb2("<font color=red>訂單成立失敗</font><br><br>錯誤資訊：<br>連絡電話請輸入手機(共10碼)，商品到店後，將發送簡訊通知。<br>名字與姓氏總合不可超過5個字。<br><br>請重新結帳。");
				b2caddtocart($thepostid);
				exit();
			}	
			if(mb_strlen($order->billing_first_name.$order->billing_last_name)>5){
				$order->update_status('cancelled');
				echo u2bb2("<font color=red>訂單成立失敗</font><br><br>錯誤資訊：<br>連絡電話請輸入手機(共10碼)，商品到店後，將發送簡訊通知。<br>名字與姓氏總合不可超過5個字。<br><br>請重新結帳。");
				b2caddtocart($thepostid);
				exit();
			}*/
			
			//根據當下頁面調整reurl是http或https
            if(is_ssl())
                $SmilePayb2c_args['reurl']=str_replace('http://','https://',$SmilePayb2c_args['reurl']);
            else
                $SmilePayb2c_args['reurl']=str_replace('https://','http://',$SmilePayb2c_args['reurl']);	
			//開啟b2c
			if($SmilePayb2c_args['reurl']!=''){$Vk=$SmilePayb2c_args['reurl']."=".$order->get_order_key();}else{$Vk='';}

            if(is_ssl())
                $URL = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            else
			    $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            //<SmilePay>item add start
            /*$products="";
            foreach ($order->get_items() as $key => $lineItem) {
                $products .= $lineItem['name'] ."*" .$lineItem['item_meta']['_qty'][0] .",";
            
            }
            $products=trim($products,",");*/

			$cardurl =	'https://ssl.smse.com.tw/ezpos/mtmk_utf.asp?'.
						'Dcvc=' . $SmilePayb2c_args['dcvc'] .
						'&Pay_zg=55' .
						'&Rvg2c=' .  $SmilePayb2c_args['Rvg2c'] .
						'&Od_sob=' .$SmilePayb2c_args['od_sob'].
						'&Pur_name=' .urlencode($pur_name ).
						'&Mobile_number=' .urlencode($order->get_billing_phone()).
						'&Email=' .urlencode($order->get_billing_email()).
						'&Data_id=' . $SmilePayb2c_args['od_sob'].
						'&Amount=' . $SmilePayb2c_args['amt'].
						'&Roturl_status=' . 'woook'.
						'&Roturl=' . $Vk.
						'&MapRoturl=' . urlencode($URL).
						'&Remark=woocommerce';
                        //<SmilePay>item add start
					    //'&Remark='. $products;
                        //<SmilePay>item add end
            //Mobile map start
            $useragent=$_SERVER['HTTP_USER_AGENT'];
            if( preg_match('/Android|iPad|iPhone/i',$useragent))
            {
                $cardurl .="&Logistics_MapType=M";
                          
            }
            //Mobile map end
            //重新結帳
            if( $smilepay_credit_pay_for_order)
            {
                WC()->session->__unset('smilepay_credit_pay_for_order');
            }
                			//訂單變成失敗中			
			$order->update_status('on-hold');			
			header("Location: $cardurl"); 
			exit();	
		}		
		if($order_status=='wc-on-hold'&&$_REQUEST['Classif']=='V')
		{
			$T_Smseid=$_REQUEST['Smseid'];
			$T_Storename=b2ub2($_REQUEST['Storename']);
			$T_Storeid=b2ub2($_REQUEST['Storeid']);
            $T_Storeaddress = $_REQUEST['Storeaddress'];
            
            //<smilepay> modify address start
            $T_Storename2=$_REQUEST['Storename'];
            if( !empty($T_Storeid) && !empty($T_Storename2) && !empty($T_Storeaddress))
            {
                $shipping_addr= $order->get_shipping_address_1();
                $store_addr=$T_Storeid ." " .$T_Storename2 ." " .$T_Storeaddress;
                if($store_addr != $shipping_addr)
                {
                    $order->set_shipping_address_1($store_addr);
                    $order->save();
                }
                
                //$order->set_billing_address_1($T_Storeid ." " .$T_Storename ." " .$T_Storeaddress);
            }
            
            //<smilepay> modify address end

            $title =b2ub2( $this->title);
			$pay_ok="繳費方式：<font color=red>$title</font><br>取貨門市：<font color=red>".$T_Storename.$T_Storeid."</font><br>";
            $pay_ok .= "門市地址：<font color=red>".b2ub2($T_Storeaddress)."</font><br>";
            $pay_ok .= "取貨人姓名：<font color=red>".b2ub2($pur_name )."</font><br>";
            $pay_ok .= "取貨人電話：<font color=red>".b2ub2($order->get_billing_phone())."</font><br>";
			
            $order->add_order_note(u2bb2($pay_ok),"1");
			$ordertext="請勿刪除此資訊，速買配SmilePay追蹤碼：".$T_Smseid;
			$order->add_order_note(u2bb2($ordertext),"0");
			echo u2bb2("<font color='red' size='3'>注意：訂單已成立，待商家寄貨後，商品抵達門市將會以簡訊通知付款取貨</font><br><br>");
			echo u2bb2("<h1>繳費資訊</h1>".$pay_ok);
			echo "<br><br>".$SmilePayb2c_args['Order_OKmain'];
            
		}
        else
        {
            echo u2bb2("<font color='red' size='3'>如忘記<u>繳費資訊</u>請自上方選單列<u>帳號設定->訂單->檢視</u></font>");
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
                wc_add_notice(u2bb2("<br>速買配商家參數錯誤，請與商家聯繫<br>") , 'error' );
                return;
            }
			/*//--物流start
			$shipping_method= $order->get_shipping_method();

            include_once('smilepayb2cp.php');
            if(!class_exists('WC_Smilepayb2cp_Shipping_Method'))
                Smilepayb2cp_shipping_method_init();
            $smilepayb2cp_obj=new WC_Smilepayb2cp_Shipping_Method();

            //重新結帳
            if(isset($_REQUEST['pay_for_order']) && $_REQUEST['pay_for_order']=='true')
            {
                WC()->session->set( 'smilepay_credit_pay_for_order', true );
            }
            
            if($smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))
			//if($shipping_method==u2bb2("速買配7-11門市取貨付款"))
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
				if(substr($order->get_billing_phone(),0,2)!="09"||mb_strlen($order->get_billing_phone())!=10||mb_strlen( $username, "utf-8")>$limit_name_number){
                    wc_add_notice( __(u2bb2("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
			else
			{
                $title=b2ub2($this->title);
                wc_add_notice( __(u2bb2("<br><font color='red'>配送方式選擇錯誤，請確認配送方式為【<b>$title</b>】</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
			    exit;
			}
			//--物流end*/
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
    function add_SmilePayb2c_gateway($methods) {
        $methods[] = 'WC_SmilePayb2c';
        return $methods;
    }


  

    add_filter('woocommerce_payment_gateways', 'add_SmilePayb2c_gateway');


    
}
    

    

function u2bb2($text)//畫面輸出
{	return iconv("big5","UTF-8",$text);}
function b2ub2($text)//寫入資料庫
{	return iconv("UTF-8","big5",$text);}
function b2caddtocart($thepostid)
{
global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
/*$sqlll="SHOW TABLES LIKE  '%comments%'";
$recheck=mysql_query($sqlll);
$row = mysql_fetch_array($recheck);
$tablelist=$row[0];
$tablelist=explode('_',$tablelist);
$tablelist=$tablelist[0].'_';*/
$tablelist= $wpdb->prefix;
//取商品訂單號
$order_items = $wpdb->get_results( "SELECT * FROM `".$tablelist."woocommerce_order_items"."` WHERE `order_id` = '$thepostid' " );
	foreach ( $order_items as $order_no ) 
	{
		$vr =$order_no->order_item_id;
		//取商品ID,數量
		if($order_no->order_item_type=="line_item"){
			$order_itemmeta = $wpdb->get_results( "SELECT * FROM `".$tablelist."woocommerce_order_itemmeta"."` WHERE `order_item_id` = '$vr' "  );
			foreach ( $order_itemmeta as $order_id ) 
			{	
				if($order_id->meta_key=="_product_id"){$order_add_id=$order_id->meta_value;}
				if($order_id->meta_key=="_qty"){$order_add_num=$order_id->meta_value;}
			}
			WC()->cart->add_to_cart($order_add_id,$order_add_num);
		}
	}
}








?>