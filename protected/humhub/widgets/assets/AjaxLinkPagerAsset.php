<?php

namespace humhub\widgets\assets;

use humhub\components\assets\AssetBundle;

class AjaxLinkPagerAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/widgets/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.ajaxLinkPager.js',
    ];
}
