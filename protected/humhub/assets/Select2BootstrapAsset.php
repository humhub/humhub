<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * select2 bootstrap asset
 *
 * @author buddha
 * @deprecated since 1.5
 */
class Select2BootstrapAsset extends AssetBundle
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
     * This is only included for backward compatibility for former 1.2 beta themes.
     * After removing this, all themes have to be rebuilt.
     * @deprecated since version 1.2.0-beta.3
     */
    public $css = ['css/select2Theme/select2-humhub.css'];

    /**
    * @inheritdoc
    */
    public $depends = [
        Select2Asset::class
    ];

}
