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
class treeActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */

  public function executeIndex(sfWebRequest $request) {
        //$this->getResponse()->addJavascript('jqueryTree/jquery.tree.js');
  }
  
  public function executeGetHTML() { 
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);

        $dirVar = $_POST['id'];
        $dirVar = str_replace("/","",$dirVar);
        if (empty($dirVar))
            $rootNode = $spacesStore->companyHome;
        else
            $rootNode = $session->getNode($spacesStore,$dirVar);  

        $content = ""; 
        $array = array(); 
  
        if ($rootNode != null) {
            if (count($rootNode->children) > 0) {
                $content = "<ul class=\"jqueryFileTree\" style=\"display: block;\">"; 
                foreach ($rootNode->children as $child) {
                    
                    $Node = $session->getNode($spacesStore, $child->child->id);      
                    if ($Node->type == "{http://www.alfresco.org/model/content/1.0}folder") {    
                        $content .= '<li id="'. $Node->getId()  .'" rel="folder" class="closed"><a href="#"><ins>&nbsp;</ins>'. $Node->cm_name .'</a></li>';  
                    }
                    else {

                        // NO FILES IN TREE
                        //echo '<li id="'. $Node->getId()  .'" rel="file"><a href="#"><ins>&nbsp;</ins>'. $Node->cm_name .'</a></li>';       
                    }  
                    
                }
                $content .= "</ul>";           
            }           
        }
       
        
        return $this->renderText($content);    
  }
  
  public function executeGetJSON() { 
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();
        $spacesStore = new SpacesStore($session);

        $dirVar = $_POST['node'];
        $dirVar = str_replace("/","",$dirVar);
        if (empty($dirVar) || $dirVar == "root")
            $rootNode = $spacesStore->companyHome;
        else
            $rootNode = $session->getNode($spacesStore,$dirVar);  

        $content = ""; 
        $array = array(); 
        
        if ($rootNode != null) {
            if (count($rootNode->children) > 0) {
                foreach ($rootNode->children as $child) {
                    
                    $Node = $session->getNode($spacesStore, $child->child->id);      
                    if ($Node->type == "{http://www.alfresco.org/model/content/1.0}folder" || $Node->type == "{http://www.alfresco.org/model/site/1.0}sites" || $Node->type == "{http://www.alfresco.org/model/site/1.0}site") {
                        $imageText = '<img src="'.$Node->getIconUrl().'" border="0" align="absmiddle"> <b>'.$Node->cm_name.'</b>';

                        $arrVal = array("cls"=>"folder",
                         "id"=>$Node->getId(),
                         "leaf"=>false,
                         "imagetext"=>$imageText,
                         "text"=>$Node->cm_name,
                         "qtip"=>$Node->cm_title);
                         
                         if ($Node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
                            $arrVal["iconCls"] = "sites-icon";
                         }
                         
                        $array[] = $arrVal;
                    }
                    else {
                        // NO FILES IN TREE
                    }  
                    
                }
                     
            }
        }
        

        $this->sort_by_old("text",$array,SORT_ASC);

        return $this->renderText(json_encode($array));    
  } 
  
  private function sort_by_old($field, &$arr, $sorting=SORT_ASC, $case_insensitive=true){

        if(is_array($arr) && (count($arr)>0)){

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
  
  public function executeGetCheckTree(sfWebRequest $request) {
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();
        $spacesStore = new SpacesStore($session);

        $dirVar = $_POST['node'];
        $dirVar = str_replace("/","",$dirVar);
        if (empty($dirVar) || $dirVar == "root")
            $rootNode = $spacesStore->companyHome;
        else
            $rootNode = $session->getNode($spacesStore,$dirVar);  

        $content = ""; 
        $array = array(); 
        
        if ($rootNode != null) {
            if (count($rootNode->children) > 0) {
                foreach ($rootNode->children as $child) {
                    
                    $Node = $session->getNode($spacesStore, $child->child->id);      
                    if ($Node->type == "{http://www.alfresco.org/model/content/1.0}folder" || $Node->type == "{http://www.alfresco.org/model/site/1.0}sites" || $Node->type == "{http://www.alfresco.org/model/site/1.0}site") {  
                        $arrVal = array("cls"=>"folder",
                         "id"=>$Node->getId(),
                         "checked"=>false,
                         "leaf"=>false,
                         "text"=>$Node->cm_name);
                         
                         if ($Node->type == "{http://www.alfresco.org/model/site/1.0}sites") {
                            $arrVal["iconCls"] = "sites-icon";
                         }
                         
                        $array[] = $arrVal;
                         
                         
                    }
                    else {
                        // NO FILES IN TREE    
                    }  
                    
                }
                     
            }
        }          

        $this->sort_by_old("text",$array,SORT_ASC);
        
        return $this->renderText(json_encode($array));    
  }       
}
