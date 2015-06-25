<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace humhub\core\content\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Stream extends AssetBundle
{
    public $sourcePath = '@humhub/core/content/assets/resources';
    public $css = [
    ];
    public $js = [
        'stream.js',
        'wall.js',
        'utils.js'
    ];
}
