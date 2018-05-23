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

    //�t�e�覡�L�o�I�ڤ覡
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
    //�t�e�覡�L�o�I�ڤ覡 �����b
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
                    $this->method_description    = u2bbp('7-11�W�Ө��f�I��');

                    //for woocommerce 2.6 modify
					//$this->enabled            = "yes";
                    $this->supports              = array(
			                                        'shipping-zones',
			                                        'instance-settings',
			                                        'instance-settings-modal',
		                                            );

                    if(isset($smilepayb2cdata->title) && !empty($smilepayb2cdata->title))
					    $this->title              = $smilepayb2cdata->title;//u2bbp("�t�R�t7-11�������f�I��"); 
                    else
                        $this->title              = u2bbp("SmilePay 7-11�W�Ө��f�I�� ú�O"); 
					$this->init();
            
				}

				public function init_form_fields() {  //��x�]�m���

					//for woocommerce 2.6 modify
					//$this->form_fields = array(
                    $this->instance_form_fields = array(
					/*	'enabled' => array(
							'title' => __(u2bbp("�ҥ�/����"), 'woocommerce'),
							'type' => 'checkbox',
							'default' => 'yes',
                   			'label' =>  $this->title,
						),*/
						'fee' => array(
							'title'       => __(u2bbp('�O��'), 'woocommerce' ),
							'type'        => 'price',
							'description' => __( '', 'woocommerce' ),
							'default'     => '',
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
						'fee_top' => array(
							'title'       => __(u2bbp('�K�B�O���e'), 'woocommerce' ),
							'type'        => 'price',
							'description' => __(u2bbp("��F�즹���B�ɡA�K�B�O�A���ϥνЫO���ť�"), 'woocommerce'),
							'desc_tip'    => true,
							'placeholder' => wc_format_localized_price( 0 )
						),
                        'min_total' => array(
							'title'       => u2bbp('�̧C���O���B'),
							'type'        => 'price',
							'description' => u2bbp('��q����B�F�즹�]�w���B�A�~�i�H�ϥΦ��t�e�覡�A���t�B�O'),
							'default'     => '',
						),			
                        'payment_filter_enabled' => array(
							'title' =>  u2bbp("SmilePay �t�e�覡�f�t�I�ڤ覡�W�h"),
                            'description' => __(u2bbp("�}�Ҧ��\���A�b���b�ɡA��t�e�覡�O���f�I�ڡA�I�ڤ覡�u�����f�I�ڡA�Y�t�e�覡���O���f�I�ڡA�h�I�ڤ覡���|�X�{���f�I��"), 'woocommerce'),
							'type' => 'checkbox',
							'default' => 'yes',
                   			'label' => u2bbp("�ҥ�"),
						),			
						'hiddtext' => array(
							'title' => __(u2bbp('�W�ӯ¨��f�`�N�ƶ�'), 'woocommerce'),
							'type' => 'hidden',
							'description' => __(u2bbp("�ϥζW�ӯ¨��f�\��A�ݪ`�N�H�U�ƶ��G
														<br>��<font color='red'>���ҲնȤ䴩�iSmilePay �W�Ө��f�I�ڡj���y�ҲաA�нT�{�ҥ�</font>							
														<br>1.�Х���SmilePay�Ӯa��x�}�Ҩ��f�\��*<a target='_blank' href='http://www.smilepay.net/RVG.ASP'>�Ӯa��x</a>*
														<br>2.�b���T��<font color='red'>�p���q��</font>������<font color='red'>������X</font>�A�ӫ~������ɷ|�H²�T�q���C
														<br>3.���O�̦��߭q���A�i��SmilePay�Ӯa��x�����o<font color='red'>��f�K�N�X</font>�C
														<br>4.���O�̦��߭q���A�i�ܭq��Ƶ����I��<font color='red'>�}�ҥ�f�K�A�ȳ�</font>�C														
														<br>5.�бN<font color='red'>��f�K�N�X</font>��ʷs�W�ܰӫ~�Ƶ��A�H�Q���O�̬d�ߪ��y���A�C
														<br>6.��h�����аѾ\�A<font color='red'>SmilePay��������</font>�P<font color='red'>WooCommerce�Ҳջ������</font>�C
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
                           $errors->add( 'shipping',u2bbp("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"));
                   
				        }
                        if(strtolower($chosen_payment_method) != 'smilepayb2c' )
                        {
                           $errors->add( 'shipping',u2bbp("<br>�I�ڤ覡��ܿ��~�A���t�e�覡���i��ӥI�ڤ覡�A�Ч󴫨�@<br>"));
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
                // �O�_���B2CP�B�e
                // $order_id:�q��s��
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
function u2bbp($text)//�e����X
{	return iconv("big5","UTF-8",$text);}
function b2ubp($text)//�g�J��Ʈw
{	return iconv("UTF-8","big5",$text);}
