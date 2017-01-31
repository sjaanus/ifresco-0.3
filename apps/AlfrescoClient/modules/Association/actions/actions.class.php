<?php

/**
 * ContentAssociation actions.
 *
 * @package    AlfrescoClient
 * @subpackage ContentAssociation
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
class AssociationActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex()
  {
    return sfView::SUCCESS;
  }
  

  public function executeRelations() {
    $this->nodeId = $this->getRequestParameter('nodeId');
    
    return sfView::SUCCESS;  
    
  }
  
  private function exportAssocationOf($Node,$AssocName) {
      try {
          if ($Node != null) {
            $AssocationsArr = array();
            $AssocName = $this->namespaceMap->getFullName($AssocName);
            $associations = $Node->getAssociations();
            if (count($associations) > 0) {
                foreach ($associations as $assoc) {
                    $typeNode = $assoc->getType(); 
                    if ($typeNode == $AssocName) {
                        $toNode = $assoc->getTo();    
                        if ($toNode != null) {
                            if ($AssocationsArr == null) {
                                $AssocationsArr = array();
                                $AssocationsArr[] = $toNode;
                            }
                            else
                                $AssocationsArr[] = $toNode;           
                        } 
                    }   
                }
            }
            return $AssocationsArr;
          }
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  private $namespaceMap = null;
  
  public function executeRelationsJSON() {
    try {
        $data = array();
        
        $user = $this->getUser();                
        $repository = $user->getRepository();
        $session = $user->getSession();
        $ticket = $user->getTicket();

        $spacesStore = new SpacesStore($session);

        $this->namespaceMap = NamespaceMap::getInstance();
        
        $companyHome = $spacesStore->companyHome;  
        $restDict = new RESTDictionary($repository,$spacesStore,$session);
        
        $Node = $session->getNode($spacesStore, $nodeId);
        
        if ($Node != null) {
            $docType = $this->namespaceMap->getShortName($Node->getType());
            $Associations = $restDict->GetClassAssociations($docType);
            if (count($Associations) > 0) {
                $index = 1;
                foreach ($Associations as $key => $Association) {
                    $AssocName = $Association->name;
                    $AssocName = $this->namespaceMap->getFullName($AssocName,":");
                    $AssocName = $this->namespaceMap->getShortName($AssocName);
                    
                    if (count($Association->target) > 0) {

                        switch ($Association->target->class) {
                            case "cm:person": 
                                $AssocNodes = $this->exportAssocationOf($Node,$AssocName);    
                                if (count($AssocNodes) > 0) {
                                    $children = array();

                                    foreach ($AssocNodes as $keyNode => $AssocNode) {
                                        $PersonName = $AssocNode->cm_firstName." ".$AssocNode->cm_lastName." (".$AssocNode->cm_userName.")";
                                        $children[] = array('name'=>'<img src="/images/icons/user_suit.png" align="absmiddle"> '.$PersonName,
                                                            'nodeId'=>$AssocNode->getId(),
                                                            'iconCls'=>'person-leaf',
                                                            '_parent'=>$index,
                                                            '_level'=>2,
                                                            '_is_leaf'=>true
                                                            );
                                    }
                                    
                                    $data["data"][] = array('name'=>'<img src="/images/icons/folder_user.png" align="absmiddle"> '.$Association->title.' ('.$this->getContext()->getI18N()->__("Person Relations" , null, 'messages').')',
                                                    'nodeId'=>$index,
                                                    'iconCls'=>'person-folder',
                                                    '_parent'=>NULL,
                                                    '_level'=>1,
                                                    '_is_leaf'=>false
                                                    //'expanded'=>true,
                                                    //'children'=>$children
                                                    );
                                    $data["data"] = array_merge($data["data"],$children);
                                }                      
                            break;
                            case "cm:content":
                                $AssocNodes = $this->exportAssocationOf($Node,$AssocName);    
                                
                                if (count($AssocNodes) > 0) {
                                    $children = array();
                                    
                                    foreach ($AssocNodes as $keyNode => $AssocNode) {
                                        $children[] = array('name'=>'<img src="/images/icons/page.png" align="absmiddle"> '.$AssocNode->cm_name,
                                                            'nodeId'=>$AssocNode->getId(),
                                                            'iconCls'=>'document-leaf',
                                                            '_parent'=>$index,
                                                            '_level'=>2,
                                                            '_is_leaf'=>true
                                                            );
                                    }
                                    
                                    $data["data"][] = array('name'=>'<img src="/images/icons/folder_page.png" align="absmiddle"> '.$Association->title.' ('.$this->getContext()->getI18N()->__("Document Relations" , null, 'messages').')',
                                                    'nodeId'=>$index,
                                                    'iconCls'=>'document-folder',
                                                    '_parent'=>NULL,
                                                    '_level'=>1,
                                                    '_is_leaf'=>false
                                                    //'expanded'=>true,
                                                    //'children'=>$children
                                                    );
                                    $data["data"] = array_merge($data["data"],$children);
                                                    
                                }                     
                            break;
                            default:
                            break;
                            
                        }   
                    }
                    $index++;
                }
            }
            
            
        }
        $data["success"] = true;
    }
    catch (Exception $e) {
        $data["success"] = false;    
    }
    
    if (count($data["data"]) == 0) {
        $data["data"][] = array('name'=>'<img src="/images/icons/information.png" align="absmiddle"> '.$this->getContext()->getI18N()->__("This document has no relations." , null, 'messages').'',
                                                    'nodeId'=>'error',
                                                    '_parent'=>NULL,
                                                    '_level'=>1,
                                                    '_is_leaf'=>true
                                                    );              
    }
       
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');

    $data["total"] = count($data["data"]);
    $return = json_encode($data);
    return $this->renderText($return);
  }

  
  public function executeSearch() {
      $this->getResponse()->setHttpHeader('Content-Type','text/html; charset=utf-8');
      $content = "";
      $searchTerm = $_POST['queryString'];

      if(isset($searchTerm))  {
        $array = split(" ",$searchTerm);
        if (array_search($searchTerm,$array) >= 0) {
            $i = array_search($searchTerm,$array);
            $content .= '<li onclick="fill(\''.$i.'\');">'.$array[$i].'</li>';
        }

      }

      return $this->renderText($content);
  }
  
  public function executeAutocomplete()
  {
    return sfView::SUCCESS;
  }

  
  public function executeAutocompleteUserData(sfWebRequest $request){
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $companyHome = $spacesStore->companyHome;    

    $this->forward404If(!$request->isXmlHttpRequest());
    $q = $request->getParameter('q');
    $dataTypeParam = $request->getParameter('dataTypeParam');
    $response = '';

    $results = $session->query($spacesStore, "@cm\:userName:*$q* OR @cm\:email:*$q* OR @cm\:lastName:*$q* OR @cm\:firstName:*$q*"); 
    if ($results != null) {
        for ($i = 0; $i < count($results); $i++) {
            $Node = $results[$i];
            $userName = $Node->cm_userName;
            $firstName = $Node->cm_firstName;
            $lastName = $Node->cm_lastName;
            $email = $Node->cm_email;
                
            $response .= "{$email}/{$Node->getId()}/{$firstName}/{$lastName}/{$userName}" . "\n";
        }
    }
    
    return $this->renderText($response);
  }
  
  public function executeAutocompleteContentData(sfWebRequest $request){

    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);

    $companyHome = $spacesStore->companyHome;    

    $this->forward404If(!$request->isXmlHttpRequest());
    $q = $request->getParameter('q');
    $dataTypeParam = $request->getParameter('dataTypeParam');
    if (empty($dataTypeParam))
        $dataTypeParam = "cm:content";    
    $response = '';

    $results = $session->query($spacesStore, 'TYPE:"'.$dataTypeParam.'" AND (@cm\:name:'.$q.' OR TEXT:'.$q.')');  
    if ($results != null) {
        for ($i = 0; $i < count($results); $i++) {
            $Node = $results[$i];
            $extension = preg_replace("/.*\.(.*)/is","$1",$Node->cm_name);
            if (!file_exists(sfConfig::get('sf_web_dir')."/images/filetypes/16x16/{$extension}.png"))
                $extension = "txt";
                
            $response .= "$extension/{$Node->getId()}/{$Node->cm_name}" . "\n";
        }
    }
    
    return $this->renderText($response);
  }
}
