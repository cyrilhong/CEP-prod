<?php
 $order = new WC_Order($order_id);
 $smseid = WC_Smilepayezcatp_Shipping_Method::get_order_smseid($order_id);
 $tracknum = WC_Smilepayezcatp_Shipping_Method::get_order_tracknum($order_id);
 $shipping_method = @array_shift($order->get_shipping_methods());
 $shipping_method_id = $shipping_method['method_id'];
?>



<img alt="smilepayezcat"  style="max-width: 100%;"  src="<?php echo plugins_url('SmilePay/log/smilepayezcat.png','');?>" />
<table>
        <tr>
            <th style="width: 100px;text-align: right;">速買配追蹤碼：</th>
            <td ><?php echo $smseid; ?></td>
             
        </tr>
        <tr>
            <th style="width: 100px;text-align: right;">託運單號：</th>
            <td ><?php echo $tracknum; ?></td>  
        </tr>
</table>
<?php if((!isset($smseid) || empty($smseid)) ||  (!isset($tracknum) || empty($tracknum))):?>
            <div>
            寄送包裹的長寬高(公分cm):<input type="text" name="sp_length" maxlength="6" style="width: 30px;" id="smilepayezcatp_length"  /> X
            <input type="text" name="sp_width" maxlength="6" style="width: 30px;" id="smilepayezcatp_width" /> X
            <input type="text" name="sp_height" maxlength="6" style="width: 30px;" id="smilepayezcatp_height" /><br>
            </div>

            <div>操作：
            
                    <button style="margin-left: 10px;" type="button" onclick="smilepayezcatp_create();" id="smilepayei_create_btn">產生取貨訂單</button>
                
            </div>
            
             <?php endif; ?>
<p>建立訂單需要一些時間，請耐心等候<br>產生託運單後，請至速買配平台列印託運單，請在14天內完成寄送程序</p>
<script type="text/javascript">

    function smilepayezcatp_create()
    {

        if(checkSmilepayezcatpForm())
        {
            var $=jQuery;
            var sp_length = $("#smilepayezcatp_length").val();
            var sp_width = $("#smilepayezcatp_width").val();
            var sp_height = $("#smilepayezcatp_height").val();
            var arg = "&sp_length="+sp_length + "&sp_width=" + sp_width +"&sp_height=" + sp_height;
            window.open('<?php echo admin_url('admin.php?page=smilepay_ezcatp_api&api_action=create_order&post=' . $_REQUEST['post']);  ?>'+arg,'_self');
        }
        
    }
    function checkSmilepayezcatpForm() {
       
            var $=jQuery;
    
            var sp_length = $("#smilepayezcatp_length").val();
            var sp_width = $("#smilepayezcatp_width").val();
            var sp_height = $("#smilepayezcatp_height").val();
            var error_lwh = "長寬高有不符合的格式(請填入可識別的數字)";
            var lwh_sum = 0;
            var shippingcode = "<?php echo $shipping_method_id ;?>";

            
            if (CheckDecimal(sp_length) && CheckDecimal(sp_width) && CheckDecimal(sp_height))
            {
                lwh_sum = parseInt( Math.round(parseFloat(sp_length) + parseFloat(sp_width) + parseFloat(sp_height)));
           

                if(shippingcode == "smilepay_ezcatp_fridge" || shippingcode == "smilepay_ezcatp_freeze")
                {
                    if(lwh_sum > 120)
                    {
                        alert("超過預期的材積，最大材積總和(長+寬+高):120公分");
                        return false;
                    }
                } 
                else
                {
                    if(lwh_sum > 150)
                    {
                        alert("超過預期的材積，最大材積總和(長+寬+高):150公分");
                        return false;
                    }
                }
            }
            else
            {
                alert(error_lwh);
                return false;
            }
            
            $("#smilepayezcatp_btn_submit").hide();
            return true;
        }

        function CheckDecimal(inputtxt) {
            var format_decimal = /[0-9]+(|.[0-9]+)$/;
   
            if (inputtxt != null && inputtxt.match(format_decimal)){
                return true;
            }
            else {
                return false;
            }
        }   
</script>