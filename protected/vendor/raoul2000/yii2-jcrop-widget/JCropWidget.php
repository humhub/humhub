<?php
namespace raoul2000\jcrop;

use Yii;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * JCropWidget is a wrapper for the [jQuery Image Cropping Plugin](http://deepliquid.com/content/Jcrop.html).
 *
 * ~~~
 * echo JCropWidget::widget([
 *    'selector' => '#image_id',
 *    'pluginOptions' => [
 *        'aspectRatio' => 1,
 *        'minSize' => [50,50],
 *        'maxSize' => [200,200],
 *        'setSelect' => [10,10,40,40],
 *        'bgColor' => 'black',
 *        'bgOpacity' => '0.5',
 *        'onChange' => new yii\web\JsExpression('function(c){console.log(c.x);}')
 *     ]
 * ]);
 * ~~~
 *
 * @author Raoul <raoul.boulard@gmail.com>
 *
 */
class JCropWidget extends Widget
{
	/**
	 * @var string the JQuery selector for the image element
	 */
	public $selector;
	/**
	 * @var array JCrop plugin options see http://deepliquid.com/content/Jcrop_Manual.html
	 */
	public $pluginOptions = [];

	/**
	 * Initializes the widget.
	 *
	 * @throws InvalidConfigException if the "id" property is not set.
	 */
	public function init()
	{
		parent::init();
		if (empty($this->selector)) {
			throw new InvalidConfigException('The "selector" property must be set.');
		}
	}

	/**
	 * Runs the widget.
	 *
	 * @see \yii\base\Widget::run()
	 */
	public function run()
	{
		$this->registerClientScript();
	}

	/**
	 * Registers the needed JavaScript and register the JS initialization code
	 */
	public function registerClientScript()
	{
		$options = empty($this->pluginOptions) ? '' : Json::encode($this->pluginOptions);

		$js = "jQuery(\"{$this->selector}\").Jcrop(" . $options . ");";

		$view = $this->getView();
		JCropAsset::register($view);
		$view->registerJs($js);
	}
}
