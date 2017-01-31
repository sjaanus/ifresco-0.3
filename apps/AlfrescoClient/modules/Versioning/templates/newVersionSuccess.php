
<style type="text/css">
#NewVersionContainer {

    background-color:#e0e8f6;
    height:150px;
    width:500px; 
    font-family:arial; 
}

#NewVersionContainer label {
    font-size:12px;
    color:#808080;
    padding-left:10px; 
    margin:0;
    font-family:arial;
    display:block;
    padding-top:5px; 
    font-weight:bold;
    padding-bottom:5px;
} 

#NewVersionContainer textarea {
    border:1px dashed #808080;   
    background-color:#fff; 
    color:#808080;
    font-family:arial;
    font-size:12px;
}

#NewVersionContainer .fields {
    font-family:arial;
    color:#808080;
    font-size:12px;
    padding-left:20px;
    padding-right:20px;
}

#NewVersionContainer .uploadContainer {
    background-color:#fff;
    border:3px dashed #808080;
    height:80px;
    margin:20px;
    margin-bottom:10px;
    width:250px;
    float: right;
}

#NewVersionContainer .uploadContainer .text {
    text-align:center;
    font-size:16px;
    color:#808080;
    font-weight:normal;
    padding-top:30px;
}

#uploadZone .buttons a {
    border:1px solid #808080;  
    background-color:#fff;
    font-size:14px;
    font-family:arial;
    margin-right:20px;
    padding:4px;
    color:#808080;
    float:right;
    text-decoration:none;
}



#NewVersionContainer .uploadContainer .fileItem {
    float:left;
    width:240px;
    padding:5px;
    background-color:#FEF3B4;
    border-bottom:1px solid #fff;
    font-size:12px;
}

#NewVersionContainer .uploadContainer .fileItem .name {
    float:left;
}

#NewVersionContainer .uploadContainer .fileItem .cancel {
    float:right;
}

#NewVersionContainer .uploadContainer .fileInfo {
    float:left;
    background-color:#FEF3B4;
    width:240px;
    padding:5px;
    font-size:10px;
}

#NewVersionContainer .uploadContainer .fileInfo .size {
    
}

#NewVersionContainer .uploadContainer .fileInfo .uploadStatus {
    border-top:1px solid #fff;
    background-color:#FEF3B4;
    float:right;
    
}
</style>

<script type="text/javascript">
var uploader = null;
var windowHandle = null;
var versionGrid = null;
var localNodeId = null;
$(document).ready(function() {      
    $('#newVersionWindowForm').submit(function() {
     return false;   
    });
});

<?php if ($EnableUpload) { ?> 
$(function() {
    $("#uploadNewVersionBtn").hide();
    
    uploader = new plupload.Uploader({
        runtimes : 'html5,gears,flash,silverlight,browserplus',
        browse_button : 'selectNewVersionBtn',
        drop_element: "uploadNewVersionElement",
        max_file_size : '300mb',
        chunk_size : '10mb',
        unique_names : false,
        url: '<?php echo url_for('Versioning/UploadNewVersion') ?>',
        <?php if (!empty($filter)) { ?>
        filters:[<?php echo $sf_data->getRaw('filter'); ?>],
        <?php } ?>
        
        flash_swf_url : '/js/plupload/plupload.flash.swf',
        silverlight_xap_url : '/js/plupload/plupload.silverlight.xap'
    });
        
        
    uploader.bind('Init', function(up, params){
    try{
        if(!!FileReader && !((params.runtime == "flash") || (params.runtime == "silverlight")))
            $("#uploadContainer .text").show();
    }
    catch(err){}});
    
    /*$('#uploadNewVersionBtn').click(function(e) {
        uploader.start();
        e.preventDefault();
    });*/
    
    
    uploader.init();
    
    uploader.bind('FilesAdded', function(up, files) {
        $.each(files, function(i, file) {
            if (uploader.files.length == 1) {
                $('#uploadNewVersionElement').html(
                    '<div id="' + file.id + '" class="fileItem"><div class="name">' +
                    file.name + '</div><a href="#" id="cancel'+file.id+'" class="cancel"><img src="/images/toolbar/cross.png" align="absmiddle" border="0"></a></div><div class="fileInfo"><span class="size"><b><?php echo __('Size:'); ?></b> ' + plupload.formatSize(file.size) + '</span>' +
                    '<div id="' + file.id + '_uploadStatus" class="uploadStatus">0%</div>');

                //Fire Upload Event
                up.refresh(); // Reposition Flash/Silverlight
                
                $('#cancel'+file.id).click(function(){
                    $("#uploadNewVersionBtn").hide();
                    $fileItem = $('#' + file.id);
                    $("#uploadNewVersionElement").html('<div class="text"><?php echo __('Drag and Drop a file here %1%', array('%1'=>(!empty($fileExt) ? '(.'.$fileExt.')' : ''))); ?></div>');
                    uploader.removeFile(file);
                    uploader.refresh();   
                    $(this).unbind().remove();
                    
                });

                $("#uploadNewVersionBtn").show();
            }
            else {
                uploader.removeFile(file);
                uploader.refresh();   
            }
        });
    });
    
    uploader.bind('UploadProgress', function(up, file) {
        $('#' + file.id + "_uploadStatus").html(file.percent + "%");
    });

    uploader.bind('FileUploaded', function(up, file) {
        $("#NewVersionContainer").unmask();
        $('#' + file.id + "_uploadStatus").html("100%");
        
        versionGrid.store.load({params:{'nodeId':localNodeId}});
        windowHandle.hide();
        
    });

    uploader.bind('Error', function(up, err) {
        $('#uploadContainer').css("background-color","#910000");
        $('#uploadContainer').css("color","#fff");

        up.refresh(); // Reposition Flash/Silverlight
    });
});

function uploadNewVersion(nodeId,window,grid) {
    localNodeId = nodeId;
    windowHandle = window;
    versionGrid = grid;
    uploader.start();
    $("#NewVersionContainer").mask("<?php echo __('Saving...'); ?>",300);
    //$("#uploadNewVersionBtn").preventDefault();
}
<?php } ?> 

function getVersionWindowInfo(nodeId) {  
    var formData = form2object('newVersionWindowForm');
    var newVersionNote = $("#newVersionNote").val();
    newVersionNote = $.URLEncode(newVersionNote);
    <?php if ($ShowVersionInfo) { ?> 
    var newVersionChange = formData.newVersionChange;  
    <?php } else { ?>
    var newVersionChange = "major";
    <?php } ?>
    
    
    <?php if (!$EnableUpload) { ?> 
        var postData = {note:newVersionNote,versionchange:newVersionChange};
    <?php } else { ?>
        var postData = {note:newVersionNote,versionchange:newVersionChange};
        uploader.settings.url = uploader.settings.url+"?nodeId="+nodeId+"&note="+newVersionNote+"&versionchange="+newVersionChange;
        
    <?php } ?>
    return postData;  
}
</script>

<div id="NewVersionContainer">
    <form name="newVersionWindowForm" id="newVersionWindowForm" action="" method="post">    
    <?php if ($EnableUpload) { ?> 
    <div style="float:left;width:40%;">
    <?php } ?>
    <label><?php echo __('Note:'); ?></label>
    <div class="fields"><textarea name="newVersionNote" id="newVersionNote" style="<?php echo (!$EnableUpload ? 'width:100%;' : ''); ?><?php echo (!$ShowVersionInfo ? 'height:120px;' : ''); ?>"></textarea><br></div>
    
    
    <?php if ($ShowVersionInfo) { ?> 
    <label><?php echo __('Version:'); ?></label>
    <div class="fields">
    <input type="radio" name="newVersionChange" value="minor" checked> <?php echo __('Minor Change'); ?><br>
    <input type="radio" name="newVersionChange" value="major"> <?php echo __('Major Change'); ?></div>
    
    <?php
    }
    ?>
    
    <?php if ($EnableUpload) { ?> 
    </div>
    <div style="float:left;width:60%;height:100px;" id="uploadZone">
        <div class="uploadContainer" id="uploadNewVersionElement">
            <div class="text" style="z-index:1;"><?php echo __('Drag and Drop a file here %1%', array('%1%'=>(!empty($fileExt) ? '(.'.$fileExt.')' : ''))); ?></div>
        </div> 
        <div class="buttons">
            <a href="#" id="selectNewVersionBtn"><?php echo __('Select a new file'); ?></a>
            <!--<a href="#" style="float: left;margin-left:20px;" id="uploadNewVersionBtn"><img src="/images/toolbar/page_white_get.png" align="absmiddle" border="0"> Upload</a>-->
        </div>
    </div>
    <?php } ?>
    
    </form>
    
</div>