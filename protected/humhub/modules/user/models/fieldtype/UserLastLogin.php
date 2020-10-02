<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Html;

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
     */
    public function getVirtualUserValue($user, $raw = true)
    {
        if (empty($user->last_login)) {
            return '';
        }

        if ($raw) {
            return Html::encode($user->last_login);
        }
    }
}
