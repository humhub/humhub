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
class JqueryTimeEntryAsset extends AssetBundle
{

    public $publishOptions = [
        'forceCopy' => false
    ];

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery-timeentry';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.plugin.js', 'jquery.timeentry.js'];

    /**
     * @inheritdoc
     */
    public $css = ['jquery.timeentry.css'];

}
