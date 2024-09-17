<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\assets;

use humhub\components\assets\CoreAssetBundle;
use humhub\modules\user\assets\UserAsset;

class SpaceChooserAsset extends CoreAssetBundle
{
    public $sourcePath = '@space/resources';

    public $js = [
        'js/humhub.space.chooser.js',
    ];

    public $depends = [
        SpaceAsset::class,
        UserAsset::class,
    ];
}
