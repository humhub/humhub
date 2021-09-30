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
class EditLink extends CommentControl
{

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return $this->comment->canEdit();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $editUrl = Url::to(['/comment/comment/edit',
            'objectModel' => $this->comment->object_model,
            'objectId' => $this->comment->object_id,
            'id' => $this->comment->id,
        ]);

        $loadUrl = Url::to(['/comment/comment/load',
            'objectModel' => $this->comment->object_model,
            'objectId' => $this->comment->object_id,
            'id' => $this->comment->id,
        ]);

        return $this->render('editLink', [
            'editUrl' => $editUrl,
            'loadUrl' => $loadUrl,
        ]);
    }

}
