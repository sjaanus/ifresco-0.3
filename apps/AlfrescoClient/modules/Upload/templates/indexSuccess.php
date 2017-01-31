<?php use_helper('ysUtil') ?>
<?php use_helper('ysJQueryRevolutions')   ?>



<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready


$(function() {
    var uploader<?php echo $containerName; ?> = $("#uploader<?php echo $containerName; ?>").pluploadQueue({
        // General settings
        runtimes : 'html5,gears,flash,silverlight,browserplus',
        url: '<?php echo url_for('Upload/upload') ?>?nodeId=<?php echo $nodeId; ?>',
        max_file_size : '300mb',
        chunk_size : '10mb',
        unique_names : false,

        // Resize images on clientside if we can
        //resize : {width : 320, height : 240, quality : 90},

        // Specify what files to browse for
        filters : [
            {title : "<?php echo __('Image files'); ?>", extensions : "jpg,gif,png,jpeg,tif,tga,psd,esp,JPG,GIF,PNG,JPEG,TIF,TGA,PSD,ESP"},
            {title : "<?php echo __('Office files'); ?>", extensions : "doc,docx,ppt,pptx,xls,xlsx,odt,ods,odp,odg,odc,odf,odi,ott,ots,otp,otg,msg,eml,DOC,DOCX,PPT,PPTX,XLS,XLSX,ODT,ODS,ODP,ODG,ODC,ODF,ODI,OTT,OTS,OTG,MSG,EML"},
            {title : "<?php echo __('PDF files'); ?>", extensions : "pdf,PDF"},
            {title : "<?php echo __('Text files'); ?>", extensions : "txt,csv,rtf,TXT,CSV,RTF"},
            {title : "<?php echo __('Zip files'); ?>", extensions : "zip,ZIP"},
            {title : "<?php echo __('Video files'); ?>", extensions : "wmv,avi,mpeg,flv,WMV,AVI,MPEG,FLV"},
            {title : "<?php echo __('Misc files'); ?>", extensions : "mp3,mp4,xml,ini,MP3,MP4,XML,INI"}
        ],
        
        // Flash settings
        flash_swf_url : '/js/plupload/plupload.flash.swf',

        // Silverlight settings
        silverlight_xap_url : '/js/plupload/plupload.silverlight.xap'
    });
    
});


function changeUploadId(nodeId) {
    $("#upload-window-nodeid<?php echo $containerName; ?>").val(nodeId);
   // if ($('#uploader<?php echo $containerName; ?>') !== null && typeof $('#uploader<?php echo $containerName; ?>') !== 'undefined')
        $('#uploader<?php echo $containerName; ?>').pluploadQueue().settings.url = '<?php echo url_for('Upload/upload') ?>?nodeId='+nodeId+'&containerName=<?php echo $containerName; ?>';
}


</script>

<form name="" action="">
    <input type="hidden" name="nodeId" id="upload-window-nodeid<?php echo $containerName; ?>" value="<?php echo $nodeId; ?>">
    <div id="uploader<?php echo $containerName; ?>">
        <p><?php echo __("You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support."); ?></p>
    </div>
</form>
            