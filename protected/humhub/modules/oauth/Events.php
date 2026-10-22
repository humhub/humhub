<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth;

use humhub\components\gates\GateInitEvent;
use humhub\modules\oauth\models\AccessToken;
use humhub\modules\oauth\models\AuthCode;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\RefreshToken;
use humhub\modules\oauth\models\WebSessionCode;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\WebSessionService;
use Yii;
use yii\helpers\Url;
use yii\web\UnauthorizedHttpException;

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
     * Removes expired codes, tokens and grants, and grants whose code was never exchanged.
     */
    public static function onCronDailyRun($event): void
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
     * A session established from a grant ends with the grant: a revoked or unusable grant logs
     * the session out. The binding is removed first so this logout does not revoke anything.
     */
    public static function onBeforeAction($event): void
    {
        $service = new WebSessionService();
        $grantId = $service->getBoundGrantId();
        if ($grantId === null) {
            return;
        }

        $grant = Grant::findOne(['id' => $grantId]);
        if ($grant !== null && (new GrantService())->isUsable($grant)) {
            return;
        }

        $service->unbindSession();
        $user = Yii::$app->user;

        if ($user->enableSession) {
            $user->logout();
            $event->isValid = false;
            Yii::$app->response->redirect(Url::to(['/user/auth/login']));
            return;
        }

        // Session-authenticated API request: the API's user component is session-less
        Yii::$app->session->destroy();
        throw new UnauthorizedHttpException();
    }

    /**
     * Logging out of a session established from a grant revokes the grant: a logout in the
     * app's WebView is an app logout.
     */
    public static function onUserBeforeLogout($event): void
    {
        $service = new WebSessionService();
        $grantId = $service->getBoundGrantId();
        if ($grantId === null) {
            return;
        }

        $service->unbindSession();
        $grant = Grant::findOne(['id' => $grantId]);
        if ($grant !== null) {
            (new GrantService())->revoke($grant);
        }
    }
}
