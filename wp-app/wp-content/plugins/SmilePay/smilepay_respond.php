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

//�^�R�}�l


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
            $pay_to='SmilePay���p�d';

            if( isset($_REQUEST['Foreign']) && $_REQUEST['Foreign'] == 'U')
			    $midfunc="woocommerce_smilepayunion_settings";
            else
                $midfunc="woocommerce_smilepayunion";

            break;
		case 'B':
			$pay_to='SmilePay�����b��';
			$midfunc="woocommerce_SmilePayatm_settings";
		    break;
		case 'C':
			$pay_to='smilepay���Xú�O';
			$midfunc="woocommerce_SmilePaycsv_settings";
		    break;
		case 'D':
			$pay_to='smilepaycn';
		    break;
		case 'E'://OK
			$pay_to='SmilePay�W�ӥN�Xú�O';
			$midfunc="woocommerce_SmilePayibon_settings";
		    break;
		case 'F':
			$pay_to='SmilePay�W�ӥN�Xú�O';
			$midfunc="woocommerce_SmilePayfamiport_settings";
		    break;  
		case 'T':
			$pay_to='SmilePay�W�Ө��f�I��';
			$midfunc="woocommerce_SmilePayc2c_settings";
		    break;  
        case 'V':
			$pay_to='SmilePay�W�Ө��f�I��';
			$midfunc="woocommerce_SmilePayb2c_settings";
		    break;  
        case 'O':
            $pay_to='SmilePay�¿ߨ��f�I��';
			$midfunc="woocommerce_smilepayezcat_settings";
            break;
		default:  
			$pay_to='SmilePay�W�ӥN�Xú�O';
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



//�P�_
if($Amount==""||$Amount=="0"){exit("Not Paid");}
//���ҭq��
$od=get_order($res,$Data_id,$Amount);
if($od==false){exit("Order Error");}
//����MID
$shopMID=getMID($midfunc);
if($shopMID!="")
{
  $MIDcode=ShowMID($shopMID,$Amount,$Smseid);
  if($MIDcode!=$Mid_smilepay){exit("MID Error");}
}
//���o�q�檬�A
$order_state=get_state($Data_id);
if($order_state=="processing"){exit("Repeat Respond");}
//�Ӯa�q�满������
change_note_shop($Data_id,$Process_date,$Process_time,$Smseid);
//���O�̭q�满������
change_note_user($Data_id);
//���O�̭q�满���W�[





add_note_user($Data_id,$Process_date,$Process_time);

//�q�檬�A��s
change_state($Data_id);

echo "<Roturlstatus>woook</Roturlstatus>";
exit();
	
//�^�R����	
}
?>
