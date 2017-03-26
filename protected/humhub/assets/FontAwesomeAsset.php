<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * Fontawesome
 *
 * @author luke
 */
class FontAwesomeAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/fontawesome';

    /**
     * @inheritdoc
     */
    public $css = ['css/font-awesome.min.css'];

}
