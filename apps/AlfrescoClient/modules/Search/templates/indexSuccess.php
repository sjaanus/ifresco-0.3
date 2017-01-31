

<style type="text/css">

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
var selectedId = null;
var searchTemplate = null;

var currentColumnsetid = "<?php echo $ColumnsetId; ?>";

Ext.onReady(function(){
    Ext.QuickTips.init();
    
    var defaultPanel = new Ext.Panel({
        frame:true,
        title: 'Default',
        collapsible:true,
        autoHeight:true,
        items:[{
            xtype:'label',
            name:'searchtemplatelabel',
            forId:'searchtemplate',
            text:'<?php echo __('Template:'); ?>'
            
        },{
            xtype: 'combo',
            id:'searchtemplate',
            name:'seachtemplate',
            typeAhead: false
        },
        {
            xtype:'label',
            name:'searchtermlabel',
            forId:'searchterm',
            text:'<?php echo __('Search:'); ?>'
            
        },
        {
            xtype: 'textfield',
            id:'searchterm',
            name:'searchterm'
        }],
        titleCollapse: true
    });
    
    var resultsPanel = new Ext.Panel({
        frame:true,
        title: '<?php echo __('Show me results for'); ?>',
        collapsible:true,
        autoHeight:true,
        items:[{
            xtype: 'radiogroup',
            columns: 1,
            id:'resultsGroup',
            items: [
                {boxLabel: '<?php echo __('All Items'); ?>', name: 'resultsFor', inputValue: 'all', checked: true},
                {boxLabel: '<?php echo __('File names and contents'); ?>', name: 'resultsFor', inputValue: 'fileAndContent'},
                {boxLabel: '<?php echo __('File names only'); ?>', name: 'resultsFor', inputValue: 'fileOnly'},
                {boxLabel: '<?php echo __('Space names only'); ?>', name: 'resultsFor', inputValue: 'spaceOnly'}
            ]
        }],
        titleCollapse: true
    });
    
    var folderSubCheck = false;
    var categorySubCheck = false;
    var handleCheck = false;
    
    var locationPanel = new Ext.Panel({
        frame:true,
        title: '<?php echo __('Look in'); ?>',
        collapsible:true,
        autoHeight:true,
        items:[{
            xtype:'tabpanel',
            deferredRender:false,
            autoHeight:true,
            activeTab:0,
            items:[{
                id:'tabSpaces',
                title: '<?php echo __('Location'); ?>',
                closable:false,
                autoHeight:true,
                autoScroll:true,
                frame:true,

                items:[{
                    xtype: 'radiogroup',
                    columns: 1,
                    id:'locationGroup',
                    items: [
                        {boxLabel: '<?php echo __('All Spaces'); ?>', name: 'location', inputValue: 'all', checked: true},
                        {
                            boxLabel: '<?php echo __('Specify Space'); ?>', 
                            name: 'location', 
                            inputValue: 'specific',
                            listeners:{
                                check:function(field,checked) {
                                    if (checked == true) {
                                        var specifySpaceTree = Ext.getCmp('specifySpaceTree');
                                        if (specifySpaceTree) {
                                            specifySpaceTree.setDisabled(false);
                                        }
                                    }
                                    else {
                                        var specifySpaceTree = Ext.getCmp('specifySpaceTree');
                                        if (specifySpaceTree) {
                                            specifySpaceTree.setDisabled(true);
                                        } 
                                    }
                                }
                            }
                        },{
                            xtype: 'treepanel',
                            disabled:true,
                            id:'specifySpaceTree',
                            bodyStyle:'background:white;padding:0;margin:0;border:none;',
                            width:200,
                            height:250,
                            useArrows:false,
                            autoScroll:true,
                            enableDD:false,
                            containerScroll:true,
                            frame:true,
                            isFormField:true,
                            rootVisibile:false,
                            dataUrl:'<?php echo url_for('tree/GetCheckTree'); ?>',
                            root:{
                                nodeType:'async',
                                id:'root',
                                draggable:false,
                                text:'<?php echo __('Company Home'); ?>',
                                expanded:true,
                                border:false
                            },
                            listeners:{
                                'checkchange': function(node,checked) {
                                    if (handleCheck == true)
                                        return;
                                        
                                    if (checked == true && folderSubCheck == true) {
                                        if (node.hasChildNodes()) {
                                            if (!node.isExpanded()) {
                                                node.expand(false,true,function(parentNode) {
                                                    handleCheck = true;
                                                    var childs = parentNode.childNodes;
                                                    for (var i = 0; i < childs.length; i++) {
                                                        var child = childs[i];
                                                        child.getUI().toggleCheck(true);
                                                    }
                                                    handleCheck = false;
                                                });
                                            }
                                            else {
                                                handleCheck = true;
                                                var childs = node.childNodes;
                                                for (var i = 0; i < childs.length; i++) {
                                                    var child = childs[i];
                                                    child.getUI().toggleCheck(true);
                                                }
                                            }
                                        }
                                        
                                    }
                                    handleCheck = false;
                                }  
                            } 
                        },
                        {
                            xtype: 'tbbutton',
                            text: '<?php echo __('Auto. check Sub-Folders'); ?>',
                            enableToggle: true,
                            toggleHandler: onSubFoldersToggle,
                            pressed: false
                        },
                        {
                            xtype: 'tbbutton',
                            text: '<?php echo __('Uncheck all'); ?>',
                            handler: function() {
                                var treeChecked = Ext.getCmp('specifySpaceTree').getChecked();
                                for(var i = 0; i < treeChecked.length; i++) {
                                    var checkedNode = treeChecked[i];
                                    checkedNode.getUI().toggleCheck(false);
                                }
                            }
                        }
                    ]
                }]
            },
            {
                id:'tabCategories',
                title: '<?php echo __('Categories'); ?>',
                closable:false,
                autoHeight:true,
                autoScroll:true,
                frame:true,

                items:[{
                    xtype: 'radiogroup',
                    columns: 1,
                    id:'categoryGroup',
                    items: [
                        {boxLabel: '<?php echo __('All Categories'); ?>', name: 'categories', inputValue: 'all', checked: true},
                        {
                            boxLabel: '<?php echo __('Specify Categories'); ?>', 
                            name: 'categories', 
                            inputValue: 'specific',
                            listeners:{
                                check:function(field,checked) {
                                    if (checked == true) {
                                        var specifyCategoryTree = Ext.getCmp('specifyCategoryTree');
                                        if (specifyCategoryTree) {
                                            specifyCategoryTree.setDisabled(false);
                                        }
                                    }
                                    else {
                                        var specifyCategoryTree = Ext.getCmp('specifyCategoryTree');
                                        if (specifyCategoryTree) {
                                            specifyCategoryTree.setDisabled(true);
                                        } 
                                    }
                                }
                            }
                        },{
                            xtype: 'treepanel',
                            disabled:true,
                            id:'specifyCategoryTree',
                            bodyStyle:'background:white;padding:0;margin:0;border:none;',
                            width:200,
                            height:250,
                            useArrows:false,
                            autoScroll:true,
                            enableDD:false,
                            containerScroll:true,
                            frame:true,
                            isFormField:true,
                            rootVisible:false,
                            dataUrl:'<?php echo url_for('Categories/GetCategoryCheckTree'); ?>',
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
                                    if (handleCheck == true)
                                        return;
                                        
                                    if (checked == true && categorySubCheck == true) {
                                        if (node.hasChildNodes()) {
                                            if (!node.isExpanded()) {
                                                node.expand(false,true,function(parentNode) {
                                                    handleCheck = true;
                                                    var childs = parentNode.childNodes;
                                                    for (var i = 0; i < childs.length; i++) {
                                                        var child = childs[i];
                                                        child.getUI().toggleCheck(true);
                                                    }
                                                    handleCheck = false;
                                                });
                                            }
                                            else {
                                                handleCheck = true;
                                                var childs = node.childNodes;
                                                for (var i = 0; i < childs.length; i++) {
                                                    var child = childs[i];
                                                    child.getUI().toggleCheck(true);
                                                }
                                            }
                                        }
                                        
                                    }
                                    handleCheck = false;
                                }    
                            } 
                        },
                        {
                            xtype: 'tbbutton',
                            text: '<?php echo __('Auto. check Sub-Categories'); ?>',
                            enableToggle: true,
                            toggleHandler: onSubCategoriesToggle,
                            pressed: false
                        },
                        {
                            xtype: 'tbbutton',
                            text: '<?php echo __('Uncheck all'); ?>',
                            handler: function() {
                                var treeChecked = Ext.getCmp('specifyCategoryTree').getChecked();
                                for(var i = 0; i < treeChecked.length; i++) {
                                    var checkedNode = treeChecked[i];
                                    checkedNode.getUI().toggleCheck(false);
                                }
                            }
                        }
                    ]
                }]
            },
            {
                id:'tabTags',
                title: '<?php echo __('Tags'); ?>',
                closable:false,
                autoHeight:true,
                autoScroll:true,
                frame:true,
                items:[{
                    el:'tagField',
                    name:'tagSearchField'
                }]
            }]
        
            
        }],
        titleCollapse: true
    });

    function onSubFoldersToggle(item, pressed){
        folderSubCheck = pressed;
    }
    
    function onSubCategoriesToggle(item, pressed){
        categorySubCheck = pressed;
    }
    
    var actionPanel = new Ext.Panel({
        id:'search-action-panel',
        split:true,
        renderTo:'leftSearchPanel',
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
        items: [resultsPanel, locationPanel]
    });
    
    /*var searchForm = new Ext.ux.form.MetaForm({
        url:'<?php echo url_for('Search/GetSearchForm') ?>?template='+searchTemplate,
        frame:false,
        name:'search-form',
        header:false,
        id:'search-form',
        bodyStyle:'padding:5px;',
        width: '100%',
        frame:true
    });*/
    
    var searchForm = new Ext.ux.form.MetaForm({
        url:'<?php echo url_for('Search/GetSearchForm') ?>?template='+searchTemplate,
        frame:false,
        name:'search-form',
        id:'search-form',
        header:false,
        bodyStyle:'padding:3px;',
        width: '100%',
        autoScroll:true,
        height:'100%',
        renderTo:'searchForm',
        containerName:'',
        addData:'#additionalData'
    });
    
    var searchTemplatesStore = new Ext.data.JsonStore({
        autoDestroy: true,
        url: '<?php echo url_for('Search/SearchTemplates') ?>',
        storeId: 'searchTemplatesStore',
        // reader configs
        root: 'templates',
        idProperty: 'id',
        fields: ['id', 'name','columnsetid']
    });

    searchTemplatesStore.load();
    
    var combo = new Ext.form.ComboBox({
        store: searchTemplatesStore,
        displayField:'name',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        emptyText: '<?php echo __('Select a template to change the fields...'); ?>',
        selectOnFocus: true,
        width: 250,
        iconCls: 'no-icon',
        listeners:{
            'select': function(combo,record,index) {
                         if (typeof record != 'undefined') {
                            var searchTemplateId = record.id;
                            
                            currentColumnsetid = record.data.columnsetid;
                            
                            
                            searchForm.url = '<?php echo url_for('Search/GetSearchForm') ?>?template='+searchTemplateId;
                            searchForm.load();
                         }
                  }
        }
    });

     
     var pnl = new Ext.Panel({
        renderTo:'searchContent',
        //width:'100%',
        id:'search-panel',
        //region:'south',
        border:false,
        frame:false,
        header:false,
        tbar:[{
            xtype:'label',
            text:'<?php echo __('Search:'); ?>'
        },{
            xtype:'textfield',
            name:'searchTermAdvanced',
            width: 200,
            id:'searchTermAdvanced'
        },
        '-',{
            xtype:'label',
            text:'<?php echo __('Search Template:'); ?>'
        },
        combo,
        '-',{
            text: '<?php echo __('Search'); ?>',
            handler: function() {
               submitBtnClick();
            }
        }],
        items:[searchForm]
        
     });
});

function submitBtnClick() {
    /*
    field of quicksearch 
    var searchTerm = $("#alfrescoSearchTerm");
    var searchString = "";
    if (!IsEmpty(searchTerm.val())) {
        searchString = searchTerm.val(); 
    }
    else {*/
    
    
    var searchTermAdv = $("#searchTermAdvanced");
    if (searchTermAdv != null && typeof searchTermAdv.length != 'undefined') {
        searchString = searchTermAdv.val(); 
    }
    
    /*}*/
    submitAdvancedSearch(searchString);
}

function submitAdvancedSearch(searchTermString) {
    var string = "";

    var options = {searchTerm:searchTermString,results:'',locations:[],categories:[],tags:''};
    var tempitems = Ext.getCmp('search-form').getValues();
    var items = tempitems;
    $.each(tempitems, function(index,value) {
        var myString = new String(index);
        if (myString.match(/#assoc#/i)) {
            var values = myString.split("#assoc#");
            if (values.length == 2) {
                var propname = values[0];
                var itemValue = values[1];
                var assocValues = $("#"+itemValue).val();
                var myRealValue = "";
                if (assocValues != null && assocValues.length > 0 && assocValues != "value" && typeof assocValues != 'undefined') {
                    myRealValue = $.JSON.decode(assocValues);
                }
                else
                    myRealValue = "";
                
                items[propname] = myRealValue;
                
                delete items[index];
            }
        }
    });

    var tagsValues = $("#tagBoxSearchValues").val();

    if (tagsValues.length > 0) {
      options.tags = tagsValues;
    }

      // resultsFor
    var resultsRadioGroup = Ext.getCmp('resultsGroup');
    if (typeof resultsRadioGroup.getValue() != "undefined") {
      var resultsVal = resultsRadioGroup.getValue().getGroupValue();
      options.results = resultsVal;
    }

    // location - all / specific
    var locationRadioGroup = Ext.getCmp('locationGroup');
    if (typeof locationRadioGroup.getValue() != "undefined") {
      var locationVal = locationRadioGroup.getValue().getGroupValue();
      if (locationVal == "specific") {
          var spaceTree = Ext.getCmp('specifySpaceTree');
          if (spaceTree != null) {
              var selNodes = spaceTree.getChecked();
              Ext.each(selNodes, function(node){
                //var nodeId = node.attributes.nodeId;
                var nodeId = node.id;
                if (nodeId != "" && nodeId != null && typeof nodeId != "undefined")
                    options.locations.push(nodeId);
              });
          }
      }
    }

    // categories - all / specific
    var categoryRadioGroup = Ext.getCmp('categoryGroup');
    if (typeof categoryRadioGroup.getValue() != "undefined") {
      var categoryVal = categoryRadioGroup.getValue().getGroupValue();
      if (categoryVal == "specific") {
          var categoryTree = Ext.getCmp('specifyCategoryTree');
          if (categoryTree != null) {
              var selNodes = categoryTree.getChecked();
              Ext.each(selNodes, function(node){
                var nodeText = node.attributes.text;
                if (nodeText != "" && nodeText != null && typeof nodeText != "undefined")
                    options.categories.push(nodeText);
              });
          }
      }
    }

    var jsonOptions = $.JSON.encode(options);

    var jsonItems = $.JSON.encode(items);
    //alert(jsonItems);
    var date = new Date();
    var dateStringTstamp = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
    var dateString = date.format("<?php echo $TimeFormat; ?>");
    //console.log("<?php echo $TimeFormat; ?>");
    //console.log(dateString);
    //var dateString = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
    var timestamp = Date.parse(dateStringTstamp);
    addTabDynamic('searchresult-tab-'+timestamp,'<?php echo __('Search Result'); ?> - '+dateString);
     //console.log("max ajax req");               
     $.ajax({
        url: "<?php echo url_for('DataGrid/index') ?>?containerName="+timestamp+"&columnsetid="+currentColumnsetid+"&addContainer="+timestamp,
        success: function(data) {          
            $("#overAll").unmask();
            $("#searchresult-tab-"+timestamp).html(data);
            //grid.store.load({params:{'nodeId':node.id}});    
            eval("reloadGridData"+timestamp+"({params:{'columnsetid':currentColumnsetid,'advancedSearchFields':jsonItems,'advancedSearchOptions':jsonOptions}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo __('Loading Results...'); ?>",300);    
        }
    });
}

$(document).ready(function() {
        $("#leftSearchPanel").css('height',$(document).height()-100+'px');
        //$("#searchContent").css('width',$("#allSearchEl").width()-240+'px');
        $("#searchPanel").css('height',$(document).height()-130+'px');
        
        /*$("#allSearchEl").submit(function() {
            var string = "";
            $("#allSearchEl").children("input").each(function() {
                var obj = $(this);
                string += obj.attr('name');
            });
            alert(string);
            return false;
        });*/

});

function loadLink(url,params) {
    $.ajax({
        url : url,
        data: (params),
        
        success : function (data) {
            $("#searchPanel").unmask();
            $("#searchContent").html(data);
        },          
        beforeSend: function(xhr) { 
            $("#searchContent").html('');
            $("#searchPanel").mask("<?php echo __('Loading...'); ?>",300);    
        }  
    });
}
</script>
<style type='text/css'>

    #loadingSearch {
        position: absolute;
        top: 5px;
        right: 5px;
        }
    
    #leftSearchPanel {
       background-color:#cad9ec;
       float:left;
       width:220px; 
    }
    
    #allSearchEl {
        width:100%;
    }
    #searchContent {
        font-size: 13px;
        font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
        width:100%;
        float:left;
        margin: 0 auto;
        margin-top:0px;
        margin-right:5px;
        padding:0;
        margin-right:0;

    }

    #searchPanel {
        overflow:auto;
    }

</style>

<script type="text/javascript">
$(function() {
    $("#allSearchEl").keypress(function (e) {
        if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
            submitBtnClick();
            return false;
        } else {
            return true;
        }
    });
});
</script>



<div id="allSearchEl">
<div id="leftSearchPanel">

<div id="defaultPanel" class="x-hidden"></div>
<div id="resultsPanel" class="x-hidden"></div>
<div id="locationPanel" class="x-hidden"></div>
</div>
<div id='loadingSearch' style='display:none'><?php echo __('Loading...'); ?></div>
<div id="searchPanel">
<div id='searchContent'>
<div id="searchForm">
</div>

<div id="additionalData">
</div>

<div id="tagField">
<div type="tags" name="tagSearchField" style="display:none;"></div>
    
    <div id="tagBoxSearch">
        <ul id="tagBoxListSearch"></ul>
    </div>
    
    <script type="text/javascript">
    $(document).ready(function() {
        $("#tagBoxListSearch").tagit({
            url: '<?php echo url_for('tags/autocompleteTagData'); ?>',
            submitname: 'tagBoxSearchValues',
            submitid:'tagBoxSearchValues'
        });

    });
    </script>

</div>


</div>
</div>

</div>
