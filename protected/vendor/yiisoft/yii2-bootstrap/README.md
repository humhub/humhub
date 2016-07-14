Twitter Bootstrap Extension for Yii 2
=====================================

This is the Twitter Bootstrap extension for [Yii framework 2.0](http://www.yiiframework.com). It encapsulates [Bootstrap](http://getbootstrap.com/) components
and plugins in terms of Yii widgets, and thus makes using Bootstrap components/plugins
in Yii applications extremely easy.

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-bootstrap/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-bootstrap)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-bootstrap/downloads.png)](https://packagist.org/packages/yiisoft/yii2-bootstrap)
[![Build Status](https://travis-ci.org/yiisoft/yii2-bootstrap.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-bootstrap)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-bootstrap
```

or add

```
"yiisoft/yii2-bootstrap": "~2.0.0"
```

to the require section of your `composer.json` file.

Usage
----

For example, the following
single line of code in a view file would render a Bootstrap Progress plugin:

```php
<?= yii\bootstrap\Progress::widget(['percent' => 60, 'label' => 'test']) ?>
```
