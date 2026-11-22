<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\controllers;

use humhub\components\gates\GateFilter;
use humhub\modules\dav\components\HttpBridge;
use humhub\modules\dav\components\ServerFactory;
use humhub\modules\dav\components\TokenBasicAuth;
use humhub\modules\oauth\Module as OAuthModule;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\user\components\User;
use humhub\modules\user\models\User as UserModel;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Every request below `/dav/`, whatever its method: authenticated by a personal access token
 * ([[TokenBasicAuth]]), then answered by Sabre ([[ServerFactory]]).
 *
 * Session-less like a token request of the API: a DAV client holds no session, and the
 * request never reads or writes one. The gates that apply to API requests (forced password
 * change, maintenance mode) apply here too.
 *
 * @since 1.20
 */
class ServerController extends Controller
{
    /**
     * @inheritdoc
     *
     * Token requests: there is no session to forge a request with.
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            // Runs once the user is known, see beforeAction().
            'gates' => ['class' => GateFilter::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        Yii::$app->set('user', [
            'class' => User::class,
            'identityClass' => UserModel::class,
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => null,
        ]);

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        foreach (HttpBridge::SECURITY_HEADERS as $name => $value) {
            $response->headers->set($name, $value);
        }

        $oauth = OAuthModule::instance();
        $oauth->activeGrant = null;

        $grant = (new TokenBasicAuth())->authenticate(Yii::$app->request);
        if ($grant === null) {
            $response->setStatusCode(401);
            $response->headers->set('WWW-Authenticate', 'Basic realm="' . str_replace(['"', '\\'], '', Yii::$app->name) . '", charset="UTF-8"');
            $response->content = '';

            return false;
        }

        Yii::$app->user->setIdentity($grant->user);
        $oauth->activeGrant = $grant;
        (new GrantService())->touch($grant);

        return parent::beforeAction($action);
    }

    public function actionIndex(): Response
    {
        $server = ServerFactory::create(OAuthModule::instance()->activeGrant, self::baseUri());
        $server->httpRequest = HttpBridge::request(Yii::$app->request);
        $server->start();

        return HttpBridge::respond($server->httpResponse, Yii::$app->response, $server->httpRequest->getMethod() === 'HEAD');
    }

    /**
     * The path of `/dav/` on this installation, with a trailing slash: below the base URL, as
     * the URL rules of the module match it (pretty URLs are required).
     */
    public static function baseUri(): string
    {
        $urlManager = Yii::$app->urlManager;

        return ($urlManager->showScriptName ? $urlManager->getScriptUrl() : $urlManager->getBaseUrl()) . '/dav/';
    }
}
