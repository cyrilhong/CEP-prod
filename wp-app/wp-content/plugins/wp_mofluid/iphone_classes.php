<?php	
class Iphone_app_class{
	public function save_iphone_configuration($args){
			global $wpdb;
		$table = 'mofluidadmin_iphone';
		
		$update_app = $wpdb->get_var( "SELECT mofluidadmin_id FROM $table" );
		
		$where = array('mofluidadmin_id' => $update_app);
		$data = array(
					'app_name' => $args['name'],
					'bundle_id' => $args['bundle_id'],
					'version' => $args['version'],
					'default_store' => $args['store'],
					'device_udid' => $args['device_udid'],
					'currency_type' => $args['currency_type'],
				);
				
		$wpdb->update( $table, $data, $where, $format = null, $where_format = null );
		$wpdb->update( 'mofluidadmin_details', array('mofluid_id'=>$args['mofluid_id'], 'mofluid_key'=>$args['mofluid_key']), $where, $format = null, $where_format = null );
		
		$build_data = array();
		$build_data['certificate_type'] = $args['certificate_type'];
		$build_data['certificate_passpharse'] = $args['mofluid_passpharse'];
		if($_FILES["mofluid_certificates"]["name"] != ''){
			$build_data['certificate_path'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["mofluid_certificates"]["name"];
			  if ($_FILES["mofluid_certificates"]["error"] > 0) {
				echo "Return Code: " . $_FILES["mofluid_certificates"]["error"] . "<br>";
			  } else {
				  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
					mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
				}			
				move_uploaded_file($_FILES["mofluid_certificates"]["tmp_name"],
				  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["mofluid_certificates"]["name"]);
				  echo "Stored in: " . "upload/" . $_FILES["mofluid_certificates"]["name"];
			  }
		}
		if($_FILES["mofluid_provisioning_profile"]["name"] != ''){
			$build_data['provisioning_profile'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["mofluid_provisioning_profile"]["name"];
			  if ($_FILES["mofluid_provisioning_profile"]["error"] > 0) {
				echo "Return Code: " . $_FILES["mofluid_provisioning_profile"]["error"] . "<br>";
			  } else {
				  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
					mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
				}			
				move_uploaded_file($_FILES["mofluid_provisioning_profile"]["tmp_name"],
				  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["mofluid_provisioning_profile"]["name"]);
				  echo "Stored in: " . "upload/" . $_FILES["mofluid_provisioning_profile"]["name"];
			  }
		}
		
		$wpdb->update( "mofluid_build_accounts", $build_data, array("mofluid_platform_id"=>1,'mofluid_admin_id' => $update_app), $format = null, $where_format = null );
		}
	
	public function save_iphone_icons_configuration(){
		global $wpdb;
		$table = 'mofluidadmin_iphone';
		
		$update_app = $wpdb->get_var( "SELECT mofluidadmin_id FROM $table" );
		$where = array('mofluidadmin_id' => $update_app);
		$up = wp_upload_dir();
		//$key_path = $up['baseurl'].'/wp_presscart/'.$_FILES["release_key"]["name"];
		
		if($_FILES["icon_small"]["name"] != ''){
				$data['icon_small'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_small"]["name"];
				  if ($_FILES["icon_small"]["error"] > 0) {
						echo "Return Code: " . $_FILES["icon_small"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_small"]["name"])) {
						  echo $_FILES["icon_small"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_small"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_small"]["name"]);
						  echo "Stored in: " . "upload/" . $_FILES["icon_small"]["name"];
						}
					  }
			}
		if($_FILES["icon_40"]["name"] != ''){
				$data['icon_40'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_40"]["name"];
				  if ($_FILES["icon_40"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_40"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_40"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_40"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_50"]["name"] != ''){
				$data['icon_50'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_50"]["name"];
				  if ($_FILES["icon_50"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_50"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_50"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_50"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_57"]["name"] != ''){
				$data['icon_57'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_57"]["name"];
				  if ($_FILES["icon_57"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_57"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_57"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_57"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_60"]["name"] != ''){
				$data['icon_60'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_60"]["name"];
				  if ($_FILES["icon_60"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_60"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_60"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_60"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_72"]["name"] != ''){
				$data['icon_72'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_72"]["name"];
				  if ($_FILES["icon_72"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_72"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_72"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_72"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_76"]["name"] != ''){
				$data['icon_76'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_76"]["name"];
				  if ($_FILES["icon_76"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_76"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_76"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_76"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_small_2x"]["name"] != ''){
				$data['icon_small_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_small_2x"]["name"];
				  if ($_FILES["icon_small_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_small_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_small_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_small_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_40_2x"]["name"] != ''){
				$data['icon_40_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_40_2x"]["name"];
				  if ($_FILES["icon_40_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_40_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_40_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_40_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_50_2x"]["name"] != ''){
				$data['icon_50_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_50_2x"]["name"];
				  if ($_FILES["icon_50_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_50_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_50_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_50_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_57_2x"]["name"] != ''){
				$data['icon_57_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_57_2x"]["name"];
				  if ($_FILES["icon_57_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_57_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_57_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_57_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_60_2x"]["name"] != ''){
				$data['icon_60_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_60_2x"]["name"];
				  if ($_FILES["icon_60_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_60_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_60_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_60_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_72_2x"]["name"] != ''){
				$data['icon_72_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_72_2x"]["name"];
				  if ($_FILES["icon_72_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_72_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_72_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_72_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["icon_76_2x"]["name"] != ''){
				$data['icon_76_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["icon_76_2x"]["name"];
				  if ($_FILES["icon_76_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_76_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["icon_76_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["icon_76_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["itunesartwork"]["name"] != ''){
				$data['itunesartwork'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["itunesartwork"]["name"];
				  if ($_FILES["itunesartwork"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["itunesartwork"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["itunesartwork"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["itunesartwork"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["itunesartwork_2x"]["name"] != ''){
				$data['itunesartwork_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["itunesartwork_2x"]["name"];
				  if ($_FILES["itunesartwork_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["itunesartwork_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["itunesartwork_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["itunesartwork_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		$wpdb->update( $table, $data, $where, $format = null, $where_format = null );
		//var_dump($data);
	}
	
public function save_iphone_spalsh_configuration(){
		global $wpdb;
		$table = 'mofluidadmin_iphone';
		
		$update_app = $wpdb->get_var( "SELECT mofluidadmin_id FROM $table" );
		$where = array('mofluidadmin_id' => $update_app);
		$up = wp_upload_dir();
		
		if($_FILES["screen_iphone_portrait"]["name"] != ''){
				$data['screen_iphone_portrait'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["screen_iphone_portrait"]["name"];
				  if ($_FILES["screen_iphone_portrait"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_iphone_portrait"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["screen_iphone_portrait"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_iphone_portrait"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["screen_iphone_portrait_2x"]["name"] != ''){
				$data['screen_iphone_portrait_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["screen_iphone_portrait_2x"]["name"];
				  if ($_FILES["screen_iphone_portrait_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_iphone_portrait_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["screen_iphone_portrait_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_iphone_portrait_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["screen_ipad_portrait"]["name"] != ''){
				$data['screen_ipad_portrait'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["screen_ipad_portrait"]["name"];
				  if ($_FILES["screen_ipad_portrait"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_portrait"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["screen_ipad_portrait"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_portrait"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["screen_ipad_portrait_2x"]["name"] != ''){
				$data['screen_ipad_portrait_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["screen_ipad_portrait_2x"]["name"];
				  if ($_FILES["screen_ipad_portrait_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_portrait_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["screen_ipad_portrait_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_portrait_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["screen_ipad_landscape"]["name"] != ''){
				$data['screen_ipad_landscape'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["screen_ipad_landscape"]["name"];
				  if ($_FILES["screen_ipad_landscape"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_landscape"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["screen_ipad_landscape"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_landscape"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["screen_ipad_landscape_2x"]["name"] != ''){
				$data['screen_ipad_landscape_2x'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["screen_ipad_landscape_2x"]["name"];
				  if ($_FILES["screen_ipad_landscape_2x"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_landscape_2x"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["screen_ipad_landscape_2x"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_ipad_landscape_2x"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
		if($_FILES["screen_iphone_default"]["name"] != ''){
				$data['screen_iphone_default'] = $up['baseurl'].'/wp_presscart/iphone/'.$_FILES["screen_iphone_default"]["name"];
				  if ($_FILES["screen_iphone_default"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart/iphone')) {
							mkdir('../wp-content/uploads/wp_presscart/iphone', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_iphone_default"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["screen_iphone_default"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/iphone/" . $_FILES["screen_iphone_default"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
			$wpdb->update( $table, $data, $where, $format = null, $where_format = null );
	}
	
	
	
		function save_iphone_theme_conf(){
				//var_dump($_POST);die;
				global $wpdb;
				$table = 'mofluidadmin_iphone';
				
				$update_app = $wpdb->get_var( "SELECT mofluidadmin_id FROM $table" );
				$where = array('mofluidadmin_id' => $update_app);
				$up = wp_upload_dir();
				$data = array(
							'theme' => $_POST['mofluid_theme'],
							'app_image_type' => $_POST['app_image_type'],
							//'logo' => $up['baseurl'].'/wp_presscart/'.$_FILES["logo"]["name"],
							//'banner' => $up['baseurl'].'/wp_presscart/'.$_FILES["banner"]["name"],
							'theme_background' => $_POST['theme_background'],
							'theme_text' => $_POST['theme_text'],
							'theme_header' => $_POST['theme_header'],
							'theme_title' => $_POST['theme_title'],
							'theme_button' => $_POST['theme_button'],
							'customfooter' => $_POST['customfooter'],
				);
				
				if($_FILES["logo"]["name"] != ''){
				$data['logo'] = $up['baseurl'].'/wp_presscart/'.$_FILES["logo"]["name"];
				if ($_FILES["logo"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart')) {
							mkdir('../wp-content/uploads/wp_presscart', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["logo"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["logo"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/" . $_FILES["logo"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
				if($_FILES["banner"]["name"] != ''){
				$data['banner'] = $up['baseurl'].'/wp_presscart/'.$_FILES["banner"]["name"];
				if ($_FILES["banner"]["error"] > 0) {
						//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
					  } else {
						  if (!file_exists('../wp-content/uploads/wp_presscart')) {
							mkdir('../wp-content/uploads/wp_presscart', 0777, true);
						}			
						if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["banner"]["name"])) {
						 // echo $_FILES["release_key"]["name"] . " already exists. ";
						} else {
						  move_uploaded_file($_FILES["banner"]["tmp_name"],
						  "../wp-content/uploads/wp_presscart/" . $_FILES["banner"]["name"]);
						 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
						}
					  }
			}
				
				if($_POST['app_image_type'] == 'custom'){
					
					$data['app_img_home'] = $_POST['hid_app_img_home'];
					$data['app_img_back'] = $_POST['hid_app_img_back'];
					
						if($_FILES["app_img_cross"]["name"] != ''){
							$data['app_img_cross'] = $up['baseurl'].'/wp_presscart/'.$_FILES["app_img_cross"]["name"];
							if ($_FILES["app_img_cross"]["error"] > 0) {
									//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
								  } else {
									  if (!file_exists('../wp-content/uploads/wp_presscart')) {
										mkdir('../wp-content/uploads/wp_presscart', 0777, true);
									}			
									if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["app_img_cross"]["name"])) {
									 // echo $_FILES["release_key"]["name"] . " already exists. ";
									} else {
									  move_uploaded_file($_FILES["app_img_cross"]["tmp_name"],
									  "../wp-content/uploads/wp_presscart/" . $_FILES["app_img_cross"]["name"]);
									 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
									}
								  }
						}
					
						if($_FILES["app_img_cart"]["name"] != ''){
							$data['app_img_cart'] = $up['baseurl'].'/wp_presscart/'.$_FILES["app_img_cart"]["name"];
							if ($_FILES["app_img_cart"]["error"] > 0) {
									//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
								  } else {
									  if (!file_exists('../wp-content/uploads/wp_presscart')) {
										mkdir('../wp-content/uploads/wp_presscart', 0777, true);
									}			
									if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["app_img_cart"]["name"])) {
									 // echo $_FILES["release_key"]["name"] . " already exists. ";
									} else {
									  move_uploaded_file($_FILES["app_img_cart"]["tmp_name"],
									  "../wp-content/uploads/wp_presscart/" . $_FILES["app_img_cart"]["name"]);
									 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
									}
								  }
						}
					
						if($_FILES["app_img_menu"]["name"] != ''){
							$data['app_img_menu'] = $up['baseurl'].'/wp_presscart/'.$_FILES["app_img_menu"]["name"];
							if ($_FILES["app_img_menu"]["error"] > 0) {
									//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
								  } else {
									  if (!file_exists('../wp-content/uploads/wp_presscart')) {
										mkdir('../wp-content/uploads/wp_presscart', 0777, true);
									}			
									if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["app_img_menu"]["name"])) {
									 // echo $_FILES["release_key"]["name"] . " already exists. ";
									} else {
									  move_uploaded_file($_FILES["app_img_menu"]["tmp_name"],
									  "../wp-content/uploads/wp_presscart/" . $_FILES["app_img_menu"]["name"]);
									 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
									}
									}
						}
					
					
					if($_FILES["app_img_del"]["name"] != ''){
							$data['app_img_del'] = $up['baseurl'].'/wp_presscart/'.$_FILES["app_img_del"]["name"];
							if ($_FILES["app_img_del"]["error"] > 0) {
									//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
								  } else {
									  if (!file_exists('../wp-content/uploads/wp_presscart')) {
										mkdir('../wp-content/uploads/wp_presscart', 0777, true);
									}			
									if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["app_img_del"]["name"])) {
									 // echo $_FILES["release_key"]["name"] . " already exists. ";
									} else {
									  move_uploaded_file($_FILES["app_img_del"]["tmp_name"],
									  "../wp-content/uploads/wp_presscart/" . $_FILES["app_img_del"]["name"]);
									 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
									}
									}
						}
					
					if($_FILES["app_img_search"]["name"] != ''){
							$data['app_img_search'] = $up['baseurl'].'/wp_presscart/'.$_FILES["app_img_search"]["name"];
							if ($_FILES["app_img_search"]["error"] > 0) {
									//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
								  } else {
									  if (!file_exists('../wp-content/uploads/wp_presscart')) {
										mkdir('../wp-content/uploads/wp_presscart', 0777, true);
									}			
									if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["app_img_search"]["name"])) {
									 // echo $_FILES["release_key"]["name"] . " already exists. ";
									} else {
									  move_uploaded_file($_FILES["app_img_search"]["tmp_name"],
									  "../wp-content/uploads/wp_presscart/" . $_FILES["app_img_search"]["name"]);
									 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
									}
									}
						}
				}
				if($_POST['customfooter'] == 1){
					$data['customfooter_code'] = $_POST['customfooter_code'];
				}
				
				$wpdb->update( $table, $data, $where, $format = null, $where_format = null );
				
		}
		
public function get_iphone_configuration(){
	global $wpdb;
	$tmp = $wpdb->get_row("SELECT * FROM mofluidadmin_iphone");
	return $tmp;
}

function save_iphone_push_conf(){
				global $wpdb;
				$table = 'mofluidpush_settings';
				
				$update_app = $wpdb->get_var( "SELECT mofluidadmin_id FROM $table" );
				$where = array('mofluidadmin_id' => $update_app);
				$up = wp_upload_dir();
				$data = array(
							'apn_mode' => $_POST['apn_mode'],
							'apn_certificate' => '',
							'apn_passphrase' => $_POST['apn_passphrase'],
				);
				
				if($_FILES["apn_pem_file"]["name"] != ''){
					$data['apn_certificate'] = $up['baseurl'].'/wp_presscart/'.$_FILES["apn_pem_file"]["name"];
					if ($_FILES["apn_pem_file"]["error"] > 0) {
							//echo "Return Code: " . $_FILES["release_key"]["error"] . "<br>";
						  } else {
							  if (!file_exists('../wp-content/uploads/wp_presscart')) {
								mkdir('../wp-content/uploads/wp_presscart', 0777, true);
							}			
							if (file_exists("../wp-content/uploads/wp_presscart/" . $_FILES["apn_pem_file"]["name"])) {
							 // echo $_FILES["release_key"]["name"] . " already exists. ";
							} else {
							  move_uploaded_file($_FILES["apn_pem_file"]["tmp_name"],
							  "../wp-content/uploads/wp_presscart/" . $_FILES["apn_pem_file"]["name"]);
							 // echo "Stored in: " . "upload/" . $_FILES["release_key"]["name"];
							}
						  }
				}
				
				$wpdb->update( $table, $data, $where, $format = null, $where_format = null );
				
		}
		
function generate_ios_app(){
	global $wpdb;
	$mofluid_user_results = $wpdb->get_results( 'SELECT * FROM mofluidadmin_details ORDER BY mofluidadmin_id DESC', OBJECT );
	$ios_build_data = $wpdb->get_results( 'SELECT * FROM mofluid_build_accounts WHERE mofluid_platform_id=1 ORDER BY mofluid_admin_id DESC', OBJECT );
	
	$platform_data = array();
	$androidAppObj = new Android_app_class();
	$buildandroid_config = $androidAppObj->get_android_configuration();
	$iphoneConf = $this->get_iphone_configuration();
	
	$keyData = json_decode($buildandroid_config->key_data);
	
	$upload_dir = wp_upload_dir();
	
	$platform_data["config"] = array(
		"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
		"mofluid_platform_id" => "1",
		"mofluid_app_name" => $iphoneConf->app_name,
		"mofluid_app_bundleid" => $iphoneConf->bundle_id,
		"mofluid_app_platform" => "ios",
		"mofluid_app_version" => $iphoneConf->version,
		"mofluid_default_store" => $iphoneConf->default_store,
		"mofluid_default_currency" => '{"code":"'.$iphoneConf->currency_type.'","symbol":"'.(strtoupper($iphoneConf->currency_type)=="USD"?base64_encode("$"):(strtoupper($iphoneConf->currency_type)=="INR"?base64_encode("Rs"):"")).'"}',
		"mofluid_default_theme" => "elegent"
	); 
	$platform_data["account"] = array(
		"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
		"mofluid_platform_id" => "1",
		"mofluid_id" => $mofluid_user_results[0]->mofluid_id,
		"mofluid_password" => $mofluid_user_results[0]->mofluid_key,
		"platform" => "ios",
		"phonegap_build_id" => "",
		"phonegap_build_password" => "",
		"certificate_type" => $ios_build_data[0]->certificate_type,
		"certificate_path" => $upload_dir['baseurl'].$ios_build_data[0]->certificate_path,
		"certificate_passpharse" => $ios_build_data[0]->certificate_passpharse,
		"provisioning_profile" => $upload_dir['baseurl'].$ios_build_data[0]->provisioning_profile,
		"release_key_type" => "0",
		"release_privatekey_password" => "",
		"release_keystore_password" => "",
		"release_key_validity" => "",
		"release_key_data" => "",
		"build_url" => "125.63.92.59/Woofluid/Production1.15.0/Platforms"
	); 
	$platform_data["assets"] = array(
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100001",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon small (29x29px)",
            "mofluid_assets_value" => $iphoneConf->icon_small,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100002",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 40 (40x40px)",
            "mofluid_assets_value" => $iphoneConf->icon_40,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100003",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 50 (50x50px)",
            "mofluid_assets_value" => $iphoneConf->icon_50,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100004",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 57 (57x57px)",
            "mofluid_assets_value" => $iphoneConf->icon_57,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100005",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 60 (60x60px)",
            "mofluid_assets_value" => $iphoneConf->icon_60,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100006",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 72 (72x72px)",
            "mofluid_assets_value" => $iphoneConf->icon_72,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100007",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 76 (76x76px)",
            "mofluid_assets_value" => $iphoneConf->icon_76,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100008",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon small @2x (58x58px)",
            "mofluid_assets_value" => $iphoneConf->icon_small_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100009",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 40 @2x (80x80px)",
            "mofluid_assets_value" => $iphoneConf->icon_40_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100010",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 50 @2x (100x100px)",
            "mofluid_assets_value" => $iphoneConf->icon_50_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100011",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 57 @2x (114x114px)",
            "mofluid_assets_value" => $iphoneConf->icon_57_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100012",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 60 @2x (120x120px)",
            "mofluid_assets_value" => $iphoneConf->icon_60_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100013",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 72 @2x (144x144px)",
            "mofluid_assets_value" => $iphoneConf->icon_72_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100014",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 76 @2x (152x152px)",
            "mofluid_assets_value" => $iphoneConf->icon_76_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "200001",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "artwork",
            "mofluid_assets_name" => "iTunesArtwork (512x512px)",
            "mofluid_assets_value" => $iphoneConf->itunesartwork,
            "mofluid_assets_isrequired" => "0",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "200002",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "artwork",
            "mofluid_assets_name" => "iTunesArtwork @2x 76 @2x (1024x1024px)",
            "mofluid_assets_value" => $iphoneConf->itunesartwork_2x,
            "mofluid_assets_isrequired" => "0",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300001",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_iphone_portrait (320x480px)",
            "mofluid_assets_value" => $iphoneConf->screen_iphone_portrait,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300002",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_iphone_portrait @2x (640x960px)",
            "mofluid_assets_value" => $iphoneConf->screen_iphone_portrait_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300003",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_portrait (768x1024px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_portrait,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300004",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_portrait @2x (1536x2048px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_portrait_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300005",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_landscape(1024x768px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_landscape,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300006",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_landscape @2x (2048x1536px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_landscape_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300007",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_iphone_default (640x1136px)",
            "mofluid_assets_value" => $iphoneConf->screen_iphone_default,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500001",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon drawable (96x96px)",
            "mofluid_assets_value" => $buildandroid_config->ico_drawable,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500002",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon hdpi (72x72px)",
            "mofluid_assets_value" => $buildandroid_config->ico_hdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500003",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon mdpi (48x48px)",
            "mofluid_assets_value" => $buildandroid_config->ico_mdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500004",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon ldpi (36x36px)",
            "mofluid_assets_value" => $buildandroid_config->ico_ldpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500005",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon xhdpi (96x96px)",
            "mofluid_assets_value" => $buildandroid_config->ico_xhdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600001",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_hdpi [Size : 480x800 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_hdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600002",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_ldpi [Size : 200x320 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_ldpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600003",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_mdpi[Size : 320x480 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_mdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600004",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_xhdpi [Size : 720x1280 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_xhdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600005",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_hdpi [Size : 800x480 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_hdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600006",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_ldpi [Size : 320x200 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_ldpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600007",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_mdpi [Size : 480x320 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_mdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600008",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_xhdpi [Size : 1280x720 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_xhdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        )
    );
	$mofluid_theme_data = array();
	$mofluid_theme_data["config"] = array(
		"mofluid_theme_id" => "1",
		"mofluid_store_id" => $buildandroid_config->default_store,
		"mofluid_theme_code" => strtolower($buildandroid_config->theme),
		"mofluid_theme_title" => ucfirst($buildandroid_config->theme),
		"mofluid_theme_status" => "1",
		"mofluid_theme_custom_footer" => $buildandroid_config->customfooter_code,
		"mofluid_display_catsimg" => "0",
		"mofluid_theme_display_custom_attribute" => $buildandroid_config->customfooter,
		"mofluid_theme_banner_image_type" => "1"
	);
	$mofluid_theme_data["colors"] = array(
		"foreground" => array(
			"product_name" => "000000",
            "product_description" => "000000",
            "price" => "EB5946",
            "category_heading" => "000000",
            "sub_category_heading" => "000000",
            "default_text" => "000000",
            "primary_button" => "000000",
            "secondary_button" => "000000",
            "table_primary_row_text" => "000000",
            "table_secondary_row_text" => "000000"
		),
		"background" => array(
			"featured_product_list" => "D1D1D1",
            "slider_menu" => "FFFFFF",
            "category_heading" => "FFFFFF",
            "sub_category_heading" => "FFFFFF",
            "default_background" => "FFFFFF",
            "primary_button" => "EB5946",
            "secondary_button" => "ADADAD",
            "table_primary_row_text" => "B5F8FF",
            "table_secondary_row_text" => "FFA8CB",
            "application_header" => "FFFFFF",
            "product_image_container" => "FFFFFF"
		)
	);
	$mofluid_theme_data["images"] = array(
		"logo" => array(
			"logo" => $buildandroid_config->logo
		),
		"banner" => array(
			"banner" => $buildandroid_config->banner
		),
		"themeicons" => array(
			"menu_32x32" => $buildandroid_config->app_img_menu,
            "back_32x32" => $buildandroid_config->app_img_back,
            "cart_32x32" => $buildandroid_config->app_img_cart,
            "search_32x32" => $buildandroid_config->app_img_search,
            "delete_32x32" => $buildandroid_config->app_img_del,
            "cross_32x32" => $buildandroid_config->app_img_cross,
            "cart_empty_150x150" => ""
		)
	);
	$mofluid_theme_data["messages"] = array(
		"button" => array(
			"home" => "Home",
			"my_account" => "My Account",
			"edit_profile" => "Edit Profile",
			"my_orders" => "My Orders",
			"sign_in" => "Sign In",
			"sign_out" => "Sign Out",
			"login" => "Login",
			"proceed" => "Proceed",
			"update" => "Update",
			"change_password" => "Change Password",
			"forgot_password" => "Forgot Password",
			"create_an_account" => "Create An Account",
			"details" => "Details",
			"add_to_cart" => "Add to Cart",
			"apply_coupon" => "Apply Coupon",
			"cancel_coupon" => "Cancel Coupon",
			"checkout" => "Checkout",
			"continue_shopping" => "Continue Shopping",
			"confirm_proceed" => "Confirm Proceed",
			"submit" => "Submit",
			"retrive_your_password" => "Retrive Your Password",
			"click_here" => "Click Here",
			"continue" => "Continue",
			"sign_up_now" => "Sign Up Now",
			"edit_information" => "Edit Information",
			"change_account_password" => "Change Account Password",
			"get_price" => "Get Price",
			"close" => "Close"
		),
		"alert" => array(
			"username_validation_for_blank" => "Username can't be left blank.",
			"username_validation_for_wrong_attempt" => "Please provide a valid email address as Username.",
			"customer_already_exist" => "Customer already exist.",
			"password_validation_for_minimum_length" => "Password should be atleast 6 characters long.",
			"invalid_username__password_message" => "The username or password you entered is incorrect",
			"login_success_message" => "Welcome {{username}} you have logged in successfully.",
			"server_not_responding" => "Server is busy please try after some time.",
			"sign_out_message" => "You have successfully log Out.",
			"out_of_stock_message" => "This product is out of stock.",
			"country_blank" => "Please enter the country",
			"firstname_blank" => "Please enter the firstname",
			"firstname_invalid" => "Please enter the valid firstname",
			"lastname_blank" => "Please enter the lastname",
			"lastname_invalid" => "Please enter the valid lastname",
			"contact_no_blank" => "Please enter the phone number",
			"empty_address" => "Please enter the address",
			"empty_city_message" => "Please enter the city",
			"empty_country_message" => "Please enter the country",
			"empty_state_message" => "Please enter the state",
			"empty_zipcode_message" => "Please enter the zipcode",
			"invalid_address" => "Please enter the valid address",
			"invalid_city_message" => "Please enter the valid city",
			"invalid_state_message" => "Please enter the valid state",
			"invalid_country_message" => "Please enter the valid country",
			"invalid_zipcode_message" => "Please enter the valid zipcode",
			"invalid_contact_message" => "Please enter the valid contact number",
			"email_not_registered" => "This email id is not registered with us.",
			"empty_email" => "Email Address can't be left blank.",
			"invalid_email" => "Please provide a valid email address.",
			"email_already_registered" => "This email id is already registered with us.",
			"forget_password_action_message" => "You will receive an email with a link to reset your password.",
			"confirm_password_different" => "Password doesn't match.",
			"change_password_success" => "Your password has been changed successfully",
			"register_successfully_message" => "You have registered successfully.",
			"profile_address_update" => "Default Billing and Shipping Address has been Updated.",
			"payment_mode" => "Please select mode of payment.",
			"configurable_options_validation" => "Please Select option from",
			"shipping_mode" => "Please select shipping method.",
			"item_remove_message" => "Please remove this item",
			"when_same_product_added_to_cart" => "This product is already added in your cart. Please visit cart to change the quantity of the product.",
			"when_a_product_removed_from_cart" => "{{product}} has been removed",
			"quantity_of_product_increased_more_than_stock" => "This Product is having only {{quantity}} quantities in stock",
			"product_out_of_stock" => "Sorry Product {{product}} is Out Of Stock. Please remove the product from cart.",
			"limited_quantity_is_in_stock_for_product" => "Sorry for Product {{product}} we have only {{quantity}} quanity in Stock please select less quantity.",
			"order_success_message" => "The order has been placed successfully",
			"failure_message_for_address_save" => "Sorry, Address are not Saved.Please Retry",
			"message_when_empty_search" => "Please provide any keyword for search.",
			"coupon_applied" => "Coupon code {{coupon}} was applied.",
			"coupon_failed" => "Coupon code {{coupon}} is not valid.",
			"coupon_canceled" => "{{coupon}} Coupon Canceled",
			"required_validation" => "Please fill all the required entries",
			"check_email" => "Please check your Email address.",
			"try_again" => "There was a temporary error please try again later.",
			"products_out_of_stock_message" => "Some products in your cart has been out of stock or we do not have required quantity at this time."
		),
		"text" => array(
			"display_application_name" => "",
			"support_text" => "Need help? 24 X 7 support",
			"policy_text" => "Policies",
			"shop_by_departments" => "",
			"personal_information" => "Personal Information",
			"billing_address" => "Billing Address",
			"no_default_billing_address_found" => "No Default Billing Address Found",
			"shipping_address" => "Shipping Address",
			"no_default_shipping_address" => "No Default Shipping Address",
			"full_name" => "Full Name",
			"first_name" => "First Name",
			"last_name" => "Last Name",
			"change_account_password" => "Change Account Password",
			"old_password" => "Old Password",
			"new_password" => "New Password",
			"confirm_password" => "Confirm Password",
			"address" => "Address",
			"member_since" => "Member Since",
			"edit_information" => "Edit Information",
			"email_address" => "Email Address",
			"contact_no" => "Contact No",
			"city" => "City",
			"state" => "State",
			"country" => "Country",
			"zipcode" => "Zipcode",
			"you_have_no_orders" => "You have no Orders",
			"total_orders" => "You have total {{totalorder}} Orders",
			"order" => "Order",
			"orders" => "Orders",
			"order_id" => "Order Id",
			"status" => "Status",
			"products" => "Products",
			"payment_method" => "Payment Method",
			"shipping_method" => "Shipping Method",
			"total_amount" => "Total Amount",
			"sku" => "SKU",
			"name" => "Name",
			"qty" => "Qty",
			"price" => "Price",
			"total" => "Total",
			"item" => "Item",
			"shipping_amount" => "Shipping Amount",
			"grand_total" => "Grand Total",
			"search_by_name" => "Search by Name",
			"search_result_text" => "Showing search result for {{serachstring}}",
			"no_search_result_found" => "No such product found",
			"position_sort_type" => "Position",
			"name_sort_type" => "Name",
			"price_sort_type" => "Price",
			"product_description" => "Product Description",
			"description" => "Description",
			"availability" => "Availability",
			"product_sku" => "Product SKU",
			"shipping_charge" => "Shipping Charge",
			"in_stock" => "In Stock",
			"out_of_stock" => "Out of Stock",
			"product_options" => "Product Options",
			"cart_empty_text" => "The Cart is empty now",
			"discount_codes" => "Discount Codes",
			"remove" => "Remove",
			"amount_payable" => "Amount Payable",
			"sender" => "Sender",
			"receiver" => "Receiver",
			"order_success" => "Thank You for placing your order with us. We'll do our best to deliver it to below address.",
			"coupon_code_text" => "Enter your coupon code if you have one.",
			"checkout_form_heading" => "Please fill the form to complete your order.",
			"same_billing_and_shipping_address_message" => "Shipping detail same as Billing details.",
			"different_shipping_address_message" => "Shipping to different address.",
			"save_to_address_book" => "Save to address book.",
			"authorizenet_redirect_message" => "Please wait, your order is being processed and you will be redirected to the Authorize.Net website.",
			"authorizenet_auto_redirect_message" => "If you are not automatically redirected to authorize.net within 5 seconds...",
			"click_here" => "Click Here",
			"paypal_redirect_message" => "Please wait, your order is being processed and you will be redirected to the paypal website",
			"paypal_auto_redirect_message" => "If you are not automatically redirected to paypal within 5 seconds...",
			"forget_password_message" => "Please provide your registered email to retrieve your password",
			"please_enter_your_username" => "Please enter your username",
			"please_enter_your_password" => "Please enter your password",
			"password" => "Password",
			"payment_information" => "Payment Information",
			"image" => "Image",
			"unit_price" => "Unit Price",
			"shipping__handling" => "Shipping & Handling",
			"discount" => "Discount",
			"terms_and_conditions" => "Terms and Conditions",
			"terms_and_conditions_message" => "I agree to the above terms and conditions.",
			"no_shipping_methods_message" => "Sorry, no quotes are available for this order at this time.",
			"no_product_found_text" => "Products will be added soon.",
			"discount_coupon" => "Discount (coupon).",
			"default" => "Default",
			"select" => "Select",
			"all_products" => "All Products",
			"tax" => "Tax",
			"no_payment_required" => "No Payment Method Required",
			"general" => "General",
			"short_description" => "Short Description",
			"full_description" => "Full Description",
			"payment_canceled" => "Sorry your payment has been canceled.",
			"reviews" => "Reviews",
			"order_summary" => "Order Summary",
			"date" => "Date",
			"message_for_invalid_payment_amount" => "Invalid amount for payment processing."
		)
	);
	$resource = array(
		"mofluid_build_accounts",
		"mofluid_build_config",
		"mofluid_build_assets",
		"mofluid_build_accounts",
		"mofluid_build_config",
		"mofluid_build_assets",
		"mofluid_build_accounts",
		"mofluid_build_config",
		"mofluid_build_assets",
		"mofluid_build_accounts",
		"mofluid_build_config",
		"mofluid_build_assets",
		"mofluid_build_accounts",
		"mofluid_build_config",
		"mofluid_build_assets",
		"mofluid_build_accounts",
		"mofluid_build_config",
		"mofluid_build_assets",
		"mofluidpush_settings",
		"mofluidpush_settings",
		"mofluidpush_settings",
		"mofluidpush_settings",
		"mofluidpush_settings",
		"mofluidpush_settings"
	);
	
	$data = array();
	$data["mofluid_build_accounts"] = array(
		array(
			"mofluid_admin_id" => $mofluid_user_results[0]->mofluidadmin_id,
            "mofluid_platform_id" => "1",
            "mofluid_id" => "",
            "mofluid_password" => $mofluid_user_results[0]->mofluid_key,
            "platform" => "ios",
            "phonegap_build_id" => "",
            "phonegap_build_password" => "",
            "certificate_type" => $ios_build_data[0]->certificate_type,
		"certificate_path" => $upload_dir['baseurl'].$ios_build_data[0]->certificate_path,
		"certificate_passpharse" => $ios_build_data[0]->certificate_passpharse,
		"provisioning_profile" => $upload_dir['baseurl'].$ios_build_data[0]->provisioning_profile,
            "release_key_type" => "1",
            "release_privatekey_password" => "",
            "release_keystore_password" => "",
            "release_key_validity" => "",
            "release_key_data" => "",
            "build_url" => "125.63.92.59/Woofluid/Production1.15.0/Platforms"	
		),
		array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
			"mofluid_platform_id" => "2",
			"mofluid_id" => $mofluid_user_results[0]->mofluid_id,
			"mofluid_password" => $mofluid_user_results[0]->mofluid_key,
			"platform" => "android",
			"phonegap_build_id" => "",
			"phonegap_build_password" => "",
			"certificate_type" => $ios_build_data[0]->certificate_type,
		"certificate_path" => $upload_dir['baseurl'].$ios_build_data[0]->certificate_path,
		"certificate_passpharse" => $ios_build_data[0]->certificate_passpharse,
		"provisioning_profile" => $upload_dir['baseurl'].$ios_build_data[0]->provisioning_profile,
			"release_key_type" => "0",
			"release_privatekey_password" => $buildandroid_config->privatekey_pswd,
			"release_keystore_password" => $buildandroid_config->keystore_pswd,
			"release_key_validity" => $buildandroid_config->key_validity,
			"release_key_data" => "{\"CN\":\"".$keyData->key_common_name."\",\"O\":\"".$keyData->key_org."\",\"OU\":\"".$keyData->key_org_unit."\",\"L\":\"".$keyData->key_city."\",\"ST\":\"".$keyData->key_state."\",\"C\":\"".$keyData->key_country."\"}",
			"build_url" => "125.63.92.59/Woofluid/Production1.15.0/Platforms"
		)
	);
	$data["mofluid_build_config"] = array(  
         array(  
            "mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_platform_id" => "1",
            "mofluid_app_name" => $iphoneConf->app_name,
            "mofluid_app_bundleid" => $iphoneConf->bundle_id,
            "mofluid_app_platform" => "ios",
            "mofluid_app_version" => $iphoneConf->version,
            "mofluid_default_store" => "",
            "mofluid_default_currency" => "",
            "mofluid_default_theme" => ""
         ),
         array(  
            "mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_platform_id" => "2",
            "mofluid_app_name" => $buildandroid_config->app_name,
            "mofluid_app_bundleid" => $buildandroid_config->bundle_id,
            "mofluid_app_platform" => "android",
            "mofluid_app_version" => $buildandroid_config->version,
            "mofluid_default_store" => $buildandroid_config->default_store,
            "mofluid_default_currency" => '{"code":"'.$buildandroid_config->currency_type.'","symbol":"'.(strtoupper($buildandroid_config->currency_type)=="USD"?base64_encode("$"):(strtoupper($buildandroid_config->currency_type)=="INR"?base64_encode("Rs"):"")).'"}',
            "mofluid_default_theme" => "elegant"
         )
    );
    $data["mofluid_build_assets"] = array(
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100001",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon small (29x29px)",
            "mofluid_assets_value" => $iphoneConf->icon_small,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100002",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 40 (40x40px)",
            "mofluid_assets_value" => $iphoneConf->icon_40,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100003",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 50 (50x50px)",
            "mofluid_assets_value" => $iphoneConf->icon_50,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100004",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 57 (57x57px)",
            "mofluid_assets_value" => $iphoneConf->icon_57,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100005",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 60 (60x60px)",
            "mofluid_assets_value" => $iphoneConf->icon_60,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100006",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 72 (72x72px)",
            "mofluid_assets_value" => $iphoneConf->icon_72,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100007",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 76 (76x76px)",
            "mofluid_assets_value" => $iphoneConf->icon_76,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100008",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon small @2x (58x58px)",
            "mofluid_assets_value" => $iphoneConf->icon_small_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100009",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 40 @2x (80x80px)",
            "mofluid_assets_value" => $iphoneConf->icon_40_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100010",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 50 @2x (100x100px)",
            "mofluid_assets_value" => $iphoneConf->icon_50_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100011",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 57 @2x (114x114px)",
            "mofluid_assets_value" => $iphoneConf->icon_57_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100012",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 60 @2x (120x120px)",
            "mofluid_assets_value" => $iphoneConf->icon_60_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100013",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 72 @2x (144x144px)",
            "mofluid_assets_value" => $iphoneConf->icon_72_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "100014",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "icon 76 @2x (152x152px)",
            "mofluid_assets_value" => $iphoneConf->icon_76_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "200001",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "artwork",
            "mofluid_assets_name" => "iTunesArtwork (512x512px)",
            "mofluid_assets_value" => $iphoneConf->itunesartwork,
            "mofluid_assets_isrequired" => "0",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "200002",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "artwork",
            "mofluid_assets_name" => "iTunesArtwork @2x 76 @2x (1024x1024px)",
            "mofluid_assets_value" => $iphoneConf->itunesartwork_2x,
            "mofluid_assets_isrequired" => "0",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300001",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_iphone_portrait (320x480px)",
            "mofluid_assets_value" => $iphoneConf->screen_iphone_portrait,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300002",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_iphone_portrait @2x (640x960px)",
            "mofluid_assets_value" => $iphoneConf->screen_iphone_portrait_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300003",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_portrait (768x1024px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_portrait,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300004",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_portrait @2x (1536x2048px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_portrait_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300005",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_landscape(1024x768px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_landscape,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300006",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_ipad_landscape @2x (2048x1536px)",
            "mofluid_assets_value" => $iphoneConf->screen_ipad_landscape_2x,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $iphoneConf->mofluidadmin_id,
            "mofluid_assets_id" => "300007",
            "mofluid_platform" => "ios",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "screen_iphone_default (640x1136px)",
            "mofluid_assets_value" => $iphoneConf->screen_iphone_default,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
		),
		array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500001",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon drawable (96x96px)",
            "mofluid_assets_value" => $buildandroid_config->ico_drawable,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500002",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon hdpi (72x72px)",
            "mofluid_assets_value" => $buildandroid_config->ico_hdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500003",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon mdpi (48x48px)",
            "mofluid_assets_value" => $buildandroid_config->ico_mdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500004",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon ldpi (36x36px)",
            "mofluid_assets_value" => $buildandroid_config->ico_ldpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "500005",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "icon",
            "mofluid_assets_name" => "Icon xhdpi (96x96px)",
            "mofluid_assets_value" => $buildandroid_config->ico_xhdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600001",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_hdpi [Size : 480x800 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_hdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600002",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_ldpi [Size : 200x320 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_ldpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600003",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_mdpi[Size : 320x480 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_mdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600004",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_port_xhdpi [Size : 720x1280 & Orientation : Portrait]",
            "mofluid_assets_value" => $buildandroid_config->drawable_port_xhdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600005",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_hdpi [Size : 800x480 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_hdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600006",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_ldpi [Size : 320x200 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_ldpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600007",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_mdpi [Size : 480x320 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_mdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        ),
        array(
			"mofluid_admin_id" => $buildandroid_config->mofluidadmin_id,
            "mofluid_assets_id" => "600008",
            "mofluid_platform" => "android",
            "mofluid_assets_type" => "splash",
            "mofluid_assets_name" => "drawable_land_xhdpi [Size : 1280x720 & Orientation : Landscape]",
            "mofluid_assets_value" => $buildandroid_config->drawable_land_xhdpi,
            "mofluid_assets_isrequired" => "1",
            "mofluid_assets_heptext" => ""
        )
    );
    ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
    $pushConf = $androidAppObj->get_android_push_conf();
    $data["mofluidpush_settings"] = array($pushConf);
	
	$requestedArr = array();
	$requestedArr["site_url"] = site_url()."/index.php/";
	$requestedArr["table_prefix"] = "";
	$requestedArr["mofluid_build_auth_key"] = base64_encode("@Woofluid1.15.0@_@Pro@_@ByEbizon@_@Anuj@");
	$requestedArr["platform_data"] = $platform_data;
	$requestedArr["mofluid_theme_data"] = $mofluid_theme_data;
	$requestedArr["resource"] = $resource;
	$requestedArr["data"] = $data;
	
	//echo "<pre>"; print_r($requestedArr); die;
	
	$json_file_name = "mofluid_buildrequest_".time().".json";
	$content = json_encode($requestedArr);
	$fp = fopen(__DIR__."/json/".$json_file_name,"wb");
	fwrite($fp,$content);
	fclose($fp);
	
	$request_file_path = site_url()."/wp-content/plugins/wp_mofluid/json/".$json_file_name;
	$data= new stdClass();
	$data->mofluidid = $requestedArr["platform_data"]["account"]["mofluid_id"];
	$data->source = $request_file_path;
	$data->build_app_type = $requestedArr["platform_data"]["account"]["certificate_type"];
	$downloadlink = $requestedArr["platform_data"]["account"]["build_url"].'/iOS/Controller.php?data='.base64_encode(json_encode($data));
	$downloadlink = str_replace(" ","%20",$downloadlink);
	$_h = curl_init(); 
	curl_setopt($_h, CURLOPT_HEADER, 1); 
	curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($_h, CURLOPT_HTTPGET, 1); 
	curl_setopt($_h, CURLOPT_URL, $downloadlink ); 
	curl_setopt($_h, CURLOPT_DNS_USE_GLOBAL_CACHE, false ); 
	curl_setopt($_h, CURLOPT_DNS_CACHE_TIMEOUT, 2 ); 
	$exec = curl_exec($_h);
	$resp = explode("\n\r\n", $exec);
	$res_msg = explode("\n", @$resp[1]);
	$json = implode("",$res_msg);
	curl_close($_h);
	$obj =$json;
	
	if($obj == "") {
		echo "Server Not Responding properly...Please Try Again after some time.";
	}
	else {
		print_r($obj);
	}
}
}
