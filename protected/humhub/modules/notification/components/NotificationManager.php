<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\components\ActiveRecord;
use humhub\components\Event;
use humhub\components\Module;
use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\notification\events\BeforeDispatchEvent;
use humhub\modules\notification\events\UnreadCountChangedEvent;
use humhub\modules\notification\jobs\DispatchJob;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationSpaceService;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\db\IntegrityException;

/**
 * The notification manager, available as `Yii::$app->notification`.
 *
 * - {@see dispatch()} creates a notification for a set of recipients. The recipients, the source
 *   and the originator are reduced to ids and handed to a {@see DispatchJob}, which writes one
 *   {@see Notification} record per recipient, groups it and hands it to the channels.
 * - {@see delete()}, {@see markSeen()}, {@see markRecordSeen()} and {@see markAllSeen()} change
 *   stored notifications.
 * - {@see getTargets()}/{@see getTarget()} are the registry of the delivery channels configured
 *   by id in `config/common.php`.
 * - {@see getNotifications()} lists the notification classes of the enabled modules and
 *   {@see getGroups()} the {@see NotificationGroup}s users switch them on or off with.
 *
 * Deleted users, contents, containers and other source records take their notifications along
 * through the foreign keys of the `notification` table and the {@see RecordMap}.
 *
 * @since 0.5, rewritten in 1.20
 */
class NotificationManager extends Component
{
    /**
     * Fired once per dispatch with a {@see BeforeDispatchEvent} carrying the arguments of the
     * call. A handler may change them or veto the dispatch with `$event->isValid = false`.
     *
     * @since 1.20
     */
    public const EVENT_BEFORE_DISPATCH = 'beforeDispatch';

    /**
     * Lets modules add notification classes kept outside a `notifications/` directory to
     * `$event->result` (an array of class names).
     */
    public const EVENT_SEARCH_MODULE_NOTIFICATIONS = 'searchModuleNotifications';

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::IS_TOUCHED_SETTINGS}
     */
    public const IS_TOUCHED_SETTINGS = NotificationSpaceService::IS_TOUCHED_SETTINGS;

    /**
     * Target properties removed in 1.20; a legacy target configuration setting them is still
     * accepted, the keys are dropped with a deprecation warning.
     */
    private const REMOVED_TARGET_PROPERTIES = ['renderer', 'defaultSetting'];

    /**
     * @var array<string, array|string> target configuration by target id, see `config/common.php`:
     * a configuration array (`['class' => ..., ...]`) or a class name
     */
    public array $targets = [];

    /**
     * @var BaseTarget[]|null
     */
    private ?array $_targets = null;

    /**
     * @var string[]|null
     */
    private ?array $_notifications = null;

    /**
     * Creates a notification of the given class for the given recipients - asynchronously, by
     * a {@see DispatchJob}.
     *
     * Options:
     * - `payload`: data of the notification, stored as JSON, see {@see BaseNotification::$payload}
     * - `notifyOriginator`: whether the originator receives the notification when among the recipients (default `false`)
     * - `priority`: overrides {@see BaseNotification::priority()}
     * - `dedupe`: skip a recipient who already has a notification of this class, source and originator (default `true`; no effect without a source)
     *
     * @param class-string<BaseNotification> $class
     * @param ActiveQueryUser|User|int|array<User|int> $recipients a query must be self-contained: it is
     * serialized into the queue, so it cannot be a relation query bound to a `primaryModel`
     * @param ActiveRecord|null $source a content, a content record (e.g. a post), a content addon (e.g. a comment), a container or any other record
     * @param array{payload?: array, notifyOriginator?: bool, priority?: NotificationPriority, dedupe?: bool} $options
     * @throws InvalidArgumentException when the class (also after {@see EVENT_BEFORE_DISPATCH}) is no
     * notification class, or the source is not saved
     * @since 1.20
     */
    public static function dispatch(
        string $class,
        ActiveQueryUser|User|int|array $recipients,
        ?ActiveRecord $source = null,
        ?User $originator = null,
        array $options = [],
    ): void {
        if (!is_subclass_of($class, BaseNotification::class)) {
            throw new InvalidArgumentException("Class {$class} is not a " . BaseNotification::class);
        }

        $event = new BeforeDispatchEvent([
            'class' => $class,
            'recipients' => $recipients,
            'source' => $source,
            'originator' => $originator,
            'options' => $options,
        ]);
        Event::trigger(static::class, self::EVENT_BEFORE_DISPATCH, $event);

        if (!$event->isValid) {
            return;
        }

        if (!is_subclass_of($event->class, BaseNotification::class)) {
            throw new InvalidArgumentException("Class {$event->class} is not a " . BaseNotification::class);
        }

        Yii::$app->queue->push(new DispatchJob([
            'class' => $event->class,
            'recipients' => self::serializeRecipients($event->recipients),
            'source' => self::serializeSource($event->source),
            'originatorId' => $event->originator !== null ? (int)$event->originator->id : null,
            'options' => $event->options,
        ]));
    }

    /**
     * An {@see ActiveQueryUser} travels through the queue as is (the queue serializes it), all
     * other forms as user ids.
     *
     * @return ActiveQueryUser|int[]
     */
    private static function serializeRecipients(ActiveQueryUser|User|int|array $recipients): ActiveQueryUser|array
    {
        if ($recipients instanceof ActiveQueryUser) {
            return $recipients;
        }

        $ids = [];
        foreach (is_array($recipients) ? $recipients : [$recipients] as $recipient) {
            $ids[] = $recipient instanceof User ? (int)$recipient->id : (int)$recipient;
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array{content?: int, record?: int, container?: int}|null
     */
    private static function serializeSource(?ActiveRecord $source): ?array
    {
        if ($source === null) {
            return null;
        }

        if ($source->isNewRecord) {
            throw new InvalidArgumentException('The source of a notification must be saved.');
        }

        if ($source instanceof Content) {
            return ['content' => (int)$source->id];
        }

        if ($source instanceof ContentActiveRecord) {
            return ['content' => (int)$source->content->id];
        }

        if ($source instanceof ContentAddonActiveRecord) {
            return ['content' => (int)$source->content->id, 'record' => RecordMap::getId($source)];
        }

        if ($source instanceof ContentContainerActiveRecord) {
            return ['container' => (int)$source->contentcontainer_id];
        }

        return ['record' => RecordMap::getId($source)];
    }

    /**
     * The notification object of a record. For a row of a grouped query that represents a group,
     * the newest member is loaded with the group's size.
     *
     * @throws IntegrityException when the class is unknown or a referenced record is gone
     * @since 1.20
     */
    public static function load(Notification $record): BaseNotification
    {
        if (!empty($record->group_max_id) && (int)$record->group_max_id !== (int)$record->id) {
            $groupCount = $record->group_count;
            $groupUnseen = $record->group_unseen;
            $record = Notification::findOne(['id' => $record->group_max_id]);
            if ($record === null) {
                throw new IntegrityException('Group head of the notification no longer exists');
            }
            $record->group_count = $groupCount;
            $record->group_unseen = $groupUnseen;
        }

        return self::fromRecord($record);
    }

    /**
     * The notification object of exactly this row - unlike {@see load()}, a row of a grouped query
     * is not swapped for the newest member of its group.
     *
     * @throws IntegrityException when the class is unknown or a referenced record is gone
     * @since 1.20
     */
    public static function fromRecord(Notification $record): BaseNotification
    {
        if (!class_exists($record->class) || !is_subclass_of($record->class, BaseNotification::class)) {
            throw new IntegrityException('Unknown notification class ' . $record->class);
        }

        return Yii::createObject($record->class, ['record' => $record]);
    }

    /**
     * Deletes the notifications of the given class, optionally limited to a source, a recipient
     * and an originator.
     *
     * The rows are deleted by `deleteAll()`, without the grouping hook of
     * {@see Notification::beforeDelete()}: a deletion by class and source takes whole entries,
     * and a group left below its threshold is self-healing - the next insert into it regroups it
     * ({@see \humhub\modules\notification\services\GroupingService::afterInsert()}).
     *
     * @return int the number of deleted rows
     * @since 1.20
     */
    public static function delete(string $class, ?ActiveRecord $source = null, ?User $user = null, ?User $originator = null): int
    {
        $sourceCondition = self::sourceCondition($source);
        if ($sourceCondition === null) {
            return 0;
        }

        $condition = array_merge(['class' => $class], $sourceCondition);
        if ($user !== null) {
            $condition['user_id'] = $user->id;
        }
        if ($originator !== null) {
            $condition['originator_id'] = $originator->id;
        }

        return Notification::deleteAll($condition);
    }

    /**
     * Marks the user's notifications of the given class and source seen - with the whole groups
     * they belong to.
     *
     * @since 1.20
     */
    public static function markSeen(string $class, ?ActiveRecord $source, User $user): void
    {
        $sourceCondition = self::sourceCondition($source);
        if ($sourceCondition === null) {
            return;
        }

        $groupingKeys = Notification::find()
            ->forUser($user)
            ->unseen()
            ->andWhere(['notification.class' => $class])
            ->andWhere(self::prefixColumns($sourceCondition))
            ->select('notification.grouping_key')
            ->distinct()
            ->column();

        if ($groupingKeys === []) {
            return;
        }

        self::updateSeen($user, ['grouping_key' => $groupingKeys]);
    }

    /**
     * Marks the record's whole group seen.
     *
     * @since 1.20
     */
    public static function markRecordSeen(Notification $record): void
    {
        $user = $record->user;
        if ($user === null) {
            return;
        }

        self::updateSeen($user, ['grouping_key' => $record->grouping_key ?? $record->id]);
    }

    /**
     * Marks all notifications of the user seen.
     *
     * @since 1.20
     */
    public static function markAllSeen(User $user): void
    {
        self::updateSeen($user, []);
    }

    /**
     * Sets `seen_at` of the user's unseen rows matching the condition; triggers
     * {@see UnreadCountChangedEvent} when any row changed.
     */
    private static function updateSeen(User $user, array $condition): void
    {
        $changed = Notification::updateAll(
            ['seen_at' => date('Y-m-d H:i:s')],
            array_merge($condition, ['user_id' => $user->id, 'seen_at' => null]),
        );

        if ($changed > 0) {
            UnreadCountChangedEvent::triggerChanged($user);
        }
    }

    /**
     * The condition on the source columns; `[]` without a source, `null` when no row can match.
     */
    private static function sourceCondition(?ActiveRecord $source): ?array
    {
        if ($source === null) {
            return [];
        }

        if ($source->isNewRecord) {
            throw new InvalidArgumentException('The source of a notification must be saved.');
        }

        // A content means the content itself, not a content addon (e.g. a comment) under it
        if ($source instanceof Content) {
            return ['content_id' => $source->id, 'source_record_id' => null];
        }

        if ($source instanceof ContentActiveRecord) {
            return ['content_id' => $source->content->id, 'source_record_id' => null];
        }

        if ($source instanceof ContentContainerActiveRecord) {
            return ['contentcontainer_id' => $source->contentcontainer_id];
        }

        // A content addon (stored with its content, but matched by itself) or any other record:
        // by its record map id. Without a map row there is no notification about it.
        if (!RecordMap::hasId($source)) {
            return null;
        }

        return ['source_record_id' => RecordMap::getId($source)];
    }

    private static function prefixColumns(array $condition): array
    {
        $prefixed = [];
        foreach ($condition as $column => $value) {
            $prefixed['notification.' . $column] = $value;
        }

        return $prefixed;
    }

    /**
     * The targets active for the given user, or the globally active ones without a user - e.g.
     * the mobile target is omitted when no push provider is installed.
     *
     * @return BaseTarget[]
     */
    public function getTargets(?User $user = null): array
    {
        if ($this->_targets === null) {
            // The configurations by target id; a later entry for the same id is merged over the earlier
            // one, so a legacy entry keyed by class name (`WebTarget::class => [...]`) overrides the
            // core `web` entry instead of adding a second target.
            $configs = [];
            foreach ($this->targets as $key => $config) {
                $config = is_array($config) ? $config : ['class' => $config];
                $isClassKey = is_string($key) && str_contains($key, '\\');
                if (!isset($config['class'])) {
                    if (!$isClassKey) {
                        Yii::warning('Notification target "' . $key . '" has no class and is ignored', 'notification');
                        continue;
                    }
                    $config['class'] = $key;
                }
                if (is_string($key) && !$isClassKey) {
                    $config['id'] ??= $key;
                }

                $id = $config['id'] ?? self::defaultTargetId($config['class']);
                $configs[$id] = array_merge($configs[$id] ?? [], $config);
            }

            foreach ($configs as $id => $config) {
                foreach (self::REMOVED_TARGET_PROPERTIES as $property) {
                    if (array_key_exists($property, $config)) {
                        Yii::warning('The "' . $property . '" of the notification target "' . $id . '" is ignored: the property is removed since 1.20', 'notification');
                        unset($configs[$id][$property]);
                    }
                }
            }

            $this->_targets = [];
            foreach ($configs as $config) {
                $this->_targets[] = Yii::createObject($config);
            }
        }

        return array_values(array_filter($this->_targets, fn(BaseTarget $target) => $target->isActive($user)));
    }

    /**
     * The id a target class declares by default.
     */
    private static function defaultTargetId(string $class): string
    {
        $id = class_exists($class) ? ((new \ReflectionClass($class))->getDefaultProperties()['id'] ?? null) : null;

        return is_string($id) && $id !== '' ? $id : $class;
    }

    /**
     * The target of the given id, e.g. `email`.
     *
     * @param string $id the target id; a class name is still accepted, deprecated since 1.20
     */
    public function getTarget(string $id): ?BaseTarget
    {
        foreach ($this->getTargets() as $target) {
            if ($target->id === $id) {
                return $target;
            }
        }

        foreach ($this->getTargets() as $target) {
            if ($target::class === $id) {
                Yii::warning('NotificationManager::getTarget() by class name is deprecated since 1.20, use the target id "' . $target->id . '"', 'notification');
                return $target;
            }
        }

        return null;
    }

    /**
     * The notification classes of the enabled modules: those in a module's `notifications/`
     * directory and those added by handlers of {@see EVENT_SEARCH_MODULE_NOTIFICATIONS}.
     *
     * Since 1.20 these are class names, no longer instances.
     *
     * @return class-string<BaseNotification>[]
     */
    public function getNotifications(): array
    {
        if ($this->_notifications === null) {
            $event = new Event(['result' => $this->findNotificationClasses()]);
            $this->trigger(self::EVENT_SEARCH_MODULE_NOTIFICATIONS, $event);

            $this->_notifications = array_values(array_unique((array)$event->result));
        }

        return $this->_notifications;
    }

    /**
     * The notification classes in the `notifications/` directories of the enabled modules.
     *
     * @return class-string<BaseNotification>[]
     * @since 1.20
     */
    protected function findNotificationClasses(): array
    {
        $classes = [];
        foreach (Yii::$app->moduleManager->getEnabledModules(['includeCoreModules' => true]) as $module) {
            if ($module instanceof Module && $module->hasNotifications()) {
                $classes = array_merge($classes, $module->getNotifications());
            }
        }

        return $classes;
    }

    /**
     * The notification groups: the four core groups and the distinct groups of
     * {@see getNotifications()}, sorted by sort order and title. With a user, only the groups
     * visible to that user.
     *
     * @return NotificationGroup[]
     * @since 1.20
     */
    public function getGroups(?User $user = null): array
    {
        $groups = [
            NotificationGroup::direct(),
            NotificationGroup::social(),
            NotificationGroup::content(),
            NotificationGroup::admin(),
        ];

        foreach ($this->getNotifications() as $class) {
            try {
                $group = $class::group();
            } catch (Throwable $e) {
                Yii::warning('Could not determine the notification group of ' . $class . ': ' . $e->getMessage(), 'notification');
                continue;
            }

            foreach ($groups as $existing) {
                if ($existing->equals($group)) {
                    continue 2;
                }
            }
            $groups[] = $group;
        }

        if ($user !== null) {
            $groups = array_filter($groups, fn(NotificationGroup $group) => $group->isVisible($user));
        }

        usort($groups, fn(NotificationGroup $a, NotificationGroup $b) => [$a->sortOrder, $a->title] <=> [$b->sortOrder, $b->title]);

        return $groups;
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::isFollowingSpace()}
     */
    public function isFollowingSpace(User $user, Space $space)
    {
        return (new NotificationSpaceService())->isFollowingSpace($user, $space);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::getFollowers()}
     */
    public function getFollowers(Content $content)
    {
        return (new NotificationSpaceService())->getFollowers($content);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::getContainerFollowers()}
     */
    public function getContainerFollowers(ContentContainerActiveRecord $container, $public = true)
    {
        return (new NotificationSpaceService())->getContainerFollowers($container, (bool)$public);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::getDefaultNotificationSpaces()}
     */
    public function getDefaultNotificationSpaces(?User $user = null): array
    {
        return (new NotificationSpaceService())->getDefaultNotificationSpaces($user);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::getSpaces()}
     */
    public function getSpaces(User $user)
    {
        return (new NotificationSpaceService())->getSpaces($user);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::isTouchedSettings()}
     */
    public static function isTouchedSettings(User $user): bool
    {
        return NotificationSpaceService::isTouchedSettings($user);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::getNonNotificationSpaces()}
     */
    public function getNonNotificationSpaces(?User $user = null, $limit = 25)
    {
        return (new NotificationSpaceService())->getNonNotificationSpaces($user, (int)$limit);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::setSpaces()}
     */
    public function setSpaces($spaceGuids, ?User $user = null)
    {
        return (new NotificationSpaceService())->setSpaces((array)$spaceGuids, $user);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::resetSpaces()}
     */
    public function resetSpaces()
    {
        (new NotificationSpaceService())->resetSpaces();
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::setSpaceSetting()}
     */
    public function setSpaceSetting(User $user, Space $space, $follow = true)
    {
        (new NotificationSpaceService())->setSpaceSetting($user, $space, (bool)$follow);
    }

    /**
     * @deprecated since 1.20, use {@see NotificationSpaceService::hasSpace()}
     */
    public function hasSpace(Space $space, ?User $user = null): bool
    {
        return (new NotificationSpaceService())->hasSpace($space, $user);
    }
}
