<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Searchable Behavior
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.behaviors
 * @since 0.5
 */
class HSearchableBehavior extends HActiveRecordBehavior
{

    public function afterSave($event)
    {

        if ($this->getOwner() instanceof ISearchable) {
            Yii::app()->search->update($this->getOwner());
        } else {
            throw new CException("Owner of HSearchableBehavior must be implement interface ISearchable");
        }

        parent::afterSave($event);
    }

    public function afterDelete($event)
    {
        if ($this->getOwner() instanceof ISearchable) {
            Yii::app()->search->delete($this->getOwner());
        } else {
            throw new CException("Owner of HSearchableBehavior must be implement interface ISearchable");
        }
        parent::afterDelete($event);
    }

}

?>