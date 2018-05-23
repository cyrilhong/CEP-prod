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
        public function init_form_fields() {  //��x�]�m���
		//	$urll=get_option('siteurl')."/?respond";
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __(u2bb2("�ҥ�/����"), 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __(u2bb2(' SmilePay 7-11�W�Ө��f�I��'), 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __(u2bb2('���D'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('�U�Ȧb���b�ɩ���ܪ��I�ڤ覡�M�t�e�覡���D'), 'woocommerce'),
                    'default' => __(u2bb2('SmilePay 7-11�W�Ө��f�I��'), 'woocommerce')
                ),
                'description' => array(
                    'title' => __(u2bb2('�I�ڤ覡����'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bb2('�U�Ȧb��ܥI�ڤ覡�ɩ���ܪ����Ф�r'), 'woocommerce'),
                    'default' => __(u2bb2("SmilePay  �W�Ө��f�I�� ú�O"), 'woocommerce')
                ),
                'dcvc' => array(
                    'title' => __(u2bb2('�Ӯa�N��'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('�ж�J�zSmilePay�ө��N��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Rvg2c' => array(
                    'title' => __(u2bb2('�Ӯa�ѼƽX'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('�ж�J�zSmilePay�Ӯa�ѼƽX'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
                'Verify_key' => array(
                    'title' => __(u2bb2('�Ӯa�ˬd�X'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('�ж�J�zSmilePay�Ӯa�ˬd�X�A�ˬd�X��Ӯa��x�u�I������API�v�������A�нƻs�öK�J�W�����'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
                'Mid_smilepay' => array(
                    'title' => __(u2bb2('�Ӯa���ҰѼ�'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('�ж�J�zSmilePa���ҽX�A���ҽX��Ӯa��x�u�򥻸�ƺ޲z�v�������A�нƻs�öK�J�W�����A�p�������ҽЫO�d�ť�'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),				
				'Order_OKmain' => array(
                    'title' => __(u2bb2('�q�榨�߫���ܰT��'), 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __(u2bb2('�q�榨����ܰT��'), 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ),
				/*'reurl' => array(
                    'title' => __(u2bb2('��������^�e��m'), 'woocommerce'),
                    'type' => 'text',
                    'description' => __(u2bb2('��������^�e��m�A�бN�U�C��r�ƻs��W��ؤ�(�p�����^�e�Яd�ť�)</br><font color=red ue size=+1>'.$urll.'</font>', 'woocommerce')),
                    'default' => __('', 'woocommerce')
                ),*/
				'hiddtext' => array(
                    'title' => __(u2bb2('�W�Ө��f�I�ڪ`�N�ƶ�'), 'woocommerce'),
                    'type' => 'hidden',
                    'description' => __(u2bb2("�ϥζW�Ө��f�I�ڥ\��A�ݪ`�N�H�U�ƶ��G
												<br>��<font color='red'>�ϥΦ��Ҳե����}�ҡi�t�R�t7-11�������f�I�ڡj�t�e�ҲաA�нT�{�ҥ�</font>												
												<br>1.�Х���SmilePay�Ӯa��x�}�Ҩ��f�I�ڥ\��*<a target='_blank' href='http://www.smilepay.net/RVG.ASP'>�Ӯa��x</a>*
												<br>2.*�H�f�ӫ~�W��*���q��W�١A�ëD�ӫ~���ӡA�]�q�W<font color='red'>�L�е������</font>�C
												<br>3.�b���T��<font color='red'>�p���q��</font>������<font color='red'>������X</font>�A�ӫ~������ɷ|�H²�T�q���C
												<br>4.���O�̦��߭q���A�Ц�SmilePay�Ӯa��x�����o<font color='red'>��f�K�N�X</font>�C
												<br>5.���O�̦��߭q���A�i�ܭq��Ƶ����I��<font color='red'>�}�ҥ�f�K�A�ȳ�</font>�C																										
												<br>6.�бN<font color='red'>��f�K�N�X</font>��ʷs�W�ܰӫ~�Ƶ��A�H�Q���O�̬d�ߪ��y���A�C
												<br>7.��h�����аѾ\�A<font color='red'>SmilePay��������</font>�P<font color='red'>WooCommerce�Ҳջ������</font>�C
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
		
       public function thankyou_page($order_id) {  //�����^�ǰѼ�����  �P   b2c����
			global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
            $order = new WC_Order($order_id);
			// $order = &new WC_Order($order_id);

			$SmilePayb2c_args = $this->get_SmilePayb2c_args($order);
			$order_status=$SmilePayb2c_args['post_status'];
            $pur_name = $order->get_billing_last_name() . $order->get_billing_first_name();

           //���s���b
            $smilepay_credit_pay_for_order =  WC()->session->get( 'smilepay_credit_pay_for_order');
     

		if($order_status=='wc-pending'||$smilepay_credit_pay_for_order)
		{	
			//�P�_�q�ܬO�_���T,�m�W
			/*if(substr($order->billing_phone,0,2)!="09"||mb_strlen($order->billing_phone)!=10){
				$order->update_status('cancelled');
				echo u2bb2("<font color=red>�q�榨�ߥ���</font><br><br>���~��T�G<br>�s���q�ܽп�J���(�@10�X)�A�ӫ~�쩱��A�N�o�e²�T�q���C<br>�W�r�P�m���`�X���i�W�L5�Ӧr�C<br><br>�Э��s���b�C");
				b2caddtocart($thepostid);
				exit();
			}	
			if(mb_strlen($order->billing_first_name.$order->billing_last_name)>5){
				$order->update_status('cancelled');
				echo u2bb2("<font color=red>�q�榨�ߥ���</font><br><br>���~��T�G<br>�s���q�ܽп�J���(�@10�X)�A�ӫ~�쩱��A�N�o�e²�T�q���C<br>�W�r�P�m���`�X���i�W�L5�Ӧr�C<br><br>�Э��s���b�C");
				b2caddtocart($thepostid);
				exit();
			}*/
			
			//�ھڷ�U�����վ�reurl�Ohttp��https
            if(is_ssl())
                $SmilePayb2c_args['reurl']=str_replace('http://','https://',$SmilePayb2c_args['reurl']);
            else
                $SmilePayb2c_args['reurl']=str_replace('https://','http://',$SmilePayb2c_args['reurl']);	
			//�}��b2c
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
            //���s���b
            if( $smilepay_credit_pay_for_order)
            {
                WC()->session->__unset('smilepay_credit_pay_for_order');
            }
                			//�q���ܦ����Ѥ�			
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
			$pay_ok="ú�O�覡�G<font color=red>$title</font><br>���f�����G<font color=red>".$T_Storename.$T_Storeid."</font><br>";
            $pay_ok .= "�����a�}�G<font color=red>".b2ub2($T_Storeaddress)."</font><br>";
            $pay_ok .= "���f�H�m�W�G<font color=red>".b2ub2($pur_name )."</font><br>";
            $pay_ok .= "���f�H�q�ܡG<font color=red>".b2ub2($order->get_billing_phone())."</font><br>";
			
            $order->add_order_note(u2bb2($pay_ok),"1");
			$ordertext="�ФŧR������T�A�t�R�tSmilePay�l�ܽX�G".$T_Smseid;
			$order->add_order_note(u2bb2($ordertext),"0");
			echo u2bb2("<font color='red' size='3'>�`�N�G�q��w���ߡA�ݰӮa�H�f��A�ӫ~��F�����N�|�H²�T�q���I�ڨ��f</font><br><br>");
			echo u2bb2("<h1>ú�O��T</h1>".$pay_ok);
			echo "<br><br>".$SmilePayb2c_args['Order_OKmain'];
            
		}
        else
        {
            echo u2bb2("<font color='red' size='3'>�p�ѰO<u>ú�O��T</u>�ЦۤW����C<u>�b���]�w->�q��->�˵�</u></font>");
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
                wc_add_notice(u2bb2("<br>�t�R�t�Ӯa�Ѽƿ��~�A�лP�Ӯa�pô<br>") , 'error' );
                return;
            }
			/*//--���ystart
			$shipping_method= $order->get_shipping_method();

            include_once('smilepayb2cp.php');
            if(!class_exists('WC_Smilepayb2cp_Shipping_Method'))
                Smilepayb2cp_shipping_method_init();
            $smilepayb2cp_obj=new WC_Smilepayb2cp_Shipping_Method();

            //���s���b
            if(isset($_REQUEST['pay_for_order']) && $_REQUEST['pay_for_order']=='true')
            {
                WC()->session->set( 'smilepay_credit_pay_for_order', true );
            }
            
            if($smilepayb2cp_obj->isSelectedSmilepayB2Cp($order_id))
			//if($shipping_method==u2bb2("�t�R�t7-11�������f�I��"))
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
                    wc_add_notice( __(u2bb2("<br>�q�ܮ榡���~�A�d��09XX123456(�@10�X)�C<br>���O�̩m�W���i�W�L5�Ӧr�A�­^��h���i�W�L10�Ӧr�C�ЦA���T�{�C<br>"), 'woothemes') . $error_message, 'error' );
                    return;
					exit;
				}
			}
			else
			{
                $title=b2ub2($this->title);
                wc_add_notice( __(u2bb2("<br><font color='red'>�t�e�覡��ܿ��~�A�нT�{�t�e�覡���i<b>$title</b>�j</font><br>"), 'woothemes') . $error_message, 'error' );
                return;
			    exit;
			}
			//--���yend*/
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
    

    

function u2bb2($text)//�e����X
{	return iconv("big5","UTF-8",$text);}
function b2ub2($text)//�g�J��Ʈw
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
//���ӫ~�q�渹
$order_items = $wpdb->get_results( "SELECT * FROM `".$tablelist."woocommerce_order_items"."` WHERE `order_id` = '$thepostid' " );
	foreach ( $order_items as $order_no ) 
	{
		$vr =$order_no->order_item_id;
		//���ӫ~ID,�ƶq
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