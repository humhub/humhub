<?php

namespace tests\codeception\unit\modules\comment\widgets;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\CommentControls;
use tests\codeception\_support\HumHubDbTestCase;

class CommentControlsTest extends HumHubDbTestCase
{
    public function testPermalinkUrlIsAbsolute()
    {
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'Permalink test comment',
            'content_id' => 11,
        ]);
        $comment->save();

        $html = CommentControls::widget(['comment' => $comment]);

        $this->assertStringContainsString('data-content-permalink="http', $html);
    }
}
