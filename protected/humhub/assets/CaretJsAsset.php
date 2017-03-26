<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-caretjs.js
 *
 * @author buddha
 */
class CaretjsAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/caret.js';

    /**
     * @inheritdoc
     */
    public $js = ['dist/jquery.caret.min.js'];

    /**
     * @inheritdoc
     */
    public $css = [];

}
