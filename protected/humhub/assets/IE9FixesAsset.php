<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;
use yii\web\AssetBundle;

/**
 * IE9FixesAsset provides CSS/JS fixes for Internet Explorer 9 versions
 *
 * @see IEFixesAsset for older IE versions
 * @since 1.2
 * @author Luke
 * @deprecated since v1.5 not in use anymore
 */
class IE9FixesAsset extends WebStaticAssetBundle
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
        'css/ie9.css',
    ];

    /**
     * @inheritdoc
     */
    public $cssOptions = [
        'condition' => 'IE 9'
    ];

}
