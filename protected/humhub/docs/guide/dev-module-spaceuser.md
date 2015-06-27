Space/User
==========





## Module

If the module also runs in Space/User Profile context, you need to add an additional behavior to the Module class.

These behaviors adds functionalities like:

- Enable/Disable module (Space / Profile)
- Additional module description/image based on context Space or Profile
 
### UserModuleBehavior

By adding this behavior to your module class, each user can enable/disable the module on the section *Account Settings -> Modules*. (Unless it's automatically enabled.)

Behavior Class: [[\humhub\core\user\behaviors\UserModule]]
Module Class Example:

```php
public function behaviors()
{
    return [
        \humhub\core\user\behaviors\UserModule::className(),
    ];
}

```

### SpaceModuleBehavior

By adding this behavior to your module class, a space admin can enable/disable the module on the section *Space Admin -> Modules*. (Unless it's automatically enabled.)

Behavior Class: [[\humhub\core\space\behaviors\SpaceModule]]
Module Class Example:

```php
public function behaviors()
{
    return [
        \humhub\core\space\behaviors\SpaceModule::className(),
    ];
}
```



## Controller

TBD



