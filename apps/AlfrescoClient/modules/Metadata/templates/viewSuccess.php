<?php $fullHeight = ($height-30);
if ($isCheckedOut || $isWorkingCopy) {
    $fullHeight = $fullHeight-30; 
}
?>
    
<style type="text/css">
    #metaDataView {
        overflow:auto;
        height:<?php echo $fullHeight; ?>px; 
        background-color:#F0F0F0;            
    } 

    
    #metaDataView .fields {
        list-style:none;
        margin:0;
        padding:0;
        width:300px;
        float:left;
        display:block;
        margin-right:2px;
    } 
    
    #metaDataView .fields li {
        margin:0;
        padding:0;
        list-style:none;
    }
    
    #metaDataView .fields li .fieldContainer {
        margin:0;
        padding:0;
        background-color:#E0E8F6; 

        font-size:11px;
        border:1px dotted #99BBE8;
        float:left;  
     
        margin-top:1px;
        padding:2px;
        min-height:20px;
        width:300px; 
        margin:2px;
        
    }
    
    #metaDataView .fields li .fieldContainer:hover {
        background-color:#F5E218;                   
    }
    
    #metaDataView .fields li label {
        font-weight:bold;

        border-right:1px dotted #99BBE8;
        float:left; 
        width:150px;
        padding:2px;                 
    }
    
    #metaDataView .fields li .value { 
        background-color:#fff;
        display:block;
        padding:2px;
        float:left;
        width:180px;
        min-height:18px;
    } 
    
    .rowB label, .rowB .value {
        color:#585858;    
    }
    
    .rowA label, .rowA .value {
        color:#000;    
    }
    
    #metaDataView .fields2 {
        list-style:none;
        margin:0;
        padding:0;

        float:none;
    } 
    
    #metaDataView .fields2 li {
        padding:0;
        margin:0;
    }
    
    #metaDataView .fields2 li .fieldContainer { 
        background-color:#F0F0F0;
    }   
    
    #metaDataView .fields li label {
        width:110px;        
    }
    
    #metaDataView a {
        color:#15428b;
    }

    
    #breadCrumb {
        height:20px;
        padding:5px;
        background-color:#EEEEEE;
        border-bottom:1px solid #FFFFFF;
    }
    
    #breadCrumb ul {
        float:left;
    }
    
    #breadCrumb ul li {
        float:left;
        margin:2px;
    }
    
   
    
    #breadCrumb ul li a {
        padding:5px;
        background-color:#e0e8f6;
        border:1px solid #fff;
        color:#969696;
        font-weight:bold;
        text-decoration:none;
    } 
    
    #breadCrumb ul li a:hover {
        padding:5px;
        background-color:#B3C9EF;
        border:1px solid #fff;
        color:#000;
        font-weight:bold;
        text-decoration:none;
    } 
    
    * html #breadCrumb ul li {
        margin:0;
        padding:0;
    }

    * html #breadCrumb ul li b {
        padding:3px;
        display:block;
    }
    
    * html #breadCrumb ul li a, * html #breadCrumb ul li a:hover {
        padding:3px;
        display:block;
        border:1px solid #fff;
        margin-left:4px;
    }

    #infoBox {
       width:100%;
       padding:5px;
       background-color:#FEF3B4;
       color:#000;
       text-align:center;
       font-size:12px;
    }
</style>
<?php if ($isCheckedOut && !$isWorkingCopy) { ?>
<div id="infoBox">
<img src="/images/icons/lock.png" align="absmiddle"><?php echo __('This document is locked by %1% !', array('%1%'=>$checkedOutBy)); ?> 
&nbsp;<a href="javascript:openDetailView('<?php echo $checkoutRefNode->getId(); ?>','<img src=\'<?php echo $checkoutRefNodeImage; ?>\' align=absmiddle border=0> <?php echo $checkoutRefNodeName; ?>');"><?php echo __('Get Working Copy'); ?></a>
</div>        
<?php } else if ($isWorkingCopy) { ?>
<div id="infoBox">
<img src="/images/icons/lock_edit.png" align="absmiddle"><?php echo __('This document is locked for the offline editing by %1% !', array('%1%'=>$checkedOutBy)); ?>
&nbsp;<a href="javascript:openDetailView('<?php echo $checkoutRefNode->getId(); ?>','<img src=\'<?php echo $checkoutRefNodeImage; ?>\' align=absmiddle border=0> <?php echo $checkoutRefNodeName; ?>');"><?php echo __('Get Original Document'); ?></a>
</div>    
<?php } ?>


<?php
$folderPathArray = $sf_data->getRaw('folderPathArray');
if (count($folderPathArray) > 0) { ?>
<div id="breadCrumb">
    <ul>
    <li><b>Path:</b></li>
    <?php
    foreach ($folderPathArray as $path => $js) {
        ?>
        <li><a href="<?php echo $js; ?>">&raquo; <?php echo $path; ?></a></li>
        <?php
    }    
    ?>

    </ul>
    </div>
<?php } ?>
<div id="metaDataView"><ul class="fields">
<?php  
    $row = "rowB";
    
    foreach ($Column1 as $Field) {
        if (!isset($Field["empty"]) && !empty($Field["fieldLabel"])) {
            if ($row == "rowB")
                $row = "rowA";
            else
                $row = "rowB";
            $propName = $Field["name"];
            $value = html_entity_decode($MetaFieldData[$propName],ENT_QUOTES);    
            ?>
            <li class="<?php echo $row;?>">
                <div class="fieldContainer">
                    <label><?php echo $Field["fieldLabel"]; ?>:</label>
                    <div class="value"><?php echo $value; ?></div>
                </div>  
            </li>
            <?php
        }
    }
?>
</ul>
<ul class="fields">
<?php
    $row = "rowB";
    
    foreach ($Column2 as $Field) {
        if (!isset($Field["empty"]) && !empty($Field["fieldLabel"])) {
            if ($row == "rowB")
                $row = "rowA";
            else
                $row = "rowB";
            $propName = $Field["name"];
            $value = html_entity_decode($MetaFieldData[$propName],ENT_QUOTES);    
            ?>
            <li class="<?php echo $row;?>">
                <div class="fieldContainer">
                    <label><?php echo $Field["fieldLabel"]; ?>:</label>
                    <div class="value"><?php echo $value; ?></div>
                </div>  
            </li>
            <?php
        }
    }
?>
</ul>
<?php

if (count($Tabs) > 0) { 
    $TabsItem = 0;
    $TabsItemList = array(); 
    foreach ($Tabs["items"] as $Items) { 
        $TabsItem++;
        ?>
        <ul class="fields fields2" id="tabItem<?php echo $TabsItem.$containerName; ?>">
        <?php
        foreach ($Items["fields"] as $Field) {           
            if (!isset($Field["empty"]) && !empty($Field["fieldLabel"])) {
                if ($row == "rowB")
                    $row = "rowA";
                else
                    $row = "rowB";
                $propName = $Field["name"];
                $value = html_entity_decode($MetaFieldData[$propName],ENT_QUOTES);    
       
                ?>
                <li class="<?php echo $row;?>">
                    <div class="fieldContainer">           
                        <label><?php echo $Field["fieldLabel"]; ?>:</label>
                        <div class="value"><?php echo $value; ?></div>
                    </div>  
                </li>
                <?php
            }
        }
        ?>
        </ul>    

        <script type="text/javascript">  
        var item<?php echo $TabsItem.$containerName; ?> = new Ext.Panel({
            border: false,
            title: '<?php echo $Items["title"]; ?>',
            autoHeight:true,
            contentEl:'tabItem<?php echo $TabsItem.$containerName; ?>'
        }); 
        </script>     

        <?php
        $TabsItemList[] = "item$TabsItem".$containerName;
    }
    $ListJoin = join(", ",$TabsItemList);

    ?>
    <script type="text/javascript">
        var accordion<?php echo $containerName; ?> = new Ext.Panel({                    
            id: 'accordion-panel<?php echo $containerName; ?>',
            layout: 'accordion',
            width: 300, 
            defaults: {bodyStyle: 'padding:0px'},
            collapsible: false,                 
            renderTo:'metaDataEl<?php echo $containerName; ?>',
            items: [<?php echo $ListJoin; ?>]                      
        });


    </script>
    <?php
}
?>
<div id="metaDataEl<?php echo $containerName; ?>" style="float:right;"></div><pre>
<?php
//print_r($Tabs);
//print_r($MetaFields);
?>
</pre></div>   