<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\helpers;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;

/**
 * MembershipHelper
 *
 * @since 1.3
 * @author Luke
 */
class MembershipHelper
{

    /**
     * Returns an array of spaces where the given user is owner.
     * 
     * @param User|null $user the user or null for current user
     * @return Space[] the list of spaces
     */
    public static function getOwnSpaces(User $user = null)
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        $spaces = [];
        foreach (Membership::GetUserSpaces($user->id) as $space) {
            if ($space->isSpaceOwner($user->id)) {
                $spaces[] = $space;
            }
        }
        return $spaces;
    }

}
