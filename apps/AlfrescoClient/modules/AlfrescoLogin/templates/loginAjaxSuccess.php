<!--<script type="text/javascript" src="/ysJQueryRevolutionsPlugin/js/jquery/jquery-1.4.js"></script>
<script type="text/javascript" src="/js/form2object.js"></script>
<script type="text/javascript" src="/js/jquery.json-2.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.ternElapse.js"></script>-->
<link rel="stylesheet" type="text/css" href="/css/login.css">
<script type="text/javascript">
    $(document).ready(function() {   

    $("#loginAjaxForm").submit(function() {
    $("#loginError").fadeOut();
    //var dataString = form2object("loginAjaxForm");
    //var jsonData = $.toJSON(dataString);

    $.post("<?php echo url_for('AlfrescoLogin/loginAjax') ?>",$("#loginAjaxForm").serialize(), function(data) {
        var error = true;
        var loggedIn = data.loggedIn;

        if (loggedIn == true) {
                $("#loginWindow").fadeOut(300, function() { $(this).remove(); jQuery(document.body).hideElapsor(); $(".PDFRenderer").show(); });
                checkLoginInterval = window.setInterval(checkloginIntervalFunc, intervalLoginCheck);
        }
        else {
            $("#loginError").fadeIn();
        }
        
    },"json");
    return false;
    // get login[] array of html and send it via post
    
  //alert (dataString);return f;
 /* $.ajax({
    type: "POST",
    url: "<?php echo url_for('AlfrescoLogin/loginAjax') ?>",

    success: function() {
      $('#contact_form').html("<div id='message'></div>");
      $('#message').html("<h2>Contact Form Submitted!</h2>")
      .append("<p>We will be in touch soon.</p>")
      .hide()
      .fadeIn(1500, function() {
        $('#message').append("<img id='checkmark' src='images/check.png' />");
      });
    }
  });*/
  
});
    });
</script>
<div id="loginWindow">
<div id="loginContainer" style="margin-top:0;z-index:12000;">
    <div id="loginBox" style="margin-top:0;">
        <div class="loginTop">
            <div>
                <img src="/images/logo200x106.png" height="106" width="200">
            </div>
        </div>
        
        <div class="loginContent">
            <form id="loginAjaxForm" name="loginAjaxForm" action="" method="POST">
            <?php echo $form['_csrf_token']; ?>
            <ul>
                <?php echo $form ?>
                <li id="loginError" style="margin:0;padding:0;color:#FF0000;display:none;">
                    <?php echo __('Invalid username or password'); ?>
                </li>
                <li>
                    <button type="submit" class="submit" style="z-index:12000;"><?php echo __('Login'); ?></button>
                </li>
            </ul>
            </form>
            <div class="copyright">&copy; 2011 May Computer GmbH. All rights reserved. </div>
        </div>
        
    </div>
</div>    
</div>