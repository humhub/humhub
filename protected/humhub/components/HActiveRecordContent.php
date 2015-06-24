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
class HActiveRecordContent extends HActiveRecord
{

    /**
     * Scopes for User Related selector
     */
    const SCOPE_USER_RELEATED_MINE = 1;
    const SCOPE_USER_RELEATED_SPACES = 2;
    const SCOPE_USER_RELEATED_FOLLOWED_SPACES = 3;
    const SCOPE_USER_RELEATED_FOLLOWED_USERS = 4;
    const SCOPE_USER_RELEATED_OWN_PROFILE = 5;

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
     * Constructor
     *
     * @param type $scenario
     */
    public function __construct($scenario = 'insert')
    {
        $this->content = new Content();
        $this->content->setUnderlyingObject($this);
        parent::__construct($scenario);

        $this->attachBehavior('HFollowableBehavior', array(
            'class' => 'application.modules_core.user.behaviors.HFollowableBehavior',
        ));
    }

    /**
     * Returns a short textual title for this content.
     * Default goes to "Classname (Id)"
     *
     * It should be overwritten for a more representative text.
     *
     * @return type
     */
    public function getContentTitle()
    {
        $objectModel = get_class($this); // e.g. Post
        return $objectModel . " (" . $this->id . ")";
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
        $this->content = Content::model()->findByAttributes(array('object_model' => get_class($this), 'object_id' => $this->getPrimaryKey()));
        
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
    public function afterSave()
    {
        // Auto follow this content
        if (get_class($this) != 'Activity') {
            $this->follow($this->created_by);
        }

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

    public function beforeValidate()
    {
        return parent::beforeValidate();
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

        return CMap::mergeArray(parent::getErrors(), $this->content->getErrors());
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

    /**
     * Scope to limit returned content to given content container.
     * It also respects visibility of content against current user.
     *
     * @param HActiveRecordContentContainer $container
     */
    public function contentContainer($container)
    {
        if ($container == null) {
            throw new CException("No container given!");
        }

        $criteria = new CDbCriteria();
        $criteria->join = "LEFT JOIN content ON content.object_model='" . get_class($this) . "' AND content.object_id=t." . $this->tableSchema->primaryKey;

        if ($container instanceof Space) {
            $criteria->join .= " LEFT JOIN space_membership ON content.space_id=space_membership.space_id AND space_membership.user_id=:userId";
            $criteria->condition = "content.space_id=" . $container->id;
            $criteria->condition .= " AND ((space_membership.status=3 AND content.visibility=0) OR content.visibility=1)";
        } elseif ($container instanceof User) {
            $criteria->condition = 'content.user_id=' . $container->id . ' AND (content.space_id="" OR content.space_id IS NULL)';
            $criteria->condition .= ' AND (content.user_id=:userId OR content.visibility=1)';
        } else {
            throw new CException("Could not determine container type!");
        }

        $criteria->params[':userId'] = Yii::app()->user->id;
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }

    /**
     * Scope to find user related content accross content containers.
     *
     * @since 0.9
     * @param array $includes Array of self::SCOPE_USER_RELATED_*.
     */
    public function userRelated($includes = array(HActiveRecordContent::SCOPE_USER_RELEATED_FOLLOWED_SPACES))
    {
        $criteria = new CDbCriteria();
        $criteria->join = "LEFT JOIN content ON content.object_model='" . get_class($this) . "' AND content.object_id=t." . $this->tableSchema->primaryKey;

        // Attach selectors
        $selectorSql = array();

        if (in_array(HActiveRecordContent::SCOPE_USER_RELEATED_MINE, $includes)) {
            $selectorSql[] = 'content.user_id=' . Yii::app()->user->id;
        }

        if (in_array(HActiveRecordContent::SCOPE_USER_RELEATED_OWN_PROFILE, $includes)) {
            $selectorSql[] = 'content.user_id=' . Yii::app()->user->id . ' and content.space_id IS NULL';
        }
        if (in_array(self::SCOPE_USER_RELEATED_SPACES, $includes)) {
            $selectorSql[] = 'content.space_id IN (SELECT space_id FROM space_membership sm WHERE sm.user_id=' . Yii::app()->user->id . ' AND sm.status =' . SpaceMembership::STATUS_MEMBER . ')';
        }
        if (in_array(self::SCOPE_USER_RELEATED_FOLLOWED_SPACES, $includes)) {
            $selectorSql[] = 'content.visibility=1 AND content.space_id IN (SELECT object_id FROM user_follow sf WHERE sf.object_model="Space" AND sf.user_id=' . Yii::app()->user->id . ')';
        }
        if (in_array(self::SCOPE_USER_RELEATED_FOLLOWED_USERS, $includes)) {
            $selectorSql[] = 'content.visibility=1 AND content.space_id IS NULL AND content.user_id IN (SELECT object_id FROM user_follow uf WHERE uf.object_model="User" AND uf.user_id=' . Yii::app()->user->id . ')';
        }

        if (count($selectorSql) != 0) {
            $criteria->condition .= "(" . join(') OR (', $selectorSql) . ")";
        } else {
            // If none valid include is given, ensure returned data is empty
            $criteria->condition .= " 1=2 ";
            Yii::log("userRelated Scope called without valid includes!", CLogger::LEVEL_WARNING);
        }

        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }


}

?>
