<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\assets;

use yii\web\AssetBundle;

/**
 * Stream related assets.
 * 
 * @since 1.2
 * @author buddha
 */
class StreamAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@stream/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.stream.StreamEntry.js',
        'js/humhub.stream.StreamRequest.js',
        'js/humhub.stream.Stream.js',
        'js/humhub.stream.wall.js',
        'js/humhub.stream.SimpleStream.js',
        'js/humhub.stream.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\modules\content\assets\ContentAsset',
        'humhub\modules\ui\filter\assets\FilterAsset'
    ];

}
