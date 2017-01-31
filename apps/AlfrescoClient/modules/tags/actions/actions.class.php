<?php

/**
 * tags actions.
 *
 * @package    AlfrescoClient
 * @subpackage tags
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
class tagsActions extends sfActions
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
  
  public function executeAutocompleteTagData(sfWebRequest $request){
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $term = $request->getParameter('q');

    $spacesStore = new SpacesStore($session);
    
    $RestTags = new RESTTags($repository,$store,$session);
    $Tags = $RestTags->GetAllTags($term);
    
    if (count($Tags) < 1) {
       $Tags = array(); 
    }

    return $this->renderText(json_encode($Tags));    
  }
  
  public function executeGetTagScope(sfWebRequest $request)
  {
    $user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    $query = "PATH:\"app:company_home//*\" AND ASPECT:\"{http://www.alfresco.org/model/content/1.0}taggable\"";
    $results = $session->query($spacesStore,$query);
    

    $tagCount = array();
    $maxCount = 0;
    $minCount = 0;
    $array = array("tags"=>array());
    if (count($results) > 0) {
        foreach ($results as $TagNode) {
            $taggable = $TagNode->cm_taggable;
            if (is_array($taggable)) {
                foreach ($taggable as $tag) { 
                    $Tag = $session->getNode($spacesStore,str_replace("workspace://SpacesStore/","",$tag));
                    $tagKey = $Tag->cm_name;
                    $count = $tagCount[$tagKey];
                    $tagCount[$tagKey] = (!empty($count) ? $count+1 : 1);
                }
            }
            else {
                if (!empty($taggable) && $taggable != null) {
                    $Tag = $session->getNode($spacesStore,str_replace("workspace://SpacesStore/","",$taggable));
                    $tagKey = $Tag->cm_name;
                    $count = $tagCount[$tagKey];
                    $tagCount[$tagKey] = (!empty($count) ? $count+1 : 1);    
                }
            }
            
        }
        
        
        foreach ($tagCount as $key => $count) {
            $array["tags"][] = array("name"=>$key,
                                      "count"=>$count);     
            if ($maxCount < $count)
                $maxCount = $count; 
            if ($minCount == 0)
                $minCount = $count;
                
            if ($minCount != 0 && $minCount > $count)
                $minCount = $count;
        }
    }
    $array["maxCount"] = $maxCount;
    $array["minCount"] = $minCount;
    
    return $this->renderText(json_encode($array));    

  }
}
