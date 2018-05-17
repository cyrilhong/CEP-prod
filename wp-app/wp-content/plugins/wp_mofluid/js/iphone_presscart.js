



								function isInvalidEmail(email) {
                                     var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                                     if (!filter.test(email)) {
                                            return true;
                                     }
                                     else {
                                        return false;
									 }
                                }
		                        function saveiPhoneAppDetail() {
								   var mofluid_id = document.iosapp_detail.mofluid_id.value;
								   var mofluid_key = document.iosapp_detail.mofluid_key.value;
								   var name = document.iosapp_detail.name.value;
								   var bundle_id = document.iosapp_detail.bundle_id.value;	
								   var version = document.iosapp_detail.version.value;
								   var store = document.iosapp_detail.store.value;
								   var device_udid = document.iosapp_detail.device_udid.value;	
								   var version_filter = /^\d+(\.\d+){0,2}$/i;
								   if(name==""||bundle_id==""||version==""||store==""||mofluid_id==""||mofluid_key==""||device_udid=="") {
									    alert("All fields are mandatory.");
							            return false; 
							       }
							       if(mofluid_id!="" && isInvalidEmail(mofluid_id)) {
									   alert("Please enter the valid Mofluid ID.");
							            return false;
								   }
								   else if(hasWhiteSpace(bundle_id)) {

 alert("Special Symbol other than . are not allowed in Bundle Id.");
return false;
}
							         else if(!name.match(/^[A-Za-z]+$/)) {
                                                                    
       alert("Special symbol , spaces and numbers are not allowed in application name.");
                                                                    return false;
                                                                   }  
							       else if(!version_filter.test(version)){
									   alert("Version number should be positive integer in X or X.X or X.X.X format.");
									   return false;
								   }
							       else if(!isInt(store)) {
							             alert("Store Id should be integer.");
							             return false;
							       } 
							       else {
							            document.iosapp_detail.submit();
							            return true;
								   }
							    }
							    function isInt(x) {
                                        return (!isNaN(x) && parseInt(x) == x)
                                 }
							 function hasWhiteSpace(spacefield) {
     return !(/^[a-z0-9.]+$/i.test(spacefield));
  }


function save_ios_assets_icons() {
                               saveiosiconAssetsDetail();
                           }
                           function save_ios_assets_splash() {
                               saveiossplashAssetsDetail();
                           }
                           function saveiosiconAssetsDetail() {
                                      var icon_small = document.getElementById("icon_small");
                                      var icon_40 = document.getElementById("icon_40");
                                      var icon_50 = document.getElementById("icon_50");
                                      var icon_57 = document.getElementById("icon_57");
                                      var icon_60 = document.getElementById("icon_60");
                                      var icon_72 = document.getElementById("icon_72");
                                      var icon_76 = document.getElementById("icon_76");
                                      var icon_small_2x = document.getElementById("icon_small_2x");
                                      var icon_40_2x = document.getElementById("icon_40_2x");
                                      var icon_50_2x = document.getElementById("icon_50_2x");
                                      var icon_57_2x = document.getElementById("icon_57_2x");
                                      var icon_60_2x = document.getElementById("icon_60_2x");
                                      var icon_72_2x = document.getElementById("icon_72_2x");
                                      var icon_76_2x = document.getElementById("icon_76_2x");
                                      var itunesartwork = document.getElementById("itunesartwork");
                                      var itunesartwork_2x = document.getElementById("itunesartwork_2x");
                                       var icon_small_ext = icon_small.value.substring(icon_small.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_40_ext = icon_40.value.substring(icon_40.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_50_ext = icon_50.value.substring(icon_50.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_57_ext = icon_57.value.substring(icon_57.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_60_ext = icon_60.value.substring(icon_60.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_72_ext = icon_72.value.substring(icon_72.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_76_ext = icon_76.value.substring(icon_76.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_small_2x_ext = icon_small_2x.value.substring(icon_small_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_40_2x_ext = icon_40_2x.value.substring(icon_40_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_50_2x_ext = icon_50_2x.value.substring(icon_50_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_57_2x_ext = icon_57_2x.value.substring(icon_57_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_60_2x_ext = icon_60_2x.value.substring(icon_60_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_72_2x_ext = icon_72_2x.value.substring(icon_72_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var icon_76_2x_ext = icon_76_2x.value.substring(icon_76_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var itunesartwork_ext = itunesartwork.value.substring(itunesartwork.value.lastIndexOf(".") + 1).toLowerCase();
                                      var itunesartwork_2x_ext = itunesartwork_2x.value.substring(itunesartwork_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                      if((document.getElementById("hid_icon_small").value==""&&icon_small.value=="")||(document.getElementById("hid_icon_40").value==""&&icon_40.value=="")||(document.getElementById("hid_icon_50").value==""&&icon_50.value=="")||(document.getElementById("hid_icon_57").value==""&&icon_57.value=="")||(document.getElementById("hid_icon_60").value==""&&icon_60.value=="")||(document.getElementById("hid_icon_72").value==""&&icon_72.value=="")||(document.getElementById("hid_icon_76").value==""&&icon_76.value=="")||(document.getElementById("hid_icon_small_2x").value==""&&icon_small_2x.value=="")||(document.getElementById("hid_icon_40_2x").value==""&&icon_40_2x.value=="")||(document.getElementById("hid_icon_50_2x").value==""&&icon_50_2x.value=="")||(document.getElementById("hid_icon_57_2x").value==""&&icon_57_2x.value=="")||(document.getElementById("hid_icon_60_2x").value==""&&icon_60_2x.value=="")||(document.getElementById("hid_icon_72_2x").value==""&&icon_72_2x.value=="")||(document.getElementById("hid_icon_76_2x").value==""&&icon_76_2x.value=="")||(document.getElementById("hid_itunesartwork").value==""&&itunesartwork.value=="")||(document.getElementById("hid_itunesartwork_2x").value==""&&itunesartwork_2x.value=="")) {
										   //alert("Please upload all icons for application. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
									  }
									  
                                      else if(icon_small_ext!="gif" && icon_small_ext!="jpg" && icon_small_ext!="jpeg" && icon_small_ext!="png" && icon_small_ext!="") {
                                          alert("Please upload a valid icon for icon_small. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_40_ext!="gif" && icon_40_ext!="jpg" && icon_40_ext!="jpeg" && icon_40_ext!="png" && icon_40_ext!="") {
                                          alert("Please upload a valid icon for icon_40. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_50_ext!="gif" && icon_50_ext!="jpg" && icon_50_ext!="jpeg" && icon_50_ext!="png" && icon_50_ext!="") {
                                          alert("Please upload a valid icon for icon_50. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_57_ext!="gif" && icon_57_ext!="jpg" && icon_57_ext!="jpeg" && icon_57_ext!="png" && icon_57_ext!="") {
                                          alert("Please upload a valid icon for icon_57. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_60_ext!="gif" && icon_60_ext!="jpg" && icon_60_ext!="jpeg" && icon_60_ext!="png" && icon_60_ext!="") {
                                          alert("Please upload a valid icon for icon_60. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_72_ext!="gif" && icon_72_ext!="jpg" && icon_72_ext!="jpeg" && icon_72_ext!="png" && icon_72_ext!="" ) {
                                          alert("Please upload a valid icon for icon_72. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_76_ext!="gif" && icon_76_ext!="jpg" && icon_76_ext!="jpeg" && icon_76_ext!="png" && icon_76_ext!="" ) {
                                          alert("Please upload a valid slash screen image for icon_76. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_small_2x_ext!="gif" && icon_small_2x_ext!="jpg" && icon_small_2x_ext!="jpeg" && icon_small_2x_ext!="png" && icon_small_2x_ext!="") {
                                          alert("Please upload a valid icon for icon_small_2x. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_40_2x_ext!="gif" && icon_40_2x_ext!="jpg" && icon_40_2x_ext!="jpeg" && icon_40_2x_ext!="png" && icon_40_2x_ext!="") {
                                          alert("Please upload a valid icon for icon_40_2x. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_50_2x_ext!="gif" && icon_50_2x_ext!="jpg" && icon_50_2x_ext!="jpeg" && icon_50_2x_ext!="png" && icon_50_2x_ext!="") {
                                          alert("Please upload a valid icon for icon_50_2x. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_57_2x_ext!="gif" && icon_57_2x_ext!="jpg" && icon_57_2x_ext!="jpeg" && icon_57_2x_ext!="png" && icon_57_2x_ext!="") {
                                          alert("Please upload a valid icon for icon_57_2x. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_60_2x_ext!="gif" && icon_60_2x_ext!="jpg" && icon_60_2x_ext!="jpeg" && icon_60_2x_ext!="png" && icon_60_2x_ext!="") {
                                          alert("Please upload a valid icon for icon_60_2x. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_72_2x_ext!="gif" && icon_72_2x_ext!="jpg" && icon_72_2x_ext!="jpeg" && icon_72_2x_ext!="png" && icon_72_2x_ext!="" ) {
                                          alert("Please upload a valid icon for icon_72. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(icon_76_2x_ext!="gif" && icon_76_2x_ext!="jpg" && icon_76_2x_ext!="jpeg" && icon_76_2x_ext!="png" && icon_76_2x_ext!="" ) {
                                          alert("Please upload a valid slash screen image for icon_76_2x. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(itunesartwork_ext!="gif" && itunesartwork_ext!="jpg" && itunesartwork_ext!="jpeg" && itunesartwork_ext!="png" && itunesartwork_ext!="" ) {
                                          alert("Please upload a valid icon for itunesartwork. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else if(itunesartwork_2x_ext!="gif" && itunesartwork_2x_ext!="jpg" && itunesartwork_2x_ext!="jpeg" && itunesartwork_2x_ext!="png" && itunesartwork_2x_ext!="" ) {
                                          alert("Please upload a valid slash screen image for itunesartwork_2x. ",{title: "Mofluid : Application Assets", width: 400});
							               return false;
                                      }
                                      else {
									    document.assets_detail_icons.submit();
                                     }
                           }
                              function saveiossplashAssetsDetail() {
                                      
                                      
                                      var screen_iphone_portrait = document.getElementById("screen_iphone_portrait");
                                      var screen_iphone_portrait_2x = document.getElementById("screen_iphone_portrait_2x");
                                      var screen_ipad_portrait = document.getElementById("screen_ipad_portrait");
                                      var screen_ipad_portrait_2x = document.getElementById("screen_ipad_portrait_2x");
                                      var screen_ipad_landscape = document.getElementById("screen_ipad_landscape");
                                      var screen_ipad_landscape_2x = document.getElementById("screen_ipad_landscape_2x");
                                      var screen_iphone_default = document.getElementById("screen_iphone_default");
                                      
                                     
                                      var screen_iphone_portrait_ext = screen_iphone_portrait.value.substring(screen_iphone_portrait.value.lastIndexOf(".") + 1).toLowerCase();
                                      var screen_iphone_portrait_2x_ext = screen_iphone_portrait_2x.value.substring(screen_iphone_portrait_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var screen_ipad_portrait_ext = screen_ipad_portrait.value.substring(screen_ipad_portrait.value.lastIndexOf(".") + 1).toLowerCase();
                                      var screen_ipad_portrait_2x_ext = screen_ipad_portrait_2x.value.substring(screen_ipad_portrait_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var screen_ipad_landscape_ext = screen_ipad_landscape.value.substring(screen_ipad_landscape.value.lastIndexOf(".") + 1).toLowerCase();
                                      var screen_ipad_landscape_2x_ext = screen_ipad_landscape_2x.value.substring(screen_ipad_landscape_2x.value.lastIndexOf(".") + 1).toLowerCase();
                                      var screen_iphone_default_ext = screen_iphone_default.value.substring(screen_iphone_default.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                   
                                      if((document.getElementById("hid_screen_iphone_portrait").value==""&&screen_iphone_portrait.value=="")||(document.getElementById("hid_screen_iphone_portrait_2x").value==""&&screen_iphone_portrait_2x.value=="")||(document.getElementById("hid_screen_ipad_portrait").value==""&&screen_ipad_portrait.value=="")||(document.getElementById("hid_screen_ipad_portrait_2x").value==""&&screen_ipad_portrait_2x.value=="")||(document.getElementById("hid_screen_ipad_landscape").value==""&&screen_ipad_landscape.value=="")||(document.getElementById("hid_screen_ipad_landscape_2x").value==""&&screen_ipad_landscape_2x.value=="")||(document.getElementById("hid_screen_iphone_default").value==""&&screen_iphone_default.value=="")) {
										  //alert("Please upload all Splash Screen for application.");
							             return false;
							         }
                                      else if(screen_iphone_portrait_ext!="gif" && screen_iphone_portrait_ext!="jpg" && screen_iphone_portrait_ext!="jpeg" && screen_iphone_portrait_ext!="png" && screen_iphone_portrait_ext!="") {
                                          alert("Please upload a valid slash screen image for screen_iphone_portrait.");
							               return false;
                                      }
                                      else if(screen_iphone_portrait_2x_ext!="gif" && screen_iphone_portrait_2x_ext!="jpg" && screen_iphone_portrait_2x_ext!="jpeg" && screen_iphone_portrait_2x_ext!="png" && screen_iphone_portrait_2x_ext!="" ) {
                                          alert("Please upload a valid slash screen image for screen_iphone_portrait_2x.");
							               return false;
                                      }
                                      else if(screen_ipad_portrait_ext!="gif" && screen_ipad_portrait_ext!="jpg" && screen_ipad_portrait_ext!="jpeg" && screen_ipad_portrait_ext!="png" && screen_ipad_portrait_ext!="" ) {
                                          alert("Please upload a valid slash screen image for screen_ipad_portrait.");
							               return false;
                                      }
                                      else if(screen_ipad_portrait_2x_ext!="gif" && screen_ipad_portrait_2x_ext!="jpg" && screen_ipad_portrait_2x_ext!="jpeg" && screen_ipad_portrait_2x_ext!="png" && screen_ipad_portrait_2x_ext!="" ) {
                                          alert("Please upload a valid slash screen image for screen_ipad_portrait_2x.");
							               return false;
                                      }
                                      else if(screen_ipad_landscape_ext!="gif" && screen_ipad_landscape_ext!="jpg" && screen_ipad_landscape_ext!="jpeg" && screen_ipad_landscape_ext!="png" && screen_ipad_landscape_ext!="" ) {
                                          alert("Please upload a valid slash screen image for screen_ipad_landscape.");
							               return false;
                                      }
                                      else if(screen_ipad_landscape_2x_ext!="gif" && screen_ipad_landscape_2x_ext!="jpg" && screen_ipad_landscape_2x_ext!="jpeg" && screen_ipad_landscape_2x_ext!="png" && screen_ipad_landscape_2x_ext!="" ) {
                                          alert("Please upload a valid slash screen image for screen_ipad_landscape_2x.");
							               return false;
                                      }
                                      else if(screen_iphone_default_ext!="gif" && screen_iphone_default_ext!="jpg" && screen_iphone_default_ext!="jpeg" && screen_iphone_default_ext!="png" && screen_iphone_default_ext!="" ) {
                                          alert("Please upload a valid slash screen image for screen_iphone_default.");
							               return false;
                                      }
                                      else {
									    document.assets_detail_splash.submit();
                                     } 
                                }
                                
function switch_footer_type_i(){
														var type = document.getElementById("customfooter_i").value;
														if(type=="1") {
															document.getElementById("customfooter_row_i").style.display = "";
														}
														else {
															document.getElementById("customfooter_row_i").style.display = "none";
														}
													}

function switch_image_type_i() {
                                      var type = document.getElementById("app_image_type_i").value;
                                                                     if(type=="custom") {
                                                                     if(document.getElementById("mofluid_theme_i").value == "Elegant") {
                                    document.getElementById("app_img_row11").style.display = "none";
                                    document.getElementById("app_img_row21").style.display = "none";
                                 }
                                 else {
                                   document.getElementById("app_img_row11").style.display = "";
                                    document.getElementById("app_img_row21").style.display = "";
                                 }
                                                                                 document.getElementById("app_img_row31").style.display = "";
                                                                                 document.getElementById("app_img_row41").style.display = "";
                                                                                 document.getElementById("app_img_row51").style.display = "";
                                                                                 document.getElementById("app_img_row61").style.display = "";
                                                                                 document.getElementById("app_img_row71").style.display = "";
                                                                                                                                                                 
                                                                         }
                                                                         else {
                                                                                 document.getElementById("app_img_row11").style.display = "none";
                                                                                 document.getElementById("app_img_row21").style.display = "none";
                                                                                 document.getElementById("app_img_row31").style.display = "none";
                                                                                 document.getElementById("app_img_row41").style.display = "none";
                                                                                 document.getElementById("app_img_row51").style.display = "none";
                                                                                 document.getElementById("app_img_row61").style.display = "none";
                                                                                 document.getElementById("app_img_row71").style.display = "none";
                                                                                                                                                                 
                                                                                 
                                                                         }
              }
              
 


                        function saveiosThemeDetail() {
				
                                var app_image_type = document.getElementById("app_image_type_i").value;
                                var app_img_home = document.getElementById("app_img_home_i");
                                var app_img_home_ext = app_img_home.value.substring(app_img_home.value.lastIndexOf(".") + 1).toLowerCase();
                                  
                                var app_img_back = document.getElementById("app_img_back_i");
                                var app_img_back_ext = app_img_back.value.substring(app_img_back.value.lastIndexOf(".") + 1).toLowerCase();  
                                  
                                var app_img_cross = document.getElementById("app_img_cross_i");
                                var app_img_cross_ext = app_img_cross.value.substring(app_img_cross.value.lastIndexOf(".") + 1).toLowerCase();   
                                var app_img_cart = document.getElementById("app_img_cart_i");
                                var app_img_cart_ext = app_img_cart.value.substring(app_img_cart.value.lastIndexOf(".") + 1).toLowerCase();
                                     
                                var app_img_del = document.getElementById("app_img_del_i");
                                var app_img_del_ext = app_img_del.value.substring(app_img_del.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                var app_img_search = document.getElementById("app_img_search_i");
                                var app_img_search_ext = app_img_search.value.substring(app_img_search.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                var app_img_menu = document.getElementById("app_img_menu_i");
                                var app_img_menu_ext = app_img_menu.value.substring(app_img_menu.value.lastIndexOf(".") + 1).toLowerCase();




								var logo = document.getElementById("logo_i");
								var banner = document.getElementById("banner_i");
								var logoext = logo.value.substring(logo.value.lastIndexOf(".") + 1).toLowerCase();
								var bannerext = banner.value.substring(banner.value.lastIndexOf(".") + 1).toLowerCase();
								var theme_background = document.getElementById("theme_background_i").value;
								var theme_text = document.getElementById("theme_text_i").value;
								var theme_header = document.getElementById("theme_header_i").value;
								var theme_title = document.getElementById("theme_title_i").value;
								var theme_button = document.getElementById("theme_button_i").value;

								var customfooter_type = document.getElementById("customfooter_i").value;
								var customfooter_code = document.getElementById("customfooter_code_i").value.trim();
 if(app_image_type=="custom"){

if(document.getElementById("hid_app_img_home_i").value==""&&app_img_home.value=="" && document.getElementById("mofluid_theme_i").value != "Elegant") {
                                            alert("Please upload a valid home image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_home_ext!="gif" && app_img_home_ext!="jpg" && app_img_home_ext!="jpeg" && app_img_home_ext!="png" && app_img_home_ext!="" && document.getElementById("mofluid_theme_i").value != "Elegant") {
                                          alert("Please upload a valid home image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
                                      else if(document.getElementById("hid_app_img_back").value=="" && app_img_back.value=="" && document.getElementById("mofluid_theme_i").value != "Elegant") {
                                            alert("Please upload a valid back image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_back_ext!="gif" && app_img_back_ext!="jpg" && app_img_back_ext!="jpeg" && app_img_back_ext!="png" && app_img_back_ext!=""&&document.getElementById("mofluid_theme_i").value != "Elegant") {
                                          alert("Please upload a valid back image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }



 else if(document.getElementById("hid_app_img_cross_i").value==""&&app_img_cross.value=="") {
                                            alert("Please upload a valid cross image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_cross_ext!="gif" && app_img_cross_ext!="jpg" && app_img_cross_ext!="jpeg" && app_img_cross_ext!="png" && app_img_cross_ext!="") {
                                          alert("Please upload a valid cross image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
 else if(document.getElementById("hid_app_img_cart_i").value==""&&app_img_cart.value=="") {
                                            alert("Please upload a valid cart image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_cart_ext!="gif" && app_img_cart_ext!="jpg" && app_img_cart_ext!="jpeg" && app_img_cart_ext!="png" && app_img_cart_ext!="") {
                                          alert("Please upload a valid cart image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
}

else if(document.getElementById("hid_app_img_del_i").value==""&&app_img_del.value=="") {
                                            alert("Please upload a valid delete image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_del_ext!="gif" && app_img_del_ext!="jpg" && app_img_del_ext!="jpeg" && app_img_del_ext!="png" && app_img_del_ext!="") {
                                          alert("Please upload a valid delete image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
 else if(document.getElementById("hid_app_img_search_i").value==""&&app_img_search.value=="") {
                                            alert("Please upload a valid search image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_search_ext!="gif" && app_img_search_ext!="jpg" && app_img_search_ext!="jpeg" && app_img_search_ext!="png" && app_img_search_ext!="") {
                                          alert("Please upload a valid search image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
 else if(document.getElementById("hid_app_img_menu_i").value==""&&app_img_menu.value=="") {
                                            alert("Please upload a valid menu image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_menu_ext!="gif" && app_img_menu_ext!="jpg" && app_img_menu_ext!="jpeg" && app_img_menu_ext!="png" && app_img_menu_ext!="") {
                                          alert("Please upload a valid menu image for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }


else if(document.getElementById("hid_logo_i").value==""&&logo.value=="") {
                                                                                   alert("Please upload a valid logo for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                      }
                                                                          else if(document.getElementById("hid_banner_i").value==""&&banner.value=="") {
                                                                                  alert("Please upload a valid banner for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                                                          }
                                      else if(logoext!="gif" && logoext!="jpg" && logoext!="jpeg" && logoext!="png" && logoext!="" ) {
                                          alert("Please upload a valid logo for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                      }                                     

else if(bannerext!="gif" && bannerext!="jpg" && bannerext!="jpeg" && bannerext!="png" && bannerext!="") {
                                          alert("Please upload a valid banner for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                      }
                                      else if(theme_background=="") {
                                                                             alert("Please slect a valid color for background.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_text=="") {
                                                                             alert("Please slect a valid color for default text.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_background === theme_text) {
                                                                             alert("Application Background and Default Text Color can't be same.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_header=="") {
                                                                             alert("Please slect a valid color for header.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_title=="") {
                                                                             alert("Please slect a valid color for title text.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_button=="") {
                                                                             alert("Please slect a valid color for application's button.",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if (customfooter_type == "1" && customfooter_code =="") {
                                        alert("Please provide the html code for custom footer.",{title: "Mofluid : Theme Configuration", width: 400});
                                        return false;
                                    }
                                                                          else {
                                                                          document.ios_theme_detail.submit();
                                      return true;
                                      }
}





else {
if(document.getElementById("hid_logo_i").value==""&&logo.value=="") {
										   alert("Please upload a valid logo for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
                                      }
									  else if(document.getElementById("hid_banner").value==""&&banner.value=="") {
										  alert("Please upload a valid banner for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
									  }
                                      else if(logoext!="gif" && logoext!="jpg" && logoext!="jpeg" && logoext!="png" && logoext!="" ) {
                                          alert("Please upload a valid logo for mobile application.",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
                                      }
                                      else if(bannerext!="gif" && bannerext!="jpg" && bannerext!="jpeg" && bannerext!="png" && bannerext!="") {
                                          alert("Please upload a valid banner for mobile application. ",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
                                      }
                                      else if(theme_background=="") {
									     alert("Please slect a valid color for background. ",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_text=="") {
									     alert("Please slect a valid color for default text. ",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_background === theme_text) {
                                        alert("Application Background and Default Text Color can't be same.",{title: "Mofluid : Theme Configuration", width: 400});
                                        return false;
                                      }
                                      else if(theme_header=="") {
									     alert("Please slect a valid color for header. ",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_title=="") {
									     alert("<p><font color=red size=2>Please slect a valid color for title text. ",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_button=="") {
									     alert("<p><font color=red size=2>Please slect a valid color for application's button. ",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if (customfooter_type == "1" && customfooter_code =="") {
                                        alert("<p><font color=red size=2>Please provide the html code for custom footer. ",{title: "Mofluid : Theme Configuration", width: 400});
                                        return false;
                                    }
									  else {
									  document.ios_theme_detail.submit();
                                      return true;
                                      }
                                              }
							  }
