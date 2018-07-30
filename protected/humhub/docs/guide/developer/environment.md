Development Environment
=======================

This guide shows some recommended settings of your development environment.

Quick Notes
-----------

- Make sure you are using a [Git/Composer HumHub installation](git-installation.md)
- Make sure the `debug` mode is enabled (default), see [Enable Production Mode](../admin/security.md#enable-production-mode)
- Disable caching under `Administration -> Settings -> Advanced -> Caching -> None`
- Use file based mailing `Administration -> Settings -> Advanced -> E-Mail`

External Modules Directory
-----------------

Custom modules can also be located outside of the default HumHub `modules` directory by
adding a path to the `moduleAutoloadPaths` array parameter in your `protected/config/common.php` configuration. This separation can
be useful while developing custom modules.

```php
return [
    'params' => [
        'moduleAutoloadPaths' => ['/some/folder/modules'],        
    ],
]
```

Yii Debug Module
----------------

You may want to enable the [Yii's debug Module](http://www.yiiframework.com/doc-2.0/ext-debug-index.html) for detailed
request and query debugging.
 
Just add the following block to your local web configuration `protected/config/web.php`:

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

Developer Tools Module
-------------------

The [devtools Module](https://github.com/humhub/humhub-modules-devtools) provides some useful showcases of widgets and a Module generator based on [Gii](https://www.yiiframework.com/doc/guide/2.0/en/start-gii).
