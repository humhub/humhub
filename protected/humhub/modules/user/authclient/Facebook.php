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
class Facebook extends \yii\authclient\clients\Facebook
{

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 860,
            'popupHeight' => 480,
            'cssIcon' => 'fa fa-facebook',
            'buttonBackgroundColor' => '#395697',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'username' => 'name',
            'firstname' => function ($attributes) {
                list($f, $l) = mb_split(' ', $attributes['name'], 2);
                return $f;
            },
            'lastname' => function ($attributes) {
                list($f, $l) = mb_split(' ', $attributes['name'], 2);
                return $l;
            },
        ];
    }

}
