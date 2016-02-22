<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
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
     * @see \humhub\modules\content\widgets\WallEntry
     * @var string WallEntry widget class
     */
    public $wallEntryClass = "";

    /**
     * Should this content automatically added to the wall on creation.
     * Note: you need to also specify the wallEntryClass attribute! 
     * 
     * @var boolean
     */
    public $autoAddToWall = true;

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
                $content->setPolymorphicRelation($this);
            }
            return $content;
        }
        return parent::__get($name);
    }

    /**
     * Returns the name of this type of content.
     * You need to override this method in your content implementation.
     *
     * @return string the name of the content
     */
    public function getContentName()
    {
        return $this->className();
    }

    /**
     * Returns a description of this particular content.
     * This will be used to create a text preview of the content record. (e.g. in Activities or Notifications)
     * You need to override this method in your content implementation.
     *
     * @return string description of this content
     */
    public function getContentDescription()
    {
        return "";
    }

    /**
     * Returns the wall output widget of this content.
     * 
     * @param array $params optional parameters for WallEntryWidget
     * @return string
     */
    public function getWallOut($params = [])
    {
        $wallEntryWidget = $this->getWallEntryWidget();
        if ($wallEntryWidget !== null) {
            Yii::configure($wallEntryWidget, $params);
            return $wallEntryWidget->renderWallEntry();
        }
        return "";
    }

    /**
     * Returns the assigned wall entry widget instance
     * 
     * @return \humhub\modules\content\widgets\WallEntry
     */
    public function getWallEntryWidget()
    {
        if ($this->wallEntryClass !== '') {
            $class = $this->wallEntryClass;
            $widget = new $class;
            $widget->contentObject = $this;
            return $widget;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {

        $content = Content::findOne(['object_id' => $this->id, 'object_model' => $this->className()]);
        if ($content !== null) {
            $content->delete();
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
            $this->follow($this->content->created_by);
        }

        // Set polymorphic relation
        if ($insert) {
            $this->content->object_model = $this->className();
            $this->content->object_id = $this->getPrimaryKey();
        }

        // Always save content
        $this->content->save();

        parent::afterSave($insert, $changedAttributes);

        if ($insert && $this->autoAddToWall && $this->wallEntryClass != "") {
            $this->content->addToWall();
        }
    }

    /**
     * Related Content model
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['object_id' => 'id'])->andWhere(['content.object_model' => self::className()]);
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
