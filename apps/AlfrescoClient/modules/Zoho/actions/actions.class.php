<?php

/**
 * Zoho actions.
 *
 * @package    AlfrescoClient
 * @subpackage Zoho
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ZohoActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {

  }
  
  public function executeZohoUpload(sfWebRequest $request)
  {
    if ( !$this->getUser()->isAuthenticated() ) {
        $this->redirect('homepage');
    }
        
    $nodeId = $this->getRequestParameter('nodeId');
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $Node = $session->getNode($spacesStore, $nodeId);
    if ($Node != null) {

        try {
            $Settings = array();
            $getSettings = array("OnlineEditing","OnlineEditingZohoApiKey","OnlineEditingZohoSkey");
            foreach ($getSettings as $SettingValue) {
                $Setting = Doctrine_Query::create()
                    ->from('Settings s')
                    ->where('s.keystring = ?',$SettingValue)
                    ->fetchOne();
                
                if ($Setting != null) {    
                    $Settings[$SettingValue] = $Setting->getValuestring();
                }
            }

            if ($Settings["OnlineEditing"] == "zoho") {
                $apiKey = $Settings["OnlineEditingZohoApiKey"];
                $sKey = $Settings["OnlineEditingZohoSkey"];

                $Zoho = new ZohoService($repository,$spacesStore,$session);
                $saveUrl = $this->getController()->genUrl("Zoho/FetchZohoFile",true);
                $resultZoho = $Zoho->UploadZoho($Node, $saveUrl, $apiKey,$sKey);

                $result = $this->getVarOfZoho($resultZoho);
            }
            else {
                $result = array("RESULT"=>false);
            }
        }
        catch (Exception $e) {
            $result = array("RESULT"=>false,"WARNING"=>$this->getContext()->getI18N()->__("Not supported mimetype!", null, 'messages'));
        }

        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->getResponse()->setHttpHeader('Pragma','no-cache');


        $return = json_encode($result);
        return $this->renderText($return);   
    }
  }
  
  
  
  private function getVarOfZoho($result) {
      $array = array();
      $split = split("\n",$result);
      for ($x = 0; $x < count($split); $x++) {
          $var = $split[$x];
      
          if (empty($var))
            continue;
          $var = str_replace("\r","",$var);
          $splitChar = split("=",$var);
          
          $name = $splitChar[0];
          
          if (count($splitChar) > 2) {
              $val = "";
            for ($i = 1; $i < count($splitChar); $i++) {  
                if ($i == 2)
                    $val .="=";   
                $val .= $splitChar[$i];
            }
          }
          else
            $val = $splitChar[1];
          
          $array[$name] = $val;
      }
      return $array;
  }
  
  public function executeFetchZohoFile(sfWebRequest $request)
  {
    $Settings = array();
    $getSettings = array("OnlineEditing");
    foreach ($getSettings as $SettingValue) {
        $Setting = Doctrine_Query::create()
            ->from('Settings s')
            ->where('s.keystring = ?',$SettingValue)
            ->fetchOne();
        
        if ($Setting != null) {    
            $Settings[$SettingValue] = $Setting->getValuestring();
        }
    }
    
    if ($Settings["OnlineEditing"] == "zoho") {
        $filename = $this->getRequestParameter('filename');
        $id = $this->getRequestParameter('id');
        $format = $this->getRequestParameter('format');
        $email = $this->getRequestParameter('email');
        $actiontemp = $this->getRequestParameter('actiontemp');
        $result = $this->getContext()->getI18N()->__("An unknown error occured!", null, 'messages');

        if (!empty($id)) {
            $splitId = split("#",$id);
            if (count($splitId) == 2) {
                $nodeId = $splitId[0];
                $ticket = $splitId[1];
                
                try {
                    $user = $this->getUser();                
                    $repository = $user->getRepository();
                    $session = $repository->createSession($ticket);      
                    
                    $spacesStore = new SpacesStore($session);

                    $Node = $session->getNode($spacesStore, $nodeId);
                    if ($Node != null) {
                        $content = $Node->cm_content;
                        if ($content != null && $content instanceof ContentData) {
                            $contentData = $content->getContent();
                            $tmp_filename = $_FILES['content']['tmp_name']; 
                                 
                            $contentFile = tempnam(sfConfig::get('sf_cache_dir'), $nodeId);
                            $upload_status = @move_uploaded_file($tmp_filename, $contentFile); 
                            if ($upload_status == true) {
                                $content->writeContentFromFile($contentFile);
                                
                                $session->save();
                                $result = $this->getContext()->getI18N()->__("Successfully saved to ifresco", null, 'messages');
                                
                                @unlink($contentFile);
                            }
                        }
                    }
                }
                catch (Exception $e) {

                }           
            }
        }
    }
    else
        $result = $this->getContext()->getI18N()->__("ERROR! Not allowed!", null, 'messages');
        
    $this->getResponse()->setHttpHeader('Content-Type','text/html; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');

    $return = $result;
    return $this->renderText($return);   
    
  }
}
