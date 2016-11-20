<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\assets;

use yii\web\AssetBundle;

class UserPickerAsset extends AssetBundle
{
    public $sourcePath = '@user/resources';
    public $css = [];
    public $js = [
        'js/humhub.user.picker.js'
    ];
    
    public $depends = [
        'humhub\assets\Select2BootstrapAsset'
    ];
}
