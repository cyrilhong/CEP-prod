<?php
/**
 * Plugin Name: SmilePay_sms
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay SMS
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */




if ( !defined( 'ABSPATH' ) ) {
    exit();
}


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
{
    add_action('plugins_loaded', 'Smilepay_sms_init', 0);
    add_filter( 'pre_update_option_smilepay_sms_settings','smilepay_sms_pre_update_option' );

    
    
    function Smilepay_sms_init()
    {
       smilepay_sms_init_setting();
     
       add_action( 'admin_menu', 'smilepay_sms_admin_menu', 15 );
      // add_action( 'admin_init', 'smilepay_sms_init_setting', 15 );
       add_action( 'woocommerce_order_status_processing', 'smilepay_sms_processing_callback', 10 ); 
       add_action( 'woocommerce_order_status_completed', 'smilepay_sms_completed_callback', 10 );
       add_action( 'woocommerce_order_status_on-hold', 'smilepay_sms_on_hold_callback',10);
       add_action( 'woocommerce_order_status_failed', 'smilepay_sms_failed_callback',10);
    }
    function smilepay_sms_init_setting()
    {
        global $smilepay_sms_config;
        $smilepay_sms_config = get_option('smilepay_sms_settings');
  
    }

    function smilepay_sms_admin_menu()
    {
     
        add_submenu_page('woocommerce','Smilepaysms', u2bsms('速買配SMS'), 'administrator', 'smilepaysms','smilepay_sms_admin_menu_tpl');

        register_setting( 'smilepaysms-settings-main-group', 'smilepay_sms_settings' );
	   
      
    }
    function smilepay_sms_admin_menu_tpl()
    {
        include_once('tpl/smilepaysms_option_tpl.php');
    }


     function smilepay_sms_pre_update_option( $option)
     {
         global $smilepay_sms_config;
         
         if(strpos($option['dc2a'],'*%smilepaypass%*')!== false )
         {
             $option['dc2a']= $smilepay_sms_config['dc2a'];
         }
         return  $option;
     }







    function smilepay_sms_convert_message($msg,$order_id)
    {
        $order = new WC_Order($order_id);
        if(!empty($order))
        {
            $payment_method=wc_get_payment_gateway_by_order($order);
            $payment_method_name = $payment_method->title;
            if(empty($payment_method_name))
                 $payment_method_name = $payment_method->method_title;

            $shipping_methods=array_shift($order->get_shipping_methods());
            $shipping_method_name = $shipping_methods['name'];
         
            $tmp_msg = str_replace('%oid%',$order_id,$msg);
            $tmp_msg = str_replace('%customer%',$order->get_billing_last_name() . $order->get_billing_first_name(),$tmp_msg);
            $tmp_msg =  str_replace('%paym%', $payment_method_name,$tmp_msg);
            $tmp_msg =  str_replace('%shipm%', $shipping_method_name,$tmp_msg);
            
            return $tmp_msg;
        }
        else
            return $msg;
    }
    function smilepay_sms_processing_callback($order_id)
    {
        global $smilepay_sms_config;
        if(!isset($smilepay_sms_config['order_processing_check']) || $smilepay_sms_config['order_processing_check']!='yes' )
            return;
        else
        {
            $order = new WC_Order($order_id);
            $shipping_methods=array_shift($order->get_shipping_methods());
            $shipping_methods_id = $shipping_methods['method_id'];
            $payment_method=wc_get_payment_gateway_by_order($order);
            $payment_method_id= $payment_method->id;
            
            if(!in_array($shipping_methods_id,$smilepay_sms_config['order_processing_check_filter']) && !in_array($payment_method_id,$smilepay_sms_config['order_processing_check_filter']))
                return;
           
            $msg=smilepay_sms_convert_message($smilepay_sms_config['order_processing_message'],$order_id);
            $result = smilepay_sms_send_message($order_id,$msg);

            if(isset($result['error']))
                $order->add_order_note(u2bsms($result['error']),"0");
        }
    }
    
    function smilepay_sms_completed_callback($order_id)
    {
        global $smilepay_sms_config;
        if(!isset($smilepay_sms_config['order_completed_check']) || $smilepay_sms_config['order_completed_check']!='yes' )
            return;
        else
        {
            $order = new WC_Order($order_id);
            $shipping_methods=array_shift($order->get_shipping_methods());
            $shipping_methods_id = $shipping_methods['method_id'];
            $payment_method=wc_get_payment_gateway_by_order($order);
            $payment_method_id= $payment_method->id;
            
            if(!in_array($shipping_methods_id,$smilepay_sms_config['order_completed_check_filter']) && !in_array($payment_method_id,$smilepay_sms_config['order_completed_check_filter']))
                return;

            $msg=smilepay_sms_convert_message($smilepay_sms_config['order_completed_message'],$order_id);
            $result = smilepay_sms_send_message($order_id,$msg);
            if(isset($result['error']))
               $order->add_order_note(u2bsms($result['error']),"0");
        }
    }
    function smilepay_sms_on_hold_callback($order_id)
    {
        global $smilepay_sms_config;
        if(!isset($smilepay_sms_config['order_on_hold_check']) || $smilepay_sms_config['order_on_hold_check']!='yes' )
            return;
        else
        {
            $order = new WC_Order($order_id);
            $shipping_methods=array_shift($order->get_shipping_methods());
            $shipping_methods_id = $shipping_methods['method_id'];
            $payment_method=wc_get_payment_gateway_by_order($order);
            $payment_method_id= $payment_method->id;
            
            if(!in_array($shipping_methods_id,$smilepay_sms_config['order_on_hold_check_filter']) && !in_array($payment_method_id,$smilepay_sms_config['order_on_hold_check_filter']))
                return;

            $msg=smilepay_sms_convert_message($smilepay_sms_config['order_on_hold_message'],$order_id);
            $result = smilepay_sms_send_message($order_id,$msg);
            if(isset($result['error']))
               $order->add_order_note(u2bsms($result['error']),"0");
        }
    }

    function smilepay_sms_failed_callback($order_id)
    {
        global $smilepay_sms_config;
        if(!isset($smilepay_sms_config['order_failed_check']) || $smilepay_sms_config['order_failed_check']!='yes' )
            return;
        else
        {
            $order = new WC_Order($order_id);
            $shipping_methods=array_shift($order->get_shipping_methods());
            $shipping_methods_id = $shipping_methods['method_id'];
            $payment_method=wc_get_payment_gateway_by_order($order);
            $payment_method_id= $payment_method->id;
            
            if(!in_array($shipping_methods_id,$smilepay_sms_config['order_failed_check_filter']) && !in_array($payment_method_id,$smilepay_sms_config['order_failed_check_filter']))
                return;

            $msg=smilepay_sms_convert_message($smilepay_sms_config['order_failed_message'],$order_id);
            $result = smilepay_sms_send_message($order_id,$msg);
            if(isset($result['error']))
               $order->add_order_note(u2bsms($result['error']),"0");
        }
    }

    function smilepay_sms_send_message($order_id,$msg)
    {
        global $smilepay_sms_config;
        $prfix = u2bsms('速買配SMS：');
        $order = new WC_Order($order_id);

        $SMS_Server = "http://smsmo.smse.com.tw/standard/sms_url_pay.asp"; 
        $sb = $msg;
        $dcvc = $smilepay_sms_config['dcvc'];
        $rvg2c = $smilepay_sms_config['rvg2c'];
        $vw2a = $smilepay_sms_config['vw2a'];
        $yhy = $smilepay_sms_config['yhy'];
        $dc2a = $smilepay_sms_config['dc2a'];

        $name =$order->get_billing_last_name() . $order->get_billing_first_name();
        $movetel = $order->get_billing_phone();
        $od_sob ="";
        if(!$dcvc)
        {
            $result=array
                    (
                        'status'=> '-1111',
                        'error'=> $prfix.u2bsms('商家代號(Dcvc)錯誤'),
                    );
           return $result;
        }   
        if(!$rvg2c)
        {
            $result=array
                    (
                        'status'=> '-1112',
                        'error'=>$prfix.u2bsms('參數碼(Rvg2c)錯誤')
                    );
           return $result;
        }

        if(!$vw2a)
        {
            $result=array
                    (
                        'status'=> '-1113',
                        'error'=>$prfix.u2bsms('企業系統代碼(Vw2a)錯誤')
                    );
           return $result;
        }

        if(!$yhy)
        {
           $result=array
                    (
                        'status'=>'-1114',
                        'error'=>$prfix.u2bsms('簡訊帳號(Yhy)錯誤')
                    );
           return $result;
        }

        if(!$dc2a)
        {
           $result=array
                    (
                        'status'=> '-1115',
                        'error'=>$prfix.u2bsms('簡訊代號(Dc2a)錯誤')
                    );
           return $result;
        }

        if(!$movetel)
        {
           $result=array
                    (
                        'status'=> '-1116',
                        'error'=>$prfix.u2bsms('電話錯誤')
                    );
           return $result;
        }

        if(!$sb)
        {
 
            $result=array
                    (
                        'status'=> '-1117',
                        'error'=>$prfix.u2bsms('簡訊無內容')
                    );
           return $result;
        }

        $post_data = array(
                        'Dcvc' => $dcvc,
		                'Rvg2c' => $rvg2c,
		                'Vw2a' => $vw2a,
		                'Yhy' => $yhy,
		                'Dc2a' => $dc2a,
		                'movetel' => $movetel,
		                'encoding' => 1,
                     //   'bf' => '2017/10/3 20:00:00'
		            );

 

        if(isset($od_sob) && $od_sob)
        {
            $post_data['od_sob'] =  $od_sob;
        }

        if(isset($sb) && $sb)
        {
            $post_data['sb'] = $sb;
        }
       

        if(isset($name) &&$name)
	        $post_data['name'] = $name;
        if(isset($mo_http) && $mo_http)
	        $post_data['mo_http'] = $mo_http;

    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$SMS_Server."?".http_build_query( $post_data));
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $sms_result=curl_exec($ch); 
        curl_close($ch);

        if($sms_result=="OK_1") //成功
	    {
	         $result=array
                    (
                        'status'=>'1',
                    );
             return $result;
	    }
        else
        {
            $result=array
                    (
                        'status'=> '-1',
                        'error'=>$prfix.$sms_result
                    );
             return $result;
        }



    }

    function u2bsms($text)//畫面輸出
    {	return iconv("big5","UTF-8",$text);}
    function b2usms($text)//寫入資料庫
    {	return iconv("UTF-8","big5",$text);}
}





?>
