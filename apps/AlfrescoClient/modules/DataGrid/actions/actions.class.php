<?php

/**
 * DataGrid actions.
 *
 * @package    AlfrescoClient
 * @subpackage DataGrid
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
class DataGridActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $nodeId = $this->getRequestParameter('nodeId');
    $clipboard = $this->getRequestParameter('clipboard');
    if ($clipboard == true)
        $this->isClipBoard = true;
    else
        $this->isClipBoard = false;
        
    $containerName = $this->getRequestParameter('containerName');

    $addContainer = $this->getRequestParameter('addContainer');
    if (!empty($addContainer)) {
        $addContainer = substr($addContainer,0,5);
    }
    $this->addContainer = $addContainer;
    
    if (empty($containerName))
        $this->nextContainer = "container".rand(0,100);
    else
        $this->nextContainer = $containerName;
    
    $columnsetid = $this->getRequestParameter('columnsetid');

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $Node = $session->getNode($spacesStore, $nodeId);
    $this->DetailUrl = $Node->getShareSpaceUrl();
    $this->CompanyHomeUrl = $spacesStore->companyHome->getSpaceUrl();
    
    $ColumnFieldsArray = array();
    $ColumnArray = array();
    
    $RepoUrl = AlfrescoConfiguration::getInstance()->RepositoryUrl;   
    $RepoUrl = preg_replace("/(https:\/\/.*?)\/.*/is","$1",$RepoUrl);
    $RepoUrl = preg_replace("/(http:\/\/.*?)\/.*/is","$1",$RepoUrl);

    $this->ShareUrl = $RepoUrl.'/share/page/document-details?nodeRef=workspace://SpacesStore/'; 
    $this->ShareSpaceUrl = $RepoUrl.'/share/page/folder-details?nodeRef=workspace://SpacesStore/'; 

    $useDefault = true;
    if ($columnsetid > 0) {
        $SearchColumnSet = Doctrine_Query::create()
             ->from('SearchColumnSets c')
             ->where('c.id = ?',$columnsetid)
             ->fetchOne();
    
        
        if ($SearchColumnSet != null) { 
            $JsonData = $SearchColumnSet->getJsonfields();
            
            if (!empty($JsonData)) {
                $JsonData = json_decode($JsonData);
                
                $useDefault = false;
                $ColumnFieldsArray["alfresco_url"] = array("type"=>"string");
                $ColumnFieldsArray["nodeId"] = array("type"=>"string");
                $ColumnFieldsArray["alfresco_mimetype"] = array("type"=>"string");
                $ColumnFieldsArray["alfresco_type"] = array("type"=>"string");
                $ColumnFieldsArray["alfresco_name"] = array("type"=>"string");
                
                $ColumnFieldsArray["alfresco_perm_edit"] = array("type"=>"boolean");
                $ColumnFieldsArray["alfresco_perm_delete"] = array("type"=>"boolean");
                $ColumnFieldsArray["alfresco_perm_cancel_checkout"] = array("type"=>"boolean");
                $ColumnFieldsArray["alfresco_perm_create"] = array("type"=>"boolean");
                $ColumnFieldsArray["alfresco_perm_permissions"] = array("type"=>"boolean");
            
                $ColumnFieldsArray["alfresco_isWorkingCopy"] = array("type"=>"boolean");
                $ColumnFieldsArray["alfresco_isCheckedOut"] = array("type"=>"boolean");
                $ColumnFieldsArray["alfresco_workingCopyId"] = array("type"=>"string");
                $ColumnFieldsArray["alfresco_originalId"] = array("type"=>"string");
                
                foreach ($JsonData as $key => $column) {
                    $strKey = str_replace(":","_",$column->name);
                    $type = str_replace("d:","",$column->dataType);
                    $ColumnFieldsArray[$strKey] = array("type"=>"string");
                    
                    $ColumnArray[$strKey] = array(
                                             "header"=>$column->title,
                                             "width"=>80,
                                             "sortable"=>true,
                                             "hideable"=>true,
                                             "groupable"=>true
                                            );
                    
                    switch ($type) {
                        case "date":
                            $ColumnFieldsArray[$strKey]["type"] = "date";
                            $ColumnFieldsArray[$strKey]["dateFormat"] = $this->getUser()->getDateFormat();
                            
                            $ColumnArray[$strKey]["renderer"] = "Ext.util.Format.dateRenderer('".$this->getUser()->getDateFormat()."')";
                        break;
                        case "datetime":
                            $ColumnFieldsArray[$strKey]["type"] = "date";
                            $ColumnFieldsArray[$strKey]["dateFormat"] = $this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat();
                            
                            $ColumnArray[$strKey]["renderer"] = "Ext.util.Format.dateRenderer('".$this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat()."')";
                        break;
                        default:
                        break;
                    }
                    
                    if ($column->hide == true)
                        $ColumnArray[$strKey]["hidden"] = true;
                }
            }
        }
    }
    
    if ($useDefault == true) {
        $ColumnFieldsArray = array("alfresco_url"=>array(
                                     "type"=>"string"
                                    ),
                                    "nodeId"=>array(
                                     "type"=>"string"
                                    ),  
                                    "alfresco_mimetype"=>array(
                                     "type"=>"string"
                                    ),                       
                                    "alfresco_type"=>array(
                                     "type"=>"string"
                                    ),
                                    "alfresco_name"=>array("type"=>"string"),
                                    
                                    "alfresco_perm_edit"=>array("type"=>"boolean"),
                                    "alfresco_perm_delete"=>array("type"=>"boolean"),
                                    "alfresco_perm_cancel_checkout"=>array("type"=>"boolean"),
                                    "alfresco_perm_create"=>array("type"=>"boolean"),
                                    "alfresco_perm_permissions"=>array("type"=>"boolean"),
                                    
                                    "alfresco_isWorkingCopy"=>array("type"=>"boolean"),
                                    "alfresco_isCheckedOut"=>array("type"=>"boolean"),
                                    "alfresco_workingCopyId"=>array("type"=>"string"),
                                    "alfresco_originalId"=>array("type"=>"string"),
                                    
                  "name"=>array(
                                     "type"=>"string"
                                    ),
                  "creator"=>array(
                                     "type"=>"string"
                                    ),
                  "created"=>array(
                                     "type"=>"date",
                                     "dateFormat"=>$this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat()
                                    ),
                  "modified"=>array(
                                     "type"=>"date",
                                     "dateFormat"=>$this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat()
                                    )                                                                    
                                                                                                       
        );
                        
        $ColumnArray = array("name"=>array(
                                 "header"=>"Name",
                                 "width"=>80,
                                 "sortable"=>true,
                                 "hideable"=>false,
                                 "groupable"=>true
                                ),
              "creator"=>array(
                                 "header"=>"Creator",
                                 "width"=>25,
                                 "sortable"=>true,
                                 "hideable"=>true,
                                 "groupable"=>true
                                ),
              "created"=>array(
                                 "header"=>"Created",
                                 "width"=>25,
                                 "sortable"=>true,
                                 "hideable"=>true,
                                 "renderer"=>"Ext.util.Format.dateRenderer('".$this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat()."')",
                                 "groupable"=>true
                                ),
              "modified"=>array(
                                 "header"=>"Modified",
                                 "width"=>25,
                                 "sortable"=>true,
                                 "hideable"=>true,
                                 "renderer"=>"Ext.util.Format.dateRenderer('".$this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat()."')",
                                 "groupable"=>true
                                ),
              "alfresco_mimetype"=>array(
                                 "header"=>"Mimetype",
                                 "width"=>20,
                                 "sortable"=>true,
                                 "hideable"=>false,
                                 "groupable"=>true
                                ),
        );
    }
           
    $this->fields = $this->_parseFields($ColumnFieldsArray);   
    $this->columns = $this->_parseColumns($ColumnArray);

    if (!empty($containerName))
        $this->containerName = $containerName;
    else
        $this->containerName = "";
    
    // Settings
    $getSettings = array("DefaultTab","OnlineEditing");
    $this->DefaultTab = 0;
    foreach ($getSettings as $SettingValue) {
        $Setting = Doctrine_Query::create()
            ->from('Settings s')
            ->where('s.keystring = ?',$SettingValue)
            ->fetchOne();
        
        if ($Setting != null) {    
            switch ($SettingValue) {
                case "DefaultTab":
                    $mapOnValues = array("0"=>"preview","1"=>"versions","2"=>"metadata");
                    $ValueString = $Setting->getValuestring();   
                    $MapKey = array_search($ValueString,$mapOnValues);       
                    $Value = $MapKey;
                break;
                default:
                    $Value = $Setting->getValuestring();           
                break;
            }
            $this->{$SettingValue} = $Value;
            
        }
    }
    
    $this->DateFormat = $this->getUser()->getDateFormat();
    $this->TimeFormat = $this->getUser()->getTimeFormat();
    
    if ($request->hasParameter("setLayoutFalse")) {
        $this->setLayout(false);
    }
  }
  
  
  public function executeGetColumns(sfWebRequest $request) {
    $q = Doctrine_Query::create()
    ->from('SearchColumnSets c');      
     
    $Columns = $q->execute();                     
     
    $columnArray = array("columnsets"=>array());           
     
    foreach ($Columns as $column) {
        $columnArray["columnsets"][] = array("id"=>$column->getId(),
                                              "name"=>$column->getName());    
    } 
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($columnArray);
    return $this->renderText($return);    
  }
  
  public function executeDetailView(sfWebRequest $request) {

    $nodeId = $this->getRequestParameter('nodeId');                                  

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();


    $spacesStore = new SpacesStore($session);
    $Node = $session->getNode($spacesStore, $nodeId); 
    if ($Node != null) {
    
                     
        $this->getResponse()->addStylesheet('plupload.queue.css');
        
        $this->getResponse()->addJavascript('http://bp.yahooapis.com/2.4.21/browserplus-min.js');
        $this->getResponse()->addJavascript('plupload/gears_init.js');
        $this->getResponse()->addJavascript('plupload/plupload.js');
        $this->getResponse()->addJavascript('plupload/plupload.gears.js');
        $this->getResponse()->addJavascript('plupload/plupload.silverlight.js');
        $this->getResponse()->addJavascript('plupload/plupload.flash.js');
        $this->getResponse()->addJavascript('plupload/plupload.browserplus.js');
        $this->getResponse()->addJavascript('plupload/plupload.html4.js');
        $this->getResponse()->addJavascript('plupload/plupload.html5.js');
        $this->getResponse()->addJavascript('plupload/jquery.plupload.queue.js');
        $this->getResponse()->addJavascript('plupload/jquery.plupload.queue.js');
        
        $this->NodeId = $nodeId;
        $this->BlockId = str_replace("-","",$nodeId);
        $this->NodeImgName = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name;
    }
  }
  
  public function _parseColumns($columnArray) {
    $content = "";  
    if (count($columnArray) > 0) {
        
        foreach ($columnArray as $key => $valArray) {
            $valueString = "";
            foreach ($valArray as $valKey => $value) {
                if (!empty($valueString))
                    $valueString .= ",";
                    
                if (is_bool($value) || is_int($value) || $valKey == "renderer") {
                    if ($value == true && is_bool($value))
                        $value = "true";
                    else if ($value == false && is_bool($value))
                        $value = "false";
                    $valueString .= "$valKey: $value";
                }
                else
                    $valueString .= "$valKey: '$value'";
                         
            }
            if (!empty($valueString)) {
                if (!empty($content))
                    $content .= ",";
                $content .= '{'.$valueString.',
                dataIndex:\''.$key.'\'}';    
            }
        }
    }  
    
    return $content;
  }

  public function _parseFields($fieldArray) {
    $content = "";  
    if (count($fieldArray) > 0) {
        
        foreach ($fieldArray as $key => $valArray) {
            $valueString = "";
            foreach ($valArray as $valKey => $value) {
                if (!empty($valueString))
                    $valueString .= ",";
                    
                $valueString .= "$valKey: '$value'";
                         
            }
            if (!empty($valueString)) {
                if (!empty($content))
                    $content .= ",";
                $content .= '{name:\''.$key.'\',
                '.$valueString.'}';    
            }
        }
    }  
    return $content;  
  }
  
  private $_repository;
  private $_session;
  private $_spacesStore;
  private $_companyHome;
  
  public function executeGridData(sfWebRequest $request)
  {

    $array = $this->parseRequestData($request);    


    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');
    $return = json_encode($array);

    return $this->renderText($return);
  }
  
  
  private $MetaRenderer = null;
  
  private function parseRequestData(sfWebRequest $request,$limit="") {

    $user = $this->getUser();                
    $this->_repository = $user->getRepository();
    $this->_session = $user->getSession();
    $ticket = $user->getTicket();

    $this->_spacesStore = new SpacesStore($this->_session);

    $this->_companyHome = $this->_spacesStore->companyHome;  
      
    $this->_start = $this->getRequestParameter('start');
    if (empty($this->_start))
        $this->_start = 0;
    
    if (!is_numeric($limit)) {
        $this->_limit = $this->getRequestParameter('limit');
        if (empty($this->_limit))
            $this->_limit = 30;
    }
    else 
        $this->_limit = $limit;

    $array = array("data"=>array());

    try {
        $this->_sort = $this->getRequestParameter('sort');
        $this->_sorting = $this->getRequestParameter('dir');
        
        
        $nodeId = $this->getRequestParameter('nodeId');
        $category = $this->getRequestParameter('categories');
        $categoryNodeId = $this->getRequestParameter('categoryNodeId');
        $subCategories = $this->getRequestParameter('subCategories');
        $fromTree = $this->getRequestParameter('fromTree');
        if (empty($subCategories) || !isset($subCategories))                  
            $subCategories = false;
        else
            $subCategories = (string)$subCategories;
            
        $tag = $this->getRequestParameter('tag');
        $searchTerm = $this->getRequestParameter('searchTerm');
        $searchTerm = urldecode($searchTerm);
        $columnsetid = $this->getRequestParameter('columnsetid');
        
        $advancedSearchFields = $this->getRequestParameter('advancedSearchFields');
        $advancedSearchOptions = $this->getRequestParameter('advancedSearchOptions');

        $clipboard = $this->getRequestParameter('clipboard');
        $clipboarditems = $this->getRequestParameter('clipboarditems');
        
        if ($columnsetid > 0) {
            $this->MetaRenderer = MetaRenderer::getInstance($user);
            $this->MetaRenderer->scanRenderers();
        }
        
        if (!empty($nodeId))
            $array = $this->_DocumentList($nodeId,$columnsetid);
        else if (!empty($category))
            $array = $this->_CategoryList($category,$categoryNodeId,$subCategories,$fromTree,$columnsetid);    
        else if (!empty($tag)) 
            $array = $this->_TagList($tag,$columnsetid);    
        else if (!empty($searchTerm)) 
            $array = $this->_QuickSearchList($searchTerm,$columnsetid);
        else if (!empty($advancedSearchFields) && !empty($advancedSearchOptions)) {
            $array = $this->_AdvancedSearchList($advancedSearchFields,$advancedSearchOptions,$columnsetid);
        }
        else if ($clipboard == true && !empty($clipboarditems)) {
            $clipboarditems = json_decode($clipboarditems);
            if (!is_array($clipboarditems))
                throw new Exception();
                
            $array = $this->_ClipBoardList($clipboarditems,$columnsetid);
        }
        else {

        }
    }
    catch (Exception $e) {
        
    }

    return $array;    
  }

  private function sort_by($field, &$arr, $sorting=SORT_ASC, $case_insensitive=true){

        if(is_array($arr) && (count($arr)>0) && ( ( is_array($arr[0]) && isset($arr[0][$field]) ) || ( is_object($arr[0]) && isset($arr[0]->$field) ) ) ){

            if($case_insensitive==true) 
                $strcmp_fn = "strnatcasecmp";
            else 
                $strcmp_fn = "strnatcmp";

            if($sorting==SORT_ASC){
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($a->'.$field.', $b->'.$field.');
                    }else if(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($a["'.$field.'"], $b["'.$field.'"]);
                    }else return 0;
                ');
            }else{
                $fn = create_function('$a,$b', '
                    if(is_object($a) && is_object($b)){
                        return '.$strcmp_fn.'($b->'.$field.', $a->'.$field.');
                    }else if(is_array($a) && is_array($b)){
                        return '.$strcmp_fn.'($b["'.$field.'"], $a["'.$field.'"]);
                    }else return 0;
                ');
            }
            usort($arr, $fn);
            return true;
        }else{
            return false;
        }
    }
    
  private function _ClipBoardList($items,$columnsetid=0) {
    $array = array("totalCount"=>0,"data"=>array());

    
    if (count($items) == 0) {
        $Nodes = array();
    }
    else {
        //$Nodes = $this->_session->filteredQuery($this->_spacesStore, "PATH:\"/cm:taggable//cm:".$tag."//member\"",$this->_limit,$this->_start);
        $searchStr = "";
        foreach ($items as $item) {
            if (!empty($searchStr))
                $searchStr .= " OR ";
            $searchStr .= 'ID:"workspace://SpacesStore/'.$item.'"';
        }
        
        $Nodes = $this->_session->filteredQuery($this->_spacesStore, $searchStr, $this->_limit,$this->_start);
        $array["totalCount"] = (string)$this->_session->getLastQueryCount();
    }

    if ($Nodes != null && count($Nodes) > 0) {
        foreach ($Nodes as $child) {
            if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites" && $child->child->type != "{http://www.alfresco.org/model/site/1.0}site") {
                $array["data"][] = $this->_ParseResult($child,$columnsetid);
            }      
        }
    }
    return $array;  
  }
  
  private function _DocumentList($nodeId,$columnsetid=0) {
    $array = array("totalCount"=>0,"data"=>array());
    if ($nodeId == "root") {
        $nodeId = $this->_companyHome->getId();
    }

    if (!empty($this->_sort))
        $Nodes = $this->_session->filteredQuery($this->_spacesStore,"PARENT:\"workspace://SpacesStore/".$nodeId."\"",$this->_limit,$this->_start,$this->_sort,$this->_sorting);
    else
        $Nodes = $this->_session->filteredQuery($this->_spacesStore,"PARENT:\"workspace://SpacesStore/".$nodeId."\"",$this->_limit,$this->_start);
        
    $array["totalCount"] = (string)$this->_session->getLastQueryCount();

    if ($Nodes != null && count($Nodes) > 0) {
        foreach ($Nodes as $child) {
            if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites" && $child->child->type != "{http://www.alfresco.org/model/site/1.0}site") {
                $array["data"][] = $this->_ParseResult($child,$columnsetid);
            }
                    
        }
    }
    return $array;
  }
  
  private function _CategoryList($category,$categoryNodeId,$subCategories,$fromTree="false",$columnsetid=0) {
  
     $array = array("totalCount"=>0,"data"=>array());

    if ($category == "root" || empty($category)) {
        $Nodes = array();
    }
    else {
        //$category = preg_replace("#.*/(.*?)#is","$1",$category);
        $splitCategories = preg_split("#/#",$category);
        if (preg_match("#/#eis",$category) || $fromTree == "true") {
            $newCategory = "";
            foreach ($splitCategories as $cat) {              
                /*$cat = str_replace(" ","_x0020_",$cat);
                $cat = str_replace(",","_x002c_",$cat);
                $cat = str_replace("%20","_x0020_",$cat);           */
                $cat = str_replace("_","-",$cat);
                $cat = ISO9075Mapper::map($cat); 
                if (!empty($newCategory))
                    $newCategory .= "/";
                $newCategory .= "cm:".$cat;    
            }
            $category = $newCategory;
            
            if ($subCategories == "true")
                $searchFor = "/member";
            else
                $searchFor = "member";           
            //FB::log("searchfor ".$searchFor." - ".$subCategories);      
            
            if (!empty($this->_sort))
                $Nodes = $this->_session->filteredQuery($this->_spacesStore, "PATH:\"/cm:generalclassifiable/" .$category. "/".$searchFor."\"",$this->_limit,$this->_start,$this->_sort,$this->_sorting);
            else
                $Nodes = $this->_session->filteredQuery($this->_spacesStore, "PATH:\"/cm:generalclassifiable/" .$category. "/".$searchFor."\"",$this->_limit,$this->_start);
            
        }
        else {
            //$category = preg_replace("#.*/(.*?)#is","$1",$category);               
            /*$category = str_replace(" ","_x0020_",$category);
            $category = str_replace(",","_x002c_",$category);
            $category = str_replace("%20","_x0020_",$category);   */
            //$category = ISO9075Mapper::map($category);
            //$category = "cm:".$category;
            // just a single make with parent
            if (!empty($this->_sort))
                $Nodes = $this->_session->filteredQuery($this->_spacesStore, "PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND -TYPE:\"cm:category\"",$this->_limit,$this->_start,$this->_sort,$this->_sorting);         
            else
                $Nodes = $this->_session->filteredQuery($this->_spacesStore, "PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND -TYPE:\"cm:category\"",$this->_limit,$this->_start);                       
                
            
        }
          
        FB::log("category: $category");                                                                                                                                                                                                                                         
        FB::log("nodeid: $categoryNodeId");                                                                                                                                                                                                                                         
        //$Nodes = $this->_session->filteredQuery($this->_spacesStore, "PATH:\"/cm:generalclassifiable//cm:" .$category. "/member\"",$this->_limit,$this->_start);
        //$Nodes = $this->_session->filteredQuery($this->_spacesStore, "PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND PATH:\"/cm:generalclassifiable//cm:" .$category. "/member\"",$this->_limit,$this->_start);
        //FB::log("PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND PATH:\"/cm:generalclassifiable//cm:" .$category. "/member\"");                  
        
        
        //FB::log("PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND TYPE!=\"cm:category\"");                                                                                                                      
        //$Nodes = $this->_session->filteredQuery($this->_spacesStore, "PARENT:\"workspace://SpacesStore/{$categoryNodeId}\" AND -TYPE:\"cm:category\"",$this->_limit,$this->_start); 
        
        $array["totalCount"] = (string)$this->_session->getLastQueryCount();
    }
    
    if ($Nodes != null && count($Nodes) > 0) {
        foreach ($Nodes as $child) {
            if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites" && $child->child->type != "{http://www.alfresco.org/model/site/1.0}site") {
                $array["data"][] = $this->_ParseResult($child,$columnsetid);
            }      
        }
    }
    
    return $array;  
  }
  
  private function _TagList($tag,$columnsetid=0) {
    $array = array("totalCount"=>0,"data"=>array());

    if (empty($tag)) {
        $Nodes = array();
    }
    else {
        $tag = preg_replace("#.*/(.*?)#is","$1",$tag);
        $tag = str_replace(" ","_x0020_",$tag);
        $tag = str_replace(",","_x002c_",$tag);
        $tag = str_replace("%20","_x0020_",$tag); 
        
        if (!empty($this->_sort))
            $Nodes = $this->_session->filteredQuery($this->_spacesStore, "PATH:\"/cm:taggable//cm:".$tag."//member\"",$this->_limit,$this->_start,$this->_sort,$this->_sorting);                 
        else
            $Nodes = $this->_session->filteredQuery($this->_spacesStore, "PATH:\"/cm:taggable//cm:".$tag."//member\"",$this->_limit,$this->_start);                                       

        $array["totalCount"] = (string)$this->_session->getLastQueryCount();
    }

    if ($Nodes != null && count($Nodes) > 0) {
        foreach ($Nodes as $child) {
            if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites" && $child->child->type != "{http://www.alfresco.org/model/site/1.0}site") {
                $array["data"][] = $this->_ParseResult($child,$columnsetid);
            }      
        }
    }
    return $array;  
  }
  
  private function _QuickSearchList($searchTerm,$columnsetid=0) {
    $array = array("totalCount"=>0,"data"=>array());

    if (empty($searchTerm)) {
        $Documents = array();
    }
    else {
        $QuickSearchSetting = Doctrine_Query::create()
            ->from('Settings s')
            ->where('s.Keystring = ?','QuickSearch')
            ->fetchOne();
        
        $Fields = array();    
        if ($QuickSearchSetting != null) {
            $JsonData = $QuickSearchSetting->getValuestring();
            $JsonData = json_decode($JsonData);
            
            $Fields = $JsonData;
        }
            
            
        $options = array("searchTerm"=>$searchTerm,"results"=>"all","searchBy"=>"OR","locations"=>array(),"categories"=>array(),"tags"=>"");
        $options = json_encode($options);
        $searchFields = array();
            
        if (count($Fields) > 0) {
            foreach ($Fields as $Field) {
                $searchFields[$Field->name] = $searchTerm;    
            }
            //return $this->_AdvancedSearchList($searchFields,$options,$columnsetid);
        }
        
        $searchFields = json_encode($searchFields);
        //$seachTerm = urldecode($searchTerm);
        /*$searchTerm = "TEXT:$searchTerm";

        $Documents = $this->_session->query($this->_spacesStore, $searchTerm);*/
        return $this->_AdvancedSearchList($searchFields,$options,$columnsetid);
    }

    if (count($Documents) > 0) {
        for ($i = 0; $i < count($Documents); $i++) {
            $Node = $Documents[$i];
            
            /*if ($Node->getType() != "{http://www.alfresco.org/model/content/1.0}folder" && $Node->getType() != "{http://www.alfresco.org/model/site/1.0}sites" && $Node->getType() != "{http://www.alfresco.org/model/site/1.0}site") {*/
            if ($Node->getType() != "{http://www.alfresco.org/model/site/1.0}sites" && $Node->getType() != "{http://www.alfresco.org/model/site/1.0}site") {
            
                $array["data"][] = $this->_ParseResult($Node,$columnsetid);
            }
                    
        }
    } 
    return $array;  
  }
  
  private function _AdvancedSearchList($searchFields,$options,$columnsetid) {  
    $searchTerm = "";

    $array = array("totalCount"=>0,"data"=>array());

    $searchFields = json_decode($searchFields);
    $options = json_decode($options);
    
    if (!isset($options->searchBy) || empty($options->searchBy))
        $searchBy = "AND";
    else
        $searchBy = $options->searchBy;     

    if (count($searchFields) == 0 && empty($options->searchTerm)) {
        $Documents = array();
    }
    else {

        $dateCombos = array();
        
        
        foreach ($searchFields as $key => $value) {
            if (empty($value))
                continue;
                
            $prop = $key;
            $prop = str_replace("#from","",$prop);
            $prop = str_replace("#to","",$prop);
            $prop = str_replace("#list","",$prop);
            $prop = str_replace(":","\\:",$prop);
            $prop = str_replace("_","\\:",$prop);
            
            if (preg_match("/#from/is",$key)) {
                $dateCombos[$prop]["from"] = $value;
            }
            if (preg_match("/#to/is",$key)) {
                $dateCombos[$prop]["to"] = $value;
            }
        }

        $alreadyChecked = array();
        
        foreach ($searchFields as $key => $value) {

            
                
            $prop = $key;
            $prop = str_replace("#from","",$prop);
            $prop = str_replace("#to","",$prop);
            $prop = str_replace("#list","",$prop);
            $prop = str_replace(":","\\:",$prop);
            $prop = str_replace("_","\\:",$prop);

            if (empty($value) || in_array($prop,$alreadyChecked))
                continue;
            //if (!empty($searchTerm))
            //    $searchTerm .= "";

            $create = false;
            if (preg_match("/[0-9]+\-[0-9]+\-[0-9]+T.*/is",$value)) {
                // date field
                // iso 8601 for lucene
                //$value = $value.'.000Z';
                //$value = str_replace("-","\-",$value);

                /*$createValue = false;
                if (is_array($value)) {
                    foreach ($value as $assocKey => $assocValue) {
                        if (count($assocValue) == 5) { // person
                            $nodeId = $assocValue->id;
                        }
                        else if (count($assocValue) == 3) { // content
                            $nodeId = $assocValue->id;
                            
                        }
                        else { // unknown
                            continue;
                        }
                    }
                }
                else if (preg_match("/#from/eis",$key)) {
                    //FB::log("search for to => ".$dateCombos[$prop]["to"]);
                    if (!empty($dateCombos[$prop]["to"])) {
                        $createValue = true;
                        $dateCombos[$prop]["from"] = $value;
                    }
                    else
                        $dateCombos[$prop] = array("from"=>$value,"to"=>"");
                }
                else if (preg_match("/#to/eis",$key)) {
                    //FB::log("search for from => ".$dateCombos[$prop]["from"]);
                    if (!empty($dateCombos[$prop]["from"])) {
                        $createValue = true;
                        $dateCombos[$prop]["to"] = $value;
                    }
                    else
                        $dateCombos[$prop] = array("from"=>"","to"=>$value);
                }*/
                
                
                //if ($createValue == true) {
                if (!empty($dateCombos[$prop]["from"]) && !empty($dateCombos[$prop]["to"])) {
                    $searchTerm .= "+@$prop:[".$dateCombos[$prop]["from"]." TO ".$dateCombos[$prop]["to"]."] "; 
                    $create = false;
                    $alreadyChecked[]=$prop;
                    unset($dateCombos[$prop]);
                }
                else
                    $create = true;
            }
            else if (preg_match("/#list/eis",$key) && preg_match("/,/eis",$value)) {
                $create = false;
                $explode = explode(",",$value);
                foreach ($explode as $exp) {
                    $searchTerm .= "+@$prop:\"$exp\" ";
                }  
             }
            else {
                $create = true;
            }
            
            if ($create == true) {
                if (!preg_match("/#to/eis",$key) && !preg_match("/#from/eis",$key)) {
                    
                    
                    if (preg_match("/.*?AND.*?/eis",$value)) {
                        $explode = explode(" AND ",$value);
                        if (count($explode) > 0) { 
                            if (!empty($searchTerm))
                                $searchTerm .= $searchBy." ";
                            $searchTerm .= "(";                             
                            for ($i = 0; $i < count($explode); $i++) {
                                $explodeVal = $explode[$i];
                                $searchTerm .= " +@$prop:\"$explodeVal\"";                                 
                            }
                            $searchTerm .= ") ";                        
                        }
                    }
                    else if (preg_match("/.*?OR.*?/eis",$value)) {                   
                        $explode = explode(" OR ",$value);
                        if (count($explode) > 0) { 
                            if (!empty($searchTerm))
                                $searchTerm .= $searchBy." ";
                            $searchTerm .= "(";                
                            for ($i = 0; $i < count($explode); $i++) {
                                $explodeVal = $explode[$i];
                                $searchTerm .= " @$prop:\"$explodeVal\"";                                 
                            }
                            $searchTerm .= ") ";                
                        }
                    }
                    else {    
                        if (!empty($searchTerm))
                            $searchTerm .= $searchBy." ";
                        if ($searchBy == "AND")
                            $searchTerm .= "+";
                        
                        
                        $searchTerm .= "@$prop:\"$value\" ";
                    }
                }
            }  

        }

        
        if (count($options->categories) > 0) {
            $categoryTerm = "";
            foreach ($options->categories as $categoryName) {
                $categoryName = ISO9075Mapper::map($categoryName);
                if (!empty($categoryName)) {  
                    //$searchTerm .= '+PATH:"/cm:generalclassifiable//cm:'.$categoryName.'//member" ';
                    if (!empty($categoryTerm))
                        $categoryTerm .= " OR ";
                    $categoryTerm .= 'PATH:"/cm:generalclassifiable//cm:'.$categoryName.'//member" ';
                }
            }
            if (count($options->categories) == 1)
                $searchTerm .= "+$categoryTerm ";
            else
                $searchTerm .= "AND (".$categoryTerm.") ";
        }
        
        if (count($options->locations) > 0) {
            $locationTerm = "";
            foreach ($options->locations as $locationId) {
                if (!empty($locationTerm))
                    $locationTerm .= " OR ";
                    
                $locationTerm .= 'PARENT:"workspace://SpacesStore/'.$locationId.'" ';
            }
            if (count($options->locations) == 1)
                $searchTerm .= "+$locationTerm ";
            else
                $searchTerm .= "AND (".$locationTerm.") ";
        }
        
        if (count($options->tags) > 0) {
            if (!empty($options->tags)) {
                $tagTerm = "";
                $tagArray = array();
                if (preg_match("/,/eis",$options->tags)) {
                    $tagArray = split(",",$options->tags);
                }
                else
                    $tagArray[] = $options->tags;
                

                for ($i = 0; $i < count($tagArray); $i++) {
                    $tagName = ISO9075Mapper::map($tagArray[$i]);
                    if (empty($tagName))
                        continue;
                        
                    if (!empty($tagTerm))
                            $tagTerm .= " OR ";
                            
                    $tagTerm .= 'PATH:"/cm:taggable//cm:'.$tagName.'//member" ';
                }
                
                if (count($tagArray) == 1)
                    $searchTerm .= "+$tagTerm ";
                else
                    $searchTerm .= "AND (".$tagTerm.") ";
            }
        }
        
        if (strlen($options->searchTerm) > 0) {
            

            if (preg_match("/.*?AND.*?/eis",$value)) {
                $explode = explode(" AND ",$value);
                if (count($explode) > 0) { 
                    if (!empty($searchTerm))
                        $searchTerm .= $searchBy." ";
                    
                    $searchTerm .= "(";              
                    for ($i = 0; $i < count($explode); $i++) {
                        $explodeVal = $explode[$i];
                    
                        $searchTerm .= 'TEXT:'.$explodeVal.'';   
                        if ($i != count($explode)-1)            
                            $searchTerm .= " AND ";             
                    }
                    $searchTerm .= ")"; 
                }
            }
            else if (preg_match("/.*?OR.*?/eis",$value)) {                   
                $explode = explode(" OR ",$value);
                if (count($explode) > 0) { 
                    if (!empty($searchTerm))
                        $searchTerm .= $searchBy." ";
                    $searchTerm .= "(";
                    for ($i = 0; $i < count($explode); $i++) {
                        $explodeVal = $explode[$i];
                        
                        $searchTerm .= 'TEXT:'.$explodeVal.''; 
                        if ($i != count($explode)-1)               
                            $searchTerm .= " OR ";  
                    }
                    $searchTerm .= ")";
                }
            }
            else {    
                if (!empty($searchTerm))
                    $searchTerm .= $searchBy." ";
                
                $searchTerm .= 'TEXT:'.$options->searchTerm.'';      
            }
            
        }
        
        
        if (count($dateCombos) > 0) {
            foreach ($dateCombos as $key => $value) {
                if (!empty($values["from"]) && empty($values["to"])) {
                    if (!empty($searchTerm))
                        $searchTerm .= " AND ";
                        
                    $searchTerm .= "@$key:[".$values["from"]." TO NOW]"; 
                }
            } 
        }
        FB::log($searchTerm);
        //$searchTerm .=" LIMIT 1";
        if (!empty($this->_sort))
            $Nodes = $this->_session->filteredQuery($this->_spacesStore, $searchTerm, $this->_limit, $this->_start,$this->_sort,$this->_sorting);                                 
        else
            $Nodes = $this->_session->filteredQuery($this->_spacesStore, $searchTerm, $this->_limit, $this->_start);                      
            
        
        $array["totalCount"] = (string)$this->_session->getLastQueryCount();
    }

    if ($Nodes != null && count($Nodes) > 0) {
        foreach ($Nodes as $child) {
            if ($child->type != "{http://www.alfresco.org/model/site/1.0}sites" && $child->child->type != "{http://www.alfresco.org/model/site/1.0}site") {
                $array["data"][] = $this->_ParseResult($child,$columnsetid);
            }      
        }
    }
    return $array;  
  }
  

  
  private function _ParseResult($Node,$columnsetid=0) {
    $resultArr = array();
    $useDefault = true;
    
    $Permissions = $Node->getPermissions();
    $isWorkingCopy = $Node->isWorkingCopy();
    $isCheckedOut = $Node->isCheckedOut();
    $workingCopyId = str_replace("workspace://SpacesStore/","",$Node->getWorkingCopy());
    $originalId = str_replace("workspace://SpacesStore/","",$Node->getCheckedoutOriginal());
    
    if ($columnsetid > 0) {
        $SearchColumnSet = Doctrine_Query::create()
             ->from('SearchColumnSets c')
             ->where('c.id = ?',$columnsetid)
             ->fetchOne();
        
        
        
        if ($SearchColumnSet != null) { 
            $JsonData = $SearchColumnSet->getJsonfields();

            if (!empty($JsonData)) {
                $JsonData = json_decode($JsonData);
                
                $useDefault = false;
                
                $contentData = $Node->cm_content;
                $url = "";
                $mimetype = "";
                if ($contentData != null) {
                    $url = $contentData->getUrl();
                    $mimetype = $contentData->getMimetype();
                }              
                
                
                /*$EditRight = $Permissions->userAccess->edit;
                $DelRight = $Permissions->userAccess->delete;
                $CheckoutCancelRight = $Permissions->userAccess->cancel-checkout;
                $CreateRight = $Permissions->userAccess->cancel-create;
                $HasRights = $Permissions->userAccess->permissions;*/
                
                $resultArr["alfresco_url"] = $url;
                $resultArr["nodeId"] = $Node->getId();
                $resultArr["alfresco_mimetype"] = $mimetype;
                $resultArr["alfresco_type"] = $Node->getType();
                $resultArr["alfresco_name"] = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name;
                $resultArr["alfresco_perm_edit"] = $Permissions->userAccess->edit;
                $resultArr["alfresco_perm_delete"] = $Permissions->userAccess->delete;
                $resultArr["alfresco_perm_cancel_checkout"] = $Permissions->userAccess->cancel-checkout;
                $resultArr["alfresco_perm_create"] = $Permissions->userAccess->cancel-checkout;
                $resultArr["alfresco_perm_permissions"] = $Permissions->userAccess->permissions;
                
                $resultArr["alfresco_isWorkingCopy"] = $isWorkingCopy;
                $resultArr["alfresco_isCheckedOut"] = $isCheckedOut;
                $resultArr["alfresco_workingCopyId"] = $workingCopyId;
                $resultArr["alfresco_originalId"] = $originalId;

                $RestDictionary = new RESTDictionary($this->_repository,$this->_spacesStore,$this->_session);                                                   
                $NamespaceMap = NamespaceMap::getInstance();
                           
                foreach ($JsonData as $key => $column) {
                    $internalName = $column->name;
                    $strKey = str_replace(":","_",$column->name);
                    $type = str_replace("d:","",$column->dataType);
                    
                    switch ($strKey) {
                        case "cm_type":
                            $TypeTitle = "";
                            $Type = $Node->getType();
                            $ShortType = $NamespaceMap->getShortName($Type,"_");
                            try {
                                $TypeInfo = $RestDictionary->GetClassDefinitions($ShortType);
                                $TypeName = $TypeInfo->name;
                                $TypeTitle = $TypeInfo->title;
                                if (empty($TypeTitle))
                                    $TypeTitle = $TypeName;
                            }
                            catch (Exception $e) {
                                
                            }
                            $resultArr[$strKey] = $TypeTitle;     
                        break;     
                        case "cm_name":
                            if ($Node->getType() == "{http://www.alfresco.org/model/content/1.0}folder")
                                $resultArr[$strKey] = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> <b>'.$Node->cm_name.'</b>';
                            else
                                $resultArr[$strKey] = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name;
                        break;
                        default:
                            
                            $propVal = "";
                            switch ($type) {
                                case "date":
                                    $DateValue = $Node->{$strKey};
                                    
                                    if (empty($DateValue))
                                        $DateValue = "";
                                    else    
                                        $DateValue = date($this->getUser()->getDateFormat(),strtotime($Node->{$strKey}));

                                    $propVal = $DateValue;            

                                break;
                                case "datetime":
                                    $propVal = date($this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat(),strtotime($Node->{$strKey}));
                                break;
                                default:
                                    $propVal = $Node->{$strKey};
                                break;
                            }
                            
                            if ($this->MetaRenderer != null) {
                                if (($ValueRender = $this->MetaRenderer->getPropertyRenderer($internalName)) != null || ($ValueRender = $this->MetaRenderer->getDataRenderer($type)) != null) {
                                    $propVal = $ValueRender->render($propVal); 
                                    
                                }
                            }
                            
                            $resultArr[$strKey] = $propVal;
                        break;
                    }
                }
                
                
            }
        }
    }
    
    if ($useDefault == true) {
        
        $contentData = $Node->cm_content;
        $url = "";
        $mimetype = "";
        if ($contentData != null) {
            $url = $contentData->getUrl();
            $mimetype = $contentData->getMimetype();
        }              
        
        if ($Node->getType() == "{http://www.alfresco.org/model/content/1.0}folder")
            $name = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> <b>'.$Node->cm_name.'</b>';
        else
            $name = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name;
        $resultArr = array("nodeId"=>$Node->getId(),
                       "name"=>$name,
                       "creator"=>$Node->cm_creator,
                       "created"=>date($this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat(),strtotime($Node->cm_created)),
                       "modified"=>date($this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat(),strtotime($Node->cm_modified)),
                       "alfresco_url"=>$url,
                       "alfresco_mimetype"=>$mimetype,
                       "alfresco_type"=>$Node->getType(),
                       "alfresco_name"=>'<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name,
                       "alfresco_perm_edit"=>$Permissions->userAccess->edit,
                       "alfresco_perm_delete"=>$Permissions->userAccess->delete,
                       "alfresco_perm_cancel_checkout"=>$Permissions->userAccess->cancel-checkout,
                       "alfresco_perm_create"=>$Permissions->userAccess->cancel-checkout,
                       "alfresco_perm_permissions"=>$Permissions->userAccess->permissions,
                       "alfresco_isWorkingCopy"=>$isWorkingCopy,
                       "alfresco_isCheckedOut"=>$isCheckedOut,
                       "alfresco_workingCopyId"=>$workingCopyId,
                       "alfresco_originalId"=>$originalId);   
    }
    
    
    
    return $resultArr;
  }
  
  
  private function arrayToObject($array) {
    if(!is_array($array)) {
        return $array;
    }
    $object = new stdClass();
    if (is_array($array) && count($array) > 0) {
      foreach ($array as $name=>$value) {
         $name = strtolower(trim($name));
         if (!empty($name)) {
            $object->$name = $this->arrayToObject($value);
         }
      }
      return $object;
    }
    else {
      return false;
    }
  }
  
  public function executeExportResultSet(sfWebRequest $request) {     

    $gridData = $this->parseRequestData($request,0);

    $skip = array("alfresco_type",
                  "alfresco_name",
                  "alfresco_isWorkingCopy",
                  "alfresco_isCheckedOut",
                  "alfresco_workingCopyId",
                  "alfresco_originalId",
                  "alfresco_perm_edit",
                  "alfresco_perm_delete",
                  "alfresco_perm_cancel_checkout",
                  "alfresco_perm_create",
                  "alfresco_perm_permissions",
                  );
    
    $csvList = "";
    $csvColumns = array();
    $csvColumnsFound = false;
    if (count($gridData["data"]) > 0) {
        foreach ($gridData["data"] as $data) {
            $csvFields = array();
            $tempList = "";
            $found = true;
            foreach ($data as $key => $value) {
                if (in_array($key,$skip))
                    continue; 

                if ($key == "alfresco_url") {
                    if (empty($value)) {
                        $found = false;                   
                    }                                  
                }

                if ($csvColumnsFound == false) 
                    $csvColumns[] = $key;                            
                    
                $newValue = trim(strip_tags($value));   
                $csvFields[$key] = utf8_decode($newValue);
            }
            
            if ($data["alfresco_type"] == "{http://www.alfresco.org/model/content/1.0}folder") {
                if (!empty($data["nodeId"])) {
                    $tempList = $this->csvRecursiveFolder($data["nodeId"],$request,$skip); 
                } 
                $found = false;      
            }
            
            if ($csvColumnsFound==false) {
                $csvList .= join(";",$csvColumns)."\n";  
                $csvColumnsFound = true;
            }  
            
            if ($found) {
                $csvList .= join(";",$csvFields)."\n";    
            }
            
            if (!empty($tempList))
                $csvList .= $tempList;
        }
    }
                      
    
    $this->getResponse()->setHttpHeader('Content-Type','text/csv; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Content-Disposition','attachment; filename=Export_Result.csv',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    return $this->renderText($csvList);
  }
  
  public function executePasteClipboard(sfWebRequest $request) {
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();
    $spacesStore = new SpacesStore($session);                
    
    $items = $this->getRequestParameter('clipboardItems');
    $actionType = $this->getRequestParameter('actionType');
    $destNodeId = $this->getRequestParameter('destNodeId');
    if (!empty($items) && !empty($actionType) && !empty($destNodeId)) {
        
        $items = json_decode($items);
        foreach ($items as $key=>$value) {
            $items[$key] = "workspace://SpacesStore/".$value;   
        } 
        $return = array("success"=>"false");

        $RestTransport = new RESTTransport($repository,$spacesStore,$session);     
        
        if ($actionType == "cut")
            $return = $RestTransport->MoveTo($destNodeId,$items);
        else
            $return = $RestTransport->CopyTo($destNodeId,$items);    
                  
        if (!empty($return->overallSuccess))
            $return->success = $return->overallSuccess;         
        
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->getResponse()->setHttpHeader('Pragma','no-cache');      
        $return = json_encode($return);
        
        return $this->renderText($return);
    }       
  }
  
  private function csvRecursiveFolder($nodeId, sfWebRequest $request,$skip) {
    $columnsetid = $this->getRequestParameter('columnsetid');      

    $csvList = "";
    $gridData = $this->_DocumentList($nodeId,$columnsetid); 
    if (count($gridData["data"]) > 0) {
        foreach ($gridData["data"] as $data) {
            $csvFields = array();
            $tempList = "";        
            $found = true;
            foreach ($data as $key => $value) {
                if (in_array($key,$skip))
                    continue; 

                if ($key == "alfresco_url") {
                    if (empty($value)) {
                        $found = false;
                    }
                }
                
                $newValue = trim(strip_tags($value));   
                $csvFields[$key] = utf8_decode($newValue);
            }
            
            if ($data["alfresco_type"] == "{http://www.alfresco.org/model/content/1.0}folder") {
                if (!empty($data["nodeId"])) {
                    $tempList = $this->csvRecursiveFolder($data["nodeId"],$request,$skip); 
                } 
                $found = false;      
            }
            
            if ($found) {
                $csvList .= join(";",$csvFields)."\n";    
            }
            
            if (!empty($tempList))
                $csvList .= $tempList;
        }
    }
                    

    return $csvList;    
  }
}
