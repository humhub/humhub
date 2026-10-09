<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use humhub\components\Module;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\MobileTarget;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * What a user switches on or off per channel. Five core categories; a module's notifications join
 * one of them or - by default - form the module's own category through {@see ofModule()}.
 *
 * A switchable category is on for every channel unless its {@see $offByDefault} names the channel;
 * the administrator's defaults and the user's own switches override that, see
 * {@see \humhub\modules\notification\services\NotificationSettingsService}.
 *
 * A module needing several categories constructs them itself, with ids prefixed by its module id:
 *
 * ```php
 * public static function category(): NotificationCategory
 * {
 *     return new NotificationCategory(
 *         'tasks-reminders',
 *         Yii::t('TasksModule.base', 'Task reminders'),
 *         Yii::t('TasksModule.base', 'Reminders of tasks that are due.'),
 *     );
 * }
 * ```
 *
 * @api
 * @since 1.20
 */
final readonly class NotificationCategory
{
    public const ID_DIRECT = 'direct';
    public const ID_SOCIAL = 'social';
    public const ID_FOLLOWERS = 'followers';
    public const ID_CONTENT = 'content';
    public const ID_ADMIN = 'admin';

    /**
     * The ids of the core categories; every other category belongs to a module.
     */
    public const CORE_IDS = [self::ID_DIRECT, self::ID_SOCIAL, self::ID_FOLLOWERS, self::ID_CONTENT, self::ID_ADMIN];

    /**
     * The icon of a module's category unless it names its own.
     */
    public const DEFAULT_ICON = 'ti-puzzle';

    /**
     * @param string $icon the Tabler icon of the settings page, e.g. `ti-heart`
     * @param string[] $offByDefault the ids of the channels (targets) the category is off for unless
     * switched on, e.g. `[MailTarget::ID]`
     * @param string[] $permissions global permission classes (e.g. `ManageUsers::class`) of which a
     * user needs any to see the category on the settings page; `[]` for everyone. Space
     * permissions are not supported.
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $description = '',
        public NotificationPriority $priority = NotificationPriority::Normal,
        public bool $switchable = true,
        public int $sortOrder = 1000,
        public string $icon = self::DEFAULT_ICON,
        public array $offByDefault = [],
        public array $permissions = [],
    ) {
    }

    /**
     * Mentions, invitations, requests, membership and role changes - high priority, not switchable.
     */
    public static function direct(): self
    {
        return new self(
            self::ID_DIRECT,
            Yii::t('NotificationModule.base', 'Directly addressed to you'),
            Yii::t('NotificationModule.base', 'Mentions, invitations and requests.'),
            priority: NotificationPriority::High,
            switchable: false,
            sortOrder: 100,
            icon: 'ti-at',
        );
    }

    /**
     * Comments and likes - low priority.
     */
    public static function social(): self
    {
        return new self(
            self::ID_SOCIAL,
            Yii::t('NotificationModule.base', 'Reactions on my content'),
            Yii::t('NotificationModule.base', 'Comments and likes.'),
            priority: NotificationPriority::Low,
            switchable: true,
            sortOrder: 200,
            icon: 'ti-heart',
        );
    }

    /**
     * New followers - low priority, in the web list only unless switched on for other channels.
     */
    public static function followers(): self
    {
        return new self(
            self::ID_FOLLOWERS,
            Yii::t('NotificationModule.base', 'New followers'),
            Yii::t('NotificationModule.base', 'People who follow you.'),
            priority: NotificationPriority::Low,
            switchable: true,
            sortOrder: 250,
            icon: 'ti-user-plus',
            offByDefault: [MailTarget::ID, MobileTarget::ID],
        );
    }

    /**
     * New content in the user's spaces - normal priority.
     */
    public static function content(): self
    {
        return new self(
            self::ID_CONTENT,
            Yii::t('NotificationModule.base', 'New content in my Spaces'),
            Yii::t('NotificationModule.base', 'New posts and other content in the Spaces selected below.'),
            priority: NotificationPriority::Normal,
            switchable: true,
            sortOrder: 300,
            icon: 'ti-news',
        );
    }

    /**
     * Updates, approvals and other administrative events - normal priority, visible to users who may
     * manage settings, users or spaces.
     */
    public static function admin(): self
    {
        return new self(
            self::ID_ADMIN,
            Yii::t('NotificationModule.base', 'Administration'),
            Yii::t('NotificationModule.base', 'Updates, approvals and other administrative events.'),
            priority: NotificationPriority::Normal,
            switchable: true,
            sortOrder: 400,
            icon: 'ti-shield',
            permissions: [ManageSettings::class, ManageUsers::class, ManageSpaces::class],
        );
    }

    /**
     * The module's own category: id = module id, title = module name - the default
     * {@see BaseNotification::category()} of a notification class.
     *
     * The id shares the namespace with the core ids, so a core module calling this for
     * `admin` or `content` equals the core category by design. A module building its own categories
     * by hand prefixes their id with its module id (e.g. `example-reports`).
     *
     * @param string|null $icon the Tabler icon of the settings page, {@see DEFAULT_ICON} without
     */
    public static function ofModule(string $notificationClass, ?string $icon = null): self
    {
        $moduleId = Yii::$app->moduleManager->getModuleIdByClass($notificationClass);
        if ($moduleId === null) {
            throw new InvalidArgumentException('No module found for ' . $notificationClass);
        }
        $module = Yii::$app->moduleManager->getModule($moduleId, false);
        $name = $module instanceof Module ? $module->getName() : $moduleId;

        return new self(
            $moduleId,
            $name,
            Yii::t('NotificationModule.base', 'Notifications of the {module} module', ['module' => $name]),
            icon: $icon ?? self::DEFAULT_ICON,
        );
    }

    /**
     * Whether this is one of the core categories ({@see CORE_IDS}) rather than a module's own.
     */
    public function isCore(): bool
    {
        return in_array($this->id, self::CORE_IDS, true);
    }

    /**
     * Whether the category is on for the channel when neither the user nor the administrator
     * switched it - always for a category that is not switchable.
     */
    public function isEnabledByDefault(string $targetId): bool
    {
        return !$this->switchable || !in_array($targetId, $this->offByDefault, true);
    }

    /**
     * Whether the user sees the category on the settings page: they hold any of its
     * {@see $permissions}, or it requires none.
     */
    public function isVisible(User $user): bool
    {
        if ($this->permissions === []) {
            return true;
        }

        return (new PermissionManager(['subject' => $user]))->can($this->permissions);
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
