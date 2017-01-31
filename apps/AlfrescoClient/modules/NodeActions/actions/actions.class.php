<?php

/**
 * NodeActions actions.
 *
 * @package    AlfrescoClient
 * @subpackage NodeActions
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
class NodeActionsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }
  
  public function executeDownload(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();
    
    $spacesStore = new SpacesStore($session);
    
    $Node = $session->getNode($spacesStore, $nodeId);
    
    if ($Node != null) {
        $contentData = $Node->cm_content;
        $url = "";
        if ($contentData != null && $contentData instanceof ContentData) {
            $url = $contentData->getUrl();
            $mime = $contentData->getMimetype();
            $encod = $contentData->getEncoding();
            $size = $contentData->getSize();
            
            $name = $Node->cm_name;
            
            //header("Location: ".$this->file);
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: ".$mime);
            header("Content-Length: ".$size);
            header('Content-Disposition: attachment; filename="'.$name.'"');
            readfile($url);

            /*$this->getResponse()->setHttpHeader('Content-Type',"{$mime}; charset={$encod}");
            $this->getResponse()->setHttpHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0');
            $this->getResponse()->setHttpHeader('Content-Length',$size);
            $this->getResponse()->setHttpHeader('Content-Disposition','attachment; filename="'.$name.'"');*/

            //$this->getResponse()->setHttpHeader('Location',$url);
            //readfile($url);
            

            /*header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: ".$type);
        header("Content-Length: ".$this->size);
        header('Content-Disposition: attachment; filename="'.$this->name.'"');

*/
            die();
            $this->setLayout(false);
        }
    }              
  }
  
  public function executeDeleteNode(sfWebRequest $request) {
    $returnArr = array("success"=>"false");

    $nodeId = $request->getParameter('nodeId');
    $type = $request->getParameter('nodeType');
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
     
    $RestContent = new RESTContent($repository,$spacesStore,$session);

    if (empty($type))
        $type = "file";
    
    
    try {
        if ($type == "file") {
            $RestContent->DeleteNode($nodeId);
        }
        else {
            $RestContent->DeleteSpace($nodeId);         
        }
        $returnArr["success"] = true;                 
    }
    catch (Exception $e) {
        $returnArr["errorMsg"] = $e->getMessage();                 
        $returnArr["success"] = false;                 
    }
    

    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($returnArr);
    return $this->renderText($return);     
  }
  
  public function executeDeleteNodes(sfWebRequest $request) {
    $returnArr = array("success"=>"false","deleted"=>0,"count"=>0);

    $nodes = $request->getParameter('nodes');
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
     
    $RestContent = new RESTContent($repository,$spacesStore,$session);

    if (!empty($nodes)) {    
        try {
            $nodes = json_decode($nodes);
            if (is_array($nodes)) {
                $countSucces = 0;
                $countFiles = count($nodes);
                
                foreach ($nodes as $Node) {
                    $type = $Node->shortType;
                    $nodeId = $Node->nodeId;
                    //FB::log("type $type => $nodeId");
                    try {
                        if ($type == "file") {
                            $RestContent->DeleteNode($nodeId);
                        }
                        else {
                            $RestContent->DeleteSpace($nodeId);         
                        }
                        $countSucces++;
                    }
                    catch (Exception $e) { 
                        $returnArr["errorMsg"] = $e->getMessage();                         
                    }
                }
                $returnArr["deleted"] = $countSucces;    
                $returnArr["count"] = $countFiles; 
                if ($countSucces == $countFiles)   
                    $returnArr["success"] = true;    
            }             
        }
        catch (Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();                 
            $returnArr["success"] = false;                 
        }
    }
    

    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($returnArr);
    return $this->renderText($return);     
  }
  
  public function executeMailNode(sfWebRequest $request) {
    $returnArr = array("success"=>"false","errorMsg"=>"unknown");

    $nodes = $request->getParameter('nodes');
    $emailTo = $request->getParameter('to');
    $emailCC = $request->getParameter('cc');
    $emailBCC = $request->getParameter('bcc');
    $emailBody = $request->getParameter('body');
    $emailSubject = $request->getParameter('subject');

    try {
        if (preg_match("/,/eis",$emailTo))
            $emailTo = explode(",",$emailTo);
            
        if (preg_match("/,/eis",$emailCC))
            $emailCC = explode(",",$emailCC);
        
        if (preg_match("/,/eis",$emailBCC))
            $emailBCC = explode(",",$emailBCC);
            
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);
        
        if (empty($emailTo)) {
            throw new Exception("Missing parameters (Required: To)");
        }
        
        
        $SMTP_HOST = Registry::getSetting("SMTP_HOST");
        $SMTP_PORT = Registry::getSetting("SMTP_PORT");
        
        if (empty($SMTP_HOST) || $SMTP_HOST == null || empty($SMTP_PORT) || $SMTP_PORT == null)
            throw new Exception("No SMTP Server is set");
        
        $SMTP_AUTH = Registry::getSetting("SMTP_AUTH");
        $SMTP_USERNAME = Registry::getSetting("SMTP_USERNAME");
        $SMTP_PASSWORD = Registry::getSetting("SMTP_PASSWORD");

        if ($SMTP_AUTH == "true")
            if (empty($SMTP_USERNAME) || empty($SMTP_PASSWORD) || $SMTP_USERNAME == null || $SMTP_PASSWORD == null)
                throw new Exception("SMTP Authentication active but no Username or Password is set");

        $FROM_EMAIL = Registry::getSetting("FROM_EMAIL");
        if (empty($FROM_EMAIL) || $FROM_EMAIL == null)
            $FROM_EMAIL = "noreplay@localhost.com";
            
        $FROM_NAME = Registry::getSetting("FROM_NAME");
        if (empty($FROM_NAME) || $FROM_NAME == null)
            $FROM_NAME = "ifresco client";
        
        if (empty($emailSubject)) 
            $emailSubject = $this->getContext()->getI18N()->__("ifresco - send files", null, 'messages');    

        if (!empty($nodes)) {    
            
                $nodes = json_decode($nodes);

                if (is_array($nodes)) {
                    $countFiles = count($nodes);
                    
                    $mail = new PHPMailer(true);
                    $mail->IsSMTP();
                    
                    $mail->Host = $SMTP_HOST;
                    $mail->Port = $SMTP_PORT; 
                    //$mail->charSet = "utf8";  
                    $mail->SMTPDebug = 0;
                    if ($SMTP_AUTH == "true") {
                        $mail->SMTPAuth = true;
                        $mail->Username = $SMTP_USERNAME;
                        $mail->Password = $SMTP_PASSWORD;
                    }
                    else
                        $mail->SMTPAuth = false;
                    $mail->PluginDir = sfConfig::get('sf_lib_dir').'/PHPMailer/';
                    $tmpFile = array();
                    
                    foreach ($nodes as $Node) {
                        $type = $Node->shortType;
                        $nodeId = $Node->nodeId;

                        try {
                            if ($type == "file") {
                                $Node = $session->getNode($spacesStore, $nodeId);
                                
                                if ($Node!=null) {
                                    $Content = $Node->cm_content;

                                    if ($Content instanceof ContentData && !empty($Node->cm_name)) {
                                        $fileName = sfConfig::get('sf_cache_dir')."/".$Node->cm_name;

                                        if (file_exists($filename))
                                            $fileName = sfConfig::get('sf_cache_dir')."/".date("d.m.Y")."-".$Node->cm_name;   
                                        $Content->readContentToFile($fileName); 
                                        $mail->AddAttachment($fileName,$Node->cm_name);
                                        $tmpFile[] = $fileName;
                                    }
                                }
                            }                        
                        }
                        catch (Exception $e) { 
                            $returnArr["errorMsg"] = $e->getMessage();                         
                        }
                    }

                    if (empty($emailBody))
                        $emailBody = "<p></p>";
                        
                    $mail->MsgHTML(utf8_decode($emailBody));
                    
                    if (is_array($emailTo)) {
                        foreach ($emailTo as $emailAddy) {
                            if (!empty($emailAddy))
                                $mail->AddAddress($emailAddy);
                        }
                    }
                    else
                        $mail->AddAddress($emailTo);
                    
                    $mail->SetFrom($FROM_EMAIL, $FROM_NAME);
                    $mail->Subject = utf8_decode($emailSubject);  
                    
                    if (!empty($emailCC)) {
                        if (is_array($emailCC)) {
                            foreach ($emailCC as $emailAddy) {
                                if (!empty($emailAddy))
                                    $mail->AddCC($emailAddy);
                            }
                        }
                        else
                            $mail->AddCC($emailCC);
                    }
                    
                    if (!empty($emailBCC)) {
                        if (is_array($emailBCC)) {
                            foreach ($emailBCC as $emailAddy) {
                                if (!empty($emailAddy))
                                    $mail->AddBCC($emailAddy);
                            }
                        }
                        else
                            $mail->AddBCC($emailBCC);
                    }
                    
                    $send = $mail->Send();
                                               
                    $mail->ClearAddresses();

                    $mail->ClearBCCs();
                    $mail->ClearCCs();
                    $mail->ClearReplyTos();
                    $mail->ClearAllRecipients();
                    $mail->ClearCustomHeaders();
                    
                    if (count($tmpFile) > 0) {
                        foreach ($tmpFile as $file) {
                            @unlink($file);
                        }
                    }
                    
                    $returnArr["success"] = $send;    
                    if ($send == true)
                        unset($returnArr["errorMsg"]);
                }             
            }
            
    }
    catch (Exception $e) {
        $returnArr["errorMsg"] = $e->getMessage();                 
        $returnArr["success"] = false;                 
    }
        

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($returnArr);
    return $this->renderText($return);
  }
  
  
  public function executePDFMerge(sfWebRequest $request) {
      $nodes = $request->getParameter('nodes');
      
      try {
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();
        
        $spacesStore = new SpacesStore($session);
        
        $nodes = json_decode($nodes);
//print_R(json_encode(array("d1e01901-4863-4adb-880d-3b726aa59ad0")));
//print_R($nodes);

        if (!is_array($nodes) || count($nodes) < 1)
            throw new Exception();

        $PDFMerge = new PDFMerge();
        
        foreach ($nodes as $nodeId) {
            $Node = $session->getNode($spacesStore, $nodeId);
            $name = $Node->cm_name;
            //."/".$name;
            $tempFile = sfConfig::get('sf_cache_dir')."/".md5($name).".pdf";
            $content = $Node->cm_content;
            if ($content != null) {
                $content->readContentToFile($tempFile);
            }
            
            $PDFMerge->addFile($tempFile);
        }
        
        $PDFMerge->merge();
        
        $PDFMerge->Output("Merge.pdf","D");
        
        die();
      }
      catch (Exception $e) {
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->getResponse()->setHttpHeader('Pragma','no-cache');
        $return = json_encode(array("success"=>false));
        return $this->renderText($return);
      }
      
      $this->setLayout(false);
  }
}
