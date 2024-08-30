<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\CoreAssetBundle;
use yii\web\View;

/**
 * jquery-knob
 *
 * @author luke
 */
class JqueryKnobAsset extends CoreAssetBundle
{
    public $jsOptions = ['position' => View::POS_BEGIN];

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/jquery-knob';

    /**
     * @inheritdoc
     */
    public $js = ['dist/jquery.knob.min.js'];

    public $depends = ['humhub\assets\AppAsset'];

}
