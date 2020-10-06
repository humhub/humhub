<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\assets;

use humhub\assets\CoreExtensionAsset;
use humhub\components\assets\AssetBundle;
use humhub\modules\content\assets\ContentAsset;
use humhub\modules\content\assets\ContentContainerAsset;
use humhub\modules\ui\filter\assets\FilterAsset;
use humhub\modules\user\assets\UserAsset;


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

    public $depends = [
        ContentAsset::class,
        ContentContainerAsset::class,
        FilterAsset::class,
        UserAsset::class,
        CoreExtensionAsset::class
    ];


}
