<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\authclient;

/**
 * 
 */
class GitHub extends \yii\authclient\clients\GitHub
{

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'cssIcon' => 'fa fa-github',
            'buttonBackgroundColor' => '#4078C0',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'username' => 'login',
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
