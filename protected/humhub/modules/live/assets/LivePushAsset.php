<?php

namespace humhub\modules\live\assets;

use humhub\assets\SocketIoAsset;
use humhub\components\assets\CoreAssetBundle;

class LivePushAsset extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@live/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.live.push.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        LiveAsset::class,
        SocketIoAsset::class,
    ];

}
