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

class TiffRenderer implements ViewRenderer {
    private $Description = "Tif Renderer";
    public function getDescription() {
        return $this->Description;         
    }
    
    private $MimeTypes = "image/tiff";        
    private $userObj = null;       
    
    public function getMimetypes() {
        return $this->MimeTypes;
    }
    
    public function render($Node,$userObj) {

        $this->userObj = $userObj;
        $nodeId = $Node->getId();  
        $cache = $this->getCacheObject();     
        $cachePDF_exists = $cache->has($nodeId); 
             
        $lifetime = 86400;
        if (!$cachePDF_exists) {
            $this->PDFFile = "";
            $viewer = Doctrine_Query::create()
                          ->from('ViewerRelations v')
                          ->where('v.nodeid = ?', $nodeId)
                          ->fetchOne();


            if ($viewer != null) {
                $fileContent = $viewer->getViewercontent();
                $this->setToCache($nodeId,$fileContent);
            }
            else {
                $this->generateViewObj($nodeId);  
            }
            
            
        }

        $file = "/cache/Viewer/".$nodeId.".pdf";
        //$this->PDFFile = $file;
        $height = $_GET["height"];

        return $this->renderView($height,$file);
        
    }
    
    private function renderView($height,$pdfFile) {
        $heightStyle = (!empty($height) ? 'max-height:'.$height.';' : 'max-height:300px'); 

        $html = '<embed src="'.$pdfFile.'" width="100%" height="'.$height.'" style="z-index:-100;" class="PDFRenderer">'; 
        return $html;      

    }
    
    private function generateViewObj($nodeId) {
        TiffRenderer::convertToPDF($nodeId,$this->userObj); 
        
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
            $this->cache = new sfWebFileCache(array('cache_dir'=>$file_cache_dir,'lifetime'=>3600),".pdf");
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
      
      public static function convertToPDF($nodeId,$user) {
        $class_name = "convertToPDFTask";          
        $repositoryUrl = AlfrescoConfiguration::getInstance()->RepositoryUrl;
        $ticket = $user->getTicket();
        //echo $nodeId . " ".$repositoryUrl." ".$ticket;
        return self::executeTask($class_name,array(
                                            $nodeId,
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

        //echo $data;
        ob_clean();
        ob_end_flush();
     
      }
}    
?>