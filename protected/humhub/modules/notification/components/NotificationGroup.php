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
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * What a user switches on or off per channel. Four core groups; a module's notifications join
 * one of them or form the module's own group through {@see ofModule()}.
 *
 * @since 1.20
 */
final readonly class NotificationGroup
{
    public const ID_DIRECT = 'direct';
    public const ID_SOCIAL = 'social';
    public const ID_CONTENT = 'content';
    public const ID_ADMIN = 'admin';

    public function __construct(
        public string $id,
        public string $title,
        public string $description = '',
        public NotificationPriority $priority = NotificationPriority::Normal,
        public bool $switchable = true,
        public int $sortOrder = 1000,
    ) {
    }

    /**
     * Mentions, invitations, requests, messages - high priority, not switchable.
     */
    public static function direct(): self
    {
        return new self(
            self::ID_DIRECT,
            Yii::t('NotificationModule.base', 'Directly addressed to you'),
            Yii::t('NotificationModule.base', 'Mentions, invitations, requests and messages. Always on while the channel is on.'),
            priority: NotificationPriority::High,
            switchable: false,
            sortOrder: 100,
        );
    }

    /**
     * Comments, likes, new followers - low priority.
     */
    public static function social(): self
    {
        return new self(
            self::ID_SOCIAL,
            Yii::t('NotificationModule.base', 'Reactions on my content'),
            Yii::t('NotificationModule.base', 'Comments, likes and new followers.'),
            priority: NotificationPriority::Low,
            switchable: true,
            sortOrder: 200,
        );
    }

    /**
     * New content in the user's spaces - normal priority.
     */
    public static function content(): self
    {
        return new self(
            self::ID_CONTENT,
            Yii::t('NotificationModule.base', 'New content in my spaces'),
            Yii::t('NotificationModule.base', 'New posts and other content in the spaces selected below.'),
            priority: NotificationPriority::Normal,
            switchable: true,
            sortOrder: 300,
        );
    }

    /**
     * Updates, approvals and other administrative events - normal priority, visible to administrators only.
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
        );
    }

    /**
     * The module's own group: id = module id, title = module name.
     *
     * The id shares the namespace with the four core ids, so a core module calling this for
     * `admin` or `content` equals the core group by design. A module building its own groups by
     * hand prefixes their id with its module id (e.g. `example-reports`).
     */
    public static function ofModule(string $notificationClass): self
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
        );
    }

    /**
     * The admin group is only shown to users holding an administrative permission.
     */
    public function isVisible(User $user): bool
    {
        if ($this->id !== self::ID_ADMIN) {
            return true;
        }

        return (new PermissionManager(['subject' => $user]))
            ->can([ManageSettings::class, ManageUsers::class, ManageSpaces::class]);
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
