<?php
    global $smilepayc2b;
    
    if(isset($smilepayc2b))
    {
        $error=$smilepayei->error_message;
        $order_id= $_REQUEST['post'];
        $smseid=$smilepayc2b->get_order_c2b_smseid($order_id);

    }
    $setting_data=$smilepayc2b->get_setting_data();
    $setting_item=$smilepayc2b->get_setting_item();
    
?>

<div id="smilepayc2b_content">
    <?php if(isset( $error) && !empty($error)): ?>
    <p class="notice notice-error"><?php echo $error;?></p>
    <?php else: ?>
    <table>
        <tr>
            <?php if(!isset($smseid) || empty($smseid)):?>
            <th style="width: 100px;text-align: right;">操作：</th>
            <td ><button style="margin-left: 10px;" type="button" onclick="smilepayc2b_create();" id="smilepayei_create_btn">產生退貨便代碼</button>
            </td>
            <?php else:?>
            <th style="width: 100px;text-align: right;">速買配追蹤碼：</th>
            <td ><?php echo $smseid; ?>
            </td>    
            <?php endif; ?>
               
        </tr>
         
    </table>
    <p style="margin-left: 20px;">
        請於產生退貨便號碼<strong style="color: red;">7日</strong>內，交至超商門市寄送，逾期將失效<br>
        相關<strong>物流狀態</strong>請至<a href="https://ssl.smse.com.tw/pay_gr/INDEX_LOGIN.ASP" target="_blank"><strong>速買配商家後臺</strong></a>查詢
    </p>
    <br>
   

    <?php endif;?>
</div>

<script type="text/javascript">

    function smilepayc2b_create()
    {
        window.open('<?php echo admin_url('admin.php?page=smilepay_c2b_api&api_action=create_order&post=' . $_REQUEST['post']);  ?>','_self');
        /*jQuery('#smilepayei_create_btn').attr('disabled',true);
        jQuery.post(
           '<?php echo 'admin-ajax.php'; ?>', 
            {
            'action': 'smilepayc2b_create_order',
            'post': '<?php echo $_REQUEST['post'];?>'
            }, 
            function(response){
                
                jQuery("#smilepayei_content").html(response);
    
            },"html"
            
        );*/
    }
   
    


</script>