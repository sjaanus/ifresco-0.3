<?php
ob_start();
/**
 * Viewer actions.
 *
 * @package    AlfrescoClient
 * @subpackage Viewer
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
 
class ViewerActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $nodeId = $this->getRequestParameter('nodeId');
    $this->height = $this->getRequestParameter('height');

    sfConfig::set('sf_web_cache_dir', sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'cache');

    $user = $this->getUser();           
    $repository = $user->getRepository();  
    $session = $user->getSession();
    $ticket = $user->getTicket();

    // Create a reference to the 'SpacesStore'
    try {
        $store = new SpacesStore($session);
        if (preg_match("#workspace://version2store/(.*)#eis",$nodeId,$match)) {
            $store = new VersionStore($session);
            $nodeId = $match[1];
        }

        $Node = $session->getNode($store, $nodeId);
        $nodeRef = Node::__toNodeRef($store,$Node->getId());
        
        $response = $this->getResponse();
        $Renderer = Renderer::getInstance(); 
        $Renderer->scanRenderers();
        
        
        $mimetype = "default";
        $ContentData = $Node->cm_content;
        
        if ($ContentData != null) {
            $mimetype = $ContentData->getMimetype();           
        }
        
        $RenderObj = $Renderer->getMimetypeRenderer($mimetype);

        echo $RenderObj->render($Node,$user);

        /*

        //echo $folderPath."<br>";
        //$results = $this->session->query($this->spacesStore, 'PARENT:"'.$nodeRef.'" +@cm\:name:"'.$spaceName.'"');  
        //$SearchNode = $results[0];
        //print_r($Node);
        //$Node = $this->session->getNode($spacesStore, $nodeId);
        
        //$file_cache_dir = sfConfig::get('sf_cache_dir') . '/Viewer';
        //$cache = $this->getCacheObject($file_cache_dir);
        
        $cache = $this->getCacheObject();
        $cacheSWF_exists = $cache->has($nodeId);
        $lifetime = 86400;

        if (!$cacheSWF_exists) {
            $this->swfFile = "";
            $viewer = Doctrine_Query::create()
                          ->from('ViewerRelations v')
                          ->where('v.nodeid = ?', $nodeId)
                          ->fetchOne();


            if ($viewer != null) {
                ////$this->swfFile = $viewer->getViewerurl();
                //$ViewerNode = $session->getNode($spacesStore, $viewer->getViewernode());
                //if ($ViewerNode != null) {
                //    $content = $ViewerNode->cm_content;
                    ////$this->swfFile = $content->getUrl();
                //}
                
                $fileContent = $viewer->getViewercontent();
                $this->setToCache($nodeId,$fileContent);
            }
            else {
                $this->generateViewObj($nodeId);  
            }
            
            
        }
        
        //$value = $cache->get($nodeId);
        //$file = $this->getFilePath($nodeId);
        //$file = sfConfig::get('sf_cache_dir') . '/Viewer/'.$nodeId;
        $file = "/cache/Viewer/".$nodeId.".swf";
        $this->swfFile = $file;
        
        
        //$this->viewerrelations = Doctrine::getTable('ViewerRelations')
        //                         ->find($nodeId);
                                 
        //$this->viewer = Doctrine::getTable('ViewerRelations')->find($nodeId);
        




        //$this->swfFile = ViewerActions::convertToSWF();  

        //ViewerActions::convertToSWF($nodeId);       */
    }
    catch (Exception $e) {
        
    }
    $this->setLayout(false);
  }

}
