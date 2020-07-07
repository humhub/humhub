<?php


namespace humhub\modules\live\assets;


use humhub\assets\SocketIoAsset;
use humhub\components\assets\AssetBundle;

class LivePushAsset extends AssetBundle
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
        SocketIoAsset::class
    ];

}
