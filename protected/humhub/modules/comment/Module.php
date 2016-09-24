<?php

namespace humhub\modules\comment;

/**
 * CommentModule adds the comment content addon functionalities.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * Maximum comments to load at once
     *
     * @var int
     */
    public $commentsBlockLoadSize = 25;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof \humhub\modules\space\models\Space) {
            return [
                new permissions\CreateComment()
            ];
        }

        return [];
    }
    
    /**
     * @inheritdoc
     */
    public function getNotifications() 
    {
       return [
           'humhub\modules\comment\notifications\NewComment'
       ];
    }

    /**
     * Checks if given content object can be commented
     * 
     * @param \humhub\modules\content\models\Content $content
     * @return boolean can comment
     */
    public function canComment(\humhub\modules\content\models\Content $content)
    {

        if ($content->container instanceof \humhub\modules\space\models\Space) {
            $space = $content->container;
            if (!$space->permissionManager->can(new permissions\CreateComment())) {
                return false;
            }
        }

        return true;
    }

}
