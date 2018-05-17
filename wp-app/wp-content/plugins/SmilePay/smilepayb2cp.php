<?php
/*
 *Plugin Name: SmilePayB2C(PAY)
 *Description: SmilePay 7-11 b2c Shipping
 * Version: 3.0.5
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
*/
 
/**
 * Check if WooCommerce is active
 */

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action('plugins_loaded', 'Smilepayb2cp_shipping_method_init', 0);
 
    add_action('woocommerce_checkout_update_order_review','Smilepayb2cp_shipping_method_match');
    add_action('before_woocommerce_pay','Smilepayb2cp_shipping_method_match_orderpay');

    //配送方式過濾付款方式
    function Smilepayb2cp_shipping_method_match()
    {

        $packages = WC()->cart->get_shipping_packages();
      

        foreach ( $packages as $package_key => $package ) 
        {
            WC()->shipping()->load_shipping_methods( $package );
           
        }
        $shipping_methods =  WC()->shipping()->shipping_methods;
        $payment_gateways = WC()->payment_gateways();



        foreach($shipping_methods as $shipping_method)
        {
           if( $shipping_method->id == 'smilepay_711_b2c_shippingpay' && $shipping_method->get_instance_id() != 0)
           {
               $smilepay_b2cp_shipping_method =  $shipping_method;
            
               break;
           }
           
        }
        if( isset($smilepay_b2cp_shipping_method) &&  $smilepay_b2cp_shipping_method)
        {
            if($smilepay_b2cp_shipping_method->enabled!="yes")
                return;
            if($smilepay_b2cp_shipping_method->instance_settings['payment_filter_enabled']!="yes")
                return;
           
        }
        

        if(isset($_REQUEST['shipping_method'][0] ))
            $selected_shipping_method = $_REQUEST['shipping_method'][0] ;
        else
        {
            $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
            if(empty($chosen_methods))
                return;
            $selected_shipping_method = $chosen_methods[0]; 
        }
                
        foreach ( $payment_gateways as $payment_gateway ) {
             foreach ( $payment_gateway as $gateway ) {
                    if(strtolower($selected_shipping_method) == 'smilepay_711_b2c_shippingpay')
                    {
               
                        if($gateway->id != 'smilepayb2c')
                            $gateway->enabled = 'no';
                    }
                    else
                    {
                        if($gateway->id == 'smilepayb2c')
                            $gateway->enabled = 'no';
                    }

             }
        }
    
         
      
    }
    //配送方式過濾付款方式 重結帳
    function Smilepayb2cp_shipping_method_match_orderpay()
    {
        global $wp;

        if ( isset( $_GET['pay_for_order']) && $_GET['pay_for_order'] == 'true') 
        {
            $order_id = $wp->query_vars['order-pay'];
           
            if($order_id)
            {
                $order = wc_get_order( $order_id ); 
                if(!$order)
                    return;
                //$packages = WC()->cart->get_shipping_packages();
                
                //make fake package for find shipping region
                $packages = array();
                $packages[0]['destination']['country']   =  $order->get_shipping_country();
			    $packages[0]['destination']['state']     =  $order->get_shipping_state();
			    $packages[0]['destination']['postcode']  =  $order->get_shipping_postcode();
			    $packages[0]['destination']['city']      =  $order->get_shipping_city();
			    $packages[0]['destination']['address']   =  $order->get_shipping_address_1();
			    $packages[0]['destination']['address_2'] =  $order->get_shipping_address_2();

              
               
                //load shipping method
                foreach ( $packages as $package_key => $package ) 
                {
                     WC()->shipping()->load_shipping_methods( $package );
                }

              
                $shipping_methods =  WC()->shipping()->shipping_methods;
                $payment_gateways = WC()->payment_gateways();
                
                    
                foreach($shipping_methods as $shipping_method)
                {

                    if( $shipping_method->id == 'smilepay_711_b2c_shippingpay' && $shipping_method->get_instance_id() != 0)
                    {
                        $smilepay_b2cp_shipping_method =  $shipping_method;
                        break;
                    }
                }

                if( isset($smilepay_b2cp_shipping_method) &&  $smilepay_b2cp_shipping_method)
                {
                    if($smilepay_b2cp_shipping_method->enabled!="yes")
                        return;
                    if($smilepay_b2cp_shipping_method->instance_settings['payment_filter_enabled']!="yes")
                        return;
                }
         
                $selected_shipping_method =    $order->has_shipping_method('smilepay_711_b2c_shippingpay');

                
                foreach ( $payment_gateways as $payment_gateway ) {
                    foreach ( $payment_gateway as $gateway ) {
                        if($selected_shipping_method)
                        {
               
                            if($gateway->id != 'smilepayb2c')
                                $gateway->enabled = 'no';
                        }
                        else
                        {
                            if($gateway->id == 'smilepayb2c')
                                $gateway->enabled = 'no';
                        }   

                    }
                }


            }
            else
                return;



        }
    }


	function  Smilepayb2cp_shipping_method_init() {


		if ( ! class_exists( 'WC_Smilepayb2cp_Shipping_Method' ) ) {
			class WC_Smilepayb2cp_Shipping_Method extends WC_Shipping_Method {

				public function __construct($instance_id = 0) {
                    include_once("smilepayb2c.php");

                    $this->instance_id 			 = absint( $instance_id );
                    $smilepayb2cdata = new WC_SmilePayb2c();
                    
					$this->id                 = 'smilepay_711_b2c_shippingpay';
					$this->method_title       = __( 'SmilePay b2c 7-11 Shipping(PAY)' ); 
                    $this->method_description    = u2bbp('7-11超商取貨付款');

                    //for woocommerce 2.6 modify
					//$this->enabled            = "yes";
                    $this->supports              = array(
			                                        'shipping-zones',
			                                        'instance-settings',
			                                        'instance-settings-modal',
		                                            );

                    if(isset($smilepayb2cdata->title) && !empty($smilepayb2cdata->title))
					    $this->title              = $smilepayb2cdata->title;//u2bbp("速買配7-11門市取貨付款"); 
                    else
                        $this->title              = u2bbp("SmilePay 7-11超商取貨付款 繳費"); 
					$this->init();
            
				}

				public function init_form_fields() {  //後台設置欄位

					//for woocommerce 2.6 modify
					//$this->form_fields = array(
                    $this->instance_form_fields = array(
					/*	'enabled' => array(
							'title' => __(u2bbp("啟用/關閉"), 'woocommerce'),
							'type' => 'checkbox',
							'default' => 'yes',
                   			'label' =>  $this->title,
						),*/
						'fee' => array(
							'title'       => __(u2bbp('費用'), 'woocommerce' ),
							'type'        => 'price',
							'description' => __( '', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
						'fee_top' => array(
							'title'       => __(u2bbp('免運費門檻'), 'woocommerce' ),
							'type'        => 'price',
							'description' => __(u2bbp("當達到此金額時，免運費，不使用請保持空白"), 'woocommerce'),
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
                        'min_total' => array(
							'title'       => u2bbp('最低消費金額'),
							'type'        => 'price',
							'description' => u2bbp('當訂單金額達到此設定金額，才可以使用此配送方式，不含運費'),
							'default'     => '',
						),			
                        'payment_filter_enabled' => array(
							'title' =>  u2bbp("SmilePay 配送方式搭配付款方式規則"),
                            'description' => __(u2bbp("開啟此功能後，在結帳時，當配送方式是取貨付款，付款方式只有取貨付款，若配送方式不是取貨付款，則付款方式不會出現取貨付款"), 'woocommerce'),
							'type' => 'checkbox',
							'default' => 'yes',
                   			'label' => u2bbp("啟用"),
						),			
						'hiddtext' => array(
							'title' => __(u2bbp('超商純取貨注意事項'), 'woocommerce'),
							'type' => 'hidden',
							'description' => __(u2bbp("使用超商純取貨功能，需注意以下事項：
														<br>※<font color='red'>此模組僅支援【SmilePay 超商取貨付款】金流模組，請確認啟用</font>							
														<br>1.請先至SmilePay商家後台開啟取貨功能*<a target='_blank' href='http://www.smilepay.net/RVG.ASP'>商家後台</a>*
														<br>2.帳單資訊中<font color='red'>聯絡電話</font>必須為<font color='red'>手機號碼</font>，商品到門市時會以簡訊通知。
														<br>3.消費者成立訂單後，可至SmilePay商家後台中取得<font color='red'>交貨便代碼</font>。
														<br>4.消費者成立訂單後，可至訂單備註中點選<font color='red'>開啟交貨便服務單</font>。														
														<br>5.請將<font color='red'>交貨便代碼</font>手動新增至商品備註，以利消費者查詢物流狀態。
														<br>6.更多說明請參閱，<font color='red'>SmilePay網站說明</font>與<font color='red'>WooCommerce模組說明文件</font>。
														"), 'woocommerce'),
							'default' => __('', 'woocommerce')
						)         
					);
				}
 
				function init() {
					$this->init_form_fields(); 
					//$this->init_settings();
					$this->fee          = $this->get_option( 'fee' );
					$this->fee_top= $this->get_option( 'fee_top' );	
                    $this->min_total =$this->get_option('min_total');	
                    $this->payment_filter_enabled= $this->get_option( 'payment_filter_enabled' );									
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

                    if($this->instance_id >0 &&  $this->enabled == 'yes' )
                    {
                         add_action('woocommerce_after_checkout_validation', array( $this, 'checkout_validate' ),10,2);
                    }
                   
				}
                function checkout_validate( $data, $errors )
                {
                   //$shipping_methods =$data['shipping_method'];
                    $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
                    $chosen_payment_method = WC()->session->get( 'chosen_payment_method' ); 
                    $find_shipping_method=FALSE;
                    foreach($chosen_shipping_methods as $shipping_method)
                    {
                        if($shipping_method == $this->id)
                        {
                            $find_shipping_method=TRUE;
                        }
                    }
                    if( $find_shipping_method)
                    {
                        
                        $username=$data['billing_first_name'].$data['billing_last_name'];
                        if( !preg_match("/[^a-zA-Z0-9 ]/",$username))
                        {
                            $limit_name_number =10;
                        }
                        else
                        {
                            $limit_name_number =5;
                        }
				        if(substr($data['billing_phone'],0,2)!="09"||mb_strlen($data['billing_phone'])!=10||mb_strlen( $username, "utf-8")>$limit_name_number){
                           $errors->add( 'shipping',u2bbp("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"));
                   
				        }
                        if(strtolower($chosen_payment_method) != 'smilepayb2c' )
                        {
                           $errors->add( 'shipping',u2bbp("<br>付款方式選擇錯誤，此配送方式不可選該付款方式，請更換其一<br>"));
                        }
                    }

                }
				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package = array() ) {
					$shipping_total=0;
					$shipping_total=$this->fee;
					
					$fee_top=$this->fee_top;
					$total = WC()->cart->cart_contents_total;					
					if($total>=$fee_top AND $fee_top<>0 AND $fee_top<>NULL)
					{$shipping_total=0;}
					
					$rate = array(
						'id' => $this->id,
						'label' => $this->title,
						'cost' => $shipping_total
					);
                    if($total < $this->min_total)
                        return;
					// Register the rate
					$this->add_rate( $rate );
				}
                // 是否選用B2CP運送
                // $order_id:訂單編號
                public function isSelectedSmilepayB2Cp($order_id)
                {
                 
                    if(!isset($order_id) || empty($order_id))
                    {
                        return false;
                    }
                    
                    $order = new WC_Order($order_id);
     
                    if(!isset($order) || empty($order))
                        return false;

                    if(is_admin())
                    {
                        $shipping_methods = $order->get_shipping_methods();
		                $has_method = false;
                             
         
		                if ( empty( $shipping_methods ) ) {
			                return false;
		                }
                         

		                foreach ( $shipping_methods as $shipping_method ) {

                            

                            if ($shipping_method->get_method_id() == $this->id ) {
				                    return true;
			                }   
                            
			                
		                }
                        return FALSE;

                    }
                    else
                    {
                         if( $order->has_shipping_method($this->id ))
                            return true;
                        else
                            return false;
                    }
                   
                 }
			}
		}

	}
 
	add_action( 'woocommerce_shipping_init', 'Smilepayb2cp_shipping_method_init' );
    
 
	function add_Smilepayb2cp_shipping_method( $methods ) {
        //fill this->id for woocommerce2.6 modify
		//$methods[] = 'WC_Smilepayb2cp_Shipping_Method';
        $methods['smilepay_711_b2c_shippingpay'] = 'WC_Smilepayb2cp_Shipping_Method';
		return $methods;
	}
 
	add_filter( 'woocommerce_shipping_methods', 'add_Smilepayb2cp_shipping_method' );
}
function u2bbp($text)//畫面輸出
{	return iconv("big5","UTF-8",$text);}
function b2ubp($text)//寫入資料庫
{	return iconv("UTF-8","big5",$text);}
