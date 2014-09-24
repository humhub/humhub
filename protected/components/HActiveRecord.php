<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * HActiveRecord is an extended version of the CActiveRecord.
 *
 * Each HActiveRecord has some extra meta database fields like:
 *  - created by
 *  - created_at
 *  - updated_by
 *  - updated_at
 *
 * The underlying HActiveRecord table must have these fields.
 *
 * @package humhub.components
 * @since 0.5
 */
abstract class HActiveRecord extends CActiveRecord {

    /**
     * Inits the active records and registers the event interceptor
     */
    public function init()
    {

        parent::init();

        // Intercept this controller
        Yii::app()->interceptor->intercept($this);
    }

    /**
     * Prepares create_time, create_user_id, update_time and update_user_id attributes before performing validation.
     */
    protected function beforeValidate()
    {
     
        $userID = ! empty(Yii::app()->user) ? Yii::app()->user->id : 0;

        // check if this object has 'created_by' if it does and 
        // its a new record please set it to current userID
        if ($this->hasAttribute('created_by') && $this->isNewRecord)
            $this->created_by = $userID;
        
        // check if this object has 'updated_by' if it does and 
        // its NOT a new record please update it to current userID
        if ($this->hasAttribute('updated_by') && !$this->isNewRecord)
            $this->updated_by = $userID;

        return parent::beforeValidate();
    }

    public function behaviors()
    {
        return array(
            'CTimestampBehavior' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'created_at',
                'updateAttribute' => 'updated_at',
            )
        );
    }

    /**
     * Returns the creator user of this active record
     * (Faster than relation() because cached).
     *
     * @return User
     */
    public function getCreator()
    {
        return User::model()->findByPk($this->created_by);
    }

    /**
     * Returns the updater user of this active record
     * (Faster than relation() because cached).
     *
     * @return User
     */
    public function getUpdater()
    {
        return User::model()->findByPk($this->updated_by);
    }

    /**
     * Returns a unique id for this record
     *
     * @return String Unique Id of this record
     */
    public function getUniqueId()
    {
        return get_class($this) . "_" . $this->id;
    }

}