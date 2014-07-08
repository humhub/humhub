Settings
========


Global Settings
---------------

Example:

```php
    HSetting::Set('someName', 'someValue' ,'exampleModuleId');
    $mySetting = HSetting::Get('someName', 'exampleModuleId');
```



User Settings
-------------

Example:

```php
    $user->setSetting("someName", "someValue", "exampleModuleId");
    $mySetting = $user->getSetting("someName", "exampleModuleId");
```



Space Settings
--------------

Example:

```php
    $space->setSetting("someName", "someValue", "exampleModuleId");
    $mySetting = $space->getSetting("someName", "exampleModuleId");
```
