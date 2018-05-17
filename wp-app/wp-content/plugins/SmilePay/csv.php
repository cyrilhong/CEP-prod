<?php
//error_reporting(0);
function u2bbk($text)//畫面輸出
{	
	//return iconv("UTF-8","big5",$text);
	return $text;
}

function printpage()
{
	global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;
//取得前綴start
    //php5.2.6
/*    $sqlll="SHOW TABLES LIKE  '%comments%'";
    $recheck=mysql_query($sqlll);
    $row = mysql_fetch_array($recheck);
    $tablelist=$row[0];
    $tablelist=explode('_',$tablelist);
    $tablelist=$tablelist[0].'_';*/
    $tablelist= $wpdb->prefix;
    //php5.3以上，請將上方語法停用,改用下方語法,並將【wp_】更換成為您伺服器的設定 , 可在根目錄\wp-config.php中查詢到
    /*
    $tablelist="wp_";
    */
//取得前綴end
    
?><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
</head>
<?php
$shopname=get_option( 'blogname', '' );

$csvpage=$_GET['csvpage'];
$csvarray=explode("!",$csvpage);
$od_sob=$csvarray[0];
$Barcode1=$csvarray[1];
$Barcode2=$csvarray[2];
$Barcode3=$csvarray[3];
$amt=$csvarray[4];
$phone=$csvarray[5];
$dater=$csvarray[6];
$dater=substr($dater,0,10);
$fivesdrafts = $wpdb->get_results( "SELECT order_item_name FROM `".$tablelist."woocommerce_order_items` WHERE `order_id` = '$od_sob' AND order_item_type='line_item'" );
$Od_sob="";
foreach ( $fivesdrafts as $fivesdraft ) 
{
	$vr =$fivesdraft->order_item_name;
	$Od_sob=$Od_sob.$vr."<br>";
}


$url=get_option('siteurl');
$dater=explode('/',$dater);

?>




<table border="0" width="100%">
<tr>
<td align="center">
<table border="0" width="700">
<tr>
	<td align="center" width="694" colspan="2">
		<p><font size="6"><?php echo u2bbk($shopname);?>&nbsp;&nbsp;<img border="0" src="https://ssl.smse.com.tw/PAY/img/SmilePAY_2.jpg" ></font></p>
	</td>
</tr>
<tr>
		<td align="center" width="694" colspan="2">
            <hr size="1">
		</td>
</tr>
<tr>
    <td align="center" width="346">
        <p align="left"><font size="2">消費者繳費注意事項：若店鋪無法讀取條碼，</br>煩請消費者以其他方式另行繳費</font></p>
    </td>
    <td>
    <p align="right"><font size="2">印單日期：<?php echo date('Y').'年'.date('m').'月'.date('d').'日';?></font></p>
    </td>
</tr>　
<tr>
	<td align="center" width="346">
    <p align="left"><font size="2" color="#FF0000">只要持單到全國的任何一家 『7-11』『全家』</br>『萊爾富』『OK』便利商店繳費即可!</font></p>
    </td>
    <td>
    <p align="right"><b><font size="3" color='red'>繳費期限：<?php echo $dater[0].'年'.$dater[1].'月'.$dater[2].'日';?></font></b></p>
    </td>
</tr>
<tr>
	<td align="center" width="50%">
    	<p><font size="3"><?php echo u2bbk($shopname);?>　電子帳單</font></p>
    </td>
	<td align="right" width="50%">
    	<p><b><font size="3">(第一聯: 客戶收執聯)</font></b></p>
    </td>
</tr>

<tr>
	<td align="center" width="694" colspan="2">
        <table border="1" width="100%" style="border-collapse: collapse">
            <tr>
                <td align="center" width="140" >店鋪訂單編號</td>
                <td align="center" width="" >商品</td>
                <td align="center" width="120" >繳費總金額</td>
                <td align="center" width="199" colspan="2">代收店鋪收訖章</td>
            </tr>
            <tr>
                <td height="34" width="140" align="center"><?php echo $od_sob;?></td>
                <td height="100" width="" align="center" rowspan="3"><?php echo u2bbk($Od_sob)?></td>
                <td height="100" width="120" align="center" rowspan="3"><?php echo $amt;?></td>
                <td rowspan="4" width="180" align="center"></td>
                <td rowspan="4" width="13">
                	<p align="right">此<br>聯<br>請<br>客<br>戶<br>保<br>存
        　		</td>
            </tr>
            <tr>
                <td height="23" width="140" align="center">流水編號</td>
            </tr>
            <tr>
                <td height="34" width="140" align="center"><?php echo $Barcode2;?></td>
            </tr>
            <tr>
                <td colspan="3"><p align="left"><font size="2">電子商務網站：<?php echo $url;?><br>
                客服專線：<?php echo $phone;?></font></td>
            </tr>
      </table>
	</td>
</tr>    
<tr>
	<td align="center" width="694" colspan="2">
		<div align="center"><img border="0" src="https://ssl.smse.com.tw/PAY/img/cut.gif" ></div>
    </td>
</tr>
<tr>
	<td align="center" width="50%">
    	<p><font size="3"><?php echo u2bbk($shopname);?>　電子帳單</font></p>
    </td>
	<td align="right" width="50%">
    	<p><b><font size="3">(第二聯: 店舖收執聯)</font></b></p>
    </td>
</tr>
<tr>
	<td align="center" width="694" colspan="2">
        <table border="1" width="100%" style="border-collapse: collapse">
            <tr>
                <td align="center" width="140" >店鋪訂單編號</td>
              <td align="center" width="" >商品</td>
              <td align="center" width="120" >繳費總金額</td>
                <td align="center" colspan="2">代收店鋪收訖章</td>
          </tr>
            <tr>
                <td height="34" width="140" align="center"><?php echo $od_sob;?></td>
              <td height="100" width="" align="center" rowspan="3"><?php echo u2bbk($Od_sob)?></td>
              <td height="100" width="120" align="center" rowspan="3"><?php echo $amt;?></td>
                <td rowspan="4" width="180" align="center"></td>
                <td rowspan="4" width="13">
               	  <p align="right">此<br>聯<br>請<br>客<br>戶<br>保<br>存
        　		</td>
            </tr>
            <tr>
                <td height="23" width="140" align="center">流水編號</td>
          </tr>
            <tr>
                <td height="34" width="140" align="center"><?php echo $Barcode2;?></td>
          </tr>
      </table>
	</td>
</tr>            
<tr>
	<td align="center" width="694" colspan="2">
        <table border="1" width="694" cellspacing="0" cellpadding="0" style="border-collapse: collapse">
            <tr height="150">
                <td width="250" height="191">
               	  <div align="center"><img border="0" src="https://ssl.smse.com.tw/PAY/img/n2tg.jpg" ></div>
           	    <b><p align="center">繳費注意事項：</p></b>
                    1. 本繳費單請盡量雷射印表機列印</br>
               		2. 若店鋪無法讀取條碼，煩請消費者以其他方式另行繳費
                </td>
<td align="center">
                    <img border="0" src="http://211.20.222.148/up/barcode.php?barcode=<?php echo $Barcode1;?>&width=350&height=50" >
                    <img border="0" src="http://211.20.222.148/up/barcode.php?barcode=<?php echo $Barcode2;?>&width=350&height=50" >
                    <img border="0" src="http://211.20.222.148/up/barcode.php?barcode=<?php echo $Barcode3;?>&width=350&height=50" >
                </td>
            </tr>
            </table>
        <div align="left"><font size="2">
        電子商務網站：<?php echo $url;?>&nbsp;&nbsp;&nbsp;&nbsp;客服專線：<?php echo $phone;?>
        </font></div></div>
	</td>
</tr>
</td></tr></table>
<?php
exit();
}
?>
