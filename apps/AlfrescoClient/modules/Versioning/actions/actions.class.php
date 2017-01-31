<?php

/**
 * Versioning actions.
 *
 * @package    AlfrescoClient
 * @subpackage Versioning
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
class VersioningActions extends sfActions
{
   

  public function executeGetJSON(sfWebRequest $request)
  {
    $array = array();
    $array["versions"] = array();
    
    $nodeId = $this->getRequestParameter('nodeId');
    
    try {
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);
        
        $Node = $session->getNode($spacesStore, $nodeId);
        if ($Node != null) {

            $Version = new RESTVersion($repository,$store,$session);
            $VersionResponse = $Version->GetVersionInfo($Node->getId());

            if (count($VersionResponse) > 0 && $Node->hasAspect("cm_versionable")) {
                foreach ($VersionResponse as $versionObj) {    
                    $versionId = $versionObj->nodeRef;
                    $versionName = $versionObj->name;
                    $versionLabel = $versionObj->label;
                    $versionDescription = $versionObj->description;
                    $versionTimestamp = strtotime($versionObj->createdDate);
                    $versionCreator = $versionObj->creator->userName;
                    
                    $versionId = preg_replace("#.*?://.*?/(.*)#is","$1",$versionId);
                    $array["versions"][] = array("nodeRef"=>$Node->getId(),
                                                 "nodeId"=>$versionId,
                                                 "version"=>$versionLabel,
                                                 "description"=>$versionDescription,
                                                 "date"=>$versionTimestamp,
                                                 "dateFormat"=>date($this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat(),$versionTimestamp),
                                                 "author"=>$versionCreator);
                    
                }
            }
            else
                 $array["versions"] = array();
        }
    }
    catch (Exception $e) {
        
    }

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');
    $return = json_encode($array);

    return $this->renderText($return);
  }
  
  public function executeDownloadVersion(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
      
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $versionStore = new VersionStore($session);
    
    $Node = $session->getNode($versionStore, $nodeId);

    if ($Node != null) {
        $contentData = $Node->cm_content;
        $url = "";
        if ($contentData != null && $contentData instanceof ContentData) {
            $url = $contentData->getUrl();
            $mime = $contentData->getMimetype();
            $encod = $contentData->getEncoding();
            $size = $contentData->getSize();
            
            $name = $Node->cm_name;

            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: ".$mime);
            header("Content-Length: ".$size);
            header('Content-Disposition: attachment; filename="'.$name.'"');
            readfile($url);
            
            die();            
        }
    }
      
  }
  
  public function executeViewVersions(sfWebRequest $request) {
      
  }
  
  public function executeNewVersion(sfWebRequest $request)
  {
      if ($request->hasParameter("enableUpload"))
        $this->EnableUpload = true;
      else
        $this->EnableUpload = false;
        
      if ($request->hasParameter("hideVersionNumber"))
        $this->ShowVersionInfo = false;
      else
        $this->ShowVersionInfo = true;
      
      $this->filter = "";
      $this->fileExt = "";
      if ($request->hasParameter("filter")) {
        $nodeId = $this->getRequestParameter('nodeId');
        
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);
        
        $Node = $session->getNode($spacesStore, $nodeId);
        if ($Node != null) {
            $fileName = $Node->cm_name;
            $fileExt = preg_replace("/.*\.(.*)/is","$1",$fileName);
            $this->fileExt = $fileExt;
            $fileExt = strtolower($fileExt);
            $fileExt .= ",".strtoupper($fileExt);
            $this->filter = '{title : "'.$fileName.'", extensions : "'.$fileExt.'"}';
        }
      }
      
      $this->setLayout(false);
  }
  
  public function executeRevertVersion(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    $versionNodeId = $this->getRequestParameter('versionNodeId');
    $VersionLabel = $this->getRequestParameter('version');
    
    $note = $this->getRequestParameter('note');
    if (!empty($note)) {
        $note = urldecode($note);
        $note = trim($note);
    }
    
    // Webscript has a bug - doesnt work right now - http://issues.alfresco.com/jira/browse/ALF-8225
    $versionchange = $this->getRequestParameter('versionchange');
    $majorChange = false;
    if ($versionchange == "major")
        $majorChange = true;
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    
    $array = array("success"=>false,"nodeId"=>$nodeId);
    
    $Node = $session->getNode($spacesStore, $nodeId);
    if ($Node != null) {
        $Version = new RESTVersion($repository,$store,$session);
        $Response = $Version->RevertVersion($nodeId,$VersionLabel,$majorChange,$note);
        $array["success"] = $Response->success;
        
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');
    $return = json_encode($array);

    return $this->renderText($return);
  } 
  
  public function executeCreateNewVersion(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    $note = $this->getRequestParameter('note');
    if (!empty($note)) {
        $note = urldecode($note);
        $note = trim($note);
    }
    $versionchange = $this->getRequestParameter('versionchange');
    $majorChange = false;
    if ($versionchange == "major")
        $majorChange = true;
        
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    
    $array = array("success"=>false,"nodeId"=>$nodeId);
    $Node = $session->getNode($spacesStore, $nodeId);
    if ($Node != null) {
        try {
            $versionNode = $Node->createVersion(utf8_encode($note),$majorChange);
            $session->save();
            $array["success"] = true;
        }
        catch (Exception $e) {
            
        }
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');
    $return = json_encode($array);

    return $this->renderText($return);
  }
  
  public function executeUploadNewVersion(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    $note = $this->getRequestParameter('note');
    if (!empty($note)) {
        $note = urldecode($note);
        $note = trim($note);
    }
    $versionchange = $this->getRequestParameter('versionchange');
    $majorChange = false;
    if ($versionchange == "major")
        $majorChange = true;
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    
    $json = array();
    try {
        $Node = $session->getNode($spacesStore, $nodeId);

        
        if ($Node != null) {
            sfConfig::add(array(
              'sf_upload_dir_name'  => $sf_upload_dir_name = 'uploads',
              'sf_upload_dir'       => sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR.$sf_upload_dir_name,      
            ));   

            $json['id'] = "id";
            $json['jsonrpc'] = "2.0";


            $fileName = $_FILES['file']['name']; 
            $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
            $fileType = $_FILES['file']['type']; 

            $mimname = "php://input";
            
            $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
            if (!file_exists(sfConfig::get('sf_cache_dir') . "/Upload"))
                mkdir(sfConfig::get('sf_cache_dir') . '/Upload');
                
            $tmpFile = sfConfig::get('sf_cache_dir') . '/Upload/'.$fileName;
            $out = fopen($tmpFile, $chunk == 0 ? "wb" : "ab");
            $in = fopen("php://input", "rb");

            if ($in) {
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);

                }
            }
            fclose($out);
            
            $fileType = $this->_mime_content_type($fileName);
            
            $RESTUpload = new RESTUpload($repository,$spacesStore,$session);
            $UploadResponse = $RESTUpload->UploadNewVersion($tmpFile,$fileName,$fileType,$Node->getId(),$note,$majorChange);
            
            if ($UploadResponse->status->code == 200)
                $json['result'] = "null";
            else
                $json['result'] = "error";
        }
    }
    catch (Exception $e) {

    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');
    return $this->renderText(json_encode($json));
  }
  
  private function _mime_content_type($filename) {

    $mime = new MimetypeHandler();
    return $mime->getMimetype($filename);
  }
}
