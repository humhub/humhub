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

    public function verify(ContentActiveRecord $content, ?User $user): bool
    {
        // Check global content visibility, private global content is visible for all users
        if (empty($content->content->contentcontainer_id) && $user !== null) {
            return true;
        }

        // Check Guest Visibility
        if (!$user) {
            return $this->checkGuestAccess($content);
        }

        // User can access own content
        if ($content->content->created_by == $user->id) {
            return true;
        }


        // Public visible content
        if ($content->content->isPublic()) {
            return true;
        }

        // Check system admin can see all content module configuration
        if ($user->canViewAllContent()) {
            return true;
        }

        return $content->content->isPrivate() &&
            $content->content->container !== null &&
            $content->content->container->canAccessPrivateContent($user);
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
    private function checkGuestAccess(ContentActiveRecord $content): bool
    {
        if (!$content->content->isPublic() || !AuthHelper::isGuestAccessEnabled()) {
            return false;
        }

        // Global content
        if (!$content->content->container) {
            return $content->content->isPublic();
        }

        if ($content->content->container instanceof Space) {
            return $content->content->isPublic() && $content->content->container->visibility == Space::VISIBILITY_ALL;
        }

        if ($content->content->container instanceof User) {
            return $content->content->isPublic() && $content->content->container->visibility == User::VISIBILITY_ALL;
        }

        return false;
    }

}
