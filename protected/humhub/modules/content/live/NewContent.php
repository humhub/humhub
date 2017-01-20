<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\live;

use humhub\modules\live\components\LiveEvent;

/**
 * Live event for new contents
 * 
 * @since 1.2
 */
class NewContent extends LiveEvent
{

    /**
     * @var int the id of the new content
     */
    public $contentId;

}
