<?php
settings_errors(); 
global  $smilepayc2b;
$setting_data=$smilepayc2b->get_setting_data();
$setting_item=$smilepayc2b->get_setting_item();

?>
<form method="post" action="options.php">

    <?php settings_fields( 'smilepay_c2b-settings-main-group' ); ?>
    <?php do_settings_sections( 'smilepay_c2b-settings-main-group' ); ?>
    <h2>基本設定</h2>
    <table class="form-table">

        <tr valign="top">
            <th scope="row">啟用</th>
            <td><input type="checkbox" name="smilepay_c2b_settings[enabled]" value="Y" <?php if( isset($setting_data['enabled']) && $setting_data['enabled'] == 'Y') echo 'checked'; ?> /></td>
        </tr>

        <tr valign="top">
            <th scope="row">商家代號(Dcvc)</th>
            <td><input type="text" name="smilepay_c2b_settings[dcvc]" value="<?php if( isset($setting_data['dcvc'])) echo $setting_data['dcvc']; ?>"  /></td>
        </tr>

        <tr valign="top">
            <th scope="row">參數碼(Rvg2c)</th>
            <td><input type="text" name="smilepay_c2b_settings[rvg2c]" value="<?php if( isset($setting_data['rvg2c'])) echo $setting_data['rvg2c']; ?>"  /></td>
        </tr>

        <tr valign="top">
            <th scope="row">檢查碼(Verify_key)</th>
            <td><input type="text" name="smilepay_c2b_settings[verify_key]" value="<?php if( isset($setting_data['verify_key']))  echo $setting_data['verify_key']; ?>" size="40" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">運費的付款對象</th>
            <td>
                <select name="smilepay_c2b_settings[paid_cs]">
                    <?php foreach($setting_item['paid_cs'] as $key=>$val):?>
                    <option value="<?php echo $key; ?>" <?php if(isset($setting_data['paid_cs'])&& $setting_data['paid_cs']==$key) echo 'selected';?>><?php echo $val; ?></option>
                    <?php endforeach; ?>         
                </select>
                <br>
                運費由何人付款
            </td>
        </tr>

       
     
                   
       
    </table>
   
  
           <?php submit_button(); ?>
</form>
</div>
<script type="text/javascript">

    
</script>
