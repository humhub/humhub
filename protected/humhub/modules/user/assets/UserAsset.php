<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\assets;

use humhub\components\assets\AssetBundle;

class UserAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@user/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.user.js',
        'js/humhub.user.login.js',
    ];

}
