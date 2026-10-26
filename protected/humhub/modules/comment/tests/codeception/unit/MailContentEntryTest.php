<?php

namespace tests\codeception\unit\modules\comment;

use humhub\modules\comment\models\Comment;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\widgets\mails\MailContentEntry;
use tests\codeception\_support\HumHubDbTestCase;

class MailContentEntryTest extends HumHubDbTestCase
{
    public function testOriginatorOfContentIsContentCreator()
    {
        $post = $this->createPost('User2');

        $html = MailContentEntry::widget(['content' => $post]);

        $this->assertStringContainsString('Sara Tester', $html);
    }

    public function testOriginatorOfCommentIsCommentAuthor()
    {
        $post = $this->createPost('User2');

        $this->becomeUser('User1');
        $comment = new Comment(['message' => 'Test comment', 'content_id' => $post->content->id]);
        $this->assertTrue($comment->save());

        $html = MailContentEntry::widget(['content' => $comment]);

        $this->assertStringContainsString('Peter Tester', $html);
        $this->assertStringNotContainsString('Sara Tester', $html);
    }

    public function testExplicitOriginatorIsKept()
    {
        $post = $this->createPost('User2');

        $html = MailContentEntry::widget([
            'content' => $post,
            'originator' => User::findOne(['id' => 4]),
        ]);

        $this->assertStringContainsString('Andreas Tester', $html);
        $this->assertStringNotContainsString('Sara Tester', $html);
    }

    private function createPost(string $userName): Post
    {
        $this->becomeUser($userName);

        $post = new Post(['message' => 'Test post']);
        $post->silentContentCreation = true;
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $this->assertTrue($post->save());

        return $post;
    }
}
