# Enviroment

## Enable Yii Debug Module

Add following block to your local web configuration (/protected/config/web.php)

```php
<?php
return [
     ...
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

Add following block to your local web configuration (/protected/config/web.php)

```php
return [
     ...
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