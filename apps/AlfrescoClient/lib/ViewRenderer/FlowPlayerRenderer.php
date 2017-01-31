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
class FlowPlayerRenderer implements ViewRenderer {
    private $Description = "FLV/MP4 FlowPlayer (www.flowplayer.org)";
    public function getDescription() {
        return $this->Description;         
    }
    
    private $MimeTypes = array("video/mp4",
                               "video/x-flv");        
    
    public function getMimetypes() {
        return $this->MimeTypes;
    }
    
    public function render($Node,$userObj) {  
        $nodeId = $Node->getId();
        $height = $_GET["height"];
        $ContentData = $Node->cm_content;
        if ($ContentData != null) {
            return $this->renderView($height,$Node->getId(),$ContentData->getUrl());
        }
        return "";
    }
    
    private function renderView($height,$nodeId,$file) {
        $PlayerID = str_replace("-","",$nodeId);
        $flowPlayerId = "flowPlayer{$PlayerID}";        
        $height = (!empty($height) ? $height : '300px'); 
       
        $html = '<script src="/js/flowplayer/flowplayer-3.2.4.min.js"></script>
        <a
            href="'.$file.'"
            style="display:block;width:100%;height:'.$height.'"
            id="'.$flowPlayerId.'">
        </a>
        <script language="JavaScript">
        flowplayer("'.$flowPlayerId.'", "/js/flowplayer/flowplayer-3.2.5.swf",{clip:{scaling:\'fit\',autoPlay: false,autoBuffering: true}});
        </script>'; 
        return $html;      
    }
}    
?>