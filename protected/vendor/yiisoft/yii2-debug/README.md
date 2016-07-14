Debug Extension for Yii 2
=========================

This extension provides a debugger for [Yii framework 2.0](http://www.yiiframework.com) applications. When this extension is used,
a debugger toolbar will appear at the bottom of every page. The extension also provides
a set of standalone pages to display more detailed debug information.

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-debug/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-debug)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-debug/downloads.png)](https://packagist.org/packages/yiisoft/yii2-debug)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-debug
```

or add

```
"yiisoft/yii2-debug": "~2.0.0"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
        ],
        // ...
    ],
    ...
];
```

You will see a debugger toolbar showing at the bottom of every page of your application.
You can click on the toolbar to see more detailed debug information.
