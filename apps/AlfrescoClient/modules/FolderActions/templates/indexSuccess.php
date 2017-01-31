<?php 
use_helper('ysJQueryRevolutions');
use_helper('ysJQueryAutocomplete');
use_helper('ysJQueryUIDialog');
?>
<link href="/css/checkbox.css" type="text/css" rel="stylesheet">
<style>


#spaceForm {
    
}

#spaceForm .formUl {
    list-style:none;
    padding:0;
    margin:0;
    
}

#spaceForm .formUl li {
    float:left;
    padding-right:50px;
    padding-left:50px;
    border-left:1px solid #F0F0F0;
}

#spaceForm .formUl li li {
    float:none;
    padding:0;
    border:none;
}

#spaceForm .first {
    padding:0;
    border:none;
    padding-left:10px;
    
}

#spaceForm .metaDataInput {
    
    width:170px;
    padding:5px;
    border:1px solid #ddd;
    background:#fafafa;
    font:12px Verdana,sans-serif;
    -moz-border-radius:0.4em;
    -khtml-border-radius:0.4em;
    text-align:left;
}

#spaceForm h2 {
    color:#039CE4;  
    font-family: Verdana,sans-serif;
    font-weight:normal;
    margin:0;
    padding:0;
    margin-bottom:5px;
}
#spaceForm .metaDataInput:hover, .metaDataInput:focus {
    border-color:#c5c5c5;
    background:#f6f6f6;
} 

#spaceForm label {
    display:block;
    padding:2px;
    color:#585858;
    font:12px Verdana,sans-serif;
    
}

#spaceForm .iPhoneCheckContainer label {
    white-space: nowrap;
    font-size: 17px;
    line-height: 17px;
    font-weight: bold;
    font-family: Helvetica Neue, Arial, Helvetica, sans-serif;
    text-transform: uppercase;
    cursor: pointer;
    display: block;
    height: 27px;
    position: absolute;
    width: auto;
    top: 0;
    padding-top: 5px;
    overflow: hidden; 
}

#spaceForm label.iPhoneCheckLabelOn {
    color: #fff;
    background: url(/images/checkbox/on.png) no-repeat;
    text-shadow: 0px 0px 2px rgba(0, 0, 0, 0.6);
    left: 0;
    padding-top: 5px; 
}

#spaceForm .submit {
    float:left;
    width:100%;
    padding-left:10px;
    margin-top:30px;

}

#spaceForm .form_row {
    margin-top:5px;
}

</style>

<form action="" method="post" id="spaceCreateForm">
<div id="spaceForm">
    <ul class="formUl">
        <li class="first" style="padding-left:10px;border:0;">
      <?php echo $form ?>
        </li>
    </ul>
    <div class="submit">
        <!--<button type="submit" />Save</button>-->
    </div>
</div>
</form>