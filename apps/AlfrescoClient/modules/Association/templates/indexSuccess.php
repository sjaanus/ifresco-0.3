<?php use_helper('ysJQueryRevolutions')?>
<?php use_helper('ysJQueryAutocomplete')?>
<?php use_helper('ysJQueryUIDialog')?>

<style type="text/css">
#contentAssociationBox {
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
}
#contentAssociationInput {
    /*width:300px;  */
    width:275px;  
    border:1px solid #ccd4db;     
    /*background:url(/images/icons/search.png) right center no-repeat; */
    background:url(/images/layer/searchDocumentLayer.png) left no-repeat;
    height:19px;
    padding-left:25px;
    font-weight: bold;
    color:#515d6b;
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
              
}

#ResultListBox {
    margin-top:2px; 
    border:1px solid #ccd4db;      
    overflow:auto; 
    width:300px; 
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
}

#ResultList {
    list-style: none;      
    margin:0;
    padding:0; 
           
    height:150px;   
    width:99%; 
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
}

#ResultList li {
    list-style: none; 
    width:100%;
    display:block;
    height:20px;
    margin:0;
    padding:0;
    font-size:12px;
    font-family: Trebuchet MS,Arial,Helvetica,sans-serif;
    /*background:url(/images/layer/footerbg.png);*/
              
}
</style>
<div id="contentAssociationBox">
    <input type="hidden" name="contentAssociationValues">
    <input type="text" id="contentAssociationInput" />
    <div id="ResultListBox">       
        <ul id="ResultList">
        
        </ul>
    </div>

        <script type="text/javascript">

function setResult(data, value) {
    var value = new String(value); 
    var myValue = value.split("/");
    var extension = myValue[0];
    var nodeId = myValue[1];
    var nodeName = myValue[2];
    
    $('#ResultList li').each(function(index) {
        //alert(index + ': ' + $(this).attr('id'));
        //jQuery("#"+$(this).attr('id')).remove(); 
        if ($(this).attr('id') == nodeId)    
            return false; 
    });

    
    jQuery("#ResultList").append("<li id=\""+ nodeId +"\" class=\"ui-state-default\"><img width=\"16\" height=\"16\" src=\"/images/filetypes/16x16/" + extension + ".png\" align=\"absmiddle\"/> "+ nodeName + " <img width=\"16\" height=\"16\" src=\"/images/icons/delete.png\" align=\"absmiddle\" style=\"float:right;cursor:pointer;\" onclick=\"removeResult(\'"+ nodeId +"\')\"></li>");
    jQuery("#"+nodeId).mouseover(function(){
        $(this).removeClass().addClass("ui-state-active");
    }).mouseout(function(){
        $(this).removeClass().addClass("ui-state-default");        
    });
    return false;    
}        

function removeResult(objId) {
    jQuery("#"+objId).remove();      
}
    
$(document).ready(function() {
    jQuery("#contentAssociationInput").autocomplete(
        "<?php echo url_for('Association/autocompleteContentData') ?>", 
        {
            oddClass: 'ui-state-default',
            evenClass: 'ui-state-hover',
            overClass: 'ui-state-active',
            focus:false,
            width: 600,
            max: 20,
            highlight: false,
            scroll: true,
            delay: 250,
            multiple:true,
            scrollHeight: 300,
            formatItem: function(data, i, n, value) {
                return '<img width=\"32\" height=\"32\" src=\"/images/filetypes/32x32/' + value.split('/')[0] + '.png\" align=\"absmiddle\"/> ' + value.split('/')[2];
                /*return '<img width=\"32\" height=\"32\" src=\"/images/filetypes/32x32/' + value.split('/')[0] + '.png\" align=\"absmiddle\"/>' + value.split('/')[2] + '<img width=\"16\" height=\"16\" src=\"/images/icons/add.png\" align=\"absmiddle\" style=\"float:right;\" onclick="setResult(\''+data+'\',\''+value+'\')">';*/   
                
                
            },
            formatResult: function(data, value) {
                /*var nodeId=value.split("/")[1];
                var nodeName=value.split("/")[2];
                return nodeName;    */
                
                return "";
                //return false;
            }
        }
        
    ).result(function(data, value){
        jQuery("#contentAssociationInput").val('');
        setResult(data,value);    
    })
});
</script>   
