<?php
/**
 * Plugin Name: SmilePay_ATM 
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay ATM 
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */

if(!defined("SMILEPAY_RESPONSE_DECLARE"))
{
    include "smilepay_respond.php";
}

add_action('plugins_loaded', 'SmilePayatm_gateway_init', 0);
function SmilePayatm_gateway_init() {


   if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    class WC_SmilePayatm extends WC_Payment_Gateway {
        public $title;
        public $description;
        public $dcvc;
        public $Rvg2c;
        public $Deadline_date;
        public $Order_OKmain;
        public $reurl;
        public $Verify_key;
        public $Mid_smilepay;

        public function __construct() {
            $this->id = 'smilepayatm';
            $this->icon = apply_filters('woocommerce_SmilePayatm_icon', plugins_url('log/atm.jpg', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('smilepayatm', 'woocommerce');
            // Load the form fields.
            $this->init_form_fields();
            // Load the settings.
            $this->init_settings();

			
            // Define user set variables
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->dcvc = $this->settings['dcvc'];
			$this->Deadline_date = $this->settings['Deadline_date'];
			$this->Order_OKmain = $this->settings['Order_OKmain'];
			//$this->reurl = $this->settings['reurl'];
            $this->reurl =get_option('siteurl')."/?smilepay_respond";
			$this->Verify_key = $this->settings['Verify_key'];
			$this->Mid_smilepay = $this->settings['Mid_smilepay'];
            $this->Rvg2c =  $this->settings['Rvg2c'];
            // Actions
            //add_action('init', array(&$this, 'check_SmilePayatm_response'));
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_'.$this->id, array($this, 'thankyou_page')); 

            //�ۮewoocommerce 2.6
           // add_action( 'woocommerce_settings_checkout'  , array( $this, 'admin_output_modify' ) );
        }
        //�ۮewoocommerce 2.6 �ץ��L�k�i�J�]�w
       /* public function admin_output_modify()
        {
            global $current_section;
            if( $current_section &&  $current_section == 'smilepayatm')
                $current_section=$this->id;
            
        }*/
        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        public function init_form_fields() {  //��x�]�m���
			//$urll=get_option('siteurl')."/?respond";
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __(u2ba("�ҥ�/����"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2ba('SmilePay �����b��/ATM'), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2ba('���D'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2ba('�U�Ȧb���b�ɩ���ܪ��I�ڤ覡���D'), 'woocommerce'),
                    'default' => __(u2ba('SmilePay �����b��/ATM ú�O'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2ba('�I�ڤ覡����'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2ba('�U�Ȧb��ܥI�ڤ覡�ɩ���ܪ����Ф�r'), 'woocommerce'),
                    'default' => __(u2ba("SmilePay �����b��/ATM ú�O"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2ba('�Ӯa�N��'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2ba('�ж�J�zSmilePay�ө��N��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2ba('�Ӯa�ѼƽX'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2ba('�ж�J�zSmilePay�Ӯa�ѼƽX'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2ba('�Ӯa�ˬd�X'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2ba('�ж�J�zSmilePay�Ӯa�ˬd�X�A�ˬd�X��Ӯa��x�u�I������API�v�������A�нƻs�öK�J�W�����'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Mid_smilepay' => array(
                    'title' => __(u2ba('�Ӯa���ҰѼ�'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2ba('�ж�J�zSmilePa���ҽX�A���ҽX��Ӯa��x�u�򥻸�ƺ޲z�v�������A�нƻs�öK�J�W�����A�p�������ҽЫO�d�ť�'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
				'Deadline_date' => array(
                    'title' => __(u2ba('ú�ںI�����'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2ba('ú�ںI������A�w�]�ȵL�����A�ж�J1~720�A���o�j��720��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
				'Order_OKmain' => array(
                    'title' => __(u2ba('�U�榨�\��ܰT��'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2ba('�U�榨�\��ܰT��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
				/*'reurl' => array(
                    'title' => __(u2ba('��������^�e��m'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2ba('��������^�e��m�A�бN�U�C��r�ƻs��W��ؤ�(�p�����^�e�Яd�ť�)</br><font color=red ue size=+1>'.$urll.'</font>', 'woocommerce')),
                    'default' => __( $urll, 'woocommerce'),
                
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
       public function get_SmilePayatm_args($order) {
            global $woocommerce;

            $paymethod = 'atm';
            $order_id = $order->id;
			//*
			$post_status = $order->post_status;
            
			$SmilePayatm_args = array(
                "dcvc" => $this->dcvc,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
				"post_status" => $post_status,
                'reurl' => $this->reurl,
                'Rvg2c' => $this->Rvg2c,
                'Verify_key' => $this->Verify_key,
                'Mid_smilepay' => $this->Mid_smilepay,
				'Deadline_date' => $this->Deadline_date,
				'Order_OKmain' => $this->Order_OKmain,
				"amt" => round($order->get_total()),
            );
            $SmilePayatm_args = apply_filters('woocommerce_SmilePayatm_args', $SmilePayatm_args);
            return $SmilePayatm_args;
        }
		
       public function thankyou_page($order_id) {  //�����^�ǰѼ�����  �P  atm����
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
			$order = new WC_Order($order_id);
			
			
			
			$SmilePayatm_args = $this->get_SmilePayatm_args($order);
			
			//�P�_�O�_����	
			$thepostid = $SmilePayatm_args['od_sob'];
			
			$order_status=$SmilePayatm_args['post_status'];

		if($order_status=='wc-pending')
		{
            //--���ystart
			$shipping_method= $order->get_shipping_method();
           
			$getc2cu="N";
			if($_REQUEST['storeid']=='')
			{
				//if($shipping_method==u2ba("�t�R�t7-11�����¨��f"))
                if(file_exists("smilepayc2cup.php"))
                    include_once("smilepayc2cup.php");
                if(class_exists('WC_Smilepayc2cu_Shipping_Method'))
                    $smilepay_c2cupobj = new WC_Smilepayc2cu_Shipping_Method();
                else
                    $smilepay_c2cupobj = null;
                //<b2c> start
                if(file_exists("smilepayb2cup.php"))
                    include_once("smilepayb2cup.php");
                if(class_exists('WC_Smilepayb2cu_Shipping_Method'))
                    $smilepay_b2cupobj = new WC_Smilepayb2cu_Shipping_Method();
                else
                    $smilepay_b2cupobj = null;
                //<b2c> end


                //�ˬd���y�O�_�ϥί¨��f
				if(  $smilepay_c2cupobj && $smilepay_c2cupobj->isSelectedSmilepayC2Cup($order_id))
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
                            $mappurl="https://emap.presco.com.tw/c2cemapm-u.ashx?eshopid=870&servicetype=1&url=".urlencode($URL);
                          
                        }
                        
                    //}
                   
                    if(!isset($mappurl))//Mobile map end
					    $mappurl="https://emap.presco.com.tw/c2cemap.ashx?eshopid=870&servicetype=1&tempvar=b2c&url=".urlencode($URL);
                   
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
            //Mobile map start
            //if(isset( WC()->session->smilepay_711_c2c_shippingunpay_mobile_map))
              //  unset( WC()->session->smilepay_711_c2c_shippingunpay_mobile_map);
            //Mobile map end
			//--���yend

           

			if(is_numeric($SmilePayatm_args['Deadline_date'])&&(int)$SmilePayatm_args['Deadline_date']<720)
			{
				$Deadline_date_value=$SmilePayatm_args['Deadline_date'];
				$Deadline_date = date("Y/m/d",mktime(0,0,0,date("m"),date("d")+$Deadline_date_value,date("Y")));			
			}
			else
			{$Deadline_date_value="";}
			
            $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
            //�ھڷ�U�����վ�reurl�Ohttp��https
            if(is_ssl())
                $SmilePayatm_args['reurl']=str_replace('http://','https://',$SmilePayatm_args['reurl']);
            else
                $SmilePayatm_args['reurl']=str_replace('https://','http://',$SmilePayatm_args['reurl']);
          	//����
			if($SmilePayatm_args['reurl']!=''){$Vk=$SmilePayatm_args['reurl']."=".$order->get_order_key();}else{$Vk='';}
		  	$smilepay_gateway = 'https://ssl.smse.com.tw/api/sppayment.asp';
            //<SmilePay>item add start
            /*$products="";
            foreach ($order->get_items() as $key => $lineItem) {
                $products .= $lineItem['name'] ."*" .$lineItem['quantity'] .",";
            
            }
            $products=trim($products,",");*/
            //<SmilePay>item add end
			$post_str = 'Dcvc=' . $SmilePayatm_args['dcvc'] .
					'&Rvg2c=' . $SmilePayatm_args['Rvg2c'] .
					'&Pur_name=' .$pur_name.
					'&Tel_number=' .$order->get_billing_phone().
                    '&Mobile_number=' .$order->get_billing_phone().
					'&Address=' .$order->get_billing_address_1().
					'&Email=' .$order->get_billing_email().
					'&Data_id=' . $SmilePayatm_args['od_sob'].
					'&od_sob=' . $SmilePayatm_args['od_sob'].
					'&Amount=' . $SmilePayatm_args['amt'].
					'&Deadline_date=' . $Deadline_date.
					'&Roturl_status=' . 'woook'.
					'&Verify_key=' . $SmilePayatm_args['Verify_key'].
                    '&Remark=woocommerce';
					//<SmilePay>item add start
					//'&Remark='. $products;
                    //<SmilePay>item add end
			$post_strall=$post_str.'&Pay_zg=2'.'&Roturl=' . $Vk;
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
			
			/*
			update_status=>has_status
			
			
			*/
			//�g�J�Ƶ���
			$xml = simplexml_load_string($string);
			$Status = $xml->Status;
			if(strpos($string,"<SmilePay>")&&$Status=="1")
			{
                 $title = b2ua($this->title);
				//�g�J�q��ú�O���-���O��
				if($xml->PayEndDate==" 23:59:59"){$payenddata="�Lú�O����";} else{$payenddata=$xml->PayEndDate;}
				$ordertext="ú�O�覡�G<font color=red>$title</font><br>�Ȧ�N���G<font color=red>".$xml->AtmBankNo."</font><br>�����b���G<font color=red>".$xml->AtmNo."</font><br>ú�O���B�G<font color='red'>NT\$".$xml->Amount."</font><br>ú�O�����G<font color=red>".$payenddata."</font></br>�p�W�Lú�O�����Ф�ú�O</br>";
				$order->add_order_note(u2ba($ordertext),"1");
				$ordertext="�ФŧR������T�A�t�R�tSmilePay�l�ܽX�G".$xml->SmilePayNO;
				$order->add_order_note(u2ba($ordertext),"0");
				//�ק�q�檬�A	
				$order->update_status( 'on-hold' );	
				//������ܤ��e
               

				echo u2ba("<font color='red' size='3'>�`�N�G�q��w���ߡA�|���I�ڡA�Ш�ú�ڸ�ƺɳt�I��</font>");
				$showtext= "<br><br><h1>ú�O��T</h1>ú�O�覡�G<font color='red' size='3'>$title</font></td></tr>";
				$showtext=$showtext."<br>�Ȧ�N���G<font color='red' size='3'>".$xml->AtmBankNo."</font>";
				$showtext=$showtext."<br>�����b���G<font color='red' size='3'>".$xml->AtmNo."</font>";
                $showtext=$showtext."<br>ú�O���B�G<font color='red' size='3'>NT\$".$xml->Amount."</font>";
				$showtext=$showtext."<br>ú�O�����G<font color='red' size='3'>".$payenddata."</font>";
				echo u2ba($showtext);
				echo u2ba("<br><font color='red' size='3'>�p�ѰO<u>ú�O��T</u>�ЦۤW����C<u>�b���]�w->�q��->�˵�</u></font>");
				echo "<br><br>".$SmilePayatm_args['Order_OKmain'];
				//--���ystart
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
					    $order->add_order_note(u2ba($scvurls),"0");
					    $scvurls_1="7-11���f�����G<font color=red>".b2ua($b2c_storename.$b2c_storeid)."</font><br>";
                        $scvurls_1 .= "�����a�}�G<font color=red>".b2ua($b2c_storeaddress)."</font><br>";
                        $scvurls_1 .= "���f�H�m�W�G<font color=red>".b2ua($pur_name)."</font><br>";
                        $scvurls_1 .= "���f�H�q�ܡG<font color=red>".b2ua($order->get_billing_phone())."</font><br>";
                        
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
					    $scvurls=repeatc2c($c2c_store,$post_str,$SmilePayatm_args['dcvc'],$SmilePayatm_args['Verify_key']);
					    $order->add_order_note(u2ba($scvurls),"0");
					    $scvurls_1="7-11���f�����G<font color=red>".b2ua($c2c_storename.$c2c_storeid)."</font><br>";
                        $scvurls_1 .= "�����a�}�G<font color=red>".b2ua($c2c_storeaddress)."</font><br>";
                        $scvurls_1 .= "���f�H�m�W�G<font color=red>".b2ua($pur_name)."</font><br>";
                        $scvurls_1 .= "���f�H�q�ܡG<font color=red>".b2ua($order->get_billing_phone())."</font><br>";
                       
                    }
					

					$order->add_order_note(u2ba($scvurls_1),"1");
					echo u2ba('<h1>���f��T</h1>' . $scvurls_1);
				}
				//--���yend
				
			}
			else
			{
				if($Status!="")
				{	$ordererror="��������</br>���~�N�X�G".$xml->Status."</br>���e��]�G".$xml->Desc."</br>�O���ɶ��G".date("Y-m-d H:i:s",mktime(date(H)+8));	}
				else
				{	$ordererror="��������</br>���e��]�G�s�u���`</br>�O���ɶ��G".date("Y-m-d H:i:s",mktime(date(H)+8));	}
				$order->add_order_note(u2ba($ordererror),"1");
				$order->update_status( 'cancelled' );	
                echo u2ba($ordererror);				
			}
		}
		else
		{
			echo u2ba("<font color='red' size='3'>�p�ѰO<u>ú�O��T</u>�ЦۤW����C<u>�b���]�w->�q��->�˵�</u></font>");
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
                wc_add_notice(u2ba("<br>�t�R�t�Ӯa�Ѽƿ��~�A�лP�Ӯa�pô<br>") , 'error' );
                return;
            }

			//--���ystart
			/*$shipping_method= $order->get_shipping_method();

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
            

            if($smilepayc2cup_obj && $smilepayc2cup_obj->isSelectedSmilepayC2Cup($order_id))
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
                    wc_add_notice( __(u2ba("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}


			}
            if($smilepayc2cp_obj && $smilepayc2cp_obj->isSelectedSmilepayC2Cp($order_id))		
			{
                wc_add_notice( __(u2ba("<br><font color='red'>�t�e�覡��ܿ��~�A���i��ܦ��t�e�覡</font><br>"), 'woothemes') . $error_message, 'error' );
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
				if(substr($order->get_billing_phone(),0,2)!="09"||mb_strlen($order->get_billing_phone())!=10||mb_strlen( $username, "utf-8")>$limit_name_number){
                    wc_add_notice( __(u2ba("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}


			}
            if($smilepayb2cp_obj && $smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))		
			{
                wc_add_notice( __(u2ba("<br><font color='red'>�t�e�覡��ܿ��~�A���i��ܦ��t�e�覡</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
                exit;
			}
            //<b2c> end
			//--���yend*/
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
    function add_SmilePayatm_gateway($methods) {
        $methods[] = 'WC_SmilePayatm';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_SmilePayatm_gateway');
}
function u2ba($text)//�e����X
{	return iconv("big5","UTF-8",$text);}
function b2ua($text)//�g�J��Ʈw
{	return iconv("UTF-8","big5",$text);}
?>