<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\oauth\services\WebSessionService;
use Yii;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;

/**
 * Redeems the app's single-use code: logs the user in and binds the session to the grant.
 *
 * Reachable for guests even when guest access is disabled — code and secret header are the credential.
 *
 * @since 1.20
 */
class WebSessionController extends Controller
{
    /**
     * @inheritdoc
     */
    public $access = ControllerAccess::class;

    public function actionIndex()
    {
        $code = Yii::$app->request->get('code');
        $secret = Yii::$app->request->headers->get(WebSessionService::SECRET_HEADER);
        $service = new WebSessionService();
        $grant = is_string($code) && $code !== '' && is_string($secret) && $secret !== ''
            ? $service->redeem($code, $secret)
            : null;

        if ($grant === null) {
            throw new BadRequestHttpException(Yii::t('OauthModule.base', 'This sign-in link is invalid or has expired.'));
        }

        // A previous login and its binding are cleared (a logout on this route revokes nothing) and
        // the session id is regenerated: no existing login, perhaps with a pending login
        // verification, gets marked as issued-credential.
        // No auto-login cookie: a cookie login would outlive the grant, as every login drops the binding.
        $user = Yii::$app->user;
        $enableAutoLogin = $user->enableAutoLogin;
        $user->enableAutoLogin = false;
        try {
            if (!$user->isGuest) {
                $user->logout();
            }
            $user->login($grant->user, 0);
        } finally {
            $user->enableAutoLogin = $enableAutoLogin;
        }
        Yii::$app->response->getCookies()->remove(new Cookie($user->identityCookie));
        $service->bindSession($grant);

        Yii::$app->response->getHeaders()
            ->set('Referrer-Policy', 'no-referrer')
            ->set('Cache-Control', 'no-store');

        return $this->redirect($this->safeReturnUrl(Yii::$app->request->get('returnUrl')) ?? Url::home());
    }

    /**
     * Only a path on this installation: no scheme, no host, no protocol-relative `//`, and no
     * whitespace or control characters, which browsers strip (`/\t/evil` becomes `//evil`).
     */
    private function safeReturnUrl(mixed $url): ?string
    {
        if (
            !is_string($url)
            || !str_starts_with($url, '/')
            || str_starts_with($url, '//')
            || str_contains($url, '\\')
            || preg_match('/[\x00-\x20\x7f]/', $url)
        ) {
            return null;
        }

        return $url;
    }
}
