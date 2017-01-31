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
        min-height:20px;
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
        min-height:18px;
    } 
    
    .rowB label, .rowB .value {
        /*color:#585858; */
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

</style>
<script type="text/javascript">
  $(function() {
    $(".submitBtn").click(function() {
        var formSerData = $('#onlineEditingForm').serialize();
        var formData = form2object('onlineEditingForm');
        var jsonData = $.toJSON(formData);
        $.ajax({
          type: 'POST',
          url: "<?php echo url_for('Admin/SaveOnlineEditing') ?>",
          data: "data="+jsonData,
          success: function(data){
             data = $.evalJSON(data);
             //$("#generalSettings").unmask();
             $("#adminPanel").unmask();   

             if (data.success == true) {  
                var icon = Ext.MessageBox.INFO;  
                var text = "<?php echo __('Successfully saved the Online Editing settings!'); ?>"; 
             }
             else {
                var icon = Ext.MessageBox.ERROR;
                var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
             }
             
             Ext.MessageBox.show({
               title: '<?php echo __('Online Editing'); ?>',
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
<form name="onlineEditingForm" id="onlineEditingForm" action="" method="post">
<input type="submit" name="submit" value="<?php echo __('Save'); ?>" class="submitBtn">
<div id="generalSettings">
    <ul class="fields"> 
        <li class="rowA">
            <div class="fieldContainer" >
                <label><?php echo __('Online Editing:'); ?></label>
                <div class="value">
            
                    <div style="float:left;width:100%;">
                        <div style="float:left;width:150px;">
                            <input type="radio" name="OnlineEditing" <?php echo ($OnlineEditing == null || empty($OnlineEditing) || $OnlineEditing == "none" ? 'checked' : ''); ?> value="none"> <b><?php echo __('Not active'); ?></b>
                        </div>
                    </div>
                    <br>&nbsp;
                    
                    <div style="float:left;width:100%;">
                        <div style="float:left;width:150px;">
                            <input type="radio" name="OnlineEditing" <?php echo ($OnlineEditing == "zoho" ? 'checked' : ''); ?> value="zoho"> <b>Zoho Writer</b> <img src="/images/admin/zoho.png"><br>
                            <br><b>ApiKey:</b><br> <input name="OnlineEditingZohoApiKey" value="<?php echo $OnlineEditingZohoApiKey; ?>" type="text"><br>
                            <b>Skey:</b><br> <input name="OnlineEditingZohoSkey" value="<?php echo $OnlineEditingZohoSkey; ?>" type="text"><br>
                        </div>
                    </div>
                    <br>&nbsp;      

                </div>
            </div>  
        </li>
    </ul>
</div>     
</form> 
