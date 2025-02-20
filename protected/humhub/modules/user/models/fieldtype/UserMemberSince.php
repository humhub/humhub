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

/**
 * UserMemberSince is a virtual profile field
 * that displays the user member since information
 *
 * @since 1.6
 */
class UserMemberSince extends BaseTypeVirtual
{
    /**
     * @inheritdoc
     */
    public function getVirtualUserValue(User $user, bool $raw = true, bool $encode = true): string
    {
        $value = $user->created_at;
        if (empty($value)) {
            return '';
        }

        if (!$raw) {
            $value = Yii::$app->formatter->asDate($value, 'long');
        }

        return $encode ? Html::encode($value) : $value;
    }
}
