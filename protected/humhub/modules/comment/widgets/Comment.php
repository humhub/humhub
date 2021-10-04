<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

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
        return $this->render('comment', [
            'comment' => $this->comment,
            'user' => $this->comment->user,
            'createdAt' => $this->comment->created_at,
            'class' => trim($this->defaultClass . ' ' . $this->additionalClass),
        ]);
    }

}
