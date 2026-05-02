<?php

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

class TopNavigationAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    public $jsOptions = ['position' => \yii\web\View::POS_END];

    /**
     * @inheritdoc
     */
    public $js = ['js/humhub/humhub.ui.topNavigation.js'];

}
