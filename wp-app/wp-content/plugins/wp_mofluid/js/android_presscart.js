

 function saveAndroidAppDetail() {
					    var mofluid_id = document.app_detail.mofluid_id.value;
					    var mofluid_key = document.app_detail.mofluid_key.value;
					    var name = document.app_detail.name.value;
					    var bundle_id = document.app_detail.bundle_id.value;	
					    var version = document.app_detail.version.value;
					    var store = document.app_detail.store.value;	
				  	    var keypass = document.app_detail.privatekey_pswd.value;
					    var storepass = document.app_detail.keystore_pswd.value;
					    var release_key2 = document.app_detail.release_key2.value;
					    var type = parseInt(document.getElementById("key_type").value);
                                            var errwords = checkbundleid(bundle_id);
   if(name==""||bundle_id==""||version==""||store==""||mofluid_id==""||mofluid_key==""||(keypass==""&&type==1)||(storepass==""&&type==1)) {
									    alert("All fields are mandatory.");
							            return false; 
							       
}
if(errwords != 0) {
 alert("Some Reserved Words are not allowed in Bundle Id like "+errwords);
return false;
}
else if(hasWhiteSpace(bundle_id)) {

 alert("Special Symbol other than . are not allowed in Bundle Id.");
return false;
}

var version_filter = /^\d+(\.\d+){0,2}$/i;

							       if(mofluid_id!="" && isInvalidEmail(mofluid_id)) {
									   alert("Please enter the valid Mofluid ID.");
							            return false;
								   } 


                                                                  else if(!name.match(/^[A-Za-z]+$/)) {
                                                                    
       alert("Special symbol , spaces and numbers are not allowed in application name.");
                                                                    return false;
                                                                   }
							       else if(version == "" || version == null) {
							             alert("Version number cant be empty.");  
							             return false;
							       }
							       else if(!version_filter.test(version)){
									   alert("Version number should be positive integer in X or X.X or X.X.X format.");
									   return false;
								   }
								   else if(!isInt(store)) {
							             alert("Store Id should be non zero positive integer.");
							             return false;
							       }
								   else if(store<1) {
									   alert("Store Id should be non zero positive integer.");
							           return false;
								   } 
							       else if(keypass.length<6&&type==1) {
									     alert("Private Key should be atleast 6 characters long.");
							             return false;
							       }
							       else if(storepass.length<6&&type==1) {
									     alert("Key Store Password should be atleast 6 characters long.");
							             return false;
							       }
							       else if((release_key2=="" && document.app_detail.release_key.value=="")&&type==1) {
									     alert("Upload a valid keystore file to sign the app for release.");
							             return false;
							       }
							       else if((document.app_detail.key_store_pwd.value=="")&&type==0) {
								         alert("Key Store Password Can't be left blank.");
							             return false;
								   }
								   else if((document.app_detail.key_pwd.value=="")&&type==0) {
									     alert("Private Key Password Can't be left blank.");
							             return false;
								   }
								   else if((document.app_detail.key_store_pwd.value.length<6)&&type==0) {
								         alert("Key Store Password should be atleast 6 characters long.");
							             return false;
								   }
								   else if((document.app_detail.key_pwd.value.length<6)&&type==0) {
									     alert("Private Key Password should be atleast 6 characters long.");
							             return false;
								   }
								   else if((document.app_detail.key_validity.value=="")&&type==0) {
									     alert("Provide Validity (in days) for Key.");
							             return false;
								   }
								   else if(!isInt(document.app_detail.key_validity.value)&&type==0) {
									     alert("Validity Should be non zero integer value.");
							             return false;
								   }
								   else if((document.app_detail.key_common_name.value=="")&&type==0) {
									     alert("Provide Comman Name of User Required for Key Generation.");
							             return false;
								   }
								   else if((document.app_detail.key_org.value=="")&&type==0) {
									     alert("Provide Organization Name Required for Key Generation.");
							             return false;
								   }
								   else if((document.app_detail.key_org_unit.value=="")&&type==0) {
									     alert("Provide Organization Unit Name Required for Key Generation.");
							             return false;
								   }
								   else if((document.app_detail.key_city.value=="")&&type==0) {
									     alert("Provide City Name Where your Organization Situated Required for Key Generation.");
							             return false;
								   }
								   else if((document.app_detail.key_state.value=="")&&type==0) {
									     alert("Provide State Name Where your Organization Situated Required for Key Generation.");
							             return false;
								   }
								   else if(((document.app_detail.key_country.value=="")&&type==0)||(document.app_detail.key_country.value.length!=2)&&type==0) {
									     alert("Provide 2 Digit Country Code Where your Organization Situated Required for Key Generation.");
							             return false;
								   }
								   else {
                                                                     document.app_detail.submit();
								     return true;
								   }  
}
 function hasWhiteSpace(spacefield) {
     return !(/^[a-z0-9.]+$/i.test(spacefield));
  }
							    function isInt(x) {
                                        return (!isNaN(x) && parseInt(x) == x)
                                }
function checkbundleid(bundle_id){
var reserverwords = ["abstract", "continue", "for", "new", "switch", "assert", "default", "goto", "package", "synchronized", "boolean", "do", "if", "private", "this", "break", "double", "implements", "protected", "throw", "byte", "else", "import", "public", "throws", "case", "enum", "instanceof", "return", "transient", "catch", "extends", "int", "short", "try", "char", "final", "interface", "static", "void", "class", "finally", "long", "strictfp", "volatile", "const", "float", "native", "super", "while"];
                                           var bundlecounter,bundlecheck;
                                           var singlebundleidword = document.app_detail.bundle_id.value.split(".");
                                           var bundleidlen = singlebundleidword.length;
                                           var bundleerrorwords = "";
                                           var bundleiderror = 0;
                                           for(bundlecounter=0;bundlecounter<bundleidlen;bundlecounter++) {
                                                bundlecheck = reserverwords.indexOf(singlebundleidword[bundlecounter]);
                                                if(bundlecheck>=0) {
                                                    bundleiderror = 1;
                                                    bundleerrorwords = singlebundleidword[bundlecounter];
                                                    break;
                                                }
            
}
if(bundleiderror == 1) {
return bundleerrorwords;
}
else {
return 0;
}
}
                                function isInvalidEmail(email) {
                                     var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                                     if (!filter.test(email)) {
                                            return true;
                                     }
                                     else {
                                        return false;
									 }
                                }


function switch_keytype() {
								     var type = parseInt(document.getElementById("key_type").value);
								     console.log("Execxuted"+type);
								     if(type==1) {
										 document.getElementById("key_row1").style.display = "none";
										 document.getElementById("key_row2").style.display = "none";
										 document.getElementById("key_row3").style.display = "none";
										 document.getElementById("key_row4").style.display = "none";
										 document.getElementById("key_row5").style.display = "none";
										 document.getElementById("key_row6").style.display = "none";
										 document.getElementById("key_row7").style.display = "none";
										 document.getElementById("key_row8").style.display = "none";
										 document.getElementById("key_row9").style.display = "none";
										 document.getElementById("key_row10").style.display = "";
										 document.getElementById("key_row11").style.display = "";
										 document.getElementById("key_row12").style.display = "";
									 }
									 else {
										 document.getElementById("key_row1").style.display = "";
										 document.getElementById("key_row2").style.display = "";
										 document.getElementById("key_row3").style.display = "";
										 document.getElementById("key_row4").style.display = "";
										 document.getElementById("key_row5").style.display = "";
										 document.getElementById("key_row6").style.display = "";
										 document.getElementById("key_row7").style.display = "";
										 document.getElementById("key_row8").style.display = "";
										 document.getElementById("key_row9").style.display = "";
										 document.getElementById("key_row10").style.display = "none";
										 document.getElementById("key_row11").style.display = "none";
										 document.getElementById("key_row12").style.display = "none";
										 
									 }
								}
								
								
// application assets
function saveAssetsDetail() {
                                      var ico_drawable = document.getElementById("ico_drawable");
                                      var ico_hdpi = document.getElementById("ico_hdpi");
                                      var ico_ldpi = document.getElementById("ico_ldpi");
                                      var ico_mdpi = document.getElementById("ico_mdpi");
                                      var ico_xhdpi = document.getElementById("ico_xhdpi");
                                      var drawable_port_hdpi = document.getElementById("drawable_port_hdpi");
                                      var drawable_port_ldpi = document.getElementById("drawable_port_ldpi");
                                      var drawable_port_mdpi = document.getElementById("drawable_port_mdpi");
                                      var drawable_port_xhdpi = document.getElementById("drawable_port_xhdpi");
                                      var drawable_land_hdpi = document.getElementById("drawable_land_hdpi");
                                      var drawable_land_ldpi = document.getElementById("drawable_land_ldpi");
                                      var drawable_land_mdpi = document.getElementById("drawable_land_mdpi");
                                      var drawable_land_xhdpi = document.getElementById("drawable_land_xhdpi");
                                      
                                      var ico_drawable_ext = ico_drawable.value.substring(ico_drawable.value.lastIndexOf(".") + 1).toLowerCase();
                                      var ico_hdpi_ext = ico_hdpi.value.substring(ico_hdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var ico_ldpi_ext = ico_ldpi.value.substring(ico_ldpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var ico_mdpi_ext = ico_mdpi.value.substring(ico_mdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var ico_xhdpi_ext = ico_xhdpi.value.substring(ico_xhdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                      var drawable_port_hdpi_ext = drawable_port_hdpi.value.substring(drawable_port_hdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var drawable_port_ldpi_ext = drawable_port_ldpi.value.substring(drawable_port_ldpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var drawable_port_mdpi_ext = drawable_port_mdpi.value.substring(drawable_port_mdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var drawable_port_xhdpi_ext = drawable_port_xhdpi.value.substring(drawable_port_xhdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                      var drawable_land_hdpi_ext = drawable_land_hdpi.value.substring(drawable_land_hdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var drawable_land_ldpi_ext = drawable_land_ldpi.value.substring(drawable_land_ldpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var drawable_land_mdpi_ext = drawable_land_mdpi.value.substring(drawable_land_mdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      var drawable_land_xhdpi_ext = drawable_land_xhdpi.value.substring(drawable_land_xhdpi.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                      
                                      if((document.getElementById("hid_ico_drawable").value==""&&ico_drawable.value=="")||(document.getElementById("hid_ico_ldpi").value==""&&ico_ldpi.value=="")||(document.getElementById("hid_ico_hdpi").value==""&&ico_hdpi.value=="")||(document.getElementById("hid_ico_mdpi").value==""&&ico_mdpi.value=="")||(document.getElementById("hid_ico_xhdpi").value==""&&ico_xhdpi.value=="")) {
										   alert("Please upload all icons for application.");
							               return false;
									  }
									  else if((document.getElementById("hid_drawable_port_hdpi").value==""&&drawable_port_hdpi.value=="")||(document.getElementById("hid_drawable_port_ldpi").value==""&&drawable_port_ldpi.value=="")||(document.getElementById("hid_drawable_port_mdpi").value==""&&drawable_port_mdpi.value=="")||(document.getElementById("hid_drawable_port_xhdpi").value==""&&drawable_port_xhdpi.value=="")||(document.getElementById("hid_drawable_land_hdpi").value==""&&drawable_land_hdpi.value=="")||(document.getElementById("hid_drawable_land_ldpi").value==""&&drawable_land_ldpi.value=="")||(document.getElementById("hid_drawable_land_mdpi").value==""&&drawable_land_mdpi.value=="")||(document.getElementById("hid_drawable_land_xhdpi").value==""&&drawable_land_xhdpi.value=="")) {
										  alert("Please upload all Splash Screen for application.");
							          }
                                      else if(ico_drawable_ext!="gif" && ico_drawable_ext!="jpg" && ico_drawable_ext!="jpeg" && ico_drawable_ext!="png" && ico_drawable_ext!="") {
                                          alert("Please upload a valid icon for drawble.");
							               return false;
                                      }
                                      else if(ico_hdpi_ext!="gif" && ico_hdpi_ext!="jpg" && ico_hdpi_ext!="jpeg" && ico_hdpi_ext!="png" && ico_hdpi_ext!="") {
                                          alert("Please upload a valid icon for hdpi.");
							               return false;
                                      }
                                      else if(ico_ldpi_ext!="gif" && ico_ldpi_ext!="jpg" && ico_ldpi_ext!="jpeg" && ico_ldpi_ext!="png" && ico_ldpi_ext!="") {
                                          alert("Please upload a valid icon for ldpi.");
							               return false;
                                      }
                                      else if(ico_mdpi_ext!="gif" && ico_mdpi_ext!="jpg" && ico_mdpi_ext!="jpeg" && ico_mdpi_ext!="png" && ico_mdpi_ext!="") {
                                          alert("Please upload a valid icon for mdpi.");
							               return false;
                                      }
                                      else if(ico_xhdpi_ext!="gif" && ico_xhdpi_ext!="jpg" && ico_xhdpi_ext!="jpeg" && ico_xhdpi_ext!="png" && ico_xhdpi_ext!="" ) {
                                          alert("Please upload a valid icon for xhdpi.");
							               return false;
                                      }
                                      else if(drawable_port_hdpi_ext!="gif" && drawable_port_hdpi_ext!="jpg" && drawable_port_hdpi_ext!="jpeg" && drawable_port_hdpi_ext!="png" && drawable_port_hdpi_ext!="" ) {
                                          alert("Please upload a valid slash screen image for drawable_port_hdpi.");
							               return false;
                                      }
                                      else if(drawable_port_ldpi_ext!="gif" && drawable_port_ldpi_ext!="jpg" && drawable_port_ldpi_ext!="jpeg" && drawable_port_ldpi_ext!="png" && drawable_port_ldpi_ext!="") {
                                          alert("Please upload a valid slash screen image for drawable_port_ldpi.");
							               return false;
                                      }
                                      else if(drawable_port_mdpi_ext!="gif" && drawable_port_mdpi_ext!="jpg" && drawable_port_mdpi_ext!="jpeg" && drawable_port_mdpi_ext!="png" && drawable_port_mdpi_ext!="" ) {
                                          alert("Please upload a valid slash screen image for drawable_port_mdpi.");
							               return false;
                                      }
                                      else if(drawable_port_xhdpi_ext!="gif" && drawable_port_xhdpi_ext!="jpg" && drawable_port_xhdpi_ext!="jpeg" && drawable_port_xhdpi_ext!="png" && drawable_port_xhdpi_ext!="" ) {
                                          alert("Please upload a valid slash screen image for drawable_port_xhdpi.");
							               return false;
                                      }
                                      else if(drawable_land_hdpi_ext!="gif" && drawable_land_hdpi_ext!="jpg" && drawable_land_hdpi_ext!="jpeg" && drawable_land_hdpi_ext!="png" && drawable_land_hdpi_ext!="" ) {
                                          alert("Please upload a valid slash screen image for drawable_land_hdpi.");
							               return false;
                                      }
                                      else if(drawable_land_ldpi_ext!="gif" && drawable_land_ldpi_ext!="jpg" && drawable_land_ldpi_ext!="jpeg" && drawable_land_ldpi_ext!="png" && drawable_land_ldpi_ext!="" ) {
                                          alert("Please upload a valid slash screen image for drawable_land_ldpi.");
							               return false;
                                      }
                                      else if(drawable_land_mdpi_ext!="gif" && drawable_land_mdpi_ext!="jpg" && drawable_land_mdpi_ext!="jpeg" && drawable_land_mdpi_ext!="png" && drawable_land_mdpi_ext!="" ) {
                                          alert("Please upload a valid slash screen image for drawable_land_mdpi.");
							               return false;
                                      }
                                      else if(drawable_land_xhdpi_ext!="gif" && drawable_land_xhdpi_ext!="jpg" && drawable_land_xhdpi_ext!="jpeg" && drawable_land_xhdpi_ext!="png" && drawable_land_xhdpi_ext!="" ) {
                                          alert("Please upload a valid slash screen image for drawable_land_xhdpi.");
							               return false;
                                      }
                                      else {
										  document.assets_detail.submit(); 
                                          return true;  
									  }
                                     
                                }
                                

						
						 function switch_footer_type(){
														var type = document.getElementById("customfooter").value;
														if(type=="1") {
															document.getElementById("customfooter_row").style.display = "";
														}
														else {
															document.getElementById("customfooter_row").style.display = "none";
														}
													}
                            
                               function switch_image_type() {
                                      var type = document.getElementById("app_image_type").value;
                                                                     if(type=="custom") {
                                                                     if(document.getElementById("mofluid_theme").value == "Elegant") {
                                    document.getElementById("app_img_row1").style.display = "none";
                                    document.getElementById("app_img_row2").style.display = "none";
                                 }
                                 else {
                                   document.getElementById("app_img_row1").style.display = "";
                                    document.getElementById("app_img_row2").style.display = "";
                                 }
                                                                                 document.getElementById("app_img_row3").style.display = "";
                                                                                 document.getElementById("app_img_row4").style.display = "";
                                                                                 document.getElementById("app_img_row5").style.display = "";
                                                                                 document.getElementById("app_img_row6").style.display = "";
                                                                                 document.getElementById("app_img_row7").style.display = "";
                                                                                                                                                                 
                                                                         }
                                                                         else {
                                                                                 document.getElementById("app_img_row1").style.display = "none";
                                                                                 document.getElementById("app_img_row2").style.display = "none";
                                                                                 document.getElementById("app_img_row3").style.display = "none";
                                                                                 document.getElementById("app_img_row4").style.display = "none";
                                                                                 document.getElementById("app_img_row5").style.display = "none";
                                                                                 document.getElementById("app_img_row6").style.display = "none";
                                                                                 document.getElementById("app_img_row7").style.display = "none";
                                                                                                                                                                 
                                                                                 
                                                                         }
                                                                }



                        function saveandroidThemeDetail() {
				
                                var app_image_type = document.getElementById("app_image_type").value;
                                var app_img_home = document.getElementById("app_img_home");
                                var app_img_home_ext = app_img_home.value.substring(app_img_home.value.lastIndexOf(".") + 1).toLowerCase();
                                  
                                var app_img_back = document.getElementById("app_img_back");
                                var app_img_back_ext = app_img_back.value.substring(app_img_back.value.lastIndexOf(".") + 1).toLowerCase();  
                                  
                                var app_img_cross = document.getElementById("app_img_cross");
                                var app_img_cross_ext = app_img_cross.value.substring(app_img_cross.value.lastIndexOf(".") + 1).toLowerCase();   
                                var app_img_cart = document.getElementById("app_img_cart");
                                var app_img_cart_ext = app_img_cart.value.substring(app_img_cart.value.lastIndexOf(".") + 1).toLowerCase();
                                     
                                var app_img_del = document.getElementById("app_img_del");
                                var app_img_del_ext = app_img_del.value.substring(app_img_del.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                var app_img_search = document.getElementById("app_img_search");
                                var app_img_search_ext = app_img_search.value.substring(app_img_search.value.lastIndexOf(".") + 1).toLowerCase();
                                      
                                var app_img_menu = document.getElementById("app_img_menu");
                                var app_img_menu_ext = app_img_menu.value.substring(app_img_menu.value.lastIndexOf(".") + 1).toLowerCase();




								var logo = document.getElementById("logo");
								var banner = document.getElementById("banner");
								var logoext = logo.value.substring(logo.value.lastIndexOf(".") + 1).toLowerCase();
								var bannerext = banner.value.substring(banner.value.lastIndexOf(".") + 1).toLowerCase();
								var theme_background = document.getElementById("theme_background").value;
								var theme_text = document.getElementById("theme_text").value;
								var theme_header = document.getElementById("theme_header").value;
								var theme_title = document.getElementById("theme_title").value;
								var theme_button = document.getElementById("theme_button").value;

								var customfooter_type = document.getElementById("customfooter").value;
								var customfooter_code = document.getElementById("customfooter_code").value.trim();
 if(app_image_type=="custom"){

if(document.getElementById("hid_app_img_home").value==""&&app_img_home.value=="" && document.getElementById("mofluid_theme").value != "Elegant") {
                                            alert("<p><font color=red size=2>Please upload a valid home image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_home_ext!="gif" && app_img_home_ext!="jpg" && app_img_home_ext!="jpeg" && app_img_home_ext!="png" && app_img_home_ext!="" && document.getElementById("mofluid_theme").value != "Elegant") {
                                          alert("<p><font color=red size=2>Please upload a valid home image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
                                      else if(document.getElementById("hid_app_img_back").value=="" && app_img_back.value=="" && document.getElementById("mofluid_theme").value != "Elegant") {
                                            alert("<p><font color=red size=2>Please upload a valid back image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_back_ext!="gif" && app_img_back_ext!="jpg" && app_img_back_ext!="jpeg" && app_img_back_ext!="png" && app_img_back_ext!=""&&document.getElementById("mofluid_theme").value != "Elegant") {
                                          alert("<p><font color=red size=2>Please upload a valid back image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }



 else if(document.getElementById("hid_app_img_cross").value==""&&app_img_cross.value=="") {
                                            alert("<p><font color=red size=2>Please upload a valid cross image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_cross_ext!="gif" && app_img_cross_ext!="jpg" && app_img_cross_ext!="jpeg" && app_img_cross_ext!="png" && app_img_cross_ext!="") {
                                          alert("<p><font color=red size=2>Please upload a valid cross image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
 else if(document.getElementById("hid_app_img_cart").value==""&&app_img_cart.value=="") {
                                            alert("<p><font color=red size=2>Please upload a valid cart image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_cart_ext!="gif" && app_img_cart_ext!="jpg" && app_img_cart_ext!="jpeg" && app_img_cart_ext!="png" && app_img_cart_ext!="") {
                                          alert("<p><font color=red size=2>Please upload a valid cart image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
}

else if(document.getElementById("hid_app_img_del").value==""&&app_img_del.value=="") {
                                            alert("<p><font color=red size=2>Please upload a valid delete image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_del_ext!="gif" && app_img_del_ext!="jpg" && app_img_del_ext!="jpeg" && app_img_del_ext!="png" && app_img_del_ext!="") {
                                          alert("<p><font color=red size=2>Please upload a valid delete image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
 else if(document.getElementById("hid_app_img_search").value==""&&app_img_search.value=="") {
                                            alert("<p><font color=red size=2>Please upload a valid search image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_search_ext!="gif" && app_img_search_ext!="jpg" && app_img_search_ext!="jpeg" && app_img_search_ext!="png" && app_img_search_ext!="") {
                                          alert("<p><font color=red size=2>Please upload a valid search image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }
 else if(document.getElementById("hid_app_img_menu").value==""&&app_img_menu.value=="") {
                                            alert("<p><font color=red size=2>Please upload a valid menu image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                            return false;
                                      }
                                      else if(app_img_menu_ext!="gif" && app_img_menu_ext!="jpg" && app_img_menu_ext!="jpeg" && app_img_menu_ext!="png" && app_img_menu_ext!="") {
                                          alert("<p><font color=red size=2>Please upload a valid menu image for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                          return false;
                                      }


else if(document.getElementById("hid_logo").value==""&&logo.value=="") {
                                                                                   alert("<p><font color=red size=3>Please upload a valid logo for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                      }
                                                                          else if(document.getElementById("hid_banner").value==""&&banner.value=="") {
                                                                                  alert("<p><font color=red size=2>Please upload a valid banner for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                                                          }
                                      else if(logoext!="gif" && logoext!="jpg" && logoext!="jpeg" && logoext!="png" && logoext!="" ) {
                                          alert("<p><font color=red size=2>Please upload a valid logo for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                      }                                     

else if(bannerext!="gif" && bannerext!="jpg" && bannerext!="jpeg" && bannerext!="png" && bannerext!="") {
                                          alert("<p><font color=red size=2>Please upload a valid banner for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                       return false;
                                      }
                                      else if(theme_background=="") {
                                                                             alert("<p><font color=red size=2>Please slect a valid color for background. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_text=="") {
                                                                             alert("<p><font color=red size=2>Please slect a valid color for default text. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_background === theme_text) {
                                                                             alert("<p><font color=red size=2>Application Background and Default Text Color can't be same.</font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_header=="") {
                                                                             alert("<p><font color=red size=2>Please slect a valid color for header. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_title=="") {
                                                                             alert("<p><font color=red size=2>Please slect a valid color for title text. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if(theme_button=="") {
                                                                             alert("<p><font color=red size=2>Please slect a valid color for application's button. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                                                     return false;
                                                                          }
                                                                          else if (customfooter_type == "1" && customfooter_code =="") {
                                        alert("<p><font color=red size=2>Please provide the html code for custom footer. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                        return false;
                                    }
                                                                          else {
                                                                          document.ios_theme_detail.submit();
                                      return true;
                                      }
}





else {
if(document.getElementById("hid_logo").value==""&&logo.value=="") {
										   alert("<p><font color=red size=2>Please upload a valid logo for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
                                      }
									  else if(document.getElementById("hid_banner").value==""&&banner.value=="") {
										  alert("<p><font color=red size=2>Please upload a valid banner for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
									  }
                                      else if(logoext!="gif" && logoext!="jpg" && logoext!="jpeg" && logoext!="png" && logoext!="" ) {
                                          alert("<p><font color=red size=2>Please upload a valid logo for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
                                      }
                                      else if(bannerext!="gif" && bannerext!="jpg" && bannerext!="jpeg" && bannerext!="png" && bannerext!="") {
                                          alert("<p><font color=red size=2>Please upload a valid banner for mobile application. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							               return false;
                                      }
                                      else if(theme_background=="") {
									     alert("<p><font color=red size=2>Please slect a valid color for background. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_text=="") {
									     alert("<p><font color=red size=2>Please slect a valid color for default text. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_background === theme_text) {
                                        alert("<p><font color=red size=2>Application Background and Default Text Color can't be same.</font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                        return false;
                                      }
                                      else if(theme_header=="") {
									     alert("<p><font color=red size=2>Please slect a valid color for header. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_title=="") {
									     alert("<p><font color=red size=2>Please slect a valid color for title text. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if(theme_button=="") {
									     alert("<p><font color=red size=2>Please slect a valid color for application's button. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
							             return false;
									  }
									  else if (customfooter_type == "1" && customfooter_code =="") {
                                        alert("<p><font color=red size=2>Please provide the html code for custom footer. </font></p>",{title: "Mofluid : Theme Configuration", width: 400});
                                        return false;
                                    }
									  else {
									  document.ios_theme_detail.submit();
                                      return true;
                                      }
                                              }
							  }




