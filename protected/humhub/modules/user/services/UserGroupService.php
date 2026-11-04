<?php

namespace humhub\modules\user\services;

use humhub\modules\user\models\User;

class UserGroupService
{
    public function __construct(User $user)
    {
    }

    public function listGroups($includeParentGroups = true): array
    {
        return [];
    }

    public function listManagingGroups(): array
    {
        return [];
    }
}
