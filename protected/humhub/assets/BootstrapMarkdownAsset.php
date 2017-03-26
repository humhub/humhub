<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * Bootstrap Markdown
 *
 * @see https://github.com/toopay/bootstrap-markdown
 * @author luke
 */
class BootstrapMarkdownAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/bootstrap-markdown';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/bootstrap-markdown.js',
    ];

    /**
     * @inheritdoc
     */
    public $css = ['css/bootstrap-markdown.min.css'];

}
