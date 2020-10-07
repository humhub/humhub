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
 * UserMemberSince is a virtual profile field
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

        return Yii::$app->formatter->asDate($user->created_at,'long');
    }
}
