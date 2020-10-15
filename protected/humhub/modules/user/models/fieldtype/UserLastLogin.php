<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Html;
use Yii;

/**
 * UserLastLogin is a virtual profile field
 * that displays the user last login dati
 *
 * @since 1.6
 */
class UserLastLogin extends BaseTypeVirtual
{

    /**
     * @inheritDoc
     * @throws \yii\base\InvalidConfigException
     */
    public function getVirtualUserValue($user, $raw = true)
    {
        if (empty($user->last_login)) {
            return '-';
        }

        return Yii::$app->formatter->asDate($user->last_login,'long');
    }
}
