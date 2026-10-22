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

/**
 * Redeems the app's single-use code: logs the user in and binds the session to the grant.
 *
 * Reachable for guests even when guest access is disabled — the code is the credential.
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
        $service = new WebSessionService();
        $grant = is_string($code) && $code !== '' ? $service->redeem($code) : null;

        if ($grant === null) {
            throw new BadRequestHttpException(Yii::t('OauthModule.base', 'This sign-in link is invalid or has expired.'));
        }

        $user = Yii::$app->user;
        if (!$user->isGuest && (int)$user->id !== (int)$grant->user_id) {
            $user->logout();
        }
        if ($user->isGuest) {
            $user->login($grant->user, 0);
        }
        $service->bindSession($grant);

        Yii::$app->response->getHeaders()->set('Referrer-Policy', 'no-referrer');

        return $this->redirect($this->safeReturnUrl(Yii::$app->request->get('returnUrl')) ?? Url::home());
    }

    /**
     * Only a path on this installation: no scheme, no host, no protocol-relative `//`.
     */
    private function safeReturnUrl(mixed $url): ?string
    {
        if (!is_string($url) || !str_starts_with($url, '/') || str_starts_with($url, '//') || str_contains($url, '\\')) {
            return null;
        }

        return $url;
    }
}
