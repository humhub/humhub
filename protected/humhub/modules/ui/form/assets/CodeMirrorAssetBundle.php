<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\assets;

use humhub\components\assets\CoreAssetBundle;
use humhub\modules\ui\view\components\View;

class CodeMirrorAssetBundle extends CoreAssetBundle
{
    /**
     * v1.5 compatibility defer script loading
     *
     * Migrate to HumHub AssetBundle once minVersion is >=1.5
     *
     * @var bool
     */
    public $defer = true;

    public $jsOptions = ['position' => View::POS_HEAD_BEGIN];
    public $sourcePath = '@vendor/npm-asset/codemirror';

    public $js = [
        'lib/codemirror.js',
        'addon/hint/show-hint.js',
        'addon/hint/html-hint.js',
        'addon/hint/xml-hint.js',
        'mode/xml/xml.js',
        'mode/javascript/javascript.js',
        'mode/css/css.js',
        'mode/htmlmixed/htmlmixed.js',
    ];

    public $css = [
        'lib/codemirror.css',
        'addon/hint/show-hint.css',
    ];

}
