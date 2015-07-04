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

namespace humhub\modules\content\components\activerecords;

use Yii;
use humhub\components\ActiveRecord;

/**
 * HActiveRecordContent is the base AR for all content records.
 *
 * Each model which represents a piece of content should derived from it.
 * (e.g. Post, Question, Task, Note, ...)
 *
 * It automatically binds a Content model to each instance.
 *
 * The Content Model is responsible for:
 *  - Content to Container (User/Space) Binding
 *  - Access Controls
 *  - Wall Integration
 *  - ...
 * (See Content Model for more details.)
 *
 * Note: Comments, Likes or Files are NOT Content Objects.
 * These objects are ContentAddons which always belongs to one Content Object.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class Content extends ActiveRecord implements \humhub\modules\content\interfaces\ContentTitlePreview
{

    /**
     * Should this content automatically added to the wall.
     *
     * @var boolean
     */
    public $autoAddToWall = true;

    /**
     * Corresponding Content ActiveRecord
     *
     * @var \humhub\modules\content\models\Content
     */
    public $content = null;

    /**
     * If this content is display inside the wall and should be editable
     * there, specify a edit route here.
     *
     * The primary key (id) will automatically added to the url.
     *
     * @var string the route to edit this content
     */
    public $wallEditRoute = "";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->content = new \humhub\modules\content\models\Content();
        $this->content->setUnderlyingObject($this);

        $this->attachBehavior('FollowableBehavior', \humhub\modules\user\behaviors\Followable::className());
    }

    /**
     * Returns a title for this type of content.
     * This method should be overwritten in the content implementation.
     *
     * @return string
     */
    public function getContentTitle()
    {
        return $this->className();
    }

    /**
     * Returns a text preview of this content.
     * This method should be overwritten in the content implementation.
     *
     * @return string
     */
    public function getContentPreview($maxLength = 0)
    {
        return "";
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
    public function getWallOut()
    {
        return "Default Wall Output for Class " . get_class($this);
    }

    public function afterFind()
    {
        $this->content = \humhub\modules\content\models\Content::findOne(['object_model' => $this->className(), 'object_id' => $this->getPrimaryKey()]);

        if ($this->content !== null) {
            $this->content->setUnderlyingObject($this);
        }

        parent::afterFind();
    }

    public function afterDelete()
    {
        if ($this->content !== null) {
            $this->content->delete();
        }
        parent::afterDelete();
    }

    /**
     * After Saving of records of type content, automatically add/bind the
     * corresponding content to it.
     *
     * If the automatic wall adding (autoAddToWall) is enabled, also create
     * wall entry for this content.
     *
     * NOTE: If you overwrite this method, e.g. for creating activities ensure
     * this (parent) implementation is invoked BEFORE your implementation. Otherwise
     * the Content Object is not available.
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Auto follow this content
        if ($this->className() != \humhub\modules\activity\models\Activity::className()) {
            $this->follow($this->created_by);
        }

        if ($insert) {
            $this->content->user_id = $this->created_by;
            $this->content->object_model = $this->className();
            $this->content->object_id = $this->getPrimaryKey();
            $this->content->created_at = $this->created_at;
            $this->content->created_by = $this->created_by;
        }

        $this->content->updated_at = $this->updated_at;
        $this->content->updated_by = $this->updated_by;

        $this->content->save();
        parent::afterSave($insert, $changedAttributes);

        if ($insert && $this->autoAddToWall) {
            $this->content->addToWall();
        }

        // When Space Content, update also last visit
        if ($this->content->space_id) {
            $membership = $this->content->space->getMembership();
            if ($membership) {
                $membership->updateLastVisit();
            }
        }
    }

    public function afterValidate()
    {
        if (!$this->content->validate())
            return false;

        if (!parent::afterValidate()) {
            return false;
        }

        return true;
    }

    public function getErrors($attribute = null)
    {
        if ($attribute != null) {
            return parent::getErrors($attribute);
        }

        return \yii\helpers\ArrayHelper::merge(parent::getErrors(), $this->content->getErrors());
    }

    public function validate($attributes = null, $clearErrors = true)
    {
        if (parent::validate($attributes, $clearErrors) && $this->content->validate($attributes, $clearErrors))
            return true;

        return false;
    }

    public function hasErrors($attribute = null)
    {
        if ($attribute != null)
            return parent::hasErrors($attribute);

        return parent::hasErrors() || $this->content->hasErrors();
    }

}

?>
