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
class PDFRenderer implements ViewRenderer {
    private $Description = "Local PDF Viewer";
    public function getDescription() {
        return $this->Description;         
    }
    
    private $MimeTypes = "application/pdf";        
    
    public function getMimetypes() {
        return $this->MimeTypes;
    }
    
    public function render($Node,$userObj) {  
        $nodeId = $Node->getId();
        $height = $_GET["height"];
        $ContentData = $Node->cm_content;
        if ($ContentData != null) {
            return $this->renderView($height,$ContentData->getUrl());
        }
        return "";
    }
    
    private function renderView($height,$file) {        
        $heightStyle = (!empty($height) ? 'max-height:'.$height.';' : 'max-height:300px'); 
       
        //$html = '<embed src="'.$file.'" width="100%" height="'.$height.'" style="z-index:-100;" class="PDFRenderer">'; 
        $html = '<object data="'.$file.'" type="application/pdf" width="100%" height="'.$height.'" style="z-index:-100;" class="PDFRenderer"><p>It appears you dont have a PDF plugin for this browser. <a href="'.$file.'">click here to download the PDF file.</a></p></object>'; 
        return $html;      
    }
}    
?>