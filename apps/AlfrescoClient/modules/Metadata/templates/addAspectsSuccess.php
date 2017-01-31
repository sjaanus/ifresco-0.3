<style type="text/css">
#AspectContainer {

    background-color:#e0e8f6;
    height:350px;
    width:500px;  
}
#availableAspects {
    overflow:auto;
    float:left;    
    width:240px;
    background-color:#fff;
    height:322px;    
    margin-top:0px; 
    margin-left:5px;   
    margin-bottom:5px; 
    padding:0;  
    list-style:none; 
    border:1px solid #808080;   
}   

#assignedAspects {
    overflow:auto;
    float:left; 
    width:50%;
    width:240px;
    background-color:#fff; 
    height:322px;

    margin-top:0px;   
    margin-left:5px;   
    margin-right:5px;   
    margin-bottom:5px;
    padding:0;   
    list-style:none;
    border:1px solid #808080; 
} 

#AspectContainer .icon {
    float:left;
    margin-right:5px;
    margin-top:1px;
}

#AspectContainer .text {
    float:left;
    font-weight:bold;
    color:#585858;
}

#AspectContainer .action {
    float:right;  
    margin-top:1px;     
}

#AspectContainer li {
    padding:0;
    margin:0;
    padding:7px;
    font-size: 13px;
    font-family:arial;
    height:18px; 
    border-bottom:1px dashed #808080;   
    
}  

.even {
    background-color:#e0e8f6;     
}

.odd {
    background-color:#fff;                                                 
}

#AspectContainer .aspectHeader {
    font-size:12px;
    color:#808080;
    padding-left:10px; 
    margin:0;
    font-family:arial;
    display:block;
    padding-top:5px; 
    font-weight:bold; 
}

</style>
<script type="text/javascript">
$(document).ready(function() {      
    refreshColors();    
});

function refreshColors() {
    $("#AspectContainer li").removeClass("even");
    $("#AspectContainer li").removeClass("odd");
    $("#AspectContainer li:even").addClass("even");
    $("#AspectContainer li:odd").addClass("odd");    
}

function addAspect(name,id) {
    var liObject = $("#"+id);
    var aImg = $("#"+id+" .action img");       
    aImg.attr('src','/images/toolbar/cross.png');
    var aTag = $("#"+id+" .action a"); 
    aTag.attr('href',"javascript:removeAspect('"+name+"','"+id+"')");          
    $("#assignedAspects").append(liObject);  
    refreshColors();                                                                  
}

function removeAspect(name,id) {
    var liObject = $("#"+id);
    var aImg = $("#"+id+" .action img");       
    aImg.attr('src','/images/toolbar/add.png');
    var aTag = $("#"+id+" .action a"); 
    aTag.attr('href',"javascript:addAspect('"+name+"','"+id+"')");          
    $("#availableAspects").append(liObject);  
    refreshColors();                                                                                                                                        
}

function getSelectedAspects() {                        
    var deselectedData = form2object('deSelAspectsForm');
    var formData = form2object('aspectsForm');
    //var jsonData = $.toJSON(formData);   
    var deselectedAspects = deselectedData.aspects; 
    var aspects = formData.aspects;  
    var nodeId = formData.nodeId;  
    var postData = "nodeId="+nodeId+"&aspects="+aspects+"&deselaspects="+deselectedAspects;

    return postData;   
}
</script>

<div id="AspectContainer">
    
    <div style="float:left;">
    <div class="float:left;width:100px;"><span class="aspectHeader"><?php echo __('Available Aspects:'); ?></span></div>
    <form name="deSelAspectsForm" id="deSelAspectsForm" action="" method="post">    
    <ul id="availableAspects">
        <?php
            foreach ($AspectList as $Aspect) {
                
                $Display = $Aspect->title;
                if (empty($Display))
                    $Display = $Aspect->name;
                    
                $IdObject = str_replace(":","_",$Aspect->name);
            ?>    

        <li id="<?php echo $IdObject; ?>">
            <input type="hidden" name="aspects[]" value="<?php echo $Aspect->name; ?>">
            <div class="icon"><img src="/images/toolbar/brick.png" border="0" align="absmiddle"> </div>
            <div class="text"><?php echo $Display; ?></div>
            <div class="action"><a href="javascript:addAspect('<?php echo $Aspect->name; ?>','<?php echo $IdObject; ?>')"><img src="/images/toolbar/add.png" border="0" align="absmiddle"></a></div>
        </li>
        <?php
        }
?>
   
    </ul></form></div>
    <div style="float:left;">
    <div class="float:left;width:100px;margin:0;padding:0;"><span class="aspectHeader"><?php echo __('Used Aspects:'); ?></span></div>             
    <form name="aspectsForm" id="aspectsForm" action="" method="post">
        <input type="hidden" name="nodeId" value="<?php echo $nodeId; ?>">
        <ul id="assignedAspects">
            <?php
                foreach ($CurrentAspectList as $Aspect) {
                    
                    $Display = $Aspect->title;
                    if (empty($Display))
                        $Display = $Aspect->name;
                        
                    $IdObject = str_replace(":","_",$Aspect->name);
                ?>    

            <li id="<?php echo $IdObject; ?>">
                <input type="hidden" name="aspects[]" value="<?php echo $Aspect->name; ?>"> 
                <div class="icon"><img src="/images/toolbar/brick.png" border="0" align="absmiddle"> </div>
                <div class="text"><?php echo $Display; ?></div>
                <div class="action"><a href="javascript:removeAspect('<?php echo $Aspect->name; ?>','<?php echo $IdObject; ?>')"><img src="/images/toolbar/cross.png" border="0" align="absmiddle"></a></div>
            </li>
            <?php
            }
    ?>
        </ul>
    </form>
    </div>
</div> 