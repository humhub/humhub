<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\assets;

use humhub\components\assets\AssetBundle;

class SpaceAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@space/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.space.js'
    ];
}
