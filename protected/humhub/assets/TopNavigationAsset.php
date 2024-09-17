<?php

namespace humhub\assets;

use humhub\components\assets\CoreAssetBundle;

class TopNavigationAsset extends CoreAssetBundle
{
    public $jsOptions = ['position' => \yii\web\View::POS_END];

    public $basePath = '@webroot-static';
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = ['js/humhub/humhub.ui.topNavigation.js'];

}
