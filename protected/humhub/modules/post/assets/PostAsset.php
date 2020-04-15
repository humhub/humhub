<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\assets;

use humhub\components\assets\AssetBundle;

class PostAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@post/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.post.js'
    ];
}
