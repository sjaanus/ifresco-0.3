<?php use_helper('ysUtil') ?>
<?php use_helper('ysJQueryRevolutions')   ?>

<script type="text/javascript">
$(document).ready(function () {  
    $("#alfrescoTree").tree({
             data : {     
                async : true,
                opts : {      
                    method : "POST",    
                    url : "<?php echo url_for('tree/getHTML') ?>"
                }
            },
            ui : {
                theme_name : "apple"
            },
            types : {
                "default" : {
                    deletable : false,
                    renameable : false
                },    
                "folder" : {
                    valid_children : [ "file" ]
                    
                }


            },
            callback : {
                    onchange : function(node, tree_obj) { 
                    reloadGridData(node.id);
                }
            }
        });
});
</script>  

<div id="alfrescoTree"></div>   

