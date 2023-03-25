<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\authclient\interfaces\StandaloneAuthClient;
use Yii;

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
     * @return \yii\web\Response response instance.
     */
    public function auth($client,  $authUrlParams = [])
    {
        $rememberMe = (bool)Yii::$app->request->get('rememberMe');
        Yii::$app->session->set('loginRememberMe', $rememberMe);

        if ($client instanceof StandaloneAuthClient) {
            return $client->authAction($this);
        }

        // Allow adding token and spaceId params in the return URL
        if (property_exists($client, 'parametersToKeepInReturnUrl')) {
            $client->parametersToKeepInReturnUrl = array_merge($client->parametersToKeepInReturnUrl ?? [], ['token', 'spaceId']);
        }

        return parent::auth($client, $authUrlParams);
    }

    /**
     * @inheritdoc
     */
    public function authSuccess($client)
    {
        return parent::authSuccess($client);
    }

}
