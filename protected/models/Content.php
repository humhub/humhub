<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

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
 * @package humhub.models
 * @since 0.5
 */
class Content extends CActiveRecord
{

    /**
     * A string contains a list of file guids which should be attached
     * to this content after creations.
     *
     * @var String
     */
    protected $attachFileGuidsAfterSave;

    /**
     * A array of user objects which should informed about this new content.
     *
     * @var Array User
     */
    protected $notifyUsersOfNewContent = array();

    // Visibility Modes
    const VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC = 1;

    /**
     * Container where content belongs to.
     * Usually a space or user.
     *
     * @var IContentContainer
     */
    protected $_container = null;

    /**
     * Inits the content record
     */
    public function init()
    {

        parent::init();

        // Intercept this controller
        Yii::app()->interceptor->intercept($this);
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Content the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors()
    {
        return array(
            'HUnderlyingObjectBehavior' => array(
                'class' => 'application.behaviors.HUnderlyingObjectBehavior',
                'mustBeInstanceOf' => array('HActiveRecordContent'),
            ),
            'HGuidBehavior' => array(
                'class' => 'application.behaviors.HGuidBehavior',
            ),
        );
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'content';
    }

    /**
     * Rules to validate content model
     *
     * Note: object_id, object_model, user_id are required but validated manually before save.
     *
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('guid', 'required'),
            array('guid', 'length', 'max' => 45),
            array('object_id, visibility, sticked, space_id, user_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('object_model', 'length', 'max' => 100),
            array('visibility', 'validateVisibility'),
            array('archived, created_at, updated_at', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'wallEntries' => array(self::HAS_MANY, 'WallEntry', 'content_id'),
            'space' => array(self::BELONGS_TO, 'Space', 'space_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
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
     * Returns a Content Object by given Class and ID
     *
     * @param string $className Class Name of the Content
     * @param int $id Primary Key
     */
    static function Get($className, $id)
    {

        $content = Content::model()->findByAttributes(array('object_model' => $className, 'object_id' => $id));

        if ($content != null)
            return $className::model()->findByPk($id);

        return null;
    }

    protected function beforeSave()
    {

        if ($this->object_model == "" || $this->object_id == "")
            throw new CException("Could not save content with object_model or object_id!");

        if ($this->user_id == "")
            throw new CException("Could not save content without user_id!");


        // Set some default values
        if (!$this->archived) {
            $this->archived = 0;
        }
        if (!$this->visibility) {
            $this->visibility = self::VISIBILITY_PRIVATE;
        }
        if (!$this->sticked) {
            $this->sticked = 0;
        }


        return parent::beforeSave();
    }

    public function afterSave()
    {

        // Loop over each eall entry and make sure its update_at / update_by
        // will also updated. (Sorting wall against update)
        foreach ($this->getWallEntries() as $wallEntry) {
            $wallEntry->save();
        }

        if ($this->isNewRecord) {
            // If there are notifyUsers specified by populateByForm() make them follow this content
            foreach ($this->notifyUsersOfNewContent as $user) {
                $this->getUnderlyingObject()->follow($user->id);

                // Fire Notification to user
                $notification = new Notification();
                $notification->class = "ContentCreatedNotification";
                $notification->user_id = $user->id;
                if (get_class($this->container) == 'Space') {
                    $notification->space_id = $this->container->id;
                }
                $notification->source_object_model = $this->object_model;
                $notification->source_object_id = $this->object_id;
                $notification->target_object_model = $this->object_model;
                $notification->target_object_id = $this->object_id;
                $notification->save();
            }
        }

        File::attachPrecreated($this->getUnderlyingObject(), $this->attachFileGuidsAfterSave);

        return parent::afterSave();
    }

    /**
     * Before deleting a SIContent try to delete all corresponding SIContentAddons.
     */
    public function beforeDelete()
    {

        // delete also all wall entries
        foreach ($this->getWallEntries() as $entry) {
            $entry->delete();
        }

        // remove from search index
        if ($this->object_model instanceof ISearchable) {
            HSearch::getInstance()->deleteModel($this->getContentObject());
        }

        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        // Try delete the underlying object (Post, Question, Task, ...)
        $this->resetUnderlyingObject();
        if ($this->getUnderlyingObject() !== null) {
            $this->getUnderlyingObject()->delete();
        }
    }

    /**
     * Checks if the given / or current user can delete this content.
     * Currently only the creator can remove.
     *
     * @todo Ask the underlying "real" content for deletion?
     *
     * @param type $userId
     */
    public function canDelete($userId = "")
    {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->created_by == $userId)
            return true;

        if (Yii::app()->user->isAdmin()) {
            return true;
        }

        if ($this->container instanceof Space && $this->container->isAdmin($userId)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if this content can readed
     *
     * @param type $userId
     * @return type
     */
    public function canRead($userId = "")
    {

        if ($userId == "")
            $userId = Yii::app()->user->id;


        // For guests users
        if (Yii::app()->user->isGuest) {
            if ($this->visibility == 1) {
                if ($this->container instanceof Space) {
                    if ($this->container->visibility == Space::VISIBILITY_ALL) {
                        return true;
                    }
                } elseif ($this->container instanceof User) {
                    if ($this->container->visibility == User::VISIBILITY_ALL) {
                        return true;
                    }
                }
            }
            return false;
        }

        if ($this->visibility == 0) {
            // Space/User Content Access Check
            if ($this->space_id != "") {

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
            } else {
                // Check for user content
                if ($userId != $this->user_id) {
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
    public function canWrite($userId = "")
    {
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
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Returns the public state of the contect object
     *
     * @return boolean
     */
    public function isPublic()
    {

        if ($this->visibility == self::VISIBILITY_PUBLIC) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the content object is sticked
     *
     * @return Boolean
     */
    public function isSticked()
    {
        return ($this->sticked);
    }

    /**
     * Sticks the content object
     */
    public function stick()
    {
        $this->sticked = 1;
        $this->save();
    }

    /**
     * Unsticks the content object
     */
    public function unstick()
    {

        $this->sticked = 0;
        $this->save();
    }

    /**
     * Checks if the user can stick this content.
     * This is only allowed for workspace owner.
     *
     * @return boolean
     */
    public function canStick()
    {

        if ($this->isArchived()) {
            return false;
        }

        if ($this->container instanceof Space) {
            return ($this->container->isAdmin());
        } elseif ($this->container instanceof User) {
            return (Yii::app()->user->id == $this->container->id);
        }

        return false;
    }

    /**
     * Creates a list of sticked content objects of the wall
     *
     * @return Int
     */
    public function countStickedItems()
    {

        $sql = "SELECT count(*) FROM wall_entry LEFT JOIN content ON content.id = wall_entry.content_id WHERE wall_entry.wall_id=:wallId AND content.sticked = 1";
        $params = array(':wallId' => $this->container->wall_id);

        return WallEntry::model()->countBySql($sql, $params);
    }

    /**
     * Checks if current content object is archived
     *
     * @return type
     */
    public function isArchived()
    {
        return ($this->archived);
    }

    /**
     * Checks if the current user can archive this content.
     * The content owner and the workspace admin can archive contents.
     *
     * @return boolean
     */
    public function canArchive()
    {

        if ($this->container instanceof Space) {
            if ($this->canWrite())
                return true;
            return ($this->container->isAdmin());
        } elseif ($this->container instanceof User) {
            return false; // Not available on user profiels because there are no filters?
        }

        return false;
    }

    /**
     * Archives the content object
     */
    public function archive()
    {
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
    public function unarchive()
    {
        if ($this->canArchive()) {

            $this->archived = 0;
            $this->save();
        }
    }

    /**
     * Adds this this content to a given wall id
     *
     * If no wallId is given, the wallId of underlying content container is
     * used.
     *
     * @param Integer $wallId
     * @return \WallEntry
     */
    public function addToWall($wallId = 0)
    {

        if ($wallId == 0) {
            $contentContainer = $this->getContainer();
            $wallId = $contentContainer->wall_id;
        }

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
    public function getWallEntries()
    {
        $entries = WallEntry::model()->findAllByAttributes(array('content_id' => $this->id));
        return $entries;
    }

    /**
     * Returns the first found wall entry Id of this object
     */
    public function getFirstWallEntryId()
    {
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
    public function getWallEntryIds()
    {
        $ids = array();
        foreach ($this->getWallEntries() as $entry) {
            $ids[] = $entry->id;
        }
        return $ids;
    }

    /**
     * Returns the url of this content.
     * 
     * By default is returns the url of the wall entry.
     * 
     * Optionally it's possible to create an own getUrl method in the underlying
     * HActiveRecordContent (e.g. Post) to overwrite this behavior. 
     * e.g. in case there is no wall entry available for this content.
     * 
     * @since 0.11.1
     */
    public function getUrl()
    {
        if (method_exists($this->getUnderlyingObject(), 'getUrl')) {
            return $this->getUnderlyingObject()->getUrl();
        }

        $firstWallEntryId = $this->getFirstWallEntryId();

        if ($firstWallEntryId == "") {
            throw new CException("Could not create url for content!");
        }

        return Yii::app()->createUrl('//wall/perma/wallEntry', array('id' => $firstWallEntryId));
    }

    /**
     * Sets container of this content.
     *
     * @param IContentContainer $container
     * @throws CException
     */
    public function setContainer($container)
    {
        if ($container instanceof Space) {
            $this->space_id = $container->id;
        } elseif ($container instanceof User) {
            $this->user_id = $container->id;
        } else {
            throw new CException("Invalid container type!");
        }

        $this->_container = $container;
    }

    /**
     * Returns the container which this content belongs to.
     * This is usally a space or user.
     *
     * @return IContentContainer
     * @throws CException
     */
    public function getContainer()
    {

        if ($this->_container != null) {
            return $this->_container;
        }

        if ($this->space_id != null) {
            $container = Space::model()->findByPk($this->space_id);
        } elseif ($this->user_id != null) {
            $container = User::model()->findByPk($this->user_id);
        } else {
            throw new CException("Could not determine container type!");
        }

        $this->_container = $container;

        return $this->_container;
    }

    /**
     * Sets standard content informations like container, visibility, files
     * by ContentFormWidget Submit Data.
     */
    public function populateByForm()
    {

        // Set Content Container
        $contentContainer = null;
        if (Yii::app()->request->getParam('containerClass') == 'User')
            $contentContainer = User::model()->findByAttributes(array('guid' => Yii::app()->request->getParam('containerGuid', "")));
        elseif (Yii::app()->request->getParam('containerClass') == 'Space')
            $contentContainer = Space::model()->findByAttributes(array('guid' => Yii::app()->request->getParam('containerGuid', "")));

        $this->container = $contentContainer;

        if (get_class($this->container) == 'Space') {
            $this->visibility = Yii::app()->request->getParam('visibility');
        } elseif (get_class($this->container) == 'User') {
            $this->visibility = 1;
        }

        // Handle Notify User Features of ContentFormWidget
        // ToDo: Check permissions of user guids
        $userGuids = Yii::app()->request->getParam('notifyUserInput');
        if ($userGuids != "") {
            foreach (explode(",", $userGuids) as $guid) {
                $user = User::model()->findByAttributes(array('guid' => trim($guid)));
                if ($user) {
                    $this->notifyUsersOfNewContent[] = $user;
                }
            }
        }

        // Store List of attached Files to add them after Save
        $this->attachFileGuidsAfterSave = Yii::app()->request->getParam('fileList');
    }

    public function beforeValidate()
    {

        if (!$this->container->canWrite($this->created_by)) {
            $this->addError('visibility', Yii::t('base', 'Insufficent permissions to create content!'));
        }

        return parent::beforeValidate();
    }

    public function validateVisibility()
    {

        if (get_class($this->container) == 'Space') {
            if (!$this->container->canShare() && $this->visibility) {
                $this->addError('visibility', Yii::t('base', 'You cannot create public visible content!'));
            }
        }
    }

}
