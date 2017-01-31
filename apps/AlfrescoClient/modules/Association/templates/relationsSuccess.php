<?php use_javascript('/js/extjs/adapter/ext/ext-base.js'); ?>
<?php use_javascript('/js/extjs/ext-all.js'); ?>

<?php use_javascript('/js/extjs/ux/maximgb/TreeGrid.js'); ?>

<?php use_stylesheet('/js/extjs/ux/maximgb/css/TreeGrid.css'); ?>

<script type="text/javascript">
Ext.onReady(function() {
    
    var RelationsRecord = Ext.data.Record.create([
         {name: 'name'},
         {name: 'nodeId'},
         {name: 'iconCls'},
         {name: '_parent', type: 'auto'},
         {name: '_is_leaf', type: 'bool'}
    ]);
       
    var RelationsStore = new Ext.ux.maximgb.tg.AdjacencyListStore({
        autoLoad : true,
        url: '<?php echo url_for('Association/RelationsJSON') ?>?nodeId=<?php echo $nodeId;?>',
            reader: new Ext.data.JsonReader(
                {
                    id: 'nodeId',
                    root: 'data',
                    totalProperty: 'total',
                    successProperty: 'success'
                }, 
                RelationsRecord
            )
    });
    // create the Grid
    var relationsView = new Ext.ux.maximgb.tg.GridPanel({
      store: RelationsStore,
      width:'100%',
      height:400,
      renderTo:'treeview',
      master_column_id : 'name',
      columns: [
        {id:'name',header: "<?php echo __('Relations'); ?>", width: 300, sortable: true, dataIndex: 'name'}
      ],
      stripeRows: true,
      autoExpandColumn: 'name',
      title: '<?php echo __('Relations'); ?>'

    });

    relationsView.getSelectionModel().selectFirstRow();
});
</script>
<div id="treeview"></div>