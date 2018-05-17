<?php
/**
 * Plugin Name: Wp Presscart Iphone
  * Description: Wp Presscart Iphone
 * Version: 1.0
 * Author: Ebizon
 * Author URI: Ebizontech.com
 **/

//require(plugin_dir_path( __FILE__ ).'install.php');
//create_wp_presscart_iphone_schema();
  require('iphone_classes.php');
  
// add menu tab in wppresscart menu
add_action( 'init', 'wp_presscart_iphone_menu' );
function wp_presscart_iphone_menu(){
	if(get_option('wppresscart_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Iphone",get_option('wppresscart_configuration_menu'))){
				$tmp = get_option('wppresscart_configuration_menu');
				$tmp['Iphone'] = 'iphone_platform_configuration_callback';
				update_option('wppresscart_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_configuration_menu', array('Iphone' => 'iphone_platform_configuration_callback'));
	}
	if(get_option('wppresscart_assest_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Iphone",get_option('wppresscart_assest_configuration_menu'))){
				$tmp = get_option('wppresscart_assest_configuration_menu');
				$tmp['Iphone'] = 'iphone_assest_configuration_callback';
				update_option('wppresscart_assest_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_assest_configuration_menu', array('Iphone' => 'iphone_assest_configuration_callback'));
	}
	
	if(get_option('wppresscart_theme_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Iphone",get_option('wppresscart_theme_configuration_menu'))){
				$tmp = get_option('wppresscart_theme_configuration_menu');
				$tmp['Iphone'] = 'iphone_theme_configuration_callback';
				update_option('wppresscart_theme_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_theme_configuration_menu', array('Iphone' => 'iphone_theme_configuration_callback'));
	}
	if(get_option('wppresscart_push_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Iphone",get_option('wppresscart_push_configuration_menu'))){
				$tmp = get_option('wppresscart_push_configuration_menu');
				$tmp['Iphone'] = 'iphone_push_configuration_callback';
				update_option('wppresscart_push_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_push_configuration_menu', array('Iphone' => 'iphone_push_configuration_callback'));
	}
	
	if(get_option('wppresscart_preview_and_download_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Iphone",get_option('wppresscart_preview_and_download_configuration_menu'))){
				$tmp = get_option('wppresscart_preview_and_download_configuration_menu');
				$tmp['Iphone'] = 'iphone_preview_and_download_configuration_callback';
				update_option('wppresscart_preview_and_download_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_preview_and_download_configuration_menu', array('Iphone' => 'iphone_preview_and_download_configuration_callback'));
	}
}
/// end here///


/**
 * Proper way to enqueue scripts and styles
 */
 add_action( 'init', 'iphone_name_scripts' );
function iphone_name_scripts() {
	wp_enqueue_style( 'wp_presscart_iphone_style', plugins_url().'/wp_mofluid/css/iphone_presscart.css' );
	wp_enqueue_script( 'wp_presscart_iphone_js', plugins_url().'/wp_mofluid/js/iphone_presscart.js', array(), '1.0.0', true );
	wp_enqueue_script( 'wp_presscart_js_color', plugins_url().'/wp_mofluid/js/jscolor.js', array(), '1.0.0', true );
}
/*
function wp_presscart_page(){
		
}
*/
function iphone_platform_configuration_callback(){
?>
          <h2>Application Details</h2>
          <hr>    
          <?php
			$iphone_app_obj_data = new Iphone_app_class();
			// Save android app data

			if(isset($_POST['iphone_app_data_submit'])){
				$iphone_app_obj_data->save_iphone_configuration($_POST);
			}
		  $iphone_data = $iphone_app_obj_data->get_iphone_configuration();
		  //var_dump($iphone_data);die;
		  global $wpdb;
		  $mofluid_user_results = $wpdb->get_results( 'SELECT * FROM mofluidadmin_details ORDER BY mofluidadmin_id DESC', OBJECT );
          ?>      
          <form name="iosapp_detail" action="#" method="post" onsubmit = "return saveiPhoneAppDetail();" enctype="multipart/form-data">
			               
		 		           <div class="fieldset box fieldset-wide"><table cellspacing="10"><tbody>
		 		           <tr><td colspan="2"><h4 style="color:#EA7601">Woofluid Details</h4></td></tr>
		 		           <tr><td>Woofluid User Id* : </td><td><input name="mofluid_id" id="mofluid_id" class="required-entry input-text" value="<?php echo isset($mofluid_user_results[0]->mofluid_id)?$mofluid_user_results[0]->mofluid_id:""?>" type="text"> (Email id of your user account at woofluid.com)</td></tr>
		 		           <tr><td>Woofluid Secret Key* : </td><td><input name="mofluid_key" id="mofluid_key" class="required-entry input-text" value="<?php echo isset($mofluid_user_results[0]->mofluid_key)?$mofluid_user_results[0]->mofluid_key:""?>" type="text"> (Password of your user account at woofluid.com)</td></tr>
		 		           <tr><td colspan="2"><h4 style="color:#EA7601">Application Details</h4></td></tr>
		 		           <tr><td>Application Name* : </td><td><input name="name" class="required-entry input-text" value="<?php echo $iphone_data->app_name;?>" type="text"></td></tr>
		 		           <tr><td>Bundle ID* : </td><td><input name="bundle_id" class="input-text" value="<?php echo $iphone_data->bundle_id;?>" type="text"></td></tr>
		                   <tr><td>Version* : </td><td><input name="version" class=" required-entry validate-number validate-zero-or-greater validate-number-range number-range-0-99999999.9999 input-text required-entry" value="<?php echo $iphone_data->version;?>" type="text"></td></tr>
		                   <tr><td>Default Store Id for App* : </td><td><input name="store" class=" required-entry validate-number validate-zero-or-greater validate-number-range number-range-0-99999999.9999 input-text required-entry" value="<?php echo $iphone_data->default_store;?>" type="text"></td></tr>
		                   <tr><td>Device UDID (please use comma for seperate multiple values)* : </td><td><textarea name="device_udid" class="required-entry validate-number validate-zero-or-greater validate-number-range input-text required-entry"><?php echo $iphone_data->device_udid;?></textarea></td></tr>
		                   <tr><td>Default Currency : </td>
		                   <td>
							   <select name="currency_type" class="select" id="currency_type" onchange="">
								   <option value="INR" <?php echo ($iphone_data->currency_type=="INR"?"selected":"")?>>INR</option>
								   <option value="USD" <?php echo ($iphone_data->currency_type=="USD"?"selected":"")?>>USD</option>
								</select> should be same as in your desktop website.
							</td></tr>
							<tr><td colspan="2"><h4 style="color:#EA7601">iOS Certificates</h4></td></tr>
		 		           <tr><td>iOS Certificates* : </td><td><input name="mofluid_certificates" id="mofluid_certificates" class="required-entry input-text" type="file">
		 		           <br><i style="font-size:12px;">(Upload your iOS Developer Account Certificate with its private key (.p12 file). Export your certificate and private key pair from your MacOS X keychain which will create .p12 file. for more detail <a target="_blank" href="http://docs.mofluid.com/how-to-export-ios-certificates">Click here</a>)</i></td></tr>
		 		           <tr><td>Passpharse* : </td><td><input name="mofluid_passpharse" id="mofluid_passpharse" class="required-entry input-text" value="" type="text">
		 		           <br><i style="font-size:12px;">(Provide Passpharse to unlock your certificate. which you have given while exporting the certificate.)</ii></td></tr>
		 		           <tr><td>Certificate Type* : </td>
		                   <td>
							   <select name="certificate_type" class="select" id="certificate_type" onchange="">
								   <option value="0">Development</option>
								   <option value="1">Production</option>
								</select>
								<br><i style="font-size:12px;">Select your iOS Developer Certificate Type. if you have uploaded distribution/production certificate, production build will be generated otherwise developement build will be generated.</ii>
							</td></tr>
							<tr><td>Provisioning Profile* : </td><td><input name="mofluid_provisioning_profile" id="mofluid_provisioning_profile" class="required-entry input-text" type="file">
							<br><i style="font-size:12px;">(Upload your provisioning profile make sure the provisioning profile will use correct bundle id and has all the device UDID attached in case of developement build. for more detail <a target="_blank" href="http://docs.mofluid.com/how-to%E2%80%8B-create%E2%80%8B-provisioning-profile">Click here</a>)</i></td></tr>
		 		           
			               <tr><td><input name="iphone_app_data_submit" value="Save Configuration" type="submit"></td></tr>
		                   </tbody></table></div></form>
     

<?php


}


function iphone_assest_configuration_callback(){
	?>

          <?php
           $iphone_app_obj_data = new Iphone_app_class();
           // Save android assests data
			if(isset($_POST['iphone_assests_icon_conf_submit'])){
				$iphone_app_obj_data->save_iphone_icons_configuration();
			}
			if(isset($_POST['iphone_assests_splash_conf_submit'])){
				$iphone_app_obj_data->save_iphone_spalsh_configuration();
				
			}
		  $iphone_data = $iphone_app_obj_data->get_iphone_configuration();
		  //var_dump($iphone_data);die;
          ?>  
          <form name="assets_detail_icons" action="#" method="post" enctype="multipart/form-data" onsubmit = "return save_ios_assets_icons();">
			                   
			                   <div class="fieldset box fieldset-wide">
			                       <table cellspacing="3">
                                       <tbody><tr>
                                           <td colspan="2">
                                               <h3 style="color:#EA7601;text-decoration:underline;">Icons</h3>
                                           </td>
                                       </tr>
                                       <tr style="padding-left:10px;">
                                           <td colspan="2">
                                               <h4 style="color:#EA7601;">App Icons</h4>
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_small <b>[Size : 29x29px]*</b> </td>
                                           <td>
                                               <input name="icon_small" id="icon_small" type="file">
                                               <img src="<?php echo $iphone_data->icon_small;?>" alt="<?php echo $iphone_data->icon_small;?>" width="25" height="25">
                                               <input id="hid_icon_small" name="hid_icon_small" value="icon_small.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_40 <b>[Size : 40x40px]*</b> </td>
                                           <td>
                                               <input name="icon_40" id="icon_40" type="file">
                                               <img src="<?php echo $iphone_data->icon_40;?>" alt="<?php echo $iphone_data->icon_40;?>" width="25" height="25">
                                               <input id="hid_icon_40" name="hid_icon_40" value="icon_40.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_50 <b>[Size : 50x50px]*</b> </td>
                                           <td><input name="icon_50" id="icon_50" type="file">
                                               <img src="<?php echo $iphone_data->icon_50;?>" alt="<?php echo $iphone_data->icon_50;?>" width="25" height="25">
                                               <input id="hid_icon_50" name="hid_icon_50" value="icon_50.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_57 <b>[Size : 57x57px]*</b> </td>
                                           <td>
                                               <input name="icon_57" id="icon_57" type="file">
                                               <img src="<?php echo $iphone_data->icon_57;?>" alt="<?php echo $iphone_data->icon_57;?>" width="25" height="25">
                                               <input id="hid_icon_57" name="hid_icon_57" value="icon.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_60 <b>[Size : 60x60px]*</b> </td>
                                           <td>
                                               <input name="icon_60" id="icon_60" type="file">
                                               <img src="<?php echo $iphone_data->icon_60;?>" alt="<?php echo $iphone_data->icon_60;?>" width="25" height="25">
                                               <input id="hid_icon_60" name="hid_icon_60" value="icon_60.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_72 <b>[Size : 72x72px]*</b> </td>
                                           <td>
                                               <input name="icon_72" id="icon_72" type="file">
                                               <img src="<?php echo $iphone_data->icon_72;?>" alt="<?php echo $iphone_data->icon_72;?>" width="25" height="25">
                                               <input id="hid_icon_72" name="hid_icon_72" value="icon_72.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_76 <b>[Size : 76x76px]*</b></td>
                                           <td>
                                               <input name="icon_76" id="icon_76" type="file">
                                               <img src="<?php echo $iphone_data->icon_76;?>" alt="<?php echo $iphone_data->icon_76;?>" width="25" height="25">
                                               <input id="hid_icon_76" name="hid_icon_76" value="icon_76.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td colspan="2">
                                               <h4 style="color:#EA7601">App Icons with 2x Resolution</h4>
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_small_2x <b>[Size : 58x58px]*</b> </td>
                                           <td>
                                               <input name="icon_small_2x" id="icon_small_2x" type="file">
                                               <img src="<?php echo $iphone_data->icon_small_2x;?>" alt="<?php echo $iphone_data->icon_small_2x;?>" width="25" height="25">
                                               <input id="hid_icon_small_2x" name="hid_icon_small_2x" value="icon_small_2x.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_40_2x <b>[Size : 80x80px]*</b> </td>
                                           <td>
                                               <input name="icon_40_2x" id="icon_40_2x" type="file">
                                               <img src="<?php echo $iphone_data->icon_40_2x;?>" alt="<?php echo $iphone_data->icon_40_2x;?>" width="25" height="25">
                                               <input id="hid_icon_40_2x" name="hid_icon_40_2x" value="icon_40_2x.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_50_2x <b>[Size : 100x100px]*</b> </td>
                                           <td>
                                               <input name="icon_50_2x" id="icon_50_2x" type="file">
                                               <img src="<?php echo $iphone_data->icon_50_2x;?>" alt="<?php echo $iphone_data->icon_50_2x;?>" width="25" height="25">
                                               <input id="hid_icon_50_2x" name="hid_icon_50_2x" value="icon_50_2x.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_57_2x <b>[Size : 144x114px]*</b> </td>
                                           <td><input name="icon_57_2x" id="icon_57_2x" type="file">
                                           <img src="<?php echo $iphone_data->icon_57_2x;?>" alt="<?php echo $iphone_data->icon_57_2x;?>" width="25" height="25">
                                           <input id="hid_icon_57_2x" name="hid_icon_57_2x" value="icon_2x.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_60_2x <b>[Size : 120x120px]*</b> </td>
                                           <td>
                                               <input name="icon_60_2x" id="icon_60_2x" type="file">
                                               <img src="<?php echo $iphone_data->icon_60_2x;?>" alt="<?php echo $iphone_data->icon_60_2x;?>" width="25" height="25">
                                               <input id="hid_icon_60_2x" name="hid_icon_60_2x" value="icon_60_2x.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_72_2x <b>[Size : 144x144px]*</b> </td>
                                           <td>
                                               <input name="icon_72_2x" id="icon_72_2x" type="file">
                                               <img src="<?php echo $iphone_data->icon_72_2x;?>" alt="<?php echo $iphone_data->icon_72_2x;?>" width="25" height="25">
                                               <input id="hid_icon_72_2x" name="hid_icon_72_2x" value="icon_72_2x.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>icon_76_2x <b>[Size : 152x152px]*</b> </td>
                                           <td>
                                               <input name="icon_76_2x" id="icon_76_2x" type="file">
                                               <img src="<?php echo $iphone_data->icon_76_2x;?>" alt="<?php echo $iphone_data->icon_76_2x;?>" width="25" height="25">
                                               <input id="hid_icon_76_2x" name="hid_icon_76_2x" value="icon_76_2x.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td colspan="2">
                                               <h4 style="color:#EA7601">iTunesArtwork Icons</h4>
                                           </td>
                                       </tr>
                                       <tr style="padding-top:1%">
                                           <td>iTunesArtwork <b>[Size : 512x512px]*</b> </td>
                                           <td>
                                               <input name="itunesartwork" id="itunesartwork" type="file">
                                               <img src="<?php echo $iphone_data->itunesartwork;?>" alt="<?php echo $iphone_data->itunesartwork;?>" width="25" height="25">
                                               <input id="hid_itunesartwork" name="hid_itunesartwork" value="" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>iTunesArtwork_2x <b>[Size : 1024x1024px]*</b> </td>
                                           <td>
                                               <input name="itunesartwork_2x" id="itunesartwork_2x" type="file">
                                               <img src="<?php echo $iphone_data->itunesartwork_2x;?>" alt="<?php echo $iphone_data->itunesartwork_2x;?>" width="25" height="25">
                                               <input id="hid_itunesartwork_2x" name="hid_itunesartwork_2x" value="" type="hidden">
                                           </td>
                                       </tr>
                                        <tr><td><input name="iphone_assests_icon_conf_submit" value="Upload icons" type="submit"></td></tr>
                                       
                                       </tbody></table>
                                       
                               </div></form>
                             
                             
                             <form name="assets_detail_splash" action="#" method="post" enctype="multipart/form-data" onsubmit = "return save_ios_assets_splash();">
			                       <table cellspacing="3">
                                       
                                       
                                       <tbody><tr style="border-top:1px solid #EA7601;text-decoration:underline;">
                                           <td colspan="2"> 
                                               <h3 style="color:#EA7601">Splash Screen</h3>
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>screen_iphone_portrait <b>[Size : 320x480px]*</b> </td>
                                           <td>
                                               <input name="screen_iphone_portrait" id="screen_iphone_portrait" type="file">
                                               <img src="<?php echo $iphone_data->screen_iphone_portrait;?>" alt="<?php echo $iphone_data->screen_iphone_portrait;?>" width="50" height="50">
                                               <input id="hid_screen_iphone_portrait" name="hid_screen_iphone_portrait" value="" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>screen_iphone_portrait_2x <b>[Size : 640x960px]*</b> </td>
                                           <td>
                                               <input name="screen_iphone_portrait_2x" id="screen_iphone_portrait_2x" type="file">
                                               <img src="<?php echo $iphone_data->screen_iphone_portrait_2x;?>" alt="<?php echo $iphone_data->screen_iphone_portrait_2x;?>" width="50" height="50">
                                               <input id="hid_screen_iphone_portrait_2x" name="hid_screen_iphone_portrait_2x" value="" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>screen_ipad_portrait <b>[Size : 768x1024px]*</b></td>
                                           <td>
                                               <input name="screen_ipad_portrait" id="screen_ipad_portrait" type="file">
                                               <img src="<?php echo $iphone_data->screen_ipad_portrait;?>" alt="<?php echo $iphone_data->screen_ipad_portrait;?>" width="50" height="50">
                                               <input id="hid_screen_ipad_portrait" name="hid_screen_ipad_portrait" value="" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>screen_ipad_portrait_2x <b>[Size : 1536x2048px]*</b></td>
                                           <td>
                                               <input name="screen_ipad_portrait_2x" id="screen_ipad_portrait_2x" type="file">
                                               <img src="<?php echo $iphone_data->screen_ipad_portrait_2x;?>" alt="<?php echo $iphone_data->screen_ipad_portrait_2x;?>" width="50" height="50">
                                               <input id="hid_screen_ipad_portrait_2x" name="hid_screen_ipad_portrait_2x" value="Default_Portrait_2x_ipad.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>screen_ipad_landscape <b>[Size : 1024x768px]*</b></td>
                                           <td>
                                               <input name="screen_ipad_landscape" id="screen_ipad_landscape" type="file">
                                               <img src="<?php echo $iphone_data->screen_ipad_landscape;?>" alt="<?php echo $iphone_data->screen_ipad_landscape;?>" width="50" height="50">
                                               <input id="hid_screen_ipad_landscape" name="hid_screen_ipad_landscape" value="Default_Landscape_ipad.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>screen_ipad_landscape_2x <b>[Size : 2048x1536px]*</b></td>
                                           <td>
                                               <input name="screen_ipad_landscape_2x" id="screen_ipad_landscape_2x" type="file">
                                               <img src="<?php echo $iphone_data->screen_ipad_landscape_2x;?>" alt="<?php echo $iphone_data->screen_ipad_landscape_2x;?>" width="50" height="50">
                                               <input id="hid_screen_ipad_landscape_2x" name="hid_screen_ipad_landscape_2x" value="Default_Landscape_2x_ipad.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr>
                                           <td>screen_iphone_default <b>[Size : 640x1136px]*</b></td>
                                           <td>
                                               <input name="screen_iphone_default" id="screen_iphone_default" type="file">
                                               <img src="<?php echo $iphone_data->screen_iphone_default;?>" alt="<?php echo $iphone_data->screen_iphone_default;?>" width="50" height="50">
                                               <input id="hid_screen_iphone_default" name="hid_screen_iphone_default" value="Default_568h_2x_iphone.png" type="hidden">
                                           </td>
                                       </tr>
                                       <tr><td><input name="iphone_assests_splash_conf_submit" value="Upload Splash screen" type="submit"></td></tr>
                                       
                                   </tbody></table>
                               </form>  
                               
                               
<?php
}

function iphone_theme_configuration_callback(){
?>
<script>
jQuery(document).ready(function(){
 document.getElementById("app_image_type").value="dark";
						document.getElementById("mofluid_theme").value="Elegant";
						document.getElementById("customfooter").value="0";
						 switch_image_type();
						 switch_footer_type();
});

</script>


          <?php
           $iphone_app_obj_data = new Iphone_app_class();
		  $iphone_data = $iphone_app_obj_data->get_iphone_configuration();
		  //var_dump($iphone_data);die;
          ?>  
			<form name="ios_theme_detail" action="#" method="post" enctype="multipart/form-data" onsubmit = "return saveiosThemeDetail()">
			               
			               <div class="fieldset box fieldset-wide"><table cellspacing="10">

<tbody><tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">Woofluid Theme</h4>
			                            </td>
			                        </tr>
                                    <tr>
                                        <td>
                                            Application's Theme
                                        </td>
                                        <td>
                                            <select name="mofluid_theme" id="mofluid_theme_i" onchange="switch_mofluid_theme();"><option value="Elegant">Elegant</option></select>
                                            <a href="https://mofluid.zendesk.com/hc/en-us/sections/200278341-Mofluid-Themes" target="_blank">Learn More About Woofluid Theme</a>
                                        </td>
                                    </tr>
                                    <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">Images &amp; Icons</h4>
			                            </td>
			                        </tr>
<tr><td>Application's Image Theme</td><td><select name="app_image_type" id="app_image_type_i" onchange="switch_image_type_i();"><option value="dark">Dark</option><option value="light">Light</option><option value="custom">Custom</option></select> (Small Images on App Which represents Home, Back, Cross, Delete, Search, Cart and Menu Icons)</td></tr>

<tr style="display: none;" id="app_img_row11"><td>Home (32x32px) </td><td><input name="app_img_home" id="app_img_home_i" type="file"><img src="<?php echo $iphone_data->app_img_home?>" alt="" width="30" height="30"><input name="hid_app_img_home" id="hid_app_img_home" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row21"><td>Back (32x32px) </td><td><input name="app_img_back" id="app_img_back_i" type="file"><img src="<?php echo $iphone_data->app_img_back?>" alt="" width="30" height="30"><input name="hid_app_img_back" id="hid_app_img_back" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row31"><td>Cross (32x32px) </td><td><input name="app_img_cross" id="app_img_cross_i" type="file"><img src="<?php echo $iphone_data->app_img_cross?>" alt="" width="30" height="30"><input name="hid_app_img_cross" id="hid_app_img_cross" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row41"><td>Cart (32x32px) </td><td><input name="app_img_cart" id="app_img_cart_i" type="file"><img src="<?php echo $iphone_data->app_img_cart?>" alt="" width="30" height="30"><input name="hid_app_img_cart" id="hid_app_img_cart" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row51"><td>Menu (32x32px) </td><td><input name="app_img_menu" id="app_img_menu_i" type="file"><img src="<?php echo $iphone_data->app_img_menu?>" alt="" width="30" height="30"><input name="hid_app_img_menu" id="hid_app_img_menu" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row61"><td>Delete (32x32px) </td><td><input name="app_img_del" id="app_img_del_i" type="file"><img src="<?php echo $iphone_data->app_img_del?>" alt="" width="30" height="30"><input name="hid_app_img_del" id="hid_app_img_del" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row71"><td>Search (32x32px) </td><td><input name="app_img_search" id="app_img_search_i" type="file"><img src="<?php echo $iphone_data->app_img_search?>" alt="" width="30" height="30"><input name="hid_app_img_search" id="hid_app_img_search" value="" type="hidden"></td></tr>
 <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">Logo &amp; Banner</h4>
			                            </td>
			                        </tr>
			               <tr><td>Application Logo* (Recommended 150x50px) : </td><td><input name="logo" id="logo_i" type="file"><img src="<?php echo $iphone_data->logo?>" alt="http://125.63.92.59/Mofluid/Production1.10.2/Default_Assets/iOS/logo_banner/logo.png" width="75" height="25"><input name="hid_logo" id="hid_logo" value="logo.png" type="hidden"></td></tr>
			               <tr><td>Application Banner* (Recommended 420x260px) : </td><td><input name="banner" id="banner_i" type="file"><img src="<?php echo $iphone_data->banner?>" alt="http://125.63.92.59/Mofluid/Production1.10.2/Default_Assets/iOS/logo_banner/banner.png" width="105" height="65"><input name="hid_banner" id="hid_banner" value="banner.png" type="hidden"></td></tr>
                            <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">Colors</h4>
			                            </td>
			                        </tr>
                           <tr><td>Application Background* : </td><td><input style="background-image: none; background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);" autocomplete="off" id="theme_background_i" name="theme_background" class="color input-text" value="<?php echo $iphone_data->theme_background?>" type="text"></td></tr>
                           <tr><td>Default Text* : </td><td><input style="background-image: none; background-color: rgb(204, 0, 0); color: rgb(255, 255, 255);" autocomplete="off" name="theme_text" id="theme_text_i" class="color input-text" value="<?php echo $iphone_data->theme_text?>" type="text"></td></tr>
                           <tr><td>Header* : </td><td><input style="background-image: none; background-color: rgb(29, 79, 23); color: rgb(255, 255, 255);" autocomplete="off" name="theme_header" id="theme_header_i" class="color input-text" value="<?php echo $iphone_data->theme_header?>" type="text"></td></tr>
                           <tr><td>Title Text* : </td><td><input style="background-image: none; background-color: rgb(188, 255, 3); color: rgb(0, 0, 0);" autocomplete="off" name="theme_title" id="theme_title_i" class="color input-text" value="<?php echo $iphone_data->theme_title?>" type="text"></td></tr>
                           <tr><td>Button* : </td><td><input style="background-image: none; background-color: rgb(255, 79, 43); color: rgb(255, 255, 255);" autocomplete="off" name="theme_button" id="theme_button_i" class="color input-text" value="<?php echo $iphone_data->theme_button?>" type="text"></td></tr>
                           <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">HTML Footer</h4>
			                            </td>
			                        </tr>
			                        <tr>
                                        <td>Status* : </td>
                                        <td>
                                            <select id="customfooter_i" name="customfooter" onchange="switch_footer_type_i();">
                                                <option value="0">Not Required</option>
                                                <option value="1">Required</option>
                                            </select>
                                        </td>
                                    </tr>
                                     <tr style="display: none;" id="customfooter_row_i">
                                        <td>HTML Code* : </td>
                                        <td>
                                            <textarea name="customfooter_code" id="customfooter_code_i" class="input-text required-entry" style="width: 60%;height: 60px;"><?php echo $iphone_data->customfooter_code?></textarea>
                                        </td>
                                    </tr>
                           <tr><td><input name="iphone_theme_conf_submit" value="Save Configuration" type="submit"></td></tr>
                           </tbody></table></div></form>
<?php

	if(isset($_POST['iphone_theme_conf_submit'])){
		$iphone_app_obj = new iphone_app_class();		
		$iphone_app_obj->save_iphone_theme_conf();
	}	
	
}

function iphone_push_configuration_callback(){
	$iphone_app_obj = new iphone_app_class();
	if(isset($_POST['iphone_push_conf_submit'])){		
		$iphone_app_obj->save_iphone_push_conf();
	}
	$android_app_obj = new Android_app_class();
	$pushConfig = $android_app_obj->get_android_push_conf();
?>
	<form name="iphone_push_conf_details" action="" method="post" enctype="multipart/form-data">
		<div class="fieldset box fieldset-wide">
			<table cellspacing="10">
				<tbody>
					<tr><td colspan="2"><h4 style="color:#EA7601">Apple Push Notification</h4></td></tr>
					<tr><td>Upload Certificate with Private Key (.pem file) : </td><td><input name="apn_pem_file" id="apn_pem_file" class="required-entry input-text" value="" type="file"><?php echo $pushConfig->apn_certificate?></td></tr>
					<tr><td>Passphrase : </td><td><input name="apn_passphrase" id="apn_passphrase" class="required-entry input-text" value="<?php echo $pushConfig->apn_passphrase?>" type="text"></td></tr>
					<tr><td>Push Notification Mode : </td>
						<td>
							<select name="apn_mode" class="select" id="apn_mode" onchange="">
								<option value="1" <?php echo $pushConfig->apn_mode==1?"selected":""?>>Production</option>
								<option value="0" <?php echo $pushConfig->apn_mode==0?"selected":""?>>Development</option>
							</select>
						</td></tr>
					<tr><td><input name="form_name" value="iphone_push_conf_details" type="hidden"></td></tr>
					<tr><td><input name="iphone_push_conf_submit" value="Save Configuration" type="submit"></td></tr>
				</tbody>
			</table>
		</div>
	</form>
	
	<form name="iphone_send_push" action="" method="post">
		<div class="fieldset box fieldset-wide">
			<table cellspacing="10">
				<tbody>
					<tr><td colspan="2"><h4 style="color:#EA7601">Send Push Notification</h4></td></tr>
					<tr><td>Push Notification Message* :</td><td><textarea rows="10" cols="100" style="resize:none;" name="push_ios_message" id="push_ios_message" maxlength="150" value=""></textarea></td></tr>
					<tr><td><input name="form_name" value="iphone_send_push" type="hidden"></td></tr>
					<tr><td><input name="iphone_send_push_submit" value="Send Push Notification" type="submit"></td></tr>
				</tbody>
			</table>
		</div>
	</form>
<?php
}
function iphone_preview_and_download_configuration_callback(){
$iphone_app_obj = new iphone_app_class();
	if(isset($_POST['iphone_gen_app_submit'])){		
		$iphone_app_obj->generate_ios_app();
	}
?>
	<form name="iphone_generate_app" action="" method="post">
		<div class="fieldset box fieldset-wide">
			<table cellspacing="10">
				<tbody>
					<tr><td><input name="form_name" value="iphone_generate_app" type="hidden"></td></tr>
					<tr><td><input name="iphone_gen_app_submit" value="Generate App" type="submit"></td></tr>
				</tbody>
			</table>
		</div>
	</form>
<?php	
}
?>
