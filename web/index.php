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
require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

if (!file_exists("../config/databases.yml") && file_exists("install") || !file_exists("../config/alfresco.xml") && file_exists("install")) {
    include("install/index.php");
    $Installer = new Installer();
    
    $Config = new ProjectConfiguration();

    if (isset($_POST["testConnection"]))
        $Installer->executeTestDatabase();
    else if (isset($_GET["startInstall"]) || isset($_POST["startInstall"]))
        $Installer->executeStartInstall();
    else
        $Installer->executeInstaller();
        
    
}
else {
    $configuration = ProjectConfiguration::getApplicationConfiguration('AlfrescoClient', 'dev', true);
    sfContext::createInstance($configuration)->dispatch();
}
