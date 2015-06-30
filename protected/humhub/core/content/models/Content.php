<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\content\models;

use Yii;
use yii\base\Exception;
use humhub\core\user\models\User;
use humhub\core\space\models\Space;

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
class Content extends \humhub\components\ActiveRecord
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

    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\UnderlyingObject::className(),
                'mustBeInstanceOf' => array(\humhub\core\content\components\activerecords\Content::className()),
            ],
            [
                'class' => \humhub\components\behaviors\GUID::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'visibility', 'sticked', 'space_id', 'user_id', 'created_by', 'updated_by'], 'integer'],
            [['archived'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['guid'], 'string', 'max' => 45],
            [['object_model'], 'string', 'max' => 100],
            [['object_model', 'object_id'], 'unique', 'targetAttribute' => ['object_model', 'object_id'], 'message' => 'The combination of Object Model and Object ID has already been taken.'],
            [['visibility'], 'validateVisibility'],
            [['guid'], 'unique']
        ];
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

    public function getUser()
    {
        return $this->hasOne(\humhub\core\user\models\User::className(), ['id' => 'user_id']);
    }

    public function getSpace()
    {
        return $this->hasOne(\humhub\core\space\models\Space::className(), ['id' => 'space_id']);
    }

    /**
     * Returns a Content Object by given Class and ID
     *
     * @param string $className Class Name of the Content
     * @param int $id Primary Key
     */
    static function Get($className, $id)
    {
        $content = self::findOne(['object_model' => $className, 'object_id' => $id]);
        if ($content != null) {
            return $className::findOne(['id' => $id]);
        }
        return null;
    }

    public function beforeSave($insert)
    {
        if ($this->object_model == "" || $this->object_id == "")
            throw new Exception("Could not save content with object_model or object_id!");

        if ($this->user_id == "")
            throw new Exception("Could not save content without user_id!");


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


        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        // Loop over each eall entry and make sure its update_at / update_by
        // will also updated. (Sorting wall against update)
        foreach ($this->getWallEntries() as $wallEntry) {
            $wallEntry->save();
        }

        if ($insert) {

            foreach ($this->notifyUsersOfNewContent as $user) {
                $this->getUnderlyingObject()->follow($user->id);
            }

            $notification = new \humhub\core\content\notifications\ContentCreated;
            $notification->source = $this->getUnderlyingObject();
            $notification->originator = $this->user;
            $notification->sendBulk($this->notifyUsersOfNewContent);
        }

        \humhub\core\file\models\File::attachPrecreated($this->getUnderlyingObject(), $this->attachFileGuidsAfterSave);

        return parent::afterSave($insert, $changedAttributes);
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

        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        // Try delete the underlying object (Post, Question, Task, ...)
        $this->resetUnderlyingObject();
        if ($this->getUnderlyingObject() !== null) {
            $this->getUnderlyingObject()->delete();
        }
        
        parent::afterDelete();
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
            $userId = Yii::$app->user->id;

        if ($this->created_by == $userId)
            return true;

        if (Yii::$app->user->isAdmin()) {
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
            $userId = Yii::$app->user->id;


        // For guests users
        if (Yii::$app->user->isGuest) {
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
                if (isset(Yii::$app->params['currentSpace']) && Yii::$app->params['currentSpace']->id == $this->space_id) {
                    $space = Yii::$app->params['currentSpace'];
                } else {
                    $space = Space::findOne(['id' => $this->space_id]);
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
            $userId = Yii::$app->user->id;

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
            return (Yii::$app->user->id == $this->container->id);
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
        $entries = WallEntry::findAll(['content_id' => $this->id]);
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
            throw new Exception("Could not create url for content!");
        }

        return \yii\helpers\Url::toRoute(['/wall/perma/wallEntry', 'id' => $firstWallEntryId]);
    }

    /**
     * Sets container of this content.
     *
     * @param IContentContainer $container
     * @throws Exception
     */
    public function setContainer($container)
    {
        if ($container instanceof Space) {
            $this->space_id = $container->id;
        } elseif ($container instanceof User) {
            $this->user_id = $container->id;
        } else {
            throw new Exception("Invalid container type!");
        }

        $this->_container = $container;
    }

    /**
     * Returns the container which this content belongs to.
     * This is usally a space or user.
     *
     * @return IContentContainer
     * @throws Exception
     */
    public function getContainer()
    {
        if ($this->_container != null) {
            return $this->_container;
        }

        if ($this->space_id != null) {
            $container = Space::findOne(['id' => $this->space_id]);
        } elseif ($this->user_id != null) {
            $container = User::findOne(['id' => $this->user_id]);
        } else {
            throw new Exception("Could not determine container type!");
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
        $containerClass = Yii::$app->request->post('containerClass');
        $containerGuid = Yii::$app->request->post('containerGuid', "");

        if ($containerClass === User::className())
            $contentContainer = User::findOne(['guid' => $containerGuid]);
        elseif ($containerClass === Space::className())
            $contentContainer = Space::findOne(['guid' => $containerGuid]);

        $this->container = $contentContainer;

        if ($this->container->className() === Space::className()) {
            $this->visibility = Yii::$app->request->post('visibility');
        } elseif ($this->container->className() === User::className()) {
            $this->visibility = 1;
        }

        // Handle Notify User Features of ContentFormWidget
        // ToDo: Check permissions of user guids
        $userGuids = Yii::$app->request->post('notifyUserInput');
        if ($userGuids != "") {
            foreach (explode(",", $userGuids) as $guid) {
                $user = User::findOne(['guid' => trim($guid)]);
                if ($user) {
                    $this->notifyUsersOfNewContent[] = $user;
                }
            }
        }

        // Store List of attached Files to add them after Save
        $this->attachFileGuidsAfterSave = Yii::$app->request->post('fileList');
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
        if ($this->container->className() == \humhub\core\space\models\Space::className()) {
            if (!$this->container->canShare() && $this->visibility) {
                $this->addError('visibility', Yii::t('base', 'You cannot create public visible content!'));
            }
        }
    }

}
