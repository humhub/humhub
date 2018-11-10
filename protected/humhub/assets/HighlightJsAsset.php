<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;

use yii\web\AssetBundle;

class HighlightJsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/highlightjs-official';

    /**
     * @inheritdoc
     */
    public $js = ['highlight.pack.min.js'];

    /**
     * @inheritdoc
     */
    public $css = ['styles/github.css'];
}
