<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-cookie
 *
 * @author buddha
 */
class JqueryCookieAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery.cookie';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.cookie.js'];

}
