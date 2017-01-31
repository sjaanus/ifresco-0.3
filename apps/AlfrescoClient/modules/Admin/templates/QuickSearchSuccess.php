<style type="text/css">
.colContainerDeleteObjects .rowField, .colContainerDeleteObjects .form_row {
    display:none;
}

.colContainer {
    border:1px dotted #F0F0F0;
    background-color:#FFFDAA;
    padding:5px;
    list-style:none;
    width:400px;
    min-height:40px;
}

.colForm .formUl {
    list-style:none;
    padding:0;
    margin:0;

    
}

.colForm .formUl li {
    float:left;
    padding-right:10px;
    padding-left:10px;
    border-left:none;
}

.colForm .formUl li li {
    float:none;
    padding:0;
    border:none;
}

.colForm .form_row, .tabdrop .form_row {
    margin-top:5px;
    padding: 10px 10px; margin-bottom: 3px;
    background-color: #efefef;
}

.colForm .metaActions, .tabdrop .metaActions {
    float:right;         
}

.colForm .moveAction, .tabdrop .moveAction {
    cursor: move;
}

.colForm .deleteAction, .tabdrop .deleteAction {
    cursor:pointer;
}
</style>
<script type="text/javascript">
$(document).ready(function() { 
    $(".colContainer").sortable({ 
        connectWith: 'ul',
        dropOnEmpty: true,
        handle : '.moveAction', 
        update : function () { 
          //var order = $('.containerLeft').sortable('serialize'); 
          var orderArr = $('.colContainer').sortable('toArray'); 

          var json = $.JSON.encode(orderArr);
          $("#searchSubmitValues").val(json);
          
          //$("#info").load("process-sortable.php?"+order); 
        } 
    }); 
  
  
    $(".colContainerDeleteObjects").sortable({ 
        connectWith: 'ul',
        dropOnEmpty: true,
        handle : '.moveAction', 
        items: 'li:not(.disabled)',
        cancel: '.disabled',

        update : function () { 
          $(".colContainerDeleteObjects").children("li:not(.disabled)").each(function() {
              var child = $(this);
              child.remove();
          });
        } 
    });
    
    $("#formColAdmin").submit(function() {
      var orderArr = $('.colContainer').sortable('toArray'); 

      var json = $.JSON.encode(orderArr);
      $("#searchSubmitValues").val(json);
      
      var formData = form2object('formColAdmin');
    var jsonData = $.toJSON(formData);

    $.ajax({
      type: 'POST',
      url: "<?php echo url_for('Admin/QuickSearchSubmit') ?>",
      data: "data="+jsonData,
      success: function(data){
         data = $.evalJSON(data);
         //$("#generalSettings").unmask();
         $("#adminPanel").unmask();   

         if (data.success == true) {  
            var icon = Ext.MessageBox.INFO;  
            var text = "<?php echo __('Successfully saved the Quick Search Settings!'); ?>"; 
         }
         else {
            var icon = Ext.MessageBox.ERROR;
            var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
         }
         
         Ext.MessageBox.show({
           title: '<?php echo __('Save Quick Search Settings'); ?>',
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



function addColProperty() {
    $("#propertiesaddlocation").val("quicksearchAdmin");
    propertyAddWindow.show();
}
</script>

<!--<form id="formColAdmin" action="<?php echo url_for("Admin/SearchColumnSetSubmit"); ?>" method="post">-->
<form id="formColAdmin" action="" method="post">
    <input type="hidden" name="edit" value="<?php echo ($FoundSettings==true ? "true" : "false"); ?>">
    <input type="hidden" name="fields" id="searchSubmitValues" value="">
    <input type="submit" value="<?php echo __('Save'); ?>" name="submit" class="submitBtn" id="SubmitBtn">
    
<div style="width:100%;float:left;margin-bottom:5px;">
    <div style="float:left;width:200px;border:1px solid #617ea3;background-color:#cedff5;color:#214573; padding:5px;margin:5px;">
        <a href="javascript:addColProperty()" style="color:#000000;"><img src="/images/icons/add.png" align="absmiddle"> <?php echo __('Add a property'); ?></a>
    </div>
    <div style="margin:5px;float:left;width:200px;height:32px;border:1px solid #617ea3;background:url(/images/admin/user-trash.png) no-repeat;background-color:#cedff5;color:#214573;" class="colContainerDeleteObjects">
        <ul class="colContainerDeleteObjects">
            <li class="disabled">
                <span style="margin-left:36px;font-size:10px;font-weight:italic;" class="disabled"><?php echo __('To delete items - drop them here'); ?></span>
            </li>  
        </ul>
        
    </div>
</div>

<div class="colForm">
    <ul class="formUl">
        <li class="first" style="padding-left:5px;border:0;">
            <ul class="colContainer">
                <?php
                    if ($FoundSettings == true) {
                        $searchSubmitValues = array();
                        foreach ($Fields as $key => $value) { 

                            $buildStr = $value->name."/".$value->class."/".$value->title."/".$value->dataType."/".$value->type."/".$hideShow;
                            $searchSubmitValues[] = $buildStr;  ?>
                            <li id="<?php echo $value->name;?>/<?php echo $value->class; ?>/<?php echo $value->title; ?>/<?php echo $value->dataType; ?>/<?php echo $value->type; ?>"><div class="form_row"><?php echo $value->title;?> <span style="font-size:10px">(<?php echo $value->dataType; ?>)</span><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>
                       <?php }
                       
                       $searchValues = json_encode($searchSubmitValues);
                       ?>
                       <script type="text/javascript">
                        $("#searchSubmitValues").val('<?php echo $searchValues; ?>');
                       </script>
                       <?php
                    }
                ?>
            </ul>
        </li>
    </ul>
</div>
</form>