Development Environment
=======================

Quick Notes
-----------

- Make sure you are using a [Git/Composer HumHub installation](git-installation.md)
- Enable the debug mode, see [Disable Errors Section](../admin/security.md#disable-errors-debugging)
- Disable caching under `Administration -> Settings -> Advanced -> Caching -> None`

External Modules Directory
-----------------

Custom modules can also be located outside of the default HumHub modules directory by
setting the `moduleAutoloadPaths` parameter in your `/protected/config/common.php` configuration. This seperation can
be useful while working with custom modules.

```php
return [
    //...
    'params' => [
        'moduleAutoloadPaths' => ['/some/folder/modules'],        
    ],
    //...
]
```

Yii Debug Module
----------------

Add following block to your local web configuration `/protected/config/web.php` in order
to allow [Yii's debug Module](http://www.yiiframework.com/doc-2.0/ext-debug-index.html).

```php
return [
    // ...
    'bootstrap' => ['debug'],
	'modules' => [
	    // ...
	    'debug' => [
	        'class' => 'yii\debug\Module',
	        'allowedIPs' => ['127.0.0.1', '::1'],
	    ],
            // ...
	]
];
```

Gii Code Generator
-------------------

 Add following block to your local web configuration `/protected/config/web.php` in order to
 enable the [Gii Code Generator Module](http://www.yiiframework.com/doc-2.0/guide-start-gii.html)

```php
return [
          // ...
	 'modules' => [
            // ...
	    'gii' => [
	        'class' => 'yii\gii\Module',
	        'allowedIPs' => ['127.0.0.1', '::1'],
	    ],
            // ...
	]
];
?>
```

Furthermore add the following block to your local console configuration `/protected/config/console.php`

```php
return [
    // ...
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    // ...
];
```

Developer Tools Module
-------------------

The [devtools Module](https://github.com/humhub/humhub-modules-devtools) provides useful showcases of widgets and also a Module generator based on Gii.
