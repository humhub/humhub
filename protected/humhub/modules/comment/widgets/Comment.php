<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use yii\helpers\Url;
use humhub\components\Widget;

/**
 * This widget is used to show a single comment.
 * It will used by the CommentsWidget and the CommentController to show comments.
 */
class Comment extends Widget
{

    /**
     * @var \humhub\modules\comment\models\Comment the comment
     */
    public $comment = null;

    /**
     * @var boolean indicator that comment has just changed
     */
    public $justEdited = false;

    /**
     * @var string Default style class of div wrapper around Comment block
     */
    public $defaultClass = 'media';

    /**
     * @var string Additional style class of div wrapper around Comment block
     */
    public $additionalClass = '';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $permaUrl = $this->comment->content->container->createUrl('/comment/perma', ['id' => $this->comment->id], true);
        $deleteUrl = Url::to(['/comment/comment/delete',
            'objectModel' => $this->comment->object_model, 'objectId' => $this->comment->object_id, 'id' => $this->comment->id]);
        $editUrl = Url::to(['/comment/comment/edit',
            'objectModel' => $this->comment->object_model, 'objectId' => $this->comment->object_id, 'id' => $this->comment->id]);
        $loadUrl = Url::to(['/comment/comment/load',
            'objectModel' => $this->comment->object_model, 'objectId' => $this->comment->object_id, 'id' => $this->comment->id]);

        return $this->render('comment', [
            'comment' => $this->comment,
            'user' => $this->comment->user,
            'justEdited' => $this->justEdited,
            'permaUrl' => $permaUrl,
            'deleteUrl' => $deleteUrl,
            'editUrl' => $editUrl,
            'loadUrl' => $loadUrl,
            'createdAt' => $this->comment->created_at,
            'canEdit' => $this->comment->canEdit(),
            'canDelete' => $this->comment->canDelete(),
            'class' => trim($this->defaultClass . ' ' . $this->additionalClass),
        ]);
    }

}
