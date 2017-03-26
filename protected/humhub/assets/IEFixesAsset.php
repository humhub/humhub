<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * Html5shivAsssets - the HTML5 shim, for IE6-8 support of HTML5 elements
 *
 * @since 1.2
 * @author Luke
 */
class IEFixesAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $basePath = '@webroot-static';

    /**
     * @inheritdoc
     */
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/ie.css',
    ];

    /**
     * @inheritdoc
     */
    public $cssOptions = [
        'condition' => 'lt IE 9'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\Html5shivAsset',
    ];

}
