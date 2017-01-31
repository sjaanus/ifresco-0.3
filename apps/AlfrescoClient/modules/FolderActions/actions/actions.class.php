<?php

/**
 * FolderActions actions.
 *
 * @package    AlfrescoClient
 * @subpackage FolderActions
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
class FolderActionsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->form = new AlfrescoSpaceCreateForm(array(),array("userEl"=>$this->getUser()));

    sfWidgetFormSchema::setDefaultFormFormatterName('AlfrescoCreateSpace');
    
  }
  
  public function executeCreateSpacePOST(sfWebRequest $request)
  {
    $json = array();
    $json['jsonrpc'] = "2.0";
    $json["success"] = "false";
    $json["message"] = "Something went wrong. Please contact the Administrator!";
    
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    
    $namespaceMap = new NamespaceMap();

    $companyHome = $spacesStore->companyHome;  
    
    $nodeId = $this->getRequestParameter('nodeId'); 

    if (empty($nodeId) || $nodeId=="root")
        $MainNode = $companyHome;
    else 
        $MainNode = $session->getNode($spacesStore, $nodeId);
            
    try {
        $properties = $this->getRequestParameter('properties'); 
        if (is_array($properties)) {
            if (count($properties) > 0) {
                $folderName = $properties["cm_name"];
                if (!empty($folderName)) {
                    $contentNode = $MainNode->createChild("cm_folder", "cm_contains", "cm_".$folderName); 
                    foreach ($properties as $key => $value) {
                        $contentNode->{$key} = $value;  
                    }              
                    $session->save();
                    $json["success"] = "true";
                    $json["message"] = $this->getContext()->getI18N()->__("Successfully created the Space %1%" , array("%1%"=>$folderName), 'messages');
                }
            }
        }
    }
    catch (Exception $e) {
 
    }
    
    
    
    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
    $this->getResponse()->setHttpHeader('Cache-Control','no-store, no-cache, must-revalidate');
    $this->getResponse()->setHttpHeader('Cache-Control','post-check=0, pre-check=0',false);
    $this->getResponse()->setHttpHeader('Pragma','no-cache');

    return $this->renderText(json_encode($json));
  }
}
