<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\events;

use humhub\modules\content\models\Content;
use yii\base\ModelEvent;

class ContentEvent extends ModelEvent
{
    public Content $content;
}
