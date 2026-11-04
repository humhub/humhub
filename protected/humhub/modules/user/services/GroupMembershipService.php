<?php

namespace humhub\modules\user\services;

use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;

class GroupMembershipService
{
    public Group $group;

    public function __construct(Group $group)
    {
        return false;
    }

    public function addMember(User $user, bool $enableNotifications = true, User $originator = null): bool
    {
        return false;
    }

    public function removeMember(User $user, bool $enableNotifications = true, User $originator = null): bool
    {
        return false;
    }

    public function isMember(User $user): bool
    {
        return false;
    }

    public function isManager(User $user): bool
    {
        return false;
    }

    public function getMembers()
    {
        return [];
    }

    public function getManagers()
    {
        return [];
    }

    public function notifyManagersAboutUserApproval(User $user): bool
    {
    }


}
