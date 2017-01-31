
<script type="text/javascript">

var versionList<?php echo $BlockId; ?> = null;

var nodeId<?php echo $BlockId; ?> = '<?php echo $nodeId;?>';
var nodeImgName<?php echo $BlockId; ?> = '<?php echo html_entity_decode($NodeImgName); ?>';
Ext.onReady(function(){

    Ext.state.Manager.setProvider(
        new Ext.state.CookieProvider({
            expires: new Date(new Date().getTime()+(1000*60*60*24*365)) //1 year from now
    }));

    
    Ext.QuickTips.init();
    
    var win<?php echo $BlockId; ?>, winSpace<?php echo $BlockId; ?>;
    
    
    
    /* VERSIONS */
    var VersionStore<?php echo $BlockId; ?> = new Ext.data.JsonStore({
        url : "<?php echo url_for('Versioning/getJSON') ?>",
        root: 'versions',
        fields: ['nodeRef', 'nodeId', 'version', 'description', {name:'date', type:'date', dateFormat:'timestamp'}, 'author', 'actions'],
        
        listeners: {
            'beforeload':{
                fn:function() {
                    $("#versionsContent<?php echo $BlockId; ?>").mask("<?php echo __('Loading...'); ?>",300);  
                }
            },
            'load':{
                fn:function() {
                    $("#versionsContent<?php echo $BlockId; ?>").unmask();
                }
            }
        }
    });
    VersionStore<?php echo $BlockId; ?>.load();

    var VersionlistView<?php echo $BlockId; ?> = new Ext.list.ListView({
        store: VersionStore<?php echo $BlockId; ?>,
        layout:'fit',
        height:'auto',
        multiSelect: false,

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
            format: 'm/d/y h:i a',
            dataIndex: 'date',
            width: .15
        },
        {
            header: '<?php echo __('Author'); ?>',
            dataIndex: 'author',
            width: .10
        },
        {
            header: '<?php echo __('Actions'); ?>',            
            dataIndex: 'actions',
            width: .10
        }]
    });
    
    // put it in a Panel so it looks pretty
    var VersionPanel<?php echo $BlockId; ?> = new Ext.Panel({
        id:'version-view',
        width:'100%',

        height:300,
        collapsible:false,
        layout:'fit',
        title:'<?php echo __('Versions'); ?>',
        items: VersionlistView<?php echo $BlockId; ?>
    });
    
    versionList<?php echo $BlockId; ?> = VersionlistView<?php echo $BlockId; ?>;

    var previewTab<?php echo $BlockId; ?> = {region: 'center',
                        header:false,
                        id:'previewContent<?php echo $BlockId; ?>',
                        maxSize: 150,
                        //autoLoad: {url: '<?php echo url_for('Viewer/index') ?>', method: 'get',params: 'nodeId=659be981-e44a-4e04-b692-424e047d773a'},
                        html: '<div id="previewWindow<?php echo $BlockId; ?>" style="height:100%;width:100%;"></div>'
    };
    
    var versionsTab<?php echo $BlockId; ?> = {region: 'center',
                        header:false,
                        id:'versionsContent<?php echo $BlockId; ?>',
                        maxSize: 150,
                        //html: '<div id="versionsWindow" style="height:100%;width:100%;"></div>'
                        items:[VersionPanel<?php echo $BlockId; ?>]
    };
    
    var relationsTab<?php echo $BlockId; ?> = {region: 'center',
                        header:false,
                        id:'relationsContent<?php echo $BlockId; ?>',
                        maxSize: 150,
                        html: '<div id="relationsWindow<?php echo $BlockId; ?>" style="height:100%;width:100%;"></div>'
                        //items:[relationsView]
    };
    
    var commentsTab<?php echo $BlockId; ?> = {region: 'center',
                        header:false,
                        id:'commentsContent<?php echo $BlockId; ?>',
                        maxSize: 150,
                        html: '<div id="commentsWindow<?php echo $BlockId; ?>" style="height:100%;width:100%;"></div>'
    };
    
    var metadataTab<?php echo $BlockId; ?> = {region: 'center',
                        header:false,
                        id:'metadataContent<?php echo $BlockId; ?>',
                        maxSize: 150,
                        html: '<div id="metadataWindow<?php echo $BlockId; ?>" style="height:100%;width:100%;"></div>',
                        tbar: [{
                            iconCls:'download-node',
                            text:'<?php echo __('Download'); ?>',
                            handler: function(){
                                var dlUrl = '<?php echo url_for('NodeActions/Download') ?>?nodeId='+nodeId<?php echo $BlockId; ?>;
                                window.open(dlUrl);
                            },
                            scope: this
                        },'-',{
                            iconCls:'view-metadata',
                            text:'<?php echo __('Edit Metadata'); ?>',
                            handler: function(){         
                                editMetadata<?php echo $BlockId; ?>(nodeId<?php echo $BlockId; ?>,nodeImgName<?php echo $BlockId; ?>);
                            },
                            scope: this
                        },'-',{
                            iconCls:'checkout-node',
                            text:'<?php echo __('Checkout'); ?>',
                            handler: function(){
                                //var nodeId = PanelNodeId<?php echo $BlockId; ?>;   
                                checkOut<?php echo $BlockId; ?>(nodeId<?php echo $BlockId; ?>);     
                            },
                            scope: this
                        },'-',{
                            iconCls:'refresh-meta',
                            text:'<?php echo __('Refresh'); ?>',
                            handler: function(){
                                //var nodeId = PanelNodeId<?php echo $BlockId; ?>;   
                                loadMetaData<?php echo $BlockId; ?>(nodeId<?php echo $BlockId; ?>);
                            },
                            scope: this
                        }]
    };

    

    var viewport = new Ext.Panel({
        layout:'fit',
        header:false,
        width:'100%',
        split:true,
        items: [{
            region: 'center',
            title: '<?php echo __('Preview'); ?>',
            layout: 'border',
            header:false,
            layout:'fit',           
            deferredRender:false,
            defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
            items: [previewTab<?php echo $BlockId; ?>]
        },{
            split:true,
            border:false,
            xtype: 'tabpanel',
            id:'previewPanel<?php echo $BlockId; ?>',
            plain: true,
            
            region: 'south',
            height: 342,         
            tabPosition: 'bottom',
            activeTab: 0,
            
            items: [{
                title: '<?php echo __('Metadata'); ?>',
                id:'mainMetadataTab<?php echo $BlockId; ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [metadataTab<?php echo $BlockId; ?>]
            },{
                title: '<?php echo __('Versions'); ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [versionsTab<?php echo $BlockId; ?>]
            }
            
           /* {
                title: '<?php echo __('Relations'); ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                id:'relationsTab<?php echo $BlockId; ?>',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [relationsTab<?php echo $BlockId; ?>]
            },
            {
                title: '<?php echo __('Comments'); ?>',
                cls: 'inner-tab-custom',
                layout: 'border',
                deferredRender:false,
                defaults: Ext.apply({},Ext.isGecko? {style:{position:'absolute'},hideMode:'visibility'}:false),
                items: [commentsTab<?php echo $BlockId; ?>]
            }*/]
        }],
        renderTo: 'detailGrid<?php echo $BlockId; ?>'
    });
    
    var nodeId<?php echo $BlockId; ?> = "<?php echo $NodeId; ?>";
    
    // METADATA
    
    loadMetaData<?php echo $BlockId; ?>(nodeId<?php echo $BlockId; ?>);
    
    
    /// VERSION
    var previewHeight<?php echo $BlockId; ?> = $("#previewWindow<?php echo $BlockId; ?>").height();               
    
    $.ajax({             
        url : "<?php echo url_for('Viewer/index') ?>",
        data: "nodeId="+nodeId<?php echo $BlockId; ?>+"&height="+previewHeight<?php echo $BlockId; ?>,
        success : function (data) {
            $("#previewContent<?php echo $BlockId; ?>").unmask();
            $("#previewWindow<?php echo $BlockId; ?>").html(data);
        },         
        beforeSend: function(xhr) { 
            $("#previewWindow<?php echo $BlockId; ?>").html('');
            $("#previewContent<?php echo $BlockId; ?>").mask("<?php echo __('Loading...'); ?>",300);  
              
        }
    });
    
    versionList<?php echo $BlockId; ?>.store.load({params:{'nodeId':nodeId<?php echo $BlockId; ?>}});
    
    /*$.ajax({             
        url : "<?php echo url_for('Association/Relations') ?>",
        data: "nodeId="+nodeId,
        success : function (data) {
            $("#relationsContent<?php echo $BlockId; ?>").unmask();
            
            $("#relationsWindow<?php echo $BlockId; ?>").html(data);
        },         
        beforeSend: function(xhr) { 
            $("#relationsWindow<?php echo $BlockId; ?>").html('');
            $("#relationsContent<?php echo $BlockId; ?>").mask("<?php echo __('Loading...'); ?>",300);  
              
        }
    }); */

    /*$.ajax({             
        url : "<?php echo url_for('Comments/index') ?>",
        data: "nodeId="+nodeId,
        success : function (data) {
            $("#commentsContent<?php echo $BlockId; ?>").unmask();
            
            $("#commentsWindow<?php echo $BlockId; ?>").html(data);
        },         
        beforeSend: function(xhr) { 
            $("#commentsWindow<?php echo $BlockId; ?>").html('');
            $("#commentsContent<?php echo $BlockId; ?>").mask("<?php echo __('Loading...'); ?>",300);  
              
        }
    }); */
});

function mySize() {   
    $("#detailGrid<?php echo $BlockId; ?>").css({'height': (($(window).height()) -98)+'px'});
    return $("#detailGrid<?php echo $BlockId; ?>").height();
}
  
function checkOut<?php echo $BlockId; ?>(nodeId) {
    $.ajax({
        url : "<?php echo url_for('Metadata/Checkout') ?>?nodeId="+nodeId,
        
        
        success : function (data) {
            Ext.MessageBox.alert('<?php echo __('Checkout'); ?>', '<?php echo __('Document checked out successfully.'); ?>');                                
        },
        beforeSend: function(req) {
              
        } 
    });   
}

function checkIn<?php echo $BlockId; ?>(nodeId) {
    $.ajax({
        url : "<?php echo url_for('Metadata/Checkin') ?>?nodeId="+nodeId,
        
        success : function (data) {
            Ext.MessageBox.alert('<?php echo __('Checkin'); ?>', '<?php echo __('Document checked in successfully.'); ?>');                                       
        },
        beforeSend: function(req) {
              
        } 
    });  
}

function editMetadata<?php echo $BlockId; ?>(nodeId,nodeName) {

    var tabnodeid = nodeId.replace(/-/g,"");              
    addTabDynamic('metadatatab-'+tabnodeid,"<?php echo __('Edit Metadata:'); ?> "+nodeName);
                    
    $.ajax({
        
        url : "<?php echo url_for('Metadata/index') ?>",
        data: ({'nodeId' : nodeId}),

        
        success : function (data) {
            $("#overAll").unmask();
            $("#metadatatab-"+tabnodeid).html(data);
        },
        beforeSend: function(req) {
            $("#overAll").mask("<?php echo __('Loading'); ?> "+nodeName+" ...",300);    
        }  
    });
}

function loadMetaData<?php echo $BlockId; ?>(nodeId) {
    var metaHeight = $("#metadataWindow<?php echo $BlockId; ?>").height();      

    $.ajax({             
        url : "<?php echo url_for('Metadata/view') ?>",
        data: "nodeId="+nodeId+"&height="+metaHeight+"&containerName=detailView",
        success : function (data) {
            $("#metadataContent<?php echo $BlockId; ?>").unmask();
            
            $("#metadataWindow<?php echo $BlockId; ?>").html(data);
        },         
        beforeSend: function(xhr) { 
            $("#metadataWindow<?php echo $BlockId; ?>").html('');
            $("#metadataContent<?php echo $BlockId; ?>").mask("<?php echo __('Loading...'); ?>",300);  
              
        }
    });
}


</script>
<style type="text/css">
#detailGrid<?php echo $BlockId; ?> {
    width:100%;
    padding:0;
    height:100%;
    margin:0;    
}

#detailGrid<?php echo $BlockId; ?> td, #detailGrid<?php echo $BlockId; ?> th, #detailGrid<?php echo $BlockId; ?> table {
    padding:0;
    margin:0;        
}

#detailGrid<?php echo $BlockId; ?> ul, #detailGrid<?php echo $BlockId; ?> ul li {
    padding:0;
    margin:0;
}

</style>

<div id="detailGrid<?php echo $BlockId; ?>">
</div>

