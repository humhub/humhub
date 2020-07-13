<?php

namespace humhub\modules\comment;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\space\models\Space;
use Yii;

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
    public $commentsBlockLoadSize = 10;

    /**
     * @var int maximum comments to show initially
     */
    public $commentsPreviewMax = 2;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof Space) {
            return [
                new permissions\CreateComment()
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('CommentModule.base', 'Comments');
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
     * @param Comment|ContentActiveRecord $object
     * @return boolean can comment
     */
    public function canComment($object)
    {
        // Only allow one level of subcomments
        if ($object instanceof Comment && $object->object_model === Comment::class) {
            return false;
        }

        $content = $object->content;

        if ($content->container instanceof Space) {
            $space = $content->container;
            if (!$space->permissionManager->can(new permissions\CreateComment())) {
                return false;
            }
        }

        if ($content->isArchived()) {
            return false;
        }

        return true;
    }

}
