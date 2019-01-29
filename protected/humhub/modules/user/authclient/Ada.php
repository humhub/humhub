<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;


/**
 * @inheritdoc
 */
class Ada extends Oauth2
{

    public $authUrl = 'https://sso.edemokraciagep.org/ada/v1/oauth2/auth';
    public $tokenUrl = 'https://sso.edemokraciagep.org/ada/v1/oauth2/token';
    public $apiBaseUrl = 'https://sso.edemokraciagep.org/ada/v1/';
    public $scope = 'email';
    public $attributeNames = [
        'email',
    ];
    public $autoRefreshAccessToken = false;
    public $autoExchangeAccessToken = false;
    public $validateAuthState = false;
    protected function initUserAttributes()
    {
        $userAttributes =  $this->api('users/me', 'GET', [
            'fields' => implode(',', $this->attributeNames),
        ]);
	return $userAttributes;
    }

    public function applyAccessTokenToRequest($request, $accessToken)
    {
        parent::applyAccessTokenToRequest($request, $accessToken);

        $headers = $request->getHeaders();
	$headers['Authorization'] ="Basic "  . $accessToken->getToken(); 
        $request->setHeaders($headers);
    }

    protected function defaultName()
    {
        return 'ada';
    }

    protected function defaultTitle()
    {
        return 'Ada';
    }


    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
            'cssIcon' => 'fa fa-user',
            'buttonBackgroundColor' => '#EE3B24',
        ];
    }

    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'username' => function() { return 'ada_user_' . $this->random_str(8); },
	    'firstname' => function() { return 'Felhasznalo';},
	    'lastname' => function() { return 'Ada';},
	    'id' => 'userid',
        ];
    }
    protected function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
      $pieces = [];
      $max = mb_strlen($keyspace, '8bit') - 1;
      for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
      }
      return implode('', $pieces);
    }

    public function buildAuthUrl(array $params = [])
    {
        $defaultParams = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->getReturnUrl(),
        ];
        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }

}
