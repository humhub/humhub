LDAP
=======

Basic configuration
-------------------

- Define server settings at: Administration -> Users -> Settings -> LDAP 
- Attribute mapping: Administration -> Users -> Profile -> Select profile field -> LDAP Attribute


User Mapping (Enterprise Edition)
---------------------------------

The Enterprise Edition provides additional LDAP capabilities like automatic User to Space or User to Group mapping.

**Space Mapping**
As administrative user you can setup a ldap group dn at: Space -> Members -> LDAP.

**Group Mapping**
Administration -> Users -> Groups -> Edit -> LDAP Mapping


Date fields
-----------

If you're using custom date formats in our ldap backend, you can specify different formats
in the configuration file.

```php
    'params' => [
        'ldap' => [
            'dateFields' => [
                'somedatefield' => 'Y.m.d'
            ],
        ],
    ],
```

Note: Make sure to use lower case in the field.
