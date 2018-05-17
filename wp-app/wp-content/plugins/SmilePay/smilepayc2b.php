<?php
/**
 * Plugin Name: SmilePay_C2B
 * Plugin URI: http://www.smilepay.net
 * Description: SmilePay C2B
 * Author:  SmilePay
 * Author URI: http://www.smilepay.net
 * Version: 3.0.5
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    class Smilepay_c2b
    {
        protected $setting_item;
        protected $setting_data;
        public $error_message;
        const SETTING_ID = 'smilepay_c2b_settings';
        protected $smilepay_c2b_create_gateway = "https://ssl.smse.com.tw/ezpos/mtmk_utf.asp";
         
        public function __construct() 
        {
		    //$this->includes();
            //menu setting
            add_action('admin_menu', array($this,'addAdminMenu'),50);
		    add_action('plugins_loaded', array($this,'init_data'), 0);
            $this->setting_data = get_option(self::SETTING_ID);

		    $this->setting_item['paid_cs'] = array(
		                                    'C' => '客人',
                                            'S' => '店家'
		                              );
	    }

        public function init_data()
        {
            $this->init_hooks();
            if(isset($this->setting_data['enabled']) && $this->setting_data['enabled'] == 'Y')
            {
                if(is_admin())
                {
                    add_action( 'add_meta_boxes', array( $this, 'c2b_order_add_meta_boxes' ), 35 );
                }
                else
                {
                    
                } 
            }
        }

        protected function init_hooks()
        {
            
            if(isset($this->setting_data['enabled']) && $this->setting_data['enabled'] == 'Y')
            {
                
                if(is_admin())
                {

                    //ajax
                  //  add_action( 'wp_ajax_smilepayc2b_create_order', array($this,'c2b_create_order' ));
                }
            }
            
        }


        public function c2b_order_add_meta_boxes() {
		    $screen    = get_current_screen();
		    $screen_id = $screen ? $screen->id : '';
            add_meta_box( 'smilepayc2b-order-data', "速買配C2B退貨便", array($this,'c2b_order_output'), 'shop_order', 'normal', 'high' );

        }
        public function get_order_c2b_smseid($order_id)
        {
            return get_post_meta( $order_id, 'Smilepay_C2B_SMSEID', true );
        }

        public function c2b_create_order_process($order_id)
        {
            if(empty($order_id))
            {
                echo '錯誤訂單編號';
                return;
            }
            $order = wc_get_order( $order_id ); 
            $pur_name = $order->get_shipping_last_name() . $order->get_shipping_first_name();

            $post_data=array();
            $post_data['Data_id'] = $order->get_id();
            $post_data['Dcvc'] = $this->setting_data['dcvc'];
            $post_data['Rvg2c'] = $this->setting_data['rvg2c'];
            $post_data['verify_key'] = $this->setting_data['verify_key'];
            if($this->setting_data['paid_cs']=='C')
                $post_data['Pay_zg'] = "58";
            elseif($this->setting_data['paid_cs']=='S')
                $post_data['Pay_zg'] = "57";
            $post_data['Pay_subzg'] = '7NET';
            $post_data['Amount'] = intval(round($order->get_total()));
            $post_data['Od_sob'] = $order->get_id();
            $post_data['Pur_name'] = $pur_name;
            $post_data['Mobile_number'] = $order->get_billing_phone();
            $post_data['Email'] = $order->get_billing_email();
            $post_data['Remark'] = 'woocommerce';
           // $post_data['MapRoturl'] = admin_url('post.php?post=' .$order_id  . "&action=edit" );

            $post_data['MapRoturl'] = admin_url('admin.php?page=smilepay_c2b_api&api_action=c2b_recv_order&post=' . $order_id);
            echo "<form id=\"id_smilepay_c2b_create\" action=\"{$this->smilepay_c2b_create_gateway}\">";
            foreach( $post_data as $name=>$val)
            {
                
                echo "<input type='hidden' name='$name' value='$val' />\r\n";
            }
            
            echo '<script>document.getElementById("id_smilepay_c2b_create").submit()</script>';


        }

        public function c2b_recv_order_process()
        {
            $status = $_REQUEST['Status'];
            $classif = $_REQUEST['Classif'];
            $classif_sub = $_REQUEST['Classif_sub'];
            $data_id = $_REQUEST['Data_id'];
            
            $payno = $_REQUEST['Paymentno'] . $_REQUEST['Validationno'];
            $smseid = $_REQUEST['Smseid'];
            $order_id = $data_id;

            if($status ==1 && ($classif == 'R' || $classif == 'S'))
            {
                $order = wc_get_order( $order_id ); 

                $admin_note =  sprintf("退貨便<br>速買配追蹤碼:%s<br>退貨便號碼:%s",$smseid,$payno);
                $customer_note =  sprintf("退貨便<br>退貨便號碼:%s",$payno);
                $order->add_order_note($admin_note,false);
                $order->add_order_note($customer_note,true);
                update_post_meta($order_id,"Smilepay_C2B_SMSEID",$smseid);
                
            }
            wp_redirect(admin_url('post.php?post=' .$order_id  . "&action=edit" ));
        }
        public function api_process()
        {
           
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

                $this->c2b_create_order_process($order_id);
            }
            elseif($action == 'c2b_recv_order')
            {
                $this->c2b_recv_order_process();
            }
        }

        



        public function c2b_order_output()
        {
           if(isset($_REQUEST['post']))
                $order_id = $_REQUEST['post'];
           else
                $order_id = 0;

           if( !$order_id)
                $this->error_message = "無法取得訂單";
                
           include_once('tpl/admin/html_smilepayc2b_order_meta_box_admin.php');
       }

        public function addAdminMenu()
        {
            add_submenu_page('woocommerce','smilepay_c2b', '速買配C2B退貨便', 'administrator', 'smilepay_c2b',array($this,'adminMenuTpl'));
            register_setting( 'smilepay_c2b-settings-main-group',Smilepay_c2b::SETTING_ID );
            
            //fake api page
            if(isset($this->setting_data['enabled']) && $this->setting_data['enabled'] == 'Y')
            {
                add_submenu_page('','smilepay_c2b_api', '速買配C2B退貨便Api', 'administrator', 'smilepay_c2b_api',array($this,'api_process'));
            }
           // add_menu_page('smilepay_einvoice_api','smilepay_einvoice_api', '速買配電子發票API', 'smilepay_einvoice_api',array($this,'api_process'));
        }
        public function adminMenuTpl()
        {
            include_once('tpl/admin/smilepay_c2b_setting_tpl.php');
        }
        public function get_setting_data()
        {
            return  $this->setting_data;
        }
        public function get_setting_item()
        {
            return  $this->setting_item;
        }

    }
    $smilepayc2b=new Smilepay_c2b();
}

?>