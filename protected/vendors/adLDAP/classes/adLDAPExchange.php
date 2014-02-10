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
 * @subpackage Exchange
 * @author Scott Barnett, Richard Hyland
 * @copyright (c) 2006-2011 Scott Barnett, Richard Hyland
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPLv2.1
 * @revision $Revision: 97 $
 * @version 4.0.2
 * @link http://adldap.sourceforge.net/
 */
require_once(dirname(__FILE__) . '/../adLDAP.php');

/**
* MICROSOFT EXCHANGE FUNCTIONS
*/
class adLDAPExchange {
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
    * Create an Exchange account
    * 
    * @param string $username The username of the user to add the Exchange account to
    * @param array $storagegroup The mailbox, Exchange Storage Group, for the user account, this must be a full CN
    *                            If the storage group has a different base_dn to the adLDAP configuration, set it using $base_dn
    * @param string $emailaddress The primary email address to add to this user
    * @param string $mailnickname The mail nick name.  If mail nickname is blank, the username will be used
    * @param bool $usedefaults Indicates whether the store should use the default quota, rather than the per-mailbox quota.
    * @param string $base_dn Specify an alternative base_dn for the Exchange storage group
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function createMailbox($username, $storageGroup, $emailAddress, $mailNickname=NULL, $useDefaults=TRUE, $baseDn=NULL, $isGUID=false)
    {
        if ($username === NULL){ return "Missing compulsory field [username]"; }     
        if ($storagegroup === NULL) { return "Missing compulsory array [storagegroup]"; }
        if (!is_array($storagegroup)) { return "[storagegroup] must be an array"; }
        if ($emailaddress === NULL) { return "Missing compulsory field [emailaddress]"; }
        
        if ($baseDn === NULL) {
            $baseDn = $this->adldap->getBaseDn();   
        }
        
        $container = "CN=" . implode(",CN=", $storageGroup);
        
        if ($mailNickname === NULL) { 
            $mailNickname = $username; 
        }
        $mdbUseDefaults = $this->utilities()->boolToString($useDefaults);
        
        $attributes = array(
            'exchange_homemdb'=>$container.",".$baseDn,
            'exchange_proxyaddress'=>'SMTP:' . $emailAddress,
            'exchange_mailnickname'=>$mailNickname,
            'exchange_usedefaults'=>$mdbUseDefaults
        );
        $result = $this->adldap->user()->modify($username, $attributes, $isGUID);
        if ($result == false) { 
            return false; 
        }
        return true;
    }
    
    /**
    * Add an X400 address to Exchange
    * See http://tools.ietf.org/html/rfc1685 for more information.
    * An X400 Address looks similar to this X400:c=US;a= ;p=Domain;o=Organization;s=Doe;g=John;
    * 
    * @param string $username The username of the user to add the X400 to to
    * @param string $country Country
    * @param string $admd Administration Management Domain
    * @param string $pdmd Private Management Domain (often your AD domain)
    * @param string $org Organization
    * @param string $surname Surname
    * @param string $givenName Given name
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function addX400($username, $country, $admd, $pdmd, $org, $surname, $givenName, $isGUID=false) 
    {
        if ($username === NULL){ return "Missing compulsory field [username]"; }     
        
        $proxyValue = 'X400:';
            
        // Find the dn of the user
        $user = $this->adldap->user()->info($username, array("cn","proxyaddresses"), $isGUID);
        if ($user[0]["dn"] === NULL) { return false; }
        $userDn = $user[0]["dn"];
        
        // We do not have to demote an email address from the default so we can just add the new proxy address
        $attributes['exchange_proxyaddress'] = $proxyValue . 'c=' . $country . ';a=' . $admd . ';p=' . $pdmd . ';o=' . $org . ';s=' . $surname . ';g=' . $givenName . ';';
       
        // Translate the update to the LDAP schema                
        $add = $this->adldap->adldap_schema($attributes);
        
        if (!$add) { return false; }
        
        // Do the update
        // Take out the @ to see any errors, usually this error might occur because the address already
        // exists in the list of proxyAddresses
        $result = @ldap_mod_add($this->adldap->getLdapConnection(), $userDn, $add);
        if ($result == false) { 
            return false; 
        }
        
        return true;
    }
    
    /**
    * Add an address to Exchange
    * 
    * @param string $username The username of the user to add the Exchange account to
    * @param string $emailaddress The email address to add to this user
    * @param bool $default Make this email address the default address, this is a bit more intensive as we have to demote any existing default addresses
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function addAddress($username, $emailAddress, $default = FALSE, $isGUID = false) 
    {
        if ($username === NULL) { return "Missing compulsory field [username]"; }     
        if ($emailaddress === NULL) { return "Missing compulsory fields [emailaddress]"; }
        
        $proxyValue = 'smtp:';
        if ($default === true) {
            $proxyValue = 'SMTP:';
        }
              
        // Find the dn of the user
        $user = $this->adldap->user()->info($username, array("cn","proxyaddresses"), $isGUID);
        if ($user[0]["dn"] === NULL){ return false; }
        $userDn = $user[0]["dn"];
        
        // We need to scan existing proxy addresses and demote the default one
        if (is_array($user[0]["proxyaddresses"]) && $default === true) {
            $modAddresses = array();
            for ($i=0;$i<sizeof($user[0]['proxyaddresses']);$i++) {
                if (strstr($user[0]['proxyaddresses'][$i], 'SMTP:') !== false) {
                    $user[0]['proxyaddresses'][$i] = str_replace('SMTP:', 'smtp:', $user[0]['proxyaddresses'][$i]);
                }
                if ($user[0]['proxyaddresses'][$i] != '') {
                    $modAddresses['proxyAddresses'][$i] = $user[0]['proxyaddresses'][$i];
                }
            }
            $modAddresses['proxyAddresses'][(sizeof($user[0]['proxyaddresses'])-1)] = 'SMTP:' . $emailAddress;
            
            $result = @ldap_mod_replace($this->adldap->getLdapConnection(), $userDn, $modAddresses);
            if ($result == false) { 
                return false; 
            }
            
            return true;
        }
        else {
            // We do not have to demote an email address from the default so we can just add the new proxy address
            $attributes['exchange_proxyaddress'] = $proxyValue . $emailAddress;
            
            // Translate the update to the LDAP schema                
            $add = $this->adldap->adldap_schema($attributes);
            
            if (!$add) { 
                return false; 
            }
            
            // Do the update
            // Take out the @ to see any errors, usually this error might occur because the address already
            // exists in the list of proxyAddresses
            $result = @ldap_mod_add($this->adldap->getLdapConnection(), $userDn,$add);
            if ($result == false) { 
                return false; 
            }
            
            return true;
        }
    }
    
    /**
    * Remove an address to Exchange
    * If you remove a default address the account will no longer have a default, 
    * we recommend changing the default address first
    * 
    * @param string $username The username of the user to add the Exchange account to
    * @param string $emailaddress The email address to add to this user
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function deleteAddress($username, $emailAddress, $isGUID=false) 
    {
        if ($username === NULL) { return "Missing compulsory field [username]"; }     
        if ($emailAddress === NULL) { return "Missing compulsory fields [emailaddress]"; }
        
        // Find the dn of the user
        $user = $this->adldap->user()->info($username, array("cn","proxyaddresses"), $isGUID);
        if ($user[0]["dn"] === NULL) { return false; }
        $userDn = $user[0]["dn"];
        
        if (is_array($user[0]["proxyaddresses"])) {
            $mod = array();
            for ($i=0;$i<sizeof($user[0]['proxyaddresses']);$i++) {
                if (strstr($user[0]['proxyaddresses'][$i], 'SMTP:') !== false && $user[0]['proxyaddresses'][$i] == 'SMTP:' . $emailAddress) {
                    $mod['proxyAddresses'][0] = 'SMTP:' . $emailAddress;
                }
                elseif (strstr($user[0]['proxyaddresses'][$i], 'smtp:') !== false && $user[0]['proxyaddresses'][$i] == 'smtp:' . $emailAddress) {
                    $mod['proxyAddresses'][0] = 'smtp:' . $emailAddress;
                }
            }
            
            $result = @ldap_mod_del($this->adldap->getLdapConnection(), $userDn,$mod);
            if ($result == false) { 
                return false; 
            }
            
            return true;
        }
        else {
            return false;
        }
    }
    /**
    * Change the default address
    * 
    * @param string $username The username of the user to add the Exchange account to
    * @param string $emailaddress The email address to make default
    * @param bool $isGUID Is the username passed a GUID or a samAccountName
    * @return bool
    */
    public function primaryAddress($username, $emailAddress, $isGUID = false) 
    {
        if ($username === NULL) { return "Missing compulsory field [username]"; }     
        if ($emailAddress === NULL) { return "Missing compulsory fields [emailaddress]"; }
        
        // Find the dn of the user
        $user = $this->adldap->user()->info($username, array("cn","proxyaddresses"), $isGUID);
        if ($user[0]["dn"] === NULL){ return false; }
        $userDn = $user[0]["dn"];
        
        if (is_array($user[0]["proxyaddresses"])) {
            $modAddresses = array();
            for ($i=0;$i<sizeof($user[0]['proxyaddresses']);$i++) {
                if (strstr($user[0]['proxyaddresses'][$i], 'SMTP:') !== false) {
                    $user[0]['proxyaddresses'][$i] = str_replace('SMTP:', 'smtp:', $user[0]['proxyaddresses'][$i]);
                }
                if ($user[0]['proxyaddresses'][$i] == 'smtp:' . $emailAddress) {
                    $user[0]['proxyaddresses'][$i] = str_replace('smtp:', 'SMTP:', $user[0]['proxyaddresses'][$i]);
                }
                if ($user[0]['proxyaddresses'][$i] != '') {
                    $modAddresses['proxyAddresses'][$i] = $user[0]['proxyaddresses'][$i];
                }
            }
            
            $result = @ldap_mod_replace($this->adldap->getLdapConnection(), $userDn, $modAddresses);
            if ($result == false) { 
                return false; 
            }
            
            return true;
        }
        
    }
    
    /**
    * Mail enable a contact
    * Allows email to be sent to them through Exchange
    * 
    * @param string $distinguishedname The contact to mail enable
    * @param string $emailaddress The email address to allow emails to be sent through
    * @param string $mailnickname The mailnickname for the contact in Exchange.  If NULL this will be set to the display name
    * @return bool
    */
    public function contactMailEnable($distinguishedName, $emailAddress, $mailNickname = NULL)
    {
        if ($distinguishedName === NULL) { return "Missing compulsory field [distinguishedname]"; }   
        if ($emailAddress === NULL) { return "Missing compulsory field [emailaddress]"; }  
        
        if ($mailNickname !== NULL) {
            // Find the dn of the user
            $user = $this->adldap->contact()->info($distinguishedName, array("cn","displayname"));
            if ($user[0]["displayname"] === NULL) { return false; }
            $mailNickname = $user[0]['displayname'][0];
        }
        
        $attributes = array("email"=>$emailAddress,"contact_email"=>"SMTP:" . $emailAddress,"exchange_proxyaddress"=>"SMTP:" . $emailAddress,"exchange_mailnickname"=>$mailNickname);
         
        // Translate the update to the LDAP schema                
        $mod = $this->adldap->adldap_schema($attributes);
        
        // Check to see if this is an enabled status update
        if (!$mod) { return false; }
        
        // Do the update
        $result = ldap_modify($this->adldap->getLdapConnection(), $distinguishedName, $mod);
        if ($result == false) { return false; }
        
        return true;
    }
    
    /**
    * Returns a list of Exchange Servers in the ConfigurationNamingContext of the domain
    * 
    * @param array $attributes An array of the AD attributes you wish to return
    * @return array
    */
    public function servers($attributes = array('cn','distinguishedname','serialnumber')) 
    {
        if (!$this->adldap->getLdapBind()){ return false; }
        
        $configurationNamingContext = $this->adldap->getRootDse(array('configurationnamingcontext'));
        $sr = @ldap_search($this->adldap->getLdapConnection(), $configurationNamingContext[0]['configurationnamingcontext'][0],'(&(objectCategory=msExchExchangeServer))', $attributes);
        $entries = @ldap_get_entries($this->adldap->getLdapConnection(), $sr);
        return $entries;
    }
    
    /**
    * Returns a list of Storage Groups in Exchange for a given mail server
    * 
    * @param string $exchangeServer The full DN of an Exchange server.  You can use exchange_servers() to find the DN for your server
    * @param array $attributes An array of the AD attributes you wish to return
    * @param bool $recursive If enabled this will automatically query the databases within a storage group
    * @return array
    */
    public function storageGroups($exchangeServer, $attributes = array('cn','distinguishedname'), $recursive = NULL) 
    {
        if (!$this->adldap->getLdapBind()){ return false; }
        if ($exchangeServer === NULL) { return "Missing compulsory field [exchangeServer]"; }
        if ($recursive === NULL) { $recursive = $this->adldap->getRecursiveGroups(); }

        $filter = '(&(objectCategory=msExchStorageGroup))';
        $sr = @ldap_search($this->adldap->getLdapConnection(), $exchangeServer, $filter, $attributes);
        $entries = @ldap_get_entries($this->adldap->getLdapConnection(), $sr);

        if ($recursive === true) {
            for ($i=0; $i<$entries['count']; $i++) {
                $entries[$i]['msexchprivatemdb'] = $this->storageDatabases($entries[$i]['distinguishedname'][0]);       
            }
        }
        
        return $entries;
    }
    
    /**
    * Returns a list of Databases within any given storage group in Exchange for a given mail server
    * 
    * @param string $storageGroup The full DN of an Storage Group.  You can use exchange_storage_groups() to find the DN 
    * @param array $attributes An array of the AD attributes you wish to return
    * @return array
    */
    public function storageDatabases($storageGroup, $attributes = array('cn','distinguishedname','displayname')) {
        if (!$this->adldap->getLdapBind()){ return false; }
        if ($storageGroup === NULL) { return "Missing compulsory field [storageGroup]"; }
        
        $filter = '(&(objectCategory=msExchPrivateMDB))';
        $sr = @ldap_search($this->adldap->getLdapConnection(), $storageGroup, $filter, $attributes);
        $entries = @ldap_get_entries($this->adldap->getLdapConnection(), $sr);
        return $entries;
    }
}
?>