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
class Live extends \yii\authclient\clients\Live
{

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'cssIcon' => 'fa fa-windows',
            'buttonBackgroundColor' => '#0078d7',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'username' => 'name',
            'firstname' => 'first_name',
            'lastname' => 'last_name',
            'email' => function ($attributes) {
                if (isset($attributes['emails']['preferred'])) {
                    return $attributes['emails']['preferred'];
                } elseif (isset($attributes['emails']['account'])) {
                    return $attributes['emails']['account'];
                }
                return "";
            },
        ];
    }

}
