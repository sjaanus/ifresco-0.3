<?php
    $isAdmin = $sf_user->isAdmin();
    $Culture = $sf_user->getCulture();
?>
<html>
<head>
  <title>ifresco client - document management by May Computer - powered by Alfresco</title>                     
    <link rel="stylesheet" type="text/css" href="/css/style.css" />
    <link rel="shortcut icon" href="/images/favicon.ico" />
      <?php use_helper('ysJQueryRevolutions'); ?>
      <?php use_helper('ysJQueryUILayout'); ?>
      <?php use_helper('ysJQueryUIMenu'); ?>
      <?php use_helper('ysJQueryUIDraggable'); ?>
      <?php use_helper('ysJQueryUIDialog'); ?>
      <?php use_helper('ysJQueryUICore'); ?>
      <?php use_helper('ysUtil') ?>
      <?php use_helper('ysJQueryAutocomplete') ?>
      <?php use_helper('ysJQueryUIAccordion')  ?>
      <?php use_helper('ysJQueryUISortable') ?>
      <?php ui_add_effects_support(array('bounce','slide','drop','scale'))   ?>
      <?php use_stylesheet('/js/extjs/shared/icons/silk.css'); ?>
      <?php use_stylesheet('/css/jquery.loadmask.css');   ?>
      <?php use_javascript('/js/jquery.loadmask.min.js'); ?>
      <?php use_javascript('/js/jquery.urlEncode.js'); ?>
      <?php use_javascript('/js/jquery.ternElapse.js'); ?>
      <?php use_javascript('/js/extjs/adapter/ext/ext-base.js'); ?>
      <?php use_javascript('/js/extjs/ext-all.js'); ?>
      
      <?php 
      use_javascript('/js/extjs/src/locale/ext-lang-'.$Culture.'.js'); 
      ?>
      <?php use_javascript('/js/extjs/ux/RowExpander.js'); ?>
    <?php use_javascript('/js/extjs/ux/TabCloseMenu.js'); ?>
    <?php use_javascript('/js/extjs/ux/TabScrollerMenu.js'); ?>
    <?php use_javascript('/js/extjs/ux/MultiSelect.js'); ?>
    <?php use_javascript('/js/extjs/ux/ItemSelector.js'); ?>
    <?php use_stylesheet('/js/extjs/ux/css/MultiSelect.css'); ?>
    <?php use_javascript('/js/extjs/ux/maximgb/TreeGrid.js'); ?>
    <?php use_stylesheet('/js/extjs/ux/maximgb/css/TreeGrid.css'); ?>
    <?php use_stylesheet('/js/extjs/ux/css/Portal.css'); ?>
    <?php use_stylesheet('/js/extjs/ux/css/GroupTab.css'); ?>
    <?php use_javascript('/js/extjs/ux/GroupTabPanel.js'); ?>
    <?php use_javascript('/js/extjs/ux/GroupTab.js'); ?>
    <?php use_javascript('/js/extjs/ux/Portal.js'); ?>
    <?php use_javascript('/js/extjs/ux/PortalColumn.js'); ?>
    <?php use_javascript('/js/extjs/ux/Portlet.js'); ?>
    <?php use_javascript('/js/extjs/ux/RowEditor.js'); ?>
    <?php use_javascript('/js/extjs/ux/CheckTreePanel.js'); ?>
    <?php use_stylesheet('/js/extjs/ux/css/RowEditor.css'); ?>
    <?php use_stylesheet('/js/extjs/ux/css/TabScrollMenu.css'); ?>
    <?php use_stylesheet('/js/SuperBoxSelect/superboxselect.css'); ?>
<?php use_javascript('/js/SuperBoxSelect/SuperBoxSelect.js'); ?>
    <?php use_javascript('/js/extjs/ux/MetaForm.js'); ?>
    <?php use_javascript('/js/extjs/ux/DateTime.js'); ?>
    <?php use_javascript('/js/extjs/ux/MultipleEmail.js'); ?>
    <?php use_javascript('/js/swfobject/swfobject.js'); ?>
    <?php use_javascript('/js/jquery.asmselect.js'); ?>
    <?php use_stylesheet('/css/jquery.asmselect.css'); ?>  
    <?php use_stylesheet('/js/extjs/resources/css/ext-all.css'); ?>
    <?php use_stylesheet('/css/plupload.queue.css'); ?>
    <?php use_javascript('/js/plupload/gears_init.js'); ?>
    <?php use_javascript('/js/browserplus-min.js'); ?>
        <?php use_javascript('/js/plupload/plupload.js'); ?>
        <?php use_javascript('/js/plupload/plupload.gears.js'); ?>
        <?php use_javascript('/js/plupload/plupload.silverlight.js'); ?>
        <?php use_javascript('/js/plupload/plupload.flash.js'); ?>
        <?php use_javascript('/js/plupload/plupload.browserplus.js'); ?>
        <?php use_javascript('/js/plupload/plupload.html4.js'); ?>
        <?php use_javascript('/js/plupload/plupload.html5.js'); ?>
        <?php use_javascript('/js/plupload/jquery.plupload.queue.js'); ?>
        <?php use_javascript('/js/jquery.json-2.2.min.js'); ?>
        <?php use_javascript('/js/jquery.simplejson.js'); ?>
        <?php use_javascript('/js/form2object.js'); ?>
        <?php use_javascript('/js/zeroclipboard/ZeroClipboard.js'); ?>
        <?php use_javascript('/js/Extjs.BorderOverwrite.js'); ?>
        <?php use_stylesheet('/js/tagit/css/jquery-ui/jquery.ui.autocomplete.custom.css'); ?>
        <?php //use_javascript('/js/tagit/js/jquery-ui/jquery-ui-1.8.core-and-interactions.min.js'); ?>
        <?php //use_javascript('/js/tagit/js/jquery-ui/jquery-ui-1.8.autocomplete.min.js'); ?>
        <?php use_javascript('/js/tagit/js/tag-it.js'); ?>
        <?php use_javascript('/js/base64.js'); ?>
        <?php use_javascript('/js/date.format.js'); ?>
    <?php 
        use_stylesheet('/css/datagrid.css'); 
    ?>
        <?php 
        use_javascript('/js/ifresco/cookies.js'); 
        use_javascript('/js/ifresco/registry.js'); 
        ?>
      <?php include_http_metas() ?>
      <?php include_metas() ?>
      <?php include_title() ?>
      <?php include_stylesheets() ?>
      <?php include_javascripts() ?>     

    <script type="text/javascript">
    Registry.getInstance().read();
    if (typeof Registry.getInstance().get("ColumnsetId") == 'undefined')
        Registry.getInstance().set("ColumnsetId",0);
    if (typeof Registry.getInstance().get("ArrangeList") == 'undefined')
        Registry.getInstance().set("ArrangeList","horizontal");
    if (typeof Registry.getInstance().get("BrowseSubCategories") == 'undefined')
        Registry.getInstance().set("BrowseSubCategories",false);

    var UserColumnsetId = Registry.getInstance().get("ColumnsetId");
    var ArrangeList = Registry.getInstance().get("ArrangeList");
    var BrowseSubCategories = Registry.getInstance().get("BrowseSubCategories");
          
    var intervalLoginCheck = 30000;
    var checkLoginInterval = null;
    $(document).ready(function() {    
        if (ArrangeList == "horizontal")
            $("#verticalBtn").addClass("disabled");
        else
            $("#horizontalBtn").addClass("disabled");
    });
    

    function checkloginIntervalFunc() {

        $.ajax({ 
            cache: false,  
            url: "<?php echo url_for('AlfrescoLogin/IsLoggedin') ?>",
            error: function(event, request, options, error) {
                switch (event.status) {
                    case 401: 
                        jQuery(document.body).elapsor({
                              color:'#000',
                              opacity:85,
                              func : function () {
                                  $(".PDFRenderer").hide();
                                   //jQuery(document.body).append('<div id="loginWindow" style="z-index:10000;position:absolute;top:30%;left:50%;width:530px;height:210px;background-color:#eeeeee;"></div>');
                                   jQuery(document.body).append('<div id="loginWindow" style="z-index:10000;position:absolute;top:30%;left:30%;width:500px;height:200px;"></div>');
                                    $.ajax({
                                        cache: false,  
                                        url : "<?php echo url_for('AlfrescoLogin/loginAjax') ?>",

                                        success : function (data) {
                                            $("#loginWindow").append(data).fadeIn();
                                        },
                                        beforeSend: function(req) {
                                            
                                        } 
                                    });
                                    window.clearInterval(checkLoginInterval);
                              }
                        });
                    break;
                }
            }

        });
    }
    
    function arrangeWindows(where) {
        var verticalBtn = $("#verticalBtn");
        var horizontalBtn = $("#horizontalBtn");

        if (Registry.getInstance().get("ArrangeList") != where) {
            if (where == "horizontal") {
               verticalBtn.addClass("disabled");
               horizontalBtn.removeClass("disabled");
               Registry.getInstance().set("ArrangeList","horizontal");
               Registry.getInstance().save();
               ArrangeList = "horizontal";
            }
            else {
               horizontalBtn.addClass("disabled");
               verticalBtn.removeClass("disabled"); 
               Registry.getInstance().set("ArrangeList","vertical");
               Registry.getInstance().save();
               ArrangeList = "vertical";
            }
        }
    }
    
    Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
         onRender : function(ct, position){
              this.el = ct.createChild({tag: 'iframe', id: 'iframe-'+ this.id, frameBorder: 0, src: this.url});
         }
    });

    Ext.onReady(function(){
        Ext.History.init();
        var tokenDelimiter = '/';

        
        var xd = Ext.data;    
        Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
        Ext.QuickTips.init();

        
        var scrollerMenu = new Ext.ux.TabScrollerMenu({
            maxText  : 1000,
            pageSize : 5
        });

        
        var contentTabs = new Ext.TabPanel({
            region: 'center', 
            deferredRender: false,
            id:'content-tabs',
            activeTab: 0,  
            enableTabScroll : true,
            resizeTabs      : false,  

            border          : true,

            plugins         : [ scrollerMenu ],
            listeners: {
                'tabchange': function(tabPanel, tab){
                    if (typeof tab != 'undefined')
                        Ext.History.add(tabPanel.id + tokenDelimiter + tab.id);
                }
            },

            items: [{
                contentEl: 'dashboardTab',
                id:'dashboard-tab',
                title: '<img src="/images/house.png" valign="absmiddle" border="0"> Home',
                bodyStyle:'background-color:#006AB3;',
                closable: false,
                autoScroll: true
            }]
        });
        
        var viewport = new Ext.Viewport({
            layout: 'border',
            items: [
            
            new Ext.BoxComponent({
                region: 'north',
                height: 60,
                contentEl: 'north'
            }), {
                region: 'west',
                id: 'west-panel',
                title: '<?php echo __('Navigation'); ?>',
                split: true,
                width: 250,
                minSize: 175,
                maxSize: 400,
                collapsible: true,
                margins: '0 0 0 0',
                layout: {
                    type: 'accordion',
                    animate: true
                },
                items: [{
                    el: 'foldersTree',
                    title: '<?php echo __('Folders'); ?>',
                    border: false,
                    iconCls: 'foldersTree'
                    
                }, {
                    el:'categoriesTree',
                    title: '<?php echo __('Categories'); ?>',
                    border: false,
                    iconCls: 'categoriesTree'
                }, {
                    el: 'favTree',
                    title: '<?php echo __('Favorites'); ?>',
                    border: false,
                    iconCls: 'favTree'
                }, {
                    title: '<?php echo __('Tags'); ?>',
                    border: false,
                    iconCls: 'tagScope',
                    contentEl: 'tagScope',
                    tbar:[
                      '->',{
                        iconCls:'refresh-icon',
                        tooltip: '<?php echo __('Refresh'); ?>',  
                         handler: function(){ 
                             
                             getTagScope();
                         },        
                         scope: this
                    }]
                }]
        
            },
            contentTabs]
        });

        
        Ext.History.on('change', function(token){
            var token = token || "";   
            var parts = new Array();   
            
            if (token.length > 0) {
                parts = token.split(tokenDelimiter);  
            }
            else
                parts[0] = "";
                
            var action = parts[0];
            
            switch (action) {
                case "document-details":
                    if (parts.length >= 2) {
                        var nodeId = parts[1]; 
                        if (nodeId.length > 0) { 
                            var nodeType = "file"; 
                            
                            $.get("<?php echo url_for('Metadata/GetNameOfNode') ?>", "nodeId="+nodeId, function(data) {
                                var success = data.success;
                                if (success == true) {
                                    var nodeImgText = data.imgName;
                                    alfDocument(nodeId,nodeType,nodeImgText);                   
                                }
                            }, "json");
                            
                            
                        }
                    }
                break;
                case "folder-view":
                    if (parts.length >= 2) {
                        var nodeId = parts[1]; 
                        if (nodeId.length > 0) {
                            var nodeType = "folder"; 
                            $.get("<?php echo url_for('Metadata/GetNameOfNode') ?>", "nodeId="+nodeId, function(data) {
                                var success = data.success;
                                if (success == true) {
                                    var nodeImgText = data.imgName;
                                    alfDocument(nodeId,nodeType,nodeImgText);                   
                                }
                            }, "json")          
                        }
                    }
                break;
                case "category-view":
                    if (parts.length >= 2) {
                        var category = parts[1]; 
                        if (category.length > 0) {
                            var nodeType = "category"; 
                            alfDocument(category,nodeType,category);          
                        }
                    }
                break;
                case "":
                    
                break;
                default:
                    
                    var tabPanel = Ext.getCmp(parts[0]);
                    var tabId = parts[1];

                    if (tabExists(tabId)) {                        
                        tabPanel.show();
                        tabPanel.setActiveTab(tabId);
                    }
                break;   
            }
        });
    });
    
    function strip_tags(input, allowed) {
           allowed = (((allowed || "") + "")
              .toLowerCase()
              .match(/<[a-z][a-z0-9]*>/g) || [])
              .join(''); 
              var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
               commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
           return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1){
              return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
           });        
    }
    
    function addTab(tabTitle, targetUrl){
        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            tabPanel.add({
                title: tabTitle,
                autoLoad: {url: targetUrl, callback: this.initSearch, scope: this},
                closable:true
            }).show();
        }
    }
    
    function addTabFixed(tabId,tabTitle){
            var tabPanel = Ext.getCmp('content-tabs');
            if (tabPanel) {
                tabPanel.add({
                    title: tabTitle,
                    id:tabId,
                    closable:true
                }).show();
            }

    }
    
    function addTabDynamic(tabId,tabTitle){
            var tabPanel = Ext.getCmp('content-tabs');
            if (tabPanel) {
                tabPanel.add({
                    title: tabTitle,
                    id:tabId,
                    closable:true
                }).show();
            }

    }
    
    function addTabDynamicLoad(tabId,tabTitle,autoLoad){
            var tabPanel = Ext.getCmp('content-tabs');
            if (tabPanel) {
                tabPanel.add({
                    title: tabTitle,
                    id:tabId,
                    autoLoad:autoLoad,
                    closable:true
                }).show();
            }

    }
    
    function getActiveContentTab() {
        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            return tabPanel.getActiveTab();
        }
        return null;
    }
    
    function closeActiveContentTab() {
        var activeTab = getActiveContentTab();   
        if (activeTab != null) {
            removeContentTab(activeTab.id);       
        }
    }
    
    function removeContentTab(tabId) {
        if (tabExists(tabId)) {
            var tabPanel = Ext.getCmp('content-tabs');
            if (tabPanel) {
                tabPanel.remove(tabId);      
            }    
        }
    }
 
    
    function updateTab(tabId,title, url) {
        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            var tab = tabPanel.getItem(tabId);
            if(tab){
                tab.getUpdater().update(url);
                tab.setTitle(title);
            }else{
                tab = addTab(title,url);
            }
            tabPanel.setActiveTab(tab);
        }
    }
    
    function setActive(tabId) {
        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            var tab = tabPanel.getItem(tabId);
            if(tab)
                tabPanel.setActiveTab(tab);
        }
    }
    
    function tabExists(tabId) {
        var tabPanel = Ext.getCmp('content-tabs');
        if (tabPanel) {
            var tab = tabPanel.getItem(tabId);
            if(tab)
                return true;
        }
        return false;
    }
 

    <?php if ($isAdmin == true) { ?>
    function openAdmin() {  
        if (!tabExists('admintab')) {
            addTabDynamic('admintab',"<?php echo __('Administration'); ?>");
            
        
            $.ajax({
                cache: false,  
                url : "<?php echo url_for('Admin/index') ?>",
 
                success : function (data) {
                    $("#admintab").html(data);
                }    
            });
        }
        setActive('admintab');
    }
    <?php
    }
    ?>
    
    function openTab(tabname,tabtitle,url) {
        if (!tabExists(tabname)) {
            addTabDynamic(tabname,tabtitle);

            $.ajax({
                cache: false,  
                url : url,
 
                success : function (data) {
                    
                    $("#"+tabname).html(data);
                    $("#overAll").unmask();
                },
                beforeSend: function(req) {
                    $("#overAll").mask("<?php echo __('Loading'); ?> "+tabtitle+"...",300);    
                }
                 
            });
        }
        setActive(tabname);
    }
    
    function addFavorite(nodeId,nodeText,nodeType) {
        nodeText = strip_tags(nodeText,"");
        $.post("<?php echo url_for('UserSpecific/AddFavorite') ?>", "nodeId="+nodeId+"&nodeText="+nodeText+"&nodeType="+nodeType, function(data) {
            var success = data.success;
            if (success == true) {
                
                var favTree = Ext.getCmp('favTree');
                if (favTree) {
                    favTree.getRootNode().reload();
                    favTree.render();
                }
            }
            else {
                /*Ext.MessageBox.show({
                   title: 'Error',
                   msg: data.message,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.WARNING
               });*/
            }
        }, "json");    
    }
    
    function removeFavoriteById(id) {
        removeFavorite(id,'favId');    
    }
    
    function removeFavoriteByNodeId(id) {
        removeFavorite(id,'nodeId');     
    }
    
    function removeFavorite(id,type) {
        $.post("<?php echo url_for('UserSpecific/RemoveFavorite') ?>", type+"="+id, function(data) {
            var success = data.success;
            if (success == true) {
                
                var favTree = Ext.getCmp('favTree');
                if (favTree) {
                    favTree.getRootNode().reload();
                    favTree.render();
                }
            }
            else {

            }
        }, "json");    
    }
    
    function alfDocument(nodeId,nodeType,nodeImgText,tabnodeid,containerName) {
        if (typeof tabnodeId=='undefined') {
            var tabnodeid = nodeId.replace(/-/g,"");                 
        }
        
        if (typeof containerName=='undefined')
            var containerName = "";
        
        tabnodeid = tabnodeid.replace(/ /g,"");                 
        containerName = containerName.replace(/ /g,"");                 
        
        /*if (typeof node.attributes.nodeId != 'undefined')  
            var tabnodeid = node.attributes.nodeId.replace(/-/g,""); 
        else  
            var tabnodeid = nodeId.replace(/-/g,"");    */
            
        var createTab = false;          
        if (!tabExists('tab-'+tabnodeid)) {
            addTabDynamic('tab-'+tabnodeid,nodeImgText);
            createTab = true;
        }
        setActive('tab-'+tabnodeid);
        
        if (nodeType == "file") {
             $.ajax({
                cache: false,  
                url : "<?php echo url_for('DataGrid/detailView') ?>?nodeId="+nodeId,
                data: ({'nodeId' : nodeId}),

                
                success : function (data) {
                    $("#overAll").unmask();
                    $("#tab-"+tabnodeid).html(data);
                    
                },
                beforeSend: function(req) {
                    $("#overAll").mask("Loading "+nodeImgText+"...",300);    
                }  
            });
        }
        else if(nodeType == "folder" || nodeType == "category" || nodeType == "tag") {

            if (createTab == true) {
                $.ajax({
                    cache: false,  
                    url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid+"&addContainer="+containerName+"&columnsetid="+UserColumnsetId,

                    success : function (data) {
                        $("#overAll").unmask();
                        $("#tab-"+tabnodeid).html(data);

                        if (nodeType == "folder")
                           eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
                        else if (nodeType == "category")
                            eval("reloadGridData"+tabnodeid+"({params:{'subCategories':false,'fromTree':false,'categoryNodeId':nodeId,'categories':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");    
                    },
                    beforeSend: function(req) {
                        $("#overAll").mask("Loading "+nodeImgText+"...",300);    
                    } 
                });   
            }
            else {
                if (nodeType == "folder")
                    eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");
                else if (nodeType == "category")
                    eval("reloadGridData"+tabnodeid+"({params:{'subCategories':Registry.getInstance().get(\"BrowseSubCategories\"),'categories':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");                       
            }      
        }
    }
    
    function openDetailView(nodeId,nodeName) {
        var tabnodeid = nodeId.replace(/[-., ]/g,"");              
        addTabDynamic('tab-'+tabnodeid,nodeName);
                        
        $.ajax({
            cache: false,  
            url : "<?php echo url_for('DataGrid/detailView') ?>",
            data: ({'nodeId' : nodeId}),

            
            success : function (data) {
                $("#overAll").unmask();
                $("#tab-"+tabnodeid).html(data);
            },
            beforeSend: function(req) {
                $("#overAll").mask("Loading "+nodeName+"...",300);    
            }  
        });  
    }

    function openCategory(nodeId,nodeName,subCategories,fromTree) {
        if (typeof subCategories == 'undefined')
            subCategories = "false";
        if (typeof fromTree == 'undefined')
            fromTree = "false";
          var tabnodeid = nodeId.replace(/[-.,:\+/\\ ]/g,"");    
                    
          tabnodeid = tabnodeid.replace(/%[a-z0-9A-Z][a-z0-9A-Z]/g,"");     
          console.log("nodeidtab "+tabnodeid);         
          addTabDynamic('tab-'+tabnodeid,'<?php echo __('Category:'); ?> '+nodeName);   

         $.ajax({
             cache: false,  
            url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid+"&columnsetid="+UserColumnsetId,
            
            success : function (data) {
                $("#overAll").unmask();
                $("#tab-"+tabnodeid).html(data);
// reloadGridData({params:{'fromTree':"true",'subCategories':BrowseSubCategories,'categoryNodeId':node.attributes.nodeId,'categories':node.id,'columnsetid':UserColumnsetId}});   
                eval("reloadGridData"+tabnodeid+"({params:{'fromTree':fromTree,'subCategories':subCategories,'categoryNodeId':nodeId,'categories':nodeName,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");  
            },
            beforeSend: function(req) {
                $("#overAll").mask("Loading Documents...",300);    
            } 
        }); 
    }
    
    function openCategoryAdvanced(nodeId,nodePath,nodeName,subCategories,fromTree) {
        if (typeof subCategories == 'undefined')
            subCategories = "false";
        if (typeof fromTree == 'undefined')
            fromTree = "false";
          var tabnodeid = nodeId.replace(/[-.,:\+/\\ ]/g,"");    
                    
          tabnodeid = tabnodeid.replace(/%[a-z0-9A-Z][a-z0-9A-Z]/g,"");     
          console.log("nodeidtab "+tabnodeid);         
          addTabDynamic('tab-'+tabnodeid,'<?php echo __('Category:'); ?> '+nodeName);   

         $.ajax({
             cache: false,  
            url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid+"&columnsetid="+UserColumnsetId,
            
            success : function (data) {
                $("#overAll").unmask();
                $("#tab-"+tabnodeid).html(data);
// reloadGridData({params:{'fromTree':"true",'subCategories':BrowseSubCategories,'categoryNodeId':node.attributes.nodeId,'categories':node.id,'columnsetid':UserColumnsetId}});   
                eval("reloadGridData"+tabnodeid+"({params:{'fromTree':fromTree,'subCategories':subCategories,'categoryNodeId':nodeId,'categories':nodePath,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");  
            },
            beforeSend: function(req) {
                $("#overAll").mask("Loading Documents...",300);    
            } 
        }); 
    }
    
    function openFolder(nodeId,nodeName) {
          var tabnodeid = nodeId.replace(/[-., ]/g,"");              
          addTabDynamic('tab-'+tabnodeid,nodeName);   
        
         $.ajax({
             cache: false,  
            url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid+"&columnsetid="+UserColumnsetId,
            
            success : function (data) {
                $("#overAll").unmask();
                $("#tab-"+tabnodeid).html(data);

                eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");  
            },
            beforeSend: function(req) {
                $("#overAll").mask('<?php echo __('Loading Documents...'); ?>',300);    
            } 
        }); 
    }

    function openTag(nodeName) {
          var tabnodename = nodeName.replace(/[-., ]/g,"");                     
          addTabDynamic('tab-'+tabnodename,'Tag: '+nodeName);   
        
         $.ajax({
             cache: false,  
            url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodename+"&columnsetid="+UserColumnsetId,
            
            success : function (data) {
                $("#overAll").unmask();
                $("#tab-"+tabnodename).html(data);

                eval("reloadGridData"+tabnodename+"({params:{'tag':nodeName,'columnsetid':Registry.getInstance().get(\"ColumnsetId\")}});");  
            },
            beforeSend: function(req) {
                $("#overAll").mask('<?php echo __('Loading Documents...'); ?>',300);    
            } 
        }); 
    }

    </script>


    <script type="text/javascript">     
        $(document).ready(function() {    
            /*$('#clipBoardContainer .pasteClipboard').click(function() {
                ClipBoard.pasteItems();       
            });
            
            $('#clipBoardContainer .clearClipboard').click(function() {
                ClipBoard.clearItems();       
                if ($("#clipBoardContainer").is(":visible"))
                    $("#clipBoardContainer").slideToggle();   
            });  */
                            
        });
        
        
        var ClipBoard = {
            items: new Array(),
            addItem: function(path,name) {
                if ($.inArray(path,ClipBoard.items) < 0) {
                    ClipBoard.items.push(path);
                }
            },
            removeItem: function(path) {
                if ($.inArray(path,ClipBoard.items) >= 0) {
                    ClipBoard.items.splice($.inArray(path,ClipBoard.items), 1); 
                }
            },
            clearItems: function() {
                ClipBoard.items = new Array();       
                
                ClipBoard.reloadClip();    
            },
            reloadClip: function() {
                var jsonItems = $.toJSON(ClipBoard.items);
                
                if (!tabExists('clipboard-tab')) {
                    addTabFixed('clipboard-tab','<?php echo __('Clipboard'); ?>');
                    
                     $.ajax({
                        cache: false,  
                        url : "<?php echo url_for('DataGrid/index') ?>?containerName=ClipBoard&clipboard=true&columnsetid="+UserColumnsetId,
                        
                        success : function (data) {
                            $("#clipboard-tab").html(data);
                            eval("reloadGridDataClipBoard({params:{'clipboard':true,'clipboarditems':'"+jsonItems+"','columnsetid':Registry.getInstance().get(\"ColumnsetId\")}})");
                        }    
                    });
                }
                else {             
                   eval("reloadGridDataClipBoard({params:{'clipboard':true,'clipboarditems':'"+jsonItems+"','columnsetid':Registry.getInstance().get(\"ColumnsetId\")}})");
                   //setActive('clipboard-tab'); // TODO - REMOVE JUST FOR DEBUG
                }
            }
        }
        
    </script>
</head>
<body>
<div id="overAll">
    <div id="north" class="x-hide-display">
        <div style="float:left;background-color:#FFFFFF;">
            <a href="http://www.ifresco.at/"><img src="/images/logo94x50.png" height="50" width="94" style="margin-left:10px;margin-top:4px;"></a>
            
        </div>
        
        <div id="clipBoardContainer" class="x-panel" style="display: none;">
            <div class="x-panel-header" style="float:left;height:49px;border-left:none;border-top:none;border-bottom:none;">
                <!--<div class="x-btn pasteClipboard"><img src="/images/toolbar/page_white_paste.png" title="Paste in active tab" border="0"></div>-->
                <div class="x-btn clearClipboard"><img src="/images/icons/cross.png" title="<?php echo __('Clear clipboard'); ?>" border="0" style="margin-top:5px;"></div>
            </div>
            <div id="clipBoardContent">
                <ul>

                </ul>
            </div>
        </div>
        
        <div style="float:right;width:600px;margin:2px;text-align:right;background-color:#FFFFFF;">
            <div id="topMenu">
                <ul>

                    <li style="background-color:none;border:none;">
                    
                    <div id="alfrescoSearch" style="margin:5px;float:left;">
                        <form action="" method="post" name="alfrescoSearchForm">
                            <a href="javascript:openTab('searchtab','<?php echo __('Advanced Search'); ?>','<?php echo url_for("Search/index"); ?>');"><?php echo __('Advanced Search'); ?></a> <input id="alfrescoSearchTerm" name="searchTerm" type="text">
                            <!--<button id="search_submit">Search</button>-->
                            <script type="text/javascript">
                                $(function() {
                                    $("#alfrescoSearchTerm").keypress(function (e) {
                                        if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                                            $('#alfrescoSearchSubmit').click();
                                            return false;
                                        } else {
                                            return true;
                                        }
                                    });
                                    
                                    $("#alfrescoSearchSubmit").click(function() {
                                        alfrescoSearchSubmit();
                                    });
                                });

                                function IsEmpty(aTextFieldvalue) {
                                   if ((aTextFieldvalue.length==0) ||
                                   (aTextFieldvalue==null)) {
                                      return true;
                                   }
                                   else { return false; }
                                }    

                                function alfrescoSearchSubmit() {
                                    var searchTerm = $("#alfrescoSearchTerm");
                                    var foundAdvancedSearch = false;
                                    /*
                                    deactivated to take the quicksearch field also in advanced search for full text search
                                    if (tabExists('searchtab')) {
                                        var tabPanel = Ext.getCmp('content-tabs');
                                        if (tabPanel) {
                                            var activetab = tabPanel.getActiveTab();
                                            if (activetab.id == "searchtab") {
                                                var searchString = "";
                                                if (!IsEmpty(searchTerm.val())) {
                                                    searchString = searchTerm.val(); 
                                                }
                                                else {
                                                    var searchTermAdv = $("#searchTermAdvanced");
                                                    if (searchTermAdv != null && typeof searchTermAdv.length != 'undefined') {
                                                        searchString = searchTermAdv.val(); 
                                                    }
                                                }
                                                submitAdvancedSearch(searchString); 
                                                foundAdvancedSearch = true;
                                            }
                                        }   
                                    }
                                    * 
                                    * modify that just the btn of quciksearch can be used for advanced search too 
                                    */
                                     if (tabExists('searchtab')) {
                                        var tabPanel = Ext.getCmp('content-tabs');
                                        if (tabPanel) {
                                            var activetab = tabPanel.getActiveTab();
                                            if (activetab.id == "searchtab") {
                                                submitBtnClick();
                                                foundAdvancedSearch = true;
                                            }
                                        }
                                     }
                                    if (foundAdvancedSearch == false) {
                                        if (!IsEmpty(searchTerm.val())) {
                                            /*var dataGridHTML =$("#dataGrid").html();
                                            if (dataGridHTML == null || dataGridHTML == "") {
                                                $.ajax({
                                                    
                                                    url : "<?php echo url_for('DataGrid/index') ?>",
                                                    
                                                    success : function (data) {
                                                        $("#ContentBox").html(data);
                                                        //grid.store.load({params:{'nodeId':node.id}});    
                                                        reloadGridData({params:{'searchTerm':searchTerm.val()}});
                                                    }    
                                                });
                                            }
                                            else {
                                                //grid.store.load({params:{'nodeId':node.id}});       
                                                reloadGridData({params:{'searchTerm':searchTerm.val()}});
                                            }*/

                                            if (!tabExists('documentgrid-tab')) {
                                                addTabFixed('documentgrid-tab','<?php echo __('Documents'); ?>');
                                                
                                                 $.ajax({
                                                    cache: false,  
                                                    url : "<?php echo url_for('DataGrid/index') ?>?columnsetid="+UserColumnsetId,
                                                    
                                                    success : function (data) {
                                                        $("#documentgrid-tab").html(data);
                                                        //grid.store.load({params:{'nodeId':node.id}});    

                                                        reloadGridData({params:{'searchTerm':searchTerm.val(),'columnsetid':UserColumnsetId}});
                                                    }    
                                                });
                                            }
                                            else {             
                                               reloadGridData({params:{'searchTerm':searchTerm.val(),'columnsetid':UserColumnsetId}});
                                               setActive('documentgrid-tab'); 
                                            }
                                        }
                                    }
                                }
                            </script>
                            
                            <input id="alfrescoSearchSubmit" class="fg-button ui-button ui-state-default" type="button" value="<?php echo __('Search'); ?>" style="cursor:pointer;font-size:10px;" title="<?php echo __('Search'); ?>">

                        </form>
                    </div>
                    
                    </li>
                    <!--<li><a href="javascript:openTab('admintab','Administration','<?php echo url_for('Admin/index') ?>');"><img align="absmiddle" src="/images/toolbar/computer.png" border="0" style="border:none;"/></a></li>
                    <li><a href="javascript:openTab('admintab','Administration','<?php echo url_for('Admin/index') ?>');"><img align="absmiddle" src="/images/toolbar/user-icon.png" border="0" style="border:none;"/></a></li>-->
                    
                    <li id="horizontalBtn"><a href="javascript:arrangeWindows('horizontal');" title="<?php echo __('Horizontal Split'); ?>"><img align="absmiddle" src="/images/toolbar/split_bottom.png" border="0" style="border:none;"/></a></li>
                    <li id="verticalBtn"><a href="javascript:arrangeWindows('vertical');" title="<?php echo __('Vertical Split'); ?>"><img align="absmiddle" src="/images/toolbar/split_left.png" border="0" style="border:none;"/></a></li>
                    <li class="splitter"></li>
                    <?php if ($isAdmin == true) { ?>
                        <li><a href="javascript:openTab('admintab','Administration','<?php echo url_for('Admin/index') ?>');" title="<?php echo __('Administration'); ?>"><img align="absmiddle" src="/images/toolbar/admin.png" border="0" style="border:none;"/></a></li>
                    <?php } ?>
                    <li class="splitter"></li>
                    <li><a href="<?php echo url_for('AlfrescoLogin/logout'); ?>" title="<?php echo __('Logout'); ?>"><img align="absmiddle" src="/images/toolbar/logout.png" border="0" style="border:none;"/></a></li>
                </ul>
            </div>

            

        </div>

    </div>

    <div id="west" class="x-hide-display">
        <script type="text/javascript">  
        Ext.onReady(function(){    
             var Tree = Ext.tree;   
               
             var tree = new Tree.TreePanel({   
                 id:'alfrescoTree',
                 el:'foldersTree',   
                 rootVisible:true,
                 autoScroll:true,   
                 animate:true,   
                 enableDD:false,   
                 containerScroll: true,   
                 dataUrl:'<?php echo url_for('tree/getJSON') ?>',
                 /*root:{
                     nodeType:'async'
                    ,id:'root'
                    ,text:'Company Home'
                    ,expanded:true
                },*/
                tbar:[
                    '->',{
                        iconCls: 'reload-tree',
                        tooltip: '<?php echo __('Reload'); ?>',                           
                        scope:this,
                        handler: function(){
                            tree.getRootNode().reload();
                            tree.render();    
                        }
                    }
                ],
                listeners: {
                    contextmenu: function(node,e) {
                        var existingMenu = Ext.getCmp('foldersTree-ctx');
                        if (existingMenu != null) {
                            existingMenu.destroy();
                        }
                        
                        var treeMenu = new Ext.menu.Menu({    
                            id:'foldersTree-ctx',
                            items:[
                                {
                                    iconCls: 'add-tab',
                                    text: '<?php echo __('Open in new tab'); ?>',                           
                                    scope:this,
                                    handler: function(){
                                        //store.getAt(index).data.name;  
                                        var nodeId = node.id;
                                        if (nodeId != "root")
                                            var nodeText = node.attributes.imagetext;
                                        else
                                            var nodeText = "<?php echo __('Company Home'); ?>";

                                        openFolder(nodeId,nodeText);
                        
                                    }   
                                },'-',
                                {
                                    iconCls: 'add-favorite',
                                    text: '<?php echo __('Add to favorites'); ?>',                           
                                    scope:this,
                                    handler: function(){
                                        //store.getAt(index).data.name;  
                                        var nodeId = node.id;
                                        var nodeText = node.text;
                                        var nodeType = "folder";

                                        addFavorite(nodeId,nodeText,nodeType);
                                         
                                    }   
                                }
                            ]     
                        });
                        e.stopEvent();
                        treeMenu.showAt(e.getXY());
                    },
                    render: function() {
                        this.getRootNode().expand();
                    },
                    click: function(node, event){
                        if (!tabExists('documentgrid-tab')) {
                            addTabFixed('documentgrid-tab','<?php echo __('Documents'); ?>');
                            
                             $.ajax({
                                cache: false,  
                                url : "<?php echo url_for('DataGrid/index') ?>?columnsetid="+UserColumnsetId,
                                
                                success : function (data) {
                                    $("#overAll").unmask();
                                    $("#documentgrid-tab").html(data);
                                    
                                    reloadGridData({params:{'nodeId':node.id,'columnsetid':UserColumnsetId}});
                                },
                                beforeSend: function(req) {
                                    $("#overAll").mask('<?php echo __('Loading Documents...'); ?>',300);    
                                } 
                            });
                        }
                        else {      
                           reloadGridData({params:{'nodeId':node.id,'columnsetid':UserColumnsetId}}); 
                           setActive('documentgrid-tab'); 
                        }
                    }
                } 
             });   
 
             var root = new Tree.AsyncTreeNode({   
                 text: '<?php echo __('Company Home'); ?>',   
                 draggable:false,   
                 id:'root'
             });   
             tree.setRootNode(root);
 
             tree.render();   
             root.expand();   
        });   
        </script>
        <div id="foldersTree"></div>
        
        <script type="text/javascript">  
        Ext.onReady(function(){   
             var TreeCat = Ext.tree;   
               
             var treeCat = new TreeCat.TreePanel({   

                 el:'categoriesTree',   
                 rootVisible:true,
                 autoScroll:true,   
                 animate:true,   
                 enableDD:false,   
                 containerScroll: true,   
                 dataUrl:'<?php echo url_for('CategoryTree/getJSON') ?>',

             tbar:[
              {
                  xtype: 'checkbox',
                  name: 'browsesub',
                  checked: (BrowseSubCategories == true ? true : false),
                  boxLabel: '<?php echo __('Browse items in sub-categories?'); ?>',
                  listeners: {
                      check: function(component,checked) {
                          Registry.getInstance().set("BrowseSubCategories",checked);  
                          
                          Registry.getInstance().save();   
                          BrowseSubCategories = checked; 
                      }
                  }
              },
              '->',{
                        iconCls:'refresh-icon',
                        tooltip: '<?php echo __('Reload'); ?>',  
                         handler: function(){ 
                             treeCat.getRootNode().reload();
                             treeCat.render(); 
                         },        
                        scope: this
                    }, '-',
                    {
                iconCls: 'icon-expand-all',
                tooltip: '<?php echo __('Expand All'); ?>',
                handler: function(){ treeCat.root.expand(true); },
                scope: this
            },{
                iconCls: 'icon-collapse-all',
                tooltip: '<?php echo __('Collapse All'); ?>',
                handler: function(){ treeCat.root.collapse(true); },
                scope: this
            }],

                
                listeners: {
                    contextmenu: function(node,e) {
                        var existingMenu = Ext.getCmp('catTree-ctx');
                        if (existingMenu != null) {
                            existingMenu.destroy();
                        }
                        
                        if (node.id == "root") {
                            
                            var catMenu = new Ext.menu.Menu({    
                                id:'catTree-ctx',
                                items:[
                                    <?php if ($isAdmin == true) { ?>
                                    {
                                        iconCls: 'add-category',
                                        text: '<?php echo __('Add a subcategory'); ?>',                           
                                        scope:this,
                                        handler: function(){
                                            catMenu.hide();     
                                            if(node.leaf == false) {
                                                node.expand(false,true,finishExpandAdd); 
                                                treeCat.doLayout();               
                                                treeCat.render();
                                                return;           
                                            }   
                                            finishExpandAdd(node);                  
                                        }   
                                    }
                                    <?php } ?>
                                ]
                            });
                        }
                        else {
                            var catMenu = new Ext.menu.Menu({    
                                id:'catTree-ctx',
                                items:[
                                    {
                                        iconCls: 'add-tab',
                                        text: '<?php echo __('Open in new tab'); ?>',                           
                                        scope:this,
                                        handler: function(){
                                            var nodePath = node.id;
                                            var nodeId = node.attributes.nodeId;  
                                            var nodeText = node.text;
                                            console.log("nodepath "+nodeId);
                                            openCategoryAdvanced(nodeId,nodePath,nodeText,BrowseSubCategories,"true");
                            
                                        }   
                                    },'-',
                                    {
                                        iconCls: 'add-favorite',
                                        text: '<?php echo __('Add to favorites'); ?>',                           
                                        scope:this,
                                        handler: function(){
                                            //var nodeId = node.id;
                                            var nodeId = node.attributes.nodeId;  
                                            var nodeText = node.text;
                                            var nodeType = "category";

                                            addFavorite(nodeId,nodeText,nodeType);
                                             
                                        }   
                                    }
                                    <?php if ($isAdmin == true) { ?>
                                    ,'-',{
                                        iconCls: 'add-category',
                                        text: '<?php echo __('Add a subcategory'); ?>',                           
                                        scope:this,
                                        handler: function(){
                                            catMenu.hide();     
                                            if(node.leaf == false) {
                                                node.expand(false,true,finishExpandAdd); 
                                                treeCat.doLayout();               
                                                treeCat.render();
                                                return;           
                                            }   
                                            finishExpandAdd(node);                  
                                        }   
                                    },{
                                        iconCls: 'edit-category',
                                        text: '<?php echo __('Rename this category'); ?>',                           
                                        scope:this,
                                        //hidden:(node.id == "root" ? true : false),
                                        handler: function(){
                                             onTreeEditing(node);
                                        }   
                                    },{
                                        iconCls: 'delete-category',
                                        text: '<?php echo __('Delete this category'); ?>',                           
                                        scope:this,
                                        handler: function(){
                                            var nodeId = node.attributes.nodeId;              
                                            var nodeText = node.text;  
                                            $.ajax({
                                                cache: false,  
                                                url : "<?php echo url_for('CategoryTree/removeCategory') ?>",
                                                data: {nodeId: nodeId},
                                                dataType:"json",
                                                
                                                success : function (data) {
                                                    $("#categoriesTree").unmask();    

                                                    if (data.success == true) {
                                                        node.remove();
                                                    }   
                                                    else {
                                                        return false;
                                                    }
                                                },
                                                error: function() {
                                                    return false;
                                                    $("#categoriesTree").unmask();    
                                                },
                                                beforeSend: function(req) {
                                                    $("#categoriesTree").mask("Delete <b>"+nodeText+"</b>...",300);    
                                                }  
                                            });    
                                        }   
                                    }
                                    <?php } ?>
                                ]     
                            });
                        }
                        
                        e.stopEvent();
                        catMenu.showAt(e.getXY());
                    },
                    render: function() {
                        this.getRootNode().expand();
                    },
                    click: function(node, event){
                        if (node.id == "root")
                            return false;
                        if (!tabExists('documentgrid-tab')) {
                            addTabFixed('documentgrid-tab','<?php echo __('Documents'); ?>');
                            
                             /*$.ajax({
                                cache: false,  
                                url : "<?php echo url_for('DataGrid/index') ?>?columnsetid="+UserColumnsetId,
                                
                                success : function (data) {
                                    $("#overAll").unmask();
                                    $("#documentgrid-tab").html(data);
                                    
                                    var categories = node.id;  
                                    //reloadGridData({params:{'categories':node.id,'columnsetid':UserColumnsetId}}); 
                                    
                                },
                                beforeSend: function(req) {
                                    $("#overAll").mask('<?php echo __('Loading Documents...'); ?>',300);    
                                } 
                            });  */
                            
                            var nodeId = node.attributes.nodeId;              
                            $.ajax({
                                 cache: false,  
                                url : "<?php echo url_for('DataGrid/index') ?>?columnsetid="+UserColumnsetId,
                                
                                success : function (data) {
                                    $("#overAll").unmask();
                                    $("#documentgrid-tab").html(data);        
                                    reloadGridData({params:{'fromTree':"true",'subCategories':BrowseSubCategories,'categoryNodeId':node.attributes.nodeId,'categories':node.id,'columnsetid':UserColumnsetId}});                        
                                },
                                beforeSend: function(req) {
                                    $("#overAll").mask('<?php echo __('Loading Documents...'); ?>',300);    
                                } 
                            }); 
                        }
                        else {     
                           reloadGridData({params:{'fromTree':"true",'categoryNodeId':node.attributes.nodeId,'subCategories':BrowseSubCategories,'categories':node.id,'columnsetid':UserColumnsetId}});
                           setActive('documentgrid-tab'); 
                        }
                    }
                } 
             });   
             
             var treeCatEditor = new Ext.tree.TreeEditor(treeCat, {}, {
                cancelOnEsc: true,
                completeOnEnter: true,
                selectOnFocus: true,
                allowBlank: false,
                listeners: {
                    complete: onTreeEditComplete,
                    beforestartedit: function(editor,element) {
                        try {
                            if (firedContextMenuEditing == false) {
                                var selNode = treeCat.selModel.selNode;
                                var editNode = editor.editNode;
                                
                                if (selNode == editNode) {
                                    treeCat.fireEvent('click',selNode);
                                    return false;      
                                }
                            }
                        }
                        catch(err) {
                            
                        }
                    }
                }
            });


             treeCat.filter = new Ext.tree.TreeFilter(treeCat);
              
             var rootCat = new TreeCat.AsyncTreeNode({   
                 text: '<?php echo __('Categories'); ?>',   
                 draggable:false,   
                 id:'root',
                 disabled:true
             });   
             treeCat.setRootNode(rootCat);
 
             treeCat.render();   
             rootCat.expand();   
             
             var firedContextMenuEditing = false;
             function onTreeEditing(n) {
                firedContextMenuEditing = true;
                treeCatEditor.editNode = n;
                treeCatEditor.startEdit(n.ui.textNode);
                firedContextMenuEditing = false;
            }
            
            function finishExpandAdd(node) {
                var newNode = node.appendChild({id: "newNode", iconCls:'new-category', cls:'folder', text: "", leaf: true});
                if (newNode != null) {
                    node.leaf = false;
                    node.expand(false);      
                    treeCat.doLayout();  
                    treeCat.render(); 
                    onTreeEditing(newNode);  
                }      
            }
            
            function onTreeEditComplete(treeEditor, value, oldValue) {
                if (value != oldValue) {
                    var nodeId = treeEditor.editNode.attributes.nodeId;
                    if (nodeId !== null && typeof nodeId !== 'undefined') {
                        $.ajax({
                            cache: false,  
                            url : "<?php echo url_for('CategoryTree/editCategory') ?>",
                            data: {nodeId: nodeId, value: value},
                            dataType:"json",
                            
                            success : function (data) {
                                $("#categoriesTree").unmask();    

                                if (data.success == true) {
                                    firedContextMenuEditing = false;
                                    return true;
                                }   
                                else {
                                    firedContextMenuEditing = false;
                                    treeEditor.editNode.setText(oldValue);
                                }
                            },
                            error: function() {
                                treeEditor.editNode.setText(oldValue);
                                $("#categoriesTree").unmask();    
                            },
                            beforeSend: function(req) {
                                $("#categoriesTree").mask("<?php echo __('Rename'); ?> <b>"+oldValue+"</b> <?php echo __('to'); ?> <b>"+value+"</b>...",300);    
                            }  
                        });
                    }
                    else {
                        //if (nodeId == "newNode" || treeId == "root") {
                            if (value.length == 0) {
                                treeEditor.editNode.remove(true);    
                                return false;
                            }
                            else {
                                var parentNode = treeEditor.editNode.parentNode;
                                if (parentNode.attributes.id == "root")
                                    var parentNodeId = "root";
                                else
                                    var parentNodeId = parentNode.attributes.nodeId;

                                 $.ajax({
                                    cache: false,  
                                    url : "<?php echo url_for('CategoryTree/addCategory') ?>", 
                                    data: {nodeId: parentNodeId, value: value},
                                    dataType:"json",
                                    
                                    success : function (data) {
                                        $("#categoriesTree").unmask();    

                                        if (data.success == true) {
                                            treeEditor.editNode.attributes.nodeId = data.nodeId;
                                            return true;
                                        }   
                                        else {
                                            treeEditor.editNode.remove(true);    
                                            return false;
                                        }
                                    },
                                    error: function() {                
                                        treeEditor.editNode.remove(true);        
                                        $("#categoriesTree").unmask();    
                                        return false;
                                    },
                                    beforeSend: function(req) {
                                        $("#categoriesTree").mask("<?php echo __('Add category'); ?> <b>"+value+"</b>...",300);    
                                    }  
                                });   
                            }       
                        //}
                    }
                }
                else
                    return false;
            }
        });   
        </script>
        <div id="categoriesTree"></div>
        
        <script type="text/javascript">  
            Ext.onReady(function(){   
 
             var TreeFav = Ext.tree;   
               
             var treeFav = new TreeFav.TreePanel({   
                 id:'favTree',
                 el:'favTree',   
                 rootVisible:false,
                 autoScroll:true,   
                 animate:true,   
                 enableDD:false,   
                 containerScroll: true,   
                 dataUrl:'<?php echo url_for('UserSpecific/GetFavorites') ?>',

                tbar:[
                    '->',{
                        iconCls:'refresh-icon',
                        tooltip: '<?php echo __('Reload'); ?>',  
                         handler: function(){ 
                             treeFav.getRootNode().reload();
                             treeFav.render(); 
                         },        
                        scope: this
                    }
                ],
                listeners: {
                    contextmenu: function(node,e) {
                        var existingMenu = Ext.getCmp('favTree-ctx');
                        if (existingMenu != null) {
                            existingMenu.destroy();
                        }

                        var favMenu = new Ext.menu.Menu({    
                            id:'favTree-ctx',
                            items:[
                                {
                                    iconCls: 'remove-favorite',
                                    text: '<?php echo __('Remove of favorites'); ?>',                           
                                    scope:this,
                                    handler: function(){ 
                                        var nodeId = node.id;
                                        var favId = node.attributes.favId;
                                        if (nodeId.length > 0) {
                                            if (typeof favId != 'undefined')
                                                removeFavoriteById(favId);   
                                            else
                                                removeFavoriteByNodeId(nodeId);    
                                        }             
                                    }   
                                }
                            ]     
                        });
                        e.stopEvent();

                        favMenu.showAt(e.getXY());
                    },
                    render: function() {
                        this.getRootNode().expand();
                    },
                    click: function(node, event){
                        var nodeType = node.attributes.type;
                        var nodeImgText = node.attributes.imageName;
                        
                        //var nodeId = node.id;     
                        if (nodeType !== "category")
                            var nodeId = node.id;
                        else
                            var nodeId = node.attributes.nodeId;     
                        var favId = node.attributes.favId;     
                        var workId = node.attributes.workId;
                        if (typeof node.attributes.nodeId != 'undefined')  
                            var tabnodeid = node.attributes.nodeId.replace(/-/g,""); 
                        else  
                            var tabnodeid = nodeId.replace(/-/g,"");   
                        
                        if (typeof workId != 'undefined' && nodeType !== 'category') 
                            var nodeId = workId;
                          
                        alfDocument(nodeId,nodeType,nodeImgText,tabnodeid,'favorite_'+favId);  
                        /*var createTab = false;          
                        if (!tabExists('tab-'+tabnodeid)) {
                            addTabDynamic('tab-'+tabnodeid,nodeImgText);
                            createTab = true;
                        }
                        setActive('tab-'+tabnodeid);
                        
                        if (nodeType == "file") {
                             $.ajax({
                                cache: false,  
                                url : "<?php echo url_for('DataGrid/detailView') ?>?nodeId="+nodeId,
                                data: ({'nodeId' : nodeId}),

                                
                                success : function (data) {
                                    $("#overAll").unmask();
                                    $("#tab-"+tabnodeid).html(data);
                                    //grid.store.load({params:{'nodeId':node.id}});    
                                    //reloadGridData({params:{'tag':val.name}}); 
                                },
                                beforeSend: function(req) {
                                    $("#overAll").mask("Loading "+nodeImgText+"...",300);    
                                }  
                            });
                        }
                        else if(nodeType == "folder" || nodeType == "category") {

                            if (createTab == true) {
                                $.ajax({
                                    cache: false,  
                                    url : "<?php echo url_for('DataGrid/index') ?>?containerName="+tabnodeid,

                                    success : function (data) {
                                        $("#overAll").unmask();
                                        $("#tab-"+tabnodeid).html(data);

                                        if (nodeType == "folder")
                                           eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId}});");
                                        else if (nodeType == "category")
                                            eval("reloadGridData"+tabnodeid+"({params:{'categories':nodeId}});");    
                                    },
                                    beforeSend: function(req) {
                                        $("#overAll").mask("Loading "+nodeImgText+"...",300);    
                                    } 
                                });   
                            }
                            else {
                                if (nodeType == "folder")
                                    eval("reloadGridData"+tabnodeid+"({params:{'nodeId':nodeId}});");
                                else if (nodeType == "category")
                                    eval("reloadGridData"+tabnodeid+"({params:{'categories':nodeId}});");                       
                            }      
                        } */
                        
                    }
                } 
             });   

             // set the root node   
             var rootFav = new TreeFav.AsyncTreeNode({   
                 text: '<?php echo __('Favorites'); ?>',   
                 draggable:false,   
                 id:'root'
             });   
             treeFav.setRootNode(rootFav);

             // render the tree   
             treeFav.render();   
             treeFav.expand();   
        });   
        </script>
        <div id="favTree"></div>

        
        
        <div id="tagScope">
            <script type="text/javascript">
                $(document).ready(function() {
                    getTagScope();

                });
                
                function getTagScope() {
                    $.getJSON("<?php echo url_for('tags/GetTagScope') ?>", function(data) {
                        $("#tagCloud").html('');
                        $("<ul>").attr("id", "tagList").appendTo("#tagCloud");
                        var maxCount = data.maxCount;
                        var minCount = data.minCount;
                        if (maxCount == minCount)
                            maxCount++;
                        var maxSize = 22;
                        var minSize = 12;
                        
                        var spread = maxCount - minCount;
                        var step = (maxSize - minSize) / (spread);

                        //create tags
                        $.each(data.tags, function(i, val) {
                          var size = Math.round(minSize + ((val.count - minCount) * step));

                          //create item
                          var li = $("<li>");
                          
                         
                          //create link
                          $("<a>").text(val.name + " ("+val.count+")").attr({title:"<?php echo __('See all documents tagged with'); ?> " + val.name, href:'#', id:val.name}).click(function() {
                            $(".active").removeClass('active');
                            //reloadGridData({params:{'tag':val.name}}); 
                            //updateTab('dashboard-tab','TEST', '<?php echo url_for('DataGrid/index') ?>');

                            if (!tabExists('documentgrid-tab')) {
                                addTabFixed('documentgrid-tab','<?php echo __('Documents'); ?>');
                                
                                 $.ajax({
                                    cache: false,  
                                    url : "<?php echo url_for('DataGrid/index') ?>?columnsetid="+UserColumnsetId,
                                    
                                    success : function (data) {
                                        $("#overAll").unmask();
                                        $("#documentgrid-tab").html(data);
                                        //grid.store.load({params:{'nodeId':node.id}});    
                                        reloadGridData({params:{'tag':val.name,'columnsetid':UserColumnsetId}}); 
                                    },
                                    beforeSend: function(req) {
                                        $("#overAll").mask('<?php echo __('Loading Documents...'); ?>',300);    
                                    } 
                                    
                                });
                            }
                            else {     
                               reloadGridData({params:{'tag':val.name,'columnsetid':UserColumnsetId}});      
                               setActive('documentgrid-tab'); 
                            }
                            
                            $(this).addClass("active");
                          }).appendTo(li);

                          //li.children().css("fontSize", (val.count / 10 < 1) ? val.count / 10 + 1 + "em": (val.count / 10 > 2) ? "2em" : val.count / 10 + "em");  
                          li.children().css("fontSize", size+"px");  
                          //add to list
                          li.appendTo("#tagList");
                        });
                    });    
                }
            </script>
            <div id="tagCloud" style="height:400px;"></div>
        </div>
        
        
    </div>
    
    
    <div id="contentTabs">
        <div id="dashboardTab" class="x-hide-display">
            <div id="ContentBox">
                <?php echo $sf_data->getRaw('sf_content') ?>        
            </div>
        </div>
    </div>
</div>  


<form id="history-form" class="x-hidden">
    <input type="hidden" id="x-history-field" />
    <iframe id="x-history-frame"></iframe>
</form>




</body>
</html>