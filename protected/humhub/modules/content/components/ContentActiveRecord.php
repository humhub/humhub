<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use humhub\components\ActiveRecord;
use humhub\modules\content\models\Content;

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
class ContentActiveRecord extends ActiveRecord implements \humhub\modules\content\interfaces\ContentTitlePreview
{

    /**
     * Should this content automatically added to the wall.
     *
     * @var boolean
     */
    public $autoAddToWall = true;

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
        $this->attachBehavior('FollowableBehavior', \humhub\modules\user\behaviors\Followable::className());
    }

    public function __get($name)
    {
        /**
         * Ensure there is always a corresponding Content 
         */
        if ($name == 'content') {
            $content = parent::__get('content');
            if (!$this->isRelationPopulated('content') || $content === null) {
                $content = new Content();
                $this->populateRelation('content', $content);
            }
            return $content;
        }
        return parent::__get($name);
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

    public function afterDelete()
    {
        if ($this->content !== null) {
            $this->content->delete();
        }
        parent::afterDelete();
    }

    public function beforeSave($insert)
    {

        if (!$this->content->validate()) {
            throw new Exception("Could not validate associated Content Record! (" . print_r($this->content->getErrors(), 1) . ")");
        }

        return parent::beforeSave($insert);
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

    public function getContent()
    {
        return $this->hasOne(Content::className(), ['object_id' => 'id'])->andWhere(['object_model' => self::className()]);
    }

}

?>
