<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\assets;

use yii\web\AssetBundle;
use yii\web\View;

class LogAsset extends AssetBundle
{

    public $jsOptions = [
        'position' => View::POS_END
    ];

    public $sourcePath = '@admin/resources';
    public $css = [];
    public $js = [
        'js/humhub.admin.log.js'
    ];

}
