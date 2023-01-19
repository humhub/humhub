<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\ActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;

/**
 * HActiveRecordContentAddon is the base active record for content addons.
 *
 * Content addons are content types like Comments, Files or Likes.
 * These are always belongs to a Content object.
 *
 * Mandatory fields:
 * - object_model
 * - object_id
 * - created_by
 * - created_at
 * - updated_by
 * - updated_at
 *
 * @property-read Content $content
 * @property-read User $user
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class ContentAddonActiveRecord extends ActiveRecord implements ContentOwner
{

    /**
     * @var boolean also update underlying contents last update stream sorting
     */
    protected $updateContentStreamSort = true;

    /**
     * @var boolean automatic following of the addon creator to the related content
     */
    protected $automaticContentFollowing = true;

    /**
     * Content object which this addon belongs to
     *
     * @var Content
     */
    private $_content;

    /**
     * Source object which this ContentAddon belongs to.
     * HActiveRecordContentAddon or HActiveRecordContent Object.
     *
     * @var Mixed
     */
    private $_source;

    /**
     * Returns the content object to which this addon belongs to.
     *
     * @return Content Content AR which this Addon belongs to
     */
    public function getContent()
    {

        if ($this->_content != null) {
            return $this->_content;
        }

        if ($this->source instanceof ContentActiveRecord) {
            $this->_content = $this->source->content;
        } elseif ($this->source instanceof ContentAddonActiveRecord) {
            if ($this->source->source instanceof ContentActiveRecord) {
                $this->_content = $this->source->source->content;
            } elseif ($this->source->source->source) {
                $this->_content = $this->source->source->source->content;
            }
        }

        if ($this->_content == null) {
            throw new Exception(Yii::t('base', 'Could not find content of addon!'));
        }

        return $this->_content;
    }

    /**
     * Returns the source of this content addon.
     *
     * @return ContentAddonActiveRecord|ContentActiveRecord the model which this addon belongs to
     */
    public function getSource()
    {
        if ($this->_source != null) {
            return $this->_source;
        }

        $className = $this->object_model;
        $pk = $this->object_id;

        if ($className == "") {
            return null;
        }

        if (!class_exists($className)) {
            Yii::error("Source class of content addon not found (" . $className . ") not found!");
            return null;
        }

        $this->_source = $className::findOne(['id' => $pk]);
        return $this->_source;
    }

    /**
     * Checks if the given / or current user can delete this content.
     * Currently only the creator can remove.
     *
     * @return boolean
     */
    public function canDelete()
    {
        if ($this->created_by == Yii::$app->user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if current user can read this object
     *
     * @return boolean
     */
    public function canRead()
    {
        return $this->content->canView();
    }

    /**
     * Checks if this content addon can be changed
     *
     * @return boolean
     * @deprecated since 1.4
     */
    public function canWrite()
    {
        return $this->canEdit();
    }

    /**
     * Checks if this record can be edited
     *
     * @param User|null $user the user
     * @return boolean
     * @since 1.4
     */
    public function canEdit(User $user = null)
    {
        if ($user === null && Yii::$app->user->isGuest) {
            return false;
        } elseif ($user === null) {
            /** @var User $user */
            try {
                $user = Yii::$app->user->getIdentity();
            } catch (\Throwable $e) {
                Yii::error($e->getMessage());
                return false;
            }
        }

        if ($this->created_by == $user->id) {
            return true;
        }

        /** @var Module $contentModule */
        $contentModule = Yii::$app->getModule('content');

        if ($contentModule->adminCanEditAllContent && Yii::$app->user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Returns a title for this type of content.
     * This method should be overwritten in the content implementation.
     *
     * @return string
     */
    public function getContentName()
    {
        return $this->className();
    }

    /**
     * Returns a text preview of this content.
     * This method should be overwritten in the content implementation.
     *
     * @return string
     */
    public function getContentDescription()
    {
        return "";
    }

    /**
     * Validates
     * @param type $attributes
     * @param type $clearErrors
     * @return type
     */
    public function validate($attributes = null, $clearErrors = true)
    {

        if ($this->source != null) {
            if (!$this->source instanceof ContentAddonActiveRecord && !$this->source instanceof ContentActiveRecord) {
                $this->addError('object_model', Yii::t('base', 'Content Addon source must be instance of HActiveRecordContent or HActiveRecordContentAddon!'));
            }
        }

        return parent::validate($attributes, $clearErrors);
    }

    /**
     * After saving content addon, mark underlying content as updated.
     *
     * @return boolean
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->automaticContentFollowing) {
            $this->content->getPolymorphicRelation()->follow($this->created_by);
        }

        if ($this->updateContentStreamSort) {
            $this->getSource()->content->updateStreamSortTime();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

}
