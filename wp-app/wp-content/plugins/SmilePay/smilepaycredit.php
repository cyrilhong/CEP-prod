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
        public function init_form_fields() {  //��x�]�m���
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __(u2bcr("�ҥ�/����"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2bcr(' SmilePay �u�W��d'), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2bcr('���D'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('�U�Ȧb���b�ɩ���ܪ��I�ڤ覡���D'), 'woocommerce'),
                    'default' => __(u2bcr('SmilePay �u�W��d ú�O'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2bcr('�I�ڤ覡����'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bcr('�U�Ȧb��ܥI�ڤ覡�ɩ���ܪ����Ф�r'), 'woocommerce'),
                    'default' => __(u2bcr("SmilePay  �u�W��d ú�O"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2bcr('�Ӯa�N��'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('�ж�J�zSmilePay�ө��N��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2bcr('�Ӯa�ѼƽX'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('�ж�J�zSmilePay�Ӯa�ѼƽX'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2bcr('�Ӯa�ˬd�X'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bcr('�ж�J�zSmilePay�Ӯa�ˬd�X�A�ˬd�X��Ӯa��x�u�I������API�v�������A�нƻs�öK�J�W�����'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
				'Order_OKmain' => array(
                    'title' => __(u2bcr('�q�榨�߫���ܰT��'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bcr('�q�榨����ܰT��'), 'woocommerce'),
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
		
       public function thankyou_page($order_id) {  //�����^�ǰѼ�����  �P   credit����
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
            $order = new WC_Order($order_id);
			
			
			
			
			// $order = &new WC_Order($order_id);
			
			$SmilePaycredit_args = $this->get_SmilePaycredit_args($order);
			$order_status=$SmilePaycredit_args['post_status'];
			
            //���s���b
            $smilepay_credit_pay_for_order =  WC()->session->get( 'smilepay_credit_pay_for_order');
        
               
		if($order_status=='wc-pending' || $smilepay_credit_pay_for_order)
		{
            //--���ystart
			$shipping_method= $order->get_shipping_method();
			$getc2cu="N";
			if($_REQUEST['storeid']==''&&$_REQUEST['Classif']!='A')
			{
				//if($shipping_method==u2bcr("�t�R�t7-11�����¨��f"))
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
			//--���yend
            //���s���b
            if( $smilepay_credit_pay_for_order)
            {
                WC()->session->__unset('smilepay_credit_pay_for_order');
            }
                
            
			//�q���ܦ��ᵲ��
			$order->update_status('on-hold');	
          	//�}�ҽu�W��d
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
				$pay_ok="ú�O�覡�G<font color=red>$title</font><br>������G�G<font color=red>���v���\</font></br>���v���B�G<font color=red>".
							$A_Amount."</font></br>����ɶ��G<font color=red>".$A_date.$A_time."</font></br>";
				$order->add_order_note(u2bcr($pay_ok),"1");
				$ordertext="<h1>ú�O��T</h1>ú�O�覡�G<font color=red>$title</font><br>������G�G<font color=red>���v���\</font></br>���v���B�G<font color=red>".
							$A_Amount."</font></br>����ɶ��G<font color=red>".$A_date." ".$A_time."</font></br>SmilePay�l�ܽX�G<font color=red>".$A_Smseid."</font>";
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

                //�ˬd���y�O�_�ϥί¨��f
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
                    
					$scvurls_1="7-11���f�����G<font color=red>".b2ucr($c2c_storename . $c2c_storeid)."</font><br>";
                    $scvurls_1 .= "�����a�}�G<font color=red>".b2ucr($c2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "���f�H�m�W�G<font color=red>".b2ucr($pur_name)."</font><br>";
                    $scvurls_1 .= "���f�H�q�ܡG<font color=red>".b2ucr($order->get_billing_phone())."</font><br>";

					$order->add_order_note(u2bcr($scvurls_1),"1");
                    echo u2bcr('<h1>���f��T</h1>' . $scvurls_1);
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
                    
					$scvurls_1="7-11���f�����G<font color=red>".b2ucr($b2c_storename . $b2c_storeid)."</font><br>";
                    $scvurls_1 .= "�����a�}�G<font color=red>".b2ucr($b2c_storeaddress)."</font><br>";
                    $scvurls_1 .= "���f�H�m�W�G<font color=red>".b2ucr($pur_name)."</font><br>";
                    $scvurls_1 .= "���f�H�q�ܡG<font color=red>".b2ucr($order->get_billing_phone())."</font><br>";

					$order->add_order_note(u2bcr($scvurls_1),"1");
                    echo u2bcr('<h1>���f��T</h1>' . $scvurls_1);
				}
				
			}
			else
			{
                if($order_status=='wc-failed')
                {
                    echo u2bcr("<font color='red' size='3'>�p�ѰO<u>ú�O��T</u>�ЦۤW����C<u>�b���]�w->�q��->�˵�</u></font>");
                    return;
                }


				$pay_err="ú�O�覡�G<font color=red>�u�W��d</font><br>������G�G<font color=red>���v����</font></br>����ɶ��G<font color=red>".
							$A_date.$A_time."</font></br>���ѭ�]�G<font color=red>".$A_Errdesc."</font></br>";
				$order->add_order_note(u2bcr($pay_err),"1");
				$ordertext="ú�O�覡�G<font color=red>�u�W��d</font><br>������G�G<font color=red>���v����</font></br>����ɶ��G<font color=red>".
							$A_date." ".$A_time."</font></br>���ѭ�]�G<font color=red>".$A_Errdesc."</font></br>SmilePay�l�ܽX�G<font color=red>".$A_Smseid."</font>";
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
                wc_add_notice(u2bcr("<br>�t�R�t�Ӯa�Ѽƿ��~�A�лP�Ӯa�pô<br>") , 'error' );
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
            */

            //���s���b
            if(isset($_REQUEST['pay_for_order']) && $_REQUEST['pay_for_order']=='true')
            {
                WC()->session->set( 'smilepay_credit_pay_for_order', true );
            }

            /*if($smilepayc2cup_obj && $smilepayc2cup_obj->isSelectedSmilepayC2Cup($order_id))
			//if($shipping_method==u2bcr("�t�R�t7-11�����¨��f"))
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
                    wc_add_notice( __(u2bcr("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if( $smilepayc2cp_obj && $smilepayc2cp_obj->isSelectedSmilepayC2Cp($order_id))
			{
                wc_add_notice( __(u2bcr("<br><font color='red'>�t�e�覡��ܿ��~�A���i��ܦ��t�e�覡</font><br>"), 'woothemes') . $error_message, 'error' );
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
                    wc_add_notice( __(u2bcr("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
            if( $smilepayb2cp_obj && $smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))
			{
                wc_add_notice( __(u2bcr("<br><font color='red'>�t�e�覡��ܿ��~�A���i��ܦ��t�e�覡</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
                exit;
            }
            //<b2c> end
            */
			//--���yend*/
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
    function u2bcr($text)//�e����X
    {	return iconv("big5","UTF-8",$text);}
}

if(!function_exists('b2ucr'))
{
    function b2ucr($text)//�g�J��Ʈw
    {	return iconv("UTF-8","big5",$text);}
}
?>