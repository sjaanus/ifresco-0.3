<?php

/**
 * Categories actions.
 *
 * @package    AlfrescoClient
 * @subpackage Categories
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
class CategoriesActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    //$this->forward('default', 'module');
  }

  public function executeGetCategoryCheckTree(sfWebRequest $request) {
        $readall = $this->getRequestParameter('readall');   
        $values = $this->getRequestParameter('values');   
        $DefaultValues = array();
        if (!empty($values)) {
            $DefaultValues = explode(",",$values);
            if (count($DefaultValues) == 0)
                $DefaultValues = array($FieldValue);
        }   
        
        if (empty($readall))
            $readall = false;   
            
        if (!empty($readall) && $readall == "true" || $readall == true)
            $readall = true; 
                        
FB::Log($DefaultValues);

        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);

        $categoryName = $_POST['node'];
        if ($categoryName == "root" || empty($categoryName)) {
            $categoryName = "";
            $breadCrumb = "";
        }
        else
            $breadCrumb = urldecode($categoryName)."/";

        $this->restCategories = new RESTCategories($repository,$spacesStore,$session);
        
        $categories = $this->restCategories->GetCategories($categoryName);
        $content = ""; 
        $array = array(); 
        
        $iconClasses = array("tag_green","tag_orange","tag_pink","tag_purple","tag_yellow");
        
        if (count($categories->items) > 0) {
            $match = array();
            $count = preg_match_all("#/#eis",$breadCrumb,$match);
            if ($count > 4)
                $iconCls = "tag_red";
            else
                $iconCls = $iconClasses[$count];  
            foreach ($categories->items as $item) {
                    $nodeId = str_replace("workspace://SpacesStore/","",$item->nodeRef);
                    $checked = false;
                    if (in_array($item->nodeRef,$DefaultValues)) {
                        FB::Log("Found -> ".$item->name);
                        $checked = true;    
                    }
                     
                    $arrVal = array("cls"=>"folder",
                     "id"=>str_replace(" ","%20",$breadCrumb.$item->name),
                     "nodeId"=>$nodeId,
                     "checked"=>$checked,
                     "expanded"=>$checked,
                     "leaf"=>($item->hasChildren == true ? false : true),
                     "iconCls"=>'category_'.$iconCls,
                     "text"=>$item->name);
                     

                     if ($readall == true && $item->hasChildren == true) {
                         //$arrVal["children"] = $this->readRecursiveCategory($item->name,$breadCrumb.$item->name,$DefaultValues);
                         $children = $this->readRecursiveCategory($item->name,$breadCrumb.$item->name,$DefaultValues);     
                         $arrVal["children"] = $children["items"];  
                         if ($children["found"] == true)   
                            $arrVal["expanded"] = true;          
                     }
                     
   

                     //if ($Node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
                     //   $arrVal["iconCls"] = "sites-icon";
                     //}
                    
                    $array[] = $arrVal;
            }    
        }              

        
        return $this->renderText(json_encode($array));    
  } 
  

  
  private function readRecursiveCategory($categoryName,$breadCrumb,$DefaultValues) {
    $searchCat = $breadCrumb;
    $searchCat = str_replace(" ","%20",$searchCat);        
    $breadCrumb = urldecode($breadCrumb)."/";
    

    $categories = $this->restCategories->GetCategories($searchCat);    
    $array = array("items"=>array(),"found"=>false); 
        
    $iconClasses = array("tag_green","tag_orange","tag_pink","tag_purple","tag_yellow");
    if (count($categories->items) > 0) {
        $match = array();
        $count = preg_match_all("#/#eis",$breadCrumb,$match);
        if ($count > 4)
            $iconCls = "tag_red";
        else
            $iconCls = $iconClasses[$count];  
        foreach ($categories->items as $item) {
                $nodeId = str_replace("workspace://SpacesStore/","",$item->nodeRef);
                $checked = false;
                if (in_array($item->nodeRef,$DefaultValues)) {
                    FB::Log("Found -> ".$item->name);
                    $checked = true;    
                    $array["found"] = true;
                }
                 
                $arrVal = array("cls"=>"folder",
                 "id"=>str_replace(" ","%20",$breadCrumb.$item->name),
                 "nodeId"=>$nodeId,
                 "checked"=>$checked,
                 "expanded"=>$checked,
                 "leaf"=>($item->hasChildren == true ? false : true),
                 "iconCls"=>'category_'.$iconCls,
                 "text"=>$item->name);
                 
                 if ($item->hasChildren == true) {
                     $children = $this->readRecursiveCategory($item->name,$breadCrumb.$item->name,$DefaultValues);     
                     $arrVal["children"] = $children["items"];  
                     if ($children["found"] == true) {
                        $arrVal["expanded"] = true;   
                        $array["found"] = true;
                     }       
                 }

                $array["items"][] = $arrVal;
        }    
    }  
    return $array;            
  }    
}
