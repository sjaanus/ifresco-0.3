<?php
    $Fields = $sf_data->getRaw('Fields');    
    $optionString = "";
    if (count($Fields) > 0) {
        foreach ($Fields as $Field) {
            $optionString .= '<option value="'.$Field["name"].'">'.$Field["name"].'</option>';
        }
    }    

?>
<style type="text/css">
    #lookups {}
        #lookups .headerLeft {
            width:40%;
        }
        #lookups label {
            font-weight:bold;
            font-size:13px;

            display:block;
            float:left;
            height:20px;
        }
        
        #lookups .headerRight {
            width:55%;
        }
        #lookups .remove {
            float:right;
            width:5%;
            text-align:right;
        }
        #lookups ul {}
            #lookups ul .lookupElement {
                border:1px dotted #99BBE8;
                
                display:block;
                float:left;
                width:80%;
                margin:10px;
                background-color:#cad9ec;
                padding:5px;
            }
                #lookups ul .lookupElement div {
                    
                    
                }
                #lookups .metafield {
                    float:left;
                    
                    width:40%;
                }
                #lookups .category {
                    float:left;
                }
</style>
<script type="text/javascript">
   $(function() {
    $(".submitBtn").click(function() {
        try {
            var formSerData = $('#lookupForm').serialize();
            var formData = form2object('lookupForm');
            var jsonData = $.toJSON(formData);
            
            var formArray = $('#lookupForm').serializeArray();
            $.each(formArray,function(index,item) {
                var name = item.name;
                var value = item.value;
                if (value === null || value.length == 0)
                    throw "<?php echo __('At least one category is required!'); ?>";
            });
        
            $.ajax({
              type: 'POST',
              url: "<?php echo url_for('Admin/SaveLookups') ?>",
              data: "data="+jsonData,
              success: function(data){
                 data = $.evalJSON(data);
                 //$("#generalSettings").unmask();
                 $("#adminPanel").unmask();   

                 if (data.success == true) {  
                    var icon = Ext.MessageBox.INFO;  
                    var text = "<?php echo __('Successfully saved the lookups!'); ?>"; 
                 }
                 else {
                    var icon = Ext.MessageBox.ERROR;
                    var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
                 }
                 
                 Ext.MessageBox.show({
                   title: '<?php echo __('Lookups'); ?>',
                   msg: text,
                   buttons: Ext.MessageBox.OK,
                   icon: icon
                 });

              },
              beforeSend: function() {
                $("#adminPanel").mask("<?php echo __('Loading...'); ?>",300);         
              }
            });
        
        }
        catch (e) {
            Ext.MessageBox.show({
               title: '<?php echo __('Error'); ?>',
               msg: e,
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.ERROR
           });
        }

        return false;
    });
    
    
    $(".createBtn").click(function() {
        createCategoryField(null,null,null);
    });
    
    
  });
  
  function createCategoryField(field,category,single) {
      var randomnumber = Math.floor(Math.random() * (2147483 - 0 + 1)) + 0;
        $("#LookupItems").append('<li class="lookupElement" id="lookupElement'+randomnumber+'"><div style="width:100%;border-bottom:1px solid #99BBE8;float:left;margin-bottom:10px;"><label class="headerLeft"><?php echo __('Select a field:'); ?></label><label class="headerRight"><?php echo __('Select a category:'); ?></label><span class="remove"><img src="/images/icons/cross.png" style="cursor:pointer;" class="removeBtn" /></span></div><div class="metafield"><select name="fieldItem[]" id="fieldItem'+randomnumber+'"><?php echo $optionString; ?></select><br><br><input type="radio" name="singleSelect'+randomnumber+'" value="0" '+(single !== null && single !== false ? '' : 'checked')+'> <?php echo __('Multiselect'); ?> <input type="radio" name="singleSelect'+randomnumber+'" value="1" '+(single === true ? 'checked' : '')+'> <?php echo __('Singleselect'); ?></div><div class="category"><div id="divCategoryTree'+randomnumber+'"></div><input type="hidden" name="categoryNodeId'+randomnumber+'" class="categoryNodeId'+randomnumber+'" value=""><input type="hidden" name="categoryNum[]" value="'+randomnumber+'"></div><div style="float:left;padding-left:10px;padding-top:2px;"><i><?php echo __('Single select only!'); ?></i></div></li>');
        
        $(".removeBtn").click(function() {
            $(this).parent().parent().parent().remove();
        });
        
        var catUrl = '<?php echo url_for('Categories/GetCategoryCheckTree'); ?>';
        if (category !== null) {
            catUrl += '?values=workspace://SpacesStore/'+category;
            $('#lookupForm .categoryNodeId'+randomnumber).val(category);
        }
        
        if (field !== null) {
            $('#fieldItem'+randomnumber).val(field);
        }
        
        var panel = new Ext.Panel({
            frame:false,
            header:false,
            collapsible:true,
            autoHeight:true,
            renderTo:'divCategoryTree'+randomnumber,
            width:200,
            items:[{
                xtype: 'treepanel',
                disabled:false,
                id:'categoryTree'+randomnumber,
                bodyStyle:'background:#ffffff;padding:0;margin:0;border:none;',
                width:200,
                height:200,
                useArrows:false,
                autoScroll:true,
                enableDD:false,
                containerScroll:true,
                frame:true,
                border:false,
                isFormField:true,
                rootVisible:false,
                dataUrl:catUrl,
                root:{
                    nodeType:'async',
                    id:'root',
                    draggable:false,
                    text:'<?php echo __('Categories'); ?>',
                    expanded:true,
                    visible:false,
                    border:false
                },
                listeners:{
                    'checkchange': function(node,checked) {
                        if (checked == true) {
                            var treeChecked = Ext.getCmp('categoryTree'+randomnumber).getChecked();
                            $.each(treeChecked,function(index,checkedNode) {
                                if (node != checkedNode) {
                                    checkedNode.getUI().toggleCheck(false);
                                    
                                }
                            });
                            
                            $('#lookupForm .categoryNodeId'+randomnumber).val(node.attributes.nodeId);
                        }
                    }  
                } 
            }]
          });
  }
</script>


<form name="lookupForm" id="lookupForm" action="" method="post">
    <input type="submit" name="submit" value="<?php echo __('Save'); ?>" class="submitBtn"> <input type="button" class="createBtn" value="<?php echo __('Create a new lookup field'); ?>">

    <div id="lookups">
        <ul id="LookupItems">
            <?php
            if (count($Lookups) > 0) {
                foreach ($Lookups as $Lookup) {
                    ?>
                    <script type="text/javascript">
                        $(function() {
                            createCategoryField('<?php echo $Lookup->field;?>','<?php echo $Lookup->data;?>',<?php echo $Lookup->single;?>);
                        });
                    </script>
                 <?php   
                }
            }
            ?>
        </ul>
    </div>
</form> 
