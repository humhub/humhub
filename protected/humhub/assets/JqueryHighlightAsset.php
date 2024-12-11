<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-highlight
 *
 * @author buddha
 */
class JqueryHighlightAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/jquery-highlight';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.highlight.js'];
}
