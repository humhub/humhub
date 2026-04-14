<?php

namespace humhub\modules\comment\widgets;

use humhub\modules\comment\models\Comment;
use humhub\modules\like\widgets\LikeLink;
use humhub\widgets\BaseStack;

class CommentEntryLinks extends BaseStack
{
    public Comment $comment;

    /**
     * @inheritdoc
     */
    public $seperator = ' &middot; ';

    public function init()
    {
        $this->initDefaultWidgets();
        parent::init();
    }

    public function initDefaultWidgets()
    {
        $this->addWidget(
            CommentLink::class,
            ['content' => $this->comment->content, 'parentComment' => $this->comment],
            ['sortOrder' => 100],
        );

        $this->addWidget(LikeLink::class, ['object' => $this->comment], ['sortOrder' => 200]);
    }

}
