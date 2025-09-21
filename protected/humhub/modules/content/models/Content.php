<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\GUID;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\Module;
use humhub\interfaces\ArchiveableInterface;
use humhub\interfaces\EditableInterface;
use humhub\interfaces\ViewableInterface;
use humhub\libs\UUIDValidator;
use humhub\modules\content\activities\ContentCreated as ActivitiesContentCreated;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\events\ContentEvent;
use humhub\modules\content\events\ContentStateEvent;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\interfaces\SoftDeletable;
use humhub\modules\content\live\NewContent;
use humhub\modules\content\notifications\ContentCreated as NotificationsContentCreated;
use humhub\modules\content\permissions\CreatePrivateContent;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\content\permissions\ManageContent;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\services\ContentStateService;
use humhub\modules\content\services\ContentTagService;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\IntegrityException;
use yii\db\StaleObjectException;
use yii\helpers\Url;

/**
 * This is the model class for table "content". The content model serves as relation between a [[ContentContainer]] and
 * [[ContentActiveRecord]] entries and contains shared content features as
 *
 *  - read/write permission checks
 *  - move content
 *  - pin content
 *  - archive content
 *  - content author and update information
 *  - stream relation by `stream_channel` field
 *
 * The relation to [[ContentActiveRecord]] models is defined by a polymorphic relation
 * `object_model` and `object_id` content table fields.
 *
 * The relation to [[ContentContainer]] is defined by `contentContainer_id` field.
 *
 * Note: Instances of this class are automatically created and saved by the [[ContentActiveRecord]] model and should not
 * manually be created or deleted to maintain data integrity.
 *
 * @property int $id
 * @property string $guid
 * @property string $object_model
 * @property int $object_id
 * @property string $stream_sort_date
 * @property string $stream_channel
 * @property int $contentcontainer_id
 * @property int $visibility
 * @property int $pinned
 * @property int $archived
 * @property int $hidden
 * @property int $state
 * @property int $was_published
 * @property string $scheduled_at
 * @property int $locked_comments
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property ContentContainer $contentContainer
 * @property-read mixed $contentName
 * @property-read mixed $content
 * @property-read ActiveQuery $tagRelations
 * @property-read ContentActiveRecord $model
 * @property-read mixed $contentDescription
 * @property-read ContentStateService $stateService
 * @property-read ContentTag[] $tags
 * @property ContentContainerActiveRecord $container
 * @mixin PolymorphicRelation
 * @mixin GUID
 * @since 0.5
 */
class Content extends ActiveRecord implements Movable, ContentOwner, ArchiveableInterface, EditableInterface, ViewableInterface, SoftDeletable
{
    /**
     * The default stream channel.
     * @since 1.6
     */
    public const STREAM_CHANNEL_DEFAULT = 'default';

    /**
     * A array of user objects which should informed about this new content.
     *
     * @var array User
     */
    public $notifyUsersOfNewContent = [];

    /**
     * @var int The private visibility mode (e.g. for space member content or user profile posts for friends)
     */
    public const VISIBILITY_PRIVATE = 0;

    /**
     * @var int Public visibility mode, e.g. content which are visibile for followers
     */
    public const VISIBILITY_PUBLIC = 1;

    /**
     * @var int Owner visibility mode, only visible for contentContainer + content owner
     */
    public const VISIBILITY_OWNER = 2;

    /**
     * Content States - By default, only content with the "Published" state is returned.
     */
    public const STATE_PUBLISHED = 1;
    public const STATE_DRAFT = 10;
    public const STATE_SCHEDULED = 20;
    public const STATE_DELETED = 100;

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
     * @event Event is used when a Content state is changed.
     */
    public const EVENT_STATE_CHANGED = 'changedState';

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
            [['guid'], UUIDValidator::class],
            [['guid'], 'unique'],
            [['object_model'], 'string', 'max' => 100],
            [['object_model', 'object_id'], 'unique', 'targetAttribute' => ['object_model', 'object_id'], 'message' => 'The combination of Object Model and Object ID has already been taken.'],
        ];
    }

    /**
     * Returns a [[ContentActiveRecord]] model by given polymorphic relation class and id.
     * If there is no existing content relation with this model instance, null is returned.
     *
     * @param string $className Class Name of the Content
     * @param int $id Primary Key
     * @return ContentActiveRecord|null
     * @throws IntegrityException
     */
    public static function Get($className, $id)
    {
        $content = self::findOne(['object_model' => $className, 'object_id' => $id]);
        if ($content) {
            return $content->getModel();
        }

        return null;
    }

    /**
     * @since 1.3
     */
    public function getModel(): ?ContentActiveRecord
    {
        return $this->getPolymorphicRelation();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->object_model == "" || $this->object_id == "") {
            throw new Exception("Could not save content with object_model or object_id!");
        }

        $this->archived ??= 0;
        $this->pinned ??= 0;
        $this->state ??= Content::STATE_PUBLISHED;

        $this->visibility ??= self::VISIBILITY_PRIVATE;
        // Force to private content for private space or if user has no permission to create public content
        if ($this->container instanceof Space
            && $this->container->visibility !== Space::VISIBILITY_ALL
            && $this->visibility === self::VISIBILITY_PUBLIC
            && (
                $this->container->visibility === Space::VISIBILITY_NONE
                || (
                    Yii::$app->user->identity // Allow creating public content from console
                    && !$this->container->can(CreatePublicContent::class)
                )
            )
        ) {
            $this->visibility = self::VISIBILITY_PRIVATE;
        }

        if ($insert) {
            $this->created_by ??= Yii::$app->user->id;
        }

        $this->stream_sort_date = date('Y-m-d H:i:s');

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
        if (array_key_exists('state', $changedAttributes)) {
            // Run process for new content(Send notifications) only after changing state
            $this->processNewContent($insert);

            $model = $this->getModel();
            if (!$insert && $model instanceof ContentActiveRecord && $this->getStateService()->isPublished()) {
                // Run process to send notifications like mentioning users, for cases when
                // it was not published on creating or on updating published Content
                $model->afterSave(false, []);
            }

            $previousState = $changedAttributes['state'] ?? null;
            $this->trigger(self::EVENT_STATE_CHANGED, new ContentStateEvent([
                'content' => $this,
                'newState' => $this->state,
                'previousState' => $previousState,
            ]));

            if ($model instanceof ContentActiveRecord) {
                $model->afterStateChange($this->state, $previousState);
            }

            if (!$this->getStateService()->wasPublished() && (int)$previousState === self::STATE_PUBLISHED) {
                $this->updateAttributes(['was_published' => 1]);
            }
        }

        (new ContentSearchService($this))->update();

        parent::afterSave($insert, $changedAttributes);
    }

    private function processNewContent($insert)
    {
        if (!$this->getStateService()->isPublished()) {
            // Don't notify about not published Content
            return;
        }

        if ($this->getStateService()->wasPublished()) {
            // No need to notify twice for already published Content before
            return;
        }

        if (!$insert) {
            // Update creation datetime after first publishing
            $this->updateAttributes(['created_at' => date('Y-m-d H:i:s')]);
        }

        $record = $this->getModel();

        Yii::debug('Process new content: ' . get_class($record) . ' ID: ' . $record->getPrimaryKey(), 'content');

        foreach ($this->notifyUsersOfNewContent as $user) {
            $record->follow($user->id);
        }

        // TODO: handle ContentCreated notifications and live events for global content

        if (!$this->isMuted()) {
            $this->notifyContentCreated();
        }

        if ($this->container) {
            Yii::$app->live->send(new NewContent([
                'sguid' => ($this->container instanceof Space) ? $this->container->guid : null,
                'uguid' => ($this->container instanceof User) ? $this->container->guid : null,
                'originator' => $this->createdBy->guid,
                'contentContainerId' => $this->container->contentContainerRecord->id,
                'visibility' => $this->visibility,
                'sourceClass' => PolymorphicRelation::getObjectModel($record),
                'sourceId' => $record->getPrimaryKey(),
                'silent' => $this->isMuted(),
                'streamChannel' => $this->stream_channel,
                'contentId' => $this->id,
                'insert' => true,
            ]));
        }
    }


    /**
     * @return bool checks if the given content allows content creation notifications and activities
     * @throws IntegrityException
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
                }, $this->notifyUsersOfNewContent)]),
            );
        }

        NotificationsContentCreated::instance()
            ->from($this->createdBy)
            ->about($contentSource)
            ->sendBulk($userQuery);

        ActivitiesContentCreated::instance()
            ->from($this->createdBy)
            ->about($contentSource)->save();
    }

    /**
     * Marks this content for deletion (soft delete).
     * Use `hardDelete()` method to delete a content immediately.
     *
     * @return bool
     * @inheritdoc
     */
    public function delete()
    {
        return $this->softDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        // Try to delete the underlying object (Post, Question, Task, ...)
        $this->resetPolymorphicRelation();

        /** @var ContentActiveRecord $record */
        $record = $this->getPolymorphicRelation();

        if ($record) {
            $record->hardDelete();
        }

        parent::afterDelete();
    }

    /**
     * @inheritdoc
     */
    public function beforeSoftDelete(): bool
    {
        $event = new ContentEvent(['content' => $this]);
        $this->trigger(self::EVENT_BEFORE_SOFT_DELETE, $event);

        return $event->isValid;
    }

    /**
     * @inheritdoc
     */
    public function softDelete(): bool
    {
        if (!$this->beforeSoftDelete()) {
            return false;
        }

        Notification::deleteAll([
            'source_class' => PolymorphicRelation::getObjectModel($this),
            'source_pk' => $this->getPrimaryKey(),
        ]);

        if (!$this->getStateService()->delete()) {
            return false;
        }

        $this->afterSoftDelete();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSoftDelete()
    {
        $this->trigger(self::EVENT_AFTER_SOFT_DELETE, new ContentEvent(['content' => $this]));
    }

    /**
     * Deletes this content immediately and permanently
     *
     * @return bool
     * @throws Throwable
     * @throws StaleObjectException
     * @since 1.14
     */
    public function hardDelete(): bool
    {
        return parent::delete() !== false;
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
     * @return bool
     */
    public function isPublic()
    {
        return $this->visibility == self::VISIBILITY_PUBLIC;
    }

    /**
     * Checks if the content visibility is set to private.
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->visibility == self::VISIBILITY_PRIVATE;
    }

    /**
     * Checks if comments are locked for the content.
     *
     * @return bool
     */
    public function isLockedComments(): bool
    {
        return (bool)$this->locked_comments;
    }

    /**
     * Checks if the content object is pinned
     *
     * @return bool
     */
    public function isPinned(): bool
    {
        return (bool)$this->pinned;
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
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function canPin(): bool
    {
        // Currently global content can not be pinned
        if (!$this->getContainer()) {
            return false;
        }

        if ($this->isArchived()) {
            return false;
        }

        if (Yii::$app->user->identity?->canManageAllContent()) {
            return true;
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
     * @return bool
     * @throws Exception
     */
    public function isArchived(): bool
    {
        return $this->archived || ($this->getContainer() !== null && $this->getContainer()->isArchived());
    }

    /**
     * Checks if the current user can archive this content.
     * The content owner and the workspace admin can archive contents.
     *
     * @inheritdoc
     */
    public function canArchive($user = null): bool
    {
        $appUser = Yii::$app->user;

        if ($appUser->isGuest) {
            return false;
        }

        if ($user === null) {
            $user = $appUser->getIdentity();
        } elseif (!($user instanceof User)) {
            $user = User::findOne(['id' => $user]);
        }

        // Currently global content can not be archived
        if (!$container = $this->getContainer()) {
            return $this->canEdit($user);
        }

        // No need to archive content on an archived container, content is marked as archived already
        if ($container->isArchived()) {
            return false;
        }

        if ($user?->canManageAllContent()) {
            return true;
        }

        return $container->getPermissionManager($user)->can(new ManageContent());
    }

    /**
     * @inheritdoc
     */
    public function archive(): bool
    {
        if ($this->isPinned()) {
            $this->unpin();
        }

        $this->archived = 1;
        return $this->save();
    }

    /**
     * {@inheritdoc}
     * @throws Throwable
     */
    public function move(ContentContainerActiveRecord $container = null, $force = false)
    {
        $move = ($force) ? true : $this->canMove($container);

        if ($move === true) {
            static::getDb()->transaction(function ($db) use ($container) {
                $this->setContainer($container);
                if ($this->updateAttributes(['contentcontainer_id', 'visibility'])) {
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
     * @throws IntegrityException
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function checkMovePermission(ContentContainerActiveRecord $container = null)
    {
        if (!$container) {
            $container = $this->container;
        }

        return
            $this->getModel()->isOwner()
            || Yii::$app->user->identity?->canManageAllContent()
            || $container->can(ManageContent::class);
    }

    /**
     * {@inheritdoc}
     */
    public function afterMove(ContentContainerActiveRecord $container = null)
    {
        // Nothing to do
    }

    /**
     * @inheritdoc
     */
    public function unarchive(): bool
    {
        $this->archived = 0;
        return $this->save();
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
     * @param bool $scheme
     * @return string the URL
     * @since 0.11.1
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
     * @return ActiveQuery
     * @since 1.1
     */
    public function getContentContainer()
    {
        return $this->hasOne(ContentContainer::class, ['id' => 'contentcontainer_id']);
    }

    /**
     * Returns the ContentTagRelation query.
     *
     * @return ActiveQuery
     * @since 1.2.2
     */
    public function getTagRelations()
    {
        return $this->hasMany(ContentTagRelation::class, ['content_id' => 'id']);
    }

    /**
     * Returns all content related tags ContentTags related to this content.
     *
     * @return ActiveQuery
     * @since 1.2.2
     */
    public function getTags($tagClass = ContentTag::class)
    {
        return $this->hasMany($tagClass, ['id' => 'tag_id'])
            ->via('tagRelations')
            ->orderBy($tagClass::tableName() . '.sort_order');
    }

    /**
     * Adds a new ContentTagRelation for this content and the given $tag instance.
     *
     * @param ContentTag $tag
     * @return bool if the provided tag is part of another ContentContainer
     * @deprecated since 1.15
     */
    public function addTag(ContentTag $tag)
    {
        return (new ContentTagService($this))->addTag($tag);
    }

    /**
     * Adds the given ContentTag array to this content.
     *
     * @param $tags ContentTag[]
     * @deprecated since 1.15
     */
    public function addTags($tags)
    {
        (new ContentTagService($this))->addTags($tags);
    }

    /**
     * Checks if the given user can edit/create this content.
     *
     * A user can edit a content if one of the following conditions are met:
     *
     *  - User is the owner of the content
     *  - User can Manage Content
     *  - The user is granted the managePermission set by the model record class
     *  - The user meets the additional condition implemented by the model records class own `canEdit()` function.
     *
     * @param User|int $user user instance or user id
     * @return bool can edit/create this content
     * @throws Exception
     * @throws IntegrityException
     * @throws Throwable
     * @throws InvalidConfigException
     * @since 1.1
     */
    public function canEdit($user = null): bool
    {
        $appUser = Yii::$app->user;

        if ($appUser->isGuest) {
            return false;
        }

        if ($user === null) {
            $user = $appUser->getIdentity();
        } elseif (!($user instanceof User)) {
            $user = User::findOne(['id' => $user]);
        }

        // Owner can edit his content
        if ($user !== null && $this->created_by === $user->id) {
            return true;
        }

        // System Admins can edit User content for moderation purposes
        if (
            $this->id // The content exists
            && $this->container instanceof User
            && $user->isSystemAdmin()
        ) {
            return true;
        }

        if ($user?->canManageAllContent()) {
            return true;
        }

        $model = $this->getModel();

        // Check additional manage permission for the given container
        if ($this->container) {
            if (($isNewRecord = $model->isNewRecord) && $model->hasCreatePermission() && $this->container->getPermissionManager($user)->can($model->getCreatePermission())) {
                return true;
            }
            if (!$isNewRecord && $model->hasManagePermission() && $this->container->getPermissionManager($user)->can($model->getManagePermission())) {
                return true;
            }
        }

        // Check if underlying models canEdit implementation
        // ToDo: Send deprecation waring when not implementing EditableInterface
        if (($model instanceof EditableInterface || method_exists($model, 'canEdit')) && $model->canEdit($user)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the user can lock comments for this content.
     *
     * @return bool
     * @throws Exception
     */
    public function canLockComments(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $user = Yii::$app->user->getIdentity();

        if ($user?->canManageAllContent()) {
            return true;
        }

        return $this->container
            ? $this->container->permissionManager->can(ManageContent::class)
            : $this->canEdit();
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
     * @throws InvalidConfigException
     * @see PermissionManager::can()
     * @since 1.2.1
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        return $this->getContainer()->getPermissionManager()->can($permission, $params, $allowCaching);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws Exception
     * @throws Throwable
     */
    public function canView($user = null): bool
    {
        if (!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } elseif (!$user instanceof User) {
            $user = User::findOne(['id' => $user]);
        }

        // Check global content visibility, private global content is visible for all users
        if (empty($this->contentcontainer_id) && !Yii::$app->user->isGuest) {
            return true;
        }

        // User can access own content
        if ($user !== null && $this->created_by === $user->id) {
            return true;
        }

        // Check Guest Visibility
        if (!$user) {
            return $this->checkGuestAccess();
        }

        // If content is not published(draft, in trash, unapproved) - restrict view access to editors
        if (!$this->getStateService()->isPublished()) {
            return $this->canEdit();
        }

        // Public visible content
        if ($this->isPublic()) {
            return true;
        }

        // Check user can manage all content
        if ($user->canManageAllContent()) {
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
     *  - The `auth.allowGuestAccess` setting is enabled
     *  - The space or profile visibility is set to VISIBILITY_ALL
     *
     * @return bool
     */
    public function checkGuestAccess()
    {
        if (!$this->isPublic() || !AuthHelper::isGuestAccessEnabled()) {
            return false;
        }

        // GLobal content
        if (!$this->container) {
            return $this->isPublic();
        }

        if ($this->container instanceof Space) {
            return $this->isPublic() && $this->container->visibility == Space::VISIBILITY_ALL;
        }

        if ($this->container instanceof User) {
            return $this->isPublic() && $this->container->visibility == User::VISIBILITY_ALL;
        }

        return false;
    }

    /**
     * Updates the wall/stream sorting time of this content for "updated at" sorting
     */
    public function updateStreamSortTime()
    {
        $this->updateAttributes(['stream_sort_date' => date('Y-m-d H:i:s')]);
    }

    /**
     * @returns Content content instance of this content owner
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

    /**
     * @returns bool true if this content has been updated, otherwise false
     * @since 1.7
     */
    public function isUpdated()
    {
        return $this->created_at !== $this->updated_at && !empty($this->updated_at) && is_string($this->updated_at);
    }

    public function getStateService(): ContentStateService
    {
        return new ContentStateService(['content' => $this]);
    }

    /**
     * @param int|string|null $state
     * @param array $options Additional options depending on state
     * @since 1.14
     * @deprecated Use $this->getStateService()->set(). It will be deleted in v1.15.
     */
    public function setState($state, array $options = [])
    {
        $this->getStateService()->set($state, $options);
    }
}
