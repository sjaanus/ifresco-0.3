<?php
    $Settings = $sf_data->getRaw('Settings');            
?>
<style type="text/css">
    #generalSettings {
        overflow:auto;
                 
    }   
    
    #generalSettings .fields {
        list-style:none;
        margin:0;
        padding:0;
        width:400px;
        float:left;
        display:block;
        margin-right:2px;
    } 
    
    #generalSettings .fields li {
        margin:0;
        padding:0;
        list-style:none;
    }
    
    #generalSettings .fields li .fieldContainer {
        margin:0;
        padding:0;

        font-size:11px;
        border:1px dotted #99BBE8;
        float:left;  
     
        margin-top:1px;
        padding:2px;
        width:400px; 
        margin:2px;
    }
    
    #generalSettings .fields li .fieldContainer:hover {
        background-color:#F5E218;                   
    }
    
    #generalSettings .fields li label {
        font-weight:bold;

        border-right:1px dotted #99BBE8;
        float:left; 
        width:120px;
        padding:2px;    
          
    }
    
    #generalSettings .fields li .value { 
        background-color:#fff;
        display:block;
        padding:2px;
        float:left;
        width:270px;

    } 
    
    .rowB label, .rowB .value {
        color:#000;
    }

    
    .rowA label, .rowA .value {
        color:#000;                          
    }
    
    .rowA .fieldContainer {
        background-color:#E0E8F6;           
    }
    
    .rowB .fieldContainer {
        background-color:#F0F0F0;  
    }

    
    #generalSettings a {
        color:#15428b;
    }
    
    #generalSettings input {
        border:1px solid #E0E8F6;
        font-size:14px;
        padding:1px;
    }
    
    #generalSettings input:hover {
        border:1px solid #99BBE8;
    }
    
    #generalSettings input:focus {
        border:1px solid #99BBE8;
        background-color:#FFFEE1;  
    }

</style>
<script type="text/javascript">
  $(function() {
    $(".submitBtn").click(function() {
        var formSerData = $('#emailSettingsForm').serialize();
        var formData = form2object('emailSettingsForm');
        
        var jsonData = $.toJSON(formData);

        $.ajax({
          type: 'POST',
          url: "<?php echo url_for('Admin/SaveEmailSettings') ?>",
          data: "data="+jsonData,
          success: function(data){
             data = $.evalJSON(data);
             //$("#generalSettings").unmask();
             $("#adminPanel").unmask();   

             if (data.success == true) {  
                var icon = Ext.MessageBox.INFO;  
                var text = "<?php echo __('Successfully saved the Email settings!'); ?>"; 
             }
             else {
                var icon = Ext.MessageBox.ERROR;
                var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
             }
             
             Ext.MessageBox.show({
               title: '<?php echo __('Email Settings'); ?>',
               msg: text,
               buttons: Ext.MessageBox.OK,
               icon: icon
             });

          },
          beforeSend: function() {
            $("#adminPanel").mask("<?php echo __('Loading...'); ?>",300);         
          }
        });

        return false;
    });
  });
  
</script>
<form name="emailSettingsForm" id="emailSettingsForm" action="" method="post">
<input type="submit" name="submit" value="<?php echo __('Save'); ?>" class="submitBtn">
<div id="generalSettings">
    <ul class="fields"> 
        <?php 
            $row = "rowB";
            $EmailSettings = $sf_data->getRaw('EmailSettings');
            foreach ($EmailSettings as $SettingKey => $Setting) {
                $SettingName = $Setting["name"];
                $SettingValue = $Setting["value"];
                if ($row == "rowB")
                    $row = "rowA";
                else
                    $row = "rowB";
                
                $checked = ""; 
                if (isset($Setting["checked"]))
                    $checked = " checked";
                           
                if (isset($Setting["type"])) {
                    $type = $Setting["type"];
                    
                }
                else
                    $type = "text";
                
                if (empty($SettingName)) { ?>
                    <li style="margin-top:10px;margin-bottom:10px;">&nbsp;</li>
                <?php 
                    continue;
                } 
                    
            ?> 
            <li class="<?php echo $row; ?>">
                <div class="fieldContainer" >
                    <label><?php echo $SettingName; ?>:</label>
                    <div class="value">
                        
                        
                        <div style="float:left;width:100%;">
                            <div style="float:left;width:150px;">
                                <input type="<?php echo $type; ?>" name="<?php echo $SettingKey; ?>" value="<?php echo $SettingValue; ?>"<?php echo $checked; ?>>
                            </div>
                        </div>
                        <br>&nbsp;
                        
                    </div>
                </div>  
            </li>
        <?php } ?> 
    </ul>
</div>     
</form> 
