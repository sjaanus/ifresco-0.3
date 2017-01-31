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
class AlfrescoConfigArrayObj {
    private $items = array();
    
    public function __construct($array) {
        $this->items = $array;
    }
    
    public function __set($name, $val) {
        $this->items[$name] = $val;
    }

    public function __get($name) {
        return $this->items[$name];
    }   
    
    public function __toArray() {
        return $this->items;
    }
}

class AlfrescoConfiguration {
    private static $instance = null;             
    
    private $configFile = '';
    
    public $items = array();
    

    private function __construct($configFile="") {    
        if (!empty($configFile))
            $this->configFile = sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.$configFile;
        else
            $this->configFile = sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'alfresco.xml';         
        $this->parse();
    }
 
   // Diese statische Methode gibt die Instanz zurueck.
    public static function getInstance($configFile="") {
      
       if (self::$instance === NULL) {
           self::$instance = new self($configFile);
       }
       return self::$instance;
    }
    
    private function __clone() {}

    public function __get($id) { 
        $item = $this->items[$id];
        if (is_array($item)) {
            return new AlfrescoConfigArrayObj($item);
        }
        else
            return $this->items[$id];   
    }

    
    private function isParameter($pn){
        return array_key_exists($pn, $this->items);
    }
    
    public function __set($name, $value) { 
        $this->items[$name] = $value; 
    }

    public function parse() {
        $doc = new DOMDocument();
        $doc->load($this->configFile);
        

        $cn = $doc->getElementsByTagName("config");

        $nodes = $cn->item(0)->getElementsByTagName("*");
        $nodes = $cn->item(0)->childNodes;
        
        foreach($nodes as $node) {
            if ($node->nodeName == "#text")
                continue;
                
            $found = false;
            if ($node->hasChildNodes()) {             
                $this->items[$node->nodeName] = array();
                foreach ($node->childNodes as $childNode) {  
                    if ($childNode->nodeName != "#text") {
                        $found = true;
                        $this->items[$node->nodeName][$childNode->nodeName] = $childNode->nodeValue;  
                    }   
                }  
            }
            
            if ($found == false) {
                $this->items[$node->nodeName] = $node->nodeValue;          
            }
        }
        
    }
    
    function save() {
        $doc = new DOMDocument();
        $doc->formatOutput = true;

        $r = $doc->createElement("config");
        $doc->appendChild($r);

        foreach($this->items as $k => $v) {
          $kn = $doc->createElement($k);
          $kn->appendChild($doc->createTextNode($v) );
          $r->appendChild($kn);
        }

        copy($this->configFile, $this->configFile.'.bak');

        $doc->save($this->configFile);
    }

}
?>