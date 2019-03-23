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
     * @inheritdoc
     */
    public $authUrl = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.linkedin.com/v2';

    /**
     * @var array list of attribute names, which should be requested from API to initialize user attributes.
     */
    public $attributeNames = [
        'id',
        'firstName',
        'lastName',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(' ', [
                'r_liteprofile',
                'r_emailaddress',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'first_name' => function ($attributes) {
                return array_values($attributes['firstName']['localized'])[0];
            },
            'last_name' => function ($attributes) {
                return array_values($attributes['lastName']['localized'])[0];
            },
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $attributes = $this->api('me?projection=(' . implode(',', $this->attributeNames) . ')', 'GET');
        $scopes = explode(' ', $this->scope);
        if (in_array('r_emailaddress', $scopes, true)) {
            $emails = $this->api('emailAddress?q=members&projection=(elements*(handle~))', 'GET');
            if (isset($emails['elements'][0]['handle~']['emailAddress'])) {
                $attributes['email'] = $emails['elements'][0]['handle~']['emailAddress'];
            }
        }
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $data = $request->getData();
        $data['oauth2_access_token'] = $accessToken->getToken();
        $request->setData($data);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'linkedin';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'LinkedIn';
    }
}
