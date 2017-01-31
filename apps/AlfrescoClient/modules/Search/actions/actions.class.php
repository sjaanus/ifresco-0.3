<?php

/**
 * Search actions.
 *
 * @package    AlfrescoClient
 * @subpackage Search
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
class SearchActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $widget = new sfAlfrescoWidgetTags();
    $url = $this->getController()->genUrl('tags/autocompleteTagData');
    $widget->addOption("urlfor",$url);

    $this->TagBox = $widget->render("tagSearchField", '', array());
    
    $SearchTemplate = Doctrine_Query::create()
     ->from('SearchTemplates t')
     ->where('t.defaultview=1')
     ->fetchOne();  
     
     if ($SearchTemplate != null) {
        $this->ColumnsetId = $SearchTemplate->getColumnsetId();
     }
     else {
        $this->ColumnsetId = 0;
     }
     
     $this->TimeFormat = $this->getUser()->getTimeFormat();
     
    //$this->forward('default', 'module');
  }
  
  private $formConfig = array();
  private $CreateElementsArray = array();
  private function RenderField($name,$dataType,$Label,$mandatory,$class,$FieldType="property") {
    $DataKeyName = str_replace(":","_",$name);
    $id = str_replace(":","_",$name);
    $dataType = str_replace("d:","",$dataType);
    
    $FieldProp = $this->importedClasses[$class][$name];

    $MetaDataArray = array();           
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
                            "xtype"=>"textarea"
                        )
                    );
                break;*/
                case "mltext":
                case "text":
                    $constraints = $FieldProp->constraints;
                    $findConst = false;
                    if (count($constraints) > 0) {

                        foreach ($constraints as $const => $value) {
                            switch ($value->type) {
                                case "LIST":
                                    $List = $value->parameters->allowedValues;
                                    
                                    /*if (count($List) > 0) {
                                        $choices = array();
                                        
                                        foreach ($List as $ListKey => $ListValue) {
                                            if (!empty($choicesList))
                                                $choicesList .= ",";
                                            $choices[$ListKey] = $ListValue;
                                        }
                                        $findConst = true;

                                        $MetaDataArray[] = array(
                                            "name"=>$DataKeyName,
                                            "id"=>$id,
                                            "fieldLabel"=>$Label,
                                            "editor"=>array(
                                                "xtype"=>"multiselect",
                                                "store"=>$choices,
                                                "width"=>$this->formConfig["defaults"]["width"],
                                                "height"=>'auto',
                                                "ddReorder"=>false,
                                                "border"=>false
                                            )
                                        );
                                    }*/
                                    if (count($List) > 0) {
                                        $choices = array();
                                        
                                        foreach ($List as $ListKey => $ListValue) {
                                            if (!empty($choicesList))
                                                $choicesList .= ",";
                                            $choices[$ListKey] = $ListValue;
                                        }
                                        $findConst = true;

                                        if ($FieldProp->repeating == true) {
                                            $MetaDataArray[] = array(
                                                "name"=>$DataKeyName."#list",
                                                "id"=>$id,
                                                "fieldLabel"=>$Label,
                                                "editor"=>array(
                                                    "xtype"=>"superboxselect",
                                                    "store"=>$choices,
                                                    "width"=>$this->formConfig["defaults"]["width"],
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
                                                    "width"=>$this->formConfig["defaults"]["width"],
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
                         ->where('l.field = ?',$name)
                         ->andWhere('l.type = ?',"category")
                         ->fetchOne();
                        
                        $foundLookup = false;
                         
                        if ($LookupSearch != null) { 
                            
                            $catId = $LookupSearch->getFielddata();
                            $single = $LookupSearch->getSingle();

                            $CategoryNode = $this->session->getNode($this->spacesStore, $catId);
                            $Categories = $CategoryNode->getChildren();
                            if (count($Categories) > 0) { 
                                $foundLookup = true;
                                //$CategoryList = array();
                                $choices = array();
                                            
                                foreach ($Categories as $ListKey => $ListValue) {
                                    $CatName = preg_replace("/\{.*?\}/is","",$ListValue->getName());
                                    
                                    if (!empty($choicesList))
                                        $choicesList .= ",";
                                    $choices[] = $CatName;
                                }
                                
                                if ($single == 1 || $single == "1" || $single == true) { 
                                    $MetaDataArray[] = array(
                                        "name"=>$DataKeyName,
                                        "id"=>$id,
                                        "fieldLabel"=>$Label,
                                        "editor"=>array(
                                            "xtype"=>"combo",
                                            "store"=>$choices,
                                            "width"=>$this->formConfig["defaults"]["width"],
                                            "mode"=>"local",
                                            "typeAhead"=>false,
                                            "forceSelection"=>false,
                                            "triggerAction"=>"all",
                                        )
                                    );
                                }
                                else {
                                    $MetaDataArray[] = array(
                                        "name"=>$DataKeyName."#list",
                                        "id"=>$id,
                                        "fieldLabel"=>$Label,
                                        "editor"=>array(
                                            "xtype"=>"superboxselect",
                                            "store"=>$choices,
                                            "width"=>$this->formConfig["defaults"]["width"],
                                            "mode"=>"local",
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
                                        "xtype"=>"textarea"
                                    )
                                );
                            }
                            else {
                                $MetaDataArray[] = array(
                                    "name"=>$DataKeyName,
                                    "id"=>$id
                                    ,"fieldLabel"=>$Label
                                    ,"editor"=>array(
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
                    $MetaDataArray[] = array(
                        "name"=>$DataKeyName,
                        "id"=>$id
                        ,"fieldLabel"=>$Label
                        ,"editor"=>array(
                             "xtype"=>"numberfield"
                        )
                    );
                    
                break;
                case "date":
                    $MetaDataArray[] = array(
                        "name"=>$DataKeyName,
                        "id"=>$id
                        ,"fieldLabel"=>$Label
                        ,"editor"=>array(
                             "xtype"=>"fieldset",
                             "checkboxToggle"=>true,
                            "title"=>$Label,
                            "autoHeight"=>true,
                            "width"=>$this->formConfig["defaultsTab"]["width"],
                            "defaultType"=> 'datefield',
                            "collapsed"=>true,

                             "items"=>array(
                                 array(
                                    "name"=>$DataKeyName."#from",
                                    "id"=>$id."#from",
                                    "xtype"=>"datefield", 
                                    "format"=>$this->getUser()->getDateFormat(),
                                    "width"=>"150",              
                                    "fieldLabel"=>"From"
                                 ),
                                 array(
                                    "name"=>$DataKeyName."#to",
                                    "id"=>$id."#to",
                                    "xtype"=>"datefield",  
                                    "format"=>$this->getUser()->getDateFormat(),
                                    "width"=>"150",              
                                    "fieldLabel"=>"To"
                                 )
                             )
                        )
                    ); 
                break; 
                case "datetime":
                    $MetaDataArray[] = array(
                        "name"=>$DataKeyName,
                        "id"=>$id
                        ,"fieldLabel"=>$Label
                        ,"editor"=>array(
                             "xtype"=>"fieldset",
                             "checkboxToggle"=>true,
                            "title"=>$Label,
                            "autoHeight"=>true,
                            "width"=>$this->formConfig["defaultsTab"]["width"],
                            "defaultType"=> 'datefield',
                            "collapsed"=>true,

                             "items"=>array(
                                 array(
                                    "name"=>$DataKeyName."#from",
                                    "id"=>$id."#from",
                                    "xtype"=>"xdatetime", 
                                    "timeFormat"=>$this->getUser()->getTimeFormat(),
                                    "dateFormat"=>$this->getUser()->getDateFormat(),            
                                    "width"=>"200",              
                                    "fieldLabel"=>"From"
                                 ),
                                 array(
                                    "name"=>$DataKeyName."#to",
                                    "id"=>$id."#to",
                                    "xtype"=>"xdatetime", 
                                    "timeFormat"=>$this->getUser()->getTimeFormat(),
                                    "dateFormat"=>$this->getUser()->getDateFormat(),
                                    "width"=>"200",           
                                    "fieldLabel"=>"To"
                                 )
                             )
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
                        $widget = new sfAlfrescoWidgetTags();
                        $url = $this->getController()->genUrl('tags/autocompleteTagData');
                        $widget->addOption("urlfor",$url);
                        $this->CreateElementsArray[$id] = $widget->render($DataKeyName, 'value', array());
                        
                        $MetaDataArray[] = array(
                            "name"=>$DataKeyName,
                            "id"=>$id,
                            "fieldLabel"=>$Label,
                            "el"=>"tagBoxList$DataKeyName",
                            "editor"=>array( 
                            )
                        );
                    }
                    else {
                        $url = $this->getController()->genUrl('Categories/GetCategoryCheckTree');
                        $MetaDataArray[] = array(
                            "name"=>$DataKeyName,
                            "id"=>$id,
                            "fieldLabel"=>$Label,
                            "editor"=>array(
                                "xtype"=>"treepanel",
                                "width"=>$this->formConfig["defaults"]["width"],
                                "height"=>250,
                                "useArrows"=>false,
                                "autoScroll"=>true,
                                "enableDD"=>false,
                                "containerScroll"=>true,
                                "frame"=>true,
                                "isFormField"=>true,
                                "rootVisible"=>false,
                                "bodyStyle"=>"background:white;",
                                "dataUrl"=>$url,
                                "root"=>array(
                                    "nodeType"=>"async",
                                    "text"=>"root",
                                    "draggable"=>false,   
                                    "id"=>"root",
                                    "expanded"=>true,
                                    "border"=>true
                                )
                            )
                        );

                    }
                break;
                
                default:
                break;
            }

       break;
       case "association":
            // Doesnt work in Search
            continue;
       
            switch ($dataType) {
                case "cm:person":
                    $widget = new sfAlfrescoWidgetUserAssociation();
                    $url = $this->getController()->genUrl('Association/autocompleteUserData');
                    $widget->addOption("urlfor",$url);
                    //$MetaDataResult["metaData"]["createElements"] .= $widget->render($DataKeyName, 'value', array());
                    $this->CreateElementsArray[$id] = $widget->render($DataKeyName, 'value', array());
                    
                    $MetaDataArray[] = array(
                        "name"=>$DataKeyName."#assoc#userAssociationValues$DataKeyName",
                        "id"=>$id,
                        "fieldLabel"=>$Label,
                        "el"=>"userAssociationBox$DataKeyName",
                        "editor"=>array( 
                        )
                    );
                break;
                case "cm:content":
                    $widget = new sfAlfrescoWidgetContentAssociation(); 
                    $url = $this->getController()->genUrl('Association/autocompleteContentData');
                    $widget->addOption("urlfor",$url);
                    //$MetaDataResult["metaData"]["createElements"] .= $widget->render($DataKeyName, 'value', array());
                    $this->CreateElementsArray[$id] = $widget->render($DataKeyName, 'value', array());
                    
                    $MetaDataArray[] = array(
                        "name"=>$DataKeyName."#assoc#contentAssociationValues$DataKeyName",
                        "id"=>$id,
                        "fieldLabel"=>$Label,
                        "el"=>"contentAssociationBox$DataKeyName",
                        "editor"=>array( 
                        )
                    );
                break;    
                default:
                break;
            }
        break;
        default:
        break;
    }

    return $MetaDataArray;
  }
  
  private $usedFields = array();
  
  private $importedClasses = array();
  
  private $repository = null;
  private $session = null;
  private $ticket = null;
  private $user = null;
  private $spacesStore = null;
  private $namespaceMap = null;
  private $RestDict = null;
  
  public function executeGetSearchForm()
  {
    $template = $this->getRequestParameter('template');
    $nodeId = null;
    

    $this->user = $this->getUser();                
    $this->repository = $this->user->getRepository();
    $this->session = $this->user->getSession();
    $this->ticket = $this->user->getTicket();

    $this->spacesStore = new SpacesStore($this->session);
    
    $this->namespaceMap = NamespaceMap::getInstance();
    
    $this->RestDict = new RESTDictionary($this->repository,$this->spacesStore,$this->session);

    $companyHome = $this->spacesStore->companyHome;  
    
    $this->widgets = array();
    $this->widgetsValues = array();
 
    $SearchTemplate = null;
    
    if ($template == "null" || empty($template) || !is_numeric($template)) {
        $SearchTemplate = Doctrine_Query::create()
         ->from('SearchTemplates t')
         ->where('t.defaultview=1')
         ->fetchOne();  
         
        if ($SearchTemplate == null) {
            $SearchTemplate = new SearchTemplates();
            $SearchTemplate->setDefaultview(0);
            $SearchTemplate->setColumnsetId(0);
            $SearchTemplate->setShowdoctype(array());
            $SearchTemplate->setMulticolumn(false);
            
            
            $SearchTemplate->setJsondata('{"Column1":[{"name":"cm:name","class":"cm:content","dataType":"d:text","title":"'.$this->getContext()->getI18N()->__("Name" , null, 'messages').'","type":"property"}],"Column2":[],"Tabs":[]}');
        }
    }
    else {
        $SearchTemplate = Doctrine_Query::create()
         ->from('SearchTemplates t')
         ->where('t.id = ?',$template)
         ->fetchOne();
    }
    

    if ($SearchTemplate != null) {
    
        
        $JsonData = $SearchTemplate->getJsondata();
        if (!empty($JsonData)) {

            
            $JsonData = json_decode($JsonData);
            $Column1 = $JsonData->Column1;
            $Column2 = $JsonData->Column2;
            $Tabs = $JsonData->Tabs;
            
            $this->usedFields = array();
            
            $this->formConfig = array(
                "labelAlign"=>"top",
                "columnCount"=>2,
                "labelWidth"=>90,
                "frame"=>false,
                "defaults"=>array(
                    "width"=>300
                ),
                "defaultsTab"=>array(
                    "width"=>300,
                )
            );
            
            $MetaDataResult = array(
                "success"=>false,
                "metaData"=>array(
                     "fields"=>array(),
                     "formConfig"=>$this->formConfig,
                     "createElements"=>""
                ),
                "data"=>array()
            );
            
            $MetaDataArray = array();
            
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

            
            
            $Columns = array();
            for ($i = 0;$i < count($Column1);$i++) {
                $Columns[] = $Column1[$i];
                $Columns[] = $Column2[$i];
            }

            
            foreach ($Columns as $key => $value) {
                
                if (empty($value)) {
                    $MetaDataArray[] = array("empty"=>true);
                    continue;
                }   
                
                $name = $value->name;
                if (in_array($name,$this->usedFields)) {
                    $MetaDataArray[] = array("empty"=>true);
                    continue;
                }
                
                $class = $value->class;

                $dataType = $value->dataType;
                $Label = $value->title;
                $type = $value->type;
                
                $this->importClassForm($class);

                $MetaDataArray = array_merge($MetaDataArray,$this->RenderField($name,$dataType,$Label,false,$class,$type));
                $this->usedFields[] = $name;
            }

            $TabArray = array();
            
            if (count($Tabs) > 0) {
                $RealTabs = $Tabs->tabs;
                if (count($RealTabs) > 0) {
                    
                    foreach ($RealTabs as $key => $TabValues) {
                        $title = $TabValues->title;
                        $items = $TabValues->items;
                        
                        $ItemsArray = array();
                        
                        foreach ($items as $itemKey => $itemValue) {
                            $split = split("/",$itemValue);
                            if (count($split) > 0) {
                            
                                $name = $split[0];
                                if (in_array($name,$this->usedFields)) {
                                    continue;
                                }
                                    
                                $class = $split[1];
                                $dataType = $split[3];
                                $Label = $split[2];
                                $type = $split[4];
                                
                                $this->importClassForm($class);
                                
                                $ItemsArray = array_merge($ItemsArray,$this->RenderField($name,$dataType,$Label,false,$class,$type));
                                $this->usedFields[] = $name;
                            }
                        }
                        
                        if (count($ItemsArray) > 0) {
                            $TabArray[] = array("title"=>$title,
                                                "layout"=>"form",
                                                "fields"=>$ItemsArray);         
                        }                           
                    }
                    
                    
                }
            }
            
            $showdoctypeMaster = $SearchTemplate->getShowdoctype();
            
            if (!empty($showdoctypeMaster)) {
                $jsonDoc = json_decode($showdoctypeMaster);
                if (is_array($jsonDoc)) {
                    foreach ($jsonDoc as $showdoctype) {
                        $class = str_replace(":","_",$showdoctype);
                        $ClassDetails = $this->RestDict->GetClassDefinitions($class);
                        
                        $this->importClassForm($class);
                        
                        if ($ClassDetails != null) {
                            
                            $Properties = $ClassDetails->properties;
                            $Associations = $ClassDetails->associations;
                            $Title = $ClassDetails->title;
                            
                            $ItemsArray = array();
                            if (count($Properties) > 0) {

                                foreach ($Properties as $key => $value) {
                                    $propName = str_replace(":","_",$key);
                                    $Property = $this->RestDict->GetClassProperty($class,$propName);
                                    if ($Property != null) {
                                        $name = $Property->name;
                                        
                                        if (in_array($name,$this->usedFields)) {
                                            continue;
                                        }
                                        
                                        $dataType = $Property->dataType;
                                        $Label = $Property->title;
                                        $ItemsArray = array_merge($ItemsArray,$this->RenderField($name,$dataType,$Label,false,$class));
                                        $this->usedFields[] = $name;
                                    }
                                }
                            }
                            
                            if (count($Associations) > 0) {
                                foreach ($Associations as $key => $value) {
                                    $assocName = str_replace(":","_",$key);
                                    $Assoc = $this->RestDict->GetClassAssociation($class,$assocName);
                                    if ($Assoc != null) {
                                        $name = $Assoc->name;
                                        
                                        if (in_array($name,$this->usedFields)) {
                                            continue;
                                        }
                                        
                                        if (count($Assoc->target) > 0) {
                                            $dataType = $Assoc->target->class;
                                        }
                                        else
                                            continue;

                                        $Label = $Assoc->title;
                                        $ItemsArray = array_merge($ItemsArray,$this->RenderField($name,$dataType,$Label,false,$class,'association'));
                                        $this->usedFields[] = $name;
                                    }
                                }
                            }

                            if (count($ItemsArray) > 0) {
                                $TabArray[] = array("title"=>$Title,
                                                    "layout"=>"form",
                                                    "fields"=>$ItemsArray);                                    
                            }
                        }
                    }
                }
            }
            
            if (count($TabArray) > 0) {
                $MetaDataArray[] = array("name"=>"metaTabPanel",
                                                    "xtype"=>"tabpanel",
                                                    "height"=>400,
                                                    "items"=>$TabArray);
            }
            
            
            
            if (count($this->CreateElementsArray) > 0) {
                /*foreach ($CreateElementsArray as $key => $value) {
                    $key = str_replace("_",":",$key);
                    if (in_array($key,$Column1) || in_array($key,$Column2))
                        $MetaDataResult["metaData"]["createElements"] .= $value;
                }*/
                $MetaDataResult["metaData"]["createElements"] = join("",$this->CreateElementsArray);
            }
            
            $MetaDataResult["metaData"]["fields"] = $MetaDataArray;
            $MetaDataResult["success"] = true;
            
            
        }
        
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->getResponse()->setHttpHeader('Pragma','no-cache');


        
        
    }
    $return = json_encode($MetaDataResult);
    return $this->renderText($return);
  }
  
  private function importClassForm($class) {
    if (!empty($class)) {
        if (!array_key_exists($class,$this->importedClasses)) {
            $this->importedClasses[$class] = array();
            $form = $this->RestDict->GetClassForm($class); 
            if (!empty($form->data)) {
                $data = $form->data->definition->fields;
                
                foreach ($data as $field) {
                    $fieldName = $field->name;
                    $this->importedClasses[$class][$fieldName] = $field;
                }
            }
        }
    }
  }
  
  public function executeSearchTemplates(sfWebRequest $request) {
    $q = Doctrine_Query::create()
        ->from('SearchTemplates t');
 
    $templates = $q->execute();
    
    $templateArray = array("templates"=>array());
    

    foreach ($templates as $templateKey => $template) {
        $JsonData = json_decode($template->getJsondata());
        $templateArray["templates"][] = array("id"=>$template->getId(),
                                              "name"=>$template->getName(),
                                              "columnsetid"=>$template->getColumnsetId());
    }
                                              
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($templateArray);
    return $this->renderText($return);
  }
}
