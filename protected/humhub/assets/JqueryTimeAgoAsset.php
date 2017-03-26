<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * TimeAgo Asset Bundle
 *
 * @author luke
 */
class JqueryTimeAgoAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery-timeago';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.timeago.js'];

}
