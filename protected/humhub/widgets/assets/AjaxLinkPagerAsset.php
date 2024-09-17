<?php

namespace humhub\widgets\assets;

use humhub\components\assets\CoreAssetBundle;

class AjaxLinkPagerAsset extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@app/humhub/widgets/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.ajaxLinkPager.js',
    ];
}
