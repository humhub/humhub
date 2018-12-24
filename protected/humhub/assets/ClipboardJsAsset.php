<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * Clipboard JS
 *
 * @author luke
 */
class ClipboardJsAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/clipboard-polyfill';

    /**
     * @inheritdoc
     */
    public $js = ['build/clipboard-polyfill.js'];

    /**
     * @inheritdoc
     */
    public $css = [];

}
