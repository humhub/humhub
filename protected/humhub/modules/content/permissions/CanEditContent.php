<?php

namespace humhub\modules\content\permissions;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\user\models\User;
use Yii;


/**
 *      * Checks if the given user can edit/create this content.
 *
 * A user can edit a content if one of the following conditions are met:
 *
 *  - User is the owner of the content
 *  - User is system administrator and the content module setting `adminCanEditAllContent` is set to true (default)
 *  - The user is granted the managePermission set by the model record class
 *  - The user meets the additional condition implemented by the model records class own `canEdit()` function.
 *
 */
class CanEditContent extends AbstractContentPermission
{

    public function verify(ContentActiveRecord $content, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        // Only owner can edit his content
        if ($content->content->created_by == $user->id) {
            return true;
        }

        // Global Admin can edit/delete arbitrarily content
        if (Yii::$app->getModule('content')->adminCanEditAllContent && $user->isSystemAdmin()) {
            return true;
        }

        // Check additional manage permission for the given container
        if ($content->content->container) {
            if ($content->isNewRecord && $content->hasCreatePermission() && $content->content->container->getPermissionManager($user)->can($content->getCreatePermission())) {
                return true;
            }
            if (!$content->isNewRecord && $content->hasManagePermission() && $content->content->container->getPermissionManager($user)->can($content->getManagePermission())) {
                return true;
            }
        }

        // Check if underlying models canEdit implementation
        // ToDo: Implement this as interface
        if (method_exists($content, 'canEdit') && $content->canEdit($user)) {
            return true;
        }

        return false;
    }
}
