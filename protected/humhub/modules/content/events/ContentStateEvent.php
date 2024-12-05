<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\events;

use humhub\modules\content\models\Content;

class ContentStateEvent extends ContentEvent
{
    public Content $content;

    public int $newState;
    public ?int $previousState;
}
