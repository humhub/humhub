<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Html;

/**
 * UserName is a virtual profile field
 * that displays the current user name of the user.
 *
 * @since 1.6
 */
class UserName extends BaseTypeVirtual
{

    /**
     * @inheritDoc
     */
    public function getVirtualUserValue($user, $raw = true)
    {
        if (empty($user->username)) {
            return '';
        }

        return Html::encode($user->username);
    }
}
