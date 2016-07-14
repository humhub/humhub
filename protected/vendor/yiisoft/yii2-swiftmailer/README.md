SwiftMailer Extension for Yii 2
===============================

This extension provides a [SwiftMailer](http://swiftmailer.org/) mail solution for [Yii framework 2.0](http://www.yiiframework.com).

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-swiftmailer/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-swiftmailer)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-swiftmailer/downloads.png)](https://packagist.org/packages/yiisoft/yii2-swiftmailer)
[![Build Status](https://travis-ci.org/yiisoft/yii2-swiftmailer.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-swiftmailer)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-swiftmailer
```

or add

```json
"yiisoft/yii2-swiftmailer": "~2.0.0"
```

to the require section of your composer.json.

Usage
-----

To use this extension,  simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
    ],
];
```

You can then send an email as follows:

```php
Yii::$app->mailer->compose('contact/html')
     ->setFrom('from@domain.com')
     ->setTo($form->email)
     ->setSubject($form->subject)
     ->send();
```

For further instructions refer to the [related section in the Yii Definitive Guide](http://www.yiiframework.com/doc-2.0/guide-tutorial-mailing.html).

