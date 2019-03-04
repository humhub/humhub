<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\behaviors\GUID;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\Module;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\permissions\CreatePrivateContent;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\content\permissions\ManageContent;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\db\IntegrityException;
use yii\helpers\Url;

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
 * @property integer $pinned
 * @property string $archived
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property ContentContainer $contentContainer
 * @property string $stream_sort_date
 * @property string $stream_channel
 * @property integer $contentcontainer_id;
 * @property ContentContainerActiveRecord $container
 * @property User $createdBy
 * @property User $updatedBy
 * @mixin PolymorphicRelation
 * @mixin GUID
 * @since 0.5
 */
class Content extends ContentDeprecated implements Movable, ContentOwner
{

    /**
     * A array of user objects which should informed about this new content.
     *
     * @var array User
     */
    public $notifyUsersOfNewContent = [];

    /**
     * @var int The private visibility mode (e.g. for space member content or user profile posts for friends)
     */
    const VISIBILITY_PRIVATE = 0;

    /**
     * @var int Public visibility mode, e.g. content which are visibile for followers
     */
    const VISIBILITY_PUBLIC = 1;

    /**
     * @var int Owner visibility mode, only visible for contentContainer + content owner
     */
    const VISIBILITY_OWNER = 2;

    /**
     * @var ContentContainerActiveRecord the Container (e.g. Space or User) where this content belongs to.
     */
    protected $_container = null;

    /**
     * @var bool flag to disable the creation of default social activities like activity and notifications in afterSave() at content creation.
     * @deprecated since v1.2.3 use ContentActiveRecord::silentContentCreation instead.
     */
    public $muteDefaultSocialActivities = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::class,
                'mustBeInstanceOf' => [ContentActiveRecord::class],
            ],
            [
                'class' => GUID::class,
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
            [['object_id', 'visibility', 'pinned'], 'integer'],
            [['archived'], 'safe'],
            [['guid'], 'string', 'max' => 45],
            [['object_model'], 'string', 'max' => 100],
            [['object_model', 'object_id'], 'unique', 'targetAttribute' => ['object_model', 'object_id'], 'message' => 'The combination of Object Model and Object ID has already been taken.'],
            [['guid'], 'unique']
        ];
    }

    /**
     * Returns a Content Object by given Class and ID
     *
     * @param string $className Class Name of the Content
     * @param int $id Primary Key
     */
    public static function Get($className, $id)
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
        if ($this->object_model == "" || $this->object_id == "") {
            throw new Exception("Could not save content with object_model or object_id!");
        }

        // Set some default values
        if (!$this->archived) {
            $this->archived = 0;
        }
        if (!$this->visibility) {
            $this->visibility = self::VISIBILITY_PRIVATE;
        }
        if (!$this->pinned) {
            $this->pinned = 0;
        }

        if ($insert) {
            if ($this->created_by == "") {
                $this->created_by = Yii::$app->user->id;
            }
        }

        $this->stream_sort_date = new \yii\db\Expression('NOW()');

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
        /* @var $contentSource ContentActiveRecord */
        $contentSource = $this->getPolymorphicRelation();

        foreach ($this->notifyUsersOfNewContent as $user) {
            $contentSource->follow($user->id);
        }

        // TODO: handle ContentCreated notifications and live events for global content
        if ($insert && !$this->isMuted()) {
            $this->notifyContentCreated();
        }

        if ($this->container) {
            Yii::$app->live->send(new \humhub\modules\content\live\NewContent([
                'sguid' => ($this->container instanceof Space) ? $this->container->guid : null,
                'uguid' => ($this->container instanceof User) ? $this->container->guid : null,
                'originator' => $this->user->guid,
                'contentContainerId' => $this->container->contentContainerRecord->id,
                'visibility' => $this->visibility,
                'sourceClass' => $contentSource->className(),
                'sourceId' => $contentSource->getPrimaryKey(),
                'silent' => $this->isMuted(),
                'contentId' => $this->id
            ]));
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool checks if the given content allows content creation notifications and activities
     */
    private function isMuted()
    {
        return $this->getPolymorphicRelation()->silentContentCreation || $this->getModel()->silentContentCreation || !$this->container;
    }

    /**
     * Notifies all followers and manually set $notifyUsersOfNewContent of the creation of this content and creates an activity.
     */
    private function notifyContentCreated()
    {
        $contentSource = $this->getPolymorphicRelation();

        $userQuery = Yii::$app->notification->getFollowers($this);
        if (count($this->notifyUsersOfNewContent) != 0) {
            // Add manually notified users
            $userQuery->union(
                User::find()->active()->where(['IN', 'user.id', array_map(function (User $user) {
                    return $user->id;
                }, $this->notifyUsersOfNewContent)])
            );
        }

        \humhub\modules\content\notifications\ContentCreated::instance()
            ->from($this->user)
            ->about($contentSource)
            ->sendBulk($userQuery);

        \humhub\modules\content\activities\ContentCreated::instance()
            ->from($this->user)
            ->about($contentSource)->save();
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
     * Returns the visibility of the content object
     *
     * @return Integer
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Checks if the content visiblity is set to public.
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->visibility == self::VISIBILITY_PUBLIC;
    }

    /**
     * Checks if the content visiblity is set to private.
     *
     * @return boolean
     */
    public function isPrivate()
    {
        return $this->visibility == self::VISIBILITY_PRIVATE;
    }

    /**
     * Checks if the content object is pinned
     *
     * @return Boolean
     */
    public function isPinned()
    {
        return ($this->pinned);
    }

    /**
     * Pins the content object
     */
    public function pin()
    {
        $this->pinned = 1;
        //This prevents the call of beforeSave, and the setting of update_at
        $this->updateAttributes(['pinned']);
    }

    /**
     * Unpins the content object
     */
    public function unpin()
    {

        $this->pinned = 0;
        $this->updateAttributes(['pinned']);
    }

    /**
     * Checks if the user can pin this content.
     * This is only allowed for workspace owner.
     *
     * @return boolean
     */
    public function canPin()
    {
        if ($this->isArchived()) {
            return false;
        }

        return $this->getContainer()->permissionManager->can(ManageContent::class);
    }

    /**
     * Creates a list of pinned content objects of the wall
     *
     * @return Int
     */
    public function countPinnedItems()
    {
        return Content::find()->where(['content.contentcontainer_id' => $this->contentcontainer_id, 'content.pinned' => 1])->count();
    }

    /**
     * Checks if current content object is archived
     *
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived || ($this->getContainer() !== null && $this->getContainer()->isArchived());
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

        return $this->getContainer()->permissionManager->can(new ManageContent());
    }

    /**
     * Archives the content object
     */
    public function archive()
    {
        if ($this->canArchive()) {

            if ($this->isPinned()) {
                $this->unpin();
            }

            $this->archived = 1;
            if (!$this->save()) {
                throw new Exception("Could not archive content!" . print_r($this->getErrors(), 1));
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws \Throwable
     */
    public function move(ContentContainerActiveRecord $container = null, $force = false)
    {
        $move = ($force) ? true : $this->canMove($container);

        if ($move === true) {
            static::getDb()->transaction(function ($db) use ($container) {
                $this->setContainer($container);
                if ($this->save()) {
                    ContentTag::deleteContentRelations($this, false);
                    $model = $this->getModel();
                    $model->populateRelation('content', $this);
                    $model->afterMove($container);
                }
            });
        }

        return $move;
    }

    /**
     * {@inheritdoc}
     */
    public function canMove(ContentContainerActiveRecord $container = null)
    {
        $model = $this->getModel();

        $canModelBeMoved = $this->isModelMovable($container);
        if ($canModelBeMoved !== true) {
            return $canModelBeMoved;
        }

        if (!$container) {
            return $this->checkMovePermission() ? true : Yii::t('ContentModule.base', 'You do not have the permission to move this content.');
        }

        if ($container->contentcontainer_id === $this->contentcontainer_id) {
            return Yii::t('ContentModule.base', 'The content can\'t be moved to its current space.');
        }

        // Check if the related module is installed on the target space
        if (!$container->moduleManager->isEnabled($model->getModuleId())) {
            /* @var $module Module */
            $module = Yii::$app->getModule($model->getModuleId());
            $moduleName = ($module instanceof ContentContainerModule) ? $module->getContentContainerName($container) : $module->getName();
            return Yii::t('ContentModule.base', 'The module {moduleName} is not enabled on the selected target space.', ['moduleName' => $moduleName]);
        }

        // Check if the current user is allowed to move this content at all
        if (!$this->checkMovePermission()) {
            return Yii::t('ContentModule.base', 'You do not have the permission to move this content.');
        }

        // Check if the current user is allowed to move this content to the given target space
        if (!$this->checkMovePermission($container)) {
            return Yii::t('ContentModule.base', 'You do not have the permission to move this content to the given space.');
        }

        // Check if the content owner is allowed to create content on the target space
        $ownerPermissions = $container->getPermissionManager($this->createdBy);
        if ($this->isPrivate() && !$ownerPermissions->can(CreatePrivateContent::class)) {
            return Yii::t('ContentModule.base', 'The author of this content is not allowed to create private content within the selected space.');
        }

        if ($this->isPublic() && !$ownerPermissions->can(CreatePublicContent::class)) {
            return Yii::t('ContentModule.base', 'The author of this content is not allowed to create public content within the selected space.');
        }

        return true;
    }

    public function isModelMovable(ContentContainerActiveRecord $container = null)
    {
        $model = $this->getModel();
        $canModelBeMoved = $model->canMove($container);
        if ($canModelBeMoved !== true) {
            return $canModelBeMoved;
        }

        // Check for legacy modules
        if (!$model->getModuleId()) {
            return Yii::t('ContentModule.base', 'This content type can\'t be moved due to a missing module-id setting.');
        }

        return true;
    }

    /**
     * Checks if the current user has generally the permission to move this content on the given container or the current container if no container was provided.
     *
     * Note this function is only used for a general permission check use [[canMove()]] for a
     *
     * This is the case if:
     *
     * - The current user is the owner of this content
     * @param ContentContainerActiveRecord|null $container
     * @return bool determines if the current user is generally permitted to move content on the given container (or the related container if no container was provided)
     */
    public function checkMovePermission(ContentContainerActiveRecord $container = null)
    {
        if (!$container) {
            $container = $this->container;
        }
        return $this->getModel()->isOwner() || Yii::$app->user->can(ManageUsers::class) || $container->can(ManageContent::class);
    }

    /**
     * {@inheritdoc}
     */
    public function afterMove(ContentContainerActiveRecord $container = null)
    {
        // Nothing to do
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
     * Returns the url of this content.
     *
     * By default is returns the url of the wall entry.
     *
     * Optionally it's possible to create an own getUrl method in the underlying
     * HActiveRecordContent (e.g. Post) to overwrite this behavior.
     * e.g. in case there is no wall entry available for this content.
     *
     * @since 0.11.1
     * @param boolean $scheme
     * @return string the URL
     */
    public function getUrl($scheme = false)
    {
        try {
            if (method_exists($this->getPolymorphicRelation(), 'getUrl')) {
                return $this->getPolymorphicRelation()->getUrl($scheme);
            }
        } catch (IntegrityException $e) {
            Yii::error($e->getMessage(), 'content');
        }

        return Url::toRoute(['/content/perma', 'id' => $this->id], $scheme);
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
        if ($container instanceof Space && $this->visibility === null) {
            $this->visibility = $container->getDefaultContentVisibility();
        }
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
        return $this->hasOne(ContentContainer::class, ['id' => 'contentcontainer_id']);
    }

    /**
     * Returns the ContentTagRelation query.
     *
     * @since 1.2.2
     * @return \yii\db\ActiveQuery
     */
    public function getTagRelations()
    {
        return $this->hasMany(ContentTagRelation::class, ['content_id' => 'id']);
    }

    /**
     * Returns all content related tags ContentTags related to this content.
     *
     * @since 1.2.2
     * @return \yii\db\ActiveQuery
     */
    public function getTags($tagClass = ContentTag::class)
    {
        return $this->hasMany($tagClass, ['id' => 'tag_id'])->via('tagRelations');
    }

    /**
     * Adds a new ContentTagRelation for this content and the given $tag instance.
     *
     * @since 1.2.2
     * @param ContentTag $tag
     * @return bool if the provided tag is part of another ContentContainer
     */
    public function addTag(ContentTag $tag)
    {
        if (!empty($tag->contentcontainer_id) && $tag->contentcontainer_id != $this->contentcontainer_id) {
            throw new InvalidArgumentException(Yii::t('ContentModule.base', 'Content Tag with invalid contentcontainer_id assigned.'));
        }

        if (ContentTagRelation::findBy($this, $tag)->count()) {
            return true;
        }

        unset($this->tags);

        $contentRelation = new ContentTagRelation($this, $tag);
        return $contentRelation->save();
    }

    /**
     * Adds the given ContentTag array to this content.
     *
     * @since 1.3
     * @param $tags ContentTag[]
     */
    public function addTags($tags)
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    /**
     * Checks if the given user can edit this content.
     *
     * A user can edit a content if one of the following conditions are met:
     *
     *  - User is the owner of the content
     *  - User is system administrator and the content module setting `adminCanEditAllContent` is set to true (default)
     *  - The user is granted the managePermission set by the model record class
     *  - The user meets the additional condition implemented by the model records class own `canEdit()` function.
     *
     * @since 1.1
     * @param User|integer $user user instance or user id
     * @return bool can edit this content
     */
    public function canEdit($user = null)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        } else if (!($user instanceof User)) {
            $user = User::findOne(['id' => $user]);
        }

        // Only owner can edit his content
        if ($user !== null && $this->created_by == $user->id) {
            return true;
        }

        // Global Admin can edit/delete arbitrarily content
        if (Yii::$app->getModule('content')->adminCanEditAllContent && $user->isSystemAdmin()) {
            return true;
        }

        $model = $this->getModel();

        // Check additional manage permission for the given container
        if ($model->hasManagePermission() && $this->getContainer() && $this->getContainer()->getPermissionManager($user)->can($model->getManagePermission())) {
            return true;
        }

        // Check if underlying models canEdit implementation
        // ToDo: Implement this as interface
        if (method_exists($model, 'canEdit') && $model->canEdit($user)) {
            return true;
        }

        return false;
    }

    /**
     * @return ContentActiveRecord
     * @since 1.3
     */
    public function getModel()
    {
        return $this->getPolymorphicRelation();
    }

    /**
     * Checks the given $permission of the current user in the contents content container.
     * This is short for `$this->getContainer()->getPermissionManager()->can()`.
     *
     * @param $permission
     * @param array $params
     * @param bool $allowCaching
     * @return bool
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @see PermissionManager::can()
     * @since 1.2.1
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        return $this->getContainer()->getPermissionManager()->can($permission, $params, $allowCaching);
    }

    /**
     * Checks if user can view this content.
     *
     * @since 1.1
     * @param User|integer $user
     * @return boolean can view this content
     * @throws Exception
     * @throws \Throwable
     */
    public function canView($user = null)
    {
        if (!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if (!$user instanceof User) {
            $user = User::findOne(['id' => $user]);
        }

        // User cann access own content
        if ($user !== null && $this->created_by == $user->id) {
            return true;
        }

        // Check Guest Visibility
        if (!$user) {
            return $this->checkGuestAccess();
        }

        // Public visible content
        if ($this->isPublic()) {
            return true;
        }

        // Check system admin can see all content module configuration
        if ($user->isSystemAdmin() && Yii::$app->getModule('content')->adminCanViewAllContent) {
            return true;
        }

        if ($this->isPrivate() && $this->getContainer() !== null && $this->getContainer()->canAccessPrivateContent($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determines if a guest user is able to read this content.
     * This is the case if all of the following conditions are met:
     *
     *  - The content is public
     *  - The `auth.allowGuestAccess` module setting is enabled
     *  - The space or profile visibility is set to VISIBILITY_ALL
     *
     * @return bool
     */
    public function checkGuestAccess()
    {
        if (!$this->isPublic() || !Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess')) {
            return false;
        }

        // Check container visibility for guests
        return ($this->container instanceof Space && $this->container->visibility == Space::VISIBILITY_ALL) || ($this->container instanceof User && $this->container->visibility == User::VISIBILITY_ALL);
    }

    /**
     * Updates the wall/stream sorting time of this content for "updated at" sorting
     */
    public function updateStreamSortTime()
    {
        $this->updateAttributes(['stream_sort_date' => new \yii\db\Expression('NOW()')]);
    }

    /**
     * @returns \humhub\modules\content\models\Content content instance of this content owner
     */
    public function getContent()
    {
        return $this;
    }

    /**
     * @returns string name of the content like 'comment', 'post'
     */
    public function getContentName()
    {
        return $this->getModel()->getContentName();
    }

    /**
     * @returns string short content description
     */
    public function getContentDescription()
    {
        return $this->getModel()->getContentDescription();
    }
}
