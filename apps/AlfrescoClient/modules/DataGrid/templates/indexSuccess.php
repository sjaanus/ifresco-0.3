
<script type="text/javascript">

var lastParams<?php echo $containerName; ?> = null;
var currentColumnsetid<?php echo $containerName; ?> = 0;
var mainGrid<?php echo $containerName; ?> = null;
var versionList<?php echo $containerName; ?> = null;
var versionStore<?php echo $containerName; ?> = null;
var currentNodeId<?php echo $containerName; ?> = null;
var orgDetailUrl<?php echo $containerName; ?> = '<?php echo $DetailUrl; ?>';
var detailUrl<?php echo $containerName; ?> = orgDetailUrl<?php echo $containerName; ?>;
    
var PanelNodeId<?php echo $containerName; ?> = null; 
var PanelNodeMimeType<?php echo $containerName; ?> = null; 
var PanelNodeText<?php echo $containerName; ?> = null; 
var PanelNodeType<?php echo $containerName; ?> = null; 
var PanelNodeUrl<?php echo $containerName; ?> = null; 
var PanelNodeIsCheckedOut<?php echo $containerName; ?> = null;
var PanelNodeCheckedOutId<?php echo $containerName; ?> = null;
var PanelNodeOrgId<?php echo $containerName; ?> = null;

var SelectedVersion<?php echo $containerName; ?> = null; 


var ZohoMimeDocs = ["application/msword","application/vnd.openxmlformats-officedocument.wordprocessingml.document","application/rtf","text/rtf","text/html","application/vnd.oasis.opendocument.text","application/vnd.sun.xml.writer","text/plain"];
var ZohoMimeSheet = ["application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.oasis.opendocument.spreadsheet","application/vnd.sun.xml.calc","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","text/csv","text/comma-separated-values","text/tab-separated-values"];

Ext.onReady(function(){
    /* Split Vertical or Horizontal */
    if (Registry.getInstance().get("ArrangeList")==="horizontal") {
        var docGridRegion = "north";
        var docGridWidth = "100%";
        var docGridHeight = getHalfSize();
        var tabPrevRegion = "center";
        var tabPrevWidth = "100%";
        var tabPrevHeight = getHalfSize();
        var tabPrevTabPos = "bottom";
        
        if ($.browser.msie) {
            mySize<?php echo $containerName; ?>();
        }
        
        var versionSize = getHalfSize()-25;
        var gridSize = getHalfSize();
    }
    else {
        var docGridRegion = "west";
        var docGridWidth = "50%";
        
        var tabPrevRegion = "center";
        var tabPrevWidth = "50%";
        if ($.browser.msie) {
            mySize<?php echo $containerName; ?>();
        }
        var docGridHeight = "100%";
        var tabPrevHeight = "100%";

        var tabPrevTabPos = "top";
        
        var versionSize = $("#documentGrid<?php echo $containerName; ?>").height()-30;
        var gridSize = $("#documentGrid<?php echo $containerName; ?>").height();
    }
    

    Ext.state.Manager.setProvider(
        new Ext.state.CookieProvider({
            expires: new Date(new Date().getTime()+(1000*60*60*24*365))
    }));


    Ext.QuickTips.init();

    var columnStore<?php echo $containerName; ?> = new Ext.data.JsonStore({
        autoDestroy: true,
        url: '<?php echo url_for('DataGrid/GetColumns') ?>',
        storeId: 'columnsetStore<?php echo $containerName; ?>',
        root: 'columnsets',
        idProperty: 'id',
        fields: ['id', 'name']
    });

    columnStore<?php echo $containerName; ?>.load();
    
    var columnStoreCombo<?php echo $containerName; ?> = new Ext.form.ComboBox({
        store: columnStore<?php echo $containerName; ?>,
        displayField:'name',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        emptyText: '<?php echo __('Select a columnset to change the columns...'); ?>',
        selectOnFocus: true,
        width: 250,
        iconCls: 'no-icon',
        listeners:{
            'select':loadNewColumns<?php echo $containerName; ?>
        }
    });
    

    var reader<?php echo $containerName; ?> = new Ext.data.JsonReader({
        idProperty:'nodeId',
        fields: [<?php echo html_entity_decode($fields,ENT_QUOTES); ?>],
        root:'data',
        remoteGroup:true,
        remoteSort: true,
        //remoteSort: false,
        totalProperty:'totalCount'
    });

    var store<?php echo $containerName; ?> = new Ext.data.GroupingStore({
            reader: reader<?php echo $containerName; ?>,
            proxy : new Ext.data.HttpProxy({
                url: '<?php echo url_for('DataGrid/GridData') ?>',
                method: 'GET',
                timeout : 1200000
            }),
            remoteSort: true,
            //remoteSort: false,
            sortInfo: {field: 'nodeId', direction: 'ASC'},
            
            listeners:{
                exception:function(proxy,type,action,options,response) {
                    Ext.MessageBox.show({
                        title: '<?php echo __('Too many results'); ?>',
                        msg: '<?php echo __('The result list was too big. Please add more arguments to get a good result.'); ?>',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.WARNING,
                        buttons: { 
                            ok: "<?php echo __('Search again'); ?>",
                            cancel: "<?php echo __('Cancel Search'); ?>"
                        },
                        fn:function(btn) {
                            if (btn == "ok") {
                                var params = options.params;
                                store<?php echo $containerName; ?>.load(params);
                            }
                            Ext.MessageBox.hide(); 
                        }

                    });
                }
            }
        });
        
    var win<?php echo $containerName; ?>, winSpace<?php echo $containerName; ?>, winMetaData<?php echo $containerName; ?>;
    
    var grid<?php echo $containerName; ?> = new Ext.grid.GridPanel({
        loadMask: {msg:'<?php echo __('Loading Documents...'); ?>'},
        layout:'fit',
        height:'auto',
        listeners: {
            rowcontextmenu: contextMenuFunc<?php echo $containerName; ?>
        },
        ds: store<?php echo $containerName; ?>,
        columns: [<?php echo html_entity_decode($columns,ENT_QUOTES); ?>],
        view: new Ext.grid.GroupingView({
            forceFit:true,
            showGroupName: false,
            enableNoGroups:true,
            enableGroupingMenu:true,
            hideGroupedColumn: false,
            emptyText: '<img src="/images/icons/information.png" align="absmiddle"> <?php echo __('No items to display.'); ?>'
        }),

        tbar: [{
            iconCls:'open-alfresco',
            id: 'open-alfresco<?php echo $containerName; ?>',
            text:'<?php echo __('Open Folder in Alfresco'); ?>',
            handler: function(){
                window.open(detailUrl<?php echo $containerName; ?>);    
            },
            scope: this
        },'-',
        {
            iconCls:'upload-content',
            id:'upload-content<?php echo $containerName; ?>',
            text:'<?php echo __('Upload File(s)'); ?>',
            handler: function(){
                if(!win<?php echo $containerName; ?>){
                    win<?php echo $containerName; ?> = new Ext.Window({
                        modal:true,
                        applyTo:'upload-window<?php echo $containerName; ?>',
                        layout:'fit',
                        width:500,
                        height:350,
                        closeAction:'hide',
                        plain: true,

                        items: new Ext.Panel({
                            applyTo: 'upload-window-panel<?php echo $containerName; ?>',
                            layout:'fit',
                            border:false
                        }),

                        listeners:{
                            'beforeshow':{
                                fn:function() {
                                    $(".PDFRenderer").hide();
                                }
                            },
                            'hide': {
                                fn:function() {
                                    $(".PDFRenderer").show();                      
                                }
                            }
                        },

                        buttons: [{
                            text: '<?php echo __('Close'); ?>',
                            handler: function() {
                                win<?php echo $containerName; ?>.hide(this);
                                $(".PDFRenderer").show();  
                                mainGrid<?php echo $containerName; ?>.getStore().reload();
                            }
                        }]
                    });
                }

                $.ajax({
                    cache: false,  
                    url : "<?php echo url_for('Upload/index') ?>",
                    data: ({'nodeId' : currentNodeId<?php echo $containerName; ?>, 'containerName':'<?php echo $containerName; ?>'}),               
                    
                    success : function (data) {
                        $("#upload-window-panel<?php echo $containerName; ?>").html(data);  
                    }    
                });
                
                win<?php echo $containerName; ?>.show();
            },
            scope: this
        },
        {
            iconCls:'create-folder',
            id:'create-folder<?php echo $containerName; ?>',
            text:'<?php echo __('Create Space'); ?>',
            handler: function(){
                if(!winSpace<?php echo $containerName; ?>){
                    winSpace<?php echo $containerName; ?> = new Ext.Window({
                        modal:true,
                        applyTo:'general-window<?php echo $containerName; ?>',
                        layout:'fit',
                        width:500,
                        height:350,
                        closeAction:'hide',
                        title:'<?php echo __('Create Space'); ?>',
                        plain: true,

                        items: new Ext.Panel({
                            applyTo: 'window-panel<?php echo $containerName; ?>',
                            layout:'fit',
                            border:false
                        }),

                        listeners:{
                            'beforeshow':{
                                fn:function() {
                                    $(".PDFRenderer").hide();  
                                    $("#general-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
                                    $.ajax({
                                        cache: false,  
                                        url : "<?php echo url_for('FolderActions/index') ?>",
                                        
                                        success : function (data) {
                                            $("#general-window<?php echo $containerName; ?>").unmask();
                                            $("#window-panel<?php echo $containerName; ?>").html(data);
                                        }    
                                    });    
                                }
                            },
                            'hide': {
                                fn:function() {
                                    $(".PDFRenderer").show();                      
                                }
                            }
                        },

                        buttons: [{
                            text: '<?php echo __('Save'); ?>',
                            handler: function() {
                                
                                $.post("<?php echo url_for('FolderActions/CreateSpacePOST') ?>", $("#spaceCreateForm").serialize()+"&nodeId="+currentNodeId<?php echo $containerName; ?>, function(data) {
                                    if (data.success === "true") {

                                        Ext.MessageBox.show({
                                           title: '<?php echo __('Success'); ?>',
                                           msg: data.message,
                                           buttons: Ext.MessageBox.OK,
                                           icon: Ext.MessageBox.INFO,
                                           fn:showRenderer
                                       });

                                        Ext.getCmp('alfrescoTree').getRootNode().reload();
                                        Ext.getCmp('alfrescoTree').render();
                                        
                                        winSpace<?php echo $containerName; ?>.hide(this);
                                        
                                        grid<?php echo $containerName; ?>.getStore().reload();   
                                        
                                        $("#window-panel<?php echo $containerName; ?>").html('');
                                        
                                    }
                                    else {

                                        Ext.MessageBox.show({
                                           title: '<?php echo __('Error'); ?>',
                                           msg: data.message,
                                           buttons: Ext.MessageBox.OK,
                                           icon: Ext.MessageBox.WARNING,
                                           fn:showRenderer
                                       });
                                    }
                                }, "json");
                            }
                        },
                        {
                            text: '<?php echo __('Close'); ?>',
                            handler: function() {
                                $(".PDFRenderer").show(); 
                                $("#window-panel<?php echo $containerName; ?>").html('');
                                
                                winSpace<?php echo $containerName; ?>.hide(this);
                            }
                        }]
                    });
                }
                winSpace<?php echo $containerName; ?>.show();
            }
        },'-',columnStoreCombo<?php echo $containerName; ?>,'-',{
            iconCls:'export-csv',
            text:'<?php echo __('Export CSV'); ?>',
            handler: function(){
                var params = lastParams<?php echo $containerName; ?>;
                window.open('<?php echo url_for('DataGrid/ExportResultSet') ?>?'+object2string(params.params));    
            },
            scope: this
        },'-',{
            iconCls:'pasteCopy-clipboard',
            tooltip:'<?php echo __('Paste clipboard (copy)'); ?>',
            id:'pastecopy-clipboard<?php echo $containerName; ?>',
            cls: 'x-btn-icon',
            handler: function(){
                pasteClipBoard<?php echo $containerName; ?>('copy');
            },
            scope: this
        },{
            iconCls:'pasteCut-clipboard',
            tooltip:'<?php echo __('Paste clipboard (cut)'); ?>',      
            id:'pastecut-clipboard<?php echo $containerName; ?>',        
            handler: function(){
                pasteClipBoard<?php echo $containerName; ?>('cut');
            },
            scope: this
        },'-',{
            iconCls:'refresh-meta',
            text:'<?php echo __('Refresh'); ?>',
            handler: function(){
                refreshGrid<?php echo $containerName; ?>();
            },
            scope: this
        }],
        
        bbar: new Ext.PagingToolbar({
            pageSize: 30,
            store: store<?php echo $containerName; ?>,
            displayInfo: true,
            displayMsg: '{0} - {1} <?php echo __('of'); ?> {2}',
            emptyMsg: "",
            listeners: {
                beforechange:function(toolbar,pageData) {
                    var newbaseParams = lastParams<?php echo $containerName; ?>.params;
                    Ext.apply(store<?php echo $containerName; ?>.baseParams, newbaseParams);
                },
                change:function(toolbar,pageData) {
                    if (pageData.total <= this.pageSize && pageData.pages < 2)
                        store<?php echo $containerName; ?>.remoteSort = false;
                    else
                        store<?php echo $containerName; ?>.remoteSort = true;
                        //store<?php echo $containerName; ?>.remoteSort = false;
                }
            }
        }),
        
        viewConfig: {

            forceFit:true

        },        

        collapsible: true,
        animCollapse: true,
        header: false,
        id: 'dataGrid<?php echo $containerName; ?>',
        iconCls: 'icon-grid',

        stateId :'documentGrid-stateid<?php echo $containerName; ?>',
        stateful : true

    });
    mainGrid<?php echo $containerName; ?> = grid<?php echo $containerName; ?>;
    
    
    
    // VERSIONS 
    var VersionStore<?php echo $containerName; ?> = new Ext.data.JsonStore({
        url : "<?php echo url_for('Versioning/getJSON') ?>",
        root: 'versions',
        fields: ['nodeRef', 'nodeId', 'version', 'description', {name:'date', type:'date', dateFormat:'timestamp'},'dateFormat', 'author']
    });
    
    versionStore<?php echo $containerName; ?> = VersionStore<?php echo $containerName; ?>;

    var VersionlistView<?php echo $containerName; ?> = new Ext.list.ListView({
        store: VersionStore<?php echo $containerName; ?>,
        height:200,
        header:false,
        multiSelect: false,
        loadingText: '<?php echo __('Loading...'); ?>',
        emptyText: '<span style="font-size:12px;"><img src="/images/icons/information.png" align="absmiddle"> <?php echo __('This document has no version history.'); ?></span>',
        reserveScrollOffset: true,

        columns: [{
            header: '<?php echo __('Version'); ?>',
            dataIndex: 'version',
            width: .10
        },{
            header: '<?php echo __('Note'); ?>',
            dataIndex: 'description'
        },
        {
            header: '<?php echo __('Date'); ?>',
            xtype: 'datecolumn',
            format: '<?php echo $DateFormat; ?> <?php echo $TimeFormat; ?>',
            dataIndex: 'date',
            width: .15
        },
        {
            header: '<?php echo __('Author'); ?>',
            dataIndex: 'author',
            width: .10
        }],
        
        listeners: {
            contextmenu: function(dataview, index, node, event){
                 var existingMenu = Ext.getCmp('version-ctx<?php echo $containerName; ?>');
                if (existingMenu !== null && typeof existingMenu !== 'undefined') {
                    existingMenu.destroy();
                }   
                
                
                var selectedVersion = VersionStore<?php echo $containerName; ?>.getAt(index);
                var ParentNodeId = selectedVersion.data.nodeRef;
                var Version = selectedVersion.data.version;
                var VersionId = selectedVersion.data.nodeId;
                
                 event.stopEvent();
                 var mnxContext = new Ext.menu.Menu({
                    id:'version-ctx<?php echo $containerName; ?>',
                    items: [{
                        iconCls: 'revert-version',
                        text: '<?php echo __('Revert to this Version'); ?>',     
                        //disabled:(editRights === true ? false : true),  // TODO CHECK FOR RIGHTS HERE!
                        scope:this,
                        handler: function(){
                            SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            Ext.MessageBox.show({
                               title: '<?php echo __('Revert to Version'); ?>',
                               msg: "<?php echo __('Are you sure you want to revert to Version:'); ?> <b>"+Version+"</b>",
                               buttons: Ext.MessageBox.YESNO,
                               icon: Ext.MessageBox.INFO,
                               fn:revertVersion<?php echo $containerName; ?>
                           });
                        }
                    },'-',{
                        iconCls: 'add-version',
                        text: '<?php echo __('New Version'); ?>',     
                        scope:this,
                        handler: function(){
                            SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            createNewVersion<?php echo $containerName; ?>(data,false);
                        }
                    },
                    {
                        iconCls: 'upload-version',
                        text: '<?php echo __('Upload new Version'); ?>',     
                        scope:this,
                        handler: function(){
                            SelectedVersion<?php echo $containerName; ?> = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            var data = {nodeId: ParentNodeId,versionNodeId:VersionId,version:Version};
                            createNewVersion<?php echo $containerName; ?>(data,true);
                        }
                    }]
                });
                 mnxContext.showAt(event.xy);
            },
            dblclick: function(dataview, index, node, event) {
                var nodeId = VersionStore<?php echo $containerName; ?>.getAt(index).data.nodeId;
                var dlUrl = '<?php echo url_for('Versioning/DownloadVersion') ?>?nodeId='+nodeId;
                window.open(dlUrl);
            }

        }
    });
    

    var VersionPanel<?php echo $containerName; ?> = new Ext.Panel({
        id:'version-view<?php echo $containerName; ?>',
        width:'100%',
        height:versionSize,
        collapsible:false,
        header:false,
        layout:'fit',
        title:'<?php echo __('Versions'); ?>',
        tbar:[{
            iconCls: 'add-version',
            text: '<?php echo __('New Version'); ?>',     
            scope:this,
            handler: function(){
                SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                createNewVersion<?php echo $containerName; ?>(data,false);
            }
        },
        {
            iconCls: 'upload-version',
            text: '<?php echo __('Upload new Version'); ?>',     
            scope:this,
            handler: function(){
                SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                createNewVersion<?php echo $containerName; ?>(data,true);
            }
        },'->',{
            iconCls: 'version-panel',
            text: '<?php echo __('Detailed Version Info'); ?>',     
            scope:this,
            handler: function(){
                SelectedVersion<?php echo $containerName; ?> = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                var data = {nodeId: PanelNodeId<?php echo $containerName; ?>};
                versionLookup<?php echo $containerName; ?>(data);
            }
        }],
        items: VersionlistView<?php echo $containerName; ?>
    });
    
    versionList<?php echo $containerName; ?> = VersionlistView<?php echo $containerName; ?>;

    
    var previewTab<?php echo $containerName; ?> = {region: 'center',
                        header:false,
                        id:'previewContent<?php echo $containerName; ?>',
                        maxSize: 150,
                        html: '<div id="previewWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>'
    };
    
    var versionsTab<?php echo $containerName; ?> = {region: 'center',
                        header:false,
                        id:'versionsContent<?php echo $containerName; ?>',
                        maxSize: 150,
                        items:[VersionPanel<?php echo $containerName; ?>]
    };
    
    var relationsTab<?php echo $containerName; ?> = {region: 'center',
                        header:false,
                        id:'relationsContent<?php echo $containerName; ?>',
                        maxSize: 150,
                        html: '<div id="relationsWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>'
    };
    
    var metadataTab<?php echo $containerName; ?> = {region: 'center',
                        header:false,
                        id:'metadataContent<?php echo $containerName; ?>',
                        maxSize: 150,
                        html: '<div id="metadataWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>',
                        tbar: [{
                            iconCls:'download-node',
                            id:'downloadContent<?php echo $containerName; ?>',
                            text:'<?php echo __('Download'); ?>',
                            handler: function(){
                                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                                var dlUrl = '<?php echo url_for('NodeActions/Download') ?>?nodeId='+nodeId;
                                window.open(dlUrl);
                            },
                            scope: this
                        },'-',{
                            iconCls:'view-metadata',
                            id:'editMetadata<?php echo $containerName; ?>',
                            text:'<?php echo __('Edit Metadata'); ?>',
                            handler: function(){
                                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                                var nodeName = PanelNodeText<?php echo $containerName; ?>;
                                
                                editMetadata<?php echo $containerName; ?>(nodeId,nodeName);
                            },
                            scope: this
                        },{
                            iconCls:'manage-aspects',
                            id:'manageAspects<?php echo $containerName; ?>',
                            text:'<?php echo __('Manage Aspects'); ?>',
                            handler: function(){
                                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                                var nodeName = PanelNodeText<?php echo $containerName; ?>;
                                
                                manageAspects<?php echo $containerName; ?>(nodeId);
                            },
                            scope: this
                        },{
                            iconCls:'specify-type',
                            text:'<?php echo __('Specify Type'); ?>',
                            id:'specifyType<?php echo $containerName; ?>',
                            handler: function(){
                                var nodeId = PanelNodeId<?php echo $containerName; ?>;
                                
                                specifyType<?php echo $containerName; ?>(nodeId);
                            },
                            scope: this
                        },'-',{
                            iconCls:'checkout-node',
                            text:'<?php echo __('Checkout'); ?>',
                            id:'checkout<?php echo $containerName; ?>',
                            handler: function(){
                                if (PanelNodeIsCheckedOut<?php echo $containerName; ?> === true) {
                                    var nodeId = PanelNodeCheckedOutId<?php echo $containerName; ?>;
                                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;   
                                    //checkIn<?php echo $containerName; ?>(nodeId,mime);
                                    
                                    SelectedVersion<?php echo $containerName; ?> = {nodeId: nodeId,mime:mime};
                                    checkInWindow<?php echo $containerName; ?>(SelectedVersion<?php echo $containerName; ?>);
                                }
                                else {
                                    var nodeId = PanelNodeId<?php echo $containerName; ?>;   
                                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;   
                                    checkOut<?php echo $containerName; ?>(nodeId,mime);   
                                }    
                                
                                
                            },
                            scope: this
                        },{
                            iconCls:'zoho-writer',
                            text:'<?php echo __('Checkout to Zoho Writer'); ?>',
                            id:'checkoutZoho<?php echo $containerName; ?>',
                            hidden:<?php echo ($OnlineEditing === "zoho" ? 'false' : 'true');?>,
                            handler: function() {            
                                if (PanelNodeIsCheckedOut<?php echo $containerName; ?> === true) {
                                    var nodeId = PanelNodeCheckedOutId<?php echo $containerName; ?>;
                                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;   
                                    editInZoho<?php echo $containerName; ?>(nodeId,mime);                
                                }
                                else {
                                    var nodeId = PanelNodeId<?php echo $containerName; ?>;  
                                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;    
                                    checkOutZoho<?php echo $containerName; ?>(nodeId,mime);    
                                }
                            },
                            scope: this
                        },{
                            iconCls:'cancel-checkout',
                            text:'<?php echo __('Cancel Checkout'); ?>',
                            id:'cancel-checkout<?php echo $containerName; ?>',
                            hidden:true,
                            handler: function(){
                                if (PanelNodeIsCheckedOut<?php echo $containerName; ?> === true) {
                                    var nodeId = PanelNodeCheckedOutId<?php echo $containerName; ?>;
                                    var orgNodeId = PanelNodeOrgId<?php echo $containerName; ?>;
                                    var mime = PanelNodeMimeType<?php echo $containerName; ?>;   
                                    cancelCheckout<?php echo $containerName; ?>(nodeId,orgNodeId,mime);
                                }
                            },
                            scope: this
                        },'-',{
                            iconCls:'refresh-meta',
                            text:'<?php echo __('Refresh'); ?>',
                            handler: function(){
                                var nodeId = PanelNodeId<?php echo $containerName; ?>;   
                                loadMetaData<?php echo $containerName; ?>(nodeId);
                            },
                            scope: this
                        }]
    };
    
    var commentsTab<?php echo $containerName; ?> = {region: 'center',
                        header:false,
                        id:'commentsContent<?php echo $containerName; ?>',
                        maxSize: 150,
                        html: '<div id="commentsWindow<?php echo $containerName; ?>" style="height:100%;width:100%;"></div>'
    };
    
    var contentTab<?php echo $containerName; ?> = {};
    
    
    grid<?php echo $containerName; ?>.on('rowdblclick', function(grid, rowIndex, e) {   
        var nodeId = store<?php echo $containerName; ?>.getAt(rowIndex).id;
        var type = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_type;
        
        var nodeText = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_name;    
        
        if (type !== "{http://www.alfresco.org/model/content/1.0}folder") {    
            var url = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_url;    
                                        
            window.open(url);             
        }
        else {
            var tabnodeid = nodeId.replace(/-/g,"");
                addTabDynamic('tab-'+tabnodeid,nodeText);
                 $.ajax({
                     cache: false,  
                    url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid<?php echo $containerName; ?>,
                    success : function (data) {
                        $("#overAll").unmask();
                        $("#tab-"+tabnodeid).html(data);

                        eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});");
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo __('Loading'); ?> "+nodeText+"...",300);    
                    } 
                });    
        }
    }); 

    grid<?php echo $containerName; ?>.on('rowclick', function(grid, rowIndex, e) {
        
        
        var nodeId = store<?php echo $containerName; ?>.getAt(rowIndex).id;
        var type = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_type;
        var nodeText = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_name;
        var nodeUrl = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_url;

        var mainPreviewTab = Ext.getCmp('mainPreviewTab<?php echo $containerName; ?>');
        var mainVersionTab = Ext.getCmp('mainVersionsTab<?php echo $containerName; ?>');
        var mainMetadataTab = Ext.getCmp('mainMetadataTab<?php echo $containerName; ?>');
        
        // mimetype
        var MimeType = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_mimetype;
        
        // RIGHTS 
        var editRights = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_perm_edit;
        var delRights = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_perm_delete;
        var cancelCheckoutRights = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_perm_cancel_checkout;
        var createRights = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_perm_create;
        var hasRights = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_perm_permissions;
        
        // CHECKOUT LOGIC 
        var isWorkingCopy = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_isWorkingCopy;
        var isCheckedOut = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_isCheckedOut;
        var originalId = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_originalId;
        var workingCopyId = store<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_workingCopyId;
        
        // BUTTONS 
        var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
        var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
        var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');
        var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');         
        var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');         
        var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');        
        var downloadContent = Ext.getCmp('downloadContent<?php echo $containerName; ?>'); 
        
        
        PanelNodeId<?php echo $containerName; ?> = nodeId;
        PanelNodeMimeType<?php echo $containerName; ?> = MimeType;
        PanelNodeText<?php echo $containerName; ?> = nodeText;
        PanelNodeType<?php echo $containerName; ?> = type;
        PanelNodeUrl<?php echo $containerName; ?> = nodeUrl;
        PanelNodeIsCheckedOut<?php echo $containerName; ?> = (isWorkingCopy === true || isCheckedOut === true ? true : false);
        if (isWorkingCopy === true || isCheckedOut === true) {
            var tempid = nodeId;
            var orgtempid = originalId;
            if (isCheckedOut === true) {
                tempid = workingCopyId;
            }

            PanelNodeOrgId<?php echo $containerName; ?> = orgtempid;
            PanelNodeCheckedOutId<?php echo $containerName; ?> = tempid;
        }
        else
            PanelNodeCheckedOutId<?php echo $containerName; ?> = null;     
        
        
        var tabPanel = Ext.getCmp("previewPanel<?php echo $containerName; ?>");  
        var activeTabId = "";
        if (tabPanel) { 
            var activeTab = tabPanel.activeTab;                  
            if (typeof activeTab !== 'undefined') {
                //activeTabTitle = activeTab.title;
                //activeTabId = activeTabId.toLowerCase();  
                activeTabId = activeTab.id
            }
        }                          
        
        if (type !== "{http://www.alfresco.org/model/content/1.0}folder") {
            mainPreviewTab.enable();
            mainVersionTab.enable();
            mainMetadataTab.enable();
            
            downloadContent.enable();
            // CHECK RIGTHS 
            if (editRights === true) {
                editMetaDataBtn.enable();
                manageAspectsBtn.enable();
                specifyTypeBtn.enable(); 
                checkoutBtn.enable();    
                
                <?php if ($OnlineEditing === "zoho") { ?>
                if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {          
                    if (isWorkingCopy === true || isCheckedOut === true)
                        checkoutZohoBtn.setText("<?php echo __('Edit in Zoho Writer'); ?>");   
                    else
                        checkoutZohoBtn.setText("<?php echo __('Checkout in Zoho Writer'); ?>"); 
                    checkoutZohoBtn.enable();  
                    checkoutZohoBtn.setVisible(true);  
                }
                else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {  
                    if (isWorkingCopy === true || isCheckedOut === true)
                        checkoutZohoBtn.setText("<?php echo __('Edit in Zoho Sheet'); ?>");    
                    else
                        checkoutZohoBtn.setText("<?php echo __('Checkout in Zoho Sheet'); ?>"); 
                    checkoutZohoBtn.enable();  
                    checkoutZohoBtn.setVisible(true);
                }
                else {         
                    checkoutZohoBtn.disable();  
                    checkoutZohoBtn.setVisible(false);
                }
                <?php } ?>
                
                if (isWorkingCopy === true || isCheckedOut === true) {
                    checkoutBtn.setText("Checkin");
                    checkoutBtn.setIconClass("checkin-node");

                    specifyTypeBtn.disable();
                    if (isWorkingCopy !== true) {
                        editMetaDataBtn.disable();
                        manageAspectsBtn.disable();
                        specifyTypeBtn.disable();
                    }
                    
                    cancelCheckoutBtn.enable();  
                    cancelCheckoutBtn.setVisible(true);
                }
                else {
                    checkoutBtn.setText("Checkout");
                    checkoutBtn.setIconClass("checkout-node");
                    
                    cancelCheckoutBtn.disable();  
                    cancelCheckoutBtn.setVisible(false);
                }
            }
            else {
                editMetaDataBtn.disable();
                manageAspectsBtn.disable();
                specifyTypeBtn.disable();
                checkoutBtn.disable(); 
                checkoutZohoBtn.disable(); 
                
                cancelCheckoutBtn.disable();  
                cancelCheckoutBtn.setVisible(false);
            }
            
            
            
            
            if (activeTabId === "mainPreviewTab<?php echo $containerName; ?>")
                loadPreview<?php echo $containerName; ?>(nodeId);
            else if (activeTabId === "mainVersionsTab<?php echo $containerName; ?>")
                versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeId}});
        }
        else {
            try {
                mainPreviewTab.enable();  
                mainMetadataTab.enable();
                mainVersionTab.disable();
                
                if (editRights === true) {
                    editMetaDataBtn.enable();
                    manageAspectsBtn.enable();
                    specifyTypeBtn.enable(); 
                }
                else {
                    editMetaDataBtn.disable();
                    manageAspectsBtn.disable();
                    specifyTypeBtn.disable();
                }
                
                checkoutBtn.disable();
                checkoutZohoBtn.disable();
                downloadContent.disable();
            }
            catch (err) { 
            }
            
            $("#previewWindow<?php echo $containerName; ?>").html('');
            $("#metadataWindow<?php echo $containerName; ?>").html('');
            
            if (activeTabId === "mainPreviewTab<?php echo $containerName; ?>") {              
                loadFolderPreview<?php echo $containerName; ?>(nodeId);
            }
            
            if (activeTabId === "mainVersionsTab<?php echo $containerName; ?>") {
                tabPanel.setActiveTab(0);   
            }
            
        }
        
        if (activeTabId === "mainMetadataTab<?php echo $containerName; ?>")   
            loadMetaData<?php echo $containerName; ?>(nodeId);
    });
    
    
    

    var viewport<?php echo $containerName; ?> = new Ext.Panel({
        layout:'border',
        header:false,
        width:'100%',
        height:$("#documentGrid<?php echo $containerName; ?>").height(),
        split:true,
        items: [{
            region: docGridRegion,
            title: '<?php echo __('DocumentGrid'); ?>',
            id:'gridCenter<?php echo $containerName; ?>',
            header:false,
            layout:'fit',
            items: [grid<?php echo $containerName; ?>],
            split: true,
            width:docGridWidth,
            height:docGridHeight
        },{
            border:false,
            xtype: 'tabpanel',
            id:'previewPanel<?php echo $containerName; ?>',
            plain: true,
            width:tabPrevWidth,
            height:tabPrevHeight,
            region: tabPrevRegion,

            tabPosition: tabPrevTabPos,
            activeTab: <?php echo $DefaultTab; ?>,
            
            listeners: {
                'tabchange': function(tabPanel, tab){
                    if (typeof tab !== 'undefined') {
                        var title = tab.title;
                        var tabId = tab.id;
                        //title = title.toLowerCase();  
                        
                        var nodeId = PanelNodeId<?php echo $containerName; ?>;   
                        var nodeType = PanelNodeType<?php echo $containerName; ?>;   
                        if (typeof nodeId !== 'undefined' && nodeId !== null) {
                            switch(tabId) {
                                case "mainMetadataTab<?php echo $containerName; ?>":
                                    loadMetaData<?php echo $containerName; ?>(nodeId);
                                break;
                                case "mainPreviewTab<?php echo $containerName; ?>":
                                    if (nodeType !== "{http://www.alfresco.org/model/content/1.0}folder")    
                                        loadPreview<?php echo $containerName; ?>(nodeId);
                                    else {    
                                        loadFolderPreview<?php echo $containerName; ?>(nodeId);  
                                    }    
                                break;
                                case "mainVersionsTab<?php echo $containerName; ?>":
                                    //$("#versionsContent<?php echo $containerName; ?>").html('');                   
                                    if (nodeType === "{http://www.alfresco.org/model/content/1.0}folder") {
                                        var tabPanel = Ext.getCmp("previewPanel<?php echo $containerName; ?>");  
                                        if (tabPanel)                  
                                            tabPanel.setActiveTab(0);        
                                    }
                                    else {
                                        versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeId}});         
                                    } 
                                break;
                            }
                        }
                    }
                }
            },
            
            items: [{
                title: '<?php echo __('Preview'); ?>',
                id:'mainPreviewTab<?php echo $containerName; ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [previewTab<?php echo $containerName; ?>]
            },
            {
                title: '<?php echo __('Versions'); ?>',
                id:'mainVersionsTab<?php echo $containerName; ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [versionsTab<?php echo $containerName; ?>]
                
            },
            {
                title: '<?php echo __('Metadata'); ?>',
                id:'mainMetadataTab<?php echo $containerName; ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [metadataTab<?php echo $containerName; ?>]
            }]
        }],
        renderTo: 'documentGrid<?php echo $containerName; ?>'
    });
    grid<?php echo $containerName; ?>.render();



    function loadFolderPreview<?php echo $containerName; ?>(nodeId) {
        var folderReader<?php echo $containerName; ?> = new Ext.data.JsonReader({
            idProperty:'nodeId',
            fields: [<?php echo html_entity_decode($fields,ENT_QUOTES); ?>],
            root:'data',
            remoteGroup:true,
            remoteSort: true
        });


        var folderStore<?php echo $containerName; ?> = new Ext.data.GroupingStore({
                reader: folderReader<?php echo $containerName; ?>,
                proxy : new Ext.data.HttpProxy({
                    url: '<?php echo url_for('DataGrid/GridData') ?>',
                    method: 'GET'
                }),
                sortInfo: {field: 'nodeId', direction: 'ASC'}
            });
            
        
            
        var foldergrid<?php echo $containerName; ?> = new Ext.grid.GridPanel({
            loadMask: {msg:'<?php echo __('Loading Documents...'); ?>'},
            layout:'fit',

            ds: folderStore<?php echo $containerName; ?>,
            columns: [<?php echo html_entity_decode($columns,ENT_QUOTES); ?>],
            view: new Ext.grid.GroupingView({
                forceFit:true,
                showGroupName: false,
                enableNoGroups:true,
                enableGroupingMenu:true,
                hideGroupedColumn: false,
                emptyText: '<img src="/images/icons/information.png" align="absmiddle"> <?php echo __('No items to display.'); ?>'
            }),
            
            tbar: [{
                iconCls:'open-alfresco',
                id: 'open-alfresco<?php echo $containerName; ?>',
                text:'<?php echo __('Open Folder in Alfresco'); ?>',
                handler: function(){
                    window.open("<?php echo $ShareSpaceUrl; ?>"+nodeId);    
                },
                scope: this
            }
            ,'-',
            {
                iconCls:'upload-content',
                id:'upload-content<?php echo $containerName; ?>',
                text:'<?php echo __('Upload File(s)'); ?>',
                handler: function(){
                    if(!win<?php echo $containerName; ?>){
                        win<?php echo $containerName; ?> = new Ext.Window({
                            modal:true,
                            applyTo:'upload-window<?php echo $containerName; ?>',
                            layout:'fit',
                            width:500,
                            height:350,
                            closeAction:'hide',
                            plain: true,

                            items: new Ext.Panel({
                                applyTo: 'upload-window-panel<?php echo $containerName; ?>',
                                layout:'fit',
                                border:false
                            }),

                            buttons: [{
                                text: '<?php echo __('Close'); ?>',
                                handler: function() {
                                    win<?php echo $containerName; ?>.hide(this);
                                    foldergrid<?php echo $containerName; ?>.getStore().reload();
                                }
                            }]
                        });
                    }

                    $.ajax({
                        cache: false,  
                        url : "<?php echo url_for('Upload/index') ?>",
                        data: ({'nodeId' : nodeId, 'containerName':'<?php echo $containerName; ?>'}),               
                        
                        success : function (data) {
                            $("#upload-window-panel<?php echo $containerName; ?>").html(data);
                        }    
                    });
                    
                    win<?php echo $containerName; ?>.show();
                },
                scope: this
            },
            {
                iconCls:'create-folder',
                id:'create-folder<?php echo $containerName; ?>',
                text:'<?php echo __('Create Space'); ?>',
                handler: function(){
                    if(!winSpace<?php echo $containerName; ?>){
                        winSpace<?php echo $containerName; ?> = new Ext.Window({
                            modal:true,
                            applyTo:'general-window<?php echo $containerName; ?>',
                            layout:'fit',
                            width:500,
                            height:350,
                            closeAction:'hide',
                            title:'<?php echo __('Create Space'); ?>',
                            plain: true,

                            items: new Ext.Panel({
                                applyTo: 'window-panel<?php echo $containerName; ?>',
                                layout:'fit',
                                border:false
                            }),
                            listeners:{
                                'beforeshow':{
                                    fn:function() {
                                        
                                        $("#general-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
                                        $.ajax({
                                            cache: false,  
                                            url : "<?php echo url_for('FolderActions/index') ?>",
                                            
                                            success : function (data) {
                                                $("#general-window<?php echo $containerName; ?>").unmask();
                                                $("#window-panel<?php echo $containerName; ?>").html(data);
                                            }    
                                        });    
                                    }
                                }
                            },

                            buttons: [{
                                text: '<?php echo __('Save'); ?>',
                                handler: function() {

                                    $.post("<?php echo url_for('FolderActions/CreateSpacePOST') ?>", $("#spaceCreateForm").serialize()+"&nodeId="+nodeId, function(data) {
                                        if (data.success === "true") {
                                            $(".PDFRenderer").hide();  
                                            Ext.MessageBox.show({
                                               title: '<?php echo __('Success'); ?>',
                                               msg: data.message,
                                               buttons: Ext.MessageBox.OK,
                                               icon: Ext.MessageBox.INFO,
                                               fn:showRenderer
                                           });

                                            Ext.getCmp('alfrescoTree').getRootNode().reload();
                                            Ext.getCmp('alfrescoTree').render();
                                            
                                            foldergrid<?php echo $containerName; ?>.getStore().reload();
                                            
                                            winSpace<?php echo $containerName; ?>.hide(this);
                                            $("#window-panel<?php echo $containerName; ?>").html('');
                                            
                                        }
                                        else {
                                            $(".PDFRenderer").hide();  
                                            Ext.MessageBox.show({
                                               title: '<?php echo __('Error'); ?>',
                                               msg: data.message,
                                               buttons: Ext.MessageBox.OK,
                                               icon: Ext.MessageBox.WARNING,
                                               fn:showRenderer
                                           });
                                        }
                                    }, "json");
                                }
                            },
                            {
                                text: '<?php echo __('Close'); ?>',
                                handler: function() {
                                    winSpace<?php echo $containerName; ?>.hide(this);
                                    $("#window-panel<?php echo $containerName; ?>").html('');
                                }
                            }]
                        });
                    }
                    winSpace<?php echo $containerName; ?>.show();
                }
            }],

            viewConfig: {
                forceFit:true
            },        

            collapsible: true,
            animCollapse: true,
            header: false,
            id: 'folderDataGrid<?php echo $containerName; ?>',
            iconCls: 'icon-grid',

            stateId :'folderDocumentGrid-stateid<?php echo $containerName; ?>',
            stateful : true,
            renderTo:'previewWindow<?php echo $containerName; ?>',
            height:400

        });  
               
        foldergrid<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});   
        foldergrid<?php echo $containerName; ?>.render();        
         
        foldergrid<?php echo $containerName; ?>.on('rowclick', function(grid, rowIndex, e) {  
            var folderNodeText = folderStore<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_name;
               
            var folderNodeId = folderStore<?php echo $containerName; ?>.getAt(rowIndex).id;
            var folderType = folderStore<?php echo $containerName; ?>.getAt(rowIndex).data.alfresco_type;
            
            if (folderType !== "{http://www.alfresco.org/model/content/1.0}folder") {
                var tabnodeid = folderNodeId.replace(/-/g,"");
                addTabDynamic('tab-'+tabnodeid,folderNodeText);
                
                $.ajax({
                    cache: false,  
                    url : "<?php echo url_for('DataGrid/detailView') ?>",
                    data: ({'nodeId' : folderNodeId}),

                    success : function (data) {
                        $("#overAll").unmask();
                        $("#tab-"+tabnodeid).html(data);
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo __('Loading'); ?> "+folderNodeText+"...",300);    
                    }  
                });
            }
            else {
                var tabnodeid = folderNodeId.replace(/-/g,"");
                addTabDynamic('tab-'+tabnodeid,folderNodeText);
                 $.ajax({
                     cache: false,  
                    url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid<?php echo $containerName; ?>,

                    success : function (data) {
                        $("#overAll").unmask();
                        $("#tab-"+tabnodeid).html(data);

                        eval("reloadGridData"+tabnodeid+"({params:{'nodeId':folderNodeId}});");
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("<?php echo __('Loading'); ?> "+folderNodeText+"...",300);    
                    } 
                });
            }
        });
    }
    
    function object2string(obj) {
        var output = "";
        for (var i in obj) {
            val = obj[i];
            
            switch (typeof val) {
                case ("object"):
                break;
                case ("string"): 
                    output += i + "=" + val + "&";
                    break;
                default:
                    output += i + "=" + val + "&";      
                break;
            }    
        }
        return output;
    }
    
    function loadPreview<?php echo $containerName; ?>(nodeId) {
        var previewHeight = $("#previewWindow<?php echo $containerName; ?>").height();

        $.ajax({ 
            cache: false,              
            url : "<?php echo url_for('Viewer/index') ?>",
            data: "nodeId="+nodeId+"&height="+previewHeight+"px",
            success : function (data) {
                $("#previewContent<?php echo $containerName; ?>").unmask();
                
                $("#previewWindow<?php echo $containerName; ?>").html(data);
            },         
            beforeSend: function(xhr) { 
                $("#previewWindow<?php echo $containerName; ?>").html('');
                $("#previewContent<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
                  
            }
        });
    }

    function contextMenuFunc<?php echo $containerName; ?>(gridx, index, e) {
        var existingMenu = Ext.getCmp('row-grid-ctx<?php echo $containerName; ?>');
        if (existingMenu !== null && typeof existingMenu !== 'undefined') {
            existingMenu.destroy();
        }
        
        
        var selection = mainGrid<?php echo $containerName; ?>.getSelectionModel().getSelections();
        var allPDF = true;
        if (selection.length > 1) {
            var selectedObjects = [];
            for (var i = 0; i < selection.length; i++) {
                var selected = selection[i];
                var mime = selected.data.alfresco_mimetype;
                var type = selected.data.alfresco_type;
                if (type !== "{http://www.alfresco.org/model/content/1.0}folder")                   
                    var nodeType = "file";
                else
                    var nodeType = "folder";    
                    
                selectedObjects.push({
                    nodeId:selected.data.nodeId,
                    nodeName:selected.data.alfresco_name,
                    type:type,
                    shortType:nodeType,
                    docName:selected.data.alfresco_name,
                    mime:mime
                });
                
                if (nodeType === "folder" || mime !== "application/pdf")
                    allPDF = false;
            }
            

            
            this.menu = new Ext.menu.Menu({
                id:'row-grid-ctx<?php echo $containerName; ?>',
                items: [
                    <?php if ($isClipBoard == false) { ?>
                    {
                        iconCls: 'add-clipboard',
                        text: '<?php echo __('Add to clipboard'); ?>',                           
                        scope:this,
                        handler: function(){
                            for (var i=0; i < selectedObjects.length; ++i) {
                                ClipBoard.addItem(selectedObjects[i].nodeId,selectedObjects[i].docName);                                 
                            }   
                            ClipBoard.reloadClip();
                        }
                    },'-',{
                        iconCls: 'delete-node',
                        text: '<?php echo __('Delete'); ?>',                           
                        scope:this,
                        handler: function(){

                            deleteNodes<?php echo $containerName; ?>(selectedObjects);           
     
                        }
                    }
                    <?php } else { ?>
                    {
                        iconCls: 'remove-clipboard',
                        text: '<?php echo __('Remove from clipboard'); ?>',                           
                        scope:this,
                        handler: function(){
                            for (var i=0; i < selectedObjects.length; ++i) {
                                ClipBoard.removeItem(selectedObjects[i].nodeId);                                 
                            }   
                            ClipBoard.reloadClip();
                        }
                    }
                    <?php } ?> 
                    ,'-',{
                        iconCls: 'add-favorite',
                        text: '<?php echo __('Add to favorites'); ?>',
                        scope:this,
                        handler: function(){
                            for (var i=0; i < selectedObjects.length; ++i) {            
                                addFavorite(selectedObjects[i].nodeId,selectedObjects[i].nodeName,selectedObjects[i].shortType);                                           
                            }   
                        }
                    },'-',{
                        iconCls: 'send-email',
                        text: '<?php echo __('Send as Email'); ?>',
                        scope:this,
                        handler: function(){
                            sendMail<?php echo $containerName; ?>(selectedObjects);
                        }
                    },'-',{
                        iconCls: 'pdf-merge',
                        text: '<?php echo __('PDF Merge'); ?>',
                        scope:this,
                        disabled:(allPDF === true ? false : true),
                        handler: function(){
                            var nodes = [];
                            for (var i=0; i < selectedObjects.length; ++i) { 
                                if (selectedObjects[i].shortType === "file" && selectedObjects[i].mime === "application/pdf") 
                                    nodes.push(selectedObjects[i].nodeId);  
                            }
                            
                            if (nodes.length > 0) {
                                var jsonNodes = $.toJSON(nodes);
                                var dlUrl = '<?php echo url_for('NodeActions/PDFMerge') ?>?nodes='+jsonNodes;
                                window.open(dlUrl);
                            }
                        }
                    }        
                ]
            });    
        }
        else {
            var selected = store<?php echo $containerName; ?>.getAt(index);
            
            var DocName = selected.data.alfresco_name;
            var nodeId = selected.data.nodeId;
            var nodeName = selected.data.alfresco_name;
            var type = selected.data.alfresco_type; 
            
            var isFolder = (type === "{http://www.alfresco.org/model/content/1.0}folder" ? true : false );
            
            
            var MimeType = selected.data.alfresco_mimetype;
            // RIGHTS 
            var editRights = selected.data.alfresco_perm_edit;
            var delRights = selected.data.alfresco_perm_delete;
            var cancelCheckoutRights = selected.data.alfresco_perm_cancel_checkout;
            var createRights = selected.data.alfresco_perm_create;
            var hasRights = selected.data.alfresco_perm_permissions;
            
            // CHECKOUT LOGIC 
            var isWorkingCopy = selected.data.alfresco_isWorkingCopy;
            var isCheckedOut = selected.data.alfresco_isCheckedOut;
            var originalId = selected.data.alfresco_originalId;
            var workingCopyId = selected.data.alfresco_workingCopyId;  
            
            // BUTTONS
            var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
            var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
            var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');
            var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');
            var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');   
            
       
                this.menu = new Ext.menu.Menu({
                    id:'row-grid-ctx<?php echo $containerName; ?>',
                    items: [{
                        iconCls: 'preview-tab',
                        text: '<?php echo __('Preview in new tab'); ?>',                           
                        scope:this,
                        handler: function(){
                            //var nodeId = store<?php echo $containerName; ?>.getAt(index).data.nodeId;   
                            //var DocName = store<?php echo $containerName; ?>.getAt(index).data.name;          
                            
                            if (isFolder) {
                                openFolder<?php echo $containerName; ?>(nodeId,nodeName);
                            }
                            else {        
                                var autoLoad = {url: '<?php echo url_for('DataGrid/detailView') ?>', scripts: true, scope:this, params: "nodeId="+nodeId};

                                if (!tabExists('tab-preview-'+nodeId))
                                    addTabDynamicLoad('tab-preview-'+nodeId,DocName,autoLoad);
                                setActive('tab-preview-'+nodeId);
                            }
                        }
                    },'-',
                    <?php if ($isClipBoard == false) { ?>
                    {
                        iconCls: 'add-clipboard',
                        text: '<?php echo __('Add to clipboard'); ?>',          
                        disabled:(editRights === true ? false : true),                                                               
                        scope:this,
                        handler: function(){        
                            ClipBoard.addItem(nodeId,DocName);  
                            ClipBoard.reloadClip();                       
                        }
                    }
                    <?php } else { ?>
                    {
                        iconCls: 'remove-clipboard',
                        text: '<?php echo __('Remove from clipboard'); ?>',                           
                        disabled:(editRights === true ? false : true),                                                               
                        scope:this,
                        handler: function(){        
                            ClipBoard.removeItem(nodeId);           
                            ClipBoard.reloadClip();              
                        }
                    }
                    <?php } ?> 
                    ,{
                        iconCls: 'view-metadata',
                        text: '<?php echo __('Edit Metadata'); ?>',     
                        disabled:(editRights === true ? false : true),                                              
                        scope:this,
                        handler: function(){
                            editMetadata<?php echo $containerName; ?>(nodeId,nodeName);
                        }
                    },{
                        iconCls: 'specify-type',
                        text: '<?php echo __('Specify type'); ?>',     
                        disabled:(editRights === true ? false : true),                                              
                        scope:this,
                        handler: function(){
                            specifyType<?php echo $containerName; ?>(nodeId);
                        }
                    },{
                        iconCls: 'manage-aspects',
                        text: '<?php echo __('Manage aspects'); ?>',     
                        disabled:(editRights === true ? false : true),                                              
                        scope:this,
                        handler: function(){
                            manageAspects<?php echo $containerName; ?>(nodeId,nodeName);
                        }
                    },
                    {
                        iconCls:'download-node',
                        text:'<?php echo __('Download'); ?>',
                        disabled:isFolder,
                        handler: function() {
                            var dlUrl = '<?php echo url_for('NodeActions/Download') ?>?nodeId='+nodeId;
                            window.open(dlUrl);
                        }
                    },
                    {
                        iconCls:'quick-aspects',
                        text:'<?php echo __('Quick add aspect'); ?>',
                        disabled:(editRights === true ? false : true), 
                        menu: {
                            items:[{
                                iconCls:'quick-aspect-tag',
                                text: '<?php echo __('Taggable'); ?>',
                                group: 'quickAspects',
                                handler: function() {
                                    quickAddAspect<?php echo $containerName; ?>(nodeId,"cm:taggable");
                                }
                            }, {
                                iconCls:'quick-aspect-version',
                                text: '<?php echo __('Versionable'); ?>',
                                group: 'quickAspects',
                                handler: function() {
                                    quickAddAspect<?php echo $containerName; ?>(nodeId,"cm:versionable");
                                }
                            }, {
                                iconCls:'quick-aspect-category',
                                text: '<?php echo __('Classifiable'); ?>',
                                group: 'quickAspects',
                                handler: function() {
                                    quickAddAspect<?php echo $containerName; ?>(nodeId,"cm:generalclassifiable");
                                }
                            }]
                        }                                    
                    },'-',
                    <?php if ($isClipBoard == false) { ?>
                    
                    {
                        iconCls: 'delete-node',
                        text: '<?php echo __('Delete'); ?>',   
                        disabled:(delRights === true ? false : true),                        
                        scope:this,
                        handler: function(){
                            if (type !== "{http://www.alfresco.org/model/content/1.0}folder")                   
                                var nodeType = "file";
                            else
                                var nodeType = "folder";
                               
                            deleteNode<?php echo $containerName; ?>(nodeId,nodeName,nodeType);
                            
                        }
                    },'-',
                    <?php } ?>
                        {
                            iconCls:(isWorkingCopy === true || isCheckedOut === true ? 'checkin-node' : 'checkout-node'),
                            text:(isWorkingCopy === true || isCheckedOut === true ? '<?php echo __('Checkin'); ?>' : '<?php echo __('Checkout'); ?>'),
                            disabled:isFolder,
                            handler: function(){
                                if (isWorkingCopy === true || isCheckedOut === true) {
                                    var tempid = nodeId;
                                    if (isCheckedOut === true) {
                                        tempid = workingCopyId;
                                    }

                                    checkIn<?php echo $containerName; ?>(tempid,MimeType);        
                                }
                                else {
                                    checkOut<?php echo $containerName; ?>(nodeId,MimeType);    
                                }  
                            },
                            scope: this
                        },'-',{
                        iconCls: 'add-favorite',
                        text: '<?php echo __('Add to favorites'); ?>',
                        scope:this,
                        handler: function(){
                            if (type !== "{http://www.alfresco.org/model/content/1.0}folder")                   
                                var nodeType = "file";
                            else
                                var nodeType = "folder";
                            
                            addFavorite(nodeId,nodeName,nodeType);                         
                        }},'-',{
                        iconCls: 'send-email',
                        text: '<?php echo __('Send as Email'); ?>',
                        scope:this,
                        disabled:(type !== "{http://www.alfresco.org/model/content/1.0}folder" ? false : true),                        
                        handler: function(){
                            var mailNodes = [{nodeId:nodeId,nodeName:nodeName,docName:nodeName,shortType:'file'}];
                            sendMail<?php echo $containerName; ?>(mailNodes);
                        }},'-',{
                        iconCls: 'view-alfresco',
                        text: '<?php echo __('View Details in Alfresco'); ?>',
                        scope:this,
                        handler: function(){
                            window.open('<?php echo $ShareUrl; ?>'+nodeId);                         
                        }
                    }]
                });
        }
            e.stopEvent();
            
            this.menu.showAt(e.getXY());
    } 
});


function loadSitesAction(item) {
    switch (item.text) {
        case "Calendar":
            $.ajax({
                cache: false,  
                url : "<?php echo url_for('Sites/Calendar') ?>",
                
                success : function (data) {
                    $("#sitesContent").unmask();
                
                    $("#sitesWindow").html(data);
                },         
                beforeSend: function(xhr) { 
                    $("#sitesWindow").html('');
                    $("#sitesContent").mask("<?php echo __('Loading...'); ?>",300);  
                      
                }    
            });   
        break;
        default:
        break;
    }
}



function mySize<?php echo $containerName; ?>() {   
    $("#documentGrid<?php echo $containerName; ?>").css({'height': (($(window).height()) -98)+'px'});
    return $("#documentGrid<?php echo $containerName; ?>").height();
}



function getHalfSize() {
    if ($.browser.msie) {
        var size = mySize<?php echo $containerName; ?>();
        //return $("#documentGrid<?php echo $containerName; ?>").parent().parent().height();
    }
    return (($("#documentGrid<?php echo $containerName; ?>").height() / 2)-4);
}

function refreshGrid<?php echo $containerName; ?>() {
    if (lastParams<?php echo $containerName; ?> !== null)
        mainGrid<?php echo $containerName; ?>.store.load(lastParams<?php echo $containerName; ?>);
}

function loadNewColumns<?php echo $containerName; ?>(combo,record,index) {
    if (typeof record !== 'undefined') {
        var ColumnsetId = record.id;
        var ColumnName = record.data.name;                              
        var params = lastParams<?php echo $containerName; ?>;
        params.params.columnsetid = ColumnsetId;  

        var date = new Date();
        //var dateString = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
        var dateString = date.getDate()+"/"+date.getDay()+"/"+date.getFullYear()+" "+date.getHours()+":"+date.getMinutes();
        var timestamp = Date.parse(dateString);
        
        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            var activeTab = tabPanel.getActiveTab();  


            Registry.getInstance().set("ColumnsetId",ColumnsetId);
            UserColumnsetId = ColumnsetId;
            Registry.getInstance().save();
            
            $.ajax({
                cache: false,  
                url: "<?php echo url_for('DataGrid/index') ?>?containerName=<?php echo $containerName ?>&addContainer=<?php echo $addContainer; ?>&columnsetid="+ColumnsetId,
                
                success : function (data) {
                    $("#overAll").unmask();
                    $("#"+activeTab.id).html(data);
                    //grid.store.load({params:{'nodeId':node.id}});  

                    eval("reloadGridData<?php echo $containerName; ?>({'params':params.params});");                      
                },
                beforeSend: function(req) {
                    $("#overAll").mask("<?php echo __('Loading Results...'); ?>",300);    
                } 
                
            });  
            
            
        }       
     }    
     
}


function reloadGridData<?php echo $containerName; ?>(params) {
    if (typeof params.params.columnsetid !== 'undefined') {
        currentColumnsetid<?php echo $containerName; ?> = params.params.columnsetid;
    }
    else {
        var columnsetid = Registry.getInstance().get("ColumnsetId");
        currentColumnsetid<?php echo $containerName; ?> = columnsetid;
        params.params.columnsetid = columnsetid;
    }
    
    mainGrid<?php echo $containerName; ?>.store.load(params);
    lastParams<?php echo $containerName; ?> = params;

    
    
    if (params.params.nodeId !== '') {

        if (params.params.nodeId === 'root')
            detailUrl<?php echo $containerName; ?> = '<?php echo $CompanyHomeUrl; ?>';
        else {
            detailUrl<?php echo $containerName; ?> = orgDetailUrl<?php echo $containerName; ?>+params.params.nodeId;
        }
        
        currentNodeId<?php echo $containerName; ?> = params.params.nodeId;    
    }
    $("#previewWindow<?php echo $containerName; ?>").html('');
    
    if(typeof changeUploadId === 'function') { 
        changeUploadId(params.params.nodeId);
    }
    
    if (params.params.nodeId === '' || typeof params.params.nodeId === 'undefined') {        
        var openAlfresco = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('open-alfresco<?php echo $containerName; ?>');
        if (typeof openAlfresco !== 'undefined') {
            openAlfresco.disable();    
            //openAlfresco.hide();
        }
        
        var uploadContent = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('upload-content<?php echo $containerName; ?>');
        if (typeof uploadContent !== 'undefined') {
            uploadContent.disable();   
            //uploadContent.hide();
        }
            
        var createFolder = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('create-folder<?php echo $containerName; ?>');
        if (typeof createFolder !== 'undefined') {
            createFolder.disable();   
            //createFolder.hide(); 
        }
            
        var pasteCopyClipboard = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('pastecopy-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCopyClipboard !== 'undefined') {
            pasteCopyClipboard.disable(); 
            //pasteCopyClipboard.hide();
        }
            
        var pasteCutClipboard = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('pastecut-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCutClipboard !== 'undefined') {
            pasteCutClipboard.disable();   
            //pasteCutClipboard.hide();
        }
    }
    else {
        var openAlfresco = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('open-alfresco<?php echo $containerName; ?>');
        if (typeof openAlfresco !== 'undefined')
            openAlfresco.enable();    
        
        var uploadContent = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('upload-content<?php echo $containerName; ?>');
        if (typeof uploadContent !== 'undefined')
            uploadContent.enable();   
            
        var createFolder = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('create-folder<?php echo $containerName; ?>');
        if (typeof createFolder !== 'undefined')
            createFolder.enable();   
            
        var pasteCopyClipboard = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('pastecopy-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCopyClipboard !== 'undefined')
            pasteCopyClipboard.enable();   
            
        var pasteCutClipboard = mainGrid<?php echo $containerName; ?>.topToolbar.items.get('pastecut-clipboard<?php echo $containerName; ?>');
        if (typeof pasteCutClipboard !== 'undefined')
            pasteCutClipboard.enable();       
    }
}


function pasteClipBoard<?php echo $containerName; ?>(type) {
    if (ClipBoard.items.length > 0) {
        var clipBoardItems = $.JSON.encode(ClipBoard.items);
        var currentNode = currentNodeId<?php echo $containerName; ?>;
        $.ajax({
            cache: false,  
            url : "<?php echo url_for('DataGrid/PasteClipboard') ?>",
            data: ({'clipboardItems' : clipBoardItems, 'actionType' : type, 'destNodeId' : currentNode}),

            
            success : function (data) {
                $("#overAll").unmask();
                
                jsonData = $.JSON.decode(data);
                var totalCount = jsonData.totalResults;                
                var successCount = jsonData.successCount;
                var failureCount = jsonData.failureCount;
                if(jsonData.success === true) {
                    $(".PDFRenderer").hide();  
                    Ext.MessageBox.show({
                       title: '<?php echo __('Successfully pasted!'); ?>',
                       msg: '<?php echo __('Successfully pasted'); ?> '+totalCount+' <?php echo __('node(s)'); ?>!',
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.INFO,
                       fn:showRenderer
                   }); 
                   
                   if (type=="cut") {
                       ClipBoard.clearItems();
                   }
                }
                else {
                    $(".PDFRenderer").hide();  
                    Ext.MessageBox.show({
                       title: '<?php echo __('Pasting was not successful!'); ?>',
                       msg: successCount+' <?php echo __('of'); ?>  '+totalCount+' <?php echo __('node(s) pasted to the destination folder'); ?>!',
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.WARNING,
                       fn:showRenderer          
                   });    
                }
                
                mainGrid<?php echo $containerName; ?>.getStore().reload();  
            },
            beforeSend: function(req) {
                $("#overAll").mask("<?php echo __('Pasting documents'); ?>",300);    
            } 
        });    
    }
}


function checkOut<?php echo $containerName; ?>(nodeId,MimeType) {
    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Metadata/Checkout') ?>?nodeId="+nodeId,
        
        
        success : function (data) {
            data = $.evalJSON(data);                  
            if (data.success === true) { 
                var workingCopyId = data.workingCopyId;
   
                Ext.MessageBox.alert('<?php echo __('Checkout'); ?>', '<?php echo __('Document checked out successfully.'); ?>');
                
                mainGrid<?php echo $containerName; ?>.store.on('load', function() {

                    var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",workingCopyId);
                    if (rowIndex !== -1) {
                        mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                        mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);
                        var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');         
                        var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');  
                        var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');  
                        
                        var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
                        var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
                        var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');
                              
                        checkoutBtn.setText("<?php echo __('Checkin'); ?>");
                        checkoutBtn.setIconClass("checkin-node");
                        
                        <?php if ($OnlineEditing === "zoho") { ?>
                        if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                            checkoutZohoBtn.setText("<?php echo __('Edit in Zoho Writer'); ?>");   
                            checkoutZohoBtn.enable();    
                            checkoutZohoBtn.setVisible(true);
                        }
                        else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {  
                            checkoutZohoBtn.setText("<?php echo __('Edit in Zoho Sheet'); ?>");     
                            checkoutZohoBtn.enable();  
                            checkoutZohoBtn.setVisible(true);
                        }
                        else {         
                            checkoutZohoBtn.disable();  
                            checkoutZohoBtn.setVisible(false);
                        }
                        <?php } ?>
                        
                        cancelCheckoutBtn.enable();  
                        cancelCheckoutBtn.setVisible(true);
                        
                        PanelNodeIsCheckedOut<?php echo $containerName; ?> = true;
                        PanelNodeCheckedOutId<?php echo $containerName; ?> = workingCopyId; 
                    }
                }, 
                this, 
                {
                single: true
                });
                
                mainGrid<?php echo $containerName; ?>.getStore().reload();   
                
                
            }
            else
                Ext.MessageBox.alert('<?php echo __('Error'); ?>', '<?php echo __('An unknown problem occured at the check out process.'); ?>');                                                                                            
        },
        beforeSend: function(req) {
              
        } 
    });   
}   

function checkInWindow<?php echo $containerName; ?>(data) {
    showNewVersionWindow<?php echo $containerName; ?>(data,"<?php echo __('Checkin'); ?>","<?php echo __('Save'); ?>","checkInVersion<?php echo $containerName; ?>","");
}

function checkInVersion<?php echo $containerName; ?>(data) {
    checkIn<?php echo $containerName; ?>(data.nodeId,data.mime,true,data.note,data.versionchange);
}

function checkIn<?php echo $containerName; ?>(nodeId,MimeType,msgbox,note,versionchange) {
    if (typeof msgbox === 'undefined' || msgbox === null)
        msgbox = true;
        
    if (typeof note === 'undefined' || note === null)
        note = "";
        
    if (typeof versionchange === 'undefined' || versionchange === null)
        versionchange = "minor";
      
    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Metadata/Checkin') ?>?nodeId="+nodeId,
        data: "note="+note+"&versionchange="+versionchange,
        
        success : function (data) {
            data = $.evalJSON(data);        
            if (data.success = true) {           
                var origNodeId = data.origNodeId;
                
                if (msgbox===true)
                    Ext.MessageBox.alert('<?php echo __('Checkin'); ?>', '<?php echo __('Document checked in successfully.'); ?>');
                
                mainGrid<?php echo $containerName; ?>.getStore().reload();  
                
                var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",origNodeId);
                if (rowIndex !== -1) {
                    mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                    mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);
                    
                    var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');         
                    var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>'); 
                    var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');   
                    
                    var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
                    var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
                    var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');
                    
                    checkoutBtn.setText("<?php echo __('Checkout'); ?>");
                    checkoutBtn.setIconClass("checkout-node");
                    
                    <?php if ($OnlineEditing === "zoho") { ?>
                    if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                        checkoutZohoBtn.setText("<?php echo __('Checkout in Zoho Writer'); ?>");   
                        checkoutZohoBtn.enable();    
                        checkoutZohoBtn.setVisible(true);
                    }
                    else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {  
                        checkoutZohoBtn.setText("<?php echo __('Checkout in Zoho Sheet'); ?>");     
                        checkoutZohoBtn.enable();  
                        checkoutZohoBtn.setVisible(true);
                    }
                    else {         
                        checkoutZohoBtn.disable();  
                        checkoutZohoBtn.setVisible(false);
                    }
                    <?php } ?>

                    
                    cancelCheckoutBtn.disable();  
                    cancelCheckoutBtn.setVisible(false);
                    
                    editMetaDataBtn.enable();
                    manageAspectsBtn.enable();
                    specifyTypeBtn.enable();   
                    
                    PanelNodeIsCheckedOut<?php echo $containerName; ?> = false;
                } 
            }  
            else
                Ext.MessageBox.alert('<?php echo __('Error'); ?>', '<?php echo __('An unknown problem occured at the check in process.'); ?>');                                                         
        },
        beforeSend: function(req) {
            if (winNewVersion<?php echo $containerName; ?> != null)
                winNewVersion<?php echo $containerName; ?>.hide(this); 
        } 
    });  
}      

function cancelCheckout<?php echo $containerName; ?>(nodeId,origNodeId,MimeType,msgbox) {
    if (typeof msgbox === 'undefined' || msgbox === null)
        msgbox = true;
        
    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Metadata/CancelCheckout') ?>?nodeId="+nodeId,
        
        success : function (data) {
            data = $.evalJSON(data);        
            if (data.success = true) {           
                
                if (msgbox===true)
                    Ext.MessageBox.alert('<?php echo __('Cancel Checkout'); ?>', '<?php echo __('Successfully canceled.'); ?>');
                
                mainGrid<?php echo $containerName; ?>.getStore().reload();  
                
                var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",origNodeId);
                if (rowIndex !== -1) {
                    mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                    mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);
                    
                    var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');         
                    var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');  
                    var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');  
                    
                    var editMetaDataBtn = Ext.getCmp('editMetadata<?php echo $containerName; ?>');
                    var manageAspectsBtn = Ext.getCmp('manageAspects<?php echo $containerName; ?>');
                    var specifyTypeBtn = Ext.getCmp('specifyType<?php echo $containerName; ?>');
                    
                    checkoutBtn.setText("<?php echo __('Checkout'); ?>");
                    checkoutBtn.setIconClass("checkout-node");
                    
                    <?php if ($OnlineEditing === "zoho") { ?>
                    if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                        checkoutZohoBtn.setText("<?php echo __('Checkout in Zoho Writer'); ?>");   
                        checkoutZohoBtn.enable();    
                        checkoutZohoBtn.setVisible(true);
                    }
                    else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {  
                        checkoutZohoBtn.setText("<?php echo __('Checkout in Zoho Sheet'); ?>");     
                        checkoutZohoBtn.enable();  
                        checkoutZohoBtn.setVisible(true);
                    }
                    else {         
                        checkoutZohoBtn.disable();  
                        checkoutZohoBtn.setVisible(false);
                    }
                    <?php } ?>
                    
                    cancelCheckoutBtn.disable();  
                    cancelCheckoutBtn.setVisible(false);
                    
                    editMetaDataBtn.enable();
                    manageAspectsBtn.enable();
                    specifyTypeBtn.enable();   
                    
                    PanelNodeIsCheckedOut<?php echo $containerName; ?> = false;
                } 
            }  
            else
                Ext.MessageBox.alert('<?php echo __('Error'); ?>', '<?php echo __('An unknown problem occured at the check in process.'); ?>');                                                         
        },
        beforeSend: function(req) {
              
        } 
    });  
}      

function checkOutZoho<?php echo $containerName; ?>(nodeId,MimeType) {
    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Metadata/Checkout') ?>?nodeId="+nodeId,
        
        
        success : function (data) {
            data = $.evalJSON(data);
            if (data.success === true) {
                var workingCopyId = data.workingCopyId;   
                
                mainGrid<?php echo $containerName; ?>.store.on('load', function() {

                    var rowIndex = mainGrid<?php echo $containerName; ?>.getStore().find("nodeId",workingCopyId);
                    if (rowIndex !== -1) {
                        mainGrid<?php echo $containerName; ?>.getSelectionModel().selectRow(rowIndex);
                        mainGrid<?php echo $containerName; ?>.fireEvent('rowclick', mainGrid<?php echo $containerName; ?>, rowIndex);
                        var checkoutBtn = Ext.getCmp('checkout<?php echo $containerName; ?>');         
                        var checkoutZohoBtn = Ext.getCmp('checkoutZoho<?php echo $containerName; ?>');  
                        
                        var cancelCheckoutBtn = Ext.getCmp('cancel-checkout<?php echo $containerName; ?>');  
                              
                        checkoutBtn.setText("<?php echo __('Checkin'); ?>");
                        checkoutBtn.setIconClass("checkin-node");
  
                        if (jQuery.inArray(MimeType,ZohoMimeDocs)>=0) {
                            checkoutZohoBtn.setText("<?php echo __('Edit in Zoho Writer'); ?>");   
                            checkoutZohoBtn.enable();    
                            checkoutZohoBtn.setVisible(true);
                        }
                        else if (jQuery.inArray(MimeType,ZohoMimeSheet)>=0) {  
                            checkoutZohoBtn.setText("<?php echo __('Edit in Zoho Sheet'); ?>");     
                            checkoutZohoBtn.enable();  
                            checkoutZohoBtn.setVisible(true);
                        }
                        else {         
                            checkoutZohoBtn.disable();  
                            checkoutZohoBtn.setVisible(false);
                        } 
                        
                        cancelCheckoutBtn.enable();  
                        cancelCheckoutBtn.setVisible(true);
                        

                        PanelNodeIsCheckedOut<?php echo $containerName; ?> = true;
                        PanelNodeCheckedOutId<?php echo $containerName; ?> = workingCopyId;            
                    }
                }, 
                this, 
                {
                single: true
                });
                
                $.ajax({
                    cache: false,  
                    url : "<?php echo url_for('Zoho/ZohoUpload') ?>?nodeId="+workingCopyId,
                    
                    
                    success : function (dataZoho) {    
                        $("#overAll").unmask();
                        dataZoho = $.evalJSON(dataZoho);
                        var zohoUrl = dataZoho.URL;        
                        var success = dataZoho.RESULT;
                        var warning = dataZoho.WARNING;
                        if (success === true || success === "TRUE") {
                            mainGrid<?php echo $containerName; ?>.getStore().reload();  
                            if (Registry.getInstance().get("ArrangeList")=="horizontal") {
                                window.open(zohoUrl);      
                            }
                            else {
                                var previewPanel = Ext.getCmp('previewPanel<?php echo $containerName; ?>');
                                if (previewPanel) {
                                    previewPanel.add({
                                        title: "Zoho",
                                        closable:true,
                                        items: [ new Ext.ux.IFrameComponent({ id: 'zohoWriter<?php echo $containerName; ?>', url: zohoUrl, width:'100%', height:"100%" }) ],
                                        tbar:[{
                                            iconCls:'open-window',
                                            id: 'zohoWriterOpenBtn<?php echo $containerName; ?>',
                                            text:'<?php echo __('Open in new Window'); ?>',
                                            handler: function(){
                                                window.open(zohoUrl);    
                                            },
                                            scope: this
                                        }]
                                    }).show();
                                }
                            }
                        }  
                        else {
                            $("#overAll").unmask();
                            Ext.MessageBox.show({
                               title: '<?php echo __('Upload to Zoho Failed!'); ?>',
                               msg: warning,
                               buttons: Ext.MessageBox.OK,
                               icon: Ext.MessageBox.ERROR
                             });
                            cancelCheckout<?php echo $containerName; ?>(workingCopyId,nodeId,MimeType,false);
                        }                                                      
                    },
                    beforeSend: function(req) {
                          
                    } 
                });      
            }
            else {
                Ext.MessageBox.alert('<?php echo __('Error'); ?>', '<?php echo __('An unknown problem occured at the check out process.'); ?>');                                                                                                
            }                             
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo __('Checkout to Zoho'); ?>",300);          
        } 
    });   
}  

function editInZoho<?php echo $containerName; ?>(nodeId,MimeType) {
    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Zoho/ZohoUpload') ?>?nodeId="+nodeId,
        
        
        success : function (dataZoho) {
            $("#overAll").unmask();
            dataZoho = $.evalJSON(dataZoho);
            var zohoUrl = dataZoho.URL;        
            var success = dataZoho.RESULT;
            var warning = dataZoho.WARNING;
            if (success === true || success === "TRUE") {
                mainGrid<?php echo $containerName; ?>.getStore().reload();  
                if (Registry.getInstance().get("ArrangeList")=="horizontal") {
                    window.open(zohoUrl);      
                }
                else {
                    var previewPanel = Ext.getCmp('previewPanel<?php echo $containerName; ?>');
                    if (previewPanel) {
                        previewPanel.add({
                            title: "Zoho Writer",
                            closable:true,
                            items: [ new Ext.ux.IFrameComponent({ id: 'zohoWriter<?php echo $containerName; ?>', url: zohoUrl, width:'100%', height:"100%" }) ],
                            tbar:[{
                                iconCls:'open-window',
                                id: 'zohoWriterOpenBtn<?php echo $containerName; ?>',
                                text:'<?php echo __('Open in new Window'); ?>',
                                handler: function(){
                                    window.open(zohoUrl);    
                                },
                                scope: this
                            }]
                        }).show();
                    }
                }
            }
            else {
                Ext.MessageBox.show({
                   title: '<?php echo __('Upload to Zoho Failed!'); ?>',
                   msg: warning,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                 });
            }                            
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo __('Edit in Zoho Writer'); ?>",300);          
        } 
    });                                   
}        



function editMetadata<?php echo $containerName; ?>(nodeId,nodeName) {

    var tabnodeid = nodeId.replace(/-/g,"");              
    addTabDynamic('metadatatab-'+tabnodeid,"<?php echo __('Edit Metadata:'); ?> "+nodeName);
                    
    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Metadata/index') ?>",
        data: ({'nodeId' : nodeId}),

        
        success : function (data) {
            $("#overAll").unmask();
            $("#metadatatab-"+tabnodeid).html(data);
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo __('Loading'); ?> "+nodeName+"...",300);    
        }  
    });
}

function loadMetaData<?php echo $containerName; ?>(nodeId) {
    if (nodeId !== null & typeof nodeId !== 'undefined') {    
        
        var metaHeight = $("#metadataWindow<?php echo $containerName; ?>").height();      
        if ($.browser.msie) {
            $("#metadataWindow<?php echo $containerName; ?>").html('');
            metaHeight = $("#metadataWindow<?php echo $containerName; ?>").height();      
        }

        $.ajax({     
            cache: false,          
            url : "<?php echo url_for('Metadata/view') ?>",
            data: "nodeId="+nodeId+"&containerName=<?php echo $addContainer; ?>&height="+metaHeight,
            success : function (data) {
                $("#metadataContent<?php echo $containerName; ?>").unmask();
                
                $("#metadataWindow<?php echo $containerName; ?>").html(data);
            },         
            beforeSend: function(xhr) { 
                $("#metadataWindow<?php echo $containerName; ?>").html('');
                $("#metadataContent<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
                  
            }
        });
    }
}

//function fillAspectsWindow<?php echo $containerName; ?>(myNodeId) {
function fillAspectsWindow<?php echo $containerName; ?>(myNodeId) {

    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Metadata/addAspects') ?>",
        data: 'nodeId='+myNodeId,
        
        success : function (data) {
            $("#aspects-window<?php echo $containerName; ?>").unmask();
            $("#aspects-window-panel<?php echo $containerName; ?>").html(data);
        },
        beforeSend : function() {

            $("#aspects-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
            $("#aspects-window-panel<?php echo $containerName; ?>").html('');                                   
        }  
    });       
}


function quickAddAspect<?php echo $containerName; ?>(myNodeId,aspect) {
    var postData = "nodeId="+myNodeId+"&aspects="+aspect; 
    $.post("<?php echo url_for('Metadata/SaveAspects') ?>", postData, function(data) {
        var succes = data.success;
        var nodeId = data.nodeId;

        if (PanelNodeId<?php echo $containerName; ?> == nodeId)
            loadMetaData<?php echo $containerName; ?>(nodeId);       
    }, "json");
}            

var winAspects<?php echo $containerName; ?> = null;

function manageAspects<?php echo $containerName; ?>(myNodeId) {

    if (myNodeId !== null & typeof myNodeId !== 'undefined') {
        if(!winAspects<?php echo $containerName; ?>) {
            winAspects<?php echo $containerName; ?> = new Ext.Window({
                modal:true,
                applyTo:'aspects-window<?php echo $containerName; ?>',
                layout:'fit',
                width:516,
                height:417,
                closeAction:'hide',
                title:'<?php echo __('Add Aspect'); ?>',
                plain: true, 
                //id:'aspects-window<?php echo $containerName; ?>',
                
                items: new Ext.Panel({
                    applyTo: 'aspects-window-panel<?php echo $containerName; ?>',
                    layout:'fit',
                    border:false
                }),
                listeners:{
                    'beforeshow':{
                        fn:function() {
                            $(".PDFRenderer").hide();
                        }
                    },
                    'hide': {
                        fn:function() {
                            $(".PDFRenderer").show();
                                
                            $("#aspects-window-panel<?php echo $containerName; ?>").html('');                        
                        }
                    }
                },

                buttons: [{
                    text: '<?php echo __('Save'); ?>',
                    handler: function() {
                        var postData = getSelectedAspects();
                        
                        $("#aspects-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);   
                        $.post("<?php echo url_for('Metadata/SaveAspects') ?>", postData, function(data) {
                            var succes = data.success;
                            var nodeId = data.nodeId;
                            
                            loadMetaData<?php echo $containerName; ?>(nodeId);

                            $("#aspects-window<?php echo $containerName; ?>").unmask();            
                        }, "json");
                        
                        winAspects<?php echo $containerName; ?>.hide(this);  

                    }
                },
                {
                    text: '<?php echo __('Close'); ?>',
                    handler: function() {      
                        $("#aspects-window-panel<?php echo $containerName; ?>").html(''); 
                        winAspects<?php echo $containerName; ?>.hide(this);                       
                    }
                }]
            });

        }
        else {
                
        }

        fillAspectsWindow<?php echo $containerName; ?>(myNodeId);
        winAspects<?php echo $containerName; ?>.show();
              
    }
}

var winVersionLookup<?php echo $containerName; ?> = null;
function versionLookup<?php echo $containerName; ?>(data) {
    if (versionStore<?php echo $containerName; ?> !== null) {
        if (versionStore<?php echo $containerName; ?>.getCount() > 0) {
            var rowNode = versionStore<?php echo $containerName; ?>.getAt(0);

            var versionLookupTpl<?php echo $containerName; ?> = new Ext.Template('<div><div style="float:left;width:40%;"><label><b>Version:</b></label> <i>{version}</i></div><div style="float:left;width:40%;"><label><b><?php echo __('Date:'); ?></b></label> <i>{dateFormat}</i></div><div style="float:left;width:40%;"><label><b><?php echo __('Author:'); ?></b></label> <i>{author}</i></div><div style="float:left;"><label><b><?php echo __('Note:'); ?></b></label></div><div style="float:left;">&nbsp;<i>{description}</i></div></div>');
            var versionLookupHtml<?php echo $containerName; ?> = versionLookupTpl<?php echo $containerName; ?>.apply(rowNode.data);

            
            winVersionLookup<?php echo $containerName; ?> = new Ext.Window({
                modal:true,
                id:'version-lookup-window<?php echo $containerName; ?>',
                layout:'fit',
                width:650,
                height:630,
                boxMinWidth:650,
                boxMinHeight:630,
                boxMaxHeight:630,
                closeAction:'close',
                title:"<?php echo __('Detailed Version Information'); ?>",
                plain: true, 

                items: new Ext.Panel({
                    //id:'version-lookup-window-panel<?php echo $containerName; ?>',
                    layout:'hbox',
                    height:'100%',
                    layoutConfig: {
                        align : 'stretch',
                        pack  : 'start'
                    },
                    bodyStyle:'background-color:#e0e8f6;',
                    border:false,
                    items: [
                        {
                            width:180,
                            border:false,
                            height:'100%',
                            bodyStyle:'background-color:#fff;',
                            items:[new Ext.list.ListView({
                                store: versionStore<?php echo $containerName; ?>,
                                header:false,
                                multiSelect: false,
                                singleSelect: true,
                                height:562,
                                id:'version-lookup-view<?php echo $containerName; ?>',
                                loadingText: '<?php echo __('Loading...'); ?>',
                                emptyText: '<span style="font-size:12px;"><img src="/images/icons/information.png" align="absmiddle"> <?php echo __('This document has no version history.'); ?></span>',
                                reserveScrollOffset: true,

                                columns: [{
                                    header: '<?php echo __('Version'); ?>',
                                    dataIndex: 'version',
                                    width: .40
                                },
                                {
                                    header: '<?php echo __('Date'); ?>',
                                    xtype: 'datecolumn',
                                    format: '<?php echo $DateFormat; ?> <?php echo $TimeFormat; ?>',
                                    dataIndex: 'date',
                                    width: .60
                                }],
                                
                                listeners: {
                                    selectionchange: function(dataview, node) {
                                        
                                    },

                                    click: {
                                        fn: function() {
                                            var listView = Ext.getCmp('version-lookup-view<?php echo $containerName; ?>');
                                            var infoPanel = Ext.getCmp('version-lookup-info-panel<?php echo $containerName; ?>');
                                            if (infoPanel && listView) {
                                                var selNode = listView.getSelectedRecords();
                                                versionLookupTpl<?php echo $containerName; ?>.overwrite(infoPanel.body, selNode[0].data);
                                                var versionTabPanel = Ext.getCmp('version-lookup-tab<?php echo $containerName; ?>');
                                                if (versionTabPanel) {
                                                    var activeTab = versionTabPanel.activeTab;                  
                                                    if (typeof activeTab !== 'undefined') {
                                                        if (activeTab.title === "<?php echo __('Preview'); ?>")
                                                            loadVersionPreview<?php echo $containerName; ?>(selNode[0].data.nodeId);
                                                        else if (activeTab.title === "<?php echo __('Metadata'); ?>") {
                                                            loadVersionMetaData<?php echo $containerName; ?>(selNode[0].data.nodeId);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            })]
                        },
                        {
                            flex:1,
                            layout:'vbox',
                            bodyStyle:'background-color:#e0e8f6;',
                            layoutConfig: {
                                align : 'stretch',
                                pack  : 'start'
                            },
                            items: [
                                {
                                    bodyStyle:'background-color:#e0e8f6;padding:5px;',
                                    height:80,
                                    id:'version-lookup-info-panel<?php echo $containerName; ?>',
                                    html:versionLookupHtml<?php echo $containerName; ?>,
                                    border:false,
                                    frame:false
                                },
                                {
                                    flex:1,
                                    xtype: 'tabpanel',
                                    border:false,
                                    plain: true,
                                    id:'version-lookup-tab<?php echo $containerName; ?>',
                                    defaults:{autoHeight: true},
                                    activeTab: 0,
                                    items: [{
                                        title: '<?php echo __('Preview'); ?>',
                                        cls: 'inner-tab-custom',
                                        layout:'fit',
                                        id:'version-lookup-preview<?php echo $containerName; ?>'
                                    }
                                    /*,{
                                        title: '<?php echo __('Metadata'); ?>',
                                        cls: 'inner-tab-custom',
                                        layout:'fit',
                                        id:'version-lookup-metadata<?php echo $containerName; ?>'
                                    }*/
                                    ],
                                    listeners: {
                                        tabchange: function(tabPanel, tab){
                                            if (typeof tab !== 'undefined') {
                                                var listView = Ext.getCmp('version-lookup-view<?php echo $containerName; ?>');
                                                if (listView) {
                                                    var selNode = listView.getSelectedRecords();
                                                    if (typeof selNode === 'undefined' || typeof selNode[0] === 'undefined')
                                                        return;
                                                    var title = tab.title;
                                                    title = title.toLowerCase();  
                                                    
                                                    var nodeId = selNode[0].data.nodeId;   
                                                    if (typeof nodeId !== 'undefined' && nodeId !== null) {
                                                        switch(title) {
                                                            case "metadata":
                                                                loadVersionMetaData<?php echo $containerName; ?>(nodeId);
                                                            break;
                                                            case "preview":
                                                                loadVersionPreview<?php echo $containerName; ?>(nodeId); 
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            ]
                            
                        }
                    ]
                }),
                listeners:{
                    'beforeshow':{
                        fn:function() {

                        }
                    },
                    'close': {
                        fn:function() {
                            //$("#version-lookup-window-panel<?php echo $containerName; ?>").html('');                        
                        }
                    }
                },
                
                buttons: [{
                    text: '<?php echo __('Close'); ?>',
                    handler: function() {
                        //$("#version-lookup-window-panel<?php echo $containerName; ?>").html('');                    
                        winVersionLookup<?php echo $containerName; ?>.close(this);             
                    }
                }]
            });
            

            winVersionLookup<?php echo $containerName; ?>.show();    
            
            var listView = Ext.getCmp('version-lookup-view<?php echo $containerName; ?>');
            if (listView) {
                listView.select(0);
                loadVersionPreview<?php echo $containerName; ?>(rowNode.data.nodeId);
            }
        }
    }
}

function loadVersionPreview<?php echo $containerName; ?>(nodeId) {
    /*var previewHeight = $("#version-lookup-tab<?php echo $containerName; ?>").height()-30;*/
    var previewHeight = "450";
    $.ajax({ 
        cache: false,              
        url : "<?php echo url_for('Viewer/index') ?>",
        data: "nodeId=workspace://version2Store/"+nodeId+"&height="+previewHeight+"px",
        success : function (data) {
            $("#version-lookup-tab<?php echo $containerName; ?>").unmask();
            $("#version-lookup-preview<?php echo $containerName; ?>").html(data);
        },         
        beforeSend: function(xhr) { 
            $("#version-lookup-preview<?php echo $containerName; ?>").html('');
            $("#version-lookup-tab<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
              
        }
    });
}

function loadVersionMetaData<?php echo $containerName; ?>(nodeId) {
    if (nodeId !== null & typeof nodeId !== 'undefined') {    
        /*var metaHeight =  $("#version-lookup-tab<?php echo $containerName; ?>").height();  */
        var metaHeight = "480";  

        $.ajax({  
            cache: false,             
            url : "<?php echo url_for('Metadata/view') ?>",
            data: "nodeId=workspace://version2Store/"+nodeId+"&containerName=<?php echo $addContainer; ?>&height="+metaHeight,
            success : function (data) {
                $("#version-lookup-tab<?php echo $containerName; ?>").unmask();
                $("#version-lookup-metadata<?php echo $containerName; ?>").html(data);
            },         
            beforeSend: function(xhr) { 
                $("#version-lookup-metadata<?php echo $containerName; ?>").html('');
                $("#version-lookup-tab<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
            }
        });
    }
}


function showNewVersionWindow<?php echo $containerName; ?>(data,title,btnText,saveFunc,addData) {
    //if(!winNewVersion<?php echo $containerName; ?>) {
        winNewVersion<?php echo $containerName; ?> = new Ext.Window({
            modal:true,
            id:'newversion-window<?php echo $containerName; ?>',
            layout:'fit',
            width:516,
            height:216,
            closeAction:'close',
            title:title,
            plain: true, 

            items: new Ext.Panel({
                id:'newversion-window-panel<?php echo $containerName; ?>',
                layout:'fit',
                bodyStyle:'background-color:#e0e8f6;',
                border:false
            }),
            listeners:{
                'beforeshow':{
                    fn:function() {
                        $(".plupload").show();
                    }
                },
                'close': {
                    fn:function() {
                        $(".plupload").hide();
                        $("#newversion-window-panel<?php echo $containerName; ?>").html('');                        
                    }
                }
            },
            
            

            buttons: [{
                id:'uploadNewVersionBtn', // no containerName here!!!
                text: btnText,
                handler: function() {
                    var postData = getVersionWindowInfo(SelectedVersion<?php echo $containerName; ?>.nodeId);
                    var SelectedVersionData = jQuery.extend(data, postData);
                    SelectedVersionData.nodeId = SelectedVersion<?php echo $containerName; ?>.nodeId;
                    eval(saveFunc+"(SelectedVersionData)");

                }
            },
            {
                text: '<?php echo __('Close'); ?>',
                handler: function() {
                    $("#newversion-window-panel<?php echo $containerName; ?>").html('');                    
                    winNewVersion<?php echo $containerName; ?>.close(this);             
                }
            }]
        });
    //}
    winNewVersion<?php echo $containerName; ?>.show();    

    $.ajax({
        cache: false,  
        url : "<?php echo url_for('Versioning/newVersion') ?>"+addData,
        data: data,

        success : function (data) {
            $("#newversion-window<?php echo $containerName; ?>").unmask();
            $("#newversion-window-panel<?php echo $containerName; ?>").html(data);
        },
        beforeSend : function() {
            $("#newversion-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
            $("#newversion-window-panel<?php echo $containerName; ?>").html('');                                                     
        }  
    }); 

    
}

function createNewVersion<?php echo $containerName; ?>(data,upload) {
    if (data !== null) {
        if (upload === true)
            showNewVersionWindow<?php echo $containerName; ?>(data,"<?php echo __('Upload a new Version'); ?>","<?php echo __('Save & Upload'); ?>","saveUploadNewVersion<?php echo $containerName; ?>","?enableUpload&filter="+SelectedVersion<?php echo $containerName; ?>.nodeId);
        else
            showNewVersionWindow<?php echo $containerName; ?>(data,"<?php echo __('Create a new Version'); ?>","<?php echo __('Save'); ?>","saveNewVersion<?php echo $containerName; ?>","");
    }
}

function saveUploadNewVersion<?php echo $containerName; ?>(data) {
    uploadNewVersion(data.nodeId, winNewVersion<?php echo $containerName; ?>,versionList<?php echo $containerName; ?>);
}

function revertVersion<?php echo $containerName; ?>(btn) {
    if (btn === "yes") {
        if (SelectedVersion<?php echo $containerName; ?> !== null) {
            showNewVersionWindow<?php echo $containerName; ?>(SelectedVersion<?php echo $containerName; ?>,"<?php echo __('Revert Version'); ?>","<?php echo __('Save'); ?>","saveRevertVersion<?php echo $containerName; ?>","?hideVersionNumber");
        }
    }
}


function saveNewVersion<?php echo $containerName; ?>(data) {
    if (SelectedVersion<?php echo $containerName; ?> !== null) {
        $.ajax({
            cache: false,  
            url : "<?php echo url_for('Versioning/CreateNewVersion') ?>",
            data: data,               
            error:function() {
              $("#newversion-window<?php echo $containerName; ?>").unmask();                
              winNewVersion<?php echo $containerName; ?>.hide(this);  
            },
            success : function (data) {
                $("#newversion-window<?php echo $containerName; ?>").unmask();              
                var jsonData = $.JSON.decode(data);
                var nodeIdOrg = jsonData.nodeId;
                versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeIdOrg}});
                winNewVersion<?php echo $containerName; ?>.hide(this);
            },
            beforeSend : function() { 
                $("#newversion-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);              
            } 
        });
    }
    else
        winNewVersion<?php echo $containerName; ?>.hide(this); 
}

function saveRevertVersion<?php echo $containerName; ?>(data) {
    if (SelectedVersion<?php echo $containerName; ?> !== null) {
        $.ajax({
            cache: false,  
            url : "<?php echo url_for('Versioning/revertVersion') ?>",
            data: data,               
            error:function() {
              $("#newversion-window<?php echo $containerName; ?>").unmask();                
              winNewVersion<?php echo $containerName; ?>.hide(this);  
            },
            success : function (data) {
                $("#newversion-window<?php echo $containerName; ?>").unmask();              
                var jsonData = $.JSON.decode(data);
                var nodeIdOrg = jsonData.nodeId;
                if (jsonData.success === true) {
                    Ext.MessageBox.show({
                       title: '<?php echo __('Success'); ?>',
                       msg: "<?php echo __('Successfully reverted to the Version:'); ?> <b>"+SelectedVersion<?php echo $containerName; ?>.version+"</b>",
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.INFO
                   });
                }
                else {
                    Ext.MessageBox.show({
                       title: '<?php echo __('Error'); ?>',
                       msg: "<?php echo __('Something went wrong at the revert process'); ?>",
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.WARNING,
                       fn:showRenderer
                   });
                }
                versionList<?php echo $containerName; ?>.store.load({params:{'nodeId':nodeIdOrg}});
                
                winNewVersion<?php echo $containerName; ?>.hide(this);
            },
            beforeSend : function() { 
                $("#newversion-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);              
            } 
        });
    }
    else
        winNewVersion<?php echo $containerName; ?>.hide(this); 
}

var winSpecifyType<?php echo $containerName; ?> = null;
var SpecifyTypeStore<?php echo $containerName; ?> = null;
var SpecifyTypeCombo<?php echo $containerName; ?> = null;
var SpecifyTypeSelectedId<?php echo $containerName; ?> = null;

function specifyType<?php echo $containerName; ?>(myNodeId) { 
    if (myNodeId !== null & typeof myNodeId !== 'undefined') {
        if (SpecifyTypeStore<?php echo $containerName; ?> === null) {
            SpecifyTypeStore<?php echo $containerName; ?> = new Ext.data.JsonStore({
                autoDestroy: true,
                url: '<?php echo url_for('Metadata/ContentTypeList') ?>',
                storeId: 'SpecifyTypeStore<?php echo $containerName; ?>',
                // reader configs
                root: 'types',
                idProperty: 'name',
                fields: ['name', 'title','description'],
                listeners: {
                    'beforeload':{
                        fn:function() {
                            $("#specifytype-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);  
                        }
                    },
                    'load':{
                        fn:function() {
                            $("#specifytype-window<?php echo $containerName; ?>").unmask();
                        }
                    }
                }
            });
            
            
            
            SpecifyTypeCombo<?php echo $containerName; ?> = new Ext.form.ComboBox({
                store: SpecifyTypeStore<?php echo $containerName; ?>,
                displayField:'title',
                typeAhead: true,
                id:'SpecifyTypeCombo<?php echo $containerName; ?>',
                mode: 'local',
                triggerAction: 'all',
                emptyText:'<?php echo __('Select a content type...'); ?>',
                selectOnFocus:true,
                listeners:{
                     'select': function(combo,record,index) {
                         if (typeof record !== 'undefined')
                            SpecifyTypeSelectedId<?php echo $containerName; ?> = record.id;
                         else
                            SpecifyTypeSelectedId<?php echo $containerName; ?> = null;
                     }
                }
            });
        }
        
            winSpecifyType<?php echo $containerName; ?> = new Ext.Window({

                modal:true,
                contentEl:'specifytype-window<?php echo $containerName; ?>',  
                layout:'fit',
                width:350,
                height:100,
                closeAction:'hide',
                title:'<?php echo __('Specify Type'); ?>',
                plain: true, 
                
                listeners:{
                    'beforeshow':{
                        fn:function() {
                            $(".PDFRenderer").hide();
                        }
                    },
                    'hide': {
                        fn:function() {
                            $(".PDFRenderer").show();                      
                        }
                    }
                },
                
                items: [
                    SpecifyTypeCombo<?php echo $containerName; ?>        
                ],
                buttons: [{
                    text:'<?php echo __('Save'); ?>',
                    disabled:false,
                    handler:function() {
                        $("#documentGrid<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);             
                        var type = SpecifyTypeSelectedId<?php echo $containerName; ?>;     
                        if (type !== null && typeof type !== 'undefined') {
                            $("#specifytype-window<?php echo $containerName; ?>").mask("<?php echo __('Loading...'); ?>",300);   
                            $.post("<?php echo url_for('Metadata/SaveContentType') ?>", "nodeId="+myNodeId+"&type="+type, function(data) {
                                var succes = data.success;
                                var nodeId = data.nodeId;

                                loadMetaData<?php echo $containerName; ?>(nodeId);
                                
                                if (succes === false) {
                                    $(".PDFRenderer").hide();  
                                    Ext.MessageBox.show({
                                       title: '<?php echo __('Specify content type error!'); ?>',
                                       msg: '<?php echo __('An error occured please try it later again.'); ?>',
                                       buttons: Ext.MessageBox.OK,
                                       icon: Ext.MessageBox.ERROR,
                                       fn:showRenderer
                                     });
                                }
                                $("#documentGrid<?php echo $containerName; ?>").unmask();
                                $("#specifytype-window<?php echo $containerName; ?>").unmask();            
                            }, "json");
                            winSpecifyType<?php echo $containerName; ?>.hide(this);  
                        }
                    }
                },{
                    text: '<?php echo __('Close'); ?>',
                    handler: function(){
                        winSpecifyType<?php echo $containerName; ?>.hide(this);
                        
                    }
                }]
            });

        //}
        //else {
            //                      
        //}   
        //SpecifyTypeSelectedId<?php echo $containerName; ?> = null; 
        SpecifyTypeStore<?php echo $containerName; ?>.load({params:{'nodeId':myNodeId}});       
        winSpecifyType<?php echo $containerName; ?>.show();   
              
    }        
}

function openFolder<?php echo $containerName; ?>(nodeId,nodeText) {
    var tabnodeid = nodeId.replace(/-/g,"");
    addTabDynamic('tab-'+tabnodeid,nodeText);
        
    $.ajax({
        cache: false,  
        url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid+"&addContainer=<?php echo $nextContainer; ?>&columnsetid="+currentColumnsetid<?php echo $containerName; ?>,


        success : function (data) {
            $("#overAll").unmask();
            $("#tab-"+tabnodeid).html(data);

            eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':currentColumnsetid<?php echo $containerName; ?>}});");
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo __('Loading'); ?> "+nodeText+"...",300);    
        } 
    });
}

function deleteNode<?php echo $containerName; ?>(nodeId,nodeName,nodeType) {
    Ext.MessageBox.show({
       title:'<?php echo __('Delete?'); ?>',
       msg: '<?php echo __('Do you really want to delete:'); ?> <br><b>'+nodeName+'</b>',
       fn:function(btn) {
        if (btn === "yes") {
            $("#documentGrid<?php echo $containerName; ?>").mask("<?php echo __('Deleting'); ?> "+nodeName+" ...",300); 
            $.post("<?php echo url_for('NodeActions/DeleteNode') ?>", "nodeId="+nodeId+"&nodeType="+nodeType, function(data) {
                var succes = data.success;
                $("#documentGrid<?php echo $containerName; ?>").unmask();                             
                if (succes === true) {
                    //Ext.MessageBox.alert('Deleted', 'Document <b>'+nodeName+'</b> deleted successfully.');   
                    mainGrid<?php echo $containerName; ?>.getStore().reload();                                                   
                }
                else {
                    Ext.MessageBox.show({
                       title: '<?php echo __('Error'); ?>',
                       msg: data.message,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.WARNING
                   })
                }
            }, "json");
        }  
        $(".PDFRenderer").show();  
       },
       buttons: Ext.MessageBox.YESNO,
       icon: Ext.MessageBox.QUESTION
   });   
}

function showRenderer(btn) {
   $(".PDFRenderer").show();   
}

function getLastParams<?php echo $containerName; ?>() {
    return lastParams<?php echo $containerName; ?>;
}

function deleteNodes<?php echo $containerName; ?>(nodes) {
    
    var jsonNodes = $.toJSON(nodes);
    var nodeNames = "";
    var nodeNamesLoad = "";
    for (var i = 0; i < nodes.length; ++i) {
        nodeNames += nodes[i].nodeName+"<br>";     
        nodeNamesLoad += "<i>"+nodes[i].nodeName+"</i><br>";     
    }       
    Ext.MessageBox.show({
       title:'<?php echo __('Delete?'); ?>',
       msg: '<?php echo __('Do you really want to delete:'); ?> <br><b>'+nodeNames+'</b>',
       fn:function(btn) {
        if (btn === "yes") {
            $("#documentGrid<?php echo $containerName; ?>").mask("<?php echo __('Deleting'); ?> <br>"+nodeNamesLoad,300); 
            $.post("<?php echo url_for('NodeActions/DeleteNodes') ?>", "nodes="+jsonNodes, function(data) {
                var succes = data.success;
                $("#documentGrid<?php echo $containerName; ?>").unmask();                             
                if (succes === true) {
                    //Ext.MessageBox.alert('Deleted', 'Document <b>'+nodeName+'</b> deleted successfully.');   
                    mainGrid<?php echo $containerName; ?>.getStore().reload();                                                   
                }
                else {
                    Ext.MessageBox.show({
                       title: '<?php echo __('Error'); ?>',
                       msg: data.message,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.WARNING
                   })
                }
            }, "json");
        }    
       },
       buttons: Ext.MessageBox.YESNO,
       icon: Ext.MessageBox.QUESTION
   });   
}

var winEmail<?php echo $containerName; ?> = null;
var emailForm<?php echo $containerName; ?> = null;

function sendMail<?php echo $containerName; ?>(nodes) {
    var attachments = "";
    $.each(nodes,function(index,node) {
        attachments += node.docName+"&nbsp;&nbsp;&nbsp;";
    });
    var encodedNodes = $.toJSON(nodes);
    emailForm<?php echo $containerName; ?> = new Ext.FormPanel({  
        labelAlign: 'left',
        frame:true,
        bodyStyle:'padding:5px 5px 0',
        height:300,
        items: [{
            layout: 'form',
            items: [
                {
                    xtype:'textfield',
                    fieldLabel: '<?php echo __('To'); ?>',
                    name: 'to',
                    anchor:'100%',
                    vtype:'multiemail',
                    allowBlank:false
                },{
                    xtype:'textfield',
                    fieldLabel: '<?php echo __('Cc'); ?>',
                    name: 'cc',
                    anchor:'100%',
                    vtype:'multiemail'
                },{
                    xtype:'textfield',
                    fieldLabel: '<?php echo __('Bcc'); ?>',
                    name: 'bcc',
                    anchor:'100%',
                    vtype:'multiemail'
                },{
                    xtype:'textfield',
                    fieldLabel: '<?php echo __('Subject'); ?>',
                    name: 'subject',
                    anchor:'100%'
                },{
                    xtype:'panel',
                    fieldLabel: '<?php echo __('Attachments'); ?>',
                    anchor:'100%',
                    html:attachments
                },{
                    xtype:'hidden',
                    name:'nodes',
                    value:encodedNodes
                }
            ]
        },{
            xtype:'htmleditor',
            id:'body',
            //fieldLabel:'Body',
            height:150,
            name: 'body',
            anchor:'100%',
            hideLabel: true,
            anchor: '100% -130'
        }]
    });
    
    winEmail<?php echo $containerName; ?> = new Ext.Window({
        modal:true,
        id:'email-window<?php echo $containerName; ?>',
        title:'<?php echo __('Send Attachment(s) via Email'); ?>',
        plain: true, 
        
        buttonAlign: 'right', //buttons aligned to the right  
        //bodyStyle:'background-color:#fff;padding: 10px',   
        width:540,
        height:400,
        closeAction:'close',
        plain: true, 
        resizable:true,

        items: emailForm<?php echo $containerName; ?>,
        listeners:{
            'beforeshow':{
                fn:function() {
                    
                }
            },
            'close': {
                fn:function() {
                    
                }
            }
        },
        buttons:[
            {
                text:'<?php echo __('Send'); ?>',
                handler:function() {
                    if(emailForm<?php echo $containerName; ?>.getForm().isValid()) {
                        emailForm<?php echo $containerName; ?>.getForm().submit({
                            url: '<?php echo url_for('NodeActions/MailNode') ?>',
                            waitMsg: '<?php echo __('Sending Email'); ?>',
                            success: function(form, result) {
                                Ext.Msg.alert('<?php echo __('Success'); ?>', "<?php echo __('We could send your email successfully!'); ?>");
                                winEmail<?php echo $containerName; ?>.close();    
                            },
                            failure: function(form, result) { 
                                Ext.Msg.alert('<?php echo __('Error'); ?>', result.result.errorMsg);
                            }
                        });
                    }
                }
            },
            {
                text:'<?php echo __('Cancel'); ?>',
                handler: function() {                 
                    winEmail<?php echo $containerName; ?>.close(this);             
                }
            }
        ]
    });
    
    winEmail<?php echo $containerName; ?>.show();    
}

</script>
<style type="text/css">
#documentGrid<?php echo $containerName; ?> {
    width:100%;
    padding:0;
    height:100%;
    margin:0;    
}

#documentGrid<?php echo $containerName; ?> td, #documentGrid<?php echo $containerName; ?> th, #documentGrid<?php echo $containerName; ?> table {
    padding:0;
    margin:0;        
}

#documentGrid<?php echo $containerName; ?> ul, #documentGrid<?php echo $containerName; ?> ul li {
    padding:0;
    margin:0;
}

</style>


<div id="documentGrid<?php echo $containerName; ?>" containerId="<?php echo $containerName; ?>" addContainer="<?php echo $addContainer; ?>">
</div>

<div id="upload-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">

    <div class="x-window-header"><?php echo __('Upload File(s)'); ?></div>
    <div id="upload-window-panel<?php echo $containerName; ?>">
        
    </div>
</div>

<div id="general-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">
<div id="window-panel<?php echo $containerName; ?>">
        
    </div>
</div>


<div id="aspects-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">
<div id="aspects-window-panel<?php echo $containerName; ?>">
        
    </div>
</div> 

<div id="specifytype-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">

</div> 


<div id="metadata-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">
<div id="metadata-window-panel<?php echo $containerName; ?>">
        
    </div>
</div>

<!--<div id="newversion-window<?php echo $containerName; ?>" class="x-hidden x-body-masked" style="z-index:100;">
<div id="newversion-window-panel<?php echo $containerName; ?>">
        
    </div>
</div> -->
