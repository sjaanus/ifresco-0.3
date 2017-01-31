<?php
    $Settings = $sf_data->getRaw('Settings');            
?>
<style type="text/css">
    #generalSettings {
        overflow:auto;   
    }   
    
    #generalSettings .fields {
        list-style:none;
        margin:0;
        padding:0;
        width:400px;
        float:left;
        display:block;
        margin-right:2px;
    } 
    
    #generalSettings .fields li {
        margin:0;
        padding:0;
        list-style:none;
    }
    
    #generalSettings .fields li .fieldContainer {
        margin:0;
        padding:0;

        font-size:11px;
        border:1px dotted #99BBE8;
        float:left;  
     
        margin-top:1px;
        padding:2px;
        min-height:20px;
        width:400px; 
        margin:2px;
        
    }
    
    #generalSettings .fields li .fieldContainer:hover {
        background-color:#F5E218;                   
    }
    
    #generalSettings .fields li label {
        font-weight:bold;

        border-right:1px dotted #99BBE8;
        float:left; 
        width:120px;
        padding:2px;                 
    }
    
    #generalSettings .fields li .value { 
        background-color:#fff;
        display:block;
        padding:2px;
        float:left;
        width:270px;
        min-height:18px;
    } 
    
    .rowB label, .rowB .value {
        /*color:#585858; */
        color:#000;
    }

    
    .rowA label, .rowA .value {
        color:#000;                          
    }
    
    .rowA .fieldContainer {
        background-color:#E0E8F6;           
    }
    
    .rowB .fieldContainer {
        background-color:#F0F0F0;  
    }

    
    #generalSettings a {
        color:#15428b;
    }

</style>

<script type="text/javascript">
  $(function() {
    $(".submitBtn").click(function() {
        var formSerData = $('#systemSettingsForm').serialize();
        var formData = form2object('systemSettingsForm');
        var jsonData = $.toJSON(formData);
        $.ajax({
          type: 'POST',
          url: "<?php echo url_for('Admin/SaveSystemSettings') ?>",
          data: "data="+jsonData,
          success: function(data){
             data = $.evalJSON(data);
             //$("#generalSettings").unmask();
             $("#adminPanel").unmask();   

             if (data.success == true) {  
                var icon = Ext.MessageBox.INFO;  
                var text = "<?php echo __('Successfully saved the system settings!'); ?>"; 
             }
             else {
                var icon = Ext.MessageBox.ERROR;
                var text = "<?php echo __('An error occured at saving procedure!'); ?>"; 
             }
             
             Ext.MessageBox.show({
               title: '<?php echo __('Icon Support'); ?>',
               msg: text,
               buttons: Ext.MessageBox.OK,
               icon: icon
             });

          },
          beforeSend: function() {
            $("#adminPanel").mask("<?php echo __('Loading...'); ?>",300);         
          }
        });

        return false;
    });
  });
  
</script>
<form name="systemSettingsForm" id="systemSettingsForm" action="" method="post">
<input type="submit" name="submit" value="<?php echo __('Save'); ?>" class="submitBtn">
<div id="generalSettings">
    <ul class="fields"> 
        <li class="rowA">
            <div class="fieldContainer" >
                <label><?php echo __('Disable Renderers:'); ?></label>
                <div class="value">
                    <?php 
                    
                    if (isset($Settings["Renderer"])) {
                        $RendererValue = $Settings["Renderer"];
                    }
                    else
                        $RendererValue = array();

                    foreach ($Renderers as $RenderName => $StdClass) {
                        $MimeTypes = $StdClass->MimeTypes;
                        $Description = $StdClass->Description;
                        
                        $inSetting = false;
                        
                        if (in_array($RenderName,$RendererValue))
                           $inSetting = true;                       
                        ?>                    
                        <div style="float:left;width:100%;">
                            <div style="float:left;width:150px;">
                                <input type="checkbox" name="Renderer[]" <?php echo ($inSetting == true ? 'checked' : ''); ?> value="<?php echo $RenderName; ?>" <?php echo ($MimeTypes == "default" ? 'disabled' : ''); ?>> <b><?php echo $RenderName; ?></b><br><span style="font-size:10px;"><?php echo $Description; ?></span>
                            </div>
                            <div style="float:left;color:#808080;">
                                <br><i><?php echo str_replace(",","<br>",$MimeTypes); ?></i>
                            </div>
                        </div>
                        <br>&nbsp;      
                        <?php         
                    }
                    ?>
                </div>
            </div>  
        </li>
        <li class="rowB">
            <div class="fieldContainer" >
                <label><?php echo __('Default Tab of Node:'); ?></label>
                <div class="value">
                    <?php 
                    
                    $DefaultTab = $Settings["DefaultTab"];

                    foreach ($DefaultTabs as $TabKey => $Tab) {
                        $Text = $Tab["text"];
                        $Description = $Tab["description"];  
                        
                        $inSetting = false;
                        
                        if ($DefaultTab == $TabKey)
                           $inSetting = true;                       
                        ?>                    
                        <div style="float:left;width:100%;">
                            <div style="float:left;width:100%;">
                                <input type="radio" name="DefaultTab" <?php echo ($inSetting == true ? 'checked' : ''); ?> value="<?php echo $TabKey; ?>"> <b><?php echo $Text; ?></b><br><span style="color:#808080;"><i><?php echo $Description; ?></i></span>
                            </div>
                        </div>
                        <br>&nbsp;      
                        <?php        
                    }
                    ?>
                </div>
            </div>  
        </li>
        <li class="rowA">
            <div class="fieldContainer" >
                <label><?php echo __('Date format & time format:'); ?></label>
                <div class="value">
                    <?php 
                    
                    $DateFormat = $Settings["DateFormat"];
                    $TimeFormat = $Settings["TimeFormat"];

                   ?>
                    <div style="float:left;width:100%;">
                        <div style="float:left;width:100%;">
                            <input type="text" name="DateFormat" value="<?php echo $DateFormat; ?>"> <br>
                            <input type="text" name="TimeFormat" value="<?php echo $TimeFormat; ?>"> <br>
                            <span style="color:#808080;"><i>
                            <b><?php echo __('Day'); ?></b><br>
                            d = <?php echo __('Day of the month, 2 digits with leading zeros'); ?><br>
                            D = <?php echo __('A textual representation of a day, three letters'); ?><br>
                            j = <?php echo __('Day of the month without leading zeros'); ?><br>
                            l (<?php echo __("lowercase 'L'"); ?>) = <?php echo __('A full textual representation of the day of the week'); ?><br>
                            N = <?php echo __('ISO-8601 numeric representation of the day of the week'); ?><br>
                            S = <?php echo __('English ordinal suffix for the day of the month, 2 characters'); ?><br>
                            w = <?php echo __('Numeric representation of the day of the week'); ?><br>
                            z = <?php echo __('The day of the year (starting from 0)'); ?><br>
                            
                            <br><b><?php echo __('Week'); ?></b><br>
                            W = <?php echo __('ISO-8601 week number of year, weeks starting on Monday'); ?><br>

                            <br><b><?php echo __('Month'); ?></b><br>
                            F = <?php echo __('A full textual representation of a month, such as January or March'); ?><br>
                            m = <?php echo __('Numeric representation of a month, with leading zeros'); ?><br>
                            M = <?php echo __('A short textual representation of a month, three letters'); ?><br>
                            n = <?php echo __('Numeric representation of a month, without leading zeros'); ?><br>
                            t = <?php echo __('Number of days in the given month'); ?><br>
                            
                            <br><b><?php echo __('Year'); ?></b><br>
                            L = <?php echo __("Whether it's a leap year"); ?><br>
                            o = <?php echo __('ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. '); ?><br>
                            Y = <?php echo __('A full numeric representation of a year, 4 digits'); ?><br>
                            y = <?php echo __('A two digit representation of a year'); ?><br>

                            <br><b><?php echo __('Time'); ?></b><br>
                            a = <?php echo __('Lowercase Ante meridiem and Post meridiem'); ?><br>
                            A = <?php echo __('Uppercase Ante meridiem and Post meridiem'); ?><br>
                            B = <?php echo __('Swatch Internet time'); ?><br>
                            g = <?php echo __('12-hour format of an hour without leading zeros'); ?><br>
                            G = <?php echo __('24-hour format of an hour without leading zeros'); ?><br>
                            h = <?php echo __('12-hour format of an hour with leading zeros'); ?><br>
                            H = <?php echo __('24-hour format of an hour with leading zeros'); ?><br>
                            i = <?php echo __('Minutes with leading zeros'); ?><br>
                            s = <?php echo __('Seconds, with leading zeros'); ?><br>
                            u = <?php echo __('Microseconds'); ?><br>
                            
                            <br><b><?php echo __('Full Date/Time'); ?></b><br>
                            c = <?php echo __('ISO 8601 date'); ?><br>
                            r = <?php echo __('RFC 2822 formatted date'); ?><br>
                            U = <?php echo __('Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)'); ?><br>
                            </i></span>
                        </div>
                    </div>   
                </div>
            </div>  
        </li>
    </ul>
</div>     
</form> 