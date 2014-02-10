<?php
/**
 * PHP LDAP CLASS FOR MANIPULATING ACTIVE DIRECTORY 
 * Version 4.0.2
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
 * @subpackage Computers
 * @author Scott Barnett, Richard Hyland
 * @copyright (c) 2006-2011 Scott Barnett, Richard Hyland
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPLv2.1
 * @revision $Revision: 97 $
 * @version 4.0.2
 * @link http://adldap.sourceforge.net/
 */
require_once(dirname(__FILE__) . '/../adLDAP.php');
require_once(dirname(__FILE__) . '/../collections/adLDAPComputerCollection.php');  

/**
* COMPUTER MANAGEMENT FUNCTIONS
*/
class adLDAPComputers {
    
    /**
    * The current adLDAP connection via dependency injection
    * 
    * @var adLDAP
    */
    protected $adldap;
    
    public function __construct(adLDAP $adldap) {
        $this->adldap = $adldap;
    }
    
    /**
    * Get information about a specific computer. Returned in a raw array format from AD
    * 
    * @param string $computerName The name of the computer
    * @param array $fields Attributes to return
    * @return array
    */
    public function info($computerName, $fields = NULL)
    {
        if ($computerName === NULL) { return false; }
        if (!$this->adldap->getLdapBind()) { return false; }

        $filter = "(&(objectClass=computer)(cn=" . $computerName . "))";
        if ($fields === NULL) { 
            $fields = array("memberof","cn","displayname","dnshostname","distinguishedname","objectcategory","operatingsystem","operatingsystemservicepack","operatingsystemversion"); 
        }
        $sr = ldap_search($this->adldap->getLdapConnection(), $this->adldap->getBaseDn(), $filter, $fields);
        $entries = ldap_get_entries($this->adldap->getLdapConnection(), $sr);
        
        return $entries;
    }
    
    /**
    * Find information about the computers. Returned in a raw array format from AD
    * 
    * @param string $computerName The name of the computer
    * @param array $fields Array of parameters to query
    * @return mixed
    */
    public function infoCollection($computerName, $fields = NULL)
    {
        if ($computerName === NULL) { return false; }
        if (!$this->adldap->getLdapBind()) { return false; }
        
        $info = $this->info($computerName, $fields);
        
        if ($info !== false) {
            $collection = new adLDAPComputerCollection($info, $this->adldap);
            return $collection;
        }
        return false;
    }
    
    /**
    * Check if a computer is in a group
    * 
    * @param string $computerName The name of the computer
    * @param string $group The group to check
    * @param bool $recursive Whether to check recursively
    * @return array
    */
    public function inGroup($computerName, $group, $recursive = NULL)
    {
        if ($computerName === NULL) { return false; }
        if ($group === NULL) { return false; }
        if (!$this->adldap->getLdapBind()) { return false; }
        if ($recursive === NULL) { $recursive = $this->adldap->getRecursiveGroups(); } // use the default option if they haven't set it

        //get a list of the groups
        $groups = $this->groups($computerName, array("memberof"), $recursive);

        //return true if the specified group is in the group list
        if (in_array($group, $groups)){ 
            return true; 
        }

        return false;
    }
    
    /**
    * Get the groups a computer is in
    * 
    * @param string $computerName The name of the computer
    * @param bool $recursive Whether to check recursively
    * @return array
    */
    public function groups($computerName, $recursive = NULL)
    {
        if ($computerName === NULL) { return false; }
        if ($recursive === NULL) { $recursive = $this->adldap->getRecursiveGroups(); } //use the default option if they haven't set it
        if (!$this->adldap->getLdapBind()){ return false; }

        //search the directory for their information
        $info = @$this->info($computerName, array("memberof", "primarygroupid"));
        $groups = $this->adldap->utilities()->niceNames($info[0]["memberof"]); //presuming the entry returned is our guy (unique usernames)

        if ($recursive === true) {
            foreach ($groups as $id => $groupName){
              $extraGroups = $this->adldap->group()->recursiveGroups($groupName);
              $groups = array_merge($groups, $extraGroups);
            }
        }

        return $groups;
    }
    
}
?>