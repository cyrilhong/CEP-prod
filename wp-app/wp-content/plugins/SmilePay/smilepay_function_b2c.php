<?php
//error_reporting(0);
//���o�q��
function repeatb2c($b2c_store,$post_str,$dcvc,$Verify_key)
{
	$post_strall_1=$post_str.'&Pay_zg=56&Logistics_store='.$b2c_store;
	
	
	$smilepay_gateway_1 = 'https://ssl.smse.com.tw/api/sppayment.asp';
	$ch1 = curl_init();
	curl_setopt($ch1, CURLOPT_URL, $smilepay_gateway_1);
	curl_setopt($ch1, CURLOPT_VERBOSE, 1);
	curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch1, CURLOPT_POST, 1);
	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch1, CURLOPT_POSTFIELDS, $post_strall_1);
	$string_1 = curl_exec($ch1);
	curl_close($ch1);
	$xmls = simplexml_load_string($string_1);
	$Status_1 = $xmls->Status;
	if(strpos($string_1,"<SmilePay>")&&$Status_1=="1")
	{
		//$cccurl="http://ssl.smse.com.tw/api/b2cPaymentU.asp?smseid=".$xmls->SmilePayNO."&dcvc=".$dcvc."&types=web&Verify_key=".$Verify_key;
		//�g�J�q��ú�O���-���O��
      //  $order_id = $xmls->Data_id;
      //  $cccurl_customer= get_admin_url()."post.php?Page=smilepay_b2cup_shipping_gen&order_id=$order_id&smseid=".$xmls->SmilePayNO."&dcvc=". $dcvc ."&Verify_key=".$Verify_key;

		$scvurls="�¨��f�A�t�R�tSmilePay�l�ܽX�G". $xmls->SmilePayNO;

	}
	else
	{
		$scvurls="�¨��f��f�K���ͥ���".$Status_1."<br>�Ц�SmilePay��x��ʦ��߭q��<br>���f������ơG".$b2c_storeid.'/'.$b2c_storename.'/'.$b2c_storeaddress;
	}
	return $scvurls;
}


?>
