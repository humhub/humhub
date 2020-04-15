<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;
use humhub\modules\ui\view\components\View;
use yii\jui\JuiAsset;

/**
 * select2
 *
 * @author buddha
 */
class JuiBootstrapBridgeAsset extends WebStaticAssetBundle
{
    /**
     * @inheritdoc
     */
    public $defer = false;

    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

    /**
     * @inheritdoc
     */
    public $jsPosition = View::POS_HEAD;

    /**
     * @inheritdoc
     */
    public $js = ['js/jui.bootstrap.bridge.js'];

    public $depends = [
        JuiAsset::class
    ];

}
