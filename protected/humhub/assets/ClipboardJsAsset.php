<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

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
    public $sourcePath = '@npm/clipboard-polyfill/dist/main';

    /**
     * @inheritdoc
     */
    public $js = ['clipboard-polyfill.js'];

    public $publishOptions = [
        'only' => ['clipboard-polyfill.js', 'clipboard-polyfill.js.map']
    ];

}
