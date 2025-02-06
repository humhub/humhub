<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Html;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;

/**
 * UserLastLogin is a virtual profile field
 * that displays the user last login dati
 *
 * @since 1.6
 */
class UserLastLogin extends BaseTypeVirtual
{
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function getVirtualUserValue(User $user, bool $raw = true, bool $encode = true): string
    {
        $value = $user->last_login;
        if (empty($value)) {
            return '-';
        }

        if (!$raw) {
            $value = Yii::$app->formatter->asDate($value, 'long');
        }

        return $encode ? Html::encode($value) : $value;
    }
}
