<script type="text/javascript">
var mainsearchColumnsSetGrid = null;

Ext.onReady(function(){

    Ext.state.Manager.setProvider(
        new Ext.state.CookieProvider({
            expires: new Date(new Date().getTime()+(1000*60*60*24*365)) //1 year from now
    }));

    
    Ext.QuickTips.init();

    var columnSetReader = new Ext.data.JsonReader({
        idProperty:'id',
        fields: [{name: 'id'},{name: 'name'},{name: 'defaultset'},{name: 'action'}],
        root:'columns',
        remoteGroup:true,
        remoteSort:true
    });
  
    var columnSetStore = new Ext.data.GroupingStore({
            reader: columnSetReader,
            // use remote data
            proxy : new Ext.data.HttpProxy({
                url: '<?php echo url_for('Admin/SearchColumnSetList') ?>',
                method: 'GET'
            }),
            sortInfo: {field: 'id', direction: 'ASC'}
        });
        
    columnSetStore.load();

        
    var searchColumnsSetGrid = new Ext.grid.GridPanel({
        loadMask: {msg:'<?php echo __('Loading Templates...'); ?>'},
        layout:'fit',
        autoHeight: true,

        ds: columnSetStore,
        columns: [{id:'name',header: "<?php echo __('Name'); ?>", width: 150, sortable: true, dataIndex: 'name'},
            {header: "<?php echo __('Default'); ?>", width: 40, sortable: true, dataIndex: 'defaultset'},
            {header: "<?php echo __('Action'); ?>", width: 40, sortable: false, dataIndex: 'action'}
        ],
        view: new Ext.grid.GroupingView({
            forceFit:true,
            showGroupName: false,
            enableNoGroups:true,
            enableGroupingMenu:true,
            hideGroupedColumn: false,
            emptyText: '<img src="/images/icons/information.png" align="absmiddle"> <?php echo __('No ColumnSets to display.'); ?>'
        }),
        viewConfig: {
            forceFit:true,
            autoHeight: true
        },        
        //plugins: expander,
        collapsible: true,
        animCollapse: true,
        header: false,
        id: 'searchColumnsSetGrid',
        stateId :'searchColumnsSetGrid-stateid',
        stateful : false,
        renderTo: 'searchColumnsSetGridContent'
    });
    mainsearchColumnsSetGrid = searchColumnsSetGrid;
    
});

function editColumnSet(id) {
    loadLink('<?php echo url_for('Admin/SearchColumnSetsAdd') ?>',{id:id});
}

function markDefaultColumnSet(id) {
    $.ajax({
        url : "<?php echo url_for('Admin/MarkDefaultColumnSet') ?>?id="+id,
        
        success : function (data) {
            mainsearchColumnsSetGrid.getStore().reload();
        },
        error: function(req, textStatus, errorThrown) {

             Ext.MessageBox.show({
               title:'<?php echo __('Mark default ColumnSet - Error occured!'); ?>',
               msg: '<?php echo __('The ColumnSet could not be marked as default ColumnSet %1% Reason: %2%',array("%1%"=>'<br><b>',"%2%"=>'</b>')); ?><br>'+textStatus,
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.ERROR
           });
        }       
    });
    
}

var deleteId = null;
function deleteColumnSet(id,classname) {
    Ext.MessageBox.show({
       title:'<?php echo __('Delete ColumnSet?'); ?>',
       msg: '<?php echo __('Do you really want to delete the ColumnSet:'); ?><br><b>'+classname+' ('+id+')</b>',
       fn:deleteColumnSetResult,
       buttons: Ext.MessageBox.YESNO,
       icon: Ext.MessageBox.QUESTION
   });
   deleteId = id;
}

function deleteColumnSetResult(btn){
    if (btn == "yes") {
        $.ajax({
            url : "<?php echo url_for('Admin/DeleteSearchColumnSet') ?>?id="+deleteId,
            
            success : function (data) {
                mainsearchColumnsSetGrid.store.load();
                
                Ext.MessageBox.show({
                   title:'<?php echo __('Successfully deleted a ColumnSet!'); ?>',
                   msg: '<?php echo __('Your ColumnSet is deleted successfully!'); ?>',
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.INFO
               });
            },
            error: function(req, textStatus, errorThrown) {

                 Ext.MessageBox.show({
                   title:'<?php echo __('Delete ColumnSet - Error occured!'); ?>',
                   msg: '<?php echo __('The ColumnSet could not be deleted %1% Reason: %2%',array("%1%"=>'<br><b>',"%2%"=>'</b>')); ?><br>'+textStatus,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
               });
            }       
        });   
    }
   
    
    deleteId = null;
}


</script>

<div id="searchColumnsSetGridContent">

</div>