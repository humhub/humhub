JCrop Yii2 Extension
====================
This yii2 extension is a wrapper for the jQuery Image Cropping Plugin (jcrop)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist raoul2000/yii2-jcrop-widget "*"
```

or add

```
"raoul2000/yii2-jcrop-widget": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
	raoul2000\jcrop\JCropWidget::widget([
		'selector' => '#image_id',
		'pluginOptions' => [
			'aspectRatio' => 1,
			'minSize' => [50,50],
			'maxSize' => [200,200],
			'setSelect' => [10,10,40,40],
			'bgColor' => 'black',
			'bgOpacity' => '0.5',
			'onChange' => new yii\web\JsExpression('function(c){console.log(c.x);}')
		]
	]);
```

For complete documentation please refer to the [official JCrop page](http://deepliquid.com/content/Jcrop.html)

License
-------

**yii2-jcrop-widget** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.