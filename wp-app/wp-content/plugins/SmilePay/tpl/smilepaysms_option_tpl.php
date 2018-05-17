<?php  
  global $smilepay_sms_config;
  $smilepay_sms_payment_methods = WC()->payment_gateways();
  $smilepay_sms_payment_methods = WC()->payment_gateways();
  $smilepay_sms_shipping_methods =  WC()->shipping()->shipping_methods;
  foreach($smilepay_sms_payment_methods as $smilepay_payment_method)
  {
      foreach ( $smilepay_payment_method as $payment_method ) {
        if($payment_method->enabled== 'yes')
        {

              $tmp = $payment_method->title;
              if(empty($tmp))
                $tmp = $payment_method->method_title;
              $smilepay_select_filter[] = array(
                                                'id' =>$payment_method->id,
                                                'title' =>$tmp
                                            );
              
            
        }
      }
     
  }

  foreach($smilepay_sms_shipping_methods as $shipping_method)
  {
      
    if(  $shipping_method->enabled == 'yes')
    {
        $tmp = $shipping_method->title;
        if(empty($tmp))
            $tmp = $shipping_method->method_title;
        $smilepay_select_filter[]= array(
                                        'id' =>$shipping_method->id,
                                        'title' =>$tmp,
                                        );
    }
           
   }

?>
<div class="wrap">
<h1>速買配SMS 模組設定</h1><hr>
<img src="<?php echo plugins_url( 'smilepay/log/smse.png');?>" alt="smse" ><br>
   
<?php
settings_errors(); 
?>
<form method="post" action="options.php">

    <?php settings_fields( 'smilepaysms-settings-main-group' ); ?>
    <?php do_settings_sections( 'smilepaysms-settings-main-group' ); ?>
    <h2>基本設定</h2>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">商家代號(Dcvc)</th>
        <td><input type="text" name="smilepay_sms_settings[dcvc]" value="<?php echo $smilepay_sms_config['dcvc']; ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">企業系統代碼(Vw2a)</th>
        <td><input type="text" name="smilepay_sms_settings[vw2a]" value="<?php echo $smilepay_sms_config['vw2a']; ?>" /></td>
        </tr>


        <tr valign="top">
        <th scope="row">參數碼(Rvg2c)</th>
        <td><input type="text" name="smilepay_sms_settings[rvg2c]" value="<?php  echo $smilepay_sms_config['rvg2c']; ?>" /></td>
        </tr>
        
            
        <tr valign="top">
        <th scope="row">簡訊帳號(Yhy)</th>
        <td><input type="text" name="smilepay_sms_settings[yhy]" value="<?php echo $smilepay_sms_config['yhy']; ?>" /></td>
        </tr>

            
        <tr valign="top">
        <th scope="row">簡訊代號(Dc2a)</th>
        <td><input type="password" name="smilepay_sms_settings[dc2a]" value="<?php if($smilepay_sms_config['dc2a'])echo '***%smilepaypass%***'; ?>"   /></td>
        </tr>

    </table>
    <div>
        以上參數，請至速買配商家後台，程式串接說明，選擇相關API服務後，即可查詢到該參數
    </div>
    <hr>
    <h2>發送內容設定</h2>
    <h3>訂單狀態</h3>


    <div>
        <input type="checkbox" id="id-order-on-hold-check" name="smilepay_sms_settings[order_on_hold_check]" value="yes" <?php if( $smilepay_sms_config['order_on_hold_check']=='yes') echo 'checked'; ?> >擱置</input><br><br>
        <div  id="id-order-on-hold-region" style="display: none;">
            &nbsp&nbsp&nbsp&nbsp<label for="smilepay_sms_settings[order_on_hold_message]" style="vertical-align: middle;">簡訊內容</label>
            <textarea rows="4" cols="50" maxlength="200" name="smilepay_sms_settings[order_on_hold_message]" style="vertical-align: middle;"><?php echo $smilepay_sms_config['order_on_hold_message'];?></textarea>
            <br><br>
            <label for="smilepay_sms_settings[order_on_hold_check_filter]">何種方式啟用</label>
            <select  multiple="multiple" name="smilepay_sms_settings[order_on_hold_check_filter][]">
             
             <?php foreach($smilepay_select_filter as $select_filter):?>
                <option value="<?php echo $select_filter['id']; ?>" <?php if(isset($smilepay_sms_config['order_on_hold_check_filter']) && in_array( $select_filter['id'],$smilepay_sms_config['order_on_hold_check_filter'])) echo 'selected';?>><?php echo $select_filter['title']; ?></option>
             <?php endforeach; ?>         
            </select>
        </div>
    </div>
     <br>
    <div>
        <input type="checkbox" id="id-order-processing-check" name="smilepay_sms_settings[order_processing_check]" value="yes" <?php if( $smilepay_sms_config['order_processing_check']=='yes') echo 'checked'; ?> >處理中</input><br><br>
        
        <div  id="id-order-processing-region">
            &nbsp&nbsp&nbsp&nbsp<label for="smilepay_sms_settings[order_processing_message]" style="vertical-align: middle;">簡訊內容</label>
            <textarea rows="4" cols="50" maxlength="200" name="smilepay_sms_settings[order_processing_message]" style="vertical-align: middle;"><?php echo $smilepay_sms_config['order_processing_message'];?></textarea>
            
            <br><br>
            <label for="smilepay_sms_settings[order_processing_check_filter]">何種方式啟用</label>
            <select  multiple="multiple" name="smilepay_sms_settings[order_processing_check_filter][]">
             
             <?php foreach($smilepay_select_filter as $select_filter):?>
                <option value="<?php echo $select_filter['id']; ?>" <?php if(isset($smilepay_sms_config['order_processing_check_filter']) && in_array( $select_filter['id'],$smilepay_sms_config['order_processing_check_filter'])) echo 'selected';?>><?php echo $select_filter['title']; ?></option>
             <?php endforeach; ?>         
            </select>
        </div>

    </div>
    <br>
    <div>
        <input type="checkbox" id="id-order-completed-check" name="smilepay_sms_settings[order_completed_check]" value="yes" <?php if( $smilepay_sms_config['order_completed_check']=='yes') echo 'checked'; ?> >完成</input><br><br>
        <div  id="id-order-completed-region" style="display: none;">
            &nbsp&nbsp&nbsp&nbsp<label for="smilepay_sms_settings[order_completed_check]" style="vertical-align: middle;">簡訊內容</label>
            <textarea rows="4" cols="50" maxlength="200" name="smilepay_sms_settings[order_completed_message]" style="vertical-align: middle;"><?php echo $smilepay_sms_config['order_completed_message'];?></textarea>
             <br><br>

            <label for="smilepay_sms_settings[order_completed_check_filter]">何種方式啟用</label>
            <select  multiple="multiple" name="smilepay_sms_settings[order_completed_check_filter][]">
             
             <?php foreach($smilepay_select_filter as $select_filter):?>
                <option value="<?php echo $select_filter['id']; ?>" <?php if(isset($smilepay_sms_config['order_completed_check_filter']) && in_array( $select_filter['id'],$smilepay_sms_config['order_completed_check_filter'])) echo 'selected';?>><?php echo $select_filter['title']; ?></option>
             <?php endforeach; ?>         
            </select>
        </div>
    </div>
    <br>

    <div>
        <input type="checkbox" id="id-order-failed-check" name="smilepay_sms_settings[order_failed_check]" value="yes" <?php if( $smilepay_sms_config['order_failed_check']=='yes') echo 'checked'; ?> >失敗</input><br><br>
        <div  id="id-order-failed-region" style="display: none;">
            &nbsp&nbsp&nbsp&nbsp<label for="smilepay_sms_settings[order_failed_check]" style="vertical-align: middle;">簡訊內容</label>
            <textarea rows="4" cols="50" maxlength="200" name="smilepay_sms_settings[order_failed_message]" style="vertical-align: middle;"><?php echo $smilepay_sms_config['order_failed_message'];?></textarea>
             <br><br>

            <label for="smilepay_sms_settings[order_failed_check_filter]">何種方式啟用</label>
            <select  multiple="multiple" name="smilepay_sms_settings[order_failed_check_filter][]">
             
             <?php foreach($smilepay_select_filter as $select_filter):?>
                <option value="<?php echo $select_filter['id']; ?>" <?php if(isset($smilepay_sms_config['order_failed_check_filter']) && in_array( $select_filter['id'],$smilepay_sms_config['order_failed_check_filter'])) echo 'selected';?>><?php echo $select_filter['title']; ?></option>
             <?php endforeach; ?>         
            </select>
        </div>
    </div>
    <br>

    <div>
        <p><b>簡訊文字替換<br>
            %oid% = 訂單編號<br>
            %customer% = 消費者姓名 <br>
            %paym% = 付款方式 <br>
            %shipm% = 配送方式 <br>
            範例:<br>
            &nbsp&nbsp&nbsp&nbsp訂單編號11<br>
            &nbsp&nbsp&nbsp&nbsp轉換前 =>親愛的客戶您好,您今天於XXX購物,您的訂單編號是%oid%<br>
            &nbsp&nbsp&nbsp&nbsp轉換後 =>親愛的客戶您好,您今天於XXX購物,您的訂單編號是11<br><br>
            <div style="color: red;">簡訊通數計算，請參閱速買配商家後台說明<br>
                此模組不會告知您簡訊發送結果，請自行至速買配商家後台查詢<br>
            </div>

        </b></p>
    </div>
    <div> <a href="http://www.smilepay.net/" target="_blank" style="height:100px;"><img  border="0"  src="<?php echo plugins_url( 'smilepay/log/sp-logo.png');?>" alt="smilepay" ></a></div>
    <?php submit_button(); ?>

</form>
</div>

<script type="text/javascript">
    jQuery(document).ready(
        function ($) {

            jQuery('#id-order-processing-check').on('change', function () {

                if (this.checked) {

                    jQuery('#id-order-processing-region').show();
                }
                else
                    jQuery('#id-order-processing-region').hide();
            });

            jQuery('#id-order-completed-check').on('change', function () {

                if (this.checked) {

                    jQuery('#id-order-completed-region').show();
                }
                else
                    jQuery('#id-order-completed-region').hide();
            });

            jQuery('#id-order-on-hold-check').on('change', function () {

                if (this.checked) {

                    jQuery('#id-order-on-hold-region').show();
                }
                else
                    jQuery('#id-order-on-hold-region').hide();
            });

            jQuery('#id-order-failed-check').on('change', function () {

                if (this.checked) {

                    jQuery('#id-order-failed-region').show();
                }
                else
                    jQuery('#id-order-failed-region').hide();
            });
            

            if (jQuery('#id-order-processing-check').attr('checked')) {
                jQuery('#id-order-processing-region').show();
               
            }
            else
                jQuery('#id-order-processing-region').hide();

            if (jQuery('#id-order-completed-check').attr('checked')) {
                jQuery('#id-order-completed-region').show();
            }
            else
                jQuery('#id-order-completed-region').hide();

            if (jQuery('#id-order-on-hold-check').attr('checked')) {
                jQuery('#id-order-on-hold-region').show();
            }
            else
                jQuery('#id-order-on-hold-region').hide();

            if (jQuery('#id-order-failed-check').attr('checked')) {
                jQuery('#id-order-failed-region').show();
            }
            else
                jQuery('#id-order-failed-region').hide();

        });
</script>