<?php

class API_class {

    public function get_product_categories($flag = NULL) {
        $taxonomy = 'product_cat';
        $orderby = 'name';
        $show_count = 0;      // 1 for yes, 0 for no
        $pad_counts = 0;      // 1 for yes, 0 for no
        $hierarchical = 0;      // 1 for yes, 0 for no  
        $title = '';
        $empty = 0;
        $args = array(
            'taxonomy' => $taxonomy,
            'orderby' => $orderby,
            'show_count' => $show_count,
            'pad_counts' => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li' => $title,
            'hide_empty' => $empty,
            'parent' => 0
        );
        $all_categories = get_categories($args);
        $cat_arr = array();
        $count = 0;
        foreach ($all_categories as $key => $value) {
            $thumbnail_id = get_woocommerce_term_meta($value->term_id, 'thumbnail_id', true);
            $image = wp_get_attachment_url($thumbnail_id);
            $cat_arr[$count]['id'] = $value->term_id;
            $cat_arr[$count]['name'] = $value->name;
            $cat_arr[$count]['image'] = $image;
            $count++;
        }
        if ($flag == '1')
            return $cat_arr;
        else
            echo json_encode(array("categories" => $cat_arr));
        //echo  $_GET["callback"].json_encode($cat_arr);
    }

    /* change 1 */

    public function get_product_subcategory($cat_id) {
        $taxonomy = 'product_cat';
        $orderby = 'name';
        $show_count = 0;      // 1 for yes, 0 for no
        $pad_counts = 0;      // 1 for yes, 0 for no
        $hierarchical = 0;      // 1 for yes, 0 for no  
        $title = '';
        $empty = 0;
        
        $catInfo = get_term_by( 'id', $cat_id, 'product_cat', 'ARRAY_A' );
        //echo "<pre>"; print_r($catInfo);
        
        $args = array(
            'taxonomy' => $taxonomy,
            'orderby' => $orderby,
            'show_count' => $show_count,
            'pad_counts' => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li' => $title,
            'hide_empty' => $empty,
            'parent' => $cat_id
        );
        $all_categories = get_categories($args);
        $cat_arr = array();
        $count = 0;
        foreach ($all_categories as $key => $value) {
            $thumbnail_id = get_woocommerce_term_meta($value->term_id, 'thumbnail_id', true);
            $image = wp_get_attachment_url($thumbnail_id);
            $cat_arr[$count]['id'] = $value->term_id;
            $cat_arr[$count]['name'] = $value->name;
            $cat_arr[$count]['image'] = $image;
            $count++;
        }
        $new_catArr = array(
			"id" => $cat_id,
			"title" => $catInfo["name"],
			"images" => false,
			"categories"=>$cat_arr
		);
        echo $_GET["callback"] . "(" . json_encode($new_catArr) . ");";

        //echo json_encode($cat_arr);
    }

    /* public function get_product_subcategory($cat_id){	
      //woocommerce_product_subcategories();

      $cat = get_term( $cat_id, 'product_cat' );
      var_dump($cat);
      $thumbnail_id = get_woocommerce_term_meta( $cat_id, 'thumbnail_id', true );
      $image = wp_get_attachment_url( $thumbnail_id );
      $cat_arr[$count]['id'] = $cat_id;
      $cat_arr[$count]['name'] = $cat->name;
      $cat_arr[$count]['image'] = $image;

      echo json_encode($cat_arr);

      } */

    public function get_product_byID($cat_id) {

        /* i am not seeing any usage of this block
         * $posts = get_posts(
          array(
          'post_type'     => 'product',
          'numberposts'   => -1,
          'post_status'   => 'publish',
          'cat'      => $cat_id

          )
          ); */
//echo "<pre>"; print_r($_GET);
		global $wpdb;
        $args = array(
            'post_type' => 'product',
            'offset'=>trim($_GET['currentpage']-1)*trim($_GET['pagesize']),
            'posts_per_page' => trim($_GET['pagesize']),
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $cat_id,
                )
            )
        );
        
        if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'price' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
			$args["meta_key"] = "_price";
			$args["orderby"] = "meta_value_num";
			$args["order"] = "ASC";
		}else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'price' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
			$args["meta_key"] = "_price";
			$args["orderby"] = "meta_value_num";
			$args["order"] = "DESC";
		}else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'name' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
			$args["orderby"] = "post_title";
			$args["order"] = "ASC";
		}else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'name' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
			$args["orderby"] = "post_title";
			$args["order"] = "DESC";
		}else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'entity_id' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
			$args["orderby"] = "ID";
			$args["order"] = "ASC";
		}else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'entity_id' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
			$args["orderby"] = "ID";
			$args["order"] = "DESC";
		}
		
        //	var_dump($args);
        $loop = new WP_Query($args);
        $product_arr = array();
        $count = 0;
        
        $q = "SELECT count AS total FROM wp_term_taxonomy WHERE term_id = '".$_GET["categoryid"]."'";
		$r = $wpdb->get_results($q);
		$totalProducInCat = $r[0]->total;
		
        if ($loop->have_posts()) {
            while ($loop->have_posts()) : $loop->the_post();
                //var_dump( the_ID());die;
                $product = get_product();
                //var_dump($product);die;
                if ($product->product_type == "simple") {
					$product_arr[$count]['id'] = $product->id;
					$product_arr[$count]['name'] = $product->get_title();
					//$product_arr[$count]['imageurl'] = $product->get_image('shop_thumbnail' );
					$product_arr[$count]['imageurl'] = wp_get_attachment_url(get_post_thumbnail_id($product->id));
					$product_arr[$count]['sku'] = $product->get_sku();
					$product_arr[$count]['price'] = $product->get_regular_price();
					$product_arr[$count]['spclprice'] = strpos($product->get_sale_price(),'.')!==false?$product->get_sale_price():$product->get_sale_price().'.00';
					$product_arr[$count]['created_date'] = $product->post->post_date;
					if ($product->is_in_stock()) {
						$product_arr[$count]['is_in_stock'] = 1;
					} else {
						$product_arr[$count]['is_in_stock'] = 0;
					}

					$product_arr[$count]['stock_quantity'] = $product->get_stock_quantity();
					if ($product->product_type == "simple") {
						$product_arr[$count]['type'] = $product->product_type;
						$product_arr[$count]['hasoptions'] = 0;
					} else if ($product->product_type == "variable") {
						$product_arr[$count]['hasoptions'] = 1;

						$product_arr[$count]['type'] = "configurable";
					}



					$count++;
				}
            endwhile;
            $json_main['total'] = $totalProducInCat;
            if($count > 0){
                /*if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'price' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
                    usort($product_arr, function($a, $b) {
                        return $a['price'] - $b['price'];
                    });
                }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'price' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
                    usort($product_arr, function($a, $b) {
                        return $b['price'] - $a['price'];
                    });
                }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'name' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
                    usort($product_arr, function($a, $b) {
                        return strcmp(strtoupper($a["name"]), strtoupper($b["name"]));
                    });
                }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'name' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
                    usort($product_arr, function($a, $b) {
                        return strcmp(strtoupper($b["name"]), strtoupper($a["name"]));
                    });
                }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'entity_id' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
                    usort($product_arr, function($a, $b) {
                        return $a['id'] - $b['id'];
                    });
                }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'entity_id' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
                    usort($product_arr, function($a, $b) {
                        return $b['id'] - $a['id'];
                    });
                }*/
            }
            $json_main['data'] = $product_arr;
        } else {
            $json_main['total'] = $totalProducInCat;
        }

        echo $_GET["callback"] . "(" . json_encode($json_main) . ");";
        //echo json_encode($product_arr);
    }

    public function payment_methods() {
        global $wpdb;
        $payments_info = "SELECT * FROM mofluidpayment"; 
        $result = $wpdb->get_results($payments_info, ARRAY_A);

       
        foreach ($result as $key => $value) {
            
            $json_r['payment_method_id'] = $value['payment_method_id'];
            $json_r['payment_method_title'] = $value['payment_method_title'];
            $json_r['payment_method_code'] = $value['payment_method_code'];
            $json_r['payment_method_status'] = $value['payment_method_status']; //$value->title;
            $json_r['payment_method_mode'] = $value['payment_method_mode']; //$value->title;
            $json_r['payment_method_account_id'] = $value['payment_method_account_id'];
            $json_r['payment_method_account_key'] = '';
            $json_r['payment_account_email'] = $value['payment_account_email'];
            $json_r['payment_method_order_code'] = $value['payment_method_code'];//$value->id;
            
//            $account_details = $value->account_details;
//            if ($account_details[0]['account_number']) {
//                $json_r['payment_method_account_id'] = $account_details[0]['account_number'];
//            }
//            if ($account_details[0]['sort_code']) {
//                $json_r['payment_method_account_key'] = $account_details[0]['sort_code'];
//            }
//            if ($value->email) {
//                $json_r['payment_account_email'] = $value->email;
//            }


            //print_r($set['sort_code']);
            //print_r($json_r);

            $json_c[] = $json_r;
            ;
        }
        $json_main = $json_c;
        echo $_GET["callback"] . "(" . json_encode($json_main) . ");";
    }

    public function create_user($args) {
        $firstname = $args['firstname'];
        $lastname = $args['lastname'];
        $password = base64_decode($args['password']);
        $email = base64_decode($args['email']);
        
        // die($email);
        $categoryid = $args['categoryid'];
        $userdata = array(
            'user_login' => $email,
            'user_email' => $email,
            'display_name' => $firstname,
            'user_pass' => $password,
            'first_name' => $firstname,
            'last_name' => $lastname
        );

        $user_id = wp_insert_user($userdata);
        //var_dump($user_id);die;
        if (count($user_id->errors) > 0) {
            /* $user = get_userdatabylogin($username);
              $json_r['id']=$user->ID;
              $json_r['email']= $user->user_email; */
            $customer = get_user_by_email($email);
            if ($customer) {
                $cus_data = get_user_meta($customer->data->ID);
                $user_info['id'] = $customer->data->ID;
                $user_info['firstname'] = $cus_data['first_name'][0];
                $user_info['lasttname'] = $cus_data['last_name'][0];
                $user_info['email'] = $email;
                $user_info['password'] = $password;
                $user_info['status'] = 0;
            }
            //echo json_encode('User Exist Already');
            /* $user_info['id'] = get_userdata($user_id)->data->ID;
              $user_info['email'] = get_userdata($user_id)->data->user_email;
              $user_info['firstname'] = get_userdata($user_id)->first_name;
              $user_info['lastname'] = get_userdata($user_id)->last_name;
              $user_info['password'] = $password;
              $user_info['status'] = get_userdata($user_id)->data->user_status; */
        } else {
            $user_info['id'] = get_userdata($user_id)->data->ID;
            $user_info['email'] = get_userdata($user_id)->data->user_email;
            $user_info['firstname'] = get_userdata($user_id)->first_name;
            $user_info['lastname'] = get_userdata($user_id)->last_name;
            $user_info['password'] = $password;
            $user_info['status'] = 1; //get_userdata($user_id)->data->user_status;
            $this->send_userregistration_mail($user_info);
        }
        echo $_GET["callback"] . "(" . json_encode($user_info) . ");";
        //echo json_encode($user_info);
    }

    public function product_search($args) {
        global $wpdb;
        $search_data = $args['search_data'];
        $sorttype = $args['sorttype'];
        $sortorder = $args['sortorder'];

        $args = array(
            'post_type' => 'product',
            "s" => $search_data,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_sku',
                    'value' => $search_data,
                    'compare' => '='
                )
            )
        );

        $mypostids = $wpdb->get_col("select DISTINCT ID from $wpdb->posts p LEFT JOIN $wpdb->postmeta pm ON (p.ID = pm.post_id)  where post_type = 'product' AND ((post_title like '%$search_data%') OR (pm.meta_key = '_sku' AND pm.meta_value = '" . $search_data . "') ) ");
        $product_arr = array();
        $count = 0;
        foreach ($mypostids as $key => $value) {

            $product = get_product($value);
            $getCategories = $product->get_categories();
            if(!empty($getCategories)){
                $product_arr['data'][$count]['id'] = $product->id;
                $product_arr['data'][$count]['name'] = $product->get_title();
                $product_arr['data'][$count]['imageurl'] = wp_get_attachment_url(get_post_thumbnail_id($product->id));
                $product_arr['data'][$count]['sku'] = $product->get_sku();
                $product_arr['data'][$count]['hasoptions'] = '0';//$product->get_sku();
               // $product_arr['data'][$count]['currencysymbol'] = '$';//$product->get_sku();
                
                $product_arr['data'][$count]['type'] = $product->product_type;//$product->get_sku();
                
                $product_arr['data'][$count]['price'] = $product->get_regular_price();
                $product_arr['data'][$count]['spclprice'] = strpos($product->get_sale_price(),'.')!==false?$product->get_sale_price():$product->get_sale_price().'.00';
                $product_arr['data'][$count]['created_date'] = $product->post->post_date;
                $product_arr['data'][$count]['is_in_stock'] = $product->is_in_stock();
                $product_arr['data'][$count]['stock_quantity'] = $product->get_stock_quantity();
                $count++;
            }
        }
        $prod_total['total'] = $count;

        if($count > 0){
            if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'price' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
                usort($product_arr['data'], function($a, $b) {
                    return $a['price'] - $b['price'];
                });
            }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'price' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
                usort($product_arr['data'], function($a, $b) {
                    return $b['price'] - $a['price'];
                });
            }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'name' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
                usort($product_arr['data'], function($a, $b) {
                    return strcmp(strtoupper($a["name"]), strtoupper($b["name"]));
                });
            }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'name' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
                usort($product_arr['data'], function($a, $b) {
                    return strcmp(strtoupper($b["name"]), strtoupper($a["name"]));
                });
            }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'entity_id' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'asc'){
                usort($product_arr['data'], function($a, $b) {
                    return $a['id'] - $b['id'];
                });
            }else if(isset($_GET['sorttype']) && $_GET['sorttype'] == 'entity_id' && isset($_GET['sortorder']) && $_GET['sortorder'] == 'desc'){
                usort($product_arr['data'], function($a, $b) {
                    return $b['id'] - $a['id'];
                });
            }
        }

        if ($mypostids) {
            echo $_GET["callback"] . "(" . json_encode(array_merge($prod_total, $product_arr)) . ");";
        } else {
            echo $_GET["callback"] . "(" . json_encode(array("total"=>0)) . ");";
        }
    }

    public function verify_user_login($username, $password) {
        $json_r = '';

        if (user_pass_ok(base64_decode($username), base64_decode($password))) {
            $json_r['username'] = base64_decode($username);
            $json_r['password'] = base64_decode($password);
            $json_r['login_status'] = 1;
            //$customer = get_user_by_username($username );
            $user = get_userdatabylogin(base64_decode($username));
            $json_r['id'] = $user->ID;
            $json_r['email'] = $user->user_email;
            $customer = get_user_by_email($json_r['email']);
            if ($customer) {
                $cus_data = get_user_meta($customer->data->ID);
                $json_r['firstname'] = $cus_data['first_name'][0];
                $json_r['lasttname'] = $cus_data['last_name'][0];
            }
        } else {
            $json_r['username'] = base64_decode($username);
            $json_r['password'] = $password;
            $json_r['login_status'] = 0;
        }
        //echo"123";
        //echo  json_encode($json_r);
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
    }

    public function get_customer($email) {
        $json_cus = array();
        $customer = get_user_by_email($email);
        if ($customer) {
            $cus_data = get_user_meta($customer->data->ID);
            //var_dump($cus_data);die;
            $json_cus['id'] = $customer->data->ID;
            $json_cus['name'] = $cus_data['first_name'][0] . ' ' . $cus_data['last_name'][0];
            $json_cus['billing_address'] = $cus_data['billing_address_1'][0];
            $json_cus['billing_city'] = $cus_data['billing_city'][0];
            $json_cus['billing_postcode'] = $cus_data['billing_postcode'][0];
            $json_cus['billing_state'] = $cus_data['billing_state'][0];
            $json_cus['billing_country'] = $cus_data['billing_country'][0];
            $json_cus['billing_phone'] = $cus_data['billing_phone'][0];
        } else {
            $json_cus[] = 'Customer not found';
        }
        echo $_GET["callback"] . "(" . json_encode($json_cus) . ");";

        //echo json_encode($json_cus);			
    }

    public function get_myprofile($customerid) {
        $json_cus = array();
        $userinfo = get_userdata($customerid);
        if ($userinfo) {
            $cus_data = get_user_meta($userinfo->data->ID);
            //var_dump($cus_data);die;  
            $json_cus['CustomerInfo']['firstname'] = $userinfo->first_name;
            $json_cus['CustomerInfo']['lastname'] = $userinfo->last_name;
            $json_cus['CustomerInfo']['email'] = $userinfo->user_email; // $cus_data['billing_email'][0];
            $json_cus['CustomerInfo']['membersince'] = $userinfo->data->user_registered;

            $json_cus['BillingAddress']['firstname'] = $cus_data['billing_first_name'][0];
            $json_cus['BillingAddress']['lastname'] = $cus_data['billing_last_name'][0];
            $json_cus['BillingAddress']['street'] = $cus_data['billing_address_1'][0] . "\n" . $cus_data['billing_address_2'][0];
            $json_cus['BillingAddress']['city'] = $cus_data['billing_city'][0];
            $json_cus['BillingAddress']['pincode'] = $cus_data['billing_postcode'][0];
            $json_cus['BillingAddress']['region'] = $cus_data['billing_state'][0];
            $json_cus['BillingAddress']['countryid'] = $cus_data['billing_country'][0];
            $json_cus['BillingAddress']['contactno'] = $cus_data['billing_phone'][0];

            $json_cus['ShippingAddress']['firstname'] = $cus_data['shipping_first_name'][0];
            $json_cus['ShippingAddress']['lastname'] = $cus_data['shipping_last_name'][0];
            $json_cus['ShippingAddress']['street'] = $cus_data['shipping_address_1'][0] . "\n" . $cus_data['shipping_address_2'][0];
            $json_cus['ShippingAddress']['city'] = $cus_data['shipping_city'][0];
            $json_cus['ShippingAddress']['pincode'] = $cus_data['shipping_postcode'][0];
            $json_cus['ShippingAddress']['region'] = $cus_data['shipping_state'][0];
            $json_cus['ShippingAddress']['countryid'] = $cus_data['shipping_country'][0];
            $json_cus['ShippingAddress']['contactno'] = $cus_data['shipping_phone'][0];
            //var_dump($cus_data);die;
            /* $json_cus['CustomerInfo']['first_name'] = $cus_data['billing_first_name'][0];
              $json_cus['CustomerInfo']['last_name'] = $cus_data['billing_last_name'][0];
              $json_cus['CustomerInfo']['membersince'] = $userinfo->data->user_registered;
              $json_cus['CustomerInfo']['BillingAddress'][] = $cus_data['billing_address_1'][0];
              $json_cus['CustomerInfo']['BillingAddress'][] = $cus_data['billing_city'][0];
              $json_cus['CustomerInfo']['BillingAddress'][] = $cus_data['billing_postcode'][0];
              $json_cus['CustomerInfo']['BillingAddress'][] = $cus_data['billing_state'][0];
              $json_cus['CustomerInfo']['BillingAddress'][] = $cus_data['billing_country'][0];
              $json_cus['CustomerInfo']['BillingAddress'][] = $cus_data['billing_phone'][0];

              $json_cus['CustomerInfo']['ShippingAddress'][] = $cus_data['shipping_address_1'][0];
              $json_cus['CustomerInfo']['ShippingAddress'][] = $cus_data['shipping_city'][0];
              $json_cus['CustomerInfo']['ShippingAddress'][] = $cus_data['shipping_postcode'][0];
              $json_cus['CustomerInfo']['ShippingAddress'][] = $cus_data['shipping_state'][0];
              $json_cus['CustomerInfo']['ShippingAddress'][] = $cus_data['shipping_country'][0];
              $json_cus['CustomerInfo']['ShippingAddress'][] = $cus_data['billing_phone'][0]; */
        } else {
            $json_cus[] = 'User not found';
        }
        echo $_GET["callback"] . "(" . json_encode($json_cus) . ");";
        //echo json_encode($json_cus);			
    }

    public function get_currency() {
        global $woocommerce;
        // var_dump( get_woocommerce_currency());die;
        $currency_d = array();
        if (get_woocommerce_currency()) {
            $currency_d['currentcurrency'] = get_woocommerce_currency();
            $currency_d['basecurrency'] = get_woocommerce_currency();
            $currency_d['currentsymbol'] = get_woocommerce_currency_symbol();
            $currency_d['basesymbol'] = get_woocommerce_currency_symbol();
        } else {
            $json_cus[] = 'currency not found';
        }

        echo json_encode($currency_d);
    }

    public function change_password($args) {
        $json_r = '';
        $customerid = $args['customerid'];
        $username = base64_decode($args['username']);
        $oldpassword = base64_decode($args['oldpassword']);
        $newpassword = base64_decode($args['newpassword']);

        $json_r['customerid'] = $customerid;
        $json_r['oldpassword'] = $oldpassword;
        $json_r['newpassword'] = $newpassword;

        if (user_pass_ok($username, $oldpassword)) {//echo $username;die;
            $u = get_userdatabylogin($username);
            wp_set_password($newpassword, $u->data->ID);
            $json_r['change_status'] = 1;
            $json_r['message'] = "Password Changed Successfully";
        } else {
            $json_r['change_status'] = 0;
            $json_r['message'] = "Incorrect Old Password.";
        }
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //	echo json_encode($json_r);			
    }

    public function productsearchhelp() {
        $json_r = '';
        $args = array(
            'post_type' => 'product'
        );
        $posts = get_posts($args);
        if (!empty($posts)) {
            //var_dump($posts);die;
            $count = 0;
            foreach ($posts as $key => $value) {
                //echo $value->ID;die;
                $product = get_product($value->ID);
                $json_r[$count]['id'] = $product->id;
                $json_r[$count]['name'] = $product->get_title();
                $count++;
            }
        } else {
            $json_r[] = 'product not found';
        }
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
        //echo json_encode($json_r);			
    }

    public function productdetail($p_id) {

        $p_id = str_replace(array("stock_status0","stock_status1"), '', $p_id);

        $json_r = '';

        $product = get_product($p_id);


        //var_dump($product->product_type);;die;
        if (!empty($product)) {
            $p_category = get_the_terms($product->id, 'product_cat');

            if ($p_category) {
                $p_cat = array();
                foreach ($p_category as $key => $value) {
                    $p_cat[] = $value->term_id;
                }
                //var_dump($product->post->post_content);die;
                $json_r['id'] = $product->id;
                $json_r['sku'] = $product->get_sku();
                if ($product->product_type == "simple") {
                    $json_r['type'] = $product->product_type;
                    $json_r['has_custom_option'] = 0;
                } else if ($product->product_type == "variable") {
                    $json_r['has_custom_option'] = 1;

                    $json_r['type'] = "configurable";
                }
                $json_r['visibility'] = $product->is_visible();
                $json_r['weight'] = $product->get_weight();
                if ($product->is_on_sale())
                    $json_r['status'] = 1;
                else
                    $json_r['status'] = 0;
                $json_r['category'] = $p_cat;
                $json_r['general'] = array('name' => $product->get_title(), 'sku' => $product->get_sku(), 'weight' => $product->get_weight());
                $json_r['price'] = array('regular' => ($product->regular_price?$product->regular_price:0.00), 'final' => ($product->sale_price?$product->sale_price:$product->regular_price));
                $count = 0;
                $json_r['image'][$count] = wp_get_attachment_url(get_post_thumbnail_id($product->id));
                $gal_image_id = $product->get_gallery_attachment_ids();
                foreach ($gal_image_id as $key => $val) {
                    $gal_image = wp_get_attachment_image_src($val);
                    $json_r['image'][++$count] = $gal_image[0];
                }
                $json_r['reviews'] = array('total' => '');
                $json_r['description'] = array('short' => base64_encode($product->post->post_excerpt), 'full' => base64_encode($product->post->post_content));

                $json_r['url'] = $product->get_permalink();
                $stock_enable = get_option('woocommerce_manage_stock');
                $is_in_stock = 0;
                if ($stock_enable == 'no'){
                    $prod_qty = '1';
                    $is_in_stock = 1;
                }
                $json_r['stock'] = array("item_id" => $product->id,
                    "product_id" => $product->id,
                    "stock_id" => "",
                    "qty" => $prod_qty,
                    "min_qty" => $prod_qty,
                    "use_config_min_qty" => "",
                    "is_qty_decimal" => "",
                    "backorders" => "",
                    "use_config_backorders" => "",
                    "min_sale_qty" => "",
                    "use_config_min_sale_qty" => "",
                    "max_sale_qty" => "",
                    "use_config_max_sale_qty" => "",
                    "is_in_stock" => $is_in_stock,
                    "low_stock_date" => null,
                    "notify_stock_qty" => null,
                    "use_config_notify_stock_qty" => "",
                    "manage_stock" => "",
                    "use_config_manage_stock" => "",
                    "stock_status_changed_auto" => "",
                    "use_config_qty_increments" => "",
                    "qty_increments" => "",
                    "use_config_enable_qty_inc" => "",
                    "enable_qty_increments" => "",
                    "is_decimal_divided" => "",
                    "type_id" => "simple",
                    "stock_status_changed_automatically" => "",
                    "use_config_enable_qty_increments" => ""
                );
                $array1 = array(
                    "code" => "",
                    "label" => "",
                    "value" => ""
                );
                $array2 = array(
                    "code" => "",
                    "label" => "",
                    "value" => ""
                );
                $data["data"] = array($array1, $array2);

                if ($stock_enable == 'yes'){
                    if(get_post_meta($product->id, '_manage_stock', TRUE) == 'yes' && get_post_meta($product->id, '_stock', TRUE) > 0){
                        $json_r['stock']['qty'] = intval(get_post_meta($product->id, '_stock', TRUE));
                        $json_r['stock']['min_qty'] = intval(get_post_meta($product->id, '_stock', TRUE));
                        $json_r['stock']['is_in_stock'] = 1;
                    }else{
                        if(get_post_meta($product->id, '_stock_status', TRUE) == 'instock'){
                            $json_r['stock']['qty'] = 99;
                            $json_r['stock']['min_qty'] = 99;
                            $json_r['stock']['is_in_stock'] = 1;
                        }
                    }
                    $prod_qty = $product->get_stock_quantity();
                }

                $json_r['custom'] = array("options" => array("status" => false),
                    "attributes" => $data);
                $json_r['products'] = array("related" => array("status" => '0'));
                $json_r['shipping'] = 0.00;
                $json_r['sprice'] = 0.00;
            }
            else {
                //$json_r[] = 'product not found';
                echo 'Error'; exit;
            }
        } else {
            //$json_r[] = 'product not found';
            echo 'Error'; exit;
        }
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //echo json_encode($json_r);			
    }

    public function forgotPassword($email) {
        
        $customer = get_user_by_email(base64_decode($email));
        //global $wpdb;
        if ($customer) {
            $this->retrieve_password($customer->data->user_login);
            $json_r['response'] = 'success';
        } else {
            $json_r['response'] = 'error';
        }
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
        //echo json_encode($json_r);			
    }

    public function storedetails() {
        global $wpdb;
        $store_arr = array();

        /*         * ****************************************************************************************** */
//Store Details                        
        $store_arr['store_id'] = '';
        $store_arr['code'] = '';
        $store_arr['website_id'] = '';
        $store_arr['group_id'] = '';
        $store_arr['sort_order'] = '';
        $store_arr['is_active'] = '1';
        $store_arr['frontname'] = get_bloginfo('name');
        $store_arr['name'] = get_bloginfo('name');
        $store_arr['email'] = get_bloginfo('admin_email');
        $store_arr['rooturl'] = get_bloginfo('url');
        $store_arr['storeurl'] = get_bloginfo('url');
        $store_arr['adminname'] = get_bloginfo('name');
        $store_arr['cache_setting'] = array('cache_time'=>'30','status'=>'1');
        $store = array('store' => $store_arr);
        /*         * ********************************************************************************************* */
        //Time Zone
        // $blogtime = current_time( 'mysql' ); 
        // list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $blogtime);
        $time_zone = get_option('timezone_string');
        $timezone = array("timezone" => array("name" => $time_zone));
        /*         * ********************************************************************************************* */
// URL                                


        /*         * ********************************************************************************************* */
// Product Category                                
        $prod_cat = array("categories" => $this->get_product_categories('1'));
        /*         * ******************************************************************************************** */
// Theme Details                        
        $theme_info = $wpdb->get_row("SELECT * FROM mofluidadmin_android");
//                       echo '<pre>';
//                       print_r($theme_info); 
        $theme_res["code"] = $theme_info->theme;
        $theme_res["logo"] = array("image" => array(array("mofluid_image_value" => $theme_info->logo),
            ),
            "alt" => "Mufluid Commerce"
        );

        $theme_res["banner"] = array("image" => array(array(
                    "mofluid_image_value" => $theme_info->banner,
                ),
            )
        );
        $theme_res_final = array("theme" => $theme_res);
        //$image['mofluid_theme_id'] =  
        /*         * ******************************************************************************************** */
// Merge All Arrays:
        $out_array = array_merge($store, $timezone, $prod_cat, $theme_res_final);
        echo $_GET["callback"] . "(" . json_encode($out_array) . ");";
    }

    public function countryList() {
        $c = new WC_Countries();
        $countrylist = $c->get_countries();
        $count = 0;
        foreach ($countrylist as $key => $value) {
            $json_r[$count]['country_id'] = $key;
            $json_r[$count++]['country_name'] = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
        }
        $json_main['mofluid_countries'] = $json_r;
        $base_country = $c->get_base_country();
        $json_main['mofluid_default_country']['country_id'] = $base_country;

        //echo json_encode($json_main);
        echo $_GET["callback"] . "(" . json_encode($json_main) . ");";
        //echo json_encode($c->get_shipping_countries( ));			
    }

    public function stateList($country_code = ''){
        if(!empty($country_code)){
            $c = new WC_Countries();
            $statelist = $c->get_states($country_code);
            $count = 0;
            foreach ($statelist as $key => $value) {
                $json_r[$count]['region_id'] = $key;
                $json_r[$count++]['region_name'] = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
            }
            $json_main['mofluid_regions'] = $json_r;

            //echo json_encode($json_main);
            echo $_GET["callback"] . "(" . json_encode($json_main) . ");";
        }
    }

    public function productQuantity($product_ids) {

        $json_r = array();
        //var_dump($product_ids);die;
        $product_ids = htmlspecialchars_decode($product_ids);
        $p_id = json_decode(stripslashes($product_ids), true);
        //$p_id=array(0=>36);	
        // var_dump($p_id);die;
        foreach ($p_id as $key => $value) {
            $product = get_product($value);
            if (!empty($product))
                $json_r[$value] = $product->get_stock_quantity();
            //var_dump($product->get_stock_quantity());
        }

        if (count($json_r) < 1)
            $json_r = FALSE;


        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
        //echo json_encode($json_r);			
    }

    public function createorder($args) {
        //echo '<pre>'; print_r($args); die;
        global $woocommerce;
       $address = base64_decode($args['address']);
       $addressarr = json_decode($address, TRUE);
       
        $customerid = $args['customerid']; //	
        $shipmethod = $args['shipmethod']; //	
        $paymentmethod = $args['paymentmethod']; //	
        $shippcharge = $args['shippcharge'];  //
        $transactionid = $args['transactionid']; //
        $couponCode = isset($args['couponCode'])?$args['couponCode']:'';	
        /* $args['products']['id']=29;
          $args['products']['sku']=5;
          $args['products']['qty']=1; */
        //$args['products']="{"id":["29","36","9"],"sku":["5","1456","10"],"qty":[1,"2",1],"customoptions":[{},null,null]}";
        //print_r(base64_decode($args['products']));
        
        $products = json_decode(stripslashes(base64_decode($args['products'])), true);
       // echo(sizeof($products));
 //die;//print_r($products); die;
        $user_info = get_userdata($customerid);
        if ($user_info) {


            // build order data
            $order_date = date_create();
            //var_dump($order_date);die;
            $order_data = array(
                'post_name' => 'order-' . date_format($order_date, 'M-d-Y-hi-a'), //'order-jun-19-2014-0648-pm'
                'post_type' => 'shop_order',
                'post_title' => 'Order &ndash; ' . date_format($order_date, 'F d, Y @ h:i A'), //'June 19, 2014 @ 07:19 PM'
                'post_status' => 'publish',
                'ping_status' => 'closed',
                'post_author' => $customerid,
                'post_date' => date('Y-m-d H:i:s'), //'order-jun-19-2014-0648-pm'
                'comment_status' => 'open'
            );

            $order_id = wp_insert_post($order_data, true);

            $order = new stdClass();

            if (is_wp_error($order_id)) {

                $order->errors = $order_id;
            } else {
                $orderObj = new WC_Order($order_id);
                if(strtoupper($paymentmethod) == 'COD'){
                    $orderObj->update_status('processing');
                }

                $order->imported = true;

                // add a bunch of meta data
                add_post_meta($order_id, 'transaction_id', $transactionid, true);
                add_post_meta($order_id, '_payment_method_title', strtoupper($paymentmethod) . ' Payment', true);
                add_post_meta($order_id, '_payment_method', $paymentmethod, true);
                add_post_meta($order_id, '_order_shipping', $shippcharge, true);
                add_post_meta($order_id, '_customer_user', $customerid, true);
                add_post_meta($order_id, '_completed_date', date_format($order_date, 'Y-m-d H:i:s e'), true);
                add_post_meta($order_id, '_order_currency', get_woocommerce_currency(), true);
                add_post_meta($order_id, '_paid_date', date_format($order_date, 'Y-m-d H:i:s e'), true);
                // add_post_meta($order_id, '_sku', $order_id, true);
                //add_post_meta($order_id, '_order_total', $order_id, true);

               // $cus_data = get_user_meta($customerid);
//print_r($cus_data);


                add_post_meta($order_id, '_billing_address_1', $addressarr['billing']['street'], true);
                add_post_meta($order_id, '_billing_city', $addressarr['billing']['city'], true);
                add_post_meta($order_id, '_billing_state', $addressarr['billing']['region'], true);
                add_post_meta($order_id, '_billing_postcode', $addressarr['billing']['postcode'], true);
                add_post_meta($order_id, '_billing_country', $addressarr['billing']['country'], true);
                add_post_meta($order_id, '_billing_email', $addressarr['billing']['email'], true);
                add_post_meta($order_id, '_billing_first_name', $addressarr['billing']['firstname'], true);
                add_post_meta($order_id, '_billing_last_name', $addressarr['billing']['lastname'], true);
                add_post_meta($order_id, '_billing_phone', $addressarr['billing']['phone'], true);
                
                
                add_post_meta($order_id, '_shipping_address_1', $addressarr['shipping']['street'], true);
                add_post_meta($order_id, '_shipping_city', $addressarr['shipping']['city'], true);
                add_post_meta($order_id, '_shipping_state', $addressarr['shipping']['region'], true);
                add_post_meta($order_id, '_shipping_postcode', $addressarr['shipping']['postcode'], true);
                add_post_meta($order_id, '_shipping_country', $addressarr['shipping']['country'], true);
                add_post_meta($order_id, '_shipping_first_name', $addressarr['shipping']['firstname'], true);
                add_post_meta($order_id, '_shipping_last_name', $addressarr['shipping']['lastname'], true);
                add_post_meta($order_id, '_shipping_phone', $addressarr['shipping']['phone'], true);
                add_post_meta($order_id, '_shipping_email', $addressarr['shipping']['email'], true);
                // get product by item_id



                $total = 0;
                
                for ($i = 0; $i < sizeof($products); $i++) {
                    
                    $product = new WC_Product($products[$i]['id']);

                    $total += ($product->sale_price?$product->sale_price:($product->regular_price?$product->regular_price:"0.00")) * $products[$i]['quantity'];
                    $item_id = wc_add_order_item($order_id, array(
                        'order_item_name' => $product->get_title(),
                        'order_item_type' => 'line_item'
                            ));
                    $item_id_2 = wc_add_order_item($order_id, array(
                        'order_item_name' => $shipmethod,
                        'order_item_type' => 'shipping'
                            ));
                    //var_dump($item_id);die;
                    //var_dump($products['qty'][$i]);

                    //print_r($products[$i]); die;
                    wc_add_order_item_meta($item_id, '_qty', $products[$i]['quantity']);
                    wc_add_order_item_meta($item_id, '_tax_class', $product->get_tax_class());
                    wc_add_order_item_meta($item_id, '_product_id', $products[$i]['id']);
                    wc_add_order_item_meta($item_id, 'Cost', ($product->sale_price?$product->sale_price:($product->regular_price?$product->regular_price:"0.00")));
                    wc_add_order_item_meta($item_id, '_line_subtotal', ($product->sale_price?$product->sale_price:($product->regular_price?$product->regular_price:"0.00")) * $products[$i]['quantity']);
                    wc_add_order_item_meta($item_id, '_line_total', ($product->sale_price?$product->sale_price:($product->regular_price?$product->regular_price:"0.00")) * $products[$i]['quantity']);
                }//die;
                // add item meta data
                add_post_meta($order_id, '_order_total', $total, true);
                // wc_add_order_item_meta( $item_id, '_variation_id', '' );
                //wc_add_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $order->gross ) );
                // wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $order->gross ) );
                //wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( 0 ) );
                //wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( 0 ) );
//echo $total;die;
                //$or = new WC_Order();


                if(!empty($couponCode)){
                    $couponData = $this->couponDetails($args,true);
                    if($couponData['status'] == 1 && $couponData['is_active'] == 1 && $couponData['simple_action'] == 'fixed_cart'){
                        update_post_meta($order_id, '_order_total', $total - $couponData['discount_amount'], $total);
                        $coupon_discount = $couponData['discount_amount'];
                        $orderObj->add_coupon( $couponCode, $coupon_discount);
                        add_post_meta($order_id, '_order_discount', $coupon_discount, true);
                        add_post_meta($order_id, '_cart_discount', $coupon_discount, true);
                        add_post_meta($order_id, 'couponUsed', 1, true);
                        add_post_meta($order_id, 'couponCode', $couponCode, true);
                    }else if($couponData['status'] == 1 && $couponData['is_active'] == 1 && $couponData['simple_action'] == 'percent'){
                        update_post_meta($order_id, '_order_total', $total - $total*$couponData['discount_amount']/100, $total);
                        $coupon_discount = $total*$couponData['discount_amount']/100;
                        $orderObj->add_coupon( $couponCode, $coupon_discount);
                        add_post_meta($order_id, '_order_discount', $coupon_discount, true);
                        add_post_meta($order_id, '_cart_discount', $coupon_discount, true);
                        add_post_meta($order_id, 'couponUsed', 1, true);
                        add_post_meta($order_id, 'couponCode', $couponCode, true);
                    }
                }
            }
            $json_r['status'] = 1;
            $json_r['id'] = $order_id;
            $json_r['orderid'] = $order_id;
            $json_r['transid'] = $transactionid;
        } else {
            $json_r[] = 'Customer not found';
        }
        if($order_id){
            $sendmail_args = array('orderid' => $order_id);
            $this->sendordermail($sendmail_args);
        }
        //var_dump($order_id);die;

        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //echo json_encode($json_r);			
    }

    public function setaddress($args) {
        $json_r = array();
        //var_dump($args);
        $cst_id = $args['customerid'];
        $address = base64_decode($args['address']);
        $address = json_decode($address);
        $billAdd = $address->billing;
        $shippAdd = $address->shipping;
        
        $u = get_userdata($cst_id);
        
        //var_dump($u);
        if ($u) {
            //var_dump($u);die;
            update_user_meta($cst_id, 'billing_first_name', stripslashes($billAdd->firstname));
            update_user_meta($cst_id, 'billing_last_name', stripslashes($billAdd->lastname));
            update_user_meta($cst_id, 'billing_address_1', stripslashes($billAdd->street));
            update_user_meta($cst_id, 'billing_city', stripslashes($billAdd->city));
            update_user_meta($cst_id, 'billing_postcode', stripslashes($billAdd->postcode));
            update_user_meta($cst_id, 'billing_state', stripslashes($billAdd->region));
            update_user_meta($cst_id, 'billing_country', stripslashes($billAdd->country));
            update_user_meta($cst_id, 'billing_phone', stripslashes($billAdd->phone));
            update_user_meta($cst_id, 'billing_email', stripslashes($billAdd->email));

            update_user_meta($cst_id, 'shipping_first_name', stripslashes($billAdd->firstname));
            update_user_meta($cst_id, 'shipping_last_name', stripslashes($billAdd->lastname));
            update_user_meta($cst_id, 'shipping_address_1', stripslashes($billAdd->street));
            update_user_meta($cst_id, 'shipping_city', stripslashes($billAdd->city));
            update_user_meta($cst_id, 'shipping_postcode', stripslashes($billAdd->postcode));
            update_user_meta($cst_id, 'shipping_state', stripslashes($billAdd->region));
            update_user_meta($cst_id, 'shipping_country', stripslashes($billAdd->country));
            update_user_meta($cst_id, 'shipping_phone', stripslashes($billAdd->phone));
            update_user_meta($cst_id, 'shipping_email', stripslashes($billAdd->email));
            $json_r['shippaddress'] = 1;
            $json_r['billaddress'] = 1;
        } else {
            $json_r['shippaddress'] = 0;
            $json_r['billaddress'] = 0;
        }
        //$cust = new WC_Customer(1);
        // var_dump($cust->get_shipping_city( ));die;
        //{'billing_address_1':'noida','billing_city':'noida12','billing_postcode':'123456','billing_state':'UP','billing_country':'IN','billing_phone':'9876765','billing_email':'vishal@test.com','shipping_address_1':'sfdfd','shipping_city':'dfdgdfgg','shipping_postcode':'23432423','shipping_state':'UP','shipping_country':'IN'}
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //echo json_encode($json_r);			
    }

    public function setprofile($args) {
        //die('sfdsfs');
        $json_r = array();

        $cst_id = $args['customerid'];
        $billAdd = json_decode("{".stripslashes($args['billaddress'])."}", TRUE);

        $shippAdd = json_decode("{".stripslashes($args['shippaddress'])."}", TRUE);
        //$profile = json_decode("{".stripslashes($args['profile'])."}", TRUE);
        $profile = $args['profile'];
        $u = get_userdata($cst_id);

        if ($u) {
            //var_dump($u);die;
            update_user_meta($cst_id, 'billing_first_name', $billAdd['billfname']);
            update_user_meta($cst_id, 'billing_last_name', $billAdd['billlname']);

            update_user_meta($cst_id, 'billing_address_1', $billAdd['billstreet1']);
            update_user_meta($cst_id, 'billing_address_2', $billAdd['billstreet2']);
            update_user_meta($cst_id, 'billing_city', $billAdd['billcity']);
            update_user_meta($cst_id, 'billing_postcode', $billAdd['billpostcode']);
            update_user_meta($cst_id, 'billing_state', $billAdd['billstate']);
            update_user_meta($cst_id, 'billing_country', $billAdd['billcountry']);
            update_user_meta($cst_id, 'billing_phone', $billAdd['billphone']);
            update_user_meta( $cst_id, 'billing_email', $billAdd['email'] );

            update_user_meta($cst_id, 'shipping_first_name', $shippAdd['shippfname']);
            update_user_meta($cst_id, 'shipping_last_name', $shippAdd['shipplname']);
            update_user_meta($cst_id, 'shipping_address_1', $shippAdd['shippstreet1']);
            update_user_meta($cst_id, 'shipping_address_2', $shippAdd['shippstreet2']);
            update_user_meta($cst_id, 'shipping_city', $shippAdd['shippcity']);
            //update_user_meta( $cst_id, 'shipping_city', $shippAdd['shippstate'] );
            update_user_meta($cst_id, 'shipping_postcode', $shippAdd['shipppostcode']);
            update_user_meta($cst_id, 'shipping_state', $shippAdd['shippstate']);
            update_user_meta($cst_id, 'shipping_country', $shippAdd['shippcountry']);
            update_user_meta($cst_id, 'shipping_phone', $shippAdd['shippphone']);
            //update_user_meta( $cst_id, 'shipping_country', $shippAdd['shippphone'] );

            $userdata = array(
                'ID' => $cst_id,
                'first_name' => $profile->fname,
                'last_name' => $profile->lname,
                'user_email' => base64_decode($profile->email)
            );
            
            wp_update_user($userdata);
            $json_r['userprofile'] = 1;
            
        } else {
            $json_r['userprofile'] = 0;
        }
        
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //echo json_encode($json_r);			
    }

    public function termCondition() {
        $json_r[] = '';
        //echo json_encode($json_r);	
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
    }

    public function validate_currency($args) {
        $currency = $args['currency'];
        //paypal supported currency codes 
        $currency_code = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'TRY', 'USD',
        );
        //print_r( $args);
        //echo $currency;
        foreach ($currency_code as $k => $val) {

            if ($val == $currency) {
                $json_r['status'] = '1';
                break;
            }
        }
        //$json_r['status']='1';
        $json_r['msg'] = 'Demo';
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
    }

    public function listShipping() {
        global $woocommerce;
        //$json_r[] = array();
        $shipping = new WC_Shipping();
        $ships = $shipping->load_shipping_methods();
        foreach ($ships as $key => $value) {
            if ($value->enabled == 'yes') {
                $json_r[$value->id]['method'] = $value->method_title;
                $json_r[$value->id]['title'] = $value->title;
            }
        }
        if ($json_r == NULL)
            $json_r[] = 'Shipping method not found';
        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //echo json_encode($json_r);			
    }

    public function validateCoupon($args) {
        $coupon_code = $args['couponCode'];
        $customerid = $args['customerid'];
        $json_r = array();
        $coupon = new WC_Coupon($coupon_code);
        if ($coupon->id != null) {
            $user_limit = $coupon->usage_limit_per_user;
            $used_by = $coupon->coupon_custom_fields['_used_by'];
            $user_coupon_count = 0;
            if(!empty($used_by)){
                foreach ($used_by as $key => $value) {
                    if ($value == $customerid) {
                        $user_coupon_count++;
                    }
                }
            }
            if ( $user_limit == '' || $user_coupon_count < $user_limit) {
                $json_r['result'] = 1;
            } else {
                $json_r['result'] = 0;
            }
        } else {
            $json_r[] = 'Coupon code not found';
        }

        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //echo json_encode($json_r);			
    }

    public function couponDetails($args, $rsponseArr = false) {
        $coupon_code = $args['couponCode'];
        //$customerid = $args['customerid'];
        $json_r = array();
        $coupon = new WC_Coupon($coupon_code);
        //var_dump($coupon);die;
        if ($coupon->id != null) {
            $p_status = 0;

            if (get_post_status($coupon->id) == 'publish')
                $p_status = 1;

            $json_r['status'] = $p_status;
            $json_r['coupon_code'] = $coupon->code;
            $json_r['is_active'] = ($coupon->is_valid() == true ? 1 : 0);
            $json_r['simple_action'] = $coupon->discount_type;
            $json_r['discount_amount'] = $coupon->coupon_amount;
            $json_r['discount_qty'] = $coupon->usage_count;
            $json_r['apply_to_shipping'] = 0;
            //$json_r['shipmyidonly'] = '';
        }else {
            $json_r[] = 'Coupon code not found';
        }

        if($rsponseArr == false)
            echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
        else
            return $json_r;

        //echo json_encode($json_r);			
    }

    public function sendordermail($args) {
        $orderid = $args['orderid'];
        $args= array('orderid' => $orderid);
        $order_info = $this->get_orderinfo($args, '1');
       
        if ($order_info['order_id'] != null) {

          //  $my_order_meta = get_post_custom($orderid);
           // $email = $my_order_meta['_billing_email'][0];
            $email = $order_info['address']['billing']['email'];
            $adminmail = get_bloginfo('admin_email');
            
            $mail_seq = array('admin', 'customer');
            foreach($mail_seq as $val){
                if($val == 'customer'){
                    
                 $content = '<table border="0" cellpadding="0" cellspacing="0" width="600" style="border-radius:6px!important;background-color:#fdfdfd;border:1px solid #dcdcdc;border-radius:6px!important"><tbody><tr><td align="center" valign="top">
                                    
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#557da1;color:#ffffff;border-top-left-radius:6px!important;border-top-right-radius:6px!important;border-bottom:0;font-family:Arial;font-weight:bold;line-height:100%;vertical-align:middle" bgcolor="#557da1"><tbody><tr><td>
                                            	<h1 style="color:#ffffff;margin:0;padding:28px 24px;display:block;font-family:Arial;font-size:30px;font-weight:bold;text-align:left;line-height:150%">Purchase detail</h1>

                                            </td>
                                        </tr></tbody></table></td>
                            </tr><tr><td align="center" valign="top">
                                    
                                	<table border="0" cellpadding="0" cellspacing="0" width="600"><tbody><tr><td valign="top" style="background-color:#fdfdfd;border-radius:6px!important">
                                                
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td valign="top">
                                                            <div style="color:#737373;font-family:Arial;font-size:14px;line-height:150%;text-align:left"><span class="im">

<p>You have successfully completed the order. Your order detail is
shown below for your reference:

</p>


</span><h2 style="color:#505050;display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%">Order: #'.$order_info["order_id"].'</h2>

<table cellspacing="0" cellpadding="6" style="width:100%;border:1px solid #eee" border="1"><thead><tr><th scope="col" style="text-align:left;border:1px solid #eee">Product</th>
			<th scope="col" style="text-align:left;border:1px solid #eee">Quantity</th>
			<th scope="col" style="text-align:left;border:1px solid #eee">Price</th>
		</tr></thead><tbody>';
                foreach($order_info["products"] as $key=>$val){
                    
                $content.= '<tr><td style="text-align:left;vertical-align:middle;border:1px solid #eee;word-wrap:break-word">'.$val["name"].
'<br><small></small></td>
		<td style="text-align:left;vertical-align:middle;border:1px solid #eee">'.$val["qty"].'</td>
		<td style="text-align:left;vertical-align:middle;border:1px solid #eee"><span>'.number_format((float)$val["price"], 2, '.', '').'</span></td>
	</tr>';
                }
                $content.= '</tbody><tfoot><tr><th scope="row" colspan="2" style="text-align:left;border:1px solid #eee">Shipping:</th>
						<td style="text-align:left;border:1px solid #eee">'.$order_info["shipping"]["method"].'</td>
					</tr><tr><th scope="row" colspan="2" style="text-align:left;border:1px solid #eee">Order Total:</th>
						<td style="text-align:left;border:1px solid #eee"><span>'.number_format((float)$order_info["amount"]["total"], 2, '.', '').'</span></td>
					</tr></tfoot></table><span class="im"><h2 style="color:#505050;display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%">Customer details</h2>

	<p><strong>Email:</strong> <a href="mailto:"'.$order_info["address"]["billing"]["email"].'" target="_blank">'.$order_info["address"]["billing"]["email"].'</a></p>
	<p><strong>Tel:</strong>'.$order_info["address"]["billing"]["phone"].'</p>

<table cellspacing="0" cellpadding="0" style="width:100%;vertical-align:top" border="0"><tbody><tr>';

        if(trim($order_info["address"]["billing"]["firstname"])==trim($order_info["address"]["shipping"]["firstname"]) && trim($order_info["address"]["billing"]["lastname"])==trim($order_info["address"]["shipping"]["lastname"]) && trim($order_info["address"]["billing"]["region"])==trim($order_info["address"]["shipping"]["region"]) && trim($order_info["address"]["billing"]["city"])==trim($order_info["address"]["shipping"]["city"]) && trim($order_info["address"]["billing"]["postcode"])==trim($order_info["address"]["shipping"]["postcode"]) && trim($order_info["address"]["billing"]["countryid"])==trim($order_info["address"]["shipping"]["countryid"]) && trim($order_info["address"]["billing"]["country"])==trim($order_info["address"]["shipping"]["country"]) && trim($order_info["address"]["billing"]["phone"])==trim($order_info["address"]["shipping"]["phone"]) && trim($order_info["address"]["billing"]["street"])==trim($order_info["address"]["shipping"]["street"])){
            $content.= '<td valign="top" width="50%">

                <h2 style="color:#505050;display:block;font-family:Arial;font-size:26px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%">Billing & Shipping Address</h2>

                <p>'.$order_info["address"]["billing"]["firstname"].' '.$order_info["address"]["billing"]["lastname"].'<br>'.$order_info["address"]["billing"]["street"].'<br>'.$order_info["address"]["billing"]["city"].'<br>'.$order_info["address"]["billing"]["region"].'<br>'.$order_info["address"]["billing"]["postcode"].'</p>

            </td>';

        }else{
            $content.= '<td valign="top" width="50%">

    			<h2 style="color:#505050;display:block;font-family:Arial;font-size:26px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%">Billing Address</h2>

    			<p>'.$order_info["address"]["billing"]["firstname"].' '.$order_info["address"]["billing"]["lastname"].'<br>'.$order_info["address"]["billing"]["street"].'<br>'.$order_info["address"]["billing"]["city"].'<br>'.$order_info["address"]["billing"]["region"].'<br>'.$order_info["address"]["billing"]["postcode"].'</p>

    		</td>
            <td valign="top" width="50%">

                <h2 style="color:#505050;display:block;font-family:Arial;font-size:26px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%">Shipping Address</h2>

                <p>'.$order_info["address"]["shipping"]["firstname"].' '.$order_info["address"]["shipping"]["lastname"].'<br>'.$order_info["address"]["shipping"]["street"].'<br>'.$order_info["address"]["shipping"]["city"].'<br>'.$order_info["address"]["shipping"]["region"].'<br>'.$order_info["address"]["shipping"]["postcode"].'</p>

            </td>';
        }

		
	$content.= '</tr></tbody></table>

															</span></div>
														</td>
                                                    </tr></tbody></table></td>
                                        </tr></tbody></table></td>
                            </tr><tr><td align="center" valign="top">
                                    
                                	<table border="0" cellpadding="10" cellspacing="0" width="600" style="border-top:0"><tbody><tr><td valign="top">
                                                <table border="0" cellpadding="10" cellspacing="0" width="100%"><tbody><tr><td colspan="2" valign="middle" style="border:0;color:#99b1c7;font-family:Arial;font-size:12px;line-height:125%;text-align:center">
                                                       
	
                                                        </td>
                                                    </tr></tbody></table></td>
                                        </tr></tbody></table></td>
                            </tr></tbody></table>';
            
          //  $content .= "</table>";
          //  $content = $order->email_order_items_table(true, true, true, true, $image_size);
            
            $headers[] = "Content-type: text/html";
            
            wp_mail($email, 'Your Order Details from ' . get_bloginfo('name') . ' on ' . $order_info['order_date'], $content, $headers);
               
                }
                if($val == 'admin'){
                    $content = '<table border="0" cellpadding="0" cellspacing="0" width="600" style="border-radius:6px!important;background-color:#fdfdfd;border:1px solid #dcdcdc;border-radius:6px!important"><tbody><tr><td align="center" valign="top">
                                    
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#557da1;color:#ffffff;border-top-left-radius:6px!important;border-top-right-radius:6px!important;border-bottom:0;font-family:Arial;font-weight:bold;line-height:100%;vertical-align:middle" bgcolor="#557da1"><tbody><tr><td>
                                            	<h1 style="color:#ffffff;margin:0;padding:28px 24px;display:block;font-family:Arial;font-size:30px;font-weight:bold;text-align:left;line-height:150%">Purchase detail</h1>

                                            </td>
                                        </tr></tbody></table></td>
                            </tr><tr><td align="center" valign="top">
                                    
                                	<table border="0" cellpadding="0" cellspacing="0" width="600"><tbody><tr><td valign="top" style="background-color:#fdfdfd;border-radius:6px!important">
                                                
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td valign="top">
                                                            <div style="color:#737373;font-family:Arial;font-size:14px;line-height:150%;text-align:left"><span class="im">

<p>

</p>


</span><h2 style="color:#505050;display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%">Order: #'.$order_info["order_id"].'</h2>

<table cellspacing="0" cellpadding="6" style="width:100%;border:1px solid #eee" border="1"><thead><tr><th scope="col" style="text-align:left;border:1px solid #eee">Product</th>
			<th scope="col" style="text-align:left;border:1px solid #eee">Quantity</th>
			<th scope="col" style="text-align:left;border:1px solid #eee">Price</th>
		</tr></thead><tbody>';
                foreach($order_info["products"] as $key=>$val){
                    
                $content.= '<tr><td style="text-align:left;vertical-align:middle;border:1px solid #eee;word-wrap:break-word">'.$val["name"].
'<br><small></small></td>
		<td style="text-align:left;vertical-align:middle;border:1px solid #eee">'.$val["qty"].'</td>
		<td style="text-align:left;vertical-align:middle;border:1px solid #eee"><span>'.number_format((float)$val["price"], 2, '.', '').'</span></td>
	</tr>';
                }
                $content.= '</tbody><tfoot><tr><th scope="row" colspan="2" style="text-align:left;border:1px solid #eee">Shipping:</th>
						<td style="text-align:left;border:1px solid #eee">'.$order_info["shipping"]["method"].'</td>
					</tr><tr><th scope="row" colspan="2" style="text-align:left;border:1px solid #eee">Order Total:</th>
						<td style="text-align:left;border:1px solid #eee"><span>'.number_format((float)$order_info["amount"]["total"], 2, '.', '').'</span></td>
					</tr></tfoot></table><span class="im"><h2 style="color:#505050;display:block;font-family:Arial;font-size:30px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%">Customer details</h2>

	<p><strong>Email:</strong> <a href="mailto:"'.$order_info["address"]["billing"]["email"].'" target="_blank">'.$order_info["address"]["billing"]["email"].'</a></p>
	<p><strong>Tel:</strong>'.$order_info["address"]["billing"]["phone"].'</p>

<table cellspacing="0" cellpadding="0" style="width:100%;vertical-align:top" border="0"><tbody><tr><td valign="top" width="50%">

			<h3 style="color:#505050;display:block;font-family:Arial;font-size:26px;font-weight:bold;margin-top:10px;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;line-height:150%"></h3>

			<p>'.$order_info["address"]["billing"]["firstname"].' '.$order_info["address"]["billing"]["lastname"].'<br>'.$order_info["address"]["billing"]["street"].'<br>'.$order_info["address"]["billing"]["city"].'<br>'.$order_info["address"]["billing"]["region"].'<br>'.$order_info["address"]["billing"]["postcode"].'</p>

		</td>

		
	</tr></tbody></table>

															</span></div>
														</td>
                                                    </tr></tbody></table></td>
                                        </tr></tbody></table></td>
                            </tr><tr><td align="center" valign="top">
                                    
                                	<table border="0" cellpadding="10" cellspacing="0" width="600" style="border-top:0"><tbody><tr><td valign="top">
                                                <table border="0" cellpadding="10" cellspacing="0" width="100%"><tbody><tr><td colspan="2" valign="middle" style="border:0;color:#99b1c7;font-family:Arial;font-size:12px;line-height:125%;text-align:center">
                                                       
	                                            </td>
                                                    </tr></tbody></table></td>
                                        </tr></tbody></table></td>
                            </tr></tbody></table>';
            
          //  $content .= "</table>";
          //  $content = $order->email_order_items_table(true, true, true, true, $image_size);
            
            $headers[] = "Content-type: text/html";
            wp_mail($adminmail, 'New Invoice has been generated', $content, $headers);
                }
            }
            
            $result = '1';
        } else {
            $result = '0';
        }
        return $result;
        
    }

    public function getShippingDetail($args) {
        $pid = json_decode(stripslashes($args['pid']), true);
        $country = (stripslashes($args['country']));
        $region = (stripslashes($args['region']));
        $zip = (stripslashes($args['zip']));
        $address = (stripslashes($args['address']));
        $address['dest_country'] = $country;
        $address['dest_region'] = $region;
        $address['dest_zip'] = $zip;

        //$product_id = 36;//$pid[1];
        //$qwt = 1;//$pid[2];
        $json_r = array();
        $package = array();
        //$value=36;
        //$p = new WC_Product();
        //print_r($woocommerce->shipping->load_shipping_methods());die;
        $grand_total = 0; {/* foreach($pid as $key => $value){
          //if($key % 2 != 0){
          //print_r($key );
          //print_r($value );
          $total_amount = get_product($value)->sale_price * $pid[$key+1];
          $grand_total += $total_amount;
          $package['contents'][] =  array(
          'product_id' => $value,
          'variation_id' => '',
          'variation' => '',
          'quantity' => $pid[$key+1],
          'data' => get_product($value),
          'line_total' => $total_amount,
          'line_tax' => 0,
          'line_subtotal' => $total_amount,
          'line_subtotal_tax' => 0
          );
          //}
          } */
        }
        foreach ($pid as $key => $value) {
            $total_amount = (get_product($key)->sale_price ) * $value;
            //print_r(get_product($key)->sale_price);	
            //print_r($total_amount);	

            $grand_total += $total_amount;
            //print_r($grand_total);	

            $package['contents'][] = array(
                'product_id' => $key,
                'variation_id' => '',
                'variation' => '',
                'quantity' => $pid[$value],
                'data' => get_product($key),
                'line_total' => $total_amount,
                'line_tax' => 0,
                'line_subtotal' => $total_amount,
                'line_subtotal_tax' => 0
            );
        }

        $package['contents_cost'] = $grand_total;
//			$package['data'] = $p->get_product();
        $package['applied_coupons'] = array();
        $package['destination'] = array(
            'country' => $address['dest_country'],
            'state' => $address['dest_region'],
            'postcode' => $address['dest_zip'],
            'city' => ''
        );

        $ship = new WC_Shipping();


        //$shiping_detail =$ship->load_shipping_methods();print_r($shiping_detail);die;
        //$shipping_methods = $woocommerce->shipping->load_shipping_methods();
        //print_r($shipping_methods);

        $shiping_detail = $ship->calculate_shipping_for_package($package);
        //print_r($shiping_detail);

        if (isset($shiping_detail['rates'])) {
            $count = 0;
            foreach ($shiping_detail['rates'] as $key => $value) {
                $json_r[$count]['title'] = $value->label;
                $json_r[$count]['method'] = $value->method_id;
                $json_r[$count]['price'] = $value->cost;
                $json_r[$count]['shippingcode'] = $value->id;
                $json_r[$count]['Status'] = 1;
                $count++;
            }
            //demo purpose
            if ($count == 0) {
                $json_r[$count]['title'] = 'Free Shipping';
                $json_r[$count]['method'] = 'Free';
                $json_r[$count]['price'] = 0;
                $json_r[$count]['shippingcode'] = 'freeshipping_freeshipping';
                $json_r[$count]['Status'] = 1;
            }
        } else {
            $json_r[] = 'Something wrong';
        }

        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";
    }

    public function countryStateList($args) {

        $country = $args['country'];

        $json_r = array();

        $country_obj = new WC_Countries();


        $state = $country_obj->get_states($country);


        if ($state) {
            foreach ($state as $key => $value) {
                $json_r[$key] = $value;
            }
        } else {
            $json_r[] = 'Invalid country';
        }

        echo $_GET["callback"] . "(" . json_encode($json_r) . ");";

        //echo json_encode($json_r);			
    }

    public function shipmyidenabled() {

        echo $_GET["callback"] . "(" . json_encode(array('result' => 1)) . ");";
        //echo json_encode(array('result' =>1));			
    }

    function myorders($arg) {
        $json_main = '';
        $user_id = $arg['customerid'];
        $args = array(
            'numberposts' => -1,
            'meta_key' => '_customer_user',
            'meta_value' => $user_id,
            'post_type' => 'shop_order',
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'shop_order_status',
                    'field' => 'slug',
                    'terms' => '*'
                )
            )
        );

        $posts = get_posts($args);

        if ($posts) {
            $order_count = 0;
            foreach ($posts as $key => $value) {

                $order = wc_get_order($posts[$key]->ID);
                $order_info = new WC_Order($posts[$key]->ID);
                //echo '<pre>'; print_r($order); die;
                $status = $order->get_status(); //=='trash'
                if ($status == 'trash') {
                    
                } else {
                    $json_main['id'] = $posts[$key]->ID;
                    $json_main['order_id'] = " ". $posts[$key]->ID;
                    if($order->get_status() == 'publish')
                    $json_main['status'] = " Processing";
                    else
                    $json_main['status'] = " ".ucwords($order->get_status());    
                    $json_main['order_date'] = $posts[$key]->post_date;
                    //$json_main['grand_total'] = $order->order_total;//7777;//$order->calculate_totals();
                    //$json_main['shipping_address']=$order->get_shipping_address();
                    //$json_main['billing_address']= $order->get_billing_address();
                    //echo $posts[$key]->ID.'<br>';
                    $json_main['couponUsed'] = get_post_meta($posts[$key]->ID, 'couponUsed', TRUE);
                    $json_main['couponCode'] = get_post_meta($posts[$key]->ID, 'couponCode', TRUE);
                    $json_main['discount_amount'] = get_post_meta($posts[$key]->ID, '_order_discount', TRUE);
                    $json_main['grand_total'] = get_post_meta($posts[$key]->ID, '_order_total', TRUE);

                    $json_cus = array();
                    $userinfo = get_userdata($user_id);
                    if ($userinfo) {
                        $cus_data = get_user_meta($userinfo->data->ID);

                        //$json_cus['CustomerInfo']['membersince'] = $userinfo->data->user_registered;

                        $json_cus['billing_address']['firstname'] = $cus_data['billing_first_name'][0];
                        $json_cus['billing_address']['lastname'] = $cus_data['billing_last_name'][0];
                        $json_cus['billing_address']['street'] = $cus_data['billing_address_1'][0];
                        $json_cus['billing_address']['city'] = $cus_data['billing_city'][0];
                        $json_cus['billing_address']['pincode'] = $cus_data['billing_postcode'][0];
                        $json_cus['billing_address']['region'] = $cus_data['billing_state'][0];
                        $json_cus['billing_address']['countryid'] = $cus_data['billing_country'][0];
                        $json_cus['billing_address']['contactno'] = $cus_data['billing_phone'][0];

                        $json_cus['shipping_address']['firstname'] = $cus_data['shipping_first_name'][0];
                        $json_cus['shipping_address']['lastname'] = $cus_data['shipping_last_name'][0];
                        $json_cus['shipping_address']['street'] = $cus_data['shipping_address_1'][0];
                        $json_cus['shipping_address']['city'] = $cus_data['shipping_city'][0];
                        $json_cus['shipping_address']['pincode'] = $cus_data['shipping_postcode'][0];
                        $json_cus['shipping_address']['region'] = $cus_data['shipping_state'][0];
                        $json_cus['shipping_address']['countryid'] = $cus_data['shipping_country'][0];
                        $json_cus['shipping_address']['contactno'] = $cus_data['billing_phone'][0];

                        $json_main['billing_address'] = $json_cus['billing_address'];
                        $json_main['shipping_address'] = $json_cus['shipping_address'];
                    }
                    //	echo "order id".$posts[$key]->ID." date ".$posts[$key]->post_date;
                    $i = 0;
                    $json_r['id'] = '';
                    // print_r($order->get_item_meta());
                    //die;
                    $grand_total = 0;
                    foreach ($order->get_items() as $item_id => $item) {
                        //print_r($item);
                        //die;
                        $p_id = $item['item_meta']['_product_id'][0];
                        $json_r = '';
                        $product = get_product($p_id);
                        if (!empty($product)) {
                            $p_category = get_the_terms($product->id, 'product_cat');

                            if ($p_category) {
                                $p_cat = array();
                                foreach ($p_category as $key => $value) {
                                    $p_cat[] = $value->term_id;
                                }

                                /* $s_pid='';
                                  $s_pid=(string)$product->id;
                                  var_dump($s_pid);
                                  var_dump($product->get_sku( ));die; */
                                $json_c['id'][$i] = (string) $product->id;
                                $json_c['sku'][$i] = $product->get_sku();
                                $json_c['name'][$i] = $product->get_title();
                                $json_c['image'][$i] = wp_get_attachment_url(get_post_thumbnail_id($product->id));
                                $json_c['stock'][$i] = $product->get_stock_quantity();
                                $json_c['unitprice'][$i] = number_format((float)$product->get_price(), 2, '.', ''); //123;//$item['item_meta'][_line_subtotal][0]; $product->get_price();
                                $json_c['quantity'][$i] = $item['qty'];
                                $json_c['total'][$i] = $product->get_price() * $item['qty'];//$item['item_meta'][_line_total][0];
                                $grand_total += $product->get_price() * $item['qty'];
                                
                                $i++;
                            }
                        }
                    }

                    $json_c[total_item_count] = $i;


                    $json_main['product'] = $json_c;
                    /*$discounting_amount = 0;
                    $coupons = $order->get_used_coupons(); //.'</br> note this';
                    $coupon_count = 0;
                    $json_main['couponUsed'] = array();
                    $json_main['couponUsed'][] = 0;
                    foreach ($coupons as $key => $value) {
                        $json_main['couponCode'][].=$value;
                        $coupon_count++;
                        $coupon = new WC_Coupon($value);
                        $discounting_amount+=$coupon->coupon_amount;
                    }
                    $json_main['discount_amount'] = $discounting_amount;*/
                    $json_main['order_currency'] =  $order->order_currency;
                    $json_main['order_currency_symbol'] = get_woocommerce_currency_symbol($order->order_currency);
                    $json_main['currency'] =  $order->order_currency;
                    //if ($coupon_count)
                    //    $json_main['couponUsed'] = 1; //$coupon_count;
                        
//echo $coupon_count;


                    $json_main['payment_method']['payment_method_title'] = get_post_meta($posts[$key]->ID, '_payment_method_title', TRUE);
                    $json_main['payment_method']['payment_method_code'] = get_post_meta($posts[$key]->ID, '_payment_method', TRUE);
                    /*$shipping = $order->get_shipping_methods();
                    $json_main['shipping_message'] = '';
                    $json_main['shipping_amount'] = 0;
                    foreach ($shipping as $key => $value) {
                        $json_main['shipping_message'] = $value[name];
                        $json_main['shipping_amount'] = $value[item_meta][cost][0];
                    }
*/
                    foreach($order->get_items( 'shipping' ) as $ship){
                $shiping_method = $ship['name'];
                if($shiping_method == 'free_shipping'){
                    $shiping_method = 'Free Shipping';
                }
            }

            $json_main['shipping_message'] = $shiping_method;
            $json_main['shipping_amount'] = method_exists($order, get_shipping)?$order->get_shipping():"0.00";
          //  $json['shipping']= array("method"=> $shiping_method, "amount"=> $order->get_shipping());
                    $json_main2['total'] = $order_count + 1;
                    $json_main2['data'][$order_count++] = $json_main;
                    //echo json_encode($json_main);	
                }    //echo $order->billing_phone;
            }


            echo $_GET["callback"] . "(" . json_encode($json_main2) . ");";
        } else
            echo  $_GET["callback"] . "(" . json_encode(array("total"=>0)) . ");";//'No order found '



        /* $orders=new WP_Query($args);
          if($orders->have_posts()):
          var_dump($orders->have_posts());
          die;
          while($orders->have_posts()): $orders->the_post();

          //use the template tags to list the posts

          endwhile;
          endif; */
    }

    function retrieve_password($user_login) {
        global $wpdb, $current_site;
        $errors = new WP_Error();
        if (empty($user_login)) {
            return false;
        } else if (strpos($user_login, '@')) {
            $user_data = get_user_by('email', trim($user_login));
            if (empty($user_data))
                return false;
        } else {
            $login = trim($user_login);
            $user_data = get_user_by('login', $login);
        }
        //var_dump($user_data);die;
        do_action('lostpassword_post');

        if ($errors->get_error_code())
            return $errors;

        if (!$user_data) {
            $errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
            return $errors;
        }

        // Redefining user_login ensures we return the right case in the email.
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        /**
         * Fires before a new password is retrieved.
         *
         * @since 1.5.0
         * @deprecated 1.5.1 Misspelled. Use 'retrieve_password' hook instead.
         *
         * @param string $user_login The user login name.
         */
        do_action('retreive_password', $user_login);

        /**
         * Fires before a new password is retrieved.
         *
         * @since 1.5.1
         *
         * @param string $user_login The user login name.
         */
        do_action('retrieve_password', $user_login);

        /**
         * Filter whether to allow a password to be reset.
         *
         * @since 2.7.0
         *
         * @param bool true           Whether to allow the password to be reset. Default true.
         * @param int  $user_data->ID The ID of the user attempting to reset a password.
         */
        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if (!$allow)
            return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
        else if (is_wp_error($allow))
            return $allow;

        // Generate something random for a password reset key.
        $key = wp_generate_password(20, false);

        /**
         * Fires when a password reset key is generated.
         *
         * @since 2.5.0
         *
         * @param string $user_login The username for the user.
         * @param string $key        The generated password reset key.
         */
        do_action('retrieve_password_key', $user_login, $key);

        // Now insert the key, hashed, into the DB.
        if (empty($wp_hasher)) {
            require_once ABSPATH . WPINC . '/class-phpass.php';
            $wp_hasher = new PasswordHash(8, true);
        }
        $hashed = $wp_hasher->HashPassword($key);
        $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));

        $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
        $message .= network_home_url('/') . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

        if (is_multisite())
            $blogname = $GLOBALS['current_site']->site_name;
        else
        /*
         * The blogname option is escaped with esc_html on the way into the database
         * in sanitize_option we want to reverse this for the plain text arena of emails.
         */
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $title = sprintf(__('Password Reset : %s'), $blogname);

        /**
         * Filter the subject of the password reset email.
         *
         * @since 2.8.0
         *
         * @param string $title Default email title.
         */
        $title = apply_filters('retrieve_password_title', $title);
        /**
         * Filter the message body of the password reset mail.
         *
         * @since 2.8.0
         *
         * @param string $message Default mail message.
         * @param string $key     The activation key.
         */
        $message = apply_filters('retrieve_password_message', $message, $key);

        if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message))
            wp_die(__('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.'));

        return true;
    }

    public function preparequote($args){
        
        global $woocommerce;
        $product_info = base64_decode($args['products']);

        $currency = $args['currency'];
        $is_create_quote = $args['is_create_quote'];
        $cst_id = $args['customerid'];
        $address = base64_decode($args['address']);
        $shiping_code = $args['shipping_code'];
        $find_shipping = $args['find_shipping'];
        $coupon_code = $args['couponCode'];
        
/**************Update User Billing/Shipping Address *******************/
        $address = json_decode($address);
        $billAdd = $address->billing;
        $shippAdd = $address->shipping;
        
        $u = get_userdata($cst_id);
        //print_r($billAdd); die;
        //var_dump($u);
        if ($u) {
            //var_dump($u);die;
            update_user_meta($cst_id, 'billing_first_name', stripslashes($billAdd->firstname));
            update_user_meta($cst_id, 'billing_last_name', stripslashes($billAdd->lastname));
            update_user_meta($cst_id, 'billing_address_1', stripslashes($billAdd->street));
            update_user_meta($cst_id, 'billing_city', stripslashes($billAdd->city));
            update_user_meta($cst_id, 'billing_postcode', stripslashes($billAdd->postcode));
            update_user_meta($cst_id, 'billing_state', stripslashes($billAdd->region));
            update_user_meta($cst_id, 'billing_country', stripslashes($billAdd->country));
            update_user_meta($cst_id, 'billing_phone', stripslashes($billAdd->phone));
            update_user_meta($cst_id, 'billing_email', stripslashes($billAdd->email));

            update_user_meta($cst_id, 'shipping_first_name', stripslashes($billAdd->firstname));
            update_user_meta($cst_id, 'shipping_last_name', stripslashes($billAdd->lastname));
            update_user_meta($cst_id, 'shipping_address_1', stripslashes($billAdd->street));
            update_user_meta($cst_id, 'shipping_city', stripslashes($billAdd->city));
            update_user_meta($cst_id, 'shipping_postcode', stripslashes($billAdd->postcode));
            update_user_meta($cst_id, 'shipping_state', stripslashes($billAdd->region));
            update_user_meta($cst_id, 'shipping_country', stripslashes($billAdd->country));
            update_user_meta($cst_id, 'shipping_phone', stripslashes($billAdd->phone));
            update_user_meta($cst_id, 'shipping_email', stripslashes($billAdd->email));
            
        }
 /***************************Get total amount from products******************/       
        $grand_total = 0;
       foreach(json_decode($product_info) as $products){
           
           $product = get_product($products->id);
         
           $grand_total += ($product->sale_price?$product->sale_price:($product->regular_price?$product->regular_price:"0.00")) * $products->quantity;
           
       } 
       
       
/**********************************************************************/        
        
        
        
        $shipping = new WC_Shipping();
        $ships = $shipping->load_shipping_methods();
        
        $shipping_dropdown_option = '';
        foreach($ships as $val){
            foreach($val as $result){
                if(is_array($result) && $result['enabled'] == "yes"){
                    $shiping_method_id = $val->id;
                    $method = $val->method_title;
                    $title = $val->title;
                    $fee = $val->fee;
                    $shipping_dropdown_option .= "<option id='$shiping_method_id' value= '$shiping_method_id' 'free_shipping' price = '$fee' description='$title'>$title</option>";
                }
        
        }
        }
        $res["available_shipping_method"] = base64_encode($shipping_dropdown_option);
        $res["total_amount"] = $grand_total;
        $res["currency"] = $currency;
        $res["status"] = "success";
        $res["tax_amount"] = 0;
        
        $res["shipping_amount"] = $fee;

        /***************Coupon Code **********/
        $couponData = $this->couponDetails($args,true);
        if($couponData['status'] == 1 && $couponData['is_active'] == 1 && $couponData['simple_action'] == 'fixed_cart'){
            $res["total_amount"] = $grand_total - $couponData['discount_amount'];
            $res["coupon_discount"] = $couponData['discount_amount'];
            $res["coupon_status"] = 1;
        }else if($couponData['status'] == 1 && $couponData['is_active'] == 1 && $couponData['simple_action'] == 'percent'){
            $res["total_amount"] = $grand_total - $grand_total*$couponData['discount_amount']/100;
            $res["coupon_discount"] = $grand_total*$couponData['discount_amount']/100;
            $res["coupon_status"] = 1;
        }
        //$coupon_code = new WC_Coupon();
        //$coupon_code_details = $coupon_code->get_coupon($coupon_code);
        /***************Coupon Code **********/


        echo $_GET["callback"] . "(" . json_encode($res) . ");";
    }
    
    public function get_orderinfo($args, $flag=null){
        $order_id = $args['orderid'];
        $cust_id = $args['customerid'];
        $json_r = array();
        
        $order = new WC_Order($order_id);
        if($order->post_status == 'publish' || strtoupper($order->post_status) == 'WC-PROCESSING'){
            $status = 'Processing';
            $state = 'Processing';
        }else{
            $status = $order->post_status;
        }
                
        $json['id'] = $order->id;
        $json['order_id'] = $order->id;
        $json['status'] = ucwords($status);
        $json['state'] = $state;
        $json['order_date'] = $order->order_date;
        if($order->payment_method == 'cod'){
            $method_title = "Cash On Delivery";
        }
        $json['payment'] = array("title" => $method_title, "code" => $order->payment_method);
        $shipping['shipping'] = array( "prefix" => null,
                                        "firstname" => $order->shipping_first_name,
                                        "lastname" => $order->shipping_last_name,
                                        "company"=>$order->shipping_company,
                                        "street"=>$order->shipping_address_1,
                                        "region"=>$order->shipping_state,
                                        "city"=>$order->shipping_city,
                                        "postcode"=>$order->shipping_postcode,
                                        "countryid"=>$order->shipping_country,
                                        "country"=>$order->shipping_country,
                                        "phone"=>$order->shipping_phone,
                                        "email"=>$order->shipping_email,
                                        "shipmyid"=>null,
                                );
        $billing['billing'] = array( "prefix" => null,
                                        "firstname" => $order->billing_first_name,
                                        "lastname" => $order->billing_last_name,
                                        "company"=>$order->billing_company,
                                        "street"=>$order->billing_address_1,
                                        "region"=>$order->billing_state,
                                        "city"=>$order->billing_city,
                                        "postcode"=>$order->billing_postcode,
                                        "countryid"=>$order->billing_country,
                                        "country"=>$order->billing_country,
                                        "phone"=>$order->billing_phone,
                                        "email"=>$order->billing_email,
                                        "shipmyid"=>null,
                                );
        
        if ($order->post != null) {
            $items = $order->get_items();
            
        foreach ( $items as $item ) {
            
            $product = new WC_Product($item['product_id']);
            $json['products'][] = array('id' => $item['product_id'],
                                        'sku' => $product->get_sku(),
                                        'name'=> $item['name'],
                                        'qty' => $item['qty'],            
                                        'image' => wp_get_attachment_url(get_post_thumbnail_id($item['product_id'])),
                                        'price' => number_format((float)($product->sale_price?$product->sale_price:($product->regular_price?$product->regular_price:"0.00")), 2, '.', ''),        
                                        );
            }
            $json['currency'] =  array("code"=> $order->order_currency , "symbol" => get_woocommerce_currency_symbol($order->order_currency), "current" => $order->order_currency);
            $json['address']= array_merge($shipping, $billing);
            $json['amount'] = array('total'=> number_format((float)$order->order_total, 2, '.', ''), 'tax'=> $order->order_tax);
            $couponArr = array();
            $postmetaCouponCode = get_post_meta($order->id, 'couponCode', TRUE);
            if(get_post_meta($order->id, 'couponCode', TRUE) && !empty($postmetaCouponCode)){
                $couponArr["applied"] =  1;
                $couponArr["code"] =  get_post_meta($order->id, 'couponCode', TRUE);
                $couponArr["amount"] =  get_post_meta($order->id, '_order_discount', TRUE);
            }
            $json['coupon']= $couponArr;
            foreach($order->get_items( 'shipping' ) as $ship){
                $shiping_method = $ship['name'];
                if($shiping_method == 'free_shipping'){
                    $shiping_method = 'Free Shipping';
                }
            }
            
            $json['shipping']= array("method"=> $shiping_method, "amount"=> method_exists($order, get_shipping)?$order->get_shipping():"0.00");
            
        }
            if($flag == '1')
                return $json;
            else
            echo $_GET["callback"] . "(" . json_encode($json) . ");";
        //echo $_GET["callback"] .'([{"id":"18","name":"Jeans-404","imageurl":"http:\/\/magento-webservice.ebizontech.com\/\/media\/catalog\/product\/k\/r\/kraus-jeans-women-blue-ankle-length-jeans_16e0085ae72f81e233d18e44c35abb5f_images_180_240_mini.jpg","sku":"404","spclprice":"0.00","price":"53.00","created_date":"2013-09-30 12:51:21","is_in_stock":"1","stock_quantity":"5996.0000"}])';
    }
    
    public function paymentresponse($args){
        $response = $args['mofluidpayaction'];
        $order_id = $args['mofluid_order_id'];
        $args= array('orderid' => $order_id);
        if($response == 'success'){
        $order_info = $this->get_orderinfo($args, '1');
        echo '<html>
		    <head>
		         <title>Success</title>
		        <meta name="viewport" content="width = 100%" />
		        <meta name="viewport" content="initial-scale=2.5, user-scalable=no" />
		    </head>
		    <body>
			 <center>
			          <h3>Thank you for your order.</h3>
			</center>';
   if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad')){
$dis_body =  "<br><br><b>Your transaction was successfull.</b><br><br><br>See Your mail for more details";
           }
             else {
		$dis_body =  "<br><b>Payment Details :- </b><br>";
		$dis_body .= "<br>Payment Status : <b>".$order_info['status']."</b>";
		//$dis_body .= "<br>Transaction ID : <b>".$postdata['txn_id']."</b>";
		$dis_body .= "<br>Order ID : ".$order_info['order_id'];
	        $dis_body .= "<br>Payment Date : ".$order_info['order_date'];
		$dis_body .= "<br>Amount : ".$order_info['amount']['total'].$order_info['currency']['symbol'];
		$dis_body .="<br><br>See Your mail ".$order_info['address']['billing']['email']. " for more details";
	  }
                     $dis_body .="<br><br><br><br>Please close this window.";
	              echo '<center>'.$dis_body.'</center></body></html>';
				 
        
    }else if($mofluidpayaction == "cancel") {
echo "<html><head><title>Canceled</title></head><body><center><h3>The order was canceled.</h3></center>";
 echo "<br><br><br><center>Please Close this window</center></body></html>";
				  }
				  else {
				    	echo "<br>Unknown Response<br>";
				  }
    }
    
public function send_userregistration_mail($user_info){

$site_url = get_option('siteurl').'/wp-login.php';    
$content = '<table cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
            <td align="center" valign="top" style="padding:20px 0 20px 0">
                <table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="650" style="border:1px solid #E0E0E0;">
            <!-- [ header starts here] -->
            <!--tr>
                <td valign="top"><a href="{{store url=""}}"><img src="{{var logo_url}}" alt="{{var logo_alt}}" style="margin-bottom:10px;" border="0"/></a></td>
            </tr-->
            <!-- [ middle starts here] -->
            <tr><td><img src="'.get_template_directory_uri().'/images/logo.png" alt="Logo" /></td></tr>
            <tr>
                <td valign="top">
                    <h1 style="font-size:22px; font-weight:normal; line-height:22px; margin:0 0 11px 0;"">Hello, '. $user_info["firstname"];
                 $content .=   '</h1><p>
                     
								New customer registration <br />
								
								UserName: '. $user_info["email"].'<br />';
		$content .=						'Password:'.$user_info["password"];
		$content .= '</p>					
					<p>							
								To login in the account, please visit the <a href="'.$site_url.'" style="color:#1E7EC8;">Login</a>
								
								<p>Thanks again!</p>
				    </p>
            </tr>
           
            <tr>
                <td bgcolor="#EAEAEA" align="center" style="background:#EAEAEA; text-align:center;"><center><p style="font-size:12px; margin:0;">Thank you</p></center></td>
            </tr>
        </table>
    </td>
</tr>
</table>';
                 
            $headers[] = "Content-type: text/html";
            
            wp_mail($user_info['email'], 'New user registration on : '. get_bloginfo('name','raw'), $content, $headers);                 

    }
    
}

?>
