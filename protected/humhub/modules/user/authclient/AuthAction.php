<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\helpers\DeviceDetectorHelper;
use humhub\helpers\MobileAppHelper;
use humhub\modules\user\authclient\interfaces\StandaloneAuthClient;
use Yii;
use yii\web\Response;

/**
 * Extended version of AuthAction with AuthClient support which are not handled
 * by AuthAction directly
 *
 * @since 1.1.2
 * @author Luke
 */
class AuthAction extends \yii\authclient\AuthAction
{
    /**
     * @inheritdoc
     *
     * @param StandaloneAuthClient $client
     * @return Response response instance.
     */
    public function auth($client, $authUrlParams = [])
    {
        $rememberMe = (bool)Yii::$app->request->get('rememberMe');
        Yii::$app->session->set('loginRememberMe', $rememberMe);

        if ($client instanceof StandaloneAuthClient) {
            return $client->authAction($this);
        }

        $response = parent::auth($client, $authUrlParams);

        // When called from the mobile app, intercept the server-side redirect to the OAuth
        // provider and hand the URL to the Flutter WebView via postMessage instead, so the
        // app can open it in the in-app-browser.
        if (DeviceDetectorHelper::isAppRequest() && $response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
            $redirectUrl = $response->headers->get('Location');
            if ($redirectUrl !== null) {
                // Reset the redirect response so we can render a normal HTML page.
                $response->setStatusCode(200);
                $response->headers->remove('Location');

                MobileAppHelper::sendAuthClientRedirect($redirectUrl);

                $response->content = Yii::$app->controller->renderContent('');
            }
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function authSuccess($client)
    {
        return parent::authSuccess($client);
    }

}
