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
class Renderer {
    private static $instance = NULL;


    private function __construct() {
        if (empty($this->dirToScan)) {
            $this->dirToScan = sfConfig::get('sf_app_lib_dir').DIRECTORY_SEPARATOR.'ViewRenderer'; 
        }
    }

    public static function getInstance() {
       if (self::$instance === NULL) {
           self::$instance = new self;
       }
       return self::$instance;
    }
    
    private function __clone() {}
    
    private $mimeTypeRenderer = array();
    private $mimeTypeRendererList = array();
    public $defaultRenderClass = "DefaultRenderer";
    public $dirToScan = "";
    
    
    public function addMimetypeRenderer($mimetype,$RenderClass) {
        if (!isset($this->mimeTypeRenderer[$mimetype]))
            $this->mimeTypeRenderer[$mimetype] = $RenderClass;
    }
    
    public function getMimetypeRenderer($mimetype) {
         if (isset($this->mimeTypeRenderer[$mimetype]))
            return $this->mimeTypeRenderer[$mimetype];
         else {
             if (isset($this->mimeTypeRenderer["default"])) {

                return $this->mimeTypeRenderer["default"];        
             }
             else {
                 throw new Exception("No View Renderer avaible!");
             }    
         }
    }
    
    public function listRenderers() {
        return $this->mimeTypeRendererList;
    }
    
    private function restrictedRenderers() {
        $Setting = Doctrine_Query::create()
            ->from('Settings s')
            ->where('s.keystring = ?',"Renderer")
            ->fetchOne();        
        
        if ($Setting != null) {
            $this->restricted = json_decode($Setting->getValuestring());    
        }
    }
    
    private $restricted = array();
    
    public function scanRenderers() {
        $this->restrictedRenderers();
        if ($handle = opendir($this->dirToScan)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && !empty($file)) {
                    if (preg_match("/(.*?)\..*/eis",$file,$fileMatch)) {    
                        $className = $fileMatch[1];  
                        if (empty($className))
                        	continue;
                        
                        $helper = new $className();       
                        if ($helper instanceof ViewRenderer) {
                            $mimetypes = $helper->getMimetypes();
                            
                            $HelperStdClass = new stdClass();
                            
                            if (is_array($mimetypes)) {
                                if (!in_array($className,$this->restricted)) {
                                    for ($i = 0; $i < count($mimetypes); $i++) {          
                                        $this->addMimetypeRenderer($mimetypes[$i],$helper);
                                    }
                                }
                                //$this->mimeTypeRendererList[$className] = join(",",$mimetypes);  
                                $HelperStdClass->MimeTypes = join(",",$mimetypes);    
                            }
                            else {
                                if (!in_array($className,$this->restricted))
                                    $this->addMimetypeRenderer($mimetypes,$helper);  
                                //$this->mimeTypeRendererList[$className] = $mimetypes;
                                $HelperStdClass->MimeTypes = $mimetypes;
                            }
                            $HelperStdClass->Description = $helper->getDescription();
                            $this->mimeTypeRendererList[$className] = $HelperStdClass;
                        }   
                    }
                }
            }
        }
    }
}    
?>