<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\assets;

use humhub\components\assets\AssetBundle;

class TopicAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@topic/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.topic.js'
    ];
}
