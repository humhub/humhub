<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use yii\db\ActiveRecord;

/**
 * Class ContentTagAddition
 *
 * @property int $id
 * @perperty integer $tag_id
 *
 * @since 1.2.2
 * @author buddha
 */
class ContentTagAddition extends ActiveRecord
{
    public function setTag(ContentTag $tag)
    {
        $this->tag_id = $tag->id;
    }
}
