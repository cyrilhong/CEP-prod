<?php
header("Access-Control-Allow-Origin: *");
//status_header(200);
/**
 * Plugin Name: Woofluid
 * Description: Woofluid
 * Version: 1.0
 * Author: Ebizon
 * Author URI: Ebizontech.com
 **/
session_start();

http_response_code(200);
if($_GET['service'] == 'createuser'){
	if(!isset($_SESSION['mofluid_email']) && isset($_GET['email'])){
		$_SESSION['mofluid_email'] = base64_encode($_GET['email']);
	}
}else if($_GET['service'] == 'verifylogin'){
	if(!isset($_SESSION['mofluid_username']) && isset($_GET['username'])){
		$_SESSION['mofluid_username'] = base64_encode($_GET['username']);
	}
}else if($_GET['service'] == 'changeprofilepassword'){
	if(!isset($_SESSION['mofluid_username']) && isset($_GET['username'])){
		$_SESSION['mofluid_username'] = base64_encode($_GET['username']);
	}
}else if($_GET['service'] == 'forgotPassword'){
	if(!isset($_SESSION['mofluid_email']) && isset($_GET['email'])){
		$_SESSION['mofluid_email'] = base64_encode($_GET['email']);
	}
}else if($_GET['service'] == 'setprofile'){
	if(!isset($_SESSION['mofluid_profile']) && isset($_GET['profile'])){
		$profileArgs = json_decode($_GET['profile']); 
		$profileArgs->email = base64_encode($profileArgs->email);
		$_SESSION['mofluid_profile'] = $profileArgs;
	}
}
else if($_GET['service'] == 'loginwithsocial'){
if(!isset($_SESSION['mofluid_email']) && isset($_GET['email'])){
		$_SESSION['mofluid_email'] = base64_encode($_GET['email']);
	}
}
 require('API_class.php');
 add_action('init', 'wpse42279_add_endpoints');
function wpse42279_add_endpoints()
{
    add_rewrite_endpoint('woofluid', EP_PAGES);
   // add_rewrite_endpoint('activities', EP_PAGES);
}

add_action( 'template_redirect', 'wpse42279_catch_vars' );

function wpse42279_catch_vars()
{       
        global $wp_query;
	if (  isset( $wp_query->query_vars['pagename'] ) && $wp_query->query_vars['pagename'] == 'woofluid')
        {
		API($_GET);
		exit;
		}
	
}

function API($args){
	http_response_code(200);
	$service = $args['service'];
	$api_class = new API_class;
	//echo $service;die;
 	switch($service){
		case category:
			$api_class->get_product_categories();
			break;
		case subcategory:
			$categoryid = $args['categoryid'];
			$api_class->get_product_subcategory($categoryid);
			break;
		case products:
			$categoryid = $args['categoryid'];
			$api_class->get_product_byID($categoryid);
			break;
		case getFeaturedProducts:
			$showproducts = $args['show'];
			$api_class->getFeaturedProducts($showproducts);
			break;
		case getNewProducts:
		    $showproducts = $args['show'];
			$api_class->getNewProducts($showproducts);
			break;
		case createuser:
			$args['email'] = $_SESSION['mofluid_email'];
			unset($_SESSION['mofluid_email']);
			$api_class->create_user($args);
			break;
		case search:
			//$search_data = $args['search_data'];
			$api_class->product_search($args);
			break;
		case verifylogin:
			$username = $_SESSION['mofluid_username'];
			unset($_SESSION['mofluid_username']);
			$password = $args['password'];
			$api_class->verify_user_login($username,$password);
			break;
		case getcustomer:
			$email = $args['email'];
			$api_class->get_customer($email);
			break;
		case myprofile:
			$customerid = $args['customerid'];
			$api_class->get_myprofile($customerid);
			break;
		case currency:
			//$customerid = $args['customerid'];
			$api_class->get_currency();
			break;
		case changeprofilepassword:
			$args['username'] = $_SESSION['mofluid_username'];
			unset($_SESSION['mofluid_username']);
			$api_class->change_password($args);
			break;
		case productsearchhelp:
			$api_class->productsearchhelp();
			break;
		case productinfo:
			$productid = $args['productid'];
			$api_class->productdetail($productid);
			break;
		case productdetaildescription:
			$productid = $args['productid'];
			$api_class->productdetaildescription($productid);
			break;
		case productdetailimage:
			$productid = $args['productid'];
			$api_class->productdetailimage($productid);
			break;
		case forgotPassword:
			$email = $_SESSION['mofluid_email'];
			unset($_SESSION['mofluid_email']);
			$api_class->forgotPassword($email);
			break;
		case storedetails:
			$api_class->getStoreInfo();
			break;
		case countryList:
			$api_class->countryList();
			break;
		case productQuantity:	
			$product_ids = $args['product'];		
			$api_class->productQuantity($product_ids);
			break;
		case placeorder:				
			$api_class->createorder($args);
			break;
		case orderupdate:		
			$api_class->updateOrderStatus($args);
			break;
		case setaddress:			
			$api_class->setaddress($args);
			break;
		case termCondition:			
			$api_class->termCondition();
			break;
		case setprofile:	
			 $args['profile'] = $_SESSION['mofluid_profile'];
              unset($_SESSION['mofluid_profile']);
			$api_class->setprofile($args);
			break;
		case listShipping:		
			$api_class->listShipping();
			break;
		case validateCoupon:		
			$api_class->validateCoupon($args);
			break;
		case couponDetails:				
			$api_class->couponDetails($args);
			break;
		case countryStateList:				
			$api_class->countryStateList($args);
			break;		
		case sendordermail:			
			$api_class->sendordermail($args);
			break;				
		case getShippingDetail:			
			$api_class->getShippingDetail($args);
			break;
		case shipmyidenabled:			
			$api_class->shipmyidenabled($args);
			break;
		case myorders:		
			$api_class->myorders($args);
			break;
		case mofluidappcountry:		
			$api_class->countryList();
			break;
		case mofluidappstates:
			//$CC = trim($args['country']);		
			$api_class->stateList();
			//$CC
			break;
		case getpaymentmethod:		
			$api_class->payment_methods();
			break;
		case validate_currency:
			$api_class->validate_currency($args);
			break;
        case preparequote:
			$api_class->preparequote($args);
			break;    
        case orderinfo:
			$api_class->get_orderinfo($args);
			break;        
		case paymentresponse:
			$api_class->paymentresponse($args);
			break;		
	   // Add extra webservice as per new build i.e 2.0:
	   case initial:
			$api_class->getCategoriesList($args);
			break;
	   case loginwithsocial:
	        $args['email'] = $_SESSION['mofluid_email'];
			unset($_SESSION['mofluid_email']);
			$api_class->ws_loginwithsocial($args);
			break;
	   case mofluidUpdateProfile:
			$api_class->update_myprofile($args);
			break;
				
	}
	
 }
 //die(plugin_dir_path( __FILE__ ).'uninstall.php');
 require(plugin_dir_path( __FILE__ ).'install.php');
require(plugin_dir_path( __FILE__ ).'uninstall.php');
 
 
/******************All dependent Plugins activation Code*******************************/ 
register_activation_hook(__FILE__,'activate_mofluid_plugins'); 
function activate_mofluid_plugins(){
   
   $main_plugin = "woofluid/woofluid.php";
    if( is_plugin_inactive($main_plugin) ){
         create_wp_presscart_schema();
         add_action('update_option_active_plugins', 'activate_mf_plugins');
         
    }
}

function activate_mf_plugins(){
   // Put your new plugin path Entry in below Array() for activation. Neeraj Kumar(22-05-2015)
   $plugin_arr = array(
                    
                    "mofluid_android" => "woofluid/woofluid_android.php",
                    "mofluid_iphone" => "woofluid/woofluid_iphone.php",
                    "mofluid_setting_menu" => "woofluid/woofluid_menu.php",
       
    
                    );
   foreach($plugin_arr as $val){
      
    activate_plugin(trim($val));
   }
    
}
/***********************************************************************************************/
/******************All dependent Plugins deactivation Code**************************************/

register_deactivation_hook(__FILE__,'deactivate_mofluid_plugins'); 
function deactivate_mofluid_plugins(){
   
   $main_plugin = "woofluid/woofluid.php";
   
    if( is_plugin_active($main_plugin) ){
         uninstall_schema();
         add_action('update_option_active_plugins', 'deactivate_mf_plugins');
    }
}

function deactivate_mf_plugins(){
   // Put your new plugin path Entry in below Array() for deactivation.  Neeraj Kumar(22-05-2015)
   $plugin_arr = array(
                    "mofluid_paypal" => "woofluid/woofluid_paypal.php",
                    "mofluid_android" => "woofluid/woofluid_android.php",
                    
                    "mofluid_setting_menu" => "woofluid/woofluid_menu.php",
                    );
   foreach($plugin_arr as $val){
    deactivate_plugins(trim($val));
   }
    
}
/*************************************************************/
