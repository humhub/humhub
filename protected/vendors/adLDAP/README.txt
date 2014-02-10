PHP LDAP CLASS FOR MANIPULATING ACTIVE DIRECTORY
Version 4.0.3

Written by Scott Barnett, Richard Hyland
email: scott@wiggumworld.com, adldap@richardhyland.com
http://adldap.sourceforge.net/

ABOUT
=====

adLDAP is a PHP class that provides LDAP authentication and integration with Active Directory.

We'd appreciate any improvements or additions to be submitted back
to benefit the entire community :)

REQUIREMENTS
============

adLDAP requires PHP 5 and both the LDAP (http://php.net/ldap) and SSL (http://php.net/openssl) libraries

INSTALLATION
============

adLDAP is not an application, but a class library designed to integrate into your own applications.

The core of adLDAP is contained in the 'src' directory.  Simply copy/rename this directory inside your own
projects.

Edit the file 'src/adLDAP.php' and change the configuration variables near the top, specifically
those for domain controllers, base dn and account suffix, and if you want to perform anything more complex
than use authentication you'll also need to set the admin username and password variables too.

From within your code simply require the adLDAP.php file and call it like so

require_once(dirname(__FILE__) . '/adLDAP.php');
$adldap = new adLDAP();

It would be better to wrap it in a try/catch though

try {
    $adldap = new adLDAP();
}
catch (adLDAPException $e) {
    echo $e;
    exit();   
}

Then simply call commands against it e.g.

$adldap->authenticate($username, $password);

or 

$adldap->group()->members($groupName);

DOCUMENTATION
=============

You can find our website at http://adldap.sourceforce.net or the class documentation at

http://adldap.sourceforge.net/wiki/doku.php?id=documentation

LICENSE
=======

This library is free software; you can redistribute it and/or modify it under the terms of the 
GNU Lesser General Public License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU Lesser General Public License for more details or LICENSE.txt distributed with
this class.

