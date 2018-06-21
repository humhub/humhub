<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use yii\authclient\OAuth2;
use yii\web\HttpException;
use Yii;

class LinkedIn extends \yii\authclient\clients\LinkedIn
{

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
            'cssIcon' => 'fa fa-linkedin',
            'buttonBackgroundColor' => '#395697',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://api.linkedin.com/v1';

    /**
     * {@inheritdoc}
     */
    public $attributeNames = [
        'id',
        'email-address',
        'first-name',
        'last-name',
        'public-profile-url',
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(' ', [
                'r_basicprofile',
                'r_emailaddress',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'email' => 'email-address',
            'first_name' => 'first-name',
            'last_name' => 'last-name',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('people/~:(' . implode(',', $this->attributeNames) . ')', 'GET');
    }

    /**
     * {@inheritdoc}
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $data = $request->getData();
        $data['oauth2_access_token'] = $accessToken->getToken();
        $request->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'linkedin';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'LinkedIn';
    }
}
