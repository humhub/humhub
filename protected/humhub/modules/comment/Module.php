<?php

namespace humhub\modules\comment;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\permissions\CreateComment;
use humhub\modules\comment\notifications\NewComment;
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
            NewComment::class
        ];
    }

    /**
     * Checks if given content object can be commented by current user
     *
     * @param Comment|ContentActiveRecord $object
     * @return boolean can comment
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function canComment($object)
    {
        if(Yii::$app->user->isGuest) {
            return false;
        }

        // Only allow one level of subcomments
        if ($object instanceof Comment && $object->object_model === Comment::class) {
            return false;
        }

        $content = $object->content;

        if ($content->container instanceof Space) {
            $space = $content->container;
            if (!$space->permissionManager->can(CreateComment::class)) {
                return false;
            }
        }

        if ($content->isArchived()) {
            return false;
        }

        return true;
    }

}
