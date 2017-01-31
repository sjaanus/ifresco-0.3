<script type="text/javascript">
$(document).ready(function() {

    $("#allowedaspects").asmSelect({
        addItemTarget: 'top',
        sortable: true,
        url: '<?php echo url_for('Admin/AspectListValues') ?>?values=<?php echo $AllowedAspects; ?>'
    });
    
     $(function() {
        $(".submitBtn").click(function() {
            //var formSerData = $('#formAspects').serialize();
            var formData = form2object('formAspects');
            var jsonData = $.toJSON(formData);

            $.ajax({
              type: 'POST',
              url: "<?php echo url_for('Admin/SaveAspectList') ?>",
              data: "data="+jsonData,
              success: function(data){
                 data = $.evalJSON(data);
                 //$("#generalSettings").unmask();
                 $("#adminPanel").unmask();   

                 if (data.success == true) {  
                    var icon = Ext.MessageBox.INFO;  
                    var text = "<?php echo __('Successfully saved the aspect list!'); ?>"; 
                 }
                 else {
                    var icon = Ext.MessageBox.ERROR;
                    var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
                 }
                 
                 Ext.MessageBox.show({
                   title: '<?php echo __('Save aspects'); ?>',
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

function loadDefault() {
    var defaultList = ["cm:generalclassifiable",
                       "cm:complianceable",
                       "cm:dublincore",
                       "cm:effectivity",
                       "cm:summarizable",
                       "cm:versionable",
                       "cm:templatable",
                       "cm:emailed",
                       "emailserver:aliasable",
                       "cm:taggable",
                       "app:inlineeditable",
                       "cm:geographic",
                       "exif:exif"];
                       
   var defaultList = defaultList.join(",");
   $("#allowedaspectsContainer").html('<select id="allowedaspects" multiple="multiple" name="allowedAspects[]" title="<?php echo __('Select an aspect...'); ?>"></select>');
   //$("#formAdminMeta").mask("Loading",300);

   $("#allowedaspects").asmSelect({
        addItemTarget: 'top',
        sortable: true,
        url: '<?php echo url_for('Admin/AspectListValues') ?>?values='+defaultList,
        succes:function(data) {

        }
    });
    /*var values = $("#allowedaspects").val();  
    while (values == null) {
        values = $("#allowedaspects").val();                              
    }
    $("#formAdminMeta").unmask();*/   
}
</script>
<form id="formAspects" action="" method="post">
<div id="formOptions">
    <input type="submit" value="<?php echo __('Save'); ?>" name="submit" class="submitBtn">
</div>   
<div style="float:left;width:400px;border:1px solid #617ea3;background-color:#cedff5;color:#214573; padding:5px;margin:5px;">
    <label for="allowedaspects"><?php echo __('Choose Aspects:'); ?></label>
    <div id="allowedaspectsContainer"><select id="allowedaspects" multiple="multiple" name="allowedAspects[]" title="<?php echo __('Select an aspect...'); ?>"></select></div>  <br>
    <a href="javascript:loadDefault();"><?php echo __('Delete all & load Default-List!'); ?></a>
</div>
</form>