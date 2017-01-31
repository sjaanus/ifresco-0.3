<html>
<head>
<title>Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="/css/login.css">
</head>
<body>
<div align="center">
    <div id="loginContainer">
        <div id="loginBox">
            <div class="loginTop">
                <div>
                    <!--<h1><span class="blue">Alfresco</span> <span class="grey">Client</span> <span class="lightgrey">Login</span></h1>-->
                    <img src="/images/logo200x106.png" height="106" width="200">
                </div>
            </div>
            <div class="loginContent">
                <form action="<?php echo url_for('AlfrescoLogin/login') ?>" method="POST">
                <?php echo $form['_csrf_token']; ?>
                <ul>
                    <?php echo $form ?>
                    <li>
                        <label for="lang"><?php echo __('Language'); ?></label>
                        <select name="lang">
                            <?php 

                            foreach ($Languages as $langKey => $language) { 
                                //if (!preg_match("/_/eis",$language))
                                //    continue;    
                                
                                ?>
                                <option value="<?php echo $langKey; ?>" <?php echo ($LanguageDefault == $langKey ? "selected" : ""); ?>><?php echo $language; ?></option>
                            <?php } ?>
                        </select>
                    </li>
                    <li>
                        <button type="submit" class="submit"><?php echo __('Login'); ?></button>
                    </li>
                </ul>
                </form>
                <div class="copyright">&copy; 2011 May Computer GmbH. All rights reserved. <i>ifresco client version <?php echo $ifrescoVersion; ?></i></div>
            </div>
            
        </div>
    </div>    
</div>
</body>
</html>
