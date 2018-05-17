<?php
/**
 * Plugin Name: SmilePay_7-11 ibon 
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay7-11 ibon 
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */
if(!defined("SMILEPAY_RESPONSE_DECLARE"))
{
    include "smilepay_respond.php";
}
add_action('plugins_loaded', 'SmilePayibon_gateway_init', 0);
function SmilePayibon_gateway_init() {
   if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    class WC_SmilePayibon extends WC_Payment_Gateway {

        public $title;
        public $description;
        public $dcvc;
        public $Rvg2c;
        public $Order_OKmain;
        public $Verify_key;

        public function __construct() {
            $this->id = 'smilepayibon';
            $this->icon = apply_filters('woocommerce_SmilePayibon_icon', plugins_url('log/ibon_logo.jpg', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('SmilePayibon', 'woocommerce');
            // Load the form fields.
            $this->init_form_fields();
            // Load the settings.
            $this->init_settings();
			$this->Rvg2c =  $this->settings['Rvg2c'];
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
            // Actions
            //add_action('init', array(&$this, 'check_SmilePayibon_response'));
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action('woocommerce_thankyou_'.$this->id, array($this, 'thankyou_page')); 
        }
        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        public function init_form_fields() {  //��x�]�m���
		//	$urll=get_option('siteurl')."/?respond";
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __(u2bi("�ҥ�/����"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2bi("SmilePay 7-11 ibon"), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2bi('���D'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bi('�U�Ȧb���b�ɩ���ܪ��I�ڤ覡���D'), 'woocommerce'),
                    'default' => __(u2bi('SmilePay�t�R�t 7-11 ibon ú�O'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2bi('�I�ڤ覡����'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bi('�U�Ȧb��ܥI�ڤ覡�ɩ���ܪ����Ф�r'), 'woocommerce'),
                    'default' => __(u2bi("SmilePay�t�R�t 7-11 ibon ú�O"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2bi('�Ӯa�N��'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bi('�ж�J�zSmilePay�ө��N��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2bi('�Ӯa�ѼƽX'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bi('�ж�J�zSmilePay�Ӯa�ѼƽX'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2bi('�Ӯa�ˬd�X'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bi('�ж�J�zSmilePay�Ӯa�ˬd�X�A�ˬd�X��Ӯa��x�u�I������API�v�������A�нƻs�öK�J�W�����'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Mid_smilepay' => array(
                    'title' => __(u2bi('�Ӯa���ҰѼ�'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bi('�ж�J�zSmilePa���ҽX�A���ҽX��Ӯa��x�u�򥻸�ƺ޲z�v�������A�нƻs�öK�J�W�����A�p�������ҽЫO�d�ť�'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
                'Deadline_date' => array(
                    'title' => __(u2bi('ú�ںI�����'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bi('ú�ںI������A�w�]�Ȭ�7��A�ж�J1~6�A���o�j��7��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Order_OKmain' => array(
                    'title' => __(u2bi('�U�榨�\��ܰT��'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bi('�U�榨�\��ܰT��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
			/*	'reurl' => array(
                    'title' => __(u2bi('��������^�e��m'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bi('��������^�e��m�A�бN�U�C��r�ƻs��W��ؤ�(�p�����^�e�Яd�ť�)</br><font color=red ue size=+1>'.$urll.'</font>', 'woocommerce')),
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
       public function get_SmilePayibon_args($order) {
            global $woocommerce;
            $paymethod = 'ibon';
            $order_id = $order->id;
			$post_status = $order->post_status;
			
            $SmilePayibon_args = array(
                "dcvc" => $this->dcvc,
                'Rvg2c' => $this->Rvg2c,
                "payment_type" => $paymethod,
                "od_sob" => $order_id,
				"post_status" => $post_status,
                'reurl' => $this->reurl,
                'Verify_key' => $this->Verify_key,
                'Mid_smilepay' => $this->Mid_smilepay,
				'Deadline_date' => $this->Deadline_date,
				'Order_OKmain' => $this->Order_OKmain,
				"amt" => round($order->get_total()),
            );
            $SmilePayibon_args = apply_filters('woocommerce_SmilePayibon_args', $SmilePayibon_args);
            return $SmilePayibon_args;
        }
		public function thankyou_page($order_id) {  //�����^�ǰѼ�����  �P  7-11 Ibon����
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
			// $order = &new WC_Order($order_id);
			$order = new WC_Order($order_id);

		
            
			$SmilePayibon_args = $this->get_SmilePayibon_args($order);
			$order_status=$SmilePayibon_args['post_status'];
		if($order_status=='wc-pending')
		{
	         //--���ystart
			 $shipping_method= $order->get_shipping_method();
			 $getc2cu="N";
			 if($_REQUEST['storeid']=='')
			 {
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
                //�ˬd���y�O�_�ϥί¨��f
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
			  //--���yend
			if(is_numeric($SmilePayibon_args['Deadline_date'])&&$SmilePayibon_args['Deadline_date']<6)
			{$Deadline_date_value=$SmilePayibon_args['Deadline_date'];}
			else
			{$Deadline_date_value=6;}
			$Deadline_date = date("Y/m/d",mktime(0,0,0,date("m"),date("d")+$Deadline_date_value,date("Y")));			
          	//����
            $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();
		  	$smilepay_gateway = 'https://ssl.smse.com.tw/api/sppayment.asp';
            //�ھڷ�U�����վ�reurl�Ohttp��https
            if(is_ssl())
                $SmilePayibon_args['reurl']=str_replace('http://','https://',$SmilePayibon_args['reurl']);
            else
                $SmilePayibon_args['reurl']=str_replace('https://','http://',$SmilePayibon_args['reurl']);

			if($SmilePayibon_args['reurl']!=''){$Vk=$SmilePayibon_args['reurl']."=".$order->get_order_key();}else{$Vk='';}


            //<SmilePay>item add start
            /*$products="";
            foreach ($order->get_items() as $key => $lineItem) {
                $products .= $lineItem['name'] ."*" .$lineItem['quantity'] .",";
            
            }
            $products=trim($products,",");*/
            //<SmilePay>item add end

			$post_str = 'Dcvc=' . $SmilePayibon_args['dcvc'] .
					'&Rvg2c=' . $SmilePayibon_args['Rvg2c'] .
					'&Pur_name=' .$pur_name.
					'&Tel_number=' .$order->get_billing_phone().
                    '&Mobile_number=' .$order->get_billing_phone().
					'&Address=' .$order->get_billing_address_1().
					'&Email=' .$order->get_billing_email().
					'&Data_id=' . $SmilePayibon_args['od_sob'].
					'&od_sob=' . $SmilePayibon_args['od_sob'].
					'&Amount=' . $SmilePayibon_args['amt'].
					'&Deadline_date=' . $Deadline_date.
					'&Roturl_status=' . 'woook'.
					'&Verify_key=' . $SmilePayibon_args['Verify_key'].
                    '&Remark=woocommerce';
                     //<SmilePay>item add start
					//'&Remark='. $products;
                     //<SmilePay>item add end
			$post_strall=$post_str.'&Pay_zg=4'.'&Roturl=' . $Vk;
			
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
			
			//�g�J�Ƶ���
			$xml = simplexml_load_string($string);
			$Status = $xml->Status;
			if(strpos($string,"<SmilePay>")&&$Status=="1")
			{
               

				//�g�J�q��ú�O���-���O��
                $title = b2ui($this->title);
				$ordertext="ú�O�覡�G<font color=red>$title</font><br>ú�O�N�X�G<font color=red>".$xml->IbonNo."</font><br>ú�O�����G<font color=red>".$xml->PayEndDate."</font></br>�p�W�Lú�O�����Ф�ú�O</br>";
				$order->add_order_note(u2bi($ordertext),"1");
				$ordertext="�ФŧR������T�A�t�R�tSmilePay�l�ܽX�G".$xml->SmilePayNO;
				$order->add_order_note(u2bi($ordertext),"0");
				//�ק�q�檬�A	
				$order->update_status( 'on-hold' );	
				//������ܤ��e
				echo u2bi("<font color='red' size='3'>�`�N�G�q��w���ߡA�|���I�ڡA�Ш�ú�ڸ�ƺɳt�I��</font>");
				$showtext= "<br><br><h1>ú�O��T</h1>ú�O�覡�G<font color='red' size='3'>$title</font></td></tr>";
				$showtext=$showtext."<br>ú�O�N�X�G<font color='red' size='3'>".$xml->IbonNo."</font>";
				$showtext=$showtext."<br>ú�O�����G<font color='red' size='3'>".$xml->PayEndDate."</font>";
				echo u2bi($showtext);
				echo u2bi("<br><font color='red' size='3'>�p�ѰO<u>ú�O��T</u>�ЦۤW����C<u>�b���]�w->�q��->�˵�</u></font>");
				echo "<br><br>".$SmilePayibon_args['Order_OKmain'];
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
					    $order->add_order_note(u2bi($scvurls),"0");
					    $scvurls_1="7-11���f�����G<font color=red>".b2ui($b2c_storename.$b2c_storeid)."</font><br>";
                        $scvurls_1 .= "�����a�}�G<font color=red>".b2ui($b2c_storeaddress)."</font><br>";
                        $scvurls_1 .= "���f�H�m�W�G<font color=red>".b2ui($pur_name)."</font><br>";
                        $scvurls_1 .= "���f�H�q�ܡG<font color=red>".b2ui($order->get_billing_phone())."</font><br>";
                       
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
					    $scvurls=repeatc2c($c2c_store,$post_str,$SmilePayibon_args['dcvc'],$SmilePayibon_args['Verify_key']);
					    $order->add_order_note(u2bi($scvurls),"0");
					    $scvurls_1="7-11���f�����G<font color=red>".b2ui($c2c_storename.$c2c_storeid)."</font><br>";
                        $scvurls_1 .= "�����a�}�G<font color=red>".b2ui($c2c_storeaddress)."</font><br>";
                        $scvurls_1 .= "���f�H�m�W�G<font color=red>".b2ui($pur_name)."</font><br>";
                        $scvurls_1 .= "���f�H�q�ܡG<font color=red>".b2ui($order->get_billing_phone())."</font><br>";
                       
                    }
					$order->add_order_note(u2bi($scvurls_1),"1");
                    echo u2bi('<h1>���f��T</h1>' . $scvurls_1);
				}
				//--���yend
			}
			else
			{
				if($Status!="")
				{	$ordererror="��������</br>���~�N�X�G".$xml->Status."</br>���e��]�G".$xml->Desc."</br>�O���ɶ��G".date("Y-m-d H:i:s",mktime(date(H)+8));	}
				else
				{	$ordererror="��������</br>���e��]�G�s�u���`</br>�O���ɶ��G".date("Y-m-d H:i:s",mktime(date(H)+8));	}
				$order->add_order_note(u2bi($ordererror),"1");
				$order->update_status( 'cancelled' );		
                echo u2bi($ordererror);			
			}
		}
		else
		{
			echo u2bi("<font color='red' size='3'>�p�ѰO<u>ú�O��T</u>�ЦۤW����C<u>�b���]�w->�q��->�˵�</u></font>");
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
		//�K�[�P�_�O�_�i��
			global $woocommerce;
			$order = new WC_Order( $order_id );		
			if(is_null($this->dcvc) || empty($this->dcvc) ||
               is_null($this->Verify_key) || empty($this->Verify_key) ||
               is_null($this->Rvg2c) || empty($this->Rvg2c)
                )
            {
                wc_add_notice(u2bi("<br>�t�R�t�Ӯa�Ѽƿ��~�A�лP�Ӯa�pô<br>") , 'error' );
                return;
            }

			/*//--���ystart
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
            //if($shipping_method==u2bi("�t�R�t7-11�����¨��f"))
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
                    wc_add_notice( __(u2bi("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"), 'woothemes') . $error_message, 'error' );
                    return;					
					exit;
				}
			}
            if($smilepayc2cp_obj && $smilepayc2cp_obj->isSelectedSmilepayC2Cp($order_id))
			//if($shipping_method==u2bi("�t�R�t7-11�������f�I��"))
			{
                wc_add_notice( __(u2bi("<br><font color='red'>�t�e�覡��ܿ��~�A���i��ܦ��t�e�覡</font><br>"), 'woothemes') . $error_message, 'error' );
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
                    wc_add_notice( __(u2bi("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}


			}
            if($smilepayb2cp_obj && $smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))		
			{
                wc_add_notice( __(u2bi("<br><font color='red'>�t�e�覡��ܿ��~�A���i��ܦ��t�e�覡</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
                exit;
			}
            //<b2c> end
			//--���yend
			*/
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
    function add_SmilePayibon_gateway($methods) {
        $methods[] = 'WC_SmilePayibon';
        return $methods;
    }
	

    add_filter('woocommerce_payment_gateways', 'add_SmilePayibon_gateway');
}
function u2bi($text)//�e����X
{	return iconv("big5","UTF-8",$text);}
function b2ui($text)//�g�J��Ʈw
{	return iconv("UTF-8","big5",$text);}
?>