<?php
 /**
 * @package    AlfrescoClient
 * @author Dominik Danninger 
 *
 * ifresco Client v0.1alpha
 * 
 * Copyright (c) 2011 Dominik Danninger, MAY Computer GmbH
 * 
 * This file is part of "ifresco Client".
 * 
 * "ifresco Client" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco Client" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco Client".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */
require_once(sfConfig::get('sf_app_lib_dir').DIRECTORY_SEPARATOR.'sfWebFileCache.class.php');    

class DefaultRenderer implements ViewRenderer {
    private $Description = "FlexPaper Viewer (+Office2PDF PDF2SWF) - http://flexpaper.devaldi.com/";
    public function getDescription() {
        return $this->Description;         
    }
    
    private $MimeTypes = "default"; 
    private $userObj = null;       
    
    public function getMimetypes() {
        return $this->MimeTypes;
    }
    
    public function render($Node,$userObj) {

        $this->userObj = $userObj;
        $nodeId = $Node->getId();  
        $nodeRef = $Node->__toString();
        $contentData = $Node->cm_content;
        $size = 0;
        if ($contentData != null && $contentData instanceof ContentData) {
            $size = $contentData->getSize();  
        }
        $cache = $this->getCacheObject();     
        $cacheSWF_exists = $cache->has($nodeId); 
             
        $lifetime = 3600;
        if (!$cacheSWF_exists) {
            $this->swfFile = "";
            $viewer = Doctrine_Query::create()
                          ->from('ViewerRelations v')
                          ->where('v.nodeid = ?', $nodeId)
                          ->fetchOne();

            $generateNew = true;
            if ($viewer != null) {
                if ($viewer->getMd5sum() != $size) {
                    $generateNew = true;
                }
                else {
                    $generateNew = false;
                    $fileContent = $viewer->getViewercontent();
                    $this->setToCache($nodeId,$fileContent);
                }
            }
            
            if ($generateNew == true) {
                $this->generateViewObj($nodeId,$nodeRef);  
            }
            
            
        }

        $file = "/cache/Viewer/".$nodeId.".swf";
        //$this->swfFile = $file;
        $height = $_GET["height"];

        return $this->renderView($height,$file);
        
    }
    
    private function renderView($height,$swfFile) {
        $heightStyle = (!empty($height) ? 'height:'.$height.';' : '');
        $heightReq = (!empty($height) ? $height : '600');               
        $html = <<<EOF
        <style type="text/css" media="screen"> 
            #flashContent { display:none; }
        </style> 

        <script type="text/javascript" src="/js/swfobject/swfobject.js"></script>
                
        <script type="text/javascript">

            if(window.addEventListener)
            window.addEventListener('DOMMouseScroll', handleWheel, false);
            window.onmousewheel = document.onmousewheel = handleWheel;
            
            if (window.attachEvent) 
            window.attachEvent("onmousewheel", handleWheel);
            
            function handleWheel(event){
                try{
                    if(!window.document.FlexPaperViewer.hasFocus()){return true;}
                    window.document.FlexPaperViewer.setViewerFocus(true);
                    window.document.FlexPaperViewer.focus();
                    
                    if(navigator.appName == "Netscape"){
                        if (event.detail)
                            delta = 0;
                        if (event.preventDefault){
                            event.preventDefault();
                            event.returnValue = false;
                            }
                    }
                    return false;    
                }catch(err){return true;}        
            }

        </script>


        <script type="text/javascript"> 

            <!-- For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection. --> 
            var swfVersionStr = "9.0.124";
            <!-- To use express install, set to playerProductInstall.swf, otherwise the empty string. -->
            var xiSwfUrlStr = "${expressInstallSwf}";
            var flashvars = { 
                  SwfFile : "$swfFile",
                  Scale : 0.6, 
                  ZoomTransition : "easeOut",
                  ZoomTime : 0.3,
                    ZoomInterval : 0.1,
                    FitPageOnLoad : true,
                    FitWidthOnLoad : false,
                    PrintEnabled : true,
                    FullScreenAsMaxWindow : false,
                    localeChain: "en_US"
                  };
             var params = {
                
                }
            params.quality = "high";
            params.bgcolor = "#ffffff";
            params.allowscriptaccess = "always";
            params.allowfullscreen = "true";
            params.wmode = "transparent";
            var attributes = {};
            attributes.id = "FlexPaperViewer";
            attributes.name = "FlexPaperViewer";
            swfobject.embedSWF(
                "/swf/FlexPaperViewer.swf", "flashContent", 
                "100%", "$heightReq", 
                swfVersionStr, xiSwfUrlStr, 
                flashvars, params, attributes);
            swfobject.createCSS("#flashContent", "display:block;text-align:left;");

        </script> 

        <div style="$heightStyle" id="previewWindow">
            <div id="flashContent"> 
                <p> 
                    To view this page ensure that Adobe Flash Player version 
                    9.0.124 or greater is installed. 
                </p> 
                <script type="text/javascript"> 
                    //var pageHost = ((document.location.protocol == "https:") ? "https://" :    "http://"); 
                    //document.write("<a href='http://www.adobe.com/go/getflashplayer'><img src='" 
                    //                + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='Get Adobe Flash player' /></a>" ); 
                </script> 
            </div>
        </div>
EOF;
        return $html;  

    }
    
    private function generateViewObj($nodeId,$nodeRef) {
        DefaultRenderer::convertToSWF($nodeId,$nodeRef,$this->userObj); 
        
        $viewer = Doctrine_Query::create()
                      ->from('ViewerRelations v')
                      ->where('v.nodeid = ?', $nodeId)
                      ->fetchOne();


        if ($viewer != null) {
            $fileContent = $viewer->getViewercontent();
            $this->setToCache($nodeId,$fileContent);
        }
      }
      
      private $cache;
      public function getCacheObject() {
        if (!$this->cache instanceof sfWebFileCache) {
            $file_cache_dir = sfConfig::get('sf_web_cache_dir') . '/Viewer';
            //echo $file_cache_dir;
            $this->cache = new sfWebFileCache(array('cache_dir'=>$file_cache_dir,'lifetime'=>3600));
        }

        return $this->cache;
      } 
      
      public function setToCache($name, $value) {
          $file_cache = $this->getCacheObject();
          $file_cache->set($name, $value);
      }
      
      public function getFromCache($name) {
        $file_cache = $this->getCacheObject();
        if ($file_cache->has($name)) {
            $cached = $file_cache->get($name);
            if (!empty($cached)) {
              return unserialize($cached);
            }
        }
      }
      
      public static function convertToSWF($nodeId,$nodeRef,$user) {
        $class_name = "convertToSWFTask";          
        $repositoryUrl = AlfrescoConfiguration::getInstance()->RepositoryUrl;
        $ticket = $user->getTicket();
        //echo $nodeId . " ".$repositoryUrl." ".$ticket;
        //FB::log($nodeRef . " ".$repositoryUrl." ".$ticket);
        return self::executeTask($class_name,array(
                                            $nodeRef,
                                            $repositoryUrl,
                                            $ticket
        ));
      }
      
      
      public static function executeTask($class_name, $arguments = array(), $options = array()) {    
        $dispatcher = sfContext::getInstance()->getEventDispatcher();
        $formatter = new sfFormatter();
        $task = new $class_name($dispatcher, $formatter);
        chdir(sfConfig::get('sf_root_dir'));
        ob_start();

        $task->run($arguments, $options);
        //if ($dispatcher->isProcessed()) {

        //}

        echo $data;
        ob_clean();
        ob_end_flush();
     
      }
}    
?>