<?php

namespace humhub\modules\content\permissions;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;


/**
 */
class CanViewContent extends AbstractContentPermission
{

    public function verify(?User $user = null): bool
    {
        // Check global content visibility, private global content is visible for all users
        if (empty($this->content->contentcontainer_id) && $user !== null) {
            return true;
        }

        // Check Guest Visibility
        if (!$user) {
            return $this->checkGuestAccess();
        }

        // User can access own content
        if ($this->content->created_by == $user->id) {
            return true;
        }


        // Public visible content
        if ($this->content->isPublic()) {
            return true;
        }

        // Check system admin can see all content module configuration
        if ($user->canViewAllContent()) {
            return true;
        }

        return $this->content->isPrivate() &&
            $this->container !== null &&
            $this->container->canAccessPrivateContent($user);
    }

    /**
     * Determines if a guest user is able to read this content.
     * This is the case if all of the following conditions are met:
     *
     *  - The content is public
     *  - The `auth.allowGuestAccess` setting is enabled
     *  - The space or profile visibility is set to VISIBILITY_ALL
     *
     * @return bool
     */
    private function checkGuestAccess(): bool
    {
        if (!$this->content->isPublic() || !AuthHelper::isGuestAccessEnabled()) {
            return false;
        }

        // Global content
        if (!$this->container) {
            return true;
        }

        if ($this->container instanceof Space) {
            return $this->container->visibility == Space::VISIBILITY_ALL;
        }

        if ($this->container instanceof User) {
            return $this->container->visibility == User::VISIBILITY_ALL;
        }

        return false;
    }

}
