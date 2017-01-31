<?php

/**
 * Metadata actions.
 *
 * @package    AlfrescoClient
 * @subpackage Metadata
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
class MetadataActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex()
  {
    
    $nodeId = $this->getRequestParameter('nodeId');
    $this->nodeId = $nodeId;
    $this->containerName = preg_replace("/[^a-zA-Z0-9äöüÄÖÜ_]/" , "" , $nodeId);

    //$this->form = new AlfrescoMetadataForm(array(),array('nodeId'=>$nodeId));
    
    sfWidgetFormSchema::setDefaultFormFormatterName('AlfrescoMetadataExtJS');
    
    return sfView::SUCCESS;
  }
  
  public function executeAdmin() {
    $nodeId = $this->getRequestParameter('nodeId');
    
    $this->form = new AlfrescoMetadataForm(array(),array('nodeId'=>$nodeId,'metaAdmin'=>true));
    sfWidgetFormSchema::setDefaultFormFormatterName('AlfrescoMetaAdmin');

    return sfView::SUCCESS;
  }

  
  public function executeView() {

    $nodeId = $this->getRequestParameter('nodeId');      
    $this->height = $this->getRequestParameter('height');      
    $addContainer = $this->getRequestParameter('containerName');     

    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();   


    $spacesStore = new SpacesStore($session);
    $versionNode = false;
    if (preg_match("#workspace://version2store/(.*)#eis",$nodeId,$match)) {
        $spacesStore = new VersionStore($session);
        $nodeId = $match[1];
        $versionNode = true;
    }
    $Node = $session->getNode($spacesStore, $nodeId);
    if ($Node != null) {
        if (!empty($addContainer))
            $addContainer = "_".$addContainer;
        else
            $addContainer = "";  
        
        $notAllowedList = array("mimetype",
                                "encoding",
                                "sys:node-dbid",
                                "sys:store-identifier",
                                "sys:node-uuid",
                                "sys:store-protocol",
                                "cm:initialVersion",
                                "size",
                                //"cm:content", 
                                //"cm:modifier",
                                //"cm:creator",
                                //"cm:created",
                                //"cm:modified",
                                //"cm:accessed",
                                "cm:workingCopyMode",
                                //"cm:autoVersion",
                                "cm:versionLabel",
                                "rn:rendition",
                                "cm:autoVersionOnUpdateProps",
                                "fm:discussion",
                                "cm:template",
                                "ver2:*",
                                "cm:contains",
                                "app:icon");  
                                
        $displayInInfoTab = array("cm:modifier",
                                  "cm:creator",
                                  "cm:created",
                                  "cm:modified");
                                
        $this->isWorkingCopy = $Node->isWorkingCopy();
        $this->isCheckedOut = $Node->isCheckedOut();
        $this->checkedOutBy = $Node->checkedOutBy();
        if ($this->checkedOutBy == $user->getUsername())
            $this->checkedOutBy = $this->getContext()->getI18N()->__("you", null, 'messages');  
            
        if ($Node->isWorkingCopy()) {
            $this->checkoutRefNode = $Node->getCheckedoutOriginal();
        }
        else if ($Node->isCheckedOut()) {
            $this->checkoutRefNode = $Node->getWorkingCopy();
            
        }
        
        if (!empty($this->checkoutRefNode) && $versionNode == false) {
            $this->checkoutRefNode = $session->getNode($spacesStore,str_replace("workspace://SpacesStore/","",$this->checkoutRefNode));
            $this->checkoutRefNodeImage = $this->checkoutRefNode->getIconUrl();
            $this->checkoutRefNodeName = $this->checkoutRefNode->cm_name;
        }
        
        
        if ($versionNode == true)
            $this->MetaData = $this->generateMetaCode($Node->__toString(),true,true,$notAllowedList,$displayInInfoTab);
        else
            $this->MetaData = $this->generateMetaCode($nodeId,true,true,$notAllowedList,$displayInInfoTab);
        $this->MetaFields = $this->MetaData["metaData"]["fields"];
        $this->MetaFieldData = $this->MetaData["data"];
        $this->fieldTypeSeperator = false;
        if (count($this->MetaFields["Column1"]) > 0 || count($this->MetaFields["Column2"]) > 0) {
            $this->Column1 = $this->MetaFields["Column1"];
            $this->Column2 = $this->MetaFields["Column2"];
            $this->Tabs = $this->MetaFields["Tabs"];
        }
        else {
            $this->Column1 = $this->MetaFields; 
            $this->Column2 = array();
            $this->Tabs = array();
        }
        
        $this->nodeId = $nodeId;
        $this->containerName = preg_replace("/[^a-zA-Z0-9äöüÄÖÜ_]/" , "" , $nodeId).$addContainer;
        //$this->folderPath = $Node->getFolderPath();
        
        $folderPath = $Node->getFolderPath(true);
        $this->folderPathArray = array();

        $explode = explode("/",$folderPath);
        if (count($explode) == 0) {
            $explode = array($folderPath);    
        }
        
        //$explode = array_merge($company,$explode);
            $didCm = false;
            $pathstring = "";
            $pathstring2 = "";
            if ($versionNode == false) {
                $parentRef = $spacesStore->companyHome->getId();

                for ($i = 0; $i < count($explode); $i++) {
                    $path = trim($explode[$i]);
                    
                    if (!empty($path)){
                        $name = $path;
                        $pathStr = $path;
                        $pathStr = preg_replace("#.*/(.*?)#is","$1",$pathStr);
                        $pathStr = str_replace(" ","_x0020_",$pathStr);
                        $pathStr = str_replace("%26","_x0026_",$pathStr);
                        $pathStr = str_replace("&","_x0026_",$pathStr);
                        $pathStr = str_replace(",","_x002c_",$pathStr);
                        $pathStr = str_replace("%20","_x0020_",$pathStr); 

                        $found = false;
                        
                        if (empty($pathstring))
                            $NodePath = $session->query($spacesStore, "PATH:\"app:company_home//*\" AND @cm\:name:\"{$name}\" AND PRIMARYPARENT:\"workspace://SpacesStore/$parentRef\""); 
                        else
                            $NodePath = $session->query($spacesStore, "PATH:\"app:company_home//*\" AND @cm\:name:\"{$name}\" AND PRIMARYPARENT:\"workspace://SpacesStore/$parentRef\""); 
                            
                        if ($NodePath != null) {
                           
                            $NodePath = $NodePath[0];
                            $parentRef = $NodePath->getId();
                            $found = true;
                        }
                        
                         if (!empty($pathstring))
                            $pathstring .= "/";
                        $pathstring .= "cm:$pathStr";

                        
                        if ($found == true) {
                            // bug mit _x...._
                            $nameOut = " {$NodePath->cm_name}";
                            $this->folderPathArray[$nameOut] = "javascript:openFolder('{$NodePath->id}','<img src={$NodePath->getIconUrl()} border=0 align=absmiddle> {$nameOut}');";
                        }
                    }   
                }
                $company = array("{$spacesStore->companyHome->cm_name}"=>"javascript:openFolder('{$spacesStore->companyHome->id}','<img src={$spacesStore->companyHome->getIconUrl()} border=0 align=absmiddle> {$spacesStore->companyHome->cm_name}');");
                $this->folderPathArray = array_merge($company,$this->folderPathArray);
            }
            else
                $this->folderPathArray = array();
        
    }
    
  }

  
  private $fieldTypeSeperator = false;
  
  public function executeGetNodeMetaData()
  {
    $nodeId = $this->getRequestParameter('nodeId');
    $fieldTypeSeperator = $this->getRequestParameter('fieldTypeSeperator');
    if ($fieldTypeSeperator == true || $fieldTypeSeperator == "true") {
        $this->fieldTypeSeperator = true;
    }
    
    $hideCreateElements = $this->getRequestParameter('hideCreateElements');
    //FB::log($hideCreateElements);
    if ($hideCreateElements == true)
        $MetaDataResult = $this->generateMetaCode($nodeId,false,false,array(),array(),true);     
    else
        $MetaDataResult = $this->generateMetaCode($nodeId);
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($MetaDataResult);
    return $this->renderText($return);
  }
  
  private function generateMetaCode($nodeId,$seperateColumns=false,$renderValues=false,$notAllowedList=array(),$displayInInfoTab=array(),$hideCreateElements=false) {
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();   

    $namespaceMap = NamespaceMap::getInstance();

    $spacesStore = new SpacesStore($session);
    $versionNode = false;
    if (preg_match("#workspace://version2store/(.*)#eis",$nodeId,$match)) {
        $spacesStore = new VersionStore($session);
        $nodeId = $match[1];
        $versionNode = true;
    }
    
    $this->widgets = array();
    $this->widgetsValues = array();
 
    $Node = $session->getNode($spacesStore, $nodeId);
    $this->MetaNode = $Node;
    

    if ($Node != null) { 
        $this->Permissions = $Node->getPermissions();

        $RestAspects = new RESTAspects($repository,$spacesStore,$session);     
        
        $shortType = $namespaceMap->getShortName($Node->getType());
        
        $restDict = new RESTDictionary($repository,$spacesStore,$session);

        $MetaForm = $restDict->GetFormdefinitions($Node->__toString());

        $ContentType = $MetaForm->data->type;
        
        $class = str_replace(":","_",$ContentType);

        $Template = Doctrine_Query::create()
                      ->from('ContentModelTemplates t')
                      ->where('t.class = ?', $class)
                      ->fetchOne();
              
   
        $Fields = $MetaForm->data->definition->fields;

        if (count($notAllowedList) == 0) {
            $notAllowedList = array("mimetype",
                                    "encoding",
                                    "cm:content",
                                    "sys:node-dbid",
                                    "sys:store-identifier",
                                    "sys:node-uuid",
                                    "sys:store-protocol",
                                    "cm:initialVersion",
                                    "size",
                                    "cm:modifier",
                                    "cm:creator",
                                    "cm:created",
                                    "cm:modified",
                                    "cm:accessed",
                                    //"cm:autoVersion",
                                    "cm:versionLabel", 
                                    "cm:autoVersionOnUpdateProps",
                                    "cm:workingCopyMode",
                                    "fm:discussion",
                                    "rn:rendition",
                                    "cm:template",
                                    "cm:contains",
                                    "app:icon");       
        } 
        
        $formConfig = array(
            "labelAlign"=>"left",
            "columnCount"=>2,
            "labelWidth"=>130,
            "defaults"=>array(
                "width"=>298
            )
        );
        
        $MetaDataResult = array(
            "success"=>false,
            "metaData"=>array(
                 "fields"=>array(),
                 "formConfig"=>$formConfig,
                 "createElements"=>""
            ),
            "data"=>array()
        );
        
        if ($renderValues == true) {                 
            $MetaRenderer = MetaRenderer::getInstance($user);
            $MetaRenderer->scanRenderers();
        }
        
        
        $MetaDataArray = array();
        $CreateElementsArray = array();
        if (count($Fields) > 0) {

            foreach ($Fields as $Field => $FieldProp) {
                $FieldName = $FieldProp->name;
                
                if (!in_array($FieldName,$displayInInfoTab)) {
                    
                    if ($FieldProp->protectedField == 1 || in_array($FieldName,$notAllowedList) || in_array(preg_replace("/(.*?:).*/is","$1*",$FieldName),$notAllowedList))
                        continue;
                }
                

                
                $FieldType = $FieldProp->type;
                $DataKeyName = $FieldProp->dataKeyName;

                $dataType = $FieldProp->dataType;
                $mandatory = $FieldProp->mandatory;
                $internalName = $FieldProp->name;
                $Label = $FieldProp->label;
                $endpointType = $FieldProp->endpointType;  
                $FieldValue = $MetaForm->data->formData->{$DataKeyName};          

                $id = str_replace(":","_",$internalName);
                
                //if ($DataKeyName == "prop_t1_signatureDate")
                //    $dataType = "datetime";    
                
                if ($this->fieldTypeSeperator == true) {
                    if ($FieldType != "association")
                        $DataKeyName = $DataKeyName."#".$dataType;
                    else {
                        $type = str_replace("cm:","",$endpointType);
                        $DataKeyName = $DataKeyName."#".$type;     
                    }             
                }
                
                
        
                if ($renderValues == true) {
                    if ((!empty($endpointType) && ($ValueRender = $MetaRenderer->getAssocRenderer($endpointType)) != null) || ($ValueRender = $MetaRenderer->getPropertyRenderer($internalName)) != null || ($ValueRender = $MetaRenderer->getDataRenderer($dataType)) != null) {
                        $FieldValue = $ValueRender->render($FieldValue); 
                        $FieldValue = $FieldValue;
                    }
                }

                if (!empty($FieldValue))
                    $MetaDataResult["data"][$DataKeyName] = $FieldValue;       
                
                
                //FB::log($FieldValue);

                //print_r($FieldName." => ".$FieldType." => ".$dataType." => ".$DataKeyName."<br>");
                switch ($FieldType) {
                    case "property":
                        switch ($dataType) {
                            case "content":
                                // Not needed
                            break;
                            /*case "mltext":
                                $MetaDataArray[] = array(
                                    "name"=>$DataKeyName,
                                    "id"=>$id
                                    ,"fieldLabel"=>$Label
                                    ,"editor"=>array(
                                        "xtype"=>"textarea",
                                        "allowBlank"=>($mandatory == true ? false : true)
                                    )
                                );
                            break; */  
                            case "mltext": 
                            case "text":
                                $constraints = $FieldProp->constraints;
                                $findConst = false;

                                if (count($constraints) > 0) {
                                    foreach ($constraints as $const => $value) {
                                        switch ($value->type) {
                                            case "LIST":
                                                if ($this->fieldTypeSeperator == true) {  
                                                    unset($MetaDataResult["data"][$DataKeyName]);         
                                                    $DataKeyName = $FieldProp->dataKeyName."#".$dataType."_constraints"; 
                                                    
                                                    $MetaDataResult["data"][$DataKeyName] = $FieldValue;                          
                                                }
                                                
                                                $List = $value->parameters->allowedValues;
                                                /*if (function_exists('mb_detect_encoding')) {
                                                    if (count($List) > 0) {
                                                        foreach ($List as $AllowedValKey => $AllowedValVal) {
                                                            
                                                            if (mb_detect_encoding($AllowedValVal, 'UTF-8', true)) {
                                                                $encoded = utf8_decode($AllowedValVal);
                                                                
                                                                if ($encoded != false && !is_null($encoded) && !empty($encoded) && $encoded != NULL) {
                                                                    $List[$AllowedValKey] = $encoded;
                                                                    FB::Log("string is $encoded");
                                                                }
                                                                else {
                                                                    $List[$AllowedValKey] = $AllowedValVal;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }*/
                                                
                                                
                                                if (count($List) > 0) {
                                                    $choices = array();
                                                    
                                                    foreach ($List as $ListKey => $ListValue) {
                                                        if (!empty($choicesList))
                                                            $choicesList .= ",";
                                                        $choices[$ListKey] = $ListValue;
                                                    }
                                                    $findConst = true;
                                                    if (count($choices) > 10) {
                                                        $height = 100;
                                                    }
                                                    else {
                                                        $height = 50;      
                                                    }

                                                    if ($FieldProp->repeating == true) {
                                                        $MetaDataArray[] = array(
                                                            "name"=>$DataKeyName,
                                                            "id"=>$id,
                                                            "fieldLabel"=>$Label,
                                                            "editor"=>array(
                                                                "xtype"=>"superboxselect",
                                                                "store"=>$choices,
                                                                "width"=>$formConfig["defaults"]["width"],
                                                                "mode"=>"local",
                                                                "resizable"=>false,
                                                                "submitValue"=>false
                                                            )
                                                        );
                                                    }
                                                    else {
                                                        $MetaDataArray[] = array(
                                                            "name"=>$DataKeyName,
                                                            "id"=>$id,
                                                            "fieldLabel"=>$Label,
                                                            "editor"=>array(
                                                                "xtype"=>"combo",
                                                                "store"=>$choices,
                                                                "width"=>$formConfig["defaults"]["width"],
                                                                "mode"=>"local",
                                                                "typeAhead"=>false,
                                                                "forceSelection"=>false,
                                                                "triggerAction"=>"all",
                                                            )
                                                        );    
                                                    }
                                                    /*$MetaDataArray[] = array(
                                                        "name"=>$DataKeyName,
                                                        "id"=>$id,
                                                        "fieldLabel"=>$Label,
                                                        "editor"=>array(
                                                            "xtype"=>"multiselect",
                                                            "store"=>$choices,
                                                            "width"=>$formConfig["defaults"]["width"],
                                                            "height"=>$height,
                                                            "ddReorder"=>false,
                                                            "border"=>false
                                                        )
                                                    );*/
                                                }
                                            break;
                                            default:

                                            break;
                                        }
                                    }
                                }
                                
                                if ($findConst == false) {
                                    
                                    $LookupSearch = Doctrine_Query::create()
                                     ->from('Lookups l')
                                     ->where('l.field = ?',$internalName)
                                     ->andWhere('l.type = ?',"category")
                                     ->fetchOne();
                                    
                                    $foundLookup = false;
                                     
                                    if ($LookupSearch != null) { 
                                        
                                        $catId = $LookupSearch->getFielddata();
                                        $single = $LookupSearch->getSingle();
                                        

                                        $CategoryNode = $session->getNode($spacesStore, $catId);
                                        $Categories = $CategoryNode->getChildren();
                                        if (count($Categories) > 0) { 
                                            $foundLookup = true;
                                            //$CategoryList = array();
                                            $choices = array();
                                            $choicesArr = array();
         
                                            foreach ($Categories as $ListKey => $ListValue) {
                                                $CatName = preg_replace("/\{.*?\}/is","",$ListValue->getName());
                                                $CatDesc = $ListValue->cm_description;
                                                $CatDescName = "$CatDesc ($CatName)";
                                                if (empty($CatDesc) || $CatDesc == null) {
                                                    $CatDesc = $CatName;
                                                    $CatDescName = $CatName;     
                                                }

                                                if (!empty($choicesList))
                                                    $choicesList .= ",";
                                                $choices[] = $CatName;
                                                  
                                                $choicesArr[] = array($CatName,$CatDesc,$CatDescName);
                                            }
                                            
                                            if ($single == 1 || $single == "1" || $single == true) { 
                                                $MetaDataArray[] = array(
                                                    "name"=>$DataKeyName,
                                                    "id"=>$id,
                                                    "fieldLabel"=>$Label,
                                                    "editor"=>array(
                                                        "xtype"=>"combo",
                                                        //"store"=>$choices,
                                                        "store"=>array(
                                                            "fields"=>array("catName","catDesc","catDescName"),
                                                            "data"=>$choicesArr
                                                        ),
                                                        "width"=>$formConfig["defaults"]["width"],
                                                        "mode"=>"local",
                                                        "valueField"=>"catName",
                                                        "displayField"=>"catDescName",
                                                        "hiddenName"=>$DataKeyName,
                                                        "hiddenValue"=>$FieldValue,
                                                        "typeAhead"=>false,
                                                        "forceSelection"=>false,
                                                        "lazyInit"=>false,
                                                        "triggerAction"=>"all",
                                                    )
                                                );
                                                //$CreateElementsArray[$id] = '<input type="hidden" value="" name="'.$DataKeyName.'">';
                                            }
                                            else {
                                                if ($this->fieldTypeSeperator == true) {
                                                    $FieldValue = str_replace(", ",",",$FieldValue);
                                                    $MetaDataResult["data"][$DataKeyName] = $FieldValue;
                                                }
                                                $MetaDataArray[] = array(
                                                    "name"=>$DataKeyName,
                                                    "id"=>$id,
                                                    "fieldLabel"=>$Label,
                                                    "editor"=>array(
                                                        "xtype"=>"superboxselect",
                                                        //"store"=>$choices,
                                                        "store"=>array(
                                                            "fields"=>array("catName","catDesc","catDescName"),
                                                            "data"=>$choicesArr
                                                        ), 
                                                        "width"=>$formConfig["defaults"]["width"],
                                                        "mode"=>"local",
                                                        "displayField"=>"catDescName",
                                                        "valueField"=>"catName",
                                                        "resizable"=>false,          
                                                        "submitValue"=>false
                                                    )
                                                );
                                            }
                                        }
                                    }
                                    
                                    if ($foundLookup == false) {
                                        if ($FieldProp->repeating == true) {
                                            $MetaDataArray[] = array(
                                                "name"=>$DataKeyName,
                                                "id"=>$id
                                                ,"fieldLabel"=>$Label
                                                ,"editor"=>array(
                                                    "xtype"=>"textarea",
                                                    "allowBlank"=>($mandatory == true ? false : true)
                                                )
                                            );
                                        }
                                        else {
                                            $MetaDataArray[] = array(
                                                "name"=>$DataKeyName,
                                                "id"=>$id
                                                ,"fieldLabel"=>$Label
                                                ,"editor"=>array(
                                                     "allowBlank"=>($mandatory == true ? false : true)
                                                )
                                            );
                                        }
                                    }
                                }
                            break;
                            
                            case "long":
                            case "double":
                            case "float":
                            case "int":
                                //$widget = new sfAlfrescoWidgetForExtJS();
                                //$widget->addOption("dataType",$dataType);
                                
                                $MetaDataArray[] = array(
                                    "name"=>$DataKeyName,
                                    "id"=>$id
                                    ,"fieldLabel"=>$Label
                                    ,"editor"=>array(
                                         "xtype"=>"numberfield",
                                         "allowBlank"=>($mandatory == true ? false : true)
                                    )
                                );
                                
                            break;
                            case "date": 
                                //if ($this->fieldTypeSeperator == true) {         
                                    if (!empty($FieldValue) && strlen($FieldValue) > 1) {
                                        $MetaDataResult["data"][$DataKeyName] = date($this->getUser()->getDateFormat(),strtotime($FieldValue));   
                                    }  
                                //}                  
                                                
                                $MetaDataArray[] = array(
                                    "name"=>$DataKeyName,
                                    "id"=>$id
                                    ,"fieldLabel"=>$Label
                                    ,"editor"=>array(
                                         "xtype"=>"datefield",
                                         "format"=>$this->getUser()->getDateFormat(),
                                         "allowBlank"=>($mandatory == true ? false : true)
                                    )
                                );
                                
                            break;
                            case "datetime":
                                //if ($this->fieldTypeSeperator == true) {         
                                    if (!empty($FieldValue) && strlen($FieldValue) > 1) {
                                        $MetaDataResult["data"][$DataKeyName] = date($this->getUser()->getDateFormat()." ".$this->getUser()->getTimeFormat(),strtotime($FieldValue));   
                                    }  
                                //}   
                                
                                $MetaDataArray[] = array(
                                    "name"=>$DataKeyName,
                                    "id"=>$id
                                    ,"fieldLabel"=>$Label
                                    ,"editor"=>array(
                                         "xtype"=>"xdatetime", 
                                         "timeFormat"=>$this->getUser()->getTimeFormat(),
                                         "dateFormat"=>$this->getUser()->getDateFormat(),
                                         "width"=>"200",                          
                                         "allowBlank"=>($mandatory == true ? false : true)
                                    )
                                );   

                            break;
                            case "boolean":
                                $MetaDataArray[] = array(
                                    "name"=>$DataKeyName,
                                    "id"=>$id
                                    ,"fieldLabel"=>$Label
                                    ,"editor"=>array(
                                        "xtype"=>"checkbox"    
                                    )
                                );
                            break;
                            case "category":

                                if ($internalName == "cm:taggable") {
                                    if ($this->fieldTypeSeperator == true) {  
                                        unset($MetaDataResult["data"][$DataKeyName]);         
                                        $DataKeyName = $FieldProp->dataKeyName."#tags"; 
                                        
                                        $MetaDataResult["data"][$DataKeyName] = $FieldValue;                          
                                    }
                                    $widget = new sfAlfrescoWidgetTags();
                                    $url = $this->getController()->genUrl('tags/autocompleteTagData');
                                    $widget->addOption("urlfor",$url);
                                    
                                    $nodeIdStrip = substr($nodeId,0,5);              
                                    $BOXNAME = str_replace("#tags","",$DataKeyName);
                                    $BOXNAMERand = $BOXNAME."_num_".$nodeIdStrip;

                                    if ($renderValues != true) {  
                                        if (!empty($FieldValue)) {                         
                                            $Tags = explode(",",$FieldValue);
                                            if (count($Tags) == 0)
                                                $Tags = array($FieldValue);

                                            $TagsValues = array();
                                            if (count($Tags) > 0) {

                                                foreach ($Tags as $Key=>$TagNodeRef) {

                                                    $TagUUId = str_replace("workspace://SpacesStore/","",$TagNodeRef);
                                                    $TagNode = $session->getNode($spacesStore, $TagUUId);       
                                                    
                                                    if ($TagNode != null) {
                                                        //$TagsValues[$TagNode->getId()] = $TagNode->cm_name;
                                                        $TagsValues[] = array("name"=>$TagNode->cm_name);
                                                        //$TempFieldValue .= '<a href="javascript:openTag(\''.$TagNode->cm_name.'\')">'.$TagNode->cm_name.'</a>, ';                               
                                                    }   
                                                }     
                                                //$FieldValue = join(", ",$CategoriesValues);                  
                                                $FieldValue = $TagsValues;
                                            }  
                                        }
                                        else {
                                            $FieldValue = array();
                                        }
                                    }

                                    //$MetaDataResult["metaData"]["createElements"] .= $widget->render($DataKeyName, 'value', array());
                                    $CreateElementsArray[$id] = $widget->render($BOXNAMERand, $FieldValue, array());
                                    
                                    $MetaDataArray[] = array(
                                        "name"=>$BOXNAME,
                                        "id"=>$id,
                                        "fieldLabel"=>$Label,
                                        "el"=>"tagBoxList$BOXNAMERand",
                                        "editor"=>array( 
                                        )
                                    );
                                }
                                else {
                                    if ($renderValues != true) {      
                                        $url = $this->getController()->genUrl('Categories/GetCategoryCheckTree')."?values=".$FieldValue."&readall=true";
                                    }
                                    else {
                                        $url = $this->getController()->genUrl('Categories/GetCategoryCheckTree');         
                                    }
                                    
                                    $nodeIdStrip = substr($nodeId,0,5);              
                                    $BOXNAME = str_replace("#tags","",$DataKeyName);
                                    $BOXNAMERand = $BOXNAME."_num_".$nodeIdStrip;
                                    
                                    $idRand = $id."_num_".$nodeIdStrip;
                                    
                                    //$widget = new sfWidgetFormInputText();
                                    $MetaDataArray[] = array(
                                        "name"=>$DataKeyName,
                                        "fieldLabel"=>$Label, 
                                        "id"=>$id, 
                                        "editor"=>array(
                                            "xtype"=>"panel",
                                            "width"=>$formConfig["defaults"]["width"],
                                            "frame"=>false,
                                            "header"=>false,
                                            "border"=>false,
                                            "id"=>$idRand, 
                                            //"html"=>'<div id="'.$BOXNAMERand.'"></div><input type="button" onclick="categoryEnable(\''.$BOXNAMERand.'\')" class="categoryBtn" value="Change categories">'
                                            "html"=>'<input type="button" onclick="categoryEnable(\''.$idRand.'\',\''.$BOXNAMERand.'\',\''.$url.'\',\''.$formConfig["defaults"]["width"].'\',this)" value="'.$this->getContext()->getI18N()->__("Change categories", null, 'messages').'">'
                                        )
                                    );   

                                    /*$MetaDataArray[] = array(
                                        "name"=>$DataKeyName,
                                        "fieldLabel"=>$Label,  
                                        "id"=>$id,     
                                        "editor"=>array(
                                            "xtype"=>"treepanel",
                                            "width"=>$formConfig["defaults"]["width"],
                                            "height"=>250,
                                            "id"=>$BOXNAMERand,
                                            "useArrows"=>false,
                                            "autoScroll"=>true,
                                            "enableDD"=>false,
                                            "containerScroll"=>true,
                                            "frame"=>true,
                                            "isFormField"=>true,
                                            "rootVisible"=>false,
                                            "bodyStyle"=>"background:white;",
                                            //"loader"=>"$url",
                                            "dataUrl"=>$url,
                                            "root"=>array(
                                                //"nodeType"=>"async",
                                                "text"=>"root",
                                                "draggable"=>false,   
                                                "id"=>"root",
                                                "expanded"=>true,
                                                "border"=>true
                                            )
                                        )
                                    );   */
                       
                                    $CreateElementsArray[$id] = '<input type="hidden" realField="'.$BOXNAMERand.'" metatype="category" name="'.$BOXNAME.'" value="'.$FieldValue.'">';

                                }
                                //$this->additionalFields[$DataKeyName] = $widget;
                            break;
                            
                            default:
                            break;
                        }

                    break;
                    case "association":
                        $endpointType = $FieldProp->endpointType;
                        switch ($endpointType) {
                            case "cm:person":
                                $widget = new sfAlfrescoWidgetUserAssociation();
                                $url = $this->getController()->genUrl('Association/autocompleteUserData');
                                $widget->addOption("urlfor",$url);
                                //$MetaDataResult["metaData"]["createElements"] .= $widget->render($DataKeyName, 'value', array());
                                $nodeIdStrip = substr($nodeId,0,5);
                                $BOXNAME = str_replace("#person","",$DataKeyName);
                                $BOXNAMERand = $BOXNAME."_num_".$nodeIdStrip;
                                
                                if ($renderValues == false) {
                                    //$ArrayValue = "";
                                    
                                    if (!empty($FieldValue)) {
                                        if (!is_array($FieldValue) && preg_match("/,/eis",$FieldValue))   
                                            $FieldValue = explode(",",$FieldValue);
                                        else if (!is_array($FieldValue)) 
                                            $FieldValue = array($FieldValue);


                                        $NewFieldValue = array();
                                        foreach ($FieldValue as $Value) {
           
                                            $Id = str_replace("workspace://SpacesStore/","",$Value);
                                            $ReferenceNode = $session->getNode($spacesStore,$Id);

                                            $NewFieldValue[] = array("mail"=>$ReferenceNode->cm_email,
                                                                     "id"=>$ReferenceNode->getId(),
                                                                     "firstName"=>$ReferenceNode->cm_firstName,
                                                                     "lastName"=>$ReferenceNode->cm_lastName,
                                                                     "userName"=>$ReferenceNode->cm_userName
                                                                     );

                                        }         
                                        $FieldValue = $NewFieldValue;
                                    }

                                    $CreateElementsArray[$id] = $widget->render($BOXNAMERand, $FieldValue, array());
                                    
                                    //echo $FieldValue;
                                }
                                
                                $MetaDataArray[] = array(
                                    "name"=>$BOXNAME,
                                    "id"=>$id,
                                    "fieldLabel"=>$Label,
                                    "el"=>"userAssociationBox$BOXNAMERand",
                                    "editor"=>array( 
                                    )
                                );

                            break;
                            case "cm:content":

                                $widget = new sfAlfrescoWidgetContentAssociation(); 
                                $url = $this->getController()->genUrl('Association/autocompleteContentData');
                                $widget->addOption("urlfor",$url);
                                //$MetaDataResult["metaData"]["createElements"] .= $widget->render($DataKeyName, 'value', array());
                                                                        
                                $nodeIdStrip = substr($nodeId,0,5);
                                $BOXNAME = str_replace("#content","",$DataKeyName);
                                $BOXNAMERand = $BOXNAME."_num_".$nodeIdStrip; 
                                

                
                                if ($renderValues == false) {
                                    //$ArrayValue = "";
                                    
                                    if (!empty($FieldValue)) {
                                        if (!is_array($FieldValue) && preg_match("/,/eis",$FieldValue))   
                                            $FieldValue = explode(",",$FieldValue);
                                        else if (!is_array($FieldValue)) 
                                            $FieldValue = array($FieldValue);


                                        $NewFieldValue = array();
                                        foreach ($FieldValue as $Value) {

                                            $Id = str_replace("workspace://SpacesStore/","",$Value);
                                            $ReferenceNode = $session->getNode($spacesStore,$Id);
                                            
                                            $extension = preg_replace("/.*\.(.*)/is","$1",$ReferenceNode->cm_name);
                                            if (!file_exists(sfConfig::get('sf_web_dir')."/images/filetypes/16x16/{$extension}.png"))
                                                $extension = "txt";

                                            $NewFieldValue[] = array("type"=>$extension,
                                                                     "id"=>$ReferenceNode->getId(),
                                                                     "name"=>$ReferenceNode->cm_name
                                                                     );

                                        }         
                                        $FieldValue = $NewFieldValue;

                                    }

                                    $CreateElementsArray[$id] = $widget->render($BOXNAMERand, $FieldValue, array());  
                                    
                                    //echo $FieldValue;
                                }

                                $MetaDataArray[] = array(
                                    "name"=>$BOXNAME,
                                    "id"=>$id,
                                    "fieldLabel"=>$Label,
                                    "el"=>"contentAssociationBox$BOXNAMERand",
                                    "editor"=>array( 
                                    )
                                );
                            break;  
                            default:
                                
                                $widget = new sfAlfrescoWidgetContentAssociation(); 
                                $url = $this->getController()->genUrl('Association/autocompleteContentData')."?dataTypeParam=".$endpointType;
                                $widget->addOption("urlfor",$url);

                                $nodeIdStrip = substr($nodeId,0,5);
                                $BOXNAME = $DataKeyName;
                                $BOXNAME = str_replace("#".preg_replace("/.*?:(.*)/is","$1",$endpointType),"",$BOXNAME);
                                $BOXNAME = str_replace(preg_replace("/.*?:(.*)/is","$1",$endpointType),"",$BOXNAME);
                                
                                $BOXNAMERand = $BOXNAME."_num_".$nodeIdStrip; 

                                if ($renderValues == false) {
                                    //$ArrayValue = "";
                                    
                                    if (!empty($FieldValue)) {
                                        if (!is_array($FieldValue) && preg_match("/,/eis",$FieldValue))   
                                            $FieldValue = explode(",",$FieldValue);
                                        else if (!is_array($FieldValue)) 
                                            $FieldValue = array($FieldValue);


                                        $NewFieldValue = array();
                                        foreach ($FieldValue as $Value) {

                                            $Id = str_replace("workspace://SpacesStore/","",$Value);
                                            $ReferenceNode = $session->getNode($spacesStore,$Id);
                                            
                                            $extension = preg_replace("/.*\.(.*)/is","$1",$ReferenceNode->cm_name);
                                            if (!file_exists(sfConfig::get('sf_web_dir')."/images/filetypes/16x16/{$extension}.png"))
                                                $extension = "txt";

                                            $NewFieldValue[] = array("type"=>$extension,
                                                                     "id"=>$ReferenceNode->getId(),
                                                                     "name"=>$ReferenceNode->cm_name
                                                                     );

                                        }         
                                        $FieldValue = $NewFieldValue;

                                    }

                                    $CreateElementsArray[$id] = $widget->render($BOXNAMERand, $FieldValue, array());  
                                    
                                    //echo $FieldValue;
                                }

                                $MetaDataArray[] = array(
                                    "name"=>$BOXNAME,
                                    "id"=>$id,
                                    "fieldLabel"=>$Label,
                                    "el"=>"contentAssociationBox$BOXNAMERand",
                                    "editor"=>array( 
                                    )
                                );
                            break;  
                        }
                    break;
                    default:
                    break;
                }
                
            }

            
            if ($Template != null) {
                $Aspectview = $Template->getAspectview();  
                $JsonData = $Template->getJsondata();
                if (!empty($JsonData)) {
                    $JsonData = json_decode($JsonData);
                    $Column1 = $JsonData->Column1;
                    $Column2 = $JsonData->Column2;
                    $Tabs = $JsonData->Tabs;
                    
                    
                    
                    if (count($Column1) != count($Column2)) {
                        $col1 = count($Column1);
                        $col2 = count($Column2);
                        
                        if ($col1 > $col2) {
                            $diff = $col1 - $col2;
                            for ($i = 0; $i < $diff; $i++) {
                                $Column2[] = "";
                            }
                        }
                        else if ($col1 < $col2) {
                            $diff = $col2 - $col1;
                            for ($i = 0; $i < $diff; $i++) {
                                $Column1[] = "";
                            }
                        }
                    }
                    $RealTabs = array();
                    if (count($Tabs) > 0) {
                        $RealTabs = $Tabs->tabs;
                    }
                    
                    $AppendAspects = array();

                    $AspectsOfNode = $Node->getAspects();
                    $Aspects = Doctrine::getTable('Allowedaspects');                         
                    foreach ($AspectsOfNode as $Aspect) {
                        $Aspect = $session->namespaceMap->getShortName($Aspect,":");  
                        if (count($Aspects) > 0) {   
                            if (count($Aspects->findByName($Aspect)) > 0) {
                                $AspectInfo = $RestAspects->GetAspect($Aspect);                 
                                $AppendAspects[$Aspect] = $AspectInfo;
                            }
                        }
                    }

                    if (count($AppendAspects) > 0) {
                        foreach ($AppendAspects as $Aspect) {
                            $FoundProps = false;
                            $Items = array();
                            foreach ($Aspect->properties as $AspectProp) {
                               
                               //
                               
                                $FoundPosition = $this->searchFieldArray($Fields,$AspectProp->name);                          
                                if ($FoundPosition != -1) {
                                    $FoundProps = true;  
                                                       
                                    $FieldIntern = $Fields[$FoundPosition];
                                    $Object = new stdClass();
                                    $Object->name = $FieldIntern->name;   
                                    $Object->dataType = $FieldIntern->dataType;   
                                    $Object->title = $FieldIntern->label;   
                                    $Object->type = $FieldIntern->type;   
                                        
                                    if ($Aspectview == "append") {
                                        $Column1[] = $Object;   
                                        $Column2[] = "";   
                                    }
                                    else {
                                        $Items[] = $Object;                                 
                                    } 
                                }         
                            } 

                            if ($Aspectview != "append") {
                                $TabObject = new stdClass();
                                $TabObject->title = $Aspect->title;
                                $TabObject->items = $Items;
                                $RealTabs[] = $TabObject;
                            } 
                            
                        } 
                           
                    }
    
                    
                    if ($seperateColumns==true)
                        $MetaDataArrayNew = $this->useSeperateTemplateRenderer($MetaDataArray,$Column1,$Column2);
                    else
                        $MetaDataArrayNew = $this->RenderOnTemplate($MetaDataArray,$Column1,$Column2);
                    

                    $TempTabCol = array();
                    
                    if (count($displayInInfoTab) > 0) {
                        $stdClass = new stdClass();
                        $stdClass->title = "Info";
                        $itemArray = array();
                        foreach ($displayInInfoTab as $item) {
                            $tempObj = new stdClass();
                            $tempObj->name = $item;
                            $itemArray[] = $tempObj;
                        }

                        $stdClass->items = $itemArray;
                        $RealTabs[] = $stdClass;
                    }
                    

                    if (count($RealTabs) > 0) {

                        $TabArray = array();
                        foreach ($RealTabs as $key => $TabValues) {
                            $title = $TabValues->title;
                            $items = $TabValues->items;

                            $ItemsArray = array();
                            foreach ($items as $itemKey => $itemValue) {
                                $TempTabCol[] = $itemValue->name;

                                $findItem = $this->searchMetaArray($MetaDataArray,$itemValue->name);
                                
                                if ($findItem != -1) {
                                    $ItemsArray[] = $MetaDataArray[$findItem];
                                }
                            }
                            
                            $TabArray[] = array("title"=>$title,
                                                "layout"=>"form",
                                                "fields"=>$ItemsArray);                                    
                        }
                        
                        $TabPanelArray = array("name"=>"metaTabPanel",
                                                    "xtype"=>"tabpanel",
                                                    "height"=>400,
                                                    "items"=>$TabArray);
                         if ($seperateColumns==true)
                            $MetaDataArrayNew["Tabs"] = $TabPanelArray;
                         else   
                            $MetaDataArrayNew[] = $TabPanelArray;
                         
                    }
                    

                    if (count($CreateElementsArray) > 0 && $hideCreateElements == false) {
                        $TempCol1 = array();
                          $TempCol2 = array();
                          if (count($Column1) > 0) {
                              foreach ($Column1 as $key => $value) {
                                  $TempCol1[] = $value->name;
                              }
                          }
                          
                          if (count($Column2) > 0) {
                              foreach ($Column2 as $key => $value) {
                                  $TempCol2[] = $value->name;
                              }
                          }

                        foreach ($CreateElementsArray as $key => $value) {

                            $key = str_replace("_",":",$key);
                            
                            if (in_array($key,$TempCol1) || in_array($key,$TempCol2) || in_array($key,$TempTabCol)) {

                                $MetaDataResult["metaData"]["createElements"] .= $value;
                            }
                        }
                    }
                    
                    $MetaDataArray = $MetaDataArrayNew;
                }
                
            }
            else {
                if (count($CreateElementsArray) > 0 && $hideCreateElements == false) {
                    foreach ($CreateElementsArray as $key => $value) {
                        $MetaDataResult["metaData"]["createElements"] .= $value;
                    }
                }
            }
            //FB::log($hideCreateElements);
            $MetaDataResult["metaData"]["fields"] = $MetaDataArray;
            $MetaDataResult["success"] = true;
        }
    } 
    return $MetaDataResult;   
  }
  
  private $MetaData = array();
  private $UseTemplate = false;
  private $TemplateData = array();
  private $Columns = false;
  
  private $Column1Index = 0;
  private $Column2Index = 1;
  
  private function useSeperateTemplateRenderer($Array,$Column1,$Column2) {

      $TempCol1 = array();
      $TempCol2 = array();
      if (count($Column1) > 0) {
          foreach ($Column1 as $key => $value) {
              $TempCol1[] = $value->name;
          }
      }
      
      if (count($Column2) > 0) {
          foreach ($Column2 as $key => $value) {
              $TempCol2[] = $value->name;
          }
      }
      $Columns = array_combine($TempCol1, $TempCol2);
      $MetaData = array();

      foreach ($Columns as $key => $value) {

          $Column1 = $this->searchMetaArray($Array,$key);
          if ($Column1 != -1) {
              $MetaData["Column1"][] = $Array[$Column1];
          }
          else {
              $MetaData["Column1"][] = array("empty"=>true);
          }
          
          $Column2 = $this->searchMetaArray($Array,$value);
          if ($Column2 != -1) {
              $MetaData["Column2"][] = $Array[$Column2];
          }
          else {
              $MetaData["Column2"][] = array("empty"=>true);
          } 
      }

      return $MetaData;
  }
  
  private function RenderOnTemplate($Array,$Column1,$Column2) {
      $TempCol1 = array();
      $TempCol2 = array();
      if (count($Column1) > 0) {
          foreach ($Column1 as $key => $value) {
              $TempCol1[] = $value->name;
          }
      }
      
      if (count($Column2) > 0) {
          foreach ($Column2 as $key => $value) {
              $TempCol2[] = $value->name;
          }
      }
      $Columns = array_combine($TempCol1, $TempCol2);
      $MetaData = array();
      foreach ($Columns as $key => $value) {
          $Column1 = $this->searchMetaArray($Array,$key);
          if ($Column1 != -1) {
              $MetaData[] = $Array[$Column1];
          }
          else {
              $MetaData[] = array("empty"=>true);
          }
          
          $Column2 = $this->searchMetaArray($Array,$value);
          if ($Column2 != -1) {
              $MetaData[] = $Array[$Column2];
          }
          else {
              $MetaData[] = array("empty"=>true);
          } 
      }
      return $MetaData;
  }
  
  private function objectToArray($d) {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
 
        if (is_array($d)) {
            return array_map(array($this, __FUNCTION__), $d);
        }
        else {
            return $d;
        }
    }
  
  private function searchMetaArray($array, $string)  {   
    $string = str_replace(":","_",$string);
    $in=-1;
    foreach($array as $key => $value) {   
        if ($value["id"]==$string) {
            $in=$key;  
        }
    } 
    return $in; 
  } 
  
  private function searchFieldArray($array, $string)  {   
    //$string = str_replace(":","_",$string);
    $in=-1;
    foreach($array as $key => $value) { 

        if ($value->name==$string) {
            $in=$key;  
        }
    } 
    return $in; 
  } 
  
  
  public function executeCheckout(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
                                                                  
    $checkOut = array("success"=>true,"workingCopyId"=>null);

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();  

    $spacesStore = new SpacesStore($session);
    

    $companyHome = $spacesStore->companyHome;  
    
    $Node = $session->getNode($spacesStore, $nodeId);  
    if ($Node != null) {
        try {
            $WorkingCopy = $Node->checkOut();
            $checkOut["workingCopyId"] = $WorkingCopy->getId(); 
        }
        catch (SoapFault $e) {
            $checkOut["success"] = false;
        }
    }         

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($checkOut);
    return $this->renderText($return);
  }
  
  public function executeCheckin(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    $note = $this->getRequestParameter('note');                 

    $versionchange = $this->getRequestParameter('versionchange');
    $majorChange = false;
    if ($versionchange == "major")
        $majorChange = true;             
                                              
    $checkIn = array("success"=>true,"origNodeId"=>null);

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();  

    $spacesStore = new SpacesStore($session);
    

    $companyHome = $spacesStore->companyHome;  
    
    $Node = $session->getNode($spacesStore, $nodeId);  
    if ($Node != null) {
        try {
            $OrigNode = $Node->checkIn($note,$majorChange);
            $checkIn["origNodeId"] = $OrigNode->getId();                
        }
        catch (SoapFault $e) {
            $checkIn["success"] = false;
        }
    }         

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($checkIn);
    return $this->renderText($return);
  }   
  
  public function executeCancelCheckout(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
                                                                  
    $checkIn = array("success"=>true);

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();  

    $spacesStore = new SpacesStore($session);
    

    $companyHome = $spacesStore->companyHome;  
    
    $Node = $session->getNode($spacesStore, $nodeId);  
    if ($Node != null) {
        try {
            $Node->cancelCheckout();  
        }
        catch (SoapFault $e) {
            $checkIn["success"] = false;
        }
    }         

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($checkIn);
    return $this->renderText($return);
      
  }
  
  public function executeDownload(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
                                                                  
    $checkOut = array("success"=>true);

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    

    $companyHome = $spacesStore->companyHome;  
    
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
            
        }
    }       

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($checkOut);
    return $this->renderText($return);
  } 
  
  public function executeContentTypeList(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    
    $companyHome = $spacesStore->companyHome;  
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    $Node = $session->getNode($spacesStore,$nodeId); 
    if ($Node != null) {
    $types = array();         
        $TypesFetch = $this->getContentTypesList($RestDictionary,$Node->getType());
        if (count($TypesFetch) > 0) {
            foreach ($TypesFetch as $Type) {
                $name = $Type->name;            
                $title = $Type->title;            
                $description = $Type->description;            
                
                if (empty($title))
                    $title = $name;
                else
                    $title = $title;
                    
                $types["types"][] = array("name"=>$name,"title"=>$title,"description"=>$description);                     
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
  
  public function executeGetAvailableContentTypes(sfWebRequest $request) {
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    
    $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);
    
    $types = array();         
    $TypesFetch = $this->getContentTypesList($RestDictionary,"cm_content");
    if (count($TypesFetch) > 0) {
        foreach ($TypesFetch as $Type) {
            $name = $Type->name;            
            $title = $Type->title;            
            $description = $Type->description;            
            
            if (empty($title))
                $title = $name;
            else
                $title = $title;
                
            $types["types"][] = array("name"=>$name,"title"=>$title,"description"=>$description);                     
        }
    }
      
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($types);
    return $this->renderText($return);
  }
  
  private function getContentTypesList($RestDictionary,$currentType) {
      $q = Doctrine_Query::create()
        ->from('Allowedtypes a');
      $Types = $q->execute();

      $TypeList = array();
   
      if (count($Types) == 0) {
          
          $SubClasses = $RestDictionary->GetSubClassDefinitions("cm_content");

          if ($SubClasses != null) {                                  
              foreach ($SubClasses as $Type) {
                $Name = $Type->name;  
                if ($currentType != $Name)
                    $TypeList[] = $Type;
              }
          }
      }
      else {
        foreach ($Types as $Type) {
            try {
                if ($currentType != $Type) {  
                    $Name = str_replace(":","_",$Type);               
                    $TypeInfo = $RestDictionary->GetClassDefinitions($Name);   
                    $TypeList[] = $TypeInfo;                      
                }
            }
            catch (Exception $e) {
                
            }
        }   
      } 
      return $TypeList;
  }
  
  public function executeSaveContentType(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    $type = $this->getRequestParameter('type');

    $data = array("success"=>false,"nodeId"=>$nodeId);
    if (!empty($type)) {
        try {
            $user = $this->getUser();                
            $repository = $user->getRepository();
            $session = $user->getSession();
            $ticket = $user->getTicket();

            $spacesStore = new SpacesStore($session);
            $Node = $session->getNode($spacesStore, $nodeId);  
            
            if ($Node != null) {
                $RestDictionary = new RESTDictionary($repository,$spacesStore,$session);                           
                $Result = $RestDictionary->SpecifyType($nodeId,$type);

                if (isset($Result->current))
                    $data["success"] = true;
            }
        
        }
        catch (Exception $e) {
            $data["success"] = false;   
        }
    }
    

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);
  } 
  
  public function executeSaveAspects(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    $aspects = $this->getRequestParameter('aspects');
    $deselaspects = $this->getRequestParameter('deselaspects');

    $data = array("success"=>false,"nodeId"=>$nodeId);
    if (!empty($aspects)) {
        try {
            $user = $this->getUser();                
            $repository = $user->getRepository();
            $session = $user->getSession();
            $ticket = $user->getTicket();

            $spacesStore = new SpacesStore($session);
            $Node = $session->getNode($spacesStore, $nodeId);  
        
            if ($Node != null) {
                $AspectArray = array();
                // NEW ASPECTS
                $explode = explode(",",$aspects);
                if (count($explode) > 0) {
                    for ($i = 0; $i < count($explode); $i++) {
                        $Aspect = str_replace(":","_",$explode[$i]);
                        if (!$Node->hasAspect($Aspect)) {
                            $Node->addAspect($Aspect);  
                        }
                    }    
                }
                else {
                    $Aspect = str_replace(":","_",$aspects);        
                    if (!$Node->hasAspect($Aspect)) {
                        $Node->addAspect($Aspect);
                    }
                }
                
                // DESELECTED ASPECTS       
                $explode = explode(",",$deselaspects);
                if (count($explode) > 0) {
                    for ($i = 0; $i < count($explode); $i++) {
                        $Aspect = str_replace(":","_",$explode[$i]);
                        if ($Node->hasAspect($Aspect)) {
                            $Node->removeAspect($Aspect);  
                        }
                    }    
                }
                else {
                    $Aspect = str_replace(":","_",$deselaspects);        
                    if ($Node->hasAspect($Aspect)) {
                        $Node->removeAspect($Aspect);
                    }
                }
                
                $session->save();
                $data["success"] = true;
            }
        
        }
        catch (Exception $e) {
            $data["success"] = false;
        }
    }
    

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);
  } 
  
  public function executeAddAspects(sfWebRequest $request) {
      $nodeId = $this->getRequestParameter('nodeId');
      
      $user = $this->getUser();                
      $repository = $user->getRepository();
      $session = $user->getSession();
      $ticket = $user->getTicket();
      $spacesStore = new SpacesStore($session);   
      
      $RestAspects = new RESTAspects($repository,$spacesStore,$session);  
       
      $CurrentAspectList = $this->currentAspects($nodeId,$RestAspects);

      $this->CurrentAspectList = $CurrentAspectList;
      $this->AspectList = $this->getAspectList($RestAspects,$CurrentAspectList);
      $this->nodeId = $nodeId;
      $this->setLayout(false);
  } 
  
  private function currentAspects($nodeId,$RestAspects) {

      $Aspects = Doctrine::getTable('Allowedaspects');

      $CurrentAspects = $RestAspects->GetNodeAspects($nodeId);      
      $CurrentAspectList = array();   
      if (count($CurrentAspects->current) > 0) {
          
          foreach ($CurrentAspects->current as $Aspect) {
            $AspectInfo = $RestAspects->GetAspect($Aspect);

            if (count($Aspects) > 0) {   
                if (count($Aspects->findByName($Aspect)) > 0) {   
                    $CurrentAspectList[$Aspect] = $AspectInfo;
                }
            }
          } 
      }
      else
        return $CurrentAspectList;  

      return $CurrentAspectList; 
  }
  
  private function getAspectList($RestAspects,$AlreadyInUse) {
      $q = Doctrine_Query::create()
        ->from('Allowedaspects a');
      $Aspects = $q->execute();

      $AspectList = array();
      //$RestAspects = new RESTAspects($repository,$spacesStore,$session);       
      if (count($Aspects) == 0) {
          
          $AspectList = $RestAspects->GetAllAspects();
          foreach ($AspectList as $Aspect) {
            $AspectInfo = $RestAspects->GetAspect($Aspect);         
            
            if (!array_key_exists($Aspect,$AlreadyInUse))
                $AspectList[] = $AspectInfo;
          }
      }
      else {
        foreach ($Aspects as $Aspect) {
            $AspectInfo = $RestAspects->GetAspect($Aspect);

            if (!array_key_exists((string)$Aspect,$AlreadyInUse))
                $AspectList[] = $AspectInfo;
        }   
      }
      return $AspectList;
  }
  
  public function executeSaveMetadata(sfWebRequest $request) {
      
    $nodeId = $this->getRequestParameter('nodeId');                                   
    $fields = $this->getRequestParameter('data');    
    $fields = base64_decode($fields);
    
    $data = array("success"=>false,"nodeId"=>$nodeId);
    
    //try {
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();
        $NamespaceMap = NamespaceMap::getInstance();

        
        $spacesStore = new SpacesStore($session);
        $Node = $session->getNode($spacesStore, $nodeId);  
        try {
            if ($Node != null && !empty($fields)) {
                $fields = json_decode($fields);  
                foreach ($fields as $fieldName => $fieldValue) {     
                    if (preg_match("/^prop_/eis",$fieldName) || preg_match("/^assoc_/eis",$fieldName)) {
                        $fieldName = str_replace("prop_","",$fieldName);
                        $fieldName = str_replace("assoc_","",$fieldName);
                        $fieldName = trim($fieldName);          
                        
                        $explode = explode("#",$fieldName);
                        if (count($explode) >= 2) {
                            $fieldName = $explode[0];
                            $dataType = $explode[1];
                            
                            switch ($dataType) {
                                case "text":
                                case "mltext":
                                case "long":
                                case "int":   
                                          
                                    $orgField = str_replace("_",":",$fieldName);
                                    
                                    $LookupSearch = Doctrine_Query::create()
                                     ->from('Lookups l')
                                     ->where('l.field = ?',$orgField)
                                     ->fetchOne();
                                     
                                     if ($LookupSearch != null) { 
       
                                        if (!empty($fieldValue) && $fieldValue != null) { 
                                            if (!is_array($fieldValue)) {
                                                $fieldValue = array($fieldValue);
                                            }
                                            $fieldValue = implode(", ",$fieldValue);
                                        }
                                        else
                                            $fieldValue = null; 
                                     } 
                                      
                                     $Node->{$fieldName} = $fieldValue;   

                                break;
                                case "double":
                                case "float":
                                    if (empty($fieldValue) && !is_double($fieldValue) && !is_float($fieldValue))
                                        $fieldValue = null;
                                    $Node->{$fieldName} = $fieldValue;   
                                break;
                                case "text_constraints":          
                                    if (!empty($fieldValue) && $fieldValue != null) { 
                                        //if (!is_array($fieldValue))
                                        //    $fieldValue = array($fieldValue);
                                    }
                                    else
                                        $fieldValue = null;
                                    $Node->{$fieldName} = $fieldValue;                   
                                break; 
                                case "boolean":
                                    $Node->{$fieldName} = (string)($fieldValue==true? "true": "false");
                                break;
                                case "date":
                                case "datetime":  
                                    if (!empty($fieldValue))
                                        $fieldValue = date("c",strtotime($fieldValue));    
                                    else
                                        $fieldValue = null;    
                                    //FB::log($fieldName ."=>".$fieldValue);
                                    $Node->{$fieldName} = $fieldValue;   
                                              
                                break;
                                case "category":

                                    if (!is_array($fieldValue))
                                        $fieldValue = array();
                                        
                                    $CatArray = array();
                                    foreach ($fieldValue as $Category) {
                                        $id = $Category->id;
                                        $CategoryNode = $session->getNode($spacesStore,$id);   
                                        if ($CategoryNode != null) {
                                            $CatArray[] = $CategoryNode;     
                                        }
                                    }
                                    
                                    //FB::log($fieldValue);       
                                    $Node->{$fieldName} = $CatArray;
                                break;                            
                                case "tags":
                                    $Tags = explode(",",$fieldValue);
                                    if (count($explode) == 0) {
                                        $Tags = array($fieldValue);
                                    }

                                    $RestTags = new RESTTags($repository,$spacesStore,$session);
                                    //$RestTags->DeleteNodeTags($nodeId);
                                    $Node->{$fieldName} = null;
                                    $session->save();  
                                    $RestTags->AddNodeTags($nodeId,$Tags);
                                break;
                                case "person":
                                    $Node->removeAssociationOfType($fieldName);  
                                    $session->save();
                                    if (!empty($fieldValue) && strlen($fieldValue) > 2) {
                                        $Persons = json_decode($fieldValue);
                                        
                                        try {
                                            if (is_array($Persons)) {
                                                
                                                $PersonArray = array();
                                                foreach ($Persons as $Person) {
                                                    $id = $Person->id;
                                                    $PersonNode = $session->getNode($spacesStore,$id);
                                                    
                                                    if ($PersonNode != null && !$Node->hasAssociation($PersonNode,$fieldName)) {
                                                        $Node->addAssociation($PersonNode,$fieldName);                       
                                                    }                     
                                                }
                                                
                                            }
                                        }
                                        catch (Exception $e) {
                                            
                                        }
                                    }  
                                break;
                                case "content":
                                    $Node->removeAssociationOfType($fieldName);  
                                    $session->save();
                                    if (!empty($fieldValue) && strlen($fieldValue) > 2) {
                                        $Contents = json_decode($fieldValue);
                                        
                                        try {
                                            if (is_array($Contents)) {
                                                
                                                $ContentArray = array();
                                                foreach ($Contents as $Content) {
                                                    $id = $Content->id;
                                                    $ContentNode = $session->getNode($spacesStore,$id);
                                                    if ($ContentNode != null && !$Node->hasAssociation($ContentNode,$fieldName)) {
                                                        $Node->addAssociation($ContentNode,$fieldName);                       
                                                    }                     
                                                }
                                                
                                            }
                                        }
                                        catch (Exception $e) {
                                            //FB::log($e->getMessage());
                                        }
                                    }
                                break;
                                default:

                                break;
                            }
                        } 
                    }
                } 
                            
                $session->save();     
                $data["success"] = true;    
            }   
        }
        catch (Exception $e) {
           $data["success"] = false;     
           $data["errorMessage"] = $e->getMessage();
        }  
    //}
    //catch (SoapFault $e) {

        //$data["errormessage"] = $e->getMessage();           
        //$data["success"] = false;           
    //}
                              
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');
        

    $return = json_encode($data);
    return $this->renderText($return);    
  }
  
  public function executeGetNameOfNode(sfWebRequest $request) { 
    $nodeId = $this->getRequestParameter('nodeId');                                         
    $data = array("nodeId"=>$nodeId);   
    try { 
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();
        

        $spacesStore = new SpacesStore($session);
        $Node = $session->getNode($spacesStore, $nodeId);  

        if ($Node != null) {    
            $data["success"] = true;           
            $data["name"] = $Node->cm_name;                                              
            $data["imgName"] = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name;     
        }
    }
    catch (Exception $e) {
        $data["success"] = false;    
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($data);
    return $this->renderText($return);                      
  }
} 
