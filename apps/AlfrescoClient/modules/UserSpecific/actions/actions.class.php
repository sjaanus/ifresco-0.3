<?php

/**
 * UserSpecific actions.
 *
 * @package    AlfrescoClient
 * @subpackage UserSpecific
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
class UserSpecificActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {

  }

  public function executeAddFavorite(sfWebRequest $request) {
    $returnArr = array("success"=>"false");

    $nodeId = $request->getParameter('nodeId');
  
    $nodeText = $request->getParameter('nodeText');
    $nodeType = $request->getParameter('nodeType');
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();
    $userName = $user->getUsername();

    $spacesStore = new SpacesStore($session);              
    
    $NodeFound = true;
    if ($nodeType != "category") {
        if ($nodeId == "root")
            $NodeFound = false;
        else {
            $Node = $session->getNode($spacesStore, $nodeId);     
            if ($Node == null) {
                $NodeFound = false;
            }
        }
    } 
    
    if ($NodeFound==true ) {
        try {

            $Alfrescofavorites = Doctrine_Query::create()
                        ->from('Alfrescofavorites a')
                        ->where("a.userkey=?",$userName)
                        ->andWhere("a.nodeid=?",$nodeId)       
                        ->fetchOne();
     
            if ($Alfrescofavorites == null) {

                $Alfrescofavorites = new Alfrescofavorites();
                $Alfrescofavorites->setNodename($nodeText); 
                $Alfrescofavorites->setNodeid($nodeId);               
                $Alfrescofavorites->setNodetype($nodeType);               
                $Alfrescofavorites->setUserkey($userName); 
                $Alfrescofavorites->save();
                $returnArr["success"] = true;                 
            }
        }
        catch (Exception $e) {
            $returnArr["errorMsg"] = $e->getMessage();                 
            $returnArr["success"] = false;      
            FB::log($e->getMessage());           
        }
    }
    

    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');


    $return = json_encode($returnArr);
    return $this->renderText($return); 
  }
  
  public function executeRemoveFavorite(sfWebRequest $request) {
    $returnArr = array("success"=>"false");

    $nodeId = $request->getParameter('nodeId');
    $favId = $request->getParameter('favId');
    
    $user = $this->getUser();               
    $userName = $user->getUsername();

    try {
        if (isset($favId) && !empty($favId)) {
            $this->removeByFavId($favId);
        }
        else {
            $this->removeByNodeId($nodeId);
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
  
  private function removeByNodeId($nodeid) {
      $user = $this->getUser();               
      $userName = $user->getUsername();
      $Alfrescofavorites = Doctrine_Query::create()
                            ->delete('Alfrescofavorites a')
                            ->where("a.userkey=?",$userName)
                            ->andWhere("a.nodeid=?",$nodeId)       
                            ->execute();      
  }
  
  private function removeByFavId($favId) {
      $user = $this->getUser();               
      $userName = $user->getUsername();
      $Alfrescofavorites = Doctrine_Query::create()
                            ->delete('Alfrescofavorites a')
                            ->where("a.userkey=?",$userName)
                            ->andWhere("a.id=?",$favId)       
                            ->execute();        
  }
  
  public function executeGetFavorites(sfWebRequest $request)
  {
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();
    $userName = $user->getUsername();

    $spacesStore = new SpacesStore($session);
    
    $companyHome = $spacesStore->companyHome;  
    $restPref = new RESTPreferences($repository,$spacesStore,$session);

    $favorites = $restPref->GetUserPreferences($userName);
    
    $dirVar = $_POST['node'];
    $dirVar = str_replace("/","",$dirVar);
    if (empty($dirVar) || $dirVar == "root") {

        $UserDatabaseFavorites = Doctrine_Query::create()
                                 ->from('Alfrescofavorites a')
                                 ->where("a.userkey=?",$userName) 
                                 ->execute();
                                 
        $myDocuments = array();    
        if (count($UserDatabaseFavorites) > 0) {
            foreach ($UserDatabaseFavorites as $DatabaseEntry) {              
                $nodeId = $DatabaseEntry->getNodeId();
                $Type = $DatabaseEntry->getNodeType();             
                $favId = $DatabaseEntry->getId();      
                try {       
                    if ($Type != "category") {    
                        $Node = $session->getNode($spacesStore, $nodeId);  
                        
                        if ($Node != null) {        
                            if ($Type == "file") {
                                $cls = "file";  
                                $leaf = true;
                                $icon = $Node->getIconUrl();     
                            } 
                            else { 
                                $cls = "folder"; 
                                $leaf = false;
                                $icon = "";   
                            }
                                     

                            $myDocuments[] = array("cls"=>$cls,
                                             "id"=>$nodeId,
                                             "icon"=>$icon,
                                             "leaf"=>$leaf,
                                             "text"=>$Node->cm_name,
                                             "type"=>$cls,
                                             "favId"=>$favId,
                                             "workId"=>$nodeId,
                                             "imageName"=>'<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name);          
                        }                                                                                                                       
                    }
                    else {
                        $category = $nodeId;
                        if (!preg_match("/[a-zA-Z0-9]+\-[a-zA-Z0-9]+\-[a-zA-Z0-9]+\-[a-zA-Z0-9]+\-[a-zA-Z0-9]+/eis",$category)) {                                     
                        
                            $categoryOrig = preg_replace("#.*/(.*)#is","$1",$category);
                            $categoryOrig = trim($categoryOrig);
                            $category = explode("/",$category);
                            $categoryStr = "";
                            $breadCrumb = "";
                            foreach ($category as $cat) {
                                if (empty($cat) || $cat == $categoryOrig)
                                    continue;
                                
                                $breadCrumb .= "{$cat}/";   
                                $cat = preg_replace("#.*/(.*?)#is","$1",$cat);
                                $cat = str_replace(" ","_x0020_",$cat);
                                $cat = str_replace(",","_x002c_",$cat);
                                $cat = str_replace("%20","_x0020_",$cat);   
                                $categoryStr .= "/cm:{$cat}";  
                                
                            } 
                            $Categories = $session->query($spacesStore, "+TYPE:\"cm:category\" +@cm\:name:\"$categoryOrig\" +PATH:\"/cm:generalclassifiable{$categoryStr}/*\"");
                        }
                        else {
                            $Categories = $session->getNode($spacesStore, $nodeId);
                        } 
                        
                        if ($Categories == null) {
                            $this->removeByFavId($favId);
                            continue;
                        }

                        if (count($Categories) > 1) {
                           foreach ($Categories as $catNode) {
                                try {
                                    if ($catNode != null) {
                                       $myDocuments[] = array("cls"=>"category",
                                         "id"=>rand(0,100)."_".str_replace(" ","%20",$breadCrumb.$catNode->cm_name),
                                         "nodeId"=>str_replace("workspace://SpacesStore/","",$catNode->getId()),
                                         "leaf"=>false,
                                         "text"=>$catNode->cm_name,
                                         "type"=>"category",
                                         "iconCls"=>'category_tag_green',  
                                         "favId"=>$favId,    
                                         "workId"=>$catNode->cm_name,         
                                         "imageName"=>'<img src="/images/icons/tag.png" border="0" align="absmiddle"> '.$catNode->cm_name);
                                    }
                                }
                                catch (SoapFault $e) {
                                }
                           }
                        }
                        else {
                            $catNode = $Categories;
                            $myDocuments[] = array("cls"=>"category",
                             "id"=>rand(0,100)."_".str_replace(" ","%20",$breadCrumb.$catNode->cm_name),
                             "nodeId"=>str_replace("workspace://SpacesStore/","",$catNode->getId()),
                             "leaf"=>false,
                             "text"=>$catNode->cm_name,
                             "nodeId"=>str_replace("workspace://SpacesStore/","",$catNode->getId()),
                             "type"=>"category",
                             "iconCls"=>'category_tag_green',  
                             "favId"=>$favId,    
                             "workId"=>$catNode->getId(),         
                             "imageName"=>'<img src="/images/icons/tag.png" border="0" align="absmiddle"> '.$catNode->cm_name);
                        }
             
                        //print_R($categories);

                        //if (preg_match("/[a-zA-Z0-9]+\-[a-zA-Z0-9\-]+/is",$nodeId)) {      
                        //}              
                    }
                }
                catch (SoapFault $e) {
                    $this->removeByFavId($favId);
                }  
            }
        }
        

        if (count($favorites) > 0) {
            $documents = $favorites->org->alfresco->share->documents->favourites;
            
            if (!empty($documents)) {
                if (preg_match("/,/eis",$documents)) {
                    $explode = explode(",",$documents);
                    for ($i = 0; $i < count($explode); $i++) {
                        $id = $explode[$i];
                        $nodeId = str_replace("workspace://SpacesStore/","",$id);

                        $Node = $session->getNode($spacesStore, $nodeId);
                        if ($Node != null) {
                            $myDocuments[] = array("cls"=>"file",
                                     "id"=>$nodeId,
                                     "icon"=>$Node->getIconUrl(),
                                     "leaf"=>true,
                                     "text"=>$Node->cm_name,
                                     "type"=>"file",   
                                     "imageName"=>'<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name);
                        }
                    }
                }
                else {
                    $id = $documents;
                    $nodeId = str_replace("workspace://SpacesStore/","",$id);
                    $Node = $session->getNode($spacesStore, $nodeId);
                    if ($Node != null) {
                        $myDocuments[] = array("cls"=>"folder",
                                 "id"=>$nodeId,
                                 "icon"=>$Node->getIconUrl(),
                                 "leaf"=>true,
                                 "text"=>$Node->cm_name,
                                 "type"=>"folder",   
                                 "imageName"=>'<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name);
                    }
                }
            }
        }
    }
    else {           
        if (preg_match("/[a-zA-Z0-9]+\-[a-zA-Z0-9\-]+/is",$dirVar)) {                                                 
            $rootNode = $session->getNode($spacesStore,$dirVar);  
            if ($rootNode != null) {
                if (count($rootNode->children) > 0) {
                    foreach ($rootNode->children as $child) {
                        
                        $Node = $session->getNode($spacesStore, $child->child->id);      
                        if ($Node != null) {
                            if ($Node->type == "{http://www.alfresco.org/model/content/1.0}folder" || $Node->type == "{http://www.alfresco.org/model/site/1.0}sites" || $Node->type == "{http://www.alfresco.org/model/site/1.0}site") {    

                                $arrVal = array("cls"=>"folder",
                                 "id"=>$Node->getId(),
                                 "leaf"=>false,
                                 "text"=>$Node->cm_name,
                                 "type"=>"folder",
                                 "imageName"=>'<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> '.$Node->cm_name);
                                 
                                 if ($Node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
                                    $arrVal["iconCls"] = "sites-icon";
                                 }
                                 
                                $myDocuments[] = $arrVal;
                            }   
                        }                
                    }
                         
                }
            }
        }
        else {
            
            $categoryName = $_POST['node'];  
            $categoryName = preg_replace("/[0-9]+_(.*)/","$1",$categoryName);                 
            $breadCrumb = urldecode($categoryName)."/";   
            
            $restCategories = new RESTCategories($repository,$spacesStore,$session); 
            $categories = $restCategories->GetCategories($categoryName);

            $iconClasses = array("tag_green","tag_orange","tag_pink","tag_purple","tag_yellow");
            if (count($categories->items) > 0) {
                $match = array();
                $count = preg_match_all("#/#eis",$breadCrumb,$match);
                if ($count > 4)
                    $iconCls = "tag_red";
                else
                    $iconCls = $iconClasses[$count];  
                foreach ($categories->items as $item) {
                    if ($item != null) {
                        $myDocuments[] = array("cls"=>"folder",
                         "id"=>rand(0,100)."_".str_replace(" ","%20",$breadCrumb.$item->name),
                         "nodeId"=>str_replace("workspace://SpacesStore/","",$item->nodeRef),
                         "leaf"=>($item->hasChildren == true ? false : true),
                         "iconCls"=>'category_'.$iconCls,
                         "text"=>$item->name,
                         "type"=>"category",
                         "imageName"=>'<img src="/images/icons/tag.png" border="0" align="absmiddle"> '.$item->name);
                    }
                }    
            }    
        }
    }
    return $this->renderText(json_encode($myDocuments));    

  }  
}
?>