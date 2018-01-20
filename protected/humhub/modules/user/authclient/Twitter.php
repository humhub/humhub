<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use yii\authclient\OAuth1;

class Twitter extends OAuth1
{
    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
            'cssIcon' => 'fa fa-twitter',
            'buttonBackgroundColor' => '#395697',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://api.twitter.com/oauth/authenticate';

    /**
     * {@inheritdoc}
     */
    public $requestTokenUrl = 'https://api.twitter.com/oauth/request_token';

    /**
     * {@inheritdoc}
     */
    public $requestTokenMethod = 'POST';

    /**
     * {@inheritdoc}
     */
    public $accessTokenUrl = 'https://api.twitter.com/oauth/access_token';

    /**
     * {@inheritdoc}
     */
    public $accessTokenMethod = 'POST';

    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://api.twitter.com/1.1';

    public $attributeParams = [];

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('account/verify_credentials.json', 'GET', $this->attributeParams);
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
}
