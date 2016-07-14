Gii Extension for Yii 2
========================

This extension provides a Web-based code generator, called Gii, for [Yii framework 2.0](http://www.yiiframework.com) applications.
You can use Gii to quickly generate models, forms, modules, CRUD, etc.

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-gii/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-gii)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-gii/downloads.png)](https://packagist.org/packages/yiisoft/yii2-gii)
[![Build Status](https://travis-ci.org/yiisoft/yii2-gii.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-gii)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --dev --prefer-dist yiisoft/yii2-gii
```

or add

```
"yiisoft/yii2-gii": "~2.0.0"
```

to the require-dev section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
        ],
        // ...
    ],
    // ...
];
```

You can then access Gii through the following URL:

```
http://localhost/path/to/index.php?r=gii
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/gii
```

Using the same configuration for your console application, you will also be able to access Gii via
command line as follows,

```
# change path to your application's base path
cd path/to/AppBasePath

# show help information about Gii
yii help gii

# show help information about the model generator in Gii
yii help gii/model

# generate City model from city table
yii gii/model --tableName=city --modelClass=City
```
