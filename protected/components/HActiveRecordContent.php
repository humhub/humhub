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
 * HActiveRecordContent is the base AR for all content models.
 *
 * Each model which represents a content should derived from it.
 * (e.g. Post, Question, Task, Note, ...)
 *
 * It automatically binds a Content model to each instance.
 *
 * The Content Model is responsible for:
 *  - Content to User/Space Binding
 *  - Access Controlling
 *  - Wall Integration
 *  - ...
 * (See Content Model for more details.)
 *
 * Note: Comments, Likes or Files are NOT Content Objects. These objects are
 * ContentAddons which always belongs to one Content Object.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HActiveRecordContent extends HActiveRecord {

    /**
     * Should this content automatically added to the wall.
     *
     * @var boolean
     */
    public $autoAddToWall = true;

    /**
     * Corresponding Content ActiveRecord
     *
     * @var Content
     */
    public $content = null;

    public function __construct($scenario = 'insert') {
        $this->content = new Content($scenario);
        parent::__construct($scenario);
    }

    /**
     * Returns a short textual title for this content.
     * Default goes to "Classname (Id)"
     *
     * It should be overwritten for a more representative text.
     *
     * @return type
     */
    public function getContentTitle() {
        $objectModel = get_class($this); // e.g. Post
        return $objectModel . " (" . $this->getOwner()->id . ")";
    }

    /**
     * If the content should also displayed on a wall, overwrite this
     * method and produce a wall output.
     *
     * e.g.
     * return Yii::app()->getController()->widget('application.modules.myModule.MyContentWidget',
     *      array('myContent' => $this),
     *      true
     * );
     *
     * @return type
     */
    public function getWallOut() {
        return "Default Wall Output for Class " . get_class($this->getOwner());
    }

    public function afterFind() {
        $this->content = Content::model()->findByAttributes(array('object_model' => get_class($this), 'object_id' => $this->getPrimaryKey()));
        parent::afterFind();
    }

    public function afterDelete() {
        $this->content->delete();
        parent::afterDelete();
    }

    public function afterSave() {

        if ($this->isNewRecord) {
            $this->content->user_id = $this->created_by;
            $this->content->object_model = get_class($this);
            $this->content->object_id = $this->getPrimaryKey();
            $this->content->created_at = $this->created_at;
            $this->content->created_by = $this->created_by;
        }

        $this->content->updated_at = $this->updated_at;
        $this->content->updated_by = $this->updated_by;


        $this->content->save();
        parent::afterSave();

        if ($this->isNewRecord && $this->autoAddToWall) {
            $this->content->addToWall();
        }

        // When Space Content, update also last visit
        if ($this->content->space_id) {
            $membership = $this->content->space->getMembership(Yii::app()->user->id);
            if ($membership) {
                $membership->updateLastVisit();
            }
        }


    }

    public function beforeValidate() {
        return parent::beforeValidate();
    }

    public function afterValidate() {
        if (!$this->content->validate())
            return false;

        if (!parent::afterValidate()) {
            return false;
        }

        return true;
    }

    public function getErrors($attribute = null) {
        if ($attribute != null) {
            return parent::getErrors($attribute);
        }

        return CMap::mergeArray(parent::getErrors(), $this->content->getErrors());
    }

    public function validate($attributes = null, $clearErrors = true) {
        if (parent::validate($attributes, $clearErrors) && $this->content->validate($attributes, $clearErrors))
            return true;

        return false;
    }

    public function hasErrors($attribute = null) {
        if ($attribute != null)
            return parent::hasErrors($attribute);

        return parent::hasErrors() || $this->content->hasErrors();
    }

}

?>
