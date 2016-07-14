<?php
namespace raoul2000\jcrop;

use yii\web\AssetBundle;

/**
 *
 * @author Raoul <raoul.boulard@gmail.com>
 */
class JCropAsset extends AssetBundle
{

	public $css = [
		'css/jquery.Jcrop.css'
	];

	public $js = [
		'js/jquery.Jcrop.js'
	];

	public $depends = [
		'yii\web\JqueryAsset'
	];

	public function init()
	{
		$this->sourcePath = __DIR__ . '/assets';
		return parent::init();
	}
}
