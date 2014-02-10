<?php
/**
 * PHP LDAP CLASS FOR MANIPULATING ACTIVE DIRECTORY 
 * Version 4.0.3
 * 
 * PHP Version 5 with SSL and LDAP support
 * 
 * Written by Scott Barnett, Richard Hyland
 *   email: scott@wiggumworld.com, adldap@richardhyland.com
 *   http://adldap.sourceforge.net/
 * 
 * Copyright (c) 2006-2011 Scott Barnett, Richard Hyland
 * 
 * We'd appreciate any improvements or additions to be submitted back
 * to benefit the entire community :)
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * @category ToolsAndUtilities
 * @package adLDAP
 * @subpackage Collection
 * @author Scott Barnett, Richard Hyland
 * @copyright (c) 2006-2011 Scott Barnett, Richard Hyland
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPLv2.1
 * @revision $Revision: 97 $
 * @version 4.0.3
 * @link http://adldap.sourceforge.net/
*/

abstract class adLDAPCollection
{
    /**
    * The current adLDAP connection via dependency injection
    * 
    * @var adLDAP
    */
    protected $adldap;
    
    /**
    * The current object being modifed / called
    * 
    * @var mixed
    */
    protected $currentObject;
    
    /**
    * The raw info array from Active Directory
    * 
    * @var array
    */
    protected $info;
    
    public function __construct($info, adLDAP $adldap) 
    {
        $this->setInfo($info);   
        $this->adldap = $adldap;
    }
    
    /**
    * Set the raw info array from Active Directory
    * 
    * @param array $info
    */
    public function setInfo(array $info) 
    {
        if ($this->info && sizeof($info) >= 1) {
            unset($this->info);
        }
        $this->info = $info;   
    }
    
    /**
    * Magic get method to retrieve data from the raw array in a formatted way
    * 
    * @param string $attribute
    * @return mixed
    */
    public function __get($attribute)
    {
        if (isset($this->info[0]) && is_array($this->info[0])) {
            foreach ($this->info[0] as $keyAttr => $valueAttr) {
                if (strtolower($keyAttr) == strtolower($attribute)) {
                    if ($this->info[0][strtolower($attribute)]['count'] == 1) {
                        return $this->info[0][strtolower($attribute)][0];   
                    }
                    else {
                        $array = array();
                        foreach ($this->info[0][strtolower($attribute)] as $key => $value) {
                            if ((string)$key != 'count') {
                                $array[$key] = $value;
                            } 
                        }  
                        return $array;   
                    }
                }   
            }
        }
        else {
            return NULL;   
        }
    }    
    
    /**
    * Magic set method to update an attribute
    * 
    * @param string $attribute
    * @param string $value
    * @return bool
    */
    abstract public function __set($attribute, $value);
}
?>
