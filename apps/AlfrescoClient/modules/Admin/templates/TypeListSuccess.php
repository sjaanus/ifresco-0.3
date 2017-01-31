<script type="text/javascript">
$(document).ready(function() {

    $("#allowedTypes").asmSelect({
        addItemTarget: 'top',
        sortable: true,
        url: '<?php echo url_for('Admin/TypeListValues') ?>?values=<?php echo $AllowedTypes; ?>'
    });
    
     $(function() {
        $(".submitBtn").click(function() {
            //var formSerData = $('#formAdminMeta').serialize();
            var formData = form2object('formAdminMeta');
            var jsonData = $.toJSON(formData);

            $.ajax({
              type: 'POST',
              url: "<?php echo url_for('Admin/SaveTypeList') ?>",
              data: "data="+jsonData,
              success: function(data){
                 data = $.evalJSON(data);
                 //$("#generalSettings").unmask();
                 $("#adminPanel").unmask();   

                 if (data.success == true) {  
                    var icon = Ext.MessageBox.INFO;  
                    var text = "<?php echo __('Successfully saved the types list!'); ?>"; 
                 }
                 else {
                    var icon = Ext.MessageBox.ERROR;
                    var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
                 }
                 
                 Ext.MessageBox.show({
                   title: '<?php echo __('Save content types'); ?>',
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
});

function deleteAll() {
   $("#allowedtypesContainer").html('<select id="allowedTypes" multiple="multiple" name="allowedTypes[]" title="<?php echo __('Select a content type...'); ?>"></select>');
   //$("#formAdminMeta").mask("Loading",300);

   $("#allowedTypes").asmSelect({
        addItemTarget: 'top',
        sortable: true,
        url: '<?php echo url_for('Admin/TypeListValues') ?>',
        succes:function(data) {
        }
    });
}
</script>
<form id="formAdminMeta" action="" method="post">
<div id="formOptions">
    <input type="submit" value="<?php echo __('Save'); ?>" name="submit" class="submitBtn">
</div>   
<div style="float:left;width:400px;border:1px solid #617ea3;background-color:#cedff5;color:#214573; padding:5px;margin:5px;">
    <label for="allowedtypes"><?php echo __('Choose Content Type:'); ?></label>
    <div id="allowedtypesContainer"><select id="allowedTypes" multiple="multiple" name="allowedTypes[]" title="<?php echo __('Select a content type...'); ?>"></select></div>  <br>
    <a href="javascript:deleteAll();"><?php echo __('Delete all'); ?></a>
</div>
</form>