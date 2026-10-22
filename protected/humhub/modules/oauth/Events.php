<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth;

use humhub\components\gates\GateInitEvent;
use humhub\helpers\ControllerHelper;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\widgets\AuthenticationMenu;
use humhub\modules\oauth\components\RecentAuthentication;
use humhub\modules\oauth\models\AccessToken;
use humhub\modules\oauth\models\AuthCode;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\RefreshToken;
use humhub\modules\oauth\models\WebSessionCode;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\WebSessionService;
use humhub\modules\oauth\widgets\AppsSections;
use humhub\modules\user\events\UserEvent as AuthUserEvent;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\SecuritySettingsMenu;
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\db\AfterSaveEvent;
use yii\web\UnauthorizedHttpException;
use yii\web\UserEvent;

/**
 * @since 1.20
 */
class Events
{
    /**
     * How long an authorized grant may wait for its code exchange.
     */
    private const ABANDONED_GRANT_AGE = 3600;

    /**
     * The only route whose logout ends the app's sign-in.
     */
    private const LOGOUT_ROUTE = 'user/auth/logout';

    /**
     * Redeems web session codes and replaces whatever session it finds itself.
     */
    private const WEB_SESSION_ROUTE_PREFIX = 'oauth/web-session/';

    /**
     * Removes expired codes, tokens and grants, and grants whose code was never exchanged.
     */
    public static function onCronDailyRun(Event $event): void
    {
        $now = time();

        foreach ([AuthCode::class, AccessToken::class, RefreshToken::class, WebSessionCode::class] as $class) {
            $class::deleteAll(['<', 'expires_at', $now]);
        }

        Grant::deleteAll(['and', ['not', ['expires_at' => null]], ['<', 'expires_at', $now]]);
        Grant::deleteAll([
            'and',
            ['type' => Grant::TYPE_OAUTH],
            ['last_used_at' => null],
            ['<', 'created_at', $now - self::ABANDONED_GRANT_AGE],
        ]);
    }

    /**
     * The web session route logs the user in, so a previous user's open gate must not intercept it.
     */
    public static function onGateInit(GateInitEvent $event): void
    {
        $event->manager->registerInfrastructureRoute('oauth/web-session');
    }

    /**
     * A session established from a grant ends with the grant: an unusable grant logs the
     * session out. A stale binding of a guest or of another user is only dropped.
     *
     * Covers controllers extending [[\humhub\components\Controller]].
     */
    public static function onBeforeAction(ActionEvent $event): void
    {
        // The redemption replaces whatever session it finds itself; a WebView with an old
        // cookie must not lose its new code to a logout and redirect.
        if (str_starts_with($event->action->getUniqueId(), self::WEB_SESSION_ROUTE_PREFIX)) {
            return;
        }

        $user = Yii::$app->user;
        if (!$user->isSessionBased()) {
            // API request not authenticated by the session (token, or guest): leave the session untouched
            return;
        }

        $service = new WebSessionService();
        $grantId = $service->getBoundGrantId();
        if ($grantId === null) {
            return;
        }

        $grant = Grant::findOne(['id' => $grantId]);

        // A session timeout does not log out, so its binding must not carry over: drop it
        // without logging out or revoking anything.
        if ($user->isGuest || ($grant !== null && (int)$grant->user_id !== self::sessionOwnerId())) {
            $service->unbindSession();
            return;
        }

        if ($grant !== null && (new GrantService())->isUsable($grant)) {
            return;
        }

        // Unbound first, so this logout does not revoke anything
        $service->unbindSession();
        $event->isValid = false;

        if ($user->enableSession) {
            $user->logout();
            $user->loginRequired();
            return;
        }

        // Session-authenticated API request: the API's user component is session-less
        Yii::$app->session->destroy();
        throw new UnauthorizedHttpException();
    }

    /**
     * Logging out in the app's WebView (the logout action) revokes the grant: it is an app
     * logout. Any other end of the session (maintenance mode, a required login, account
     * deletion, …) only unbinds it, so the app stays signed in.
     */
    public static function onUserBeforeLogout(UserEvent $event): void
    {
        // Session-less user components (API token logins) never touch the browser session
        if (!$event->sender->enableSession) {
            return;
        }

        $service = new WebSessionService();
        $grantId = $service->getBoundGrantId();
        if ($grantId === null) {
            return;
        }

        $service->unbindSession();
        if (Yii::$app->controller?->action?->getUniqueId() !== self::LOGOUT_ROUTE) {
            return;
        }

        $grant = Grant::findOne(['id' => $grantId]);
        if ($grant !== null) {
            (new GrantService())->revoke($grant);
        }
    }

    /**
     * Any login starts without a previous binding; the web session route binds after its own login.
     * It also drops a recorded interactive login: only the login controller records a new one.
     */
    public static function onUserAfterLogin(UserEvent $event): void
    {
        if (!$event->sender->enableSession) {
            return;
        }

        RecentAuthentication::forgetInteractiveLogin();

        $service = new WebSessionService();
        if ($service->getBoundGrantId() !== null) {
            $service->unbindSession();
        }
    }

    /**
     * Triggered by the login controller only, i.e. for interactive logins (password, LDAP, SSO …),
     * not for remember-me cookie logins.
     */
    public static function onAuthAfterLogin(AuthUserEvent $event): void
    {
        if ($event->user instanceof User) {
            RecentAuthentication::recordInteractiveLogin($event->user);
        }
    }

    /**
     * A new password row is a change unless it is the user's first password.
     */
    public static function onPasswordAfterInsert(AfterSaveEvent $event): void
    {
        /** @var Password $password */
        $password = $event->sender;
        if (!Module::instance()->revokesOnPasswordChange()) {
            return;
        }

        $isChange = Password::find()
            ->where(['user_id' => $password->user_id])
            ->andWhere(['!=', 'id', $password->id])
            ->exists();
        if ($isChange) {
            self::revokeGrantsOfPasswordOwner($password);
        }
    }

    /**
     * Some flows change the password by updating the existing row.
     */
    public static function onPasswordAfterUpdate(AfterSaveEvent $event): void
    {
        if (array_key_exists('password', $event->changedAttributes) && Module::instance()->revokesOnPasswordChange()) {
            self::revokeGrantsOfPasswordOwner($event->sender);
        }
    }

    /**
     * Revokes the user's grants but the one of the app session the change was made in,
     * so changing the password in the app's WebView does not sign the app out.
     */
    private static function revokeGrantsOfPasswordOwner(Password $password): void
    {
        $keep = (new WebSessionService())->getBoundGrantId();

        $service = new GrantService();
        foreach (Grant::findAll(['user_id' => $password->user_id]) as $grant) {
            if ($keep === null || (int)$grant->id !== $keep) {
                $service->revoke($grant);
            }
        }
    }

    /**
     * Adds the "Apps & Tokens" tab to the security settings.
     */
    public static function onSecuritySettingsMenuInit(Event $event): void
    {
        if (!AppsSections::isAvailable()) {
            return;
        }

        /** @var SecuritySettingsMenu $menu */
        $menu = $event->sender;
        $menu->addEntry(new MenuLink([
            'label' => Yii::t('OauthModule.base', 'Apps & Tokens'),
            'url' => ['/oauth/account/index'],
            'sortOrder' => 350,
            'isActive' => ControllerHelper::isActivePath('oauth', 'account'),
        ]));
    }

    /**
     * Adds "Apps & API Access" to the user settings of the administration.
     */
    public static function onAuthenticationMenuInit(Event $event): void
    {
        /** @var AuthenticationMenu $menu */
        $menu = $event->sender;
        $menu->addEntry(new MenuLink([
            'label' => Yii::t('OauthModule.base', 'Apps & API Access'),
            'url' => ['/oauth/admin/index'],
            'sortOrder' => 500,
            'isActive' => ControllerHelper::isActivePath('oauth', 'admin'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class),
        ]));
    }

    /**
     * The user the session belongs to: the impersonating admin while an impersonation runs,
     * since it switches the identity within the same session.
     */
    private static function sessionOwnerId(): ?int
    {
        $user = Yii::$app->user;
        if ($user->getImpersonation()->isActive()) {
            $impersonator = $user->getImpersonation()->getImpersonator();
            return $impersonator === null ? null : (int)$impersonator->id;
        }

        return (int)$user->id;
    }
}
