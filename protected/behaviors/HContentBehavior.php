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
 * Behavior for Contents Objects
 *
 * Generally binds a Content Object to the given ActiveRecord
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.behaviors
 * @since 0.5
 */
class HContentBehavior extends HActiveRecordBehavior {

    private $_content = null;

    /**
     * Before deleting of a content object (e.g. post, activity, ...)
     *
     * @param type $event
     */
    public function beforeDelete($event) {
        parent::beforeDelete($event);

        // Make sure corresponding content object is loaded
        // So we can delete is afterwards
        $this->getContentMeta();
    }

    /**
     * After deleting of a content object (e.g. post, activity, ...)
     *
     * @param type $event
     */
    public function afterDelete($event) {
        parent::afterDelete($event);

        // Search Stuff
        if ($this->getOwner() instanceof ISearchable) {

            if (!$this->getOwner()->isNewRecord)
                HSearch::getInstance()->deleteModel($this->getOwner());

            HSearch::getInstance()->addModel($this->getOwner());
        }

        $this->getContentMeta()->delete();
    }

    /**
     * After saving of a content object (e.g. post, activity, ...)
     *
     * @param type $event
     */
    public function afterSave($event) {

        parent::afterSave($event);

        if ($this->getContentMeta()->isNewRecord) {
            $this->getContentMeta()->object_model = get_class($this->getOwner());
            $this->getContentMeta()->object_id = $this->getOwner()->id;

            // Because Content Model dont set this automatically atm
            // For update reasons
            // On later release remove this, and change Content Model to set this automatically
            $this->getContentMeta()->created_at = $this->getOwner()->created_at;
            $this->getContentMeta()->created_by = $this->getOwner()->created_by;
            $this->getContentMeta()->updated_at = $this->getOwner()->updated_at;
            $this->getContentMeta()->updated_by = $this->getOwner()->updated_by;
        }

        $this->getContentMeta()->save();

        return true;
    }

    /**
     * After finding a record
     *
     * @param type $event
     */
    public function afterFind($event) {
        parent::afterFind($event);

        // Make sure corresponding content object is empty
        $this->_content = null;
    }

    /**
     * Returns corresponding Content (Meta) Object for underlying content class.
     *
     * ToDo: After Find Remove Existing ContentMeta Object
     * @return type
     */
    public function getContentMeta() {

        // Content Object already instanciated
        if ($this->_content != null)
            return $this->_content;

        // Is new record, so return empty content object
        if ($this->getOwner()->isNewRecord) {
            $this->_content = new Content;

            // some defaults
            $this->_content->sticked = 0;
            $this->_content->archived = 0;
            $this->_content->visibility = Content::VISIBILITY_PRIVATE;

            return $this->_content;
        }

        $this->_content = Content::model()->findByAttributes(array('object_model' => get_class($this->getOwner()), 'object_id' => $this->getOwner()->id));

        if ($this->_content == null) {
            throw new CHttpException(500, Yii::t('base', 'Missing underlying content object!'));
        }

        return $this->_content;
    }

}

?>