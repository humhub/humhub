<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use humhub\modules\user\authclient\interfaces\CustomAuth;
use yii\web\Response;

/**
 * HumHub AuthAction extends Yii's authclient dispatcher with support for
 * AuthClients that don't fit the OAuth1/OAuth2/OpenId families.
 *
 * Dispatch priority:
 *  1. {@see CustomAuth} — self-dispatching clients (SAML, JWT, Passkey,
 *     …). Calls $client->handleAuthRequest(); a returned Response
 *     short-circuits, null signals completion and triggers authSuccess().
 *  2. OAuth1/OAuth2/OpenId — delegated to Yii's parent::auth().
 *
 * @since 1.1.2
 */
class AuthAction extends \yii\authclient\AuthAction
{
    /**
     * @inheritdoc
     */
    public function auth($client, $authUrlParams = [])
    {
        if ($client instanceof CustomAuth) {
            $result = $client->handleAuthRequest();
            return $result instanceof Response ? $result : $this->authSuccess($client);
        }

        return parent::auth($client, $authUrlParams);
    }
}
