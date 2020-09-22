<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;

/**
 * jQuery-nicescroll
 *
 * @author luke
 */
class JqueryNiceScrollAsset extends WebStaticAssetBundle
{

    /**
     * @inheritdoc
     */
    public $js = ['js/jquery.nicescroll.min.js'];

}
