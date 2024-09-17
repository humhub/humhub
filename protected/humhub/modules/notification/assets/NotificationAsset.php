<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\assets;

use humhub\components\assets\CoreAssetBundle;

class NotificationAsset extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@notification/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.notification.js',
    ];
}
