<?php


namespace humhub\assets;


use yii\web\AssetBundle;

class TopNavigationAsset extends AssetBundle
{
    public $jsOptions = ['position' => \yii\web\View::POS_END];

    public $basePath = '@webroot-static';
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = ['js/humhub/humhub.ui.topNavigation.js'];

}
