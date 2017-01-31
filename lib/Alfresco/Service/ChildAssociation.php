<?php
/*
 * Copyright (C) 2005 Alfresco, Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

 * As a special exception to the terms and conditions of version 2.0 of 
 * the GPL, you may redistribute this Program in connection with Free/Libre 
 * and Open Source Software ("FLOSS") applications as described in Alfresco's 
 * FLOSS exception.  You should have recieved a copy of the text describing 
 * the FLOSS exception, and it is also available here: 
 * http://www.alfresco.com/legal/licensing"
 */

class ChildAssociation extends BaseObject
{
	private $_parent;
	private $_child;
	private $_type;
	private $_name;
	private $_isPrimary;
	private $_nthSibling;
    private $_session;
    private $_properties;
	
	public function __construct($parent, $child, $type, $name, $isPrimary=false, $nthSibling=0, $properties=array())
	{
		$this->_parent = $parent;
        if ($this->_parent instanceof Node)
            $this->_session = $parent->getSession();
            
		$this->_child = $child;
		$this->_type = $type;
		$this->_name = $name;
		$this->_isPrimary = $isPrimary;
		$this->_nthSibling = $nthSibling;
        $this->_properties = $properties;
	}
	
	public function getParent()
	{
		return $this->_parent;
	}
	
	public function getChild()
	{
		return $this->_child;
	}
	
	public function getType()
	{
		return $this->_type;
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function getIsPrimary()
	{
		return $this->_isPrimary;
	}
    
    public function __get($name)
    {
        $fullName = $this->_session->namespaceMap->getFullName($name);

        if ($fullName != $name)
        { 
            if (array_key_exists($fullName, $this->_properties) == true)
            {
                return $this->_properties[$fullName];
            }    
            else
            {    
                return null;    
            }     
        }    
        else
        {
            return parent::__get($name);
        }
    }
	
	public function getNthSibling()
	{
		return $this->_nthSibling;
	}
}
?>
