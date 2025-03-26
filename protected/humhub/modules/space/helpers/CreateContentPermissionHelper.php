<?php

namespace humhub\modules\space\helpers;

use humhub\libs\BasePermission;
use humhub\modules\content\models\ContentContainerDefaultPermission;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Chooser;
use Yii;
use yii\db\Expression;
use yii\web\IdentityInterface;

final class CreateContentPermissionHelper
{
    /**
     * Returns a list of Spaces where the user has a special permission (e.g. CreatePost).
     *
     * @param string $permissionClass
     * @param string|null $keyword
     * @param IdentityInterface|null $user
     * @return array
     */
    public static function findSpaces(
        string $permissionClass,
        ?string $keyword = null,
        ?IdentityInterface $user = null,
    ): array {
        $user = $user ?? Yii::$app->user->identity;

        $spaces = Space::find()
            ->visible($user)
            ->filterBlockedSpaces($user)
            ->andWhere(['space.status' => Space::STATUS_ENABLED]);

        if ($keyword) {
            $spaces->search($keyword);
        }

        if (!$user->isSystemAdmin()) {
            // Check the User can create a Post in the searched Spaces
            $spaces->leftJoin('space_membership', 'space_membership.space_id = space.id')
                ->leftJoin(
                    'contentcontainer_permission',
                    'contentcontainer_permission.contentcontainer_id = space.contentcontainer_id
                    AND contentcontainer_permission.group_id = space_membership.group_id
                    AND contentcontainer_permission.permission_id = :permission_id',
                )
                ->andWhere(['space_membership.user_id' => $user->id])
                ->andWhere(['OR',
                    // Allowed by default
                    ['AND',
                        ['IN', 'space_membership.group_id', self::getDefaultAllowedGroups($permissionClass)],
                        ['IS', 'contentcontainer_permission.permission_id', new Expression('NULL')],
                    ],
                    // Set to allow
                    ['contentcontainer_permission.state' => $permissionClass::STATE_ALLOW],
                ])
                ->addParams(['permission_id' => $permissionClass]);
        }

        $result = [];
        foreach ($spaces->all() as $space) {
            $result[] = Chooser::getSpaceResult($space);
        }

        return $result;
    }

    private static function getDefaultAllowedGroups(string $permissionClass): array
    {
        $defaultAllowedGroups = (new $permissionClass())->defaultAllowedGroups;

        /* @var ContentContainerDefaultPermission[] $defaultPermissions */
        $defaultPermissions = ContentContainerDefaultPermission::find()
            ->where(['contentcontainer_class' => Space::class])
            ->andWhere(['permission_id' => $permissionClass])
            ->all();

        foreach ($defaultPermissions as $defaultPermission) {
            switch ($defaultPermission->state) {
                case BasePermission::STATE_ALLOW:
                    if (!in_array($defaultPermission->group_id, $defaultAllowedGroups)) {
                        $defaultAllowedGroups[] = $defaultPermission->group_id;
                    }
                    break;
                case BasePermission::STATE_DENY:
                    if (($i = array_search($defaultPermission->group_id, $defaultAllowedGroups)) !== false) {
                        unset($defaultAllowedGroups[$i]);
                    }
                    break;
            }
        }

        return $defaultAllowedGroups;
    }
}
