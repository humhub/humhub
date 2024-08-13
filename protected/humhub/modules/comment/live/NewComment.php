<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\live;

use humhub\modules\live\components\LiveEvent;

/**
 * Live event for new comments
 *
 * @since 1.2
 */
class NewComment extends LiveEvent
{

    /**
     * @var int the id of the new comment
     */
    public $commentId;

    /**
     * @var int the id of the content
     */
    public $contentId;

}
