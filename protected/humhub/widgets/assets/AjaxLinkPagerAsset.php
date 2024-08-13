<?php

namespace humhub\widgets\assets;

use humhub\components\assets\AssetBundle;

class AjaxLinkPagerAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@app/humhub/widgets/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.ajaxLinkPager.js'
    ];
}
