<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use Yii;
use yii\base\Exception;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\activity\models\Activity;

/**
 * This is the model class for table "content".
 *
 * Content Container Assignment:
 * If the "space_id" attribute is set, the record belongs to a Space.
 * If not the "user_id" will be used as Content Container.
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
    public $attachFileGuidsAfterSave;

    /**
     * A array of user objects which should informed about this new content.
     *
     * @var Array User
     */
    public $notifyUsersOfNewContent = array();

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
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'mustBeInstanceOf' => array(ContentActiveRecord::className()),
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
            [['archived'], 'safe'],
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

    /**
     * User which created this Content - May also be the ContentContainer of this Content
     * when no Space Relation exists
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

    /**
     * Related space (if ContentContainer is a Space)
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getSpace()
    {
        return $this->hasOne(\humhub\modules\space\models\Space::className(), ['id' => 'space_id']);
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

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->object_model == "" || $this->object_id == "")
            throw new Exception("Could not save content with object_model or object_id!");


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

        if ($insert) {
            if ($this->user_id == "") {
                $this->user_id = Yii::$app->user->id;
            }
        }

        if ($this->user_id == "")
            throw new Exception("Could not save content without user_id!");

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
                $this->getPolymorphicRelation()->follow($user->id);
            }

            $notification = new \humhub\modules\content\notifications\ContentCreated;
            $notification->source = $this->getPolymorphicRelation();
            $notification->originator = $this->user;
            $notification->sendBulk($this->notifyUsersOfNewContent);

            if (!$this->getPolymorphicRelation() instanceof \humhub\modules\activity\models\Activity) {
                $activity = new \humhub\modules\content\activities\ContentCreated;
                $activity->source = $this->getPolymorphicRelation();
                $activity->create();
            }
        }

        \humhub\modules\file\models\File::attachPrecreated($this->getPolymorphicRelation(), $this->attachFileGuidsAfterSave);

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
        $this->resetPolymorphicRelation();
        if ($this->getPolymorphicRelation() !== null) {
            $this->getPolymorphicRelation()->delete();
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

        if ($this->user_id == $userId)
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

        if ($this->user_id == $userId)
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
        //This prevents the call of beforesave, and the setting of update_at 
        $this->updateAttributes(['sticked']);
    }

    /**
     * Unsticks the content object
     */
    public function unstick()
    {

        $this->sticked = 0;
        $this->updateAttributes(['sticked']);
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
        $wallId = $this->container->wall_id;
        return WallEntry::find()->joinWith('content')->where(['wall_entry.wall_id'=>$wallId, 'content.sticked' => 1])->count();
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
            if (!$this->save()) {
                throw new Exception("Could not archive content!" . print_r($this->getErrors(), 1));
            }
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
        if (method_exists($this->getPolymorphicRelation(), 'getUrl')) {
            return $this->getPolymorphicRelation()->getUrl();
        }

        $firstWallEntryId = $this->getFirstWallEntryId();

        if ($firstWallEntryId == "") {
            throw new Exception("Could not create url for content!");
        }

        return \yii\helpers\Url::toRoute(['/content/perma/wall-entry', 'id' => $firstWallEntryId]);
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
            $container = $this->space;
        } elseif ($this->user_id != null) {
            $container = $this->user;
        } else {
            throw new Exception("Could not determine container type!");
        }

        $this->_container = $container;

        return $this->_container;
    }

    public function beforeValidate()
    {
        if (!$this->container->canWrite($this->user_id) && $this->getPolymorphicRelation()->className() != Activity::className()) {
            $this->addError('visibility', Yii::t('base', 'Insufficent permissions to create content!'));
        }

        return parent::beforeValidate();
    }

    public function validateVisibility()
    {
        if ($this->object_model == Activity::className() || $this->getPolymorphicRelation()->className() == Activity::className()) {
            return;
        }

        if ($this->container->className() == Space::className()) {
            if (!$this->container->canShare() && $this->visibility) {
                $this->addError('visibility', Yii::t('base', 'You cannot create public visible content!'));
            }
        }
    }

}
