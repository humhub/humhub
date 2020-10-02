<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Html;

/**
 * UserEmail is a virtual profile field
 * that displays the user member since information
 *
 * @since 1.6
 */
class UserMemberSince extends BaseTypeVirtual
{

    /**
     * @inheritDoc
     */
    public function getVirtualUserValue($user, $raw = true)
    {
        if (empty($user->created_at)) {
            return '';
        }

        if ($raw) {
            return Html::encode($user->created_at);
        }
    }
}
