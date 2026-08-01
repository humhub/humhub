<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin;

use humhub\modules\admin\permissions\ManageAllContent;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * Admin Module
 */
class Module extends \humhub\components\Module
{
    /**
     * Impersonation mode: the impersonating user has access to everything, including private content.
     * @since 1.19
     */
    public const IMPERSONATE_MODE_FULL_ACCESS = 'fullAccess';

    /**
     * Impersonation mode: same as [[IMPERSONATE_MODE_FULL_ACCESS]], but each impersonation is written to the log.
     * @since 1.19
     */
    public const IMPERSONATE_MODE_FULL_ACCESS_LOGGED = 'fullAccessLogged';

    /**
     * Impersonation mode: access to private content is
     * denied while impersonating.
     * @since 1.19
     */
    public const IMPERSONATE_MODE_DENY_PRIVATE_CONTENT = 'denyPrivateContent';

    /**
     * Impersonation mode: same as [[IMPERSONATE_MODE_DENY_PRIVATE_CONTENT]], but each impersonation is written to
     * the log.
     * @since 1.19
     */
    public const IMPERSONATE_MODE_DENY_PRIVATE_CONTENT_LOGGED = 'denyPrivateContentLogged';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\admin\controllers';

    /**
     * @inheritdoc
     */
    public $defaultRoute = 'index';

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @inheritdoc
     */
    public $resourcesPath = 'resources';

    /**
     * @var bool check daily for new HumHub version
     */
    public $dailyCheckForNewVersion = true;

    /**
     * @var bool allow admins to impersonate other users
     */
    public $allowUserImpersonate = true;

    /**
     * @var string Defines what an impersonating user is allowed to see and whether impersonations are logged,
     *      see the `IMPERSONATE_MODE_*` constants.
     * @since 1.19
     */
    public string $impersonateMode = self::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT_LOGGED;

    /**
     * @since 1.3.2
     * @var bool show incomplete setup warning on the dashboard for admins
     */
    public $showDashboardIncompleteSetupWarning = true;

    /**
     * @since 1.4
     * @var array list of script urls which should not be cached on the client side
     */
    public $defaultReloadableScripts = [
        'https://platform.twitter.com/widgets.js',
    ];

    /**
     * @var int seconds before delete old pending registrations messages
     */
    public $cleanupPendingRegistrationInterval = 60 * 60 * 24 * 90;

    /**
     * @since 1.17
     * @var bool Enable the "Manage All Content" Permission
     */
    public $enableManageAllContentPermission = false;

    /**
     * @var bool Restrict add/delete member to/from group by group manager
     * @since 1.18
     */
    public bool $groupManagerCannotModifyMembers = false;

    /**
     * @var bool Inherit group manager permissions to sub groups
     * @since 1.18
     */
    public bool $groupManagerInheritance = true;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('AdminModule.base', 'Admin');
    }

    /**
     * @return bool Whether access to private content must be denied while impersonating a user
     * @since 1.19
     */
    public function isImpersonatePrivateContentDenied(): bool
    {
        return in_array($this->impersonateMode, [
            self::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT,
            self::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT_LOGGED,
        ], true);
    }

    /**
     * @return bool Whether each impersonation must be written to the log
     * @since 1.19
     */
    public function isImpersonateLogged(): bool
    {
        return in_array($this->impersonateMode, [
            self::IMPERSONATE_MODE_FULL_ACCESS_LOGGED,
            self::IMPERSONATE_MODE_DENY_PRIVATE_CONTENT_LOGGED,
        ], true);
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof Space) {
            return [];
        }

        if ($contentContainer instanceof User) {
            return [];
        }

        return array_merge([
            new permissions\ManageModules(),
            new permissions\ManageSettings(),
            new permissions\SeeAdminInformation(),
            new permissions\ManageUsers(),
            new permissions\ManageGroups(),
            new permissions\ManageSpaces(),
        ], $this->enableManageAllContentPermission ? [
            new ManageAllContent(),
        ] : []);
    }

    /**
     * @inheritdoc
     */
    public function getNotifications()
    {
        if (Yii::$app->user->isAdmin()) {
            return [
                'humhub\modules\admin\notifications\NewVersionAvailable',
            ];
        }

        return [];
    }

}
