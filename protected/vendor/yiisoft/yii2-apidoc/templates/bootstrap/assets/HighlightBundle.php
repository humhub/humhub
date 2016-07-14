<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap\assets;

use yii\web\View;

/**
 * The asset bundle for the highlight.js styles.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0.5
 */
class HighlightBundle extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/scrivo/highlight.php/styles';
    public $css = [
        'solarized_light.css'
    ];
}
