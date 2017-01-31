<?php
require_once(dirname(__FILE__).'/../lib/sfFileUploadCache.class.php');

/**
 * Upload actions.
 *
 * @package    AlfrescoClient
 * @subpackage Upload
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
class UploadActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->nodeId = $this->getRequestParameter('nodeId');
    $containerName = $this->getRequestParameter('containerName');    
    
    
    if (!empty($containerName))
        $this->containerName = $containerName;
    else
        $this->containerName = ""; 
    
  }
  
  private function _mime_content_type($filename) {

    $mime = new MimetypeHandler();
    return $mime->getMimetype($filename);
  }
  
  private $cache;
  public function getCacheObject() {
    if (!$this->cache instanceof sfFileUploadCache) {
        $file_cache_dir = sfConfig::get('sf_cache_dir') . '/Upload';
        //echo $file_cache_dir;
        $this->cache = new sfFileUploadCache(array('cache_dir'=>$file_cache_dir,'lifetime'=>3600));
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
  
  public function getFileName($name) {

        return sfConfig::get('sf_cache_dir') . '/Upload/'.$name;

  }

  
  public function executeUpload(sfWebRequest $request)
  {
  
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    // Create a reference to the 'SpacesStore'
    $spacesStore = new SpacesStore($session);

    $companyHome = $spacesStore->companyHome;      
    
    
    $nodeId = $this->getRequestParameter('nodeId'); 
    
    try {
        if (empty($nodeId))
            $MainNode = $companyHome;
        else 
            $MainNode = $session->getNode($spacesStore, $nodeId);
        
        sfConfig::add(array(
          'sf_upload_dir_name'  => $sf_upload_dir_name = 'uploads',
          'sf_upload_dir'       => sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.$sf_upload_dir_name,      
        ));   

        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->getResponse()->setHttpHeader('Pragma','no-cache');
        
        

        $json['id'] = "id";
        $json['jsonrpc'] = "2.0";


        $fileName = $_FILES['file']['name']; 
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        $fileType = $_FILES['file']['type']; 

        $mimname = "php://input";
        
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        //if (!file_exists("sfConfig::get('sf_cache_dir') . '/Upload"))
        //    mkdir(sfConfig::get('sf_cache_dir') . '/Upload');
            
        $tmpFile = sfConfig::get('sf_cache_dir') . '/Upload/'.$fileName;
        $out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");
        $in = fopen("php://input", "rb");

        if ($in) {
            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);

            }
        }
        fclose($out);
        
        $fileType = $this->_mime_content_type(strtolower($fileName));
    
        try { 
            $contentNode = $MainNode->createChild("cm_content", "cm_contains", "cm_".$fileName);   
            
            $contentNode->cm_name = $fileName;
            $contentNode->cm_title = "";
            $contentNode->cm_description = "";   

            $contentData = $contentNode->setContent("cm_content", $fileType, "UTF-8");  

            $contentData->writeContentFromFile($tmpFile);
            
            /*$this->logMessage("Filename => $fileName", "debug");
            $this->logMessage("Filetype => $fileType", "debug");
            $this->logMessage("Files => ".var_export($_FILES,true), "debug");
            $this->logMessage("Request => ".var_export($_REQUEST,true), "debug");
            $this->logMessage("Server => ".var_export($_SERVER,true), "debug");
            $this->logMessage("POST => ".var_export($_POST,true), "debug");
            $this->logMessage("GET => ".var_export($_GET,true), "debug");
            $this->logMessage("TMP => ".$_FILES['file']["tmp_name"], "debug");
            $this->logMessage("CACHE => ".$this->getFileName($fileName), "debug");
            $this->logMessage("MIMETYPE => ".$this->_mime_content_type($mimname), "debug");
            $this->logMessage("NodeId => ".$nodeId, "debug");*/


            $session->save();
            unlink($tmpFile);
        }
        catch (SoapFault $e) {
            $this->logMessage($e, "error");    
        }
        // Return JSON-RPC response
        $json['result'] = "null";
    }
    catch (Exception $e) {
        $this->logMessage("SOAPFAULT => ".$e, "debug");   
    }
    
    return $this->renderText(json_encode($json));

    
  }
}
