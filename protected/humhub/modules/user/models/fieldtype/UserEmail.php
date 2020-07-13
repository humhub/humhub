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
 * that displays the current email address of the user.
 *
 * @since 1.6
 */
class UserEmail extends BaseTypeVirtual
{

    /**
     * @inheritDoc
     */
    public function getVirtualUserValue($user, $raw = true)
    {
        if (empty($user->email)) {
            return '';
        }

        if ($raw) {
            return Html::encode($user->email);
        } else {
            return Html::a(Html::encode($user->email), 'mailto:' . $user->email);
        }
    }
}
