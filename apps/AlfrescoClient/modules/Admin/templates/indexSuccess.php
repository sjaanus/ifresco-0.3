<?php use_javascript('/js/extjs/adapter/ext/ext-base.js'); ?>
<?php use_javascript('/js/extjs/ext-all.js'); ?>
<?php use_stylesheet('/js/extjs/resources/css/ext-all.css'); ?>

<style>

#action-panel .x-panel {
    margin-bottom:3px;
    margin-right:0;
}
#action-panel .x-panel-body {
    border:0 none;

}
#action-panel .x-panel-body li {
    margin:3px;
}

#action-panel .x-panel-body li a {
    text-decoration:none;
    color:#3764A0;
}
#action-panel .x-plain-body {
    background-color:#cad9ec;
    padding:3px 5px 0 5px;
}

#action-panel .x-panel-body li a:hover {
    text-decoration:underline;
    color:#15428b;
}

.x-panel-trans {
    background:transparent;
}

</style>
<script type='text/javascript'>
var templateDesignerWindow = null;
var templateDesignerCombo = null;
var propertyAddWindow = null;
var selectedId = null;
Ext.onReady(function(){
    Ext.QuickTips.init();
    
    var contentModelPanel = new Ext.Panel({
        frame:true,
        title: '<?php echo __('Content Model'); ?>',
        collapsible:true,
        autoHeight: true,
        contentEl:'contentModelPanel',
        titleCollapse: true
    });
    
    var searchPanelLeft = new Ext.Panel({
        frame:true,
        title: '<?php echo __('Advanced Search'); ?>',
        collapsible:true,
        autoHeight: true,
        contentEl:'searchPanelLeft',
        titleCollapse: true
    });
    
    var colPanelLeft = new Ext.Panel({
        frame:true,
        title: '<?php echo __('Columns'); ?>',
        collapsible:true,
        autoHeight: true,
        contentEl:'colPanelLeft',
        titleCollapse: true
    });
    
    var generalSettingsLeft = new Ext.Panel({
        frame:true,
        title: '<?php echo __('General Settings'); ?>',
        collapsible:true,
        contentEl:'generalSettingsLeft',
        titleCollapse: true
    });
    
    var infoPanel = new Ext.Panel({
        frame:true,
        title: '<?php echo __('About'); ?>',
        collapsible:true,
        contentEl:'infoPanel',
        titleCollapse: true
    });
    
    var actionPanel = new Ext.Panel({
        id:'action-panel',
        split:true,
        renderTo:'leftpanel',
        region:'west',
        collapsible: true,
        collapseMode: 'mini',
        header: false,
        layout:'fit',
        width:220,
        
        minWidth: 250,
        maxWidth: 250,
        border: false,
        baseCls:'x-plain',
        items: [contentModelPanel, searchPanelLeft, colPanelLeft, generalSettingsLeft, infoPanel]
    });
    
    
    
    var templateDesignerStore = new Ext.data.JsonStore({
        autoDestroy: true,
        url: '<?php echo url_for('Admin/TemplateContentTypes') ?>',
        storeId: 'templateDesignerStore',
        // reader configs
        root: 'types',
        idProperty: 'name',
        fields: ['name', 'title','description']
    });
    
    templateDesignerStore.load();


    templateDesignerWindow = new Ext.Window({
        applyTo:'templateDesignerWindow',
        layout:'fit',
        width:350,
        height:100,
        closeAction:'hide',
        plain: true,
        items: [
            templateDesignerCombo = new Ext.form.ComboBox({
                store: templateDesignerStore,
                displayField:'title',
                typeAhead: true,
                id:'templateDesignerCombo',
                mode: 'local',
                triggerAction: 'all',
                emptyText:'<?php echo __('Select a content type...'); ?>',
                selectOnFocus:true,
                listeners:{
                     'select': function(combo,record,index) {
                         if (typeof record !== 'undefined')
                            selectedId = record.id;
                         else
                            selectedId = null;
                     }
                }

            })
        ],
        buttons: [{
            text:'<?php echo __('Next'); ?>',
            disabled:false,
            handler:loadRealDesigner
        },{
            text: '<?php echo __('Close'); ?>',
            handler: function(){
                templateDesignerWindow.hide();
            }
        }]
    });
    
    propertyAddWindow = new Ext.Window({
        applyTo:'propertyAddWindow',
        layout:'fit',
        width:350,
        height:180,
        closeAction:'hide',
        plain: true,
        items: [
            new Ext.Panel({
                frame:true,
                header:false,
                contentEl:'contentPropertyWindow'
            })
        ],
        buttons: [{
            text:'<?php echo __('Add'); ?>',
            disabled:false,
            handler: function() {
                var location = $("#propertiesaddlocation").val();
                if (location === "quicksearchAdmin") {
                    var values = new String($("#propertiesadd").val());
                    var valueString = "";
                    if (values.length !== 0) {
                        var valuearr = values.split(",");
                        if (valuearr.length > 0) {
                            for (var i = 0; i < valuearr.length; i++) {
                                var splitter = valuearr[i].split("/");
                                var name = splitter[0];
                                var title = splitter[2];
                                if (title.length === 0)
                                    var showTitle = name;
                                else
                                    var showTitle = title;
                                    
                                var datatype = splitter[3];
                                valueString += '<li id="'+valuearr[i]+'/property"><div class="form_row">'+showTitle+' <span style="font-size:10px">('+datatype+')</span><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>';
                            }
                        }
                        
                        $(".colContainer").append(valueString);
                        var orderArr = $('.colContainer').sortable('toArray'); 
                        var json = $.JSON.encode(orderArr);
                        $("#colSubmitValues").val(json);
                        
                        $("#contentPropertyWindow").html('<input type="hidden" value="'+location+'" name="propertiesaddlocation" id="propertiesaddlocation"><select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo __('Select a property...'); ?>"></select>');

                        $("#propertiesadd").asmSelect({
                            addItemTarget: 'top',
                            sortable: true,
                            url: '<?php echo url_for('Admin/TemplateProperties') ?>'
                        });
                    }
                }
                else if (location === "colAdmin") {
                    var values = new String($("#propertiesadd").val());
                    var valueString = "";
                    if (values.length !== 0) {
                        var valuearr = values.split(",");
                        if (valuearr.length > 0) {
                            for (var i = 0; i < valuearr.length; i++) {
                                var splitter = valuearr[i].split("/");
                                var name = splitter[0];
                                var title = splitter[2];
                                if (title.length === 0)
                                    var showTitle = name;
                                else
                                    var showTitle = title;
                                    
                                var datatype = splitter[3];
                                valueString += '<li id="'+valuearr[i]+'/property/show"><div class="form_row">'+showTitle+' <span style="font-size:10px">('+datatype+')</span><div class="options"><input type="checkbox" class="hiddenFlag" name="hiddenFlag" value="true" onclick="changeSelectState(\''+valuearr[i]+'/property/show\',this)"> <?php echo __('Hide on default'); ?></div><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>';
                            }
                        }
                        
                        $(".colContainer").append(valueString);
                        var orderArr = $('.colContainer').sortable('toArray'); 
                        var json = $.JSON.encode(orderArr);
                        $("#colSubmitValues").val(json);
                        
                        $("#contentPropertyWindow").html('<input type="hidden" value="'+location+'" name="propertiesaddlocation" id="propertiesaddlocation"><select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo __('Select a property...'); ?>"></select>');

                        $("#propertiesadd").asmSelect({
                            addItemTarget: 'top',
                            sortable: true,
                            url: '<?php echo url_for('Admin/TemplateProperties') ?>'
                        });
                    }
                }
                else if (location.length > 0) {
                    var values = new String($("#propertiesadd").val());
                    var valueString = "";
                    if (values.length !== 0) {
                        var valuearr = values.split(",");
                        if (valuearr.length > 0) {
                            for (var i = 0; i < valuearr.length; i++) {
                                var splitter = valuearr[i].split("/");
                                var name = splitter[0];
                                var title = splitter[2];
                                if (title.length === 0)
                                    var showTitle = name;
                                else
                                    var showTitle = title;
                                    
                                var datatype = splitter[3];
                                valueString += '<li id="'+valuearr[i]+'/property"><div class="form_row">'+showTitle+' <span style="font-size:10px">('+datatype+')</span><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>';
                            }
                        }
                        if (location === "left") {
                            $(".containerLeft").append(valueString);
                            var orderArr = $('.containerLeft').sortable('toArray'); 
                            var json = $.JSON.encode(orderArr);
                            $("#col1Values").val(json);
                        }
                        else if (location === "right") {
                            $(".containerRight").append(valueString);
                            var orderArr = $('.containerRight').sortable('toArray'); 
                            var json = $.JSON.encode(orderArr);
                            $("#col2Values").val(json);
                        }

                        $("#contentPropertyWindow").html('<input type="hidden" value="'+location+'" name="propertiesaddlocation" id="propertiesaddlocation"><select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo __('Select a property...'); ?>"></select>');

                        $("#propertiesadd").asmSelect({
                            addItemTarget: 'top',
                            sortable: true,
                            url: '<?php echo url_for('Admin/TemplateProperties') ?>'
                        });
                        
                         
                    }
                    
                }
            }
        },{
            text: '<?php echo __('Close'); ?>',
            handler: function(){
                propertyAddWindow.hide();
            }
        }]
    });

    /*var metaAdminPanel = new Ext.Panel({
        layout:'fit',
        header:false,
        frame:true,
        width:'100%',
        height:$(document).height()-100,
        renderTo:'adminPanel'

    });*/
});

function changeSelectState(id,checkbox) {
    if (checkbox.checked === true) {
        var newid = id.replace(/\/show/g,"/hide");      
        var el = document.getElementById(id);
        el.id = newid;
        //$("#"+id).attr('id',newid);
        //$("#"+id).attr('hide',true);
    }
    else {
        var newid = id.replace(/\/hide/g,"/show");
        var el = document.getElementById(id);
        el.id = newid;
        //$("#"+id).attr('id',newid);   
        //$("#"+id).attr('hide',false);
    }
}

$(document).ready(function() {
        $("#leftpanel").css('height',$(document).height()-100+'px');
        
        if ( $.browser.msie ) {
            $("#adminContent").css('width',$("#allElAdmin").width()-242+'px');
            $("#adminPanel").css('height',$(document).height()-110+'px');
        }
        else {
            $("#adminContent").css('width',$("#allElAdmin").width()-240+'px');
            $("#adminPanel").css('height',$(document).height()-110+'px');
        }
        //$("#adminContent").css("height",$("#allElAdmin").height()-600+'px');
        //$("#adminContent").css("overflow",'scroll');
        
        $("#propertiesadd").asmSelect({
                addItemTarget: 'top',
                sortable: true,
                url: '<?php echo url_for('Admin/TemplateProperties') ?>'
            });
});

function loadLink(url,params) {
    $.ajax({
        cache:false,
        url : url,
        data: (params),
        
        success : function (data) {
            $("#adminPanel").unmask();
            
            
            
            $("#adminContent").html(data);
            
            //alert($("#adminContent").height());
        },          
        beforeSend: function(xhr) { 
            $("#adminContent").html('');
            $("#adminPanel").mask("<?php echo __('Loading...'); ?>",300);    
        }  
    });
}

function loadRealDesigner() {
    if (selectedId !== null) {
        templateDesignerWindow.hide();
        loadLink('<?php echo url_for('Admin/TemplateDesigner') ?>',{'class':selectedId});
    }
}

function loadTemplateDesigner() {
    templateDesignerWindow.show();
    
    
}
</script>
<style type='text/css'>

    #loading {
        position: absolute;
        top: 5px;
        right: 5px;
        }
    
    #leftpanel {
       background-color:#cad9ec;
       float:left;
       width:220px; 
    }
    
    #allElAdmin {
        width:100%;
    }
    #adminContent {
        font-size: 13px;
        font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
        width:75%;
        float:right;
        margin: 0 auto;
        margin-top:5px;
        margin-right:5px;
        //float:left;
        //padding-left:5px;
    }

    #adminPanel {
        overflow:auto;
        //float:left;
    }
        
      .addCat-icon {
    background-image: url('/images/admin/tag_blue_add.png');
}

.userAdd-icon {
    background-image: url('/images/admin/user_add.png');
}

.groupAdd-icon {
    background-image: url('/images/admin/group_add.png');
}

.templates-icon {
    background-image: url('/images/admin/layout_content.png');
}

.lookups-icon {
    background-image: url('/images/admin/wizard.png');
}

</style>

<div id="contentModelPanel" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/Templates') ?>',{});"><?php echo __('Templates'); ?></a></li>
        <li><a href="#" onclick="loadTemplateDesigner()"><?php echo __('Create a Template'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/Lookups') ?>',{});"><?php echo __('Lookups'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/NameSpaceMapping') ?>',{});"><?php echo __('Namespace Mapping'); ?></a></li>
    </ul>
</div>
<div id="searchPanelLeft" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/SearchTemplates') ?>',{});"><?php echo __('Templates'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/SearchTemplateDesigner') ?>',{});"><?php echo __('Create a Template'); ?></a></li>
    </ul>
</div>
<div id="colPanelLeft" class="x-hidden">
    <ul>
        <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/SearchColumnSets') ?>',{});"><?php echo __('Column Sets'); ?></a></li>
        <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/SearchColumnSetsAdd') ?>',{});"><?php echo __('Add a Column Set'); ?></a></li>
    </ul>
</div>
<div id="generalSettingsLeft" class="x-hidden">
<ul>
    <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/SystemSettings') ?>',{});"><?php echo __('System'); ?></a></li>
    <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/EmailSettings') ?>',{});"><?php echo __('Email'); ?></a></li>   
    <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/AspectList') ?>',{});"><?php echo __('Aspects'); ?></a></li>   
    <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/TypeList') ?>',{});"><?php echo __('Content Types'); ?></a></li>   
    <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/QuickSearch') ?>',{});"><?php echo __('Quick Search'); ?></a></li>   
    <li><a href="#" onclick="loadLink('<?php echo url_for('Admin/OnlineEditing') ?>',{});"><?php echo __('Online Editing'); ?></a></li>   
</ul>
</div>
<div id="infoPanel" class="x-hidden">
<b>Version:</b> <?php echo $ifrescoVersion; ?>
</div>
<div id="allElAdmin">
<div id="leftpanel"></div>
<div id='loading' style='display:none'><?php echo __('Loading...'); ?></div>
<div id="adminPanel">
<div id='adminContent'>

</div>
</div>

<div id="templateDesignerWindow" class="x-hidden">
    <div class="x-window-header"><?php echo __('Select a content type'); ?></div>
    
</div>

<div id="propertyAddWindow" class="x-hidden">
    <div class="x-window-header"><?php echo __('Select a property'); ?></div>
    
    <div id="contentPropertyWindow">
        <input type="hidden" value="" name="propertiesaddlocation" id="propertiesaddlocation">
        <select id="propertiesadd" multiple="multiple" name="propertiesadd[]" title="<?php echo __('Select a property...'); ?>"></select>
    </div>
</div>

</div>

