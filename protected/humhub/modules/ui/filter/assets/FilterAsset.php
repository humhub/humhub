<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\filter\assets;


use yii\web\AssetBundle;

class FilterAsset extends AssetBundle
{
    public $sourcePath = '@ui/filter/resources';
    public $css = [];
    public $js = [
        'js/humhub.ui.filter.js'
    ];

    public $depends = [
        'humhub\assets\CoreApiAsset'
    ];
}
