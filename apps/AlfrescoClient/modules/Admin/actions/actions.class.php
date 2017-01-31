<?php

/**
 * Admin actions.
 *
 * @package    AlfrescoClient
 * @subpackage Admin
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
class AdminActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $user = $this->getUser();  
    $isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $this->ifrescoVersion = AlfrescoClientConfiguration::get_version();
  }
  
  public function executeQuickSearch(sfWebRequest $request)
  {
    $user = $this->getUser();  
    $isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $this->FoundSettings = false;  
     
    $QuickSearchSetting = Doctrine_Query::create()
        ->from('Settings s')
        ->where('s.Keystring = ?','QuickSearch')
        ->fetchOne();
        
    if ($QuickSearchSetting != null) {
        $JsonData = $QuickSearchSetting->getValuestring();
        $JsonData = json_decode($JsonData);
        $this->Fields = $JsonData;
        $this->FoundSettings = true;
    }
    else
        $this->Fields = null;
  }
  
  public function executeQuickSearchSubmit($request)
  {
    $returnArr = array("success"=>false);
    $this->forward404Unless($request->isMethod('post'));
     
    $data = $request->getParameter('data');
    
    if (!empty($data)) {
        $data = json_decode($data);
        $params = array(
            'edit'    => $data->edit,
            'fields'    => $data->fields
        );
    }
    else {
        $params = array(
            'edit'    => $request->getParameter('edit'),
            'fields' => $request->getParameter('fields')
        );
    }
    
    try {

        if (!empty($params["fields"]))
            $params["fields"] = json_decode($params["fields"]);
        else
            $params["fields"] = array();
            
        $Fields = array();
        if (count($params["fields"]) > 0) {
            foreach ($params["fields"] as $Entry) {
                $split = split("/",$Entry);
                if (count($split) > 0) {
                    $name = $split[0];
                    $class = $split[1];
                    $label = $split[2];
                    $dataType = $split[3];
                    $type = $split[4];
                    
                    $Fields[] = array("name"=>$name,"class"=>$class,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                }
            }
        }
        
        $Setting = Doctrine_Query::create()
             ->from('Settings s')
             ->where('s.keystring = ?',"QuickSearch")
             ->fetchOne();
             
        if ($Setting == null) 
            $Setting = new Settings();
            
        $Setting->setKeystring("QuickSearch");
        $Setting->setValuestring(json_encode($Fields));
        $Setting->save();
                 
        
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

  
  
  public function executeSearchTemplates(sfWebRequest $request)
  {
    $user = $this->getUser();  
    $isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
  }
  
  public function executeTypeList(sfWebRequest $request)
  {
    $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
      $AllowedtypesList = Doctrine_Query::create()
                 ->from('Allowedtypes a')
                 ->execute();
                 
    $Allowedtypes = "";
    if (count($AllowedtypesList) > 0) {
        foreach ($AllowedtypesList as $Type) {
             if (!empty($Allowedtypes))
                $Allowedtypes .= ",";
            $Allowedtypes .= $Type->getName();  
        }
    } 
    $this->AllowedTypes = $Allowedtypes; 
  }
  
  
  
  public function executeSaveTypeList(sfWebRequest $request) {
    $returnArr = array("success"=>false);

    $dataGet = $request->getParameter('data');
    $data = array();
    $defaultValues = array();
    if (!empty($dataGet)) {
        $data = json_decode($dataGet);            
    }

    if ($dataGet == "{}") {
        $data = $defaultValues;    
    }

    try {
        $returnArr["success"] = true;   
        
        foreach ($data as $Key => $Value) { 
            $continue = true;
            switch ($Key) {
                case "allowedTypes":  
                    $DBType = Doctrine_Query::create()
                                ->delete('Allowedtypes a')
                                ->execute();
                                    
                    if (is_array($Value) && count($Value) > 0) {
                        $Array = $Value[0];
                        foreach ($Array as $Type) {
                            $DBType = new Allowedtypes();
                                
                            $DBType->setName($Type);
                            $DBType->save(); 
                        }
                    }
                break;
                default:
                break;
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
  
  public function executeTypeListValues(sfWebRequest $request)
  {
    $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $values = $request->getParameter('values');

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $valuesArr = array();
    if (!empty($values)) {
        $split = split(",",$values);
        if (count($split) > 0) {
            for ($i = 0; $i < count($split); $i++)
                $valuesArr[] = $split[$i];
        }
        else
            $valuesArr[] = $values;    
    }

        

    $companyHome = $spacesStore->companyHome;  
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    $SubClasses = $RestDictionary->GetSubClassDefinitions("cm_content");

    $types = array();
    if ($SubClasses != null) {
        foreach ($SubClasses as $key => $SubClass) {
            $name = $SubClass->name;            
            $title = $SubClass->title;            
            $description = $SubClass->description;            
            
            if (empty($title))
                $title = $name;
            else
                $title = $name." - ".$title;
                
            if (in_array($name,$valuesArr))
                $state = "selected";
            else
                $state = "";
            $types[] = array('attributes'=>array('value'=>$name,'id'=>str_replace(":","_",$name)),'state'=>$state,'text'=>$title);
        }
    }
    

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($types);
    return $this->renderText($return);  
  }
  
  public function executeAspectList(sfWebRequest $request)
  {
    $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
      $AspectList = Doctrine_Query::create()
                 ->from('Allowedaspects a')
                 ->execute();
                 
    $AllowedAspects = "";
    if (count($AspectList) > 0) {
        foreach ($AspectList as $Aspect) {
             if (!empty($AllowedAspects))
                $AllowedAspects .= ",";
            $AllowedAspects .= $Aspect->getName();  
        }
    } 
    $this->AllowedAspects = $AllowedAspects; 
  }
  
  public function executeSaveAspectList(sfWebRequest $request) {
      
  
    $returnArr = array("success"=>false);

    $dataGet = $request->getParameter('data');
    $data = array();
    $defaultValues = array();
    if (!empty($dataGet)) {
        $data = json_decode($dataGet);            
    }

    if ($dataGet == "{}") {
        $data = $defaultValues;    
    }

    try {
        $returnArr["success"] = true;   
        
        foreach ($data as $Key => $Value) {
            $continue = true;
            switch ($Key) {
                case "allowedAspects":  
                    $DBAspect = Doctrine_Query::create()
                                ->delete('Allowedaspects a')
                                ->execute();
                                    
                    if (is_array($Value) && count($Value) > 0) {
                        $Array = $Value[0];
                        foreach ($Array as $Aspect) {
                            $DBAspect = new Allowedaspects();
                                
                            $DBAspect->setName($Aspect);
                            $DBAspect->save();  
                        }
                    }
                break;
                default:
                break;
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
  
  public function executeAspectListValues(sfWebRequest $request)
  {
    $user = $this->getUser();  
    $isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $values = $request->getParameter('values');

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $valuesArr = array();
    if (!empty($values)) {
        $split = split(",",$values);
        if (count($split) > 0) {
            for ($i = 0; $i < count($split); $i++)
                $valuesArr[] = $split[$i];
        }
        else
            $valuesArr[] = $values;    
    }

        

    $companyHome = $spacesStore->companyHome;  
    
    $RestAspects = new RESTAspects($repository,$spacesStore,$session);
    $AspectList = $RestAspects->GetAllAspects();

    $types = array();
    if ($AspectList != null) {
        foreach ($AspectList as $key => $Aspect) {
            $name = $Aspect->name;            
            $title = $Aspect->title;            
            $description = $Aspect->description;            
            
            if (empty($title))
                $title = $name;
            else
                $title = $name ." - ". $title;
                
            if (in_array($name,$valuesArr))
                $state = "selected";
            else
                $state = "";
            $types[] = array('attributes'=>array('value'=>$name,'id'=>str_replace(":","_",$name)),'state'=>$state,'text'=>$title);
        }
    }

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($types);
    return $this->renderText($return);  
  }
  
  public function executeSearchTemplateDesigner(sfWebRequest $request)
  {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $templateId = $this->getRequestParameter('id');    
    $this->FoundTemplate = false;  
    $this->Multicolumn = 1;
     
    if (!empty($templateId)) {
        $SearchTemplate = Doctrine_Query::create()
             ->from('SearchTemplates t')
             ->where('t.id = ?',$templateId)
             ->fetchOne();
        
        
        if ($SearchTemplate != null) { 
            $JsonData = $SearchTemplate->getJsondata();
            if (!empty($JsonData)) {
                $this->FoundTemplate = true;
                $JsonData = json_decode($JsonData);
                $this->Column1 = $JsonData->Column1;
                $this->Column2 = $JsonData->Column2;
                $this->Tabs = $JsonData->Tabs;
                $this->Multicolumn = $SearchTemplate->getMulticolumn();
                $this->Showdoctype = json_decode($SearchTemplate->getShowdoctype());
                $this->ColumnsetId = $SearchTemplate->getColumnsetId();

                $this->Name = $SearchTemplate->getName();
                $this->Id = $SearchTemplate->getId();
            }
        }
    }
    
    $q = Doctrine_Query::create()
        ->from('SearchColumnSets c');
        
    $this->ColumnSets = $q->execute();
  }
  
  public function executeSystemSettings(sfWebRequest $request)
  {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
      
    $Renderer = Renderer::getInstance();  
    $Renderer->scanRenderers();
    $this->Renderers = $Renderer->listRenderers();
    $q = Doctrine_Query::create()
        ->from('Settings s');
    $Settings = $q->execute();

    $SettingsArray = array();

    foreach ($Settings as $settingKey => $SettingClass) {
        $realKey = $SettingClass->getKeystring();
        $value = $SettingClass->getValuestring();
        switch ($realKey) {
            case "Renderer":
                if (!empty($value)) {
                    $value = json_decode($value);    
                }
            break;
            default:
            break;
        } 
        $SettingsArray[$realKey] = $value;
    }
    
    if (!isset($SettingsArray["DateFormat"]) || empty($SettingsArray["DateFormat"]))
        $SettingsArray["DateFormat"] = "m/d/Y";
        
        if (!isset($SettingsArray["TimeFormat"]) || empty($SettingsArray["TimeFormat"]))
        $SettingsArray["TimeFormat"] = "H:i";
        

    if (!isset($SettingsArray["DefaultTab"]) || empty($SettingsArray["DefaultTab"]))
        $SettingsArray["DefaultTab"] = "preview";          
    
    $this->DefaultTabs = array(
                                "preview"=>array(
                                            "text"=>$this->getContext()->getI18N()->__("Preview", null, 'messages'),
                                            "description"=>$this->getContext()->getI18N()->__("Preview of the Node -> Renderer", null, 'messages')
                                           ),
                                "versions"=>array(
                                            "text"=>$this->getContext()->getI18N()->__("Versions", null, 'messages'),
                                            "description"=>$this->getContext()->getI18N()->__("Version Control of the Node", null, 'messages')
                                           ),
                                "metadata"=>array(
                                            "text"=>$this->getContext()->getI18N()->__("Metadata", null, 'messages'),
                                            "description"=>$this->getContext()->getI18N()->__("Display Metadata of the Node", null, 'messages')
                                           ),
                         );
    
    
    $this->Settings = $SettingsArray;
  } 

  
  
  public function executeEmailSettings(sfWebRequest $request)
  {
    $user = $this->getUser();  
    $isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $this->FoundSettings = false;  
    $this->EmailSettings = array();
     
    $getSettings = array("SMTP_HOST"=>array("name"=>$this->getContext()->getI18N()->__("SMTP Host", null, 'messages'),"value"=>""),
                         "SMTP_PORT"=>array("name"=>$this->getContext()->getI18N()->__("SMTP Port", null, 'messages'),"value"=>"25"),
                         "SMTP_AUTH"=>array("name"=>$this->getContext()->getI18N()->__("SMTP Authentication", null, 'messages'),"type"=>"checkbox","value"=>"true"),
                         "SMTP_USERNAME"=>array("name"=>$this->getContext()->getI18N()->__("SMTP Username", null, 'messages'),"value"=>""),
                         "SMTP_PASSWORD"=>array("name"=>$this->getContext()->getI18N()->__("SMTP Password", null, 'messages'),"value"=>""),
                         ""=>array("name"=>"","value"=>""),
                         "FROM_EMAIL"=>array("name"=>$this->getContext()->getI18N()->__("From Email", null, 'messages'),"value"=>""),
                         "FROM_NAME"=>array("name"=>$this->getContext()->getI18N()->__("From Name", null, 'messages'),"value"=>"ifresco client"),
                         );
    foreach ($getSettings as $SettingKey => $SettingEntry) {

        $Setting = Doctrine_Query::create()
        ->from('Settings s')
        ->where('s.Keystring = ?',$SettingKey)
        ->fetchOne();
        
        $this->EmailSettings[$SettingKey] = array();
        $this->EmailSettings[$SettingKey]["name"] = $SettingEntry["name"];
        if (isset($SettingEntry["type"]))
        $this->EmailSettings[$SettingKey]["type"] = $SettingEntry["type"];
              
       if ($Setting != null) {

           $this->EmailSettings[$SettingKey]["value"] = $Setting->getValuestring();
            
           if (isset($SettingEntry["type"]) && $SettingEntry["type"] == "checkbox" && $this->EmailSettings[$SettingKey]["value"] == "true") {
            $this->EmailSettings[$SettingKey]["checked"] = true;
           }
           
           if (isset($SettingEntry["type"]) && $SettingEntry["type"] == "checkbox")
            $this->EmailSettings[$SettingKey]["value"] = "true";
           
           $this->FoundSettings = true;
        }
        else {
           $this->EmailSettings[$SettingKey]["value"] = $SettingEntry["value"];
        }
    }
  }
  
  public function executeSaveEmailSettings(sfWebRequest $request) {
    $returnArr = array("success"=>false);

    $dataGet = $request->getParameter('data');
    
    $data = array();
    $defaultValues = array("SMTP_AUTH"=>"false","SMTP_PORT"=>"25","FROM_NAME"=>"ifresco client");
    if (!empty($dataGet)) {
        $data = json_decode($dataGet);            
    }

    if ($dataGet == "{}") {
        $data = $defaultValues;    
    }
     
    try {
        $continue = false;
        $dataArr = (array)$data;
        if (!isset($dataArr["SMTP_AUTH"])) {
            $append = array("SMTP_AUTH"=>"false");
            $data = (object)array_merge((array)$data, (array)$append);
        }

        foreach ($data as $Key => $Value) {
            $Setting = Doctrine_Query::create()
                 ->from('Settings s')
                 ->where('s.keystring = ?',$Key)
                 ->fetchOne();
                 
                 
            if ($Setting == null) {
                $Setting = new Settings();
            
            }
            
            $Setting->setKeystring($Key);
            $Setting->setValuestring($Value);
            $Setting->save();
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
  
  public function executeOnlineEditing(sfWebRequest $request)
  {
    $user = $this->getUser();  
    $isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $this->FoundSettings = false;  
     
    $getSettings = array("OnlineEditing"=>"none","OnlineEditingZohoApiKey"=>"","OnlineEditingZohoSkey"=>"");
    foreach ($getSettings as $SettingKey => $SettingDefVal) {

        $Setting = Doctrine_Query::create()
        ->from('Settings s')
        ->where('s.Keystring = ?',$SettingKey)
        ->fetchOne();
             
       if ($Setting != null) {
            $this->{$SettingKey} = $Setting->getValuestring();
            $this->FoundSettings = true;
        }
        else
            $this->{$SettingKey} = $SettingDefVal;
    }
  }
  
  public function executeSaveOnlineEditing(sfWebRequest $request) {
    $returnArr = array("success"=>false);

    $dataGet = $request->getParameter('data');
    $data = array();
    $defaultValues = array("OnlineEditing"=>null,"OnlineEditingZohoApiKey"=>null,"OnlineEditingZohoSkey"=>null);
    if (!empty($dataGet)) {
        $data = json_decode($dataGet);            
    }

    if ($dataGet == "{}") {
        $data = $defaultValues;    
    }
     
    try {

        $saveSettings = array("OnlineEditing","OnlineEditingZohoApiKey","OnlineEditingZohoSkey");
        foreach ($saveSettings as $SettingKey) {
            $SettingVal = $data->{$SettingKey};

            if ($SettingVal == null)
                $SettingVal = "";
            $Setting = Doctrine_Query::create()
                 ->from('Settings s')
                 ->where('s.keystring = ?',$SettingKey)
                 ->fetchOne();
                 
            if ($Setting == null) 
                $Setting = new Settings();
                
            $Setting->setKeystring($SettingKey);
            $Setting->setValuestring($SettingVal);
            $Setting->save();
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
  

  
  public function executeSaveSystemSettings(sfWebRequest $request) {
    $returnArr = array("success"=>false);

    $dataGet = $request->getParameter('data');
    $data = array();
    $defaultValues = array("Renderer"=>array(),"DefaultTab"=>"preview");
    if (!empty($dataGet)) {
        $data = json_decode($dataGet);            
    }

    if ($dataGet == "{}") {
        $data = $defaultValues;    
    }
     
    try {
        $returnArr["success"] = true;   
        
        $continue = false;
        foreach ($data as $Key => $Value) {
               
            $continue = true;
            switch ($Key) {
                case "Renderer":
                    $Value = json_encode($Value);
                break;
                case "DefaultTab":
                case "DateFormat":
                case "TimeFormat":
                break;
                default:
                    $continue = false;
                break;
            }

            if ($continue == true) {
                $Setting = Doctrine_Query::create()
                     ->from('Settings s')
                     ->where('s.keystring = ?',$Key)
                     ->fetchOne();
                     
                if ($Setting == null) 
                    $Setting = new Settings();
                    
                $Setting->setKeystring($Key);
                $Setting->setValuestring($Value);
                $Setting->save();
                     
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
  
  public function executeTemplates(sfWebRequest $request)
  {
    $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
  }
  
  public function executeNameSpaceMapping(sfWebRequest $request)
  {
    $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
  }
  
  public function executeSearchColumnSets(sfWebRequest $request)
  {
    $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
  }
  
  public function executeSearchColumnSetsAdd(sfWebRequest $request)
  {
    $columnsetId = $this->getRequestParameter('id');    
    $this->FoundColumnset = false;  
     
    if (!empty($columnsetId)) {
        $SearchColumnSet = Doctrine_Query::create()
             ->from('SearchColumnSets c')
             ->where('c.id = ?',$columnsetId)
             ->fetchOne();
        
        
        if ($SearchColumnSet != null) { 
            $JsonData = $SearchColumnSet->getJsonfields();
            if (!empty($JsonData)) {
                $this->FoundColumnset = true;
                $JsonData = json_decode($JsonData);
                $this->Columns = $JsonData;

                $this->Name = $SearchColumnSet->getName();
                $this->Id = $SearchColumnSet->getId();
            }
        }
    }
  }
  
  public function executeAddPropertyValues(sfWebRequest $request) {
      $user = $this->getUser();                
      $repository = $user->getRepository();
      $session = $user->getSession();
      $ticket = $user->getTicket();

      $spacesStore = new SpacesStore($session);
      
      $values = $request->getParameter('values');
      $outputValue = "";
      
      if (!empty($values)) {
          $splitValues = split(",",$values);
          
          if (count($splitValues) == 0) {
              $splitValues = array($values);
          }
          $RestDictionary = new RESTDictionary($repository,$store,$session);
          
          for ($i = 0; $i < count($splitValues); $i++) {
              $info = split("/",$splitValues[$i]);

              $PropertyInfo = $RestDictionary->GetClassProperty($info[0],$info[1]);
              if (count($PropertyInfo) > 0) {
                  $outputValue .= '<li id="'.$PropertyInfo->name.'/'.$info[0].'/'.$PropertyInfo->title.'/'.$PropertyInfo->dataType.'/property"><div class="form_row">'.$PropertyInfo->title.' <span style="font-size:10px">('.$PropertyInfo->dataType.')</span><div class="metaActions"><img src="/images/icons/arrow_out.png" class="moveAction"></div></div></li>';
                  
                  
              }
          }
      }

      return $this->renderText($outputValue); 
  }
  
  public function executeNameSpaceMapListDelete(sfWebRequest $request) {
    $returnArr = array("success"=>false);
    
    $NameSpace = $request->getParameter('data');
    if (!empty($NameSpace)) {
        $returnArr["success"] = true;
        $NameSpace = json_decode($NameSpace);
        
        $qN = Doctrine_Query::create()
             ->delete()
             ->from('NameSpaceMapping n')
             ->where('n.namespace = ?', $NameSpace->namespace)
             ->andWhere('n.prefix = ?', $NameSpace->prefix)
             ->execute();
    } 
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($returnArr);
    return $this->renderText($return); 
  }

  public function executeNameSpaceMapListUpdate(sfWebRequest $request) {
    $returnArr = array("success"=>false);
    
    $data = $request->getParameter('data');
    if (!empty($data)) {
        $returnArr["success"] = true;
        $data = json_decode($data);

        foreach ($data as $NameSpace) {
            $id = 0;
            if (isset($NameSpace->id) && $NameSpace->id != 0) {
                $id = $NameSpace->id;
                Doctrine_Query::create()
                      ->update('NameSpaceMapping n')
                      ->set('n.namespace', '?', $NameSpace->namespace)
                      ->set('n.prefix', '?', $NameSpace->prefix)
                      ->where('n.id = ?', $NameSpace->id)
                      ->execute();
            }
            else {
                $qN = Doctrine_Query::create()
                     ->from('NameSpaceMapping n')
                     ->where('n.namespace = ?', $NameSpace->namespace)
                     ->orWhere('n.prefix = ?', $NameSpace->prefix);
                     
                $NameSpaceMap = $qN->fetchOne();
                if ($NameSpaceMap == null) {
                    $NameSpaceMap = new NameSpaceMapping();
                }
                
                $NameSpaceMap->setNamespace($NameSpace->namespace);
                $NameSpaceMap->setPrefix($NameSpace->prefix);
                $NameSpaceMap->save();
            }
        }
        
        
    }

    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($returnArr);
    return $this->renderText($return); 
  }
  
  public function executeNameSpaceMapList(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
     $q = Doctrine_Query::create()
        ->from('NameSpaceMapping n');
 
    $NameSpaces = $q->execute();
    
    $templateArray = array("namespacemaps"=>array());
    
    
    foreach ($NameSpaces as $namespaceKey => $namespacemap) {
        $templateArray["namespacemaps"][] = array("id"=>$namespacemap->getId(),
                                              "namespace"=>$namespacemap->getNamespace(),
                                              "prefix"=>$namespacemap->getPrefix());
    }
    

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($templateArray);
    return $this->renderText($return); 
  }
  
  public function executeSearchTemplateList(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $q = Doctrine_Query::create()
        ->from('SearchTemplates t');
 
    $templates = $q->execute();
    
    $templateArray = array("templates"=>array());
    
    
    foreach ($templates as $templateKey => $template) {
        $JsonData = json_decode($template->getJsondata());
        $tabcount = 0;
        if (count($JsonData->Tabs) > 0)
            if (count($JsonData->Tabs->tabs) > 0)
                $tabcount = count($JsonData->Tabs->tabs);
        
        
        $actionList = '<a href="javascript:editSearchTemplate(\''.$template->getId().'\');"><img src="/images/admin/layout_edit.png" title="'.
$this->getContext()->getI18N()->__("Edit template", null, 'messages').'" border="0"></a> <a href="javascript:deleteSearchTemplate(\''.$template->getId().'\',\''.$template->getName().'\');"><img src="/images/admin/layout_delete.png" title="'.$this->getContext()->getI18N()->__("Delete template" , null, 'messages').'" border="0"></a>';     
        
        if ($template->getDefaultview() != 1) {
            $actionList .= ' <a href="javascript:markDefaultSearchTemplate(\''.$template->getId().'\');"><img src="/images/admin/star.png" title="'.$this->getContext()->getI18N()->__("Mark as default template" , null, 'messages').'" border="0"></a>';
        }
        
        
        
        $templateArray["templates"][] = array("id"=>$template->getId(),
                                              "name"=>$template->getName(),
                                              "defaultview"=>($template->getDefaultview() == 1 ? true : false),
                                              "multiColumns"=>($template->getMulticolumn() == 1 ? true : false),
                                              "action"=>$actionList);
    }
    

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($templateArray);
    return $this->renderText($return);
  }
  
  public function executeSearchColumnSetList(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $q = Doctrine_Query::create()
        ->from('SearchColumnSets c');
        
    $columns = $q->execute();
    
    $columnsArray = array("columns"=>array());
    
    
    foreach ($columns as $columnKey => $column) {
        
        $actionList = '<a href="javascript:editColumnSet(\''.$column->getId().'\');"><img src="/images/admin/layout_edit.png" title="'.
$this->getContext()->getI18N()->__("Edit ColumnSet", null, 'messages').'" border="0"></a> <a href="javascript:deleteColumnSet(\''.$column->getId().'\',\''.$column->getName().'\');"><img src="/images/admin/layout_delete.png" title="'.$this->getContext()->getI18N()->__("Delete ColumnSet" , null, 'messages').'" border="0"></a>';     
        
        if ($column->getDefaultset() != 1) {
            $actionList .= ' <a href="javascript:markDefaultColumnSet(\''.$column->getId().'\');"><img src="/images/admin/star.png" title="'.$this->getContext()->getI18N()->__("Mark as default ColumnSet" , null, 'messages').'" border="0"></a>';
        }
        
        $columnsArray["columns"][] = array("id"=>$column->getId(),
                                              "name"=>$column->getName(),
                                              "defaultset"=>($column->getDefaultset() == 1 ? true : false),
                                              "action"=>$actionList);
    }
    

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($columnsArray);
    return $this->renderText($return);
  }
  
  
  public function executeMarkDefaultColumnSet(sfWebRequest $request) {
    $id = $this->getRequestParameter('id');
    $data = array("success"=>false);
    if (!empty($id)) {
        Doctrine_Query::create()
                  ->update('SearchColumnSets c')
                  ->set('c.defaultset', '?', false)
                  ->where('c.defaultset = ?', true)
                  ->execute();
                  
        Doctrine_Query::create()
                  ->update('SearchColumnSets c')
                  ->set('c.defaultset', '?', true)
                  ->where('c.id = ?', $id)
                  ->execute();
        $data["success"] = true;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);
  } 
  
  public function executeDeleteSearchColumnSet(sfWebRequest $request) {
    $id = $this->getRequestParameter('id');
    $data = array("success"=>false);
    if (!empty($id)) {
        $deleted = Doctrine_Query::create()
                  ->delete()
                  ->from('SearchColumnSets c')
                  ->andWhere('c.id = ?',$id)
                  ->execute();
                  
        $data["success"] = true;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);
  }
  
  public function executeTemplateList(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $q = Doctrine_Query::create()
        ->from('ContentModelTemplates t');
 
    $templates = $q->execute();
    
    $templateArray = array("templates"=>array());
    
    
    foreach ($templates as $templateKey => $template) {
        $JsonData = json_decode($template->getJsondata());
        $tabcount = 0;
        if (count($JsonData->Tabs) > 0)
            if (count($JsonData->Tabs->tabs) > 0)
                $tabcount = count($JsonData->Tabs->tabs);
                
        $templateArray["templates"][] = array("id"=>$template->getId(),
                                              "class"=>$template->getClass(),
                                              "multiColumns"=>($template->getMulticolumn() == 1 ? true : false),
                                              "aspectsView"=>$template->getAspectView(),
                                              "tabs"=>$tabcount,
                                              "action"=>'<a href="javascript:editTemplate(\''.$template->getId().'\');"><img src="/images/admin/layout_edit.png" title="'.$this->getContext()->getI18N()->__("Edit template" , null, 'messages').'" border="0"></a> <a href="javascript:deleteTemplate(\''.$template->getId().'\',\''.$template->getClass().'\');"><img src="/images/admin/layout_delete.png" title="'.$this->getContext()->getI18N()->__("Delete template" , null, 'messages').'" border="0"></a>');
    }
    

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($templateArray);
    return $this->renderText($return);
  }
  
  
  public function executeMarkDefaultSearchTemplate(sfWebRequest $request) {
    $id = $this->getRequestParameter('id');
    $data = array("success"=>false);
    if (!empty($id)) {
        Doctrine_Query::create()
                  ->update('SearchTemplates t')
                  ->set('t.defaultview', '?', false)
                  ->where('t.defaultview = ?', true)
                  ->execute();
                  
        Doctrine_Query::create()
                  ->update('SearchTemplates t')
                  ->set('t.defaultview', '?', true)
                  ->where('t.id = ?', $id)
                  ->execute();
        $data["success"] = true;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);
  } 
  
  
  public function executeDeleteSearchTemplate(sfWebRequest $request) {
    $id = $this->getRequestParameter('id');
    $data = array("success"=>false);
    if (!empty($id)) {
        $deleted = Doctrine_Query::create()
                  ->delete()
                  ->from('SearchTemplates t')
                  ->andWhere('t.id = ?',$id)
                  ->execute();
                  
        $data["success"] = true;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);
  } 
  
  public function executeDeleteTemplate(sfWebRequest $request) {
    $id = $this->getRequestParameter('id');
    $data = array("success"=>false);
    if (!empty($id)) {
        $deleted = Doctrine_Query::create()
                  ->delete()
                  ->from('ContentModelTemplates t')
                  ->andWhere('t.id = ?',$id)
                  ->execute();
                  
        $data["success"] = true;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);
  }
  
  public function executeTemplateDesigner(sfWebRequest $request)
  {
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);


    $companyHome = $spacesStore->companyHome;  
        
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $class = $this->getRequestParameter('class');
    $templateId = $this->getRequestParameter('id');    
    
    $this->FoundTemplate = false;
    if (!empty($templateId)) {
        $Template = Doctrine_Query::create()
             ->from('ContentModelTemplates t')
             ->where('t.id = ?',$templateId)
             ->fetchOne();
        
        
        if ($Template != null) { 
            $JsonData = $Template->getJsondata();

            if (!empty($JsonData)) {
                $this->FoundTemplate = true;
                $usedFields = array();
                
                $JsonData = json_decode($JsonData);
                $this->Column1 = $JsonData->Column1;
                $this->Column2 = $JsonData->Column2;
                $this->Tabs = $JsonData->Tabs;
                $this->Multicolumn = $Template->getMulticolumn();
                $this->Aspectsview = $Template->getAspectview();

                $this->Class = $Template->getClass();
                $this->Id = $Template->getId();
                
                
                $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
                $ClassProperties = $RestDictionary->GetClassProperties($this->Class);
                $ClassAssociation = $RestDictionary->GetClassAssociations($this->Class);
                
                
                foreach ($this->Column1 as $Prop) {
                    $usedFields[] = $Prop->name;
                }
                foreach ($this->Column2 as $Prop) {
                    $usedFields[] = $Prop->name;
                }
                
                if (count($this->Tabs->tabs) > 0) {
                    foreach ($this->Tabs->tabs as $Tab) {
                        foreach ($Tab->items as $Prop) {
                            $usedFields[] = $Prop->name;
                        }
                    }
                }

                
                if ($ClassProperties != null) {
                    $DeletedProps = array();
                    $ignoreArray = array("cm:content");
                    foreach ($ClassProperties as $Prop) {
                        if (!in_array($Prop->name,$ignoreArray) && !in_array($Prop->name,$usedFields))
                            $DeletedProps[] = $Prop;
                    }
                    $this->DeletedProps = $DeletedProps;
                }

                if ($ClassAssociation != null) {
                    $DeletedAssocs = array();
                    foreach ($ClassAssociation as $Assoc) {
                        if (!in_array($Prop->name,$usedFields))
                            $DeletedAssocs[] = $Assoc;
                    }
                    $this->DeletedAssocs = $DeletedAssocs;
                }
                
                
            }
        }
    }
    if ($this->FoundTemplate == false) {

        
        
        $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
        $class = str_replace(":","_",$class);
        $ClassProperties = $RestDictionary->GetClassProperties($class);
        $ClassAssociation = $RestDictionary->GetClassAssociations($class);
        
        
        if ($ClassProperties != null) {
            $ClassPropertiesTemp = array();
            $ignoreArray = array("cm:content");
            foreach ($ClassProperties as $Prop) {
                if (!in_array($Prop->name,$ignoreArray))
                    $ClassPropertiesTemp[] = $Prop;
            }
            $this->properties = $ClassPropertiesTemp;
        }

        if ($ClassAssociation != null) {
            $this->associations = $ClassAssociation;
        }
        
        $this->Class = $class;
        $this->Multicolumn = 1; 
        $this->Aspectsview = "";
    }
  }
  
  public function executeSearchColumnSetSubmit($request)
  {
    $returnArr = array("success"=>false);
    $this->forward404Unless($request->isMethod('post'));
     
    $data = $request->getParameter('data');
    
    if (!empty($data)) {
        $data = json_decode($data);
        $params = array(
            'edit'    => $data->edit,
            'name'    => $data->name,
            'cols'    => $data->cols
        );
    }
    else {
        $params = array(
            'edit'    => $request->getParameter('edit'),
            'name'    => $request->getParameter('name'),
            'cols' => $request->getParameter('cols')
        );
    }
    
    try {

        if (!empty($params["cols"]))
            $params["cols"] = json_decode($params["cols"]);
        else
            $params["cols"] = array();
            
        $Columns = array();
        if (count($params["cols"]) > 0) {
            foreach ($params["cols"] as $Entry) {
                $split = split("/",$Entry);
                if (count($split) > 0) {
                    $name = $split[0];
                    $class = $split[1];
                    $label = $split[2];
                    $dataType = $split[3];
                    $type = $split[4];
                    $showHide = $split[5];
                    $hide = false;
                    if ($showHide == "hide")
                        $hide = true;
                    
                    $Columns[] = array("name"=>$name,"class"=>$class,"dataType"=>$dataType,"title"=>$label,"type"=>$type,"hide"=>$hide);
                }
            }
        }
        
        $q = Doctrine_Query::create()
             ->from('SearchColumnSets c')
             ->where('c.defaultset=1')
             ->limit(1)
             ->fetchArray();

        if ($params["edit"] == null || empty($params["edit"]) || $params["edit"] == "null") {                
            $ColumnSet = new SearchColumnSets();
            if ($q == null)
                $ColumnSet->setDefaultset(1);
            else
                $ColumnSet->setDefaultset(0);
        }
        else {
            $ColumnSet = Doctrine_Query::create()
             ->from('SearchColumnSets c')
             ->where('c.id = ?',$params["edit"])
             ->fetchOne();
        }

        $ColumnSet->setName($params['name']);
        
        $ColumnSet->setJsonfields(json_encode($Columns));
        $ColumnSet->save();
        
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
  
  public function executeSearchTemplateDesignerSubmit($request)
  {
    $returnArr = array("success"=>false);
    
    $this->forward404Unless($request->isMethod('post'));
     
    $data = $request->getParameter('data');

    if (!empty($data)) {
        $data = json_decode($data);
        $params = array(
            'edit'    => $data->edit,
            'name'    => $data->name,
            'showdoctype'    => $data->showDoctype,
            'multiColumns'    => $data->multiColumns,
            'columnset'    => $data->columnset,
            'col1'    => $data->col1,
            'col2'    => $data->col2,
            'tabs'    => $data->tabs
        );
    }
    else {
        $params = array(
            'edit'    => $request->getParameter('edit'),
            'name'    => $request->getParameter('name'),
            'showdoctype' => $request->getParameter('showDoctype'),
            'multiColumns'    => $request->getParameter('multiColumns'),
            'columnset' => $request->getParameter('columnset'),
            'col1'    => $request->getParameter('col1'),
            'col2'    => $request->getParameter('col2'),
            'tabs'    => $request->getParameter('tabs'),
        );
    }
     
    
     
     try {

        if (!empty($params["col1"]))
            $params["col1"] = json_decode($params["col1"]);
        else
            $params["col1"] = array();
            
        if (!empty($params["col2"]))
            $params["col2"] = json_decode($params["col2"]);
        else
            $params["col2"] = array();
            
        if (!empty($params["tabs"]))
            $params["tabs"] = json_decode($params["tabs"]);
        else
            $params["tabs"] = array();
            
        $newCol1 = array();
        if (count($params["col1"]) > 0) {
            foreach ($params["col1"] as $Entry) {
                $split = split("/",$Entry);
                if (count($split) > 0) {
                    $name = $split[0];
                    $class = $split[1];
                    $label = $split[2];
                    $dataType = $split[3];
                    $type = $split[4];
                    
                    $newCol1[] = array("name"=>$name,"class"=>$class,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                }
            }
        }
        
        $newCol2 = array();
        if (count($params["col2"]) > 0) {
            foreach ($params["col2"] as $Entry) {
                $split = split("/",$Entry);
                if (count($split) > 0) {
                    $name = $split[0];
                    $class = $split[1];
                    $label = $split[2];
                    $dataType = $split[3];
                    $type = $split[4];
                    
                    $newCol2[] = array("name"=>$name,"class"=>$class,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                }
            }
        }
        
        $newTabs = array();
        if (count($params["tabs"]) > 0) {
            if (isset($params["tabs"]->tabs)) {
                $tabItems = $params["tabs"]->tabs[0]->items;
                foreach ($tabItems as $Entry) {
                    $split = split("/",$Entry);
                    if (count($split) > 0) {
                        $name = $split[0];
                        $class = $split[1];
                        $label = $split[2];
                        $dataType = $split[3];
                        $type = $split[4];
                        
                        $newTabs[] = array("name"=>$name,"class"=>$class,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                    }
                }
            }
            else {
                foreach ($params["tabs"] as $Entry) {
                    $split = split("/",$Entry);
                    if (count($split) > 0) {
                        $name = $split[0];
                        $class = $split[1];
                        $label = $split[2];
                        $dataType = $split[3];
                        $type = $split[4];
                        
                        $newTabs[] = array("name"=>$name,"class"=>$class,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                    }
                }
            }
        }
        
        if (empty($params['showdoctype']))
            $params['showdoctype'] = array();
        else {
            if (is_array($params['showdoctype']) && count($params['showdoctype']) > 0) {
                if (is_array($params['showdoctype'][0]))
                    $params['showdoctype'] = $params['showdoctype'][0];    
            }
        }
        
        $Columns = array("Column1"=>$newCol1,
                         "Column2"=>$newCol2,
                         "Tabs"=>$params["tabs"]);
                         
        

        $q = Doctrine_Query::create()
             ->from('SearchTemplates t')
             ->where('t.defaultview=1')
             ->limit(1)
             ->fetchArray();

        if ($params["edit"] == null || empty($params["edit"]) || $params["edit"] == "null") {                
            $Template = new SearchTemplates();
            if ($q == null)
                $Template->setDefaultview(1);
            else
                $Template->setDefaultview(0);
        }
        else {
            $Template = Doctrine_Query::create()
             ->from('SearchTemplates t')
             ->where('t.id = ?',$params["edit"])
             ->fetchOne();
        }

        $Template->setName($params['name']);
        
        $Template->setColumnsetId($params['columnset']);
        $Template->setShowdoctype(json_encode($params['showdoctype']));
        $Template->setMulticolumn(($params['multiColumns'] == "true" ? 1 : 0));
        $Template->setJsondata(json_encode($Columns));
        $Template->save();
        
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
  
  public function executeTemplateDesignerSubmit($request)
  {
      $returnArr = array("success"=>false);
    $this->forward404Unless($request->isMethod('post'));
    
    $data = $request->getParameter('data');

    if (!empty($data)) {
        $data = json_decode($data);
        $params = array(
            'edit'    => $data->edit,
            'class'    => $data->class,
            'multiColumns'    => $data->multiColumns,
            'aspectsView'    => $data->aspectsView,
            'col1'    => $data->col1,
            'col2'    => $data->col2,
            'tabs'    => $data->tabs
        );
    }
    else {
        $params = array(
            'edit'    => $request->getParameter('edit'),        
            'class'    => $request->getParameter('class'),
            'multiColumns'    => $request->getParameter('multiColumns'),
            'aspectsView'    => $request->getParameter('aspectsView'),
            'col1'    => $request->getParameter('col1'),
            'col2'    => $request->getParameter('col2'),
            'tabs'    => $request->getParameter('tabs'),
        );
    }

        try {
        
        if (!empty($params["col1"]))
            $params["col1"] = json_decode($params["col1"]);
        else
            $params["col1"] = array();
            
        if (!empty($params["col2"]))
            $params["col2"] = json_decode($params["col2"]);
        else
            $params["col2"] = array();
            
        if (!empty($params["tabs"]))
            $params["tabs"] = json_decode($params["tabs"]);
        else
            $params["tabs"] = array();

                         
        $newCol1 = array();
        if (count($params["col1"]) > 0) {
            foreach ($params["col1"] as $Entry) {
                $split = split("/",$Entry);
                if (count($split) > 0) {
                    $name = $split[0];
                    $label = $split[1];
                    $dataType = $split[2];
                    $type = $split[3];
                    
                    if (!empty($name))
                        $newCol1[] = array("name"=>$name,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                }
            }
        }
        
        $newCol2 = array();
        if (count($params["col2"]) > 0) {
            foreach ($params["col2"] as $Entry) {
                $split = split("/",$Entry);
                if (count($split) > 0) {
                    $name = $split[0];
                    $label = $split[1];
                    $dataType = $split[2];
                    $type = $split[3];
                    
                    if (!empty($name))   
                        $newCol2[] = array("name"=>$name,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                }
            }
        }
        
        $newTabs = array();

        if (count($params["tabs"]->tabs) > 0) {
            $newTabs = $params["tabs"];
            foreach ($params["tabs"]->tabs as $TabKey => $Tab) {   
                if (count($Tab->items) > 0) {
                    foreach ($Tab->items as $key => $Entry) {
                        $split = split("/",$Entry);
                        if (count($split) > 0) {
                            $name = $split[0];
                            $label = $split[1];
                            $dataType = $split[2];
                            $type = $split[3];
                            
                            if (!empty($name))   
                                $newTabs->tabs[$TabKey]->items[$key] = array("name"=>$name,"dataType"=>$dataType,"title"=>$label,"type"=>$type);
                        }
                    }
                }
            }
        }
               
        $Columns = array("Column1"=>$newCol1,
                         "Column2"=>$newCol2,
                         "Tabs"=>$newTabs);
        
        if ($params["edit"] == null || empty($params["edit"]) || $params["edit"] == "null") {                
            $Template = new ContentModelTemplates();

        }
        else {
            $Template = Doctrine_Query::create()
             ->from('ContentModelTemplates t')
             ->where('t.id = ?',$params["edit"])
             ->fetchOne();
        }
        
        $Template->setClass($params['class']);
        $Template->setMulticolumn(($params['multiColumns'] == "true" ? 1 : 0));
        $Template->setAspectView($params['aspectsView']);
        $Template->setJsondata(json_encode($Columns));
        $Template->save();
        
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

  
  public function executeTemplateContentTypes(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $asmselect = $request->getParameter('asmselect');
    $values = $request->getParameter('values');
    if (empty($asmselect))
        $asmselect = false;

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    

    $valuesArr = array();
    if (!empty($values)) {
        $split = split(",",$values);
        if (count($split) > 0) {
            for ($i = 0; $i < count($split); $i++)
                $valuesArr[] = $split[$i];
        }
        else
            $valuesArr[] = $values;    
    }

        

    $companyHome = $spacesStore->companyHome;  
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    $SubClasses = $RestDictionary->GetSubClassDefinitions("cm_content");
    if ($asmselect == false)
        $types = array("types"=>array());
    else
        $types = array();
    if ($SubClasses != null) {
        foreach ($SubClasses as $key => $SubClass) {
            $name = $SubClass->name;            
            $title = $SubClass->title;            
            $description = $SubClass->description;            
            
            if (empty($title))
                $title = $name;
            else
                $title = $name ." - ". $title;
                
            if ($asmselect == false)
                $types["types"][] = array("name"=>$name,"title"=>$title,"description"=>$description);
            else {
                if (in_array($name,$valuesArr))
                    $state = "selected";
                else
                    $state = "";
                $types[] = array('attributes'=>array('value'=>$name,'id'=>str_replace(":","_",$name)),'state'=>$state,'text'=>$title);
            }
        }
    }
   
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($types);
    return $this->renderText($return);
  }
  
  
  public function executeTemplateProperties(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $companyHome = $spacesStore->companyHome;  
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    $Props = $RestDictionary->GetAllProperties();
    
    $types = array();
    $index = 0;
    if ($Props != null) {
        foreach ($Props as $key => $Property) {
            $name = $Property->name;            
            $title = $Property->title;            
            $dataType = $Property->dataType;            
            if (empty($title) || $title == "null")
                $showTitle = $name;
            else
                $showTitle = $name." ".$title;
            $value = $name."//".$title."/".$dataType;

            $state = "";
                
            $types[$index] = array('attributes'=>array('value'=>$value,'id'=>str_replace(":","_",$name)),'state'=>$state,'text'=>$showTitle);
            $index++;
        }
    }
 
    $typesArr = $this->array_sort($types, "text");
    $types = $typesArr;
    if (count($typesArr) > 0) {
        $types = array();
        foreach ($typesArr as $k => $v) {
            $types[] = $v;
        }
    }
      
      
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($types);
    return $this->renderText($return);
  }
  
  public function executeTemplateAssociations(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $companyHome = $spacesStore->companyHome;  
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    $Props = $RestDictionary->GetAllProperties();
    
    $types = array();
    $index = 0;
    if ($Props != null) {
        foreach ($Props as $key => $Property) {
            $name = $Property->name;            
            $title = $Property->title;            
            $dataType = $Property->dataType;            
            if (empty($title) || $title == "null")
                $showTitle = $name;
            else
                $showTitle = $name." ".$title;
            $value = $name."//".$title."/".$dataType;

            $state = "";
                
            $types[$index] = array('attributes'=>array('value'=>$value,'id'=>str_replace(":","_",$name)),'state'=>$state,'text'=>$showTitle);
            $index++;
        }
    }
 
    $typesArr = $this->array_sort($types, "text");
    $types = $typesArr;
    if (count($typesArr) > 0) {
        $types = array();
        foreach ($typesArr as $k => $v) {
            $types[] = $v;
        }
    }
      
      
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($types);
    return $this->renderText($return);
  }
  
  private function array_sort($array, $on, $order=SORT_ASC) {
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
  }
  
  public function executeTemplateMetadata(sfWebRequest $request) {
      $user = $this->getUser();  
$isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
    $class = $this->getRequestParameter('class');

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    


    $companyHome = $spacesStore->companyHome;  
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    $class = str_replace(":","_",$class);
    $ClassProperties = $RestDictionary->GetClassProperties($class);
    $ClassAssociation = $RestDictionary->GetClassAssociations($class);
    
    $array = array("Properties"=>array(),
                   "Associations"=>array());
    if ($ClassProperties != null) {
        $array["Properties"] = $ClassProperties;
    }

    if ($ClassAssociation != null) {
        $array["Associations"] = $ClassAssociation;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($array);
    return $this->renderText($return);
  }
  
  public function executeLookups(sfWebRequest $request)
  {
    $user = $this->getUser();  
    $isAdmin = $user->isAdmin();             
    if ($isAdmin == false)
        $this->forward('default', 'module');
        
            
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);    
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    $Props = $RestDictionary->GetAllProperties();
    
    $this->Fields = array();
    
    

    if ($Props != null) {
        foreach ($Props as $key => $Property) {
            $name = $Property->name;            
            $title = $Property->title;            
            $dataType = $Property->dataType;    
            if ($dataType != "d:text" && $dataType != "d:mltext")        
                continue;
                
            if (empty($title) || $title == "null")
                $showTitle = $name;
            else
                $showTitle = $name." ".$title;
            
            $this->Fields[] = array("name"=>$name,"title"=>$title,"showTitle"=>$showTitle);
        }
    }
    
    $q = Doctrine_Query::create()
        ->from('Lookups l');
    $Lookups = $q->execute();
    
    $this->Lookups = array();
    if ($Lookups != null) {
        foreach ($Lookups as $Lookup) { 
            $Val = array("field"=>$Lookup->getField(),
                                     "type"=>$Lookup->getType(),
                                     "data"=>$Lookup->getFielddata(),
                                     "single"=>($Lookup->getSingle() == 1 ? "true" : "false"));
            $this->Lookups[] = (object)$Val;
        }
    }
  }
  
  public function executeSaveLookups(sfWebRequest $request) {
    $returnArr = array("success"=>false);

    
    try {
        $dataGet = $request->getParameter('data');
    
        $data = array();
        if (!empty($dataGet)) {
            $data = json_decode($dataGet);            
        }

        if ($dataGet == "{}") {
            $data = array();
            
        }
        
        Doctrine_Query::create()
                ->delete('Lookups l')
                ->execute();
        
        if (count($data) > 0) {
            foreach ($data->fieldItem as $index => $field) {   
                $Field = $field;
                $randNum = $data->categoryNum[$index];
                
                $Category = $data->{"categoryNodeId".$randNum};
                $SingleSelect = $data->{"singleSelect".$randNum};

                $Lookup = Doctrine_Query::create()
                     ->from('Lookups l')
                     ->where('l.field = ?',$Field)
                     ->fetchOne();
                     
                     
                if ($Lookup == null) {
                    $Lookup = new Lookups();
                
                }
                
                $Lookup->setField($Field);
                $Lookup->setType("category");
                $Lookup->setFielddata($Category);
                $Lookup->setSingle($SingleSelect);
                $Lookup->save();
            }
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
}
