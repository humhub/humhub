<?php

namespace humhub\assets;

use humhub\components\assets\WebResourcesAssetBundle;

class TopNavigationAsset extends WebResourcesAssetBundle
{
    public $jsOptions = ['position' => \yii\web\View::POS_END];

    /**
     * @inheritdoc
     */
    public $js = ['js/humhub/humhub.ui.topNavigation.js'];

}
