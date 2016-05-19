<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\console\Exception;

/**
 * Searchable Behavior
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.behaviors
 * @since 0.5
 */
class Searchable extends Behavior
{

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
        ];
    }

    public function afterSave($event)
    {

        if ($this->owner instanceof \humhub\modules\search\interfaces\Searchable) {
            Yii::$app->search->update($this->owner);
        } else {
            throw new Exception("Owner of HSearchableBehavior must be implement interface ISearchable");
        }
    }

    public function afterDelete($event)
    {
        if ($this->owner instanceof \humhub\modules\search\interfaces\Searchable) {
            Yii::$app->search->delete($this->owner);
        } else {
            throw new Exception("Owner of HSearchableBehavior must be implement interface ISearchable");
        }
    }

}
