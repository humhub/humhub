<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 19.09.2017
 * Time: 16:00
 */

namespace humhub\modules\mobile\assets;


use yii\web\AssetBundle;

class MobileAsset extends AssetBundle
{
    public $publishOptions = [
        'forceCopy' => true
    ];

    public $sourcePath = '@mobile/resources';
    public $css = [];
    public $js = [
        'js/humhub.mobile.js'
    ];

}