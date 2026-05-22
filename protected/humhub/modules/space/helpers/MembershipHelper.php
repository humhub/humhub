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
     * @param bool $useCache use cached result if available
     * @return Space[] the list of spaces
     */
    public static function getOwnSpaces(?User $user = null, bool $useCache = true)
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        $spaceIds = Membership::getUserSpaceIds($user->id, $useCache);
        if (empty($spaceIds)) {
            return [];
        }

        $spaces = Space::find()
            ->where(['id' => $spaceIds, 'created_by' => $user->id])
            ->indexBy('id')
            ->all();

        $result = [];
        // keep original ordering from Membership::getUserSpaceIds
        foreach ($spaceIds as $spaceId) {
            if (isset($spaces[$spaceId])) {
                $result[] = $spaces[$spaceId];
            }
        }

        return $result;
    }

}
