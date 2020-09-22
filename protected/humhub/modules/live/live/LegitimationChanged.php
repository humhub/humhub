<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\live;

use humhub\modules\live\components\LiveEvent;
use humhub\modules\content\models\Content;

/**
 * Live event for push driver when contentContainerId legitimation was changed
 *
 * @since 1.3
 */
class LegitimationChanged extends LiveEvent
{

    /**
     * @var array the legitimation array
     */
    public $legitimation;

    /**
     * @var int the user id
     */
    public $userId;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $this->visibility = Content::VISIBILITY_OWNER;
    }

}
