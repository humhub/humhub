<?php


namespace modules\live\assets;


use humhub\assets\SocketIoAsset;
use humhub\components\assets\AssetBundle;
use humhub\modules\live\assets\LiveAsset;

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
        'js/humhub.live.poll.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        LiveAsset::class,
        SocketIoAsset::class
    ];

}
