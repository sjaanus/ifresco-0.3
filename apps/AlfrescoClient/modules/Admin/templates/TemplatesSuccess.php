<script type="text/javascript">

var mainTemplateGrid = null;

Ext.onReady(function(){

    Ext.state.Manager.setProvider(
        new Ext.state.CookieProvider({
            expires: new Date(new Date().getTime()+(1000*60*60*24*365)) //1 year from now
    }));

    
    Ext.QuickTips.init();

    var templateReader = new Ext.data.JsonReader({
        idProperty:'id',
        fields: [{name: 'id'},{name: 'class'},{name: 'multiColumns'},{name: 'aspectsView'},{name: 'tabs'},{name: 'action'}],
        root:'templates',
        remoteGroup:true,
        remoteSort:true
    });
  
    var templateStore = new Ext.data.GroupingStore({
            reader: templateReader,
            // use remote data
            proxy : new Ext.data.HttpProxy({
                url: '<?php echo url_for('Admin/TemplateList') ?>',
                method: 'GET'
            }),
            sortInfo: {field: 'id', direction: 'ASC'}
        });
        
    templateStore.load();

        
    var templateGrid = new Ext.grid.GridPanel({
        loadMask: {msg:'<?php echo __('Loading Templates...'); ?>'},
        layout:'fit',
        autoHeight: true,

        ds: templateStore,
        columns: [{id:'class',header: "<?php echo __('Content Type'); ?>", width: 150, sortable: true, dataIndex: 'class'},
            {header: "<?php echo __('Multi Column'); ?>", width: 40, sortable: true, dataIndex: 'multiColumns'},
            {header: "<?php echo __('Aspects show as'); ?>", width: 50, sortable: true, dataIndex: 'aspectsView'},
            {header: "<?php echo __('Tabs'); ?>", width: 40, sortable: true, dataIndex: 'tabs'},
            {header: "<?php echo __('Action'); ?>", width: 40, sortable: false, dataIndex: 'action'}
        ],
        view: new Ext.grid.GroupingView({
            forceFit:true,
            showGroupName: false,
            enableNoGroups:true,
            enableGroupingMenu:true,
            hideGroupedColumn: false,
            emptyText: '<img src="/images/icons/information.png" align="absmiddle"> <?php echo __('No templates to display.'); ?>'
        }),
        viewConfig: {
            forceFit:true,
            autoHeight: true
        },        
        //plugins: expander,
        collapsible: true,
        animCollapse: true,
        header: false,
        id: 'templateGrid',
        stateId :'templateGrid-stateid',
        stateful : false,
        renderTo: 'templateGridContent'
    });
    mainTemplateGrid = templateGrid;
    
});

function editTemplate(id) {
    loadLink('<?php echo url_for('Admin/TemplateDesigner') ?>',{id:id}); 
}

var deleteId = null;
function deleteTemplate(id,classname) {
    Ext.MessageBox.show({
       title:'<?php echo __('Delete template?'); ?>',
       msg: '<?php echo __('Do you really want to delete the template:'); ?><br><b>'+classname+' ('+id+')</b>',
       fn:deleteTemplateResult,
       buttons: Ext.MessageBox.YESNO,
       icon: Ext.MessageBox.QUESTION
   });
   deleteId = id;
}

function deleteTemplateResult(btn){
    if (btn == "yes") {
        $.ajax({
            url : "<?php echo url_for('Admin/DeleteTemplate') ?>?id="+deleteId,
            
            success : function (data) {
                mainTemplateGrid.store.load();
                
                Ext.MessageBox.show({
                   title:'<?php echo __('Successfully delete a template!'); ?>',
                   msg: '<?php echo __('Your template was delete successfully!'); ?>',
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.INFO
               });
            },
            error: function(req, textStatus, errorThrown) {

                 Ext.MessageBox.show({
                   title:'<?php echo __('Delete template - Error occured!'); ?>',
                   msg: '<?php echo __('The Template could not be deleted %1% Reason: %2%',array("%1%"=>'<br><b>',"%2%"=>'</b>')); ?><br>'+textStatus,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
               });
            }       
        });   
    }
   
    
    deleteId = null;
}


</script>

<div id="templateGridContent">

</div>
