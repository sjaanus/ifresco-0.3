<?php //use_stylesheets_for_form($form) ?>
<?php //use_javascripts_for_form($form) 
use_helper('ysJQueryRevolutions');
use_helper('ysJQueryAutocomplete');
use_helper('ysJQueryUIDialog');
?>
<link href="/css/checkbox.css" type="text/css" rel="stylesheet">


<script src="/js/iphone-style-checkboxes.js"></script>


<style type="text/css">
.save {
    background-image: url( /images/icons/disk.png ) !important;
}
.cancel {
    background-image: url( /images/icons/cross.png ) !important;
}
</style>

<script type="text/javascript">

Ext.onReady(function(){


var metaForm = new Ext.FormPanel({
        labelAlign: 'left',
        labelWidth: 100,
        frame:false,
        header:false,
        bodyStyle:'padding:5px;',
        width: '100%',
        renderTo:'metaFormPanel',
        tbar: [{
            xtype: 'buttongroup',
            items: [{
                text: '<?php echo __('Save'); ?>',
                iconCls: 'save',
            },
            {
                text: '<?php echo __('Cancel'); ?>',
                iconCls: 'cancel',
            }]
        }],

        defaults: {width: 298},
        defaultType: 'textfield',
        items: [
            <?php echo $form; ?>
        ],

        buttons: [{
            text: '<?php echo __('Save'); ?>'
        },{
            text: '<?php echo __('Cancel'); ?>'
        }]
    });

});
</script>


<?php //echo form_tag_for($form, '@metadata') ?>
<div id="metaFormPanel"></div>

<?php //echo $form['assoc_cx_responsiblePerson'];
if (count($form->additionalFields) > 0) {
    foreach ($form->additionalFields as $fieldName => $widget) {
        echo $form[$fieldName];        
    }
} ?>

