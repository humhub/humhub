<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use yii\base\Exception;
use humhub\components\ActiveRecord;
use humhub\modules\content\models\Content;

/**
 * ContentActiveRecord is the base ActiveRecord [[\yii\db\ActiveRecord]] for Content.
 * 
 * Each instance automatically belongs to a [[\humhub\modules\content\models\Content]] record which is accessible via the content attribute.
 * This relations will be automatically added/updated and is also available before this record is inserted.
 * 
 * The Content record/model holds all neccessary informations/methods like:
 * - Related ContentContainer (must be set before save!)
 * - Visibility
 * - Meta informations (created_at, created_by, ...)
 * - Wall handling, archiving, sticking, ...
 * 
 * Before adding a new ContentActiveRecord instance, you need at least assign an ContentContainer.
 * 
 * Example:
 * 
 * ```php
 * $post = new Post();
 * $post->content->container = $space;
 * $post->content->visibility = Content::VISIBILITY_PRIVATE; // optional
 * $post->message = "Hello world!";
 * $post->save();
 * ```
 * 
 * Note: If the underlying Content record cannot be saved or validated an Exception will thrown.
 * 
 * @author Luke
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        if ($this->content !== null) {
            $this->content->delete();
        }
        parent::afterDelete();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {

        if (!$this->content->validate()) {
            throw new Exception("Could not validate associated Content Record! (" . print_r($this->content->getErrors(), 1) . ")");
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Auto follow this content
        if ($this->className() != \humhub\modules\activity\models\Activity::className()) {
            $this->follow($this->content->user_id);
        }

        // Set polymorphic relation
        if ($insert) {
            $this->content->object_model = $this->className();
            $this->content->object_id = $this->getPrimaryKey();
        }

        // Always save content
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

    /**
     * Related Content model
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['object_id' => 'id'])->andWhere(['object_model' => self::className()]);
    }

    /**
     * Returns an ActiveQueryContent to find content.
     * 
     * @inheritdoc
     * 
     * @return ActiveQueryContent
     */
    public static function find()
    {
        return Yii::createObject(ActiveQueryContent::className(), [get_called_class()]);
    }

}

?>
