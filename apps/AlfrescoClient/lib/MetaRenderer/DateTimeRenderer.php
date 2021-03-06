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
class DateTimeRenderer implements InterfaceMetaRenderer {
    private $PropertyNames = "datatype=datetime";        
    
    public function getPropertyNames() {
        return $this->PropertyNames;
    }
    
    public function render($FieldValue) { 
        $FieldValue = trim($FieldValue);
                
        $DateFormat = Registry::getSetting("DateFormat");
        if (empty($DateFormat))
            $DateFormat = "m/d/Y";
            
                
        $TimeFormat = Registry::getSetting("TimeFormat");
        if (empty($TimeFormat))
            $TimeFormat = "H:i";
            
        if (!empty($FieldValue))
            $FieldValue = date($DateFormat." ".$TimeFormat,strtotime($FieldValue));

        return $FieldValue;
    }
}    
?>