<style type="text/css">
.save {
    background-image: url( /images/icons/disk.png ) !important;
}
.cancel {
    background-image: url( /images/icons/cross.png ) !important;
}  
</style>
<link rel="stylesheet" href="/js/SuperBoxSelect/superboxselect.css" type="text/css">
<script type="text/javascript" src="/js/SuperBoxSelect/SuperBoxSelect.js"></script>
<script type="text/javascript">
Ext.onReady(function(){

var width = "100%";
if ( $.browser.msie )
    width = "96%";
    
    
var metaForm<?php echo $containerName; ?> = new Ext.ux.form.MetaForm({
        url:'<?php echo url_for('Metadata/GetNodeMetaData?nodeId='.$nodeId.'&fieldTypeSeperator=true') ?>',
        frame:false,
        name:'meta-form<?php echo $containerName; ?>',
        id:'meta-form<?php echo $containerName; ?>',
        header:false,
        bodyStyle:'padding:3px;',
        width:width,
        autoScroll:true,
        height:($(document).height()-90),
        //height:'100%',
        renderTo:'metaForm<?php echo $containerName; ?>',
        containerName:'<?php echo $containerName; ?>',
        addData:'#additionalData<?php echo $containerName; ?>',
        tbar: [{
            xtype: 'buttongroup',
            items: [{
                text: '<?php echo __('Save'); ?>',
                iconCls: 'save',
                handler:function() {
                    submitMetaData<?php echo $containerName; ?>();
                }
            },
            {
                text: '<?php echo __('Cancel'); ?>',
                iconCls: 'cancel',
                handler:function() {   
                    closeActiveContentTab();    
                }
            }]
        }]
    });    
}); 



/*$(document).ready(function() {
    $('#MetaDataForm').submit(function() {
        var formData = form2object('metaForm');
        var jsonData = $.toJSON(formData);
        alert(jsonData);
        return false;
    });    
});*/

function submitMetaData<?php echo $containerName; ?>() {
    var resultForm = Ext.getCmp("meta-form<?php echo $containerName; ?>").getForm().getValues(); 
                
    $("#allMetaData<?php echo $containerName; ?> :input").each(function() {
        var inputType = $(this).attr("type");
        inputType = inputType.toLowerCase();
                                  
        if (inputType == "checkbox") {
            var name = $(this).attr('name');
            var checked = $(this).is(':checked');

            resultForm[name] = checked;                        
        }                                            
    }); 
    
    $("#additionalData<?php echo $containerName; ?> :input[type=hidden]").each(function() { 
        var metaType = $(this).attr("metatype");
        var realField = $(this).attr("realField");
        var name = $(this).attr('name');   
        if(typeof metaType != 'undefined' && typeof realField != 'undefined' ) {
            metaType = metaType.toLowerCase();
            
            if (metaType == "category") {
                var jsonObj = [];
                var metaTreePanel = Ext.getCmp(realField);
                if (metaTreePanel) {
                    var selNodes = metaTreePanel.getChecked();
                    Ext.each(selNodes, function(node){
                        var nodeId = node.attributes.nodeId;
                        //var nodeName = node.text;
                        //nodeName = escape(nodeName);
                        //jsonObj.push({id:nodeId,name:nodeName});
                        jsonObj.push({id:nodeId});
                    });
                    resultForm[name] = jsonObj;             
                }
                
            }

        }
           
    });
    /*$("#allMetaData textarea").each(function() {
        var name = $(this).attr('name');
        var value = $(this).val();
        
        resultForm[name] = value;                                             
    }); */
    
    var resultFormJSON = $.toJSON(resultForm);    
    //alert(resultFormJSON);
    
    $("#allMetaData<?php echo $containerName; ?>").mask("<?php echo __('Saving...'); ?>",300);
    resultFormJSON = $.base64Encode(resultFormJSON);
        
    $.post("<?php echo url_for('Metadata/SaveMetadata') ?>", "nodeId=<?php echo $nodeId; ?>&data="+resultFormJSON, function(data) {
        if (data.success == "true") {
            
        }
        $("#allMetaData<?php echo $containerName; ?>").unmask();
        
    }, "json");
}

function categoryEnable(panelId,treeId,catUrl,width,btn) {
    /*$MetaDataArray[] = array(
                                        "name"=>$DataKeyName,
                                        "fieldLabel"=>$Label,  
                                        "id"=>$id,     
                                        "editor"=>array(
                                            "xtype"=>"treepanel",
                                            "width"=>$formConfig["defaults"]["width"],
                                            "height"=>250,
                                            "id"=>$BOXNAMERand,
                                            "useArrows"=>false,
                                            "autoScroll"=>true,
                                            "enableDD"=>false,
                                            "containerScroll"=>true,
                                            "frame"=>true,
                                            "isFormField"=>true,
                                            "rootVisible"=>false,
                                            "bodyStyle"=>"background:white;",
                                            //"loader"=>"$url",
                                            "dataUrl"=>$url,
                                            "root"=>array(
                                                //"nodeType"=>"async",
                                                "text"=>"root",
                                                "draggable"=>false,   
                                                "id"=>"root",
                                                "expanded"=>true,
                                                "border"=>true
                                            )
                                        )
                                    ); */
    var panel = new Ext.Panel({
        frame:false,
        header:false,
        collapsible:true,
        autoHeight:true,
        //renderTo:panelId,
        applyTo:panelId,
        width:width,
        items:[{
            xtype: 'treepanel',
            disabled:false,
            id:treeId,
            bodyStyle:'background:#ffffff;padding:0;margin:0;border:none;',
            width:width,
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
                //nodeType:'async',
                id:'root',
                draggable:false,
                text:'<?php echo __('Categories'); ?>',
                expanded:true,
                border:true
            },
            listeners:{
                beforeload: function() {
                    $("#"+panelId).mask("Loading...",300);
                },
                load: function() {
                    $("#"+panelId).unmask();
                }
            }
        }]
    });
    
    $(btn).hide();
}
</script>

<div id="allMetaData<?php echo $containerName; ?>">         
    <div id="metaForm<?php echo $containerName; ?>">
    </div>
    
    <div id="additionalData<?php echo $containerName; ?>">
    </div>
</div>


