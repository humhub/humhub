<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use yii\authclient\OAuth2;

class Twitter extends OAuth2
{

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
            'cssIcon' => 'fa-twitter',
            'buttonBackgroundColor' => '#395697',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://api.twitter.com/oauth2/authenticate';

    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://api.twitter.com/oauth2/token';

    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://api.twitter.com/1.1';

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('account/verify_credentials.json', 'GET');
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'twitter';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Twitter';
    }

    /**
     * {@inheritdoc}
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $request->getHeaders()->set('Authorization', 'Bearer '. $accessToken->getToken());
    }
}
