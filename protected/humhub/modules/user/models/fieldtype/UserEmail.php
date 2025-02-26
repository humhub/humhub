<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Html;
use humhub\modules\user\models\User;

/**
 * UserEmail is a virtual profile field
 * that displays the current email address of the user.
 *
 * @since 1.6
 */
class UserEmail extends BaseTypeVirtual
{
    /**
     * @inheritdoc
     */
    protected function getVirtualUserValue(User $user, bool $raw = true, bool $encode = true): string
    {
        if (empty($user->email)) {
            return '';
        }

        $value = $encode ? Html::encode($user->email) : $user->email;

        if (!$raw) {
            return Html::a($value, 'mailto:' . $user->email);
        }

        return $value;
    }
}
