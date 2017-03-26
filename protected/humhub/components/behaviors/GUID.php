<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use yii\db\ActiveRecord;
use yii\base\Behavior;

/**
 * GUID Behavior
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.behaviors
 * @since 0.5
 */
class GUID extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'setGuid',
            ActiveRecord::EVENT_BEFORE_INSERT => 'setGuid',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setGuid',
        ];
    }

    public function setGuid($event) {
        if ($this->owner->isNewRecord) {
            if ($this->owner->guid == "") {
                $this->owner->guid = \humhub\libs\UUID::v4();
            }
        }
    }

}