<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\assets;

use yii\web\AssetBundle;

class NotificationAsset extends AssetBundle
{

    public $sourcePath = '@notification/resources';
    public $css = [];
    public $js = [
        'js/humhub.notification.js'
    ];
}
