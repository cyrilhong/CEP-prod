<?php
/**
 * Plugin Name: SmilePay_Ezcat (Un-Pay)
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay Ezcat UnPay
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */
 
/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action('plugins_loaded', 'smilepayezcatup_shipping_method_init', 0);

    /*add_action('woocommerce_checkout_update_order_review','Smilepayezcatup_shipping_method_match');
    add_action('before_woocommerce_pay','Smilepayezcatup_shipping_method_match_orderpay');
    //配送方式過濾付款方式 重結帳
    function Smilepayezcatup_shipping_method_match_orderpay()
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

                    if( $shipping_method->id == 'smilepay_711_ezpay_shippingunpay' && $shipping_method->get_instance_id() != 0)
                    {
                        $smilepay_ezcatup_shipping_method =  $shipping_method;
                        break;
                    }
                }

                if( isset($smilepay_ezcatup_shipping_method) &&  $smilepay_ezcatup_shipping_method)
                {
                    if($smilepay_ezcatup_shipping_method->enabled!="yes")
                        return;
                    if($smilepay_ezcatup_shipping_method->instance_settings['payment_filter_enabled']!="yes")
                        return;
                }
         
                $selected_shipping_method =    $order->has_shipping_method('smilepay_711_ezpay_shippingunpay');

                
                foreach ( $payment_gateways as $payment_gateway ) {
                    foreach ( $payment_gateway as $gateway ) {
                        if($selected_shipping_method)
                        {
               
                             if(!in_array($gateway->id, $smilepay_ezcatup_shipping_method->filter_payment) )
                                 $gateway->enabled = 'no';
                        }

                    }
                }


            }
            else
                return;
        }
    }*/


	function  smilepayezcatup_shipping_method_init() {
		if ( ! class_exists( 'WC_Smilepayezcatup_Shipping_Method' ) ) {
            
			class WC_Smilepayezcatup_Shipping_Method extends WC_Shipping_Method {
                var $filter_payment;
                const  SERVICE_TEMPERATURE_NORMAL_ID = 'smilepay_ezcatup_normal';
                const  SERVICE_TEMPERATURE_FRIDGE_ID = 'smilepay_ezcatup_fridge';
                const  SERVICE_TEMPERATURE_FREEZE_ID = 'smilepay_ezcatup_freeze';
                static $instance_hook_init=0;
                static $support_service=array(self::SERVICE_TEMPERATURE_NORMAL_ID,self::SERVICE_TEMPERATURE_FRIDGE_ID,self::SERVICE_TEMPERATURE_FREEZE_ID);

				public function __construct($instance_id = 0) {
					$this->id                 = 'smilepay_ezcat_shippingunpay';
					$this->method_title       = __( 'SmilePay Ezcat Shipping(UN-PAY)' ); 
                    
                    
                  
                    //for woocommerce 2.6 modify
					//$this->enabled            = "yes";
                    $this->instance_id 			 = absint( $instance_id );
                    $this->supports              = array(
			                                        'shipping-zones',
			                                        'instance-settings',
			                                        'instance-settings-modal',
		                                            );
                    $this->method_description    = '速買配 黑貓宅配(取貨不付款)';

                    $this->service_id   = array(
                                            'normal' => self::SERVICE_TEMPERATURE_NORMAL_ID,
                                            'fridge' => self::SERVICE_TEMPERATURE_FRIDGE_ID,
                                            'freeze' => self::SERVICE_TEMPERATURE_FREEZE_ID
                                        );
					
                 
				 
                    

                    $this->title = '速買配 黑貓宅配';
                    $this->init();

                    $this->service_name   = array(
                                            'normal' => $this->normal_title,
                                            'fridge' => $this->fridge_title,
                                            'freeze' => $this->freeze_title
                                            );
                    if( empty($this->service_name['normal']))
                        $this->service_name['normal'] ='速買配 黑貓宅配(常溫)';

                    if( empty($this->service_name['fridge']))
                        $this->service_name['fridge'] ='速買配 黑貓宅配(冷藏)';

                    if( empty($this->service_name['freeze']))
                        $this->service_name['freeze'] ='速買配 黑貓宅配(冷凍)';
                       
                    
				}

				public function init_form_fields() {  //後台設置欄位
					
					//for woocommerce 2.6 modify
					//$this->form_fields = array(
                    $this->instance_form_fields = array(
						/*'enabled' => array(
							'title' => __(u2bcu("啟用/關閉"), 'woocommerce'),
							'type' => 'checkbox',
							'default' => 'yes',
              'label' => __(u2bcu("SmilePay 速買配超商宅配"), 'woocommerce'),
						),*/
                     array( 'title' => '黑貓常溫設定', 'type' => 'title', 'id' => 'smilepay_ezcatup_normal_title' ),
                        'normal_enabled' => array(
							'title' =>  "宅配(常溫)",
                            'description' => "開啟此功能後，配送方式會多一個黑貓宅配常溫",
							'type' => 'checkbox',
							'default' => 'yes',
                   			'label' => "啟用",
						),	
                        'normal_title' => array(
							'title' =>  "配送方式名稱(常溫)",
                            'description' => "前台顯示的配送名稱",
							'type' => 'text',
							'default' => '速買配 黑貓宅配(常溫)',
                   			'label' => "前台名稱(常溫)",
						),	
                        'fee' => array(
							'title'       => '費用(常溫)',
							'type'        => 'price',
							'description' => __( '', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
                        array( 'title' => '黑貓冷藏設定', 'type' => 'title', 'id' => 'smilepay_ezcatup_fridge_title' ),
                        'fridge_enabled' => array(
							'title' =>  "宅配(冷藏)",
                            'description' => "開啟此功能後，配送方式會多一個黑貓宅配冷藏",
							'type' => 'checkbox',
							'default' => 'yes',
                   			'label' => "啟用",
						),	

                        'fridge_title' => array(
							'title' =>  "配送方式名稱(冷藏)",
                            'description' => "前台顯示的配送名稱",
							'type' => 'text',
							'default' => '速買配 黑貓宅配(冷藏)',
                   			'label' => "前台名稱(冷藏)",
						),	
                         'fridge_fee' => array(
							'title'       => '費用(冷藏)',
							'type'        => 'price',
							'description' => __( '', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
                        array( 'title' => '黑貓冷凍設定', 'type' => 'title', 'id' => 'smilepay_ezcatup_freeze_title' ),
                        'freeze_enabled' => array(
							'title' =>  "宅配(冷凍)",
                            'description' => "開啟此功能後，配送方式會多一個黑貓宅配冷凍",
							'type' => 'checkbox',
							'default' => 'yes',
                   			'label' => "啟用",
						),	
                        'freeze_title' => array(
							'title' =>  "配送方式名稱(冷凍)",
                            'description' => "前台顯示的配送名稱",
							'type' => 'text',
							'default' => '速買配 黑貓宅配(冷凍)',
                   			'label' => "前台名稱(冷凍)",
						),	
                        'freeze_fee' => array(
							'title'       => '費用(冷凍)',
							'type'        => 'price',
							'description' => __( '', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
                        array( 'title' => '黑貓共通設定', 'type' => 'title', 'id' => 'smilepay_ezcatup_title' ),
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
                        'fee_top' => array(
							'title'       => '免運費門檻',
							'type'        => 'price',
							'description' => "當達到此金額時，免運費，不使用請保持空白",
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
                        'min_total' => array(
							'title'       => '最低消費金額',
							'type'        => 'price',
							'description' =>'當訂單金額達到此設定金額，才可以使用此配送方式，不含運費',
							'default'     => '',
						),		
                        
						'hiddtext' => array(
							'title' => '黑貓宅配注意事項',
							'type' => 'hidden',
							'description' => '',
							'default' => ''
						)         
					);
				}
 
				function init() {
					$this->init_form_fields(); 
					$this->init_settings();
					$this->fee= $this->get_option( 'fee' );
                    $this->fridge_fee          = $this->get_option( 'fridge_fee' );
                    $this->freeze_fee          = $this->get_option( 'freeze_fee' );
					$this->fee_top= $this->get_option( 'fee_top' );	
                    $this->min_total =$this->get_option('min_total');	
                    //$this->payment_filter_enabled= $this->get_option( 'payment_filter_enabled' );	
                   
                    $this->normal_enabled = $this->get_option( 'normal_enabled' );
                    $this->fridge_enabled = $this->get_option( 'fridge_enabled' );
                    $this->freeze_enabled = $this->get_option( 'freeze_enabled' );

                    $this->normal_title = $this->get_option( 'normal_title' );
                    $this->fridge_title = $this->get_option( 'fridge_title' );
                    $this->freeze_title = $this->get_option( 'freeze_title' );

                    $this->dcvc = $this->get_option( 'dcvc' );
                    $this->Rvg2c = $this->get_option( 'Rvg2c' );
                    $this->Verify_key = $this->get_option( 'Verify_key' );
                    //$this->payment_filter_enabled= $this->get_option( 'payment_filter_enabled' );
                    
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
					
					
                    if($this->instance_id >0 &&  $this->enabled == 'yes' )
                    {
                        add_action('woocommerce_after_checkout_validation', array( $this, 'checkout_validate' ),10,2);
                        //add_action( 'woocommerce_checkout_order_processed', array($this,'create_smilepay_order_process'),10,3 );
                        //self::$instance_hook_init =1;
                    }
                   
				}

                


                function checkout_validate( $data, $errors )
                {
                   //$shipping_methods =$data['shipping_method'];
                    /*$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
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
                           $errors->add( 'shipping',u2bcu("<br>電話格式錯誤，範例09XX123456(共10碼)。<br>消費者姓名不可超過5個字，純英文則不可超過10個字。請再次確認。<br>"));
                   
				        }
                        if(!in_array($chosen_payment_method,$this->filter_payment) )
                        {
                           $errors->add( 'shipping',u2bcu("<br>付款方式選擇錯誤，此配送方式不可選該付款方式，請更換其一<br>"));
                        }
                    }*/

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
					$fee_top=$this->fee_top;
				    $total = WC()->cart->cart_contents_total;	
                    	
					if($this->normal_enabled == "yes")
                    {
                        $shipping_total=$this->fee;	
                        //if(isset($outside_fee))
                         //   $shipping_total += $outside_fee;
					    if($total>=$fee_top AND $fee_top<>0 AND $fee_top<>NULL)
					    {$shipping_total=0;}
                        $rate = array(
						    'id' => $this->service_id['normal'],
						    'label' =>$this->service_name['normal'],
						    'cost' => $shipping_total
					    );
                        // Register the rate
                        if($total >= $this->min_total)
                            $this->add_rate( $rate );
                    }

					if($this->fridge_enabled == "yes")
                    {
                        $shipping_total=$this->fridge_fee;
                        //if(isset($outside_fee))
                          //  $shipping_total += $outside_fee;			
					    if($total>=$fee_top AND $fee_top<>0 AND $fee_top<>NULL)
					    {$shipping_total=0;}
                        $rate = array(
						    'id' => $this->service_id['fridge'],
						    'label' =>$this->service_name['fridge'],
						    'cost' => $shipping_total
					    );
                        // Register the rate
                        if($total >= $this->min_total)
                            $this->add_rate( $rate );
                    }
                    
                    if($this->freeze_enabled == "yes")
                    {
                        $shipping_total=$this->freeze_fee;	
                        //if(isset($outside_fee))
                           // $shipping_total += $outside_fee;		
					    if($total>=$fee_top AND $fee_top<>0 AND $fee_top<>NULL)
					    {$shipping_total=0;}
                        $rate = array(
						    'id' => $this->service_id['freeze'],
						    'label' =>$this->service_name['freeze'],
						    'cost' => $shipping_total
					    );
                        // Register the rate
                        if($total >= $this->min_total)
                            $this->add_rate( $rate );
                    }   
				}
                // 是否選用ezcatup運送
                // $order_id:訂單編號
                public function isSelectedSmilepayezcatup($order_id)
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
                                if ( in_array($shipping_method->get_method_id(),$this->service_id)  ) {
				                    return true;
			                    }
		                }
                        return FALSE;

                    }
                    else
                    {

                        foreach($this->service_id as $service_id)
                        {
                            if( $order->has_shipping_method($service_id ))
                                return true;
                        }

                        return false;
                    }
                 }
                 public function create_smilepay_order($order_id)
                 {
                    $order = new WC_Order($order_id);

                    if($order->has_shipping_method(self::SERVICE_TEMPERATURE_NORMAL_ID))
                        $temperature="0001";
                    elseif($order->has_shipping_method(self::SERVICE_TEMPERATURE_FRIDGE_ID))
                        $temperature="0002";
                    elseif($order->has_shipping_method( self::SERVICE_TEMPERATURE_FREEZE_ID))
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
                    $pur_name = $order->get_shipping_last_name() . $order->get_shipping_first_name();
			        $smilepay_gateway = 'https://ssl.smse.com.tw/api/sppayment.asp';
			        $post_strall='Dcvc=' . $this->dcvc .
						        '&Pay_zg=82' .
                                '&Pay_subzg=TCAT' .
						        '&Rvg2c=' .  $this->Rvg2c .
                                '&Verify_key=' . $this->Verify_key.
						        '&Od_sob=' . $order_id .
						        '&Pur_name=' .urlencode($pur_name ).
						        '&Mobile_number=' .urlencode($order->get_billing_phone()).
						        '&Email=' .urlencode($order->get_billing_email()).
						        '&Data_id=' . $order_id .
						        '&Amount=' . intval(round($order->get_total())) .
						        //'&Roturl_status=' . 'woook'.
						        //'&Roturl=' . $Vk.
						        //'&MapRoturl=' . urlencode($URL).
                                "&temperature=". $temperature .
                                '&Address='.urlencode($address) .
						        '&Remark=woocommerce';
                                //<SmilePay>item add start
					            //'&Remark='. $products;
                                //<SmilePay>item add end
                                
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
                    $xml = simplexml_load_string($string);
			        $Status = $xml->Status;
                   
                    if($xml !== false)
                        return (array)$xml;
                    else
                        return  $string;
                 }


                 public function create_smilepay_order_process($order_id, $posted_data, $order)
                 {
                     if($this->isSelectedSmilepayezcatup($order_id))
                     { 
                        
                        //$order = new WC_Order($order_id);
                        $result = $this->create_smilepay_order($order_id);
                        if(!is_array( $result))
                        {
                             $result = array(
                                'Status' => '-10000',
                                'Desc' =>  'response empty or connect fail'
                            );
                            return $result; 
                            //$order->update_status('cancelled');
                        }
			            elseif(isset($result['Status']) && $result['Status']=="1")
			            {
                            $T_Smseid = $result['SmilePayNO'];
                            $ordertext = "速買配黑貓宅配<br>請勿刪除此資訊，速買配SmilePay追蹤碼：".$T_Smseid;
                            //update_post_meta( $order_id, 'smilepay_ezcatp_smseid', $T_Smseid )	;

                            self::set_order_smseid($order_id, $T_Smseid);

                            $order->add_order_note($ordertext,"0");
                            //$order->update_status('on-hold');		
                        }
                        else
                        {
                            /*$T_Smseid = $result['SmilePayNO'];
                            $ordertext = "訂單成立失敗，錯誤碼：". $result['Status'] ."錯誤描述：".$result['Desc'];
                            $order->add_order_note($ordertext,"0");*/
                            //$order->update_status('cancelled');
                            //$error_message= "<span style='color:red;'>" .  $this->title . "訂單成立失敗，請與商家聯繫" ."</span>";
                            //$order->add_order_note($error_message,"1");
                        }
                         
                     }
                     else
                     {
                         return array(
                                    'Status'=> '-10009',
                                    'Desc' => 'shipping_method error'
                                );
                     }
                     return $result;
                 }

                 public static function isSupport_Service($method_id)
                 {
                     if(in_array($method_id,self::$support_service))
                        return TRUE;
                     return FALSE;
                 } 

                 public static function getOrderSmilepayezcatupPlugin($order_id)
                 {
                    $order = new WC_Order($order_id);    
                     //make fake package for find shipping region
                    $packages = array();
                    $packages['destination']['country']   =  $order->get_shipping_country();
		            $packages['destination']['state']     =  $order->get_shipping_state();
		            $packages['destination']['postcode']  =  $order->get_shipping_postcode();
		            $packages['destination']['city']      =  $order->get_shipping_city();
		            $packages['destination']['address']   =  $order->get_shipping_address_1();
		            $packages['destination']['address_2'] =  $order->get_shipping_address_2();
                    $zone = WC_Shipping_Zones::get_zone_matching_package($packages);
                    $shipping_methods = $zone->get_shipping_methods(true);
                   
                    foreach($shipping_methods as $shipping_method)
                    {
                        
                        if($shipping_method->id=='smilepay_ezcat_shippingunpay' )
                        {
                            return $shipping_method;  
                        }
                    }
                    return NULL;
                 }

                 public static function api_process()
                 {
                    $order_url = get_admin_url().'edit.php?post_type=shop_order';
           
                    if(!isset($_REQUEST['api_action']))
                    {
                        return FALSE;
                    }
                    else
                        $action =  $_REQUEST['api_action'] ;

                    if( $action ==  'create_order')
                    {
                        if(isset($_REQUEST['post']))
                            $order_id = $_REQUEST['post'];
                        else
                            $order_id = 0;

                        $order_url =  get_admin_url()."post.php?post=$order_id&action=edit";


                        $smilepayezcatup_plug = self::getOrderSmilepayezcatupPlugin($order_id);
                        if($smilepayezcatup_plug != null)
                        {
                            $order = new WC_Order($order_id);   
                            $smseid = self::get_order_smseid($order_id); 
                            if(empty($smseid))
                            {
                               $result =  $smilepayezcatup_plug->create_smilepay_order_process($order_id,NULL,$order);
                                if($result['Status'] != "1")
                                {
                                    $errormsg = 'order 錯誤碼:'. $result['Status'] . "<br>錯誤描述:".$result['Desc'];
                                    setcookie("smilepay_ezcatup_admin_shipping_error", $errormsg, time()+30);
                                    wp_redirect($order_url);
                                    die();
                                } 
                            }
                               
                            
                            $tracknum =  self::get_order_tracknum($order_id);
                            $sp_length = $_REQUEST['sp_length'];
                            $sp_width = $_REQUEST['sp_width'];
                            $sp_height = $_REQUEST['sp_height'];
                            $lwh = intval( round($sp_length + $sp_width + $sp_height));

                           
                            if(empty($tracknum))
                            {
                                $result_shipping = $smilepayezcatup_plug->create_smilepay_shipping_process($order_id,$lwh);

                              
                                if($result_shipping['Status'] != "1")
                                {
                                    $errormsg = 'shipping 錯誤碼:'. $result_shipping['Status'] . "<br>錯誤描述:".$result_shipping['Desc'];
                                    setcookie("smilepay_ezcatup_admin_shipping_error", $errormsg, time()+30);
                                    wp_redirect($order_url);
                                    die();
                                }
                            }
                            
                            
                            wp_redirect($order_url);
                            die();
                        }
                        
                    }
                    
                }

                public function create_smilepay_shipping_order($order_id,$lwh)
                {
                    $order = new WC_Order($order_id);

           
                    if($order->has_shipping_method(self::SERVICE_TEMPERATURE_NORMAL_ID))
                         $max_lwh=150;
                    elseif($order->has_shipping_method(self::SERVICE_TEMPERATURE_FRIDGE_ID))
                        $max_lwh=120;
                    elseif($order->has_shipping_method( self::SERVICE_TEMPERATURE_FREEZE_ID))
                        $max_lwh=120;

                    if($lwh <= 60 )
                        $packagea_size = "0001";
                    elseif($lwh>60 && $lwh <= 90)
                        $packagea_size = "0002";
                    elseif($lwh>90 && $lwh <= 120)
                        $packagea_size = "0003";
                    elseif($lwh>120 && $lwh <= 150)
                        $packagea_size = "0004";

                    $smseid = self::get_order_smseid($order_id); 
                    if( empty($smseid))
                    {
                        $result = array(
                                'Status' => '-10202',
                                'Desc' =>  'No Smseid Re-run the Process.'
                            );
                        return $result; 
                    }
                    $post_data = array();
                    $post_data['Dcvc'] =  trim( $this->dcvc);
                    $post_data['Rvg2c'] = trim($this->Rvg2c);
                    $post_data['Verify_key'] = trim($this->Verify_key);
                    $post_data['smseid'] = $smseid;
                    $post_data['package_size'] = $packagea_size;
                    $post_data['delivery_date'] = date ('Y-m-d', mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
                    $post_data['delivery_timezone'] = "4";
                  
                                     
			        $smilepay_gateway = 'https://ssl.smse.com.tw/api/ezcatGetTrackNum.asp';
			      
                                
                    $ch = curl_init();
			        curl_setopt($ch, CURLOPT_URL, $smilepay_gateway);
			        curl_setopt($ch, CURLOPT_VERBOSE, 1);
			        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			        curl_setopt($ch, CURLOPT_POST, 1);
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data,'','&'));
			        $string = curl_exec($ch);
			        curl_close($ch);
                    $xml = simplexml_load_string($string);
			        $Status = $xml->Status;
                   
                    if($xml !== false)
                        return (array)$xml;
                    else
                        return  $string;
                }

                public function create_smilepay_shipping_process($order_id,$lwh)
                {
                    if($this->isSelectedSmilepayezcatup($order_id))
                    { 
                        
                        $order = new WC_Order($order_id);
                        $result = $this->create_smilepay_shipping_order($order_id,$lwh);
                        if(!is_array( $result))
                        {
                            //$ordertext= "<span style='color:red;'>" .'連線未如預期，請稍後嘗試，若持續無法解決請與廠商聯繫'."</span>";
                            //$order->add_order_note($ordertext,"0");
                            //$order->update_status('cancelled');
                             $result = array(
                                'Status' => '-10000',
                                'Desc' =>  'response empty or connect fail'
                            );
                            return $result; 
                        }
			            elseif(isset($result['Status']) && $result['Status']=="1")
			            {
                            $tracknum = $result['TrackNum'];
                            $ordertext = "速買配黑貓宅配<br>請勿刪除此資訊，黑貓託運單號：".$tracknum;
                            //update_post_meta( $order_id, 'smilepay_ezcatp_smseid', $T_Smseid )	;

                            self::set_order_tracknum($order_id, $tracknum);

                            $order->add_order_note($ordertext,"0");
                            $ordertext_customer = "黑貓託運單號：".$tracknum;
                            $order->add_order_note($ordertext_customer,"1");
                            //$order->update_status('on-hold');		
                        }
                        else
                        {
                            /*$T_Smseid = $result['SmilePayNO'];
                            $ordertext = "訂單成立失敗，錯誤碼：". $result['Status'] ."錯誤描述：".$result['Desc'];
                            $order->add_order_note($ordertext,"0");
                            */
                        }
                         
                     }
                     else
                     {
                         return array(
                                    'Status'=> '-10009',
                                    'Desc' => 'shipping_method error'
                                );
                     }
                     return $result;
                }


                public static function get_order_smseid($order_id)
                {
                    return get_post_meta( $order_id, 'Smilepay_ezcatup_SMSEID', true );
                }
                public static function set_order_smseid($order_id,$smseid)
                {
                    return update_post_meta( $order_id, 'Smilepay_ezcatup_SMSEID', $smseid );
                }

                public static function get_order_tracknum($order_id)
                {
                    return get_post_meta( $order_id, 'Smilepay_ezcatup_TRACKNUM', true );
                }
                public static function set_order_tracknum($order_id,$smseid)
                {
                    return update_post_meta( $order_id, 'Smilepay_ezcatup_TRACKNUM', $smseid );
                }

			}
		}


        if(is_admin())
        {
            global $pagenow;
            add_action('admin_notices', 'smilepay_ezcatup_shipping_error_show' );
            add_action('admin_menu','addAdminMenuSmilepayezcatup',50);
           
           


            if($pagenow=='post.php' && $_GET['Page']=='smilepay_ezcatup_shipping_gen')
            {
                add_action('init', 'smilepay_ezcatup_shipping_gen');
            }
        }

	}

    
	add_action( 'woocommerce_shipping_init', 'smilepayezcatup_shipping_method_init' );

    function addAdminMenuSmilepayezcatup()
    {
         add_submenu_page('','smilepay_ezcatup_api', '速買配黑貓Api', 'administrator', 'smilepay_ezcatup_api','WC_Smilepayezcatup_Shipping_Method::api_process');
    }

	function add_smilepayezcatup_shipping_method( $methods ) {

        //for woocommerce 2.6 modify
		$methods['smilepay_ezcat_shippingunpay'] = 'WC_Smilepayezcatup_Shipping_Method';
		return $methods;
	}

    
    function smilepay_ezcatup_shipping_error_show()
    {
        $error="";
        if(isset($_COOKIE['smilepay_ezcatup_admin_shipping_error']))
        {
             $error= $_COOKIE['smilepay_ezcatup_admin_shipping_error'];
             unset($_COOKIE['smilepay_ezcatup_admin_shipping_error']);
             setcookie('smilepay_ezcatup_admin_shipping_error', '', time() - 30 ); 
        }
      
            
        if($error)
        {
            echo "<div class='notice notice-error'><p>$error</p></div>";
        }

    }

    function smilepay_ezcatup_menu_init_data()
    {     
        if(is_admin())
        {
            add_action( 'add_meta_boxes', 'smilepayezcatup_order_add_meta_boxes' , 35 );            
        }      
    }

    function smilepayezcatup_order_add_meta_boxes() {
	    $screen    = get_current_screen();
	    $screen_id = $screen ? $screen->id : '';

        
        if($screen->post_type == 'shop_order' && class_exists('WC_Smilepayezcatup_Shipping_Method'))
        {
            if(isset($_REQUEST['post']))
                $order_id = $_REQUEST['post'];
            else
                $order_id = 0;

            if( empty($order_id))
                return;
            $order = new WC_Order($order_id);
           
            $shipping_method = @array_shift($order->get_shipping_methods());
            $shipping_method_id = $shipping_method['method_id'];
           
            if(WC_Smilepayezcatup_Shipping_Method::isSupport_Service( $shipping_method_id) )
                add_meta_box( 'smilepayezcatup-order-data', "速買配黑貓宅配", 'smilepayezcatup_order_output', 'shop_order', 'normal', 'low' );
        }
            
    }

    function smilepayezcatup_order_output()
    {
        if(isset($_REQUEST['post']))
            $order_id = $_REQUEST['post'];
        else
            $order_id = 0;

        if( !$order_id)
           return;

        $smilepayezcatup_plug = WC_Smilepayezcatup_Shipping_Method::getOrderSmilepayezcatupPlugin($order_id);
       
        if( $smilepayezcatup_plug != null)
            include_once('tpl/admin/html_smilepayezcatup_order_meta_box_admin.php');
        else
            echo '未知錯誤';
    }


    add_action('add_meta_boxes','smilepay_ezcatup_menu_init_data', 10);
    
	add_filter( 'woocommerce_shipping_methods', 'add_smilepayezcatup_shipping_method' );
}


   