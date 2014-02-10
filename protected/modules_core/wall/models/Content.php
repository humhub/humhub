<?php

/**
 * This is the model class for table "content".
 *
 * The followings are the available columns in table 'content':
 * @property integer $id
 * @property string $guid
 * @property string $object_model
 * @property integer $object_id
 * @property integer $visibility
 * @property integer $sticked
 * @property string $archived
 * @property integer $space_id
 * @property integer $user_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.wall.models
 * @since 0.5
 */
class Content extends CActiveRecord {
    // Visibility Modes

    const VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC = 1;

    /**
     * @var User stores the create user
     */
    protected $_user;

    /**
     * Inits the content record
     */
    public function init() {

        parent::init();

        // Intercept this controller
        Yii::app()->interceptor->intercept($this);
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Content the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'HUnderlyingObjectBehavior' => array(
                'class' => 'application.behaviors.HUnderlyingObjectBehavior',
                'mustBeInstanceOf' => array('HContentBehavior'),
            ),
        );
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'content';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('object_model, object_id', 'required'),
            array('object_id, visibility, sticked, space_id, user_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('guid', 'length', 'max' => 45),
            array('object_model', 'length', 'max' => 100),
            array('archived, created_at, updated_at', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, guid, object_model, object_id, visibility, sticked, archived, space_id, user_id, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'workspace' => array(self::BELONGS_TO, 'Space', 'space_id'),
            'wallEntries' => array(self::HAS_MANY, 'WallEntry', 'content_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'guid' => 'Guid',
            'object_model' => 'Object Model',
            'object_id' => 'Object',
            'visibility' => 'Visibility',
            'sticked' => 'Sticked',
            'archived' => 'Archived',
            'space_id' => 'Space',
            'user_id' => 'User',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('guid', $this->guid, true);
        $criteria->compare('object_model', $this->object_model, true);
        $criteria->compare('object_id', $this->object_id);
        $criteria->compare('visibility', $this->visibility);
        $criteria->compare('sticked', $this->sticked);
        $criteria->compare('archived', $this->archived, true);
        $criteria->compare('space_id', $this->space_id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns a SIContent Object by given Class and ID
     *
     * @param type $className
     * @param type $id
     */
    static function Get($className, $id) {

        $content = Content::model()->findByAttributes(array('object_model' => $className, 'object_id' => $id));

        if ($content != null)
            return $className::model()->findByPk($id);

        return null;
    }

    /**
     * Before Save Addons
     *
     * @return type
     */
    protected function beforeSave() {

        if ($this->isNewRecord) {

            if ($this->guid == "") {
                // Create GUID for new users
                $this->guid = UUID::v4();
            }
        }

        return parent::beforeSave();
    }

    /**
     * After saving (update/create) a Content
     *
     * @param type $event
     */
    public function afterSave() {

        // Loop over each eall entry and make sure its update_at / update_by
        // will also updated. (Sorting wall against update)
        foreach ($this->getWallEntries() as $wallEntry) {
            $wallEntry->save();
        }

        // Dirty hack
        $this->updateInvolvedUsers();

        return parent::afterSave();
    }

    /**
     * Before deleting a SIContent try to delete all corresponding SIContentAddons.
     */
    public function beforeDelete() {

        // delete also all wall entries
        foreach ($this->getWallEntries() as $entry) {
            $entry->delete();
        }

        // remove from search index
        if ($this->object_model instanceof ISearchable) {
            HSearch::getInstance()->deleteModel($this->getContentObject());
        }

        // Remove From User Content
        UserContent::model()->deleteAllByAttributes(array('object_model' => $this->object_model, 'object_id' => $this->object_id));

        // Try delete the underlying object (Post, Question, Task, ...)
        if ($this->getUnderlyingObject() !== null)
            $this->getUnderlyingObject()->delete();


        /* Replace by event
          // delete all comments
          $comments = Comment::model()->findAllByAttributes(array('object_model' => $objectModel, 'object_id' => $this->getOwner()->id));
          foreach ($comments as $comment) {
          $comment->delete();
          }

          // delete all likes
          $likes = Like::model()->findAllByAttributes(array('object_model' => $objectModel, 'object_id' => $this->getOwner()->id));
          foreach ($likes as $like) {
          $like->delete();
          }

          // delete all activities
          $activities = Activity::model()->findAllByAttributes(array('object_model' => $objectModel, 'object_id' => $this->getOwner()->id));
          foreach ($activities as $activity) {
          $activity->delete();
          }
         */

        return parent::beforeDelete();
    }

    /**
     * Updates the involved Users of this object.
     * Currently this will be execution always after saving, maybe find a better way.
     *
     * Fast Hack!11!
     *
     * ToDo: - Make it more flexible!
     *       - Make it faster!
     *       - Missing Users which likes a comment
     */
    public function updateInvolvedUsers() {

        // Collect User Ids
        $foundUsersIds = array();

        $foundUsersIds[] = $this->created_by;

        if ($this->object_model != "Activity") {
            $comments = Comment::model()->findAllByAttributes(array('object_model' => $this->object_model, 'object_id' => $this->object_id));
            foreach ($comments as $comment) {
                $foundUsersIds[] = $comment->created_by;

                // Comment Likes
                $likes = Like::model()->findAllByAttributes(array('object_model' => 'Comment', 'object_id' => $comment->id));
                foreach ($likes as $like) {
                    $foundUsersIds[] = $like->created_by;
                }
            }

            $likes = Like::model()->findAllByAttributes(array('object_model' => $this->object_model, 'object_id' => $this->object_id));
            foreach ($likes as $like) {
                $foundUsersIds[] = $like->created_by;
            }
        }

        UserContent::model()->deleteAllByAttributes(array('object_model' => $this->object_model, 'object_id' => $this->object_id));

        // Add currently involved users
        foreach (array_unique($foundUsersIds) as $userId) {
            $userContent = new UserContent();
            $userContent->object_model = $this->object_model;
            $userContent->object_id = $this->object_id;
            $userContent->user_id = $userId;
            $userContent->save();
        }

        // Rewrite!
    }

    /**
     * Returns the content base of this object
     *
     * This can be a workspace or a user.
     */
    public function getContentBase() {

        if ($this->space_id != "") {
            return Space::model()->findByPk($this->space_id);
        } else if (isset($this->user_id) && $this->user_id != "") {
            return User::model()->findByPk($this->user_id);
        }

        return null;
    }

    /**
     * Checks if the given / or current user can delete this content.
     * Currently only the creator can remove.
     *
     * @todo Ask the underlying "real" content for deletion?
     *
     * @param type $userId
     */
    public function canDelete($userId = "") {

        if (HSetting::Get('canAdminAlwaysDeleteContent', 'security') == 1 && Yii::app()->user->isAdmin())
            return true;

        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->created_by == $userId)
            return true;

        return false;
    }

    /**
     * Checks if this content can readed
     *
     * @param type $userId
     * @return type
     */
    public function canRead($userId = "") {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        // Space Content Access Check
        if ($this->space_id != "" && $this->visibility == 0) {

            $space = null;
            if (isset(Yii::app()->params['currentSpace']) && Yii::app()->params['currentSpace']->id == $this->space_id) {
                $space = Yii::app()->params['currentSpace'];
            } else {
                $space = Space::model()->findByPk($this->space_id);
            }

            // Space Found
            if ($space != null) {
                if (!$space->isMember($userId)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks if this content can be changed
     *
     * @param type $userId
     * @return type
     */
    public function canWrite($userId = "") {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->created_by == $userId)
            return true;

        return false;
    }

    /**
     * Returns the visibility of the content object
     *
     * @return Integer
     */
    public function getVisibility() {
        return $this->visibility;
    }

    /**
     * Returns the public state of the contect object
     *
     * @return boolean
     */
    public function isPublic() {

        // Space Content
        if ($this->space_id != null) {
            if ($this->visibility == self::VISIBILITY_PUBLIC)
                return true;

            // User Content
        } elseif ($this->user_id != null) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the content object is sticked
     *
     * @return Boolean
     */
    public function isSticked() {
        return ($this->sticked);
    }

    /**
     * Sticks the content object
     */
    public function stick() {
        $this->sticked = 1;
        $this->save();
    }

    /**
     * Unsticks the content object
     */
    public function unstick() {

        $this->sticked = 0;
        $this->save();
    }

    /**
     * Checks if the user can stick this content.
     * This is only allowed for workspace owner.
     *
     * @return boolean
     */
    public function canStick() {

        if ($this->isArchived()) {
            return false;
        }

        $contentBase = $this->getContentBase();
        if ($contentBase instanceOf Space) {
            return ($contentBase->isAdmin());
        } elseif ($contentBase instanceOf User) {
            return (Yii::app()->user->id == $contentBase->id);
        }

        return false;
    }

    /**
     * Creates a list of sticked content objects of the wall
     *
     * @return Int
     */
    public function countStickedItems() {

        $contentBase = $this->getContentBase();

        $sql = "SELECT count(*) FROM wall_entry LEFT JOIN content ON content.id = wall_entry.content_id WHERE wall_entry.wall_id=:wallId AND content.sticked = 1";
        $params = array(':wallId' => $contentBase->wall_id);

        return WallEntry::model()->countBySql($sql, $params);

        #return Content::model()->with('wallEntries')->together(true)->countByAttributes(array('sticked' => 1, 'wall_id' => $contentBase->wall_id));
    }

    /**
     * Checks if current content object is archived
     *
     * @return type
     */
    public function isArchived() {
        return ($this->archived);
    }

    /**
     * Checks if the current user can archive this content.
     * The content owner and the workspace admin can archive contents.
     *
     * @return boolean
     */
    public function canArchive() {

        $contentBase = $this->getContentBase();
        if ($contentBase instanceOf Space) {
            if ($this->canWrite())
                return true;
            return ($contentBase->isAdmin());
        } elseif ($contentBase instanceOf User) {
            return false; // Not available on user profiels because there are no filters?
            //return (Yii::app()->user->id == $contentBase->id);
        }

        return false;
    }

    /**
     * Archives the content object
     */
    public function archive() {
        if ($this->canArchive()) {

            if ($this->isSticked()) {
                $this->unstick();
            }

            $this->archived = 1;
            $this->save();
        }
    }

    /**
     * Unarchives the content object
     */
    public function unarchive() {
        if ($this->canArchive()) {

            $this->archived = 0;
            $this->save();
        }
    }

    /**
     * Also adds this this Content to the given wall
     *
     * @param Integer $wallId
     * @return \WallEntry
     */
    public function addToWall($wallId) {

        $wallEntry = new WallEntry();
        $wallEntry->wall_id = $wallId;
        $wallEntry->content_id = $this->id;
        $wallEntry->save();

        return $wallEntry;
    }

    /**
     * Returns the Wall Entries, which belongs to this Content.
     *
     * @return Array of wall entries for this content
     */
    public function getWallEntries() {
        $entries = WallEntry::model()->findAllByAttributes(array('content_id' => $this->id));
        return $entries;
    }

    /**
     * Returns the first found wall entry Id of this object
     */
    public function getFirstWallEntryId() {
        $wallEntries = $this->getWallEntries();
        if (isset($wallEntries[0])) {
            return $wallEntries[0]->id;
        }
        return 0;
    }

    /**
     * Returns an array of all wall entry Ids used
     * by this content.
     *
     * @return Array
     */
    public function getWallEntryIds() {
        $ids = array();
        foreach ($this->getWallEntries() as $entry) {
            $ids[] = $entry->id;
        }
        return $ids;
    }

    /**
     * Returns the User which created this content
     *
     * @return type
     */
    public function getUser() {

        // Use already loaded user
        if ($this->_user != null)
            return $this->_user;

        if ($this->user != null) {
            $this->_user = $this->user;
            return $this->_user;
        }

        $this->_user = User::model()->findByPk($this->created_by);
        return $this->_user;
    }

}