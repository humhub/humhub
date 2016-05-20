<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

/**
 * @inheritdoc
 */
class Google extends \yii\authclient\clients\GoogleOAuth
{

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
            'cssIcon' => 'fa fa-google',
            'buttonBackgroundColor' => '#e0492f',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'username' => 'displayName',
            'firstname' => function ($attributes) {
                return $attributes['name']['givenName'];
            },
            'lastname' => function ($attributes) {
                return $attributes['name']['familyName'];
            },
            'title' => 'tagline',
            'email' => function ($attributes) {
                return $attributes['emails'][0]['value'];
            },
        ];
    }

}
