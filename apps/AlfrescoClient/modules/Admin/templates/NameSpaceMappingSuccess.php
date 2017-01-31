<script type="text/javascript">

var mainNamespaceGrid = null;

Ext.onReady(function(){

    Ext.state.Manager.setProvider(
        new Ext.state.CookieProvider({
            expires: new Date(new Date().getTime()+(1000*60*60*24*365)) //1 year from now
    }));

    
    Ext.QuickTips.init();

    var namespaceReader = new Ext.data.JsonReader({
        idProperty:'namespace',
        record: 'NameSpaceMap',
        fields: [{name: 'id',type: 'int'},{name: 'namespace',type: 'string'},{name: 'prefix',type: 'string'}],
        root:'namespacemaps',
        remoteGroup:true,
        remoteSort:true
    });
  

    var namespaceStore = new Ext.data.GroupingStore({
            reader: namespaceReader,
            // use remote data
            proxy : new Ext.data.HttpProxy({
                url: '<?php echo url_for('Admin/NameSpaceMapList') ?>',
                method: 'GET'

            }),
            sortInfo: {field: 'namespace', direction: 'ASC'}
        });
        
    namespaceStore.load();

    var fm = Ext.form;

    var editor = new Ext.ux.grid.RowEditor({
        saveText: '<?php echo __('Update'); ?>'
    });

        
    var namespaceGrid = new Ext.grid.GridPanel({
        loadMask: {msg:'<?php echo __('Loading Namespaces...'); ?>'},
        layout:'fit',
        //autoHeight: true,
        height: 500,

        //clicksToEdit: 1,
        autoScroll: true,
        plugins: [editor],

        tbar: [{text: '<?php echo __('Save'); ?>',
                iconCls: 'silk-disk',
                handler : function(){
                    if (namespaceStore.getCount() > 0) {
                        //var Records = namespaceStore.getRange(0,namespaceStore.getCount());
                        var Records = namespaceStore.getModifiedRecords();
                        var jsonArr = [];
                        for (var i = 0; i < Records.length; i++) {
                            
                            //var namespace = Records[i].data.namespace;
                            //var prefix = Records[i].data.prefix;
                            //var rec = {namespace:namespace,prefix:prefix};
                            jsonArr.push(Records[i].data);
                        }
                        var result = $.JSON.encode(jsonArr);

                        $.ajax({
                            url : "<?php echo url_for('Admin/NameSpaceMapListUpdate') ?>",
                            data: "data="+result,
                            type:"POST",
                            
                            success : function (data) {
                                namespaceStore.load();
                            },  
                            error:function (xhr, ajaxOptions, thrownError){
                                namespaceStore.load();
                            }  
                        });
                    }
                }
            },'-',
            {
                text: '<?php echo __('Add a Namespace'); ?>',
                iconCls: 'silk-add',
                handler : function(){
                    // access the Record constructor through the grid's store
                    /*var NameSpaceMap = namespaceGrid.getStore().recordType;
                    var n = new NameSpaceMap({
                        id:0,
                        namespace: '',
                        prefix: ''
                    });
                    namespaceGrid.stopEditing();
                    namespaceStore.insert(0, n);
                    namespaceGrid.startEditing(0, 0);*/
                    
                    var n = new namespaceGrid.store.recordType({
                        id : 0,
                        namespace: '',
                        prefix : ''
                    });
                    
                    editor.stopEditing();
                    namespaceGrid.store.insert(0, n);
                    editor.startEditing(0);

                }
            },'-', 
            {
                text: '<?php echo __('Delete the selected Namespace'); ?>',
                iconCls: 'silk-delete',
                handler: onDelete
            
        }],

        ds: namespaceStore,
        columns: [{id:'namespace',
                   header: "<?php echo __('Namespace'); ?>", 
                   width: 200, 
                   sortable: true, 
                   dataIndex: 'namespace',
                   editor: new fm.TextField({
                   })
                 },
                 {header: "<?php echo __('Prefix'); ?>", 
                  width: 100, 
                  sortable: true, 
                  dataIndex: 'prefix',
                  editor: new fm.TextField({
                   })
                 }
        ],
        view: new Ext.grid.GroupingView({
            forceFit:true,
            showGroupName: false,
            enableNoGroups:true,
            enableGroupingMenu:true,
            hideGroupedColumn: false,
            emptyText: '<img src="/images/icons/information.png" align="absmiddle"> <?php echo __('No Namespaces to display.'); ?>'
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
    
    function onDelete() {
        var rec = namespaceGrid.getSelectionModel().getSelected();
        if (!rec) {
            return false;
        }

        var result = $.JSON.encode(rec.data);

                        
        $.ajax({
            url : "<?php echo url_for('Admin/NameSpaceMapListDelete') ?>",
            data: "data="+result,
            type:"POST",
            
            success : function (data) {
                namespaceGrid.store.remove(rec);
            },  
            error:function (xhr, ajaxOptions, thrownError){
            }  
        });
        
    }

    
    mainNamespaceGrid = namespaceGrid;
});



var deleteId = null;
function deleteNameSpaceMap(id,text) {
    Ext.MessageBox.show({
       title:'<?php echo __('Delete a Namespacemap?'); ?>',
       msg: '<?php echo __('Do you really want to delete the template:'); ?><br><b>'+text+' ('+id+')</b>',
       fn:deleteTemplateResult,
       buttons: Ext.MessageBox.YESNO,
       icon: Ext.MessageBox.QUESTION
   });
   deleteId = id;
}

function deleteNameSpaceMap(btn){
    if (btn == "yes") {
        $.ajax({
            url : "<?php echo url_for('Admin/DeleteNameSpaceMap') ?>?id="+deleteId,
            
            success : function (data) {
                mainNamespaceGrid.store.load();
                
                Ext.MessageBox.show({
                   title:'<?php echo __('Successfully deleted a Namespacemap!'); ?>',
                   msg: '<?php echo __('Your Namespacemap was delete successfully!'); ?>',
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.INFO
               });
            },
            error: function(req, textStatus, errorThrown) {

                 Ext.MessageBox.show({
                   title:'<?php echo __('Delete a Namespacemap - Error occured'); ?>',
                   msg: '<?php echo __('The Namespacemap could not be deleted %1% Reason: %2%',array('%1%'=>'<br><b>','%2%'=>'</b>')); ?><br>'+textStatus,
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