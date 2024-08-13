<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\assets;

use humhub\components\assets\AssetBundle;

/**
 * Assets for like related resources.
 *
 * @since 1.2
 * @author buddha
 */
class LikeAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@like/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.like.js'
    ];

}
