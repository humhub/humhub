<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use yii\helpers\Url;

/**
 * EditLink for Comment
 *
 * @since 1.10
 */
class DeleteLink extends CommentControl
{

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return $this->comment->canDelete();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $deleteUrl = Url::to(['/comment/comment/delete',
            'objectModel' => $this->comment->object_model,
            'objectId' => $this->comment->object_id,
            'id' => $this->comment->id,
        ]);

        return $this->render('deleteLink', [
            'deleteUrl' => $deleteUrl,
        ]);
    }

}
