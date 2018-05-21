<?php
/**
 * Plugin Name: Wp Presscart menu
  * Description: Wp Presscart Menu
 * Version: 1.0
 * Author: Ebizon
 * Author URI: Ebizontech.com
 **/
add_action( 'admin_menu', 'register_wp_presscart_page_dashboard' );

function register_wp_presscart_page_dashboard(){
    add_menu_page( 'Woofluid', 'Woofluid', 'manage_options', 'platform_configuration_callback', '', '', 6 );
}
//add_action( 'init', 'theme_name_scripts' );
// sub menu
add_action('admin_menu', 'register_wp_presscart_sub_menu');

function register_wp_presscart_sub_menu() {
	add_submenu_page( 'platform_configuration_callback' , 'Platform Configuration', 'Platform Configuration', 'manage_options', 'platform_configuration_callback', 'platform_configuration_callback' ); 
	add_submenu_page( 'platform_configuration_callback' , 'Application Assets', 'Application Assets', 'manage_options', 'assest_configuration_callback', 'assest_configuration_callback' ); 
	add_submenu_page( 'platform_configuration_callback' , 'Theme Configuration', 'Theme Configuration', 'manage_options', 'theme_configuration_callback', 'theme_configuration_callback' ); 
	add_submenu_page( 'platform_configuration_callback' , 'Payment Configuration', 'Payment Configuration', 'manage_options', 'payment_configuration_callback', 'payment_configuration_callback' ); 
	//add_submenu_page( 'wp_presscart_page' , 'Push Notification', 'Push Notification', 'manage_options', 'push_notification_callback', 'push_notification_callback' ); 
	add_submenu_page( 'platform_configuration_callback' , 'Generate App', 'Generate App', 'manage_options', 'preview_and_donwload_callback', 'preview_and_donwload_callback' ); 
	add_submenu_page( 'wp_presscart_page' , 'Android', 'Generate App', 'manage_options', 'android_config_callback', 'android_config_callback' ); 
}


/**
 * Proper way to enqueue scripts and styles
 */
 add_action( 'init', 'wppresscart_name_scripts' );
function wppresscart_name_scripts() {
	
	wp_enqueue_script( 'wp_presscart_menu_js', plugins_url().'/wp_mofluid/js/tab_menu_presscart.js', array(), '1.0.0', true );

}

function platform_configuration_callback(){
	$config_menu = get_option('wppresscart_configuration_menu');
	if(!$config_menu)
	return true;
	
	$count = 1;
	echo "<h3>Platform Configuration</h3>";
	echo '<ul id="tabs">';
	foreach($config_menu as $key => $value){
		?>
			
				  
				  <?php
				 	  echo '<li><a href="#" name="#tab'.$count.'">'.$key.'</a></li>';					
				  ?>
				  
			  
		<?php
		$count++;
	}
	echo '</ul>';
	echo '<div id="content">';
	$count = 1;
	//var_dump($config_menu);die;
	//echo "<h3>Platform Configuration</h3>";
	foreach($config_menu as $key => $value){
		?>
			<div id="tab<?php echo $count;?>">
				  <h2><?php echo $key;?> Platform Configuration</h2>
				<hr> 
				  <?php
				  call_user_func($value);
				  ?>
				  
			  </div>
		<?php
		$count++;
	}
	echo '</div>';
	
}

function assest_configuration_callback(){
	$assest_menu = get_option('wppresscart_assest_configuration_menu');
	if(!$assest_menu)
	return true;
	$count = 1;
	//var_dump($assest_menu);die;
	echo "<h3>Application Assets</h3>";
	echo '<ul id="tabs">';
	foreach($assest_menu as $key => $value){
		?>
			
				  
				  <?php
				 	  echo '<li><a href="#" name="#tab'.$count.'">'.$key.'</a></li>';					
				  ?>
				  
			  
		<?php
		$count++;
	}
	echo '</ul>';
	echo '<div id="content">';
	$count = 1;
	//var_dump($config_menu);die;
	//echo "<h3>Platform Configuration</h3>";
	foreach($assest_menu as $key => $value){
		?>
			<div id="tab<?php echo $count;?>">
				  <h2><?php echo $key;?> Assets Configuration</h2>
				<hr> 
				  <?php
				  call_user_func($value);
				  ?>
				  
			  </div>
		<?php
		$count++;
	}
	echo '</div>';
	
}

function theme_configuration_callback(){
	$theme_menu = get_option('wppresscart_theme_configuration_menu');
	if(!$theme_menu)
	return true;
	$count = 1;
	//var_dump($assest_menu);die;
	echo "<h3>Application Theme</h3>";
	echo '<ul id="tabs">';
	foreach($theme_menu as $key => $value){
		?>
			
				  
				  <?php
				 	  echo '<li><a href="#" name="#tab'.$count.'">'.$key.'</a></li>';					
				  ?>
				  
			  
		<?php
		$count++;
	}
	echo '</ul>';
	echo '<div id="content">';
	$count = 1;
	//var_dump($config_menu);die;
	//echo "<h3>Platform Configuration</h3>";
	foreach($theme_menu as $key => $value){
		?>
			<div id="tab<?php echo $count;?>">
				  <h2><?php echo $key;?> Theme Configuration</h2>
				<hr> 
				  <?php
				  call_user_func($value);
				  ?>
				  
			  </div>
		<?php
		$count++;
	}
	echo '</div>';
	
}

function payment_configuration_callback(){
  global $wpdb;
    if(isset($_POST['payment_methods_submit'])){  
        $data = $_POST['method_status'];
        //print_r($data); die;
        $table = "mofluidpayment";
        $where = array('payment_method_status' => 1);
        $data_array = array('payment_method_status' => 0);
       
        $wpdb->update( $table, $data_array,$where);
        
            foreach($data as $val){
                $where = array('payment_method_id' => (int)$val);
                $data_array = array('payment_method_status' => 1);
                //var_dump($where);
                $wpdb->update( $table, $data_array, $where );
            }
            
	}
   
        if(isset($_POST['paypal_settings_submit'])){
            
            $merchant_id = $_POST['merchant_id'];
            $mode = $_POST['paypal_mode'];
            $paypal_id = $_POST['paypal_id'];
            $business_mail = $_POST['bus_mail'];
            $paypal_mode = '';
            if($mode == 'live')
                                       $paypal_mode = '1';
                                    else
                                        $paypal_mode = '0';
            $table = "mofluidpayment";
            $data = array('payment_method_account_id' => $merchant_id, 'payment_account_email'=>$business_mail, 'payment_method_mode' => $paypal_mode);
            $where = array('payment_method_id' => (int)$paypal_id);
            //print_r($data); die;
            $wpdb->update( $table, $data, $where );
            
        }
    
	echo "<h3>Payment Configuration</h3>";
	echo '<ul id="tabs">';
	
				 	  echo '<li><a href="#" name="#tab1">Payment Configuration</a></li>';					
				 	  //echo '<li><a href="#" name="#tab2">Paypal Configuration</a></li>';					
				 
	echo '</ul>';
	echo '<div id="content">';
	?>
			<div id="tab1">
			<?php                                   
                                    $payments_info = "SELECT * FROM mofluidpayment"; 
                                    $result = $wpdb->get_results($payments_info, ARRAY_A);
                         
                        ?>
				<hr> 
                                <form name="payment_methods" action="#" method="post" enctype="multipart/form-data">
                                    <table cellspacing = "10" class="payment_menthods">
                                        <thead>
                                           <tr><th>Payment</th><th>Active</th></tr>
                                           </thead>
                                           <tbody>
                                           <?php
                                           foreach($result as $val){
                                           $id = $val['payment_method_id'];    
                                           $status = $val['payment_method_status'];
                                           if($status == '0'){
                                           $status = '';    
                                           }else{
                                               $status = 'checked';    
                                           }
                                          // echo $status;
											if(strtolower($val["payment_method_title"]) == "cash on delivery")
												echo '<tr id='."$id".'><td>'.$val["payment_method_title"].'</td><td><input type="checkbox" name="method_status[]" '."$status".' value='."$id".'></td></tr>'; 
												break;
											} ?>
                                               <tr><td><input name="payment_methods_submit" value="Save" type="submit"></td></tr>    
                                           </tbody>
                                    </table>  
                                </form>       
			  </div>
			<div id="tab2">
                            <?php                                   
                                    
                                    $result = $wpdb->get_row("SELECT * FROM mofluidpayment WHERE payment_method_code = 'paypal_standard'");
                                    
                                    $paypal_id = $result->payment_method_id;
                                   
                                    $mode = $result->payment_method_mode;
                                    
                                    $bus_mail = $result->payment_account_email;
                                    $merchant_id = $result->payment_method_account_id;
                                    $liveSel = '';
                                    $sandboxSel = '';
                                    if($mode == '1')
                                       $liveSel = 'selected';
                                    else
                                        $sandboxSel = 'selected';
                       
                        ?>
				     
			  </div>
                                        
		
	</div><?php
        
        
}


function push_notification_callback(){


	$push_menu = get_option('wppresscart_push_configuration_menu');
	if(!$push_menu)
	return true;
	
	$count = 1;
	//var_dump($assest_menu);die;
	echo "<h3>Push Notification</h3>";
	echo '<ul id="tabs">';
	foreach($push_menu as $key => $value){
		?>
			
				  
				  <?php
				 	  echo '<li><a href="#" name="#tab'.$count.'">'.$key.'</a></li>';					
				  ?>
				  
			  
		<?php
		$count++;
	}
	echo '</ul>';
	echo '<div id="content">';
	$count = 1;
	//var_dump($config_menu);die;
	//echo "<h3>Platform Configuration</h3>";
	foreach($push_menu as $key => $value){
		?>
			<div id="tab<?php echo $count;?>">
				  <h2><?php echo $key;?> Push Configuration</h2>
				<hr> 
				  <?php
				  call_user_func($value);
				  ?>
				  
			  </div>
		<?php
		$count++;
	}
	echo '</div>';


}


function preview_and_donwload_callback(){

	$donload_menu = get_option('wppresscart_preview_and_download_configuration_menu');
	if(!$donload_menu)
	return true;
	
	$count = 1;
	//var_dump($assest_menu);die;
	//echo "<h3>Download Build</h3>";
	echo '<ul id="tabs">';
	foreach($donload_menu as $key => $value){
		echo '<li><a href="#" name="#tab'.$count.'">'.$key.'</a></li>';
		$count++;
	}
	echo '</ul>';
	echo '<div id="content">';
	$count = 1;
	//var_dump($config_menu);die;
	//echo "<h3>Platform Configuration</h3>";
	foreach($donload_menu as $key => $value){
		//if(strtolower($key) == "iphone"){
		?>
			<div id="tab<?php echo $count;?>">
				  <h2>Download build for <?php echo $key?>.</h2>
				<hr> 
				  <?php
				  call_user_func($value);
				  ?>
				  
			  </div>
		<?php
		$count++;
	//}
	}
	echo '</div>';
}


/**
 * Proper way to enqueue scripts and styles
 */

function wp_presscart_page(){
		
}

function android_config_callback(){
	add_submenu_page( 'platform_configuration_callback' , 'Android', 'Generate App', 'manage_options', 'android_config_callback', 'android_config_callback' ); 
echo 'android_config_callback';	
}