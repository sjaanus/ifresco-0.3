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
class MetaRenderer {
    private static $instance = NULL;
    public static $userObject = NULL;
    public static $session = NULL;
    public static $repository = NULL;
    public static $ticket = NULL;
    public static $spacesStore = NULL;

    private function __construct() {
        if (empty($this->dirToScan)) {
            $this->dirToScan = sfConfig::get('sf_app_lib_dir').DIRECTORY_SEPARATOR.'MetaRenderer'; 
        }
    }

    public static function getInstance($userObject) {
       if (self::$instance === NULL) {
           self::$instance = new self;
           self::$userObject = $userObject;
           self::$session = $userObject->getSession();
           self::$repository = $userObject->getRepository();                 
           self::$ticket = $userObject->getTicket();                             
           self::$spacesStore = new SpacesStore(self::$session);             
       }
       return self::$instance;
    }
    
    public static function getUserObject() {
        if (self::$userObject != NULL) {
            return self::$userObject;
        }
        return null;
    }
    
    private function __clone() {}
    
    private $propRenderer = array();
    public $dirToScan = "";
    
    public function addPropertyRenderer($PropName,$RenderClass) {
        if (!isset($this->propRenderer[$PropName]))
            $this->propRenderer[$PropName] = $RenderClass;
    }
    
    public function getPropertyRenderer($PropName) {
         if (isset($this->propRenderer[$PropName]))
            return $this->propRenderer[$PropName];
         else
            return null;
    }
    
    public function getAssocRenderer($Type) {
         if (isset($this->propRenderer["type=".$Type]))
            return $this->propRenderer["type=".$Type];
         else if (isset($this->propRenderer["type=*"]))
            return $this->propRenderer["type=*"];
         else
            return null;
    }
    
    public function getDataRenderer($Type) {

         if (isset($this->propRenderer["datatype=".$Type]))
            return $this->propRenderer["datatype=".$Type];
         else
            return null;
    }
    
    public function scanRenderers() {
        if ($handle = opendir($this->dirToScan)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && !empty($file)) {
                    if (preg_match("/(.*?)\..*/eis",$file,$fileMatch)) {    
                        $className = $fileMatch[1];  
                        if (empty($className))
                        	continue;
                        	
                        $helper = new $className();       
                        if ($helper instanceof InterfaceMetaRenderer) {
                            $propertyNames = $helper->getPropertyNames();
                            if (is_array($propertyNames)) {
                                for ($i = 0; $i < count($propertyNames); $i++) {
                                    $this->addPropertyRenderer($propertyNames[$i],$helper);
                                }
                            }
                            else {
                                $this->addPropertyRenderer($propertyNames,$helper);          
                            }

                        }   
                    }
                }
            }
        }
    }
}    
?>