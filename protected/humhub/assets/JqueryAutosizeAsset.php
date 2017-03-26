<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-autosize
 *
 * @author buddha
 */
class JqueryAutosizeAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/autosize';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.autosize.min.js'];

    /**
     * @inheritdoc
     */
    public $css = [];

}
