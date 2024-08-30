<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\CoreAssetBundle;

/**
 * animate.css
 *
 * @author buddha
 */
class SwipedEventsAssets extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/swiped-events/dist';

    /**
     * @inheritdoc
     */
    public $js = ['swiped-events.min.js'];

}
