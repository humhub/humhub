LDAP Support
============

This feature is highly experimental and requires PHP LDAP Support!



Configuration
--------------

Add following under ``protected/config/_settings.php`` in the ´´params -> auth`` array.

    // LDAP Specifc Settings
    'ldap' => array(

        // User Group
        'userGroup' => 'CN=office,CN=Users,DC=domain,DC=local',

        // Account 
        'adminUser' => 'Admin User',
        'adminPassword' => 'xxx',

        // Connection String
        'adldapConfig' => array('base_dn'=>'DC=domain,DC=local','account_suffix'=>'@domain.local', 'domain_controllers'=>array('localhost')),

        // Mapping between LDAP Group and Space
        'groupToSpaceMap' => array(
            'CN=someGroup,CN=Users,DC=domain,DC=local' => 'Office',
            // ...
        ),

        // Field Sync (LDAP -> User Field)
        'fieldMapping' => array(
            'lastname' => array('ldapField'=>'sn', 'sync'=>'fromLdap'),
            'firstname' => array('ldapField'=>'givenname', 'sync'=>'fromLdap'),
            //'city'  => array('ldapField'=>'givenname', 'sync'=>'full'),
            //'state'  => array('ldapField'=>'st', 'sync'=>'full'),
            //'country'  => array('ldapField'=>'c', 'sync'=>'fromLdap'),
            //'country'  => array('ldapField'=>'c', 'sync'=>'fromLdap'),
        ),

    ),

LDAP also needs a cronjob

    # When using LDAP
    45 * * * * /path/to/protected/protected/yiic ldap_update >/dev/null 2>&1
