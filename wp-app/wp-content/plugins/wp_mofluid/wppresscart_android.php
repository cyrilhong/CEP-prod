<?php
/**
 * Plugin Name: Wp Presscart Android
  * Description: Wp Presscart Android
 * Version: 1.0
 * Author: Ebizon
 * Author URI: Ebizontech.com
 **/
  require('android_classes.php');


// add menu tab in wppresscart menu
add_action( 'init', 'wp_presscart_android_menu' );
function wp_presscart_android_menu(){
	//var_dump(get_option('wppresscart_configuration_menu'));die;
	if(get_option('wppresscart_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Android",get_option('wppresscart_configuration_menu'))){
				$tmp = get_option('wppresscart_configuration_menu');
				$tmp['Android'] = 'android_platform_configuration_callback';
				update_option('wppresscart_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}
	
	if(get_option('wppresscart_assest_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Android",get_option('wppresscart_assest_configuration_menu'))){
				$tmp = get_option('wppresscart_assest_configuration_menu');
				$tmp['Android'] = 'android_assest_configuration_callback';
				update_option('wppresscart_assest_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_assest_configuration_menu', array('Android' => 'android_assest_configuration_callback'));
	}
	if(get_option('wppresscart_theme_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Android",get_option('wppresscart_theme_configuration_menu'))){
				$tmp = get_option('wppresscart_theme_configuration_menu');
				$tmp['Android'] = 'android_theme_configuration_callback';
				update_option('wppresscart_theme_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_theme_configuration_menu', array('Android' => 'android_theme_configuration_callback'));
	}
	
	if(get_option('wppresscart_push_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Android",get_option('wppresscart_push_configuration_menu'))){
				$tmp = get_option('wppresscart_push_configuration_menu');
				$tmp['Android'] = 'android_push_configuration_callback';
				update_option('wppresscart_push_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_push_configuration_menu', array('Android' => 'android_push_configuration_callback'));
	}
	
	if(get_option('wppresscart_preview_and_download_configuration_menu')){
			//var_dump(get_option('wppresscart_configuration_menu'));
			if(!array_key_exists("Android",get_option('wppresscart_preview_and_download_configuration_menu'))){
				$tmp = get_option('wppresscart_preview_and_download_configuration_menu');
				$tmp['Android'] = 'android_preview_and_download_configuration_callback';
				update_option('wppresscart_preview_and_download_configuration_menu', $tmp);
			}
			//update_option('wppresscart_configuration_menu', array('Android' => 'android_platform_configuration_callback'));
	}else{
		update_option('wppresscart_preview_and_download_configuration_menu', array('Android' => 'android_preview_and_download_configuration_callback'));
	}
}
/// end here///

add_action( 'init', 'wppresscart_android_scripts' );
function wppresscart_android_scripts() {
	wp_enqueue_style( 'wp_presscart_style', plugins_url().'/wp_mofluid/css/android_presscart.css' );
	wp_enqueue_script( 'wp_presscart_js', plugins_url().'/wp_mofluid/js/android_presscart.js', array(), '1.0.0', true );
	wp_enqueue_script( 'wp_presscart_js_color', plugins_url().'/wp_mofluid/js/jscolor.js', array(), '1.0.0', true );
}


function android_platform_configuration_callback(){
	$android_app_obj_data = new Android_app_class();
	// Save android app data
	if(isset($_POST['android_app_data_submit'])){
		$android_app_obj_data->save_android_configuration($_POST);
	}
	$and_data = $android_app_obj_data->get_android_configuration();
	//echo '<pre>'; print_r($and_data);die;
	$o = json_decode($and_data->key_data);
	//var_dump($o);die;
	
	global $wpdb;
	$mofluid_user_results = $wpdb->get_results( 'SELECT * FROM mofluidadmin_details ORDER BY mofluidadmin_id DESC', OBJECT );
	?>   
	<form name="app_detail" action="#" method="post" enctype="multipart/form-data" onsubmit = "return saveAndroidAppDetail();">
		<div class="fieldset box fieldset-wide">
			<table cellspacing="10">
				<tbody>
					<tr><td colspan="2"><h4 style="color:#EA7601">Woofluid Details</h4></td></tr>
					<tr><td>Woofluid User Id* : </td><td><input name="mofluid_id" id="mofluid_id" class="required-entry input-text" value="<?php echo isset($mofluid_user_results[0]->mofluid_id)?$mofluid_user_results[0]->mofluid_id:""?>" type="text"> (Email id of your user account at woofluid.com)</td></tr>
					<tr><td>Woofluid Secret Key* : </td><td><input name="mofluid_key" id="mofluid_key" class="required-entry input-text" value="<?php echo isset($mofluid_user_results[0]->mofluid_key)?$mofluid_user_results[0]->mofluid_key:""?>" type="text"> (Password of your user account at woofluid.com)</td></tr>
					<tr><td colspan="2"><h4 style="color:#EA7601">Application Details</h4></td></tr>
					<tr><td>Application Name* : </td><td><input name="name" class="required-entry input-text" value="<?php echo $and_data->app_name;?>" type="text"></td></tr>
					<tr><td>Bundle ID* : </td><td><input name="bundle_id" class="input-text" value="<?php echo $and_data->bundle_id;?>" type="text"></td></tr>
					<tr><td>Version* : </td><td><input name="version" class=" required-entry validate-number validate-zero-or-greater validate-number-range number-range-0-99999999.9999 input-text required-entry" value="<?php echo $and_data->version;?>" type="text"></td></tr>
					<tr><td>Default Store Id for App* : </td><td><input name="store" class=" required-entry validate-number validate-zero-or-greater validate-number-range number-range-0-99999999.9999 input-text required-entry" value="<?php echo $and_data->default_store;?>" type="text"></td></tr>
					<tr><td><h4 style="color:#EA7601">Default Currency : </h4></td>
						<td>
							<select name="currency_type" class="select" id="currency_type" onchange="">
								<option value="INR" <?php echo ($and_data->currency_type=="INR"?"selected":"")?>>INR</option>
								<option value="USD" <?php echo ($and_data->currency_type=="USD"?"selected":"")?>>USD</option>
							</select> should be same as in your desktop website.
						</td></tr>
					<tr><td colspan="2"><h4 style="color:#EA7601">Key Details</h4></td></tr>
					<tr><td>Release Key Type : </td><td><select name="key_type" class="select" id="key_type" onchange="switch_keytype();"><option value="0">Apply for new private key</option><option value="1">I already have a private key </option></select></td></tr>
					<tr style="" id="key_row1"><td>Key Store's Password* : </td><td><input name="key_store_pwd" class="input-text" value="<?php echo $and_data->keystore_pswd;?>" type="password"></td></tr>
					<tr style="" id="key_row2"><td>Private Key's Password* : </td><td><input name="key_pwd" class="input-text" value="<?php echo $and_data->privatekey_pswd;?>" type="password"></td></tr>
					<tr style="" id="key_row3"><td>Validity (in days)* : </td><td><input name="key_validity" class="input-text" value="<?php echo $and_data->key_validity;?>" type="text"></td></tr>
					<tr style="" id="key_row4"><td>Common Name* : </td><td><input name="key_common_name" class="input-text" value="<?php echo $o->key_common_name;?>" type="text"></td></tr>
					<tr style="" id="key_row5"><td>Organization Name* : </td><td><input name="key_org" class="input-text" value="<?php echo $o->key_org;?>" type="text"></td></tr>
					<tr style="" id="key_row6"><td>Organization Unit* : </td><td><input name="key_org_unit" class="input-text" value="<?php echo $o->key_org_unit;?>" type="text"></td></tr>
					<tr style="" id="key_row7"><td>City or Locality* : </td><td><input name="key_city" class="input-text" value="<?php echo $o->key_city;?>" type="text"></td></tr>
					<tr style="" id="key_row8"><td>State or Province Name* : </td><td><input name="key_state" class="input-text" value="<?php echo $o->key_state;?>" type="text"></td></tr>
					<tr style="" id="key_row9"><td>Country Code* : </td><td><input name="key_country" class="input-text" value="<?php echo $o->key_country;?>" maxlength="2" type="text"></td></tr>
					<tr style="display: none;" id="key_row10"><td>Upload .keystore file* : </td><td><input name="release_key" id="release_key" type="file"><input value="" name="release_key2" id="release_key2" type="hidden"></td></tr>
					<tr style="display: none;" id="key_row11"><td>Key Store' Password* : </td><td><input name="keystore_pswd" id="keystore_pswd" class="input-text" value="" type="password"></td></tr>
					<tr style="display: none;" id="key_row12"><td>Private Key's Password* : </td><td><input name="privatekey_pswd" id="privatekey_pswd" class="input-text" value="" type="password"></td></tr>
					<tr><td><input name="form_name" value="appdetails" type="hidden"></td></tr>
					<tr><td><input name="android_app_data_submit" value="Save Configuration" type="submit"></td></tr>
				</tbody>
			</table>
		</div>
	</form>


<?php


}


function android_assest_configuration_callback(){
	?>
	
          <?php
          $android_app_obj_data = new Android_app_class();
		  // Save android assests data
		  if(isset($_POST['android_assests_conf_submit'])){
			  $android_app_obj_data->save_android_assests();
		  }
		  $android_data = $android_app_obj_data->get_android_configuration();
          ?>
          <form name="assets_detail" action="#" method="post" enctype="multipart/form-data" onsubmit = "return saveAssetsDetail()">
			               
			               <div class="fieldset box fieldset-wide"><table cellspacing="5">
                           <tbody><tr><td colspan="2"><h3 style="color:#EA7601">Icons</h3></td></tr>
                           <tr><td>drawable <b>[Size : 96x96]*</b> </td><td><input name="ico_drawable" id="ico_drawable" type="file"><img src="<?php echo $android_data->ico_drawable;?>" alt="<?php echo $android_data->ico_drawable;?>" width="25" height="25"><input id="hid_ico_drawable" name="hid_ico_drawable" value="drawable.png" type="hidden"></td></tr>
                           <tr><td>hdpi <b>[Size : 72x72]*</b> </td><td><input name="ico_hdpi" id="ico_hdpi" type="file"><img src="<?php echo $android_data->ico_hdpi;?>" alt="<?php echo $android_data->ico_hdpi;?>" width="25" height="25"><input id="hid_ico_hdpi" name="hid_ico_hdpi" value="drawable_hdpi.png" type="hidden"></td></tr>
                           <tr><td>ldpi <b>[Size : 36x36]*</b></td><td><input name="ico_ldpi" id="ico_ldpi" type="file"><img src="<?php echo $android_data->ico_ldpi;?>" alt="<?php echo $android_data->ico_ldpi;?>" width="25" height="25"><input id="hid_ico_ldpi" name="hid_ico_ldpi" value="drawable_ldpi.png" type="hidden"></td></tr>
                           <tr><td>mdpi <b>[Size : 48x48]*</b></td><td><input name="ico_mdpi" id="ico_mdpi" type="file"><img src="<?php echo $android_data->ico_mdpi;?>" alt="<?php echo $android_data->ico_mdpi;?>" width="25" height="25"><input id="hid_ico_mdpi" name="hid_ico_mdpi" value="drawable_mdpi.png" type="hidden"></td></tr>
                           <tr><td>xhdpi <b>[Size : 96x96]*</b> </td><td><input name="ico_xhdpi" id="ico_xhdpi" type="file"><img src="<?php echo $android_data->ico_xhdpi;?>" alt="<?php echo $android_data->ico_xhdpi;?>" width="25" height="25"><input id="hid_ico_xhdpi" name="hid_ico_xhdpi" value="drawable_xhdpi.png" type="hidden"></td></tr>
                           <tr><td colspan="2"><h3 style="color:#EA7601">Splash Screens</h3></td></tr>
                           <tr><td colspan="2"><h4 style="color:#EA7601">Portrait</h4></td></tr>
                           <tr><td>drawable_port_hdpi <b>[Size : 480x800 &amp; Orientation : Portrait]*</b></td><td><input name="drawable_port_hdpi" id="drawable_port_hdpi" type="file"><img src="<?php echo $android_data->drawable_port_hdpi;?>" alt="<?php echo $android_data->drawable_port_hdpi;?>" width="50" height="50"><input id="hid_drawable_port_hdpi" name="hid_drawable_port_hdpi" value="drawable_port_hdpi.png" type="hidden"></td></tr>
                           <tr><td>drawable_port_ldpi <b>[Size : 200x320 &amp; Orientation : Portrait]*</b></td><td><input name="drawable_port_ldpi" id="drawable_port_ldpi" type="file"><img src="<?php echo $android_data->drawable_port_ldpi;?>" alt="<?php echo $android_data->drawable_port_ldpi;?>" width="50" height="50"><input id="hid_drawable_port_ldpi" name="hid_drawable_port_ldpi" value="drawable_port_ldpi.png" type="hidden"></td></tr>
                           <tr><td>drawable_port_mdpi <b>[Size : 320x480 &amp; Orientation : Portrait]*</b></td><td><input name="drawable_port_mdpi" id="drawable_port_mdpi" type="file"><img src="<?php echo $android_data->drawable_port_mdpi;?>" alt="<?php echo $android_data->drawable_port_mdpi;?>" width="50" height="50"><input id="hid_drawable_port_mdpi" name="hid_drawable_port_mdpi" value="drawable_port_mdpi.png" type="hidden"></td></tr>
                           <tr><td>drawable_port_xhdpi <b>[Size : 720x1280 &amp; Orientation : Portrait]*</b></td><td><input name="drawable_port_xhdpi" id="drawable_port_xhdpi" type="file"><img src="<?php echo $android_data->drawable_port_xhdpi;?>" alt="<?php echo $android_data->drawable_port_xhdpi;?>" width="50" height="50"><input id="hid_drawable_port_xhdpi" name="hid_drawable_port_xhdpi" value="drawable_port_xhdpi.png" type="hidden"></td></tr>
                           <tr><td colspan="2"><h4 style="color:#EA7601">Landscape</h4></td></tr>
                           <tr><td>drawable_land_hdpi <b>[Size : 800x480 &amp; Orientation : Landscape]*</b></td><td><input name="drawable_land_hdpi" id="drawable_land_hdpi" type="file"><img src="<?php echo $android_data->drawable_land_hdpi;?>" alt="<?php echo $android_data->drawable_land_hdpi;?>" width="50" height="50"><input id="hid_drawable_land_hdpi" name="hid_drawable_land_hdpi" value="drawable_land_hdpi.png" type="hidden"></td></tr>
                           <tr><td>drawable_land_ldpi <b>[Size : 320x200 &amp; Orientation : Landscape]*</b></td><td><input name="drawable_land_ldpi" id="drawable_land_ldpi" type="file"><img src="<?php echo $android_data->drawable_land_ldpi;?>" alt="<?php echo $android_data->drawable_land_ldpi;?>" width="50" height="50"><input id="hid_drawable_land_ldpi" name="hid_drawable_land_ldpi" value="drawable_land_ldpi.png" type="hidden"></td></tr>
                           <tr><td>drawable_land_mdpi <b>[Size : 480x320 &amp; Orientation : Landscape]*</b></td><td><input name="drawable_land_mdpi" id="drawable_land_mdpi" type="file"><img src="<?php echo $android_data->drawable_land_mdpi;?>" alt="<?php echo $android_data->drawable_land_mdpi;?>" width="50" height="50"><input id="hid_drawable_land_mdpi" name="hid_drawable_land_mdpi" value="drawable_land_mdpi.png" type="hidden"></td></tr>
                           <tr><td>drawable_land_xhdpi <b>[Size : 1280x720 &amp; Orientation : Landscape]*</b></td><td><input name="drawable_land_xhdpi" id="drawable_land_xhdpi" type="file"><img src="<?php echo $android_data->drawable_land_xhdpi;?>" alt="<?php echo $android_data->drawable_land_xhdpi;?>" width="50" height="50"><input id="hid_drawable_land_xhdpi" name="hid_drawable_land_xhdpi" value="drawable_land_xhdpi.png" type="hidden"></td></tr>
                           
                           <tr><td><input name="form_name" value="assetsdetails" type="hidden"></td></tr>
                           <tr><td><input name="android_assests_conf_submit" value="Save Configuration" type="submit"></td></tr>
                           </tbody></table></div></form>
         
<?php

}


function android_theme_configuration_callback(){
	
	
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
           $android_app_obj_data = new Android_app_class();
          if(isset($_POST['android_theme_conf_submit'])){
			  $android_app_obj_data->save_android_theme_conf();
			}
		  $android_data = $android_app_obj_data->get_android_configuration();
          ?>
			<form name="ios_theme_detail" action="#" method="post" enctype="multipart/form-data" onsubmit = "return saveandroidThemeDetail()">
			               
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
                                            <select name="mofluid_theme" id="mofluid_theme" onchange="switch_mofluid_theme();"><option value="Elegant">Elegant</option></select>
                                            <a href="https://mofluid.zendesk.com/hc/en-us/sections/200278341-Mofluid-Themes" target="_blank">Learn More About Woofluid Theme</a>
                                        </td>
                                    </tr>
                                    <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">Images &amp; Icons</h4>
			                            </td>
			                        </tr>
<tr><td>Application's Image Theme</td><td><select name="app_image_type" id="app_image_type" onchange="switch_image_type();"><option value="dark">Dark</option><option value="light">Light</option><option value="custom">Custom</option></select> (Small Images on App Which represents Home, Back, Cross, Delete, Search, Cart and Menu Icons)</td></tr>

<tr style="display: none;" id="app_img_row1"><td>Home (32x32px) </td><td><input name="app_img_home" id="app_img_home" type="file"><img src="<?php echo $android_data->app_img_home;?>" alt="" width="30" height="30"><input name="hid_app_img_home" id="hid_app_img_home" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row2"><td>Back (32x32px) </td><td><input name="app_img_back" id="app_img_back" type="file"><img src="<?php echo $android_data->app_img_back;?>" alt="" width="30" height="30"><input name="hid_app_img_back" id="hid_app_img_back" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row3"><td>Cross (32x32px) </td><td><input name="app_img_cross" id="app_img_cross" type="file"><img src="<?php echo $android_data->app_img_cross;?>" alt="" width="30" height="30"><input name="hid_app_img_cross" id="hid_app_img_cross" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row4"><td>Cart (32x32px) </td><td><input name="app_img_cart" id="app_img_cart" type="file"><img src="<?php echo $android_data->app_img_cart;?>" alt="" width="30" height="30"><input name="hid_app_img_cart" id="hid_app_img_cart" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row5"><td>Menu (32x32px) </td><td><input name="app_img_menu" id="app_img_menu" type="file"><img src="<?php echo $android_data->app_img_menu;?>" alt="" width="30" height="30"><input name="hid_app_img_menu" id="hid_app_img_menu" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row6"><td>Delete (32x32px) </td><td><input name="app_img_del" id="app_img_del" type="file"><img src="<?php echo $android_data->app_img_del;?>" alt="" width="30" height="30"><input name="hid_app_img_del" id="hid_app_img_del" value="" type="hidden"></td></tr>

<tr style="display: none;" id="app_img_row7"><td>Search (32x32px) </td><td><input name="app_img_search" id="app_img_search" type="file"><img src="<?php echo $android_data->app_img_search;?>" alt="" width="30" height="30"><input name="hid_app_img_search" id="hid_app_img_search" value="" type="hidden"></td></tr>
 <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">Logo &amp; Banner</h4>
			                            </td>
			                        </tr>
			               <tr><td>Application Logo* (Recommended 150x50px) : </td><td><input name="logo" id="logo" type="file"><img src="<?php echo $android_data->logo;?>" alt="<?php echo $android_data->logo;?>" width="75" height="25"><input name="hid_logo" id="hid_logo" value="logo.png" type="hidden"></td></tr>
			               <tr><td>Application Banner* (Recommended 420x260px) : </td><td><input name="banner" id="banner" type="file"><img src="<?php echo $android_data->banner;?>" alt="<img src="<?php echo $android_data->banner;?>" width="105" height="65"><input name="hid_banner" id="hid_banner" value="banner.png" type="hidden"></td></tr>
                            <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">Colors</h4>
			                            </td>
			                        </tr>
                           <tr><td>Application Background* : </td><td><input style="background-image: none; background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);" autocomplete="off" id="theme_background" name="theme_background" class="color input-text" value="<?php echo $android_data->theme_background;?>" type="text"></td></tr>
                           <tr><td>Default Text* : </td><td><input style="background-image: none; background-color: rgb(204, 0, 0); color: rgb(255, 255, 255);" autocomplete="off" name="theme_text" id="theme_text" class="color input-text" value="<?php echo $android_data->theme_text;?>" type="text"></td></tr>
                           <tr><td>Header* : </td><td><input style="background-image: none; background-color: rgb(29, 79, 23); color: rgb(255, 255, 255);" autocomplete="off" name="theme_header" id="theme_header" class="color input-text" value="<?php echo $android_data->theme_header;?>" type="text"></td></tr>
                           <tr><td>Title Text* : </td><td><input style="background-image: none; background-color: rgb(188, 255, 3); color: rgb(0, 0, 0);" autocomplete="off" name="theme_title" id="theme_title" class="color input-text" value="<?php echo $android_data->theme_title;?>" type="text"></td></tr>
                           <tr><td>Button* : </td><td><input style="background-image: none; background-color: rgb(255, 79, 43); color: rgb(255, 255, 255);" autocomplete="off" name="theme_button" id="theme_button" class="color input-text" value="<?php echo $android_data->theme_button;?>" type="text"></td></tr>
                           <tr>
			                            <td colspan="2">
			                                <h4 style="color:#EA7601">HTML Footer</h4>
			                            </td>
			                        </tr>
			                        <tr>
                                        <td>Status* : </td>
                                        <td>
                                            <select id="customfooter" name="customfooter" onchange="switch_footer_type();">
                                                <option value="0">Not Required</option>
                                                <option value="1">Required</option>
                                            </select>
                                        </td>
                                    </tr>
                                     <tr style="display: none;" id="customfooter_row">
                                        <td>HTML Code* : </td>
                                        <td>
                                            <textarea name="customfooter_code" id="customfooter_code" class="input-text required-entry" style="width: 60%;height: 60px;"><?php echo $android_data->customfooter_code;?></textarea>
                                        </td>
                                    </tr>
                           <tr><td><input name="android_theme_conf_submit" value="Save Configuration" type="submit"></td></tr>
                           </tbody></table></div></form>
                   
<?php
}


function android_push_configuration_callback(){
	$android_app_obj = new Android_app_class();
	if(isset($_POST['android_push_conf_submit'])){		
		$android_app_obj->save_android_push_conf();
	}
	$pushConfig = $android_app_obj->get_android_push_conf();
?>
	<form name="android_push_conf_details" action="" method="post">
		<div class="fieldset box fieldset-wide">
			<table cellspacing="10">
				<tbody>
					<tr><td colspan="2"><h4 style="color:#EA7601">Google Cloud Messaging Account</h4></td></tr>
					<tr><td>GCM ID* : </td><td><input name="gcm_id" id="gcm_id" class="required-entry input-text" value="<?php echo $pushConfig->gcm_id?>" type="text"></td></tr>
					<tr><td>GCM API Server Key* : </td><td><input name="gcm_key" id="gcm_key" class="required-entry input-text" value="<?php echo $pushConfig->gcm_key?>" type="text"></td></tr>
					<tr><td>Push Notification Mode : </td>
						<td>
							<select name="gcm_mode" class="select" id="gcm_mode" onchange="">
								<option value="1" <?php echo $pushConfig->gcm_mode==1?"selected":""?>>Production</option>
								<option value="0" <?php echo $pushConfig->gcm_mode==0?"selected":""?>>Development</option>
							</select>
						</td></tr>
					<tr><td><input name="form_name" value="android_push_conf_details" type="hidden"></td></tr>
					<tr><td><input name="android_push_conf_submit" value="Save Configuration" type="submit"></td></tr>
				</tbody>
			</table>
			<p>To Create GCM Project and get API Server key Open <a href="https://cloud.google.com/console/project" target="_blank">Google Developers Console</a>.</p>
			<p>For More detail about Google Cloud Messaging Account <a href="https://support.google.com/googleplay/android-developer/answer/2663268?hl=en" target="_blank">Click here</a></p>
		</div>
	</form>
	
	<form name="android_send_push" action="" method="post">
		<div class="fieldset box fieldset-wide">
			<table cellspacing="10">
				<tbody>
					<tr><td colspan="2"><h4 style="color:#EA7601">Send Push Notification</h4></td></tr>
					<tr><td>Push Notification Message* :</td><td><textarea rows="10" cols="100" style="resize:none;" name="push_android_message" id="push_android_message" maxlength="150" value=""></textarea></td></tr>
					<tr><td><input name="form_name" value="android_send_push" type="hidden"></td></tr>
					<tr><td><input name="android_send_push_submit" value="Send Push Notification" type="submit"></td></tr>
				</tbody>
			</table>
		</div>
	</form>
<?php
}
function android_preview_and_download_configuration_callback(){
	$android_app_obj = new Android_app_class();
	if(isset($_POST['android_gen_app_submit'])){		
		$android_app_obj->generate_android_app();
	}
?>
	<form name="android_generate_app" action="" method="post">
		<div class="fieldset box fieldset-wide">
			<table cellspacing="10">
				<tbody>
					<tr><td><input name="form_name" value="android_generate_app" type="hidden"></td></tr>
					<tr><td><input name="android_gen_app_submit" value="Generate App" type="submit"></td></tr>
				</tbody>
			</table>
		</div>
	</form>
<?php
}
?>
