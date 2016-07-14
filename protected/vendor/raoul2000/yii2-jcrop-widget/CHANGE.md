# 1.0.0

- change the way plugin options are set.

Example :

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

# 0.1
initial release