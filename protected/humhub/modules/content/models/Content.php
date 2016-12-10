<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use Yii;
use yii\base\Exception;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * This is the model class for table "content".
 *
 *
 * The followings are the available columns in table 'content':
 * @property integer $id
 * @property string $guid
 * @property string $object_model
 * @property integer $object_id
 * @property integer $visibility
 * @property integer $sticked
 * @property string $archived
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
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
     * @var ContentContainerActiveRecord the Container (e.g. Space or User) where this content belongs to.
     */
    protected $_container = null;

    /**
     * @inheritdoc
     */
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
            [['object_id', 'visibility', 'sticked'], 'integer'],
            [['archived'], 'safe'],
            [['guid'], 'string', 'max' => 45],
            [['object_model'], 'string', 'max' => 100],
            [['object_model', 'object_id'], 'unique', 'targetAttribute' => ['object_model', 'object_id'], 'message' => 'The combination of Object Model and Object ID has already been taken.'],
            [['guid'], 'unique']
        ];
    }

    /**
     * User which created this Content
     * Note: Use createdBy attribute instead.
     *
     * @deprecated since version 1.1
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->createdBy;
    }

    /**
     * Return space (if this content assigned to a space)
     * Note: Use container attribute instead
     *
     * @deprecated since version 1.1
     * @return \yii\db\ActiveQuery
     */
    public function getSpace()
    {
        if ($this->getContainer() instanceof Space) {
            return $this->getContainer();
        }

        return null;
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
            if ($this->created_by == "") {
                $this->created_by = Yii::$app->user->id;
            }
        }

        if ($this->created_by == "") {
            throw new Exception("Could not save content without created_by!");
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
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
     * @inheritdoc
     */
    public function beforeDelete()
    {
        // delete also all wall entries
        foreach ($this->getWallEntries() as $entry) {
            $entry->delete();
        }

        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
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
     * Checks if the content can be deleted
     * Note: Use canEdit method instead.
     *
     * @deprecated since version 1.1
     * @param int $userId optional user id (if empty current user id will be used)
     */
    public function canDelete($userId = "")
    {
        return $this->canEdit(($userId !== '') ? User::findOne(['id' => $userId]) : null);
    }

    /**
     * Checks if this content can readed
     * Note: use canView method instead
     *
     * @deprecated since version 1.1
     * @param int $userId
     * @return boolean
     */
    public function canRead($userId = "")
    {
        return $this->canView(($userId !== '') ? User::findOne(['id' => $userId]) : null);
    }

    /**
     * Checks if this content can be changed
     * Note: use canEdit method instead
     *
     * @deprecated since version 1.1
     * @param int $userId
     * @return boolean
     */
    public function canWrite($userId = "")
    {
        return $this->canEdit(($userId !== '') ? User::findOne(['id' => $userId]) : null);
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

        return $this->getContainer()->permissionManager->can(new \humhub\modules\content\permissions\ManageContent());
    }

    /**
     * Creates a list of sticked content objects of the wall
     *
     * @return Int
     */
    public function countStickedItems()
    {
        $wallId = $this->container->wall_id;
        return WallEntry::find()->joinWith('content')->where(['wall_entry.wall_id' => $wallId, 'content.sticked' => 1])->count();
    }

    /**
     * Checks if current content object is archived
     *
     * @return boolean
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
        // Disabled on user profiles, there is no stream filter available yet.
        if ($this->getContainer() instanceof User) {
            return false;
        }

        return $this->getContainer()->permissionManager->can(new \humhub\modules\content\permissions\ManageContent());
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
     * Sets container (e.g. space or user record) for this content.
     *
     * @param ContentContainerActiveRecord $container
     * @throws Exception
     */
    public function setContainer(ContentContainerActiveRecord $container)
    {
        $this->contentcontainer_id = $container->contentContainerRecord->id;
        $this->_container = $container;
    }

    /**
     * Returns the content container (e.g. space or user record) of this content
     *
     * @return ContentContainerActiveRecord
     * @throws Exception
     */
    public function getContainer()
    {
        if ($this->_container != null) {
            return $this->_container;
        }

        if ($this->contentContainer !== null) {
            $this->_container = $this->contentContainer->getPolymorphicRelation();
        }

        return $this->_container;
    }

    /**
     * Relation to ContentContainer model
     * Note: this is not a Space or User instance!
     *
     * @since 1.1
     * @return \yii\db\ActiveQuery
     */
    public function getContentContainer()
    {
        return $this->hasOne(ContentContainer::className(), ['id' => 'contentcontainer_id']);
    }

    /**
     * Checks if user can edit this content
     *
     * @todo create possibility to define own canEdit in ContentActiveRecord
     * @todo also check content containers canManage content permission
     * @since 1.1
     * @param User $user
     * @return boolean can edit this content
     */
    public function canEdit($user = null)
    {
        if(Yii::$app->user->isGuest) {
            return false;
        }
        
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        // Only owner can edit his content
        if ($user !== null && $this->created_by == $user->id) {
            return true;
        }
        
        if($this->getContainer()->permissionManager->can(new \humhub\modules\content\permissions\ManageContent())) {
            return true;
        }

        // Global Admin can edit/delete arbitrarily content
        if (Yii::$app->getModule('content')->adminCanEditAllContent && Yii::$app->user->getIdentity()->isSystemAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user can view this content
     *
     * @since 1.1
     * @param User $user
     * @return boolean can view this content
     */
    public function canView($user = null)
    {
        if ($user === null && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        }

        // Check Guest Visibility
        if ($user === null) {
            if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') && $this->visibility === self::VISIBILITY_PUBLIC) {
                // Check container visibility for guests
                if (($this->container instanceof Space && $this->container->visibility == Space::VISIBILITY_ALL) ||
                        ($this->container instanceof User && $this->container->visibility == User::VISIBILITY_ALL)) {
                    return true;
                }
            }
            return false;
        }

        // Public visible content
        if ($this->visibility === self::VISIBILITY_PUBLIC) {
            return true;
        }

        // Check Superadmin can see all content option
        if ($user->isSystemAdmin() && Yii::$app->getModule('content')->adminCanViewAllContent) {
            return true;
        }

        if ($this->visibility === self::VISIBILITY_PRIVATE && $this->getContainer()->canAccessPrivateContent($user)) {
            return true;
        }

        return false;
    }

    /**
     * Updates the wall/stream sorting time of this content for "updated at" sorting
     */
    public function updateStreamSortTime()
    {
        foreach ($this->getWallEntries() as $wallEntry) {
            $wallEntry->updated_at = new \yii\db\Expression('NOW()');
            $wallEntry->save();
        }
    }

}
