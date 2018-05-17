<?php
include_once ("smilepay_function.php");
define("SMILEPAY_RESPONSE_DECLARE","SMILEPAY_RESPONSE_DECLARE");

if(isset($_GET['respond']))
    $res=$_GET['respond'];
else
    $res="";
if(isset($_GET['smilepay_respond']))
    $res2=$_GET['smilepay_respond'];
else
    $res2="";


if($res!="" || $res2 != ""){add_action('init', 'smilepay_respond', 90);}

function smilepay_respond(){

//回沖開始


$pay_code = !empty($_REQUEST['Classif']) ? trim($_REQUEST['Classif']) : '';


if (empty($pay_code))
{
	exit ('No Information');
}
else
{     
	switch ($pay_code)
	{
        case 'A':
            $pay_to='SmilePay銀聯卡';

            if( isset($_REQUEST['Foreign']) && $_REQUEST['Foreign'] == 'U')
			    $midfunc="woocommerce_smilepayunion_settings";
            else
                $midfunc="woocommerce_smilepayunion";

            break;
		case 'B':
			$pay_to='SmilePay虛擬帳號';
			$midfunc="woocommerce_SmilePayatm_settings";
		    break;
		case 'C':
			$pay_to='smilepay條碼繳費';
			$midfunc="woocommerce_SmilePaycsv_settings";
		    break;
		case 'D':
			$pay_to='smilepaycn';
		    break;
		case 'E'://OK
			$pay_to='SmilePay超商代碼繳費';
			$midfunc="woocommerce_SmilePayibon_settings";
		    break;
		case 'F':
			$pay_to='SmilePay超商代碼繳費';
			$midfunc="woocommerce_SmilePayfamiport_settings";
		    break;  
		case 'T':
			$pay_to='SmilePay超商取貨付款';
			$midfunc="woocommerce_SmilePayc2c_settings";
		    break;  
        case 'V':
			$pay_to='SmilePay超商取貨付款';
			$midfunc="woocommerce_SmilePayb2c_settings";
		    break;  
        case 'O':
            $pay_to='SmilePay黑貓取貨付款';
			$midfunc="woocommerce_smilepayezcat_settings";
            break;
		default:  
			$pay_to='SmilePay超商代碼繳費';
	}
}
$Data_id=$_REQUEST['Data_id'];
$Amount=$_REQUEST['Amount'];
$Process_date=$_REQUEST['Process_date'];
$Process_time=$_REQUEST['Process_time'];
$Smseid=$_REQUEST['Smseid'];
$Mid_smilepay=$_REQUEST['Mid_smilepay'];

if(isset($_GET['smilepay_respond']))
    $res = $_GET['smilepay_respond'];
else
    $res= "";
if($res=="")
{
    if(isset($_GET['respond']))
        $res=$_GET['respond'];
    else
        $res="";
}
    

global $post, $wpdb, $thepostid, $theorder, $order_status, $woocommerce;



if($pay_code == 'A' && isset($_REQUEST['Foreign']) && $_REQUEST['Foreign'] == 'U') //union
{
    $union_ok = $_REQUEST['Response_id'];
    $od=get_order($res,$Data_id,$Amount);
    
    if($Amount=="0" &&   $union_ok == "0")
    {
        $shopMID=getMID($midfunc);
        if($shopMID!="")
        {
           
         $total=smilepay_get_order_total($Data_id);
         $MIDcode=ShowMID($shopMID,$total,$Smseid);
         if($MIDcode!=$Mid_smilepay){exit("MID Error");}
        }

       
        if(isset($_REQUEST['Errdesc']))
            $err=$_REQUEST['Errdesc'];
        else
            $err="";
       smilepay_ipn_union_fail($Data_id,$Amount,$Smseid,$Process_date . " ". $Process_time,$err);
    }
    elseif( $union_ok == 1 && $od!=false)
    {
      
       $shopMID=getMID($midfunc);
       if($shopMID!="")
       {
         $MIDcode=ShowMID($shopMID,$Amount,$Smseid);
         if($MIDcode!=$Mid_smilepay){exit("MID Error");}
       }

       smilepay_ipn_union_success($Data_id,$Amount,$Smseid,$Process_date . " " . $Process_time);
    }
    elseif($od==false)
    {
        echo 'Not Found Order!';
        exit;
    }
    echo "<Roturlstatus>woook</Roturlstatus>";
    exit;
}



//判斷
if($Amount==""||$Amount=="0"){exit("Not Paid");}
//驗證訂單
$od=get_order($res,$Data_id,$Amount);
if($od==false){exit("Order Error");}
//驗證MID
$shopMID=getMID($midfunc);
if($shopMID!="")
{
  $MIDcode=ShowMID($shopMID,$Amount,$Smseid);
  if($MIDcode!=$Mid_smilepay){exit("MID Error");}
}
//取得訂單狀態
$order_state=get_state($Data_id);
if($order_state=="processing"){exit("Repeat Respond");}
//商家訂單說明改變
change_note_shop($Data_id,$Process_date,$Process_time,$Smseid);
//消費者訂單說明隱藏
change_note_user($Data_id);
//消費者訂單說明增加





add_note_user($Data_id,$Process_date,$Process_time);

//訂單狀態更新
change_state($Data_id);

echo "<Roturlstatus>woook</Roturlstatus>";
exit();
	
//回沖結束	
}
?>
