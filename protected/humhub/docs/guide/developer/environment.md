Development Environment
=======================


Quick Notes
-----------

- Make sure that your using a Git/Composer HumHub Installation
- Enable development mode, see [Disable Errors Section](../admin/security.md#disable-errors-debugging)
- Disable Caching under `Administration -> Settings -> Advanced -> Caching -> None`


Modules Directory
-----------------

You can also locate your custom modules outside of the HumHub project structure.

```php
return [
    //...
    'params' => [
        'moduleAutoloadPaths' => ['/some/folder/modules'],        
    ],
    //...
```



Yii Debug Module
----------------

Add following block to your local web configuration (/protected/config/web.php)

```php
<?php
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
?>
```

Gii Code Generator
-------------------

 Add following block to your local web configuration (/protected/config/web.php)

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

Add following block to your local console configuration (/protected/config/console.php)

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

