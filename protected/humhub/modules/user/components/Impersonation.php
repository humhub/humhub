<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\components\Request;
use humhub\modules\admin\Module as AdminModule;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\User as UserModel;
use Yii;
use yii\base\Component;

/**
 * Impersonation holds the state and configuration of an impersonation session, in which an
 * administrator temporarily takes over another user's identity.
 *
 * While an impersonation is active, private content (content with non-public visibility, private
 * spaces, and module data guarded by [[\humhub\components\access\ControllerAccess::RULE_DENY_IMPERSONATED]])
 * is hidden from the impersonating user by default, see [[$allowPrivateContentAccess]].
 *
 * The impersonation state is bound to the session and fails closed: the impersonated identity never
 * receives an auto-login cookie, and ending an impersonation whose impersonator can no longer be
 * resolved logs the session out instead of silently continuing as the impersonated user.
 *
 * Configuration example (`protected/config/web.php`):
 *
 * ```php
 * 'components' => [
 *     'user' => [
 *         'impersonation' => [
 *             'allowPrivateContentAccess' => true, // pre-1.19 behavior
 *             'log' => false,
 *         ],
 *     ],
 * ],
 * ```
 *
 * @since 1.19
 */
class Impersonation extends Component
{
    /**
     * Session key holding the impersonator's user id and the remembered auto-login cookie duration.
     */
    public const SESSION_KEY = 'impersonator';

    /**
     * @var bool whether the impersonating user keeps access to private content (the behavior of
     * HumHub before 1.19). By default, private content and private spaces are hidden while impersonating.
     */
    public bool $allowPrivateContentAccess = false;

    /**
     * @var bool whether each started impersonation is written to the log (category `user`),
     * naming the impersonating and the impersonated user
     */
    public bool $log = true;

    /**
     * @var User|null the user component this impersonation state belongs to, defaults to `Yii::$app->user`
     */
    public ?User $user = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->user === null) {
            $this->user = Yii::$app->user;
        }
    }

    /**
     * Determines if the current session is an impersonation.
     *
     * This only relies on the session marker, so it stays `true` even when the impersonator
     * can no longer be resolved, see [[stop()]].
     *
     * A session-authenticated API request counts as a session even though its user component
     * is session-less (`enableSession = false`, so a token login can never write into the
     * browser session) — otherwise the private-content restriction below would silently not
     * apply to the platform's own frontend calling the API while impersonating. See
     * [[\humhub\components\Request::$isSessionAuthenticated]].
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return ($this->user->enableSession || $this->isSessionAuthenticatedRequest())
            && !$this->user->getIsGuest()
            && Yii::$app->has('session')
            && Yii::$app->session->has(self::SESSION_KEY);
    }

    /**
     * @see self::isActive() for why this matters; guarded for console applications, whose
     * request component has no such flag.
     */
    private function isSessionAuthenticatedRequest(): bool
    {
        $request = Yii::$app->request ?? null;

        return $request instanceof Request && $request->isSessionAuthenticated;
    }

    /**
     * Determines if the current session may access private content.
     * Only `false` while an impersonation is active and [[$allowPrivateContentAccess]] is disabled.
     *
     * @return bool
     */
    public function canAccessPrivateContent(): bool
    {
        return $this->allowPrivateContentAccess || !$this->isActive();
    }

    /**
     * @return UserModel|null the impersonating admin user, if it can be resolved
     */
    public function getImpersonator(): ?UserModel
    {
        if (!$this->isActive()) {
            return null;
        }

        $data = Yii::$app->session->get(self::SESSION_KEY);
        $id = is_array($data) ? ($data['id'] ?? null) : null;

        return $id === null
            ? null
            : UserModel::findOne(['id' => $id, 'status' => UserModel::STATUS_ENABLED]);
    }

    /**
     * Determines if the current user is allowed to impersonate the given user:
     * impersonation must be enabled ([[AdminModule::$allowUserImpersonate]]), the given user must not
     * be the current user, and the current user needs the `ManageUsers` permission.
     *
     * @param UserModel $user
     * @return bool
     */
    public function canStart(UserModel $user): bool
    {
        if ($this->isActive() || $this->user->getIsGuest()) {
            return false;
        }

        /* @var AdminModule $adminModule */
        $adminModule = Yii::$app->getModule('admin');
        $identity = $this->user->getIdentity();

        return $adminModule->allowUserImpersonate
            && $user->id != $identity->id
            && (new PermissionManager(['subject' => $identity]))->can(ManageUsers::class);
    }

    /**
     * Starts impersonating the given user.
     *
     * @param UserModel $user
     * @return bool whether the impersonation has been started
     */
    public function start(UserModel $user): bool
    {
        if (!$this->canStart($user)) {
            return false;
        }

        $impersonator = $this->user->getIdentity();

        if ($this->log) {
            Yii::warning(sprintf(
                'User "%s" (ID: %d) impersonates user "%s" (ID: %d).',
                $impersonator->displayName,
                $impersonator->id,
                $user->displayName,
                $user->id,
            ), 'user');
        }

        Yii::$app->session->set(self::SESSION_KEY, [
            'id' => $impersonator->id,
            // Restored in stop() — the impersonated identity itself never gets an auto-login cookie
            'duration' => $this->user->getAuthCookieDuration(),
        ]);
        $this->user->switchIdentity($user);

        return true;
    }

    /**
     * Stops the impersonation and switches back to the impersonator's identity.
     *
     * If the impersonator can no longer be resolved (deleted, disabled, or the session predates
     * HumHub 1.19), the user is logged out instead of silently continuing the session.
     *
     * @return bool whether an impersonation was active
     */
    public function stop(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $data = Yii::$app->session->get(self::SESSION_KEY);
        $impersonator = $this->getImpersonator();

        Yii::$app->session->remove(self::SESSION_KEY);

        if ($impersonator === null) {
            $this->user->logout();
        } else {
            $this->user->switchIdentity($impersonator, is_array($data) ? (int)($data['duration'] ?? 0) : 0);
        }

        return true;
    }
}
