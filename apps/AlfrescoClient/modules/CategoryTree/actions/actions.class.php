<?php

/**
 * tree actions.
 *
 * @package    AlfrescoClient
 * @subpackage tree
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
class CategoryTreeActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */

  public function executeIndex(sfWebRequest $request) {

  }
  
  public function executeAddCategory(sfWebRequest $request) {   
    $nodeId = $this->getRequestParameter('nodeId');
    $value = $this->getRequestParameter('value');
    
    $return = array("success"=>false);
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();


    $spacesStore = new SpacesStore($session);
    $catHome = $spacesStore->getCategoryRoot();
    $Classification = new Classification($repository, $spacesStore, $session);
    try {
        if ($nodeId != "root") {
            $Node = $session->getNode($spacesStore, $nodeId);

            if ($Node != null) {
                $CatNode = $Classification->addCategory($value,$Node);    
                if ($CatNode != false) {
                    $session->save();
                    $return["success"] = true;    
                    $return["nodeId"] = $CatNode->getId();    
                }    
            }   
        }
        else {
            $CatNode = $Classification->addCategory($value);   
            if ($CatNode != false) {
                $session->save();
                $return["success"] = true;    
                $return["nodeId"] = $CatNode->getId();    
            }     
        }
    }
    catch (Exception $e) {
        $return["success"] = false;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');      
    $return = json_encode($return);
    return $this->renderText($return);      
  }  
  
  public function executeRemoveCategory(sfWebRequest $request) {   
    $nodeId = $this->getRequestParameter('nodeId');
    
    $return = array("success"=>false);
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();


    $spacesStore = new SpacesStore($session);
    $Classification = new Classification($repository, $spacesStore, $session);
    try {
        $Node = $session->getNode($spacesStore, $nodeId);
        if ($Node != null) {
            $Classification->removeCategory($Node);    
            $session->save();
            $return["success"] = true;       
        }   
    }
    catch (Exception $e) {
        $return["success"] = false;
    }
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');      
    $return = json_encode($return);
    return $this->renderText($return);      
  }        
  
  public function executeEditCategory(sfWebRequest $request) {
    $nodeId = $this->getRequestParameter('nodeId');
    $value = $this->getRequestParameter('value');
                                                                     
    $return = array("success"=>false);  
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();


    $spacesStore = new SpacesStore($session);

    try {
        $Node = $session->getNode($spacesStore, $nodeId);
        if ($Node != null) {
            $Node->cm_name = $value;
            $session->save();
            $return["success"] = true;
        }
    }
    catch (Exception $e) {
        $return["success"] = false;  
    }

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');      
    $return = json_encode($return);
    return $this->renderText($return);      
  }
  
  public function executeGetJSON() { 
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);         

        $categoryName = $_POST['node'];
        $categoryName = str_replace("%2520","%20",$categoryName);

        if ($categoryName == "root" || empty($categoryName)) {
            $categoryName = "";
            $breadCrumb = "";
        }
        else
            $breadCrumb = urldecode($categoryName)."/";     
        
        //FB::log($categoryName);
        $restCategories = new RESTCategories($repository,$spacesStore,$session);
        $categories = $restCategories->GetCategories($categoryName);
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
                    

                    
                    $arrVal = array("cls"=>"folder",
                     "id"=>str_replace(" ","%20",$breadCrumb.$item->name), 
                     "nodeId"=>str_replace("workspace://SpacesStore/","",$item->nodeRef),
                     "leaf"=>($item->hasChildren == true ? false : true),
                     "iconCls"=>'category_'.$iconCls,
                     "text"=>$item->name,
                     "qtip"=>$item->description);
                     
                     //if ($Node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
                     //   $arrVal["iconCls"] = "sites-icon";
                     //}
                    
                    $array[] = $arrVal;
            }    
        }              

        
        return $this->renderText(json_encode($array));    
  }     
}
