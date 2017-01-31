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
class myUser extends sfBasicSecurityUser
{
    private function getAlfrescoAuthentication() {

    } 
    
    public function getRepository() {
        //$repositoryUrl = "http://192.168.254.128:8080/alfresco/api";  
        $repositoryUrl = AlfrescoConfiguration::getInstance()->RepositoryUrl;

        $repository = new Repository($repositoryUrl);
        
        $this->loadNamespaceMap();
        return $repository;
    }
    
    public function getPassword() {
        $Repository = $this->getRepository();
        if ($Repository != null) {
            return $Repository->getPassword();    
        }
        return null;
    }
    
    public function getSession() {
        $session = $this->getRepository()->createSession($this->getTicket());  
        $session->setLanguage($this->getCulture());
        $this->loadNamespaceMap();
        return $session;
    }
    
    public function getUsername() {
        if ($this->hasAttribute('AlfrescoUsername'))
            return $this->getAttribute('AlfrescoUsername');           
        return null;                                       
    }
    
    public function getUserDetails() {
        $session = $this->getSession();
        $spacesStore = new SpacesStore($session);
        $RestPerson = new RESTPerson($this->getRepository(),$spacesStore,$session);
        $Person = $RestPerson->GetPerson($this->getUsername());
        return $Person;
    }
    
    public function isAdmin() {
        $Person = $this->getUserDetails();
        return $Person->capabilities->isAdmin;
    }

    
    public function getTicket() {
        if ($this->hasAttribute('AlfrescoTicket'))
            return $this->getAttribute('AlfrescoTicket');           
        return null;                                       
        //return $this->getAlfrescoAuthentication()->getTicket();
    }
    
    public function loadNamespaceMap() {
        $q = Doctrine_Query::create()
        ->from('Namespacemapping n');
        $Namespacemapping = $q->execute();

        if (count($Namespacemapping) > 0) {
            $NamespaceMap = NamespaceMap::getInstance();
            foreach ($Namespacemapping as $Namespace) {
                $nsp = $Namespace->getNamespace();
                $prefix = $Namespace->getPrefix();
                $NamespaceMap->addNamespaceMap($prefix,$nsp);   
            }
        }
      
        
        
    }
    
    public function getDateFormat() {
        return $this->GetSetting("DateFormat","m/d/Y");
    }
    
    public function getTimeFormat() {
        return $this->GetSetting("TimeFormat","H:i");
    }
    
    public function GetSetting($settingKey,$default="") {
        $Setting = Doctrine_Query::create()
            ->from('Settings s')
            ->where('s.keystring = ?',$settingKey)
            ->fetchOne();
        
        if ($Setting != null) {    
            $ValueString = $Setting->getValuestring();   
            return $ValueString;
        }
        return $default;
    }
}
