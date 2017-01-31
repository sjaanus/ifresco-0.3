<style type="text/css">
#templateOptions {
    padding:10px;

}

.metaForm {
    
}

.metaForm .formUl {
    list-style:none;
    padding:0;
    margin:0;

    
}

.metaForm .formUl li {
    float:left;
    padding-right:10px;
    padding-left:10px;
    border-left:none;
}

.metaForm .formUl li li {
    float:none;
    padding:0;
    border:none;
}

.metaForm .formUl li .rte-toolbar ul li {
    float:left;
    padding:0;
    border:none;
}



.metaForm .first {
    padding:0;
    border:none;
    padding-left:10px;
    
}

.metaForm .metaDataInput {
    
    width:170px;
    padding:5px;
    border:1px solid #ddd;
    background:#fafafa;
    font:12px Verdana,sans-serif;
    -moz-border-radius:0.4em;
    -khtml-border-radius:0.4em;
    text-align:left;
}

.metaForm h2 {
    color:#039CE4;  
    font-family: Verdana,sans-serif;
    font-weight:normal;
    margin:0;
    padding:0;
    margin-bottom:5px;
}
.metaForm .metaDataInput:hover, .metaDataInput:focus {
    border-color:#c5c5c5;
    background:#f6f6f6;
} 

.metaForm label {
    display:block;
    padding:2px;
    color:#585858;
    font:12px Verdana,sans-serif;
    
}


.metaForm .submit {
    float:left;
    width:100%;
    padding-left:10px;
    margin-top:30px;

}

.metaForm .form_row, .tabdrop .form_row {
    margin-top:5px;
    padding: 10px 10px; margin-bottom: 3px;
    background-color: #efefef;
}

.metaForm .metaActions, .tabdrop .metaActions {
    float:right;         
}

.metaForm .inRow {
    
}

.metaForm .moveAction, .tabdrop .moveAction {
    cursor: move;
}

.metaForm .deleteAction, .tabdrop .deleteAction {
    cursor:pointer;
}

.metaForm .containerLeft, .containerRight, .tabdrop {
    border:1px dotted #F0F0F0;
    background-color:#FFFDAA;
    padding:5px;
    list-style:none;
    width:200px;
    min-height:40px;
}

.tabdrop {
    min-height:50px;
}

.metaForm  .containerDeleteObjects {
    width:100px;   
    border:1px dotted #F0F0F0;
    background-color:#FFFDAA;
    padding:5px;
    list-style:none;

}

.metaForm  .containerDeleteObjects .rowField {
    display:none;
}

.metaForm .editable {
    
}

</style>


<script type="text/javascript"> 


function createDeleteAction() {
  $(".deleteAction").click(function() {
    var metaActions = $(this).parent();
    metaActions.parent().slideUp('slow', function() {
        metaActions.parent().remove();
    });
  });    
}

function createElements(objEl) {
    if ($("#descBtn").is(".ui-state-active")) {
        $("#descBtn").removeClass("ui-state-active");   
        

        objEl.prepend('<li><div class="form_row">'+
                    '<div class="inRow"><div class="editable"></div></div>'+
                    '<div class="metaActions">'+
                        '<img src="/images/icons/delete.png" class="deleteAction"> '+
                      '<img src="/images/icons/arrow_out.png" class="moveAction">'+
                    '</div>'+
                '</div></li>');
                
        $('.editable').inlineEdit();
        createDeleteAction();
    }  
    
    if ($("#headingBtn").is(".ui-state-active")) {
        $("#headingBtn").removeClass("ui-state-active");   
        

        objEl.prepend('<li><div class="form_row">'+
                    '<div class="inRow"><h2 class="editable"></h2></div>'+
                    '<div class="metaActions">'+
                        '<img src="/images/icons/delete.png" class="deleteAction"> '+
                      '<img src="/images/icons/arrow_out.png" class="moveAction">'+
                    '</div>'+
                '</div></li>');
                
        $('.editable').inlineEdit();
        createDeleteAction();
    }  
}


var tabCount = 0;
var metaTabs = null;
Ext.onReady(function(){
    // basic tabs 1, built from existing content
    var tabs = new Ext.TabPanel({
        renderTo: 'metaTabs',
        width:450,
        activeTab: 0,
        frame:true,
        plain:true,
        closable: true,                  
        id:'meta-tabs',
        defaults:{autoHeight: true},
        listeners: {
            beforeremove: function(tabPanel, tab) {
                $("#"+tab.id+" ul").children().each(function() {
                    var child = $(this);   
                    $(".containerDeleteObjects").append(child);
                });   
            }
        }
    });
    
    metaTabs = tabs;
});


$(document).ready(function() { 
  $(".containerLeft").sortable({ 
    connectWith: 'ul',
    dropOnEmpty: true,
    handle : '.moveAction', 
    update : function () { 
      //var order = $('.containerLeft').sortable('serialize'); 
      var orderArr = $('.containerLeft').sortable('toArray'); 
      
      var json = $.JSON.encode(orderArr);
      $("#col1Values").val(json);
      
      //$("#info").load("process-sortable.php?"+order); 
    } 
  }); 
  
  $(".containerRight").sortable({ 
    connectWith: 'ul',
    dropOnEmpty: true,
    handle : '.moveAction', 
    update : function (event,ui) { 
      //var order = $('.formUl').sortable('serialize'); 
      
      var orderArr = $('.containerRight').sortable('toArray'); 
      
      var json = $.JSON.encode(orderArr);
      $("#col2Values").val(json);
      
      //$("#info").load("process-sortable.php?"+order); 
    } 
  });
  
  $(".containerDeleteObjects").sortable({ 
    connectWith: 'ul',
    dropOnEmpty: true,
    handle : '.moveAction', 
    items: 'li:not(.disabled)',
    cancel: '.disabled',

    update : function () { 
      var order = $('.formUl').sortable('serialize'); 
      
      //$("#info").load("process-sortable.php?"+order); 
    } 
  });
  
  createDeleteAction();
  
  
  $(".containerLeft").click(function() {
      createElements($(this));
  });
  
  $(".containerRight").click(function() {
      createElements($(this));
  });
  
  
  
  
  $("#multiColumns").click(function(){
      if ($("#multiColumns").is(":checked")) {
        //$(".containerRight").css({'display':'block'});
        $(".containerRight").slideDown();

      }
      else {
        //$(".containerRight").css({'display':'none'});
        
        $(".containerRight").slideUp();
        
        var col1Value = $("#col1Values").val();
        if (col1Value == "" || col1Value == null) 
            col1Value = {};
        else   
            col1Value = $.JSON.decode(col1Value);
        
        var col2Value = $("#col2Values").val(); 
        if (col2Value == "" || col2Value == null) 
            col2Value = {};
        else   
            col2Value = $.JSON.decode(col2Value);
        
        $(".containerRight").children().each(function() {
            var child = $(this);
            var name = child.attr('id');
            var indexCol2 = col2Value.indexOf(name);

            if (indexCol2 >= 0)
                delete col2Value[indexCol2];
                
            col1Value.push(name);
            
            $(".containerLeft").append(child);
        });
        
        col1Value = $.JSON.encode(col1Value);
        $("#col1Values").val(col1Value);
        
        col2Value = $.JSON.encode(col2Value);
        $("#col2Values").val(col2Value);
      }
  });
  

  $(".addTab").click(function() {
      if (tabCount == 0) {
          $("#metaTabs").css({'visibility':'visible'}); 
      }

      addMetaTab(tabCount,"-");
      tabCount++;
  });
  
  
  $("#formAdminMeta").submit(function() {
     
      if (metaTabs.items.length > 0) {
        var finalObj = {};
        var tabs = [];
        for (var i = 0; i < metaTabs.items.length; i++) {
            
            var title = metaTabs.items.get(i).title;

            var id = metaTabs.items.get(i).id;
            var items = [];
            
            //alert(metaTabs.items.get(i).title);
            //finalObj.tabs.add(metaTabs.items.get(i).title);
            
            $('#'+id+'_list').children().each(function() {
                var child = $(this);
                var prop_id = child.attr('id');
                items.push(prop_id);
            })
            
            var tabitem = {title:title,items:items};
            tabs.push(tabitem);
            
        }
        
        Ext.apply(finalObj, {
              tabs:tabs
        });
        
        var json = $.JSON.encode(finalObj);
        $("#tabsValues").val(json);
      }
      var formData = form2object('formAdminMeta');
        var jsonData = $.toJSON(formData);

        $.ajax({
          type: 'POST',
          url: "<?php echo url_for('Admin/TemplateDesignerSubmit') ?>",
          data: "data="+jsonData,
          success: function(data){
             data = $.evalJSON(data);
             //$("#generalSettings").unmask();
             $("#adminPanel").unmask();   

             if (data.success == true) {  
                var icon = Ext.MessageBox.INFO;  
                var text = "<?php echo __('Successfully saved the template!'); ?>"; 
             }
             else {
                var icon = Ext.MessageBox.ERROR;
                var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
             }
             
             Ext.MessageBox.show({
               title: '<?php echo __('Save Template'); ?>',
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


function restoreTabs(title) {
    
      if (tabCount == 0) {
          $("#metaTabs").css({'visibility':'visible'}); 
      }
      var tempCount = tabCount;
      addMetaTab(tabCount,title);
      tabCount++;
      return tempCount;
} 

function addMetaTab(tabId,tabTitle){
        var tabPanel = Ext.getCmp('meta-tabs');
        if (tabPanel) {
            tabPanel.add({
                title: tabTitle,
                id:'tab_drop_'+tabId,
                closable:true,
                html:'<ul id="tab_drop_'+tabId+'_list" style="margin:10px;" class="tabdrop"></ul>',
                tbar:[{
                    xtype:'textfield',
                    name:tabId+'_titleField',
                    id:tabId+'_titleField'
                },
                {
                    xtype:'button',
                    text:'<?php echo __('Save title'); ?>',
                    listeners: {
                        click: function(button,eobject) {                     
                            var titleField = Ext.getCmp(tabId+'_titleField');
                            if (titleField) {
                                var value = titleField.getValue();
                                if (value != "" && value.length > 0 && typeof value != 'undefined') {
                                    var tab = Ext.getCmp('tab_drop_'+tabId);
                                    if (tab) {
                                        tab.setTitle(value);
                                    }
                                }
                            }
                        }
                    }
                }]
            }).show();
        }
        
        $("#tab_drop_"+tabId+"_list").sortable({ 
            connectWith: 'ul',
            dropOnEmpty: true,
            handle : '.moveAction', 
            update : function (event,ui) { 
              var orderArr = $("#tab_drop_"+tabId+"_list").sortable('toArray'); 
            } 
        });

}

function addProperty(where) {
    $("#propertiesaddlocation").val(where);
    propertyAddWindow.show();
}


</script>
<div style="width:95%">
    <!--<form id="formAdminMeta" action="<?php echo url_for('Admin/TemplateDesignerSubmit') ?>" method="post">-->
    <form id="formAdminMeta" name="formAdminMeta" action="" method="post">
    <input type="hidden" name="edit" value="<?php echo ($FoundTemplate==true ? $Id : 'null'); ?>">   
    <input type="hidden" name="class" value="<?php echo $Class; ?>">
    <input type="hidden" name="col1" id="col1Values" value="">
    <input type="hidden" name="col2" id="col2Values" value="">
    <input type="hidden" name="tabs" id="tabsValues" value="">
    <div id="templateOptions">
        <input type="submit" value="<?php echo __('Save'); ?>" name="submit" class="submitBtn" id="SubmitBtn">
    </div>
    
    <div style="width:100%;float:left;margin-bottom:5px;">
        <div style="float:left;width:200px;border:1px solid #617ea3;background-color:#cedff5;color:#214573; padding:5px;margin:5px;">
            <label for=""><?php echo __('Multi Columns:'); ?></label>
            <input id="multiColumns" type="checkbox" name="multiColumns" <?php echo ($Multicolumn==1 ? 'checked' : ''); ?> value="true"><br>
            <label for=""><?php echo __('Aspects managed as:'); ?></label><br>
            <input type="radio" name="aspectsView" value="tabs" title="Tabs" class="tabsRadio" <?php echo ($Aspectsview == "tabs" ? "checked" : ""); ?>> <img title="Tabs" src="/images/admin/tabs.png" align="absmiddle">
            <input type="radio" name="aspectsView" value="append" title="Append on Column" <?php echo ($Aspectsview != "tabs" ? "checked" : ""); ?> class="appendRadio"> <img title="Append on Column" src="/images/admin/columns.png" align="absmiddle">
        </div>
        <div style="float:left;width:200px;border:1px solid #617ea3;background-color:#cedff5;color:#214573; padding:5px;margin:5px;">
            <ul>
                <li><a href="#" class="addTab"><img src="/images/admin/tab_add.png"> <?php echo __('Add a tab'); ?></a></li>
            </ul>
        </div>
    </div>

    <div class="metaForm">

        <ul class="formUl">
            <li class="first" style="padding-left:10px;border:0;">
                <a href="javascript:addProperty('left')" style="color:#000000;"><img src="/images/icons/add.png" align="absmiddle"> <?php echo __('Add a property'); ?></a>
                <ul class="containerLeft">
    <?php 
   if ($FoundTemplate == true) {
                            $column1Values = array();
                            foreach ($Column1 as $key => $Prop) {
                                $column1Values[] = $Prop->name."/".$Prop->title."/".$Prop->dataType."/".$Prop->type; ?>
                                <li id="<?php echo $Prop->name;?>/<?php echo $Prop->title; ?>/<?php echo $Prop->dataType; ?>/<?php echo $Prop->type; ?>">
                                    <div class="form_row">
                                    <?php echo $Prop->title; ?> <span style="font-size:10px">(<?php echo $Prop->dataType; ?>)</span>
                                        <div class="metaActions">
                                            <img src="/images/icons/arrow_out.png" class="moveAction">
                                        </div>
                                    </div>
                                </li>
                                
                           <?php }
                           $column1Values = json_encode($column1Values);
                           ?>
                            <script type="text/javascript">
                                $("#col1Values").val('<?php echo $column1Values; ?>');
                                </script>
                                <?php
   }
   else { 
    foreach($properties as $key => $Prop) { 
        $inputname = str_replace(":",":",$Prop->name); ?>
                        <li id="<?php echo $inputname;?>/<?php echo $Prop->title; ?>/<?php echo $Prop->dataType; ?>/property">
                            <div class="form_row">
                            <?php echo $Prop->title; ?> <span style="font-size:10px">(<?php echo $Prop->dataType; ?>)</span>
                                <div class="metaActions">
                                    <img src="/images/icons/arrow_out.png" class="moveAction">
                                </div>
                            </div>
                        </li>
    <?php } 
    if (count($associations) > 0) {
    foreach($associations as $key => $Prop) { 
        $dataType = $Prop->target->class;
        $inputname = str_replace(":",":",$Prop->name); ?>
                        <li id="<?php echo $inputname;?>/<?php echo $Prop->title; ?>/<?php echo $dataType; ?>/association">
                            <div class="form_row">
                            <?php echo $Prop->title; ?> <span style="font-size:10px">(<?php echo $dataType; ?>)</span>
                                <div class="metaActions">
                                    <img src="/images/icons/arrow_out.png" class="moveAction">
                                </div>
                            </div>
                        </li>
    <?php }
    }
   }
    
    ?>
                </ul>
            </li>
            
            <li>
                <a href="javascript:addProperty('right')" style="color:#000000;"><img src="/images/icons/add.png" align="absmiddle"> <?php echo __('Add a property'); ?></a>
              <ul class="containerRight">
              <?php
                if ($FoundTemplate == true) {
                            $column2Values = array();

                            foreach ($Column2 as $key => $Prop) {
                                $column2Values[] = $Prop->name."/".$Prop->title."/".$Prop->dataType."/".$Prop->type; ?>
                                <li id="<?php echo $Prop->name;?>/<?php echo $Prop->title; ?>/<?php echo $Prop->dataType; ?>/<?php echo $Prop->type; ?>">
                                    <div class="form_row">
                                    <?php echo $Prop->title; ?> <span style="font-size:10px">(<?php echo $Prop->dataType; ?>)</span>
                                        <div class="metaActions">
                                            <img src="/images/icons/arrow_out.png" class="moveAction">
                                        </div>
                                    </div>
                                </li>
                                      <?php }
                           $column2Values = json_encode($column2Values);
                           ?>
                            <script type="text/javascript">
                                $("#col2Values").val('<?php echo $column2Values; ?>');
                                </script>
                                <?php
   }
       ?>
              </ul>
            </li>
            
            <li>
              <ul class="containerDeleteObjects">
                <li class="disabled">
                    <h2><?php echo __('Unvisible'); ?></h2>
                </li>  
                 <?php
                 if (count($DeletedProps) > 0) {
                foreach($DeletedProps as $key => $Prop) { 
        $inputname = str_replace(":",":",$Prop->name); ?>
                        <li id="<?php echo $inputname;?>/<?php echo $Prop->title; ?>/<?php echo $Prop->dataType; ?>/property">
                            <div class="form_row">
                            <?php echo $Prop->title; ?> <span style="font-size:10px">(<?php echo $Prop->dataType; ?>)</span>
                                <div class="metaActions">
                                    <img src="/images/icons/arrow_out.png" class="moveAction">
                                </div>
                            </div>
                        </li>
    <?php } 
                 }
    if (count($DeletedAssocs) > 0) {
    foreach($DeletedAssocs as $key => $Prop) { 
        $dataType = $Prop->target->class;
        $inputname = str_replace(":",":",$Prop->name); ?>
                        <li id="<?php echo $inputname;?>/<?php echo $Prop->title; ?>/<?php echo $dataType; ?>/association">
                            <div class="form_row">
                            <?php echo $Prop->title; ?> <span style="font-size:10px">(<?php echo $dataType; ?>)</span>
                                <div class="metaActions">
                                    <img src="/images/icons/arrow_out.png" class="moveAction">
                                </div>
                            </div>
                        </li>
    <?php }
    }
       ?>
              </ul>
            </li>
        </ul>
    </div>

    
    <?php
        if ($FoundTemplate == true) {
            if (count($Tabs->tabs) > 0) {
  
                foreach ($Tabs->tabs as $key => $value) { 

                    $items = $value->items;
                    $title = $value->title;
                    
                    ?>
                    <script type="text/javascript">
                        Ext.onReady(function(){ 
                        var tabId = restoreTabs('<?php echo $title; ?>');   
          
                        <?php foreach ($items as $item) { 
                             $itemValue = $item->name."/".$item->title."/".$item->dataType."/".$item->type;
                            ?>
                            $("#tab_drop_"+tabId+"_list").append('<li id="<?php echo $itemValue; ?>"><div class="form_row"><?php echo $item->title; ?> <span style="font-size:10px">(<?php echo $item->dataType; ?>)</span><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>');
                        
                        <?php 
                            } ?>
                        }); 
                    </script>
               <?php }
            }
        }
    ?>
    
    <div style="float:left;width:100%;margin:5px;margin-left:10px;">
    <div id="metaTabs" style="visibility: hidden;">

    </div>
</div>
    </form>
</div>