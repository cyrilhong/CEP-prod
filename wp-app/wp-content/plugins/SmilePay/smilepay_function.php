<?php
global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
//���o�e��start
  //php5.2.6
  /*$sqlll="SHOW TABLES LIKE  '%comments%'";
  $recheck=mysql_query($sqlll);
  $row = mysql_fetch_array($recheck);

  $tablelist=$row[0];
  $tablelist=explode('_',$tablelist);
  $tablelist=$tablelist[0].'_';*/
  $tablelist= $wpdb->prefix;
  //php5.3�H�W�A�бN�W��y�k����,��ΤU��y�k,�ñN�iwp_�j�󴫦����z���A�����]�w , �i�b�ڥؿ�\wp-config.php���d�ߨ�
  /*
  $tablelist="wp_";
  */

//���o�e��end
define('S_commentmeta',$tablelist."commentmeta" );
define('S_comments',$tablelist."comments" );
define('S_options',$tablelist."options" );
define('S_postmeta',$tablelist."postmeta" );
define('S_posts',$tablelist."posts" );
define('S_terms',$tablelist."terms" );
define('S_term_relationships',$tablelist."term_relationships" );
define('S_term_taxonomy',$tablelist."term_taxonomy" );
define('S_itemmeta',$tablelist."woocommerce_order_itemmeta" );



//���o�q��
function get_order($res,$Data_id,$Amount)
{
	global $wpdb;
	$ordersql = $wpdb->get_results( "SELECT `post_id` FROM  `".S_postmeta."` WHERE  `meta_value` =  '$res'" );
	foreach ( $ordersql as $array ) 
	{$outtext =$array->post_id;	}
	if($outtext==$Data_id)
	{
		$ordersql = $wpdb->get_results( "SELECT `meta_value` FROM  `".S_postmeta."` WHERE  `post_id` =  '$Data_id' AND `meta_key`='_order_total'" );
		foreach ( $ordersql as $array ) 
		{$outtext =intval(round($array->meta_value));	}
		if($outtext==$Amount){return true;}else{return false;}
	}
	else{return false;}
}
//���o���A
function get_state($Data_id)
{
	global $wpdb;
	$ordersql = $wpdb->get_results( "SELECT `term_taxonomy_id` FROM  `".S_term_relationships."` WHERE  `object_id` =  '$Data_id'" );
	foreach ( $ordersql as $array ) 
	{$outtext =$array->term_taxonomy_id;}
	define('nowtermstate',$outtext );
	$ordersql = $wpdb->get_results( "SELECT `slug` FROM  `".S_terms."` WHERE  `term_id` =  '$outtext'" );
	foreach ( $ordersql as $array ) 
	{$outtext =$array->slug;}
	return $outtext;
}

//�Ӯa�q�满������
function change_note_shop($Data_id,$date,$time,$Smseid)
{
	global $wpdb;
	//���o�������
	$ordersql = $wpdb->get_results( "SELECT `comment_ID` FROM  `".S_comments."` WHERE  `comment_post_ID` =  '$Data_id' AND `comment_content` LIKE '%$Smseid%'" );
	foreach ( $ordersql as $array ) {$notes =$array->comment_ID;}
	//�ק�
	$main="SmilePay�۰ʾP�b���\<br>�P�b�ɶ��G".$date.$time."<br>SmilePay�l�ܽX�G".$Smseid;
	$wpdb->update(S_comments, array('comment_content' => iconv("big5","UTF-8",$main)), array('comment_post_ID' => $Data_id , 'comment_ID' =>$notes ));	
}
//���O�̭q�满������
function change_note_user($Data_id)
{
	global $wpdb;
	//���o�������
	$ordersql = $wpdb->get_results( "SELECT `comment_ID` FROM  `".S_comments."` WHERE  `comment_post_ID` = '$Data_id' AND `comment_content` LIKE '%".iconv("big5","UTF-8",'ú�O�覡�G<font color=red>')."%'" );
	foreach ( $ordersql as $array ) {$notes =$array->comment_ID;}
	$wpdb->update(S_commentmeta, array('meta_value' => "0"), array('comment_id' => $notes));	
}
//���O�̭q�满���W�[
function add_note_user($Data_id,$date,$time)
{
	global $wpdb;
	$nowtime= date("Y-m-d G-i-s",mktime(date("G")+8,date("i"),date("s"),date("m"),date("d"),date("Y")));
	//���o�������
	$main="����z���I��<br>�J�b�ɶ��G".$date.$time."<br>";
	$wpdb->insert( S_comments,array('comment_post_ID' => $Data_id, 
									'comment_date' => $nowtime,  
									'comment_date_gmt' => $nowtime,
									'comment_content'=>iconv("big5","UTF-8",$main),
									'comment_karma'=>"0",
									'comment_approved'=>"1",
									'comment_agent'=>"WooCommerce",
									'comment_type'=>"order_note",
									'comment_parent'=>"0",
									'user_id'=>"0",
									 ));
	$ordersql = $wpdb->get_results( "SELECT `comment_ID` FROM  `".S_comments."` WHERE  `comment_post_ID` = '$Data_id' AND `comment_content` LIKE '%".iconv("big5","UTF-8",'����z���I��<br>�J�b�ɶ�')."%'" );
	foreach ( $ordersql as $array ) {$notes =$array->comment_ID;}
	$wpdb->insert(S_commentmeta, array('comment_id' => $notes , 'meta_key' => 'is_customer_note', 'meta_value' => "1"));	
}
//���ܪ��A
function change_state($Data_id)
{
	global $wpdb;
    $order = new WC_Order($Data_id);
	//$wpdb->update(S_posts, array('post_status' => 'wc-processing'), array('ID' => $Data_id));
    if(!empty($order))
    {
        $order->update_status( 'processing' );	
    }
	    

	/*
	$ordersql = $wpdb->get_results( "SELECT `term_id` FROM  `".S_terms."` WHERE  `name` = 'processing'" );
	foreach ( $ordersql as $array ) {$term_id =$array->term_id;}
	$wpdb->update(S_term_relationships, array('term_taxonomy_id' => $term_id), array('object_id' => $Data_id));
	$count="count";
	$ordersql = $wpdb->get_results( "SELECT `count` FROM  `".S_term_taxonomy."` WHERE  `term_taxonomy_id` = '$term_id'" );
	foreach ( $ordersql as $array ) {$count01 =$array->$count ;}
	$ordersql = $wpdb->get_results( "SELECT `count` FROM  `".S_term_taxonomy."` WHERE  `term_taxonomy_id` = '".nowtermstate."'" );
	foreach ( $ordersql as $array ) {$count02 =$array->$count;}
	$wpdb->update(S_term_taxonomy, array('count' => $count01+1), array('term_taxonomy_id' => $term_id));
	$wpdb->update(S_term_taxonomy, array('count' => $count02-1), array('term_taxonomy_id' => nowtermstate));
	*/
}


//���oMID
function getMID($midfunc)
{
	global $wpdb;
	$midsql = $wpdb->get_results( "SELECT `option_value` FROM  `".S_options."` WHERE  `option_name` =  '$midfunc'" );
	foreach ( $midsql as $fivesdraft ) 
	{$vr =$fivesdraft->option_value;	}
	$tion=strpos($vr,'Mid_smilepay');
	$mid=substr($vr,$tion,30); 
	$mids=explode(';',$mid);
	$midss=explode(':',$mids[1]);
	$dbmid=trim($midss[2],"\"");
	return $dbmid;
}

//MID�p��(�Ӯa�Ѽ�,���B,Smseid
function ShowMID($Smilepay_mid,$Amount,$Smseid)
{
	$r_all=substr($Smseid,-4,4);
	$r1= substr($r_all,0,1);
	$r2= substr($r_all,1,1);
	$r3= substr($r_all,2,1);
	$r4= substr($r_all,3,1);
	if (is_numeric(r1)) {  $r1 = "9";}
	if (is_numeric(r2)) {  $r2 = "9";}
	if (is_numeric(r3)) {  $r3 = "9";}
	if (is_numeric(r4)) {  $r4 = "9";}
	$str0=$r1.$r2.$r3.$r4;
	$str1 = str_pad($Amount,8,'0',STR_PAD_LEFT);
	$str=$Smilepay_mid.$str1.$str0;
	$odd=$even=0;
	for($i=0;$i<16;$i++)
	{
		if($i%2==0)
		{$even=$even+substr($str,$i,1);}
		if($i%2==1)
		{$odd=$odd+substr($str,$i,1);}
	}
	 $mid=$even*9+$odd*3;
	return  $mid;
}
function smilepay_get_order_total($Data_id)
{
    $order = new WC_Order($Data_id);
	return $order->get_total();
}

function smilepay_ipn_union_success($order_id,$Amount,$Smseid,$datetime)
{
      $order = new WC_Order($order_id);
      //$datetime = iconv("big5","UTF-8",$datetime);
      $ordertext="ú�O�覡�G<font color=red>�u�W��d</font><br>������G�G<font color=red>���v���\</font></br>���v���B�G<font color=red>".
							$Amount."</font></br>����ɶ��G<font color=red>".$datetime . "</font>";
      $ordertext= iconv("big5","UTF-8",$ordertext);
      $order->add_order_note($ordertext,"0");

      $ordertext="<h1>ú�O��T</h1>ú�O�覡�G<font color=red>�u�W��d</font><br>������G�G<font color=red>���v���\</font></br>���v���B�G<font color=red>".
							$Amount."</font></br>����ɶ��G<font color=red>".$datetime."</font></br>SmilePay�l�ܽX�G<font color=red>".$Smseid."</font>";
      $ordertext= iconv("big5","UTF-8",$ordertext);
      $order->add_order_note($ordertext,"1");


      $order->update_status( 'processing' );	
}

function smilepay_ipn_union_fail($order_id,$Amount,$Smseid,$datetime,$err_desc)
{
      $order = new WC_Order($order_id);
     // $datetime = iconv("big5","UTF-8",$datetime);
  
      $ordertext="ú�O�覡�G<font color=red>�u�W��d</font><br>������G�G<font color=red>���v����</font><br>����ɶ��G<font color=red>".
							 $datetime ."</font><br>���ѭ�]�G<font color=red>".$err_desc."</font><br>SmilePay�l�ܽX�G<font color=red>".$Smseid."</font>";
                               
      $ordertext= iconv("big5","UTF-8",$ordertext);
    
      $order->add_order_note($ordertext,"0");
  
      $ordertext="ú�O�覡�G<font color=red>�u�W��d</font><br>������G�G<font color=red>���v����</font><br>����ɶ��G<font color=red>".
							 $datetime ."</font><br>���ѭ�]�G<font color=red>".$err_desc."</font>";
      $ordertext= iconv("big5","UTF-8",$ordertext);
      $order->add_order_note($ordertext,"1");


      $order->update_status('failed');	
}


?>
