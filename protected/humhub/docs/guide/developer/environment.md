Development Environment
=======================

Quick Notes
-----------

- Make sure you are using a [Git/Composer HumHub Installation](git-installation.md)
- Enable development mode, see [Disable Errors Section](../admin/security.md#disable-errors-debugging)
- Disable Caching under `Administration -> Settings -> Advanced -> Caching -> None`

> Tip: If you are working on a windows machine, but wan't to operate your test environment on linux, you should consider using a virtual machine with a shared host directory.

Modules Directory
-----------------

You can locate your custom modules outside of the HumHub project directory by means of the following [configuration](../admin/advanced-configuration.md#application-params)

```php
return [
    'params' => [
        'moduleAutoloadPaths' => ['/some/folder/modules'],        
    ]
]
```



Yii Debug Module
----------------

Add the following block to your local web configuration `/protected/config/web.php` to enable the [Debug Extension for Yii 2](http://www.yiiframework.com/doc-2.0/ext-debug-index.html)

```php
<?php
return [
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1', '::1'],
        ],
    ]
];
?>
```

Gii Code Generator
-------------------

Add following block to your local web configuration `/protected/config/web.php` to enable the [Gii Code Generator](http://www.yiiframework.com/doc-2.0/guide-start-gii.html)

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

Furthermore add the following block to your local console configuration (/protected/config/console.php)

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

> Tip: Check out the [HumHub devtools](https://github.com/humhub/humhub-modules-devtools) module for a HumHub module generator and some showcases and tutorials. At the time of writing, the devtools Module is still under developement. 

HumHub Build
-----------

Some of the tasks

### Setup

 1. Install NPM
 2. Install Grunt (http://gruntjs.com/getting-started)
 3. call `npm update` in humhub root

> Note: Since symlinks are not supported in some virtual machine shared folders the update command should be called from the host.

#### Setup grunt dependencies

Call the following commands in your humhub root directory:
 - `npm update`
 - `npm install grunt --save-dev`

### Build Production Assets

HumHub uses Yiis build-in mechanism for compressing and combining assets as javascript or stylesheet files in combination with grunt.

Your compressed files will be saved under `/humhub/js/all-*.js` respectively `static/css/all-*.css`. The task will also rebuild your `static/assets` folder, which contains dependent production assets. 

> Info: HumHub will only use the compressed assets if operated in [production mode](admin-installation.md#disable-errors-debugging), otherwise all assets will be served seperatly.

##### Grunt Asset Built

The simples way to build your production assets is using the following grunt task:

```
grunt build-assets
```

#####  Manual Asset Built

1. Delete the content of your `static/assets` directory.
2. Delete the old compressed file `static/js/all-*.js` and `static/css/all-*.css`
2. Run:

```
php yii asset humhub/config/assets.php humhub/config/assets-prod.php
```

> Info: More information is available in the [Yii Asset Guide](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html#combining-compressing-assets).

### Build Theme

To rebuild the community themes  `theme.css` file you can execute one of the following commands:

```
lessc -x themes/HumHub/less/build.less themes/HumHub/css/theme.css
```

or with grunt:

```
grunt build-theme
```

You can also build other themes within your `themes` folder as follows

```
grunt build-theme --name=myTheme
```


### Other Grunt Tasks
 - `grunt build-search` Rebuild your [Search Index](../admin/search.md)

