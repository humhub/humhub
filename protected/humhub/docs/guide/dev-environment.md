# Enviroment

## Enable Yii Debug Module

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

## Enable Gii 

### Web

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


### Console

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