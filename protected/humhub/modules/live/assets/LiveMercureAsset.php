<?php

namespace humhub\modules\live\assets;

use humhub\components\assets\AssetBundle;

class LiveMercureAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@live/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.live.mercure.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        LiveAsset::class,
    ];

}
