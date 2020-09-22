<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

use Yii;

/**
 * @inheritdoc
 */
class Google extends \yii\authclient\clients\Google
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
                if (!isset($attributes['given_name'])) {
                    return '';
                }

                return $attributes['given_name'];
            },
            'lastname' => function ($attributes) {
                if (!isset($attributes['family_name'])) {
                    return '';
                }

                return $attributes['family_name'];
            },
            'title' => 'tagline',
            'email' => function ($attributes) {
                return $attributes['email'];
            },
        ];
    }

}
