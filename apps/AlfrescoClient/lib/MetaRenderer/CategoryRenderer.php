<?php
 /**
 * @package    AlfrescoClient
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
class CategoryRenderer implements InterfaceMetaRenderer {
    private $PropertyNames = "cm:categories";        
    
    public function getPropertyNames() {
        return $this->PropertyNames;
    }
    
    public function render($FieldValue) { 
        $session = MetaRenderer::$session;
        $spacesStore = MetaRenderer::$spacesStore;

        if (!empty($FieldValue)) {        
            if (!is_array($FieldValue)) {
                $Categories = explode(",",$FieldValue);
                if (count($Categories) == 0)
                    $Categories = array($FieldValue);
            }
            else
                $Categories = $FieldValue;     
            
            $CategoriesValues = array();
            if (count($Categories) > 0) {
                foreach ($Categories as $Key=>$CatNodeRef) {
                    $CatUUId = str_replace("workspace://SpacesStore/","",$CatNodeRef);
                    
                    $CatNode = $session->getNode($spacesStore, $CatUUId);       
                    
                    if ($CatNode != null) {
                        $name = $CatNode->cm_name;
                        $name = str_replace("'","\'",$name);
                        //$CategoriesValues[$CatNode->getId()] = $CatNode->cm_name;
                        $CategoriesValues[] = '<a href="javascript:openCategory(\''.$CatNode->getId().'\',\''.$name.'\')">'.$CatNode->cm_name.'</a>';                               
                    }  
                }     
                //$FieldValue = join(", ",$CategoriesValues);                  
                $FieldValue = join(", ",$CategoriesValues);                  
            }
        } 

        return $FieldValue;   
    }
}    
?>