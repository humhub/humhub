<?php

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\notifications\Mentioned;
use tests\codeception\_support\HumHubDbTestCase;

class MentionTest extends HumHubDbTestCase
{

    /**
     * @throws \yii\base\Exception
     */
    public function testPostMention()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 1]);

        $post = new Post(['message' => ' [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "url")']);
        $post->content->container = $space;
        $post->save();

        ProsemirrorRichText::getProcessor($post->message, $post)->parseMentioning();

        $this->assertHasNotification(Mentioned::class, $post);
        $this->assertMailSent(1, 'Mentioned Notification');
    }

    public function testMentionAuthor()
    {
        $this->becomeUser('User2');

        // Mention Admin in Space 1 (Admin is author of post)
        $comment = new Comment([
            'message' => 'Hi [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "url")',
            'object_model' => Post::class,
            'object_id' => 7
        ]);

        $comment->save();

        $this->assertHasNotification(Mentioned::class, $comment);

        // We expect only the Mentioned mail
        $this->assertMailSent(1, 'Comment Notification Mail sent');
    }

    public function testMentionNonAuthor()
    {
        $this->becomeUser('User2');

        // Mention User1 in post
        $comment = new Comment([
            'message' => 'Hi [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839d "url")',
            'object_model' => Post::class,
            'object_id' => 7
        ]);

        $comment->save();

        $this->assertHasNotification(Mentioned::class, $comment);

        // Commented mail for Admin and Mentioned mail for User1
        $this->assertMailSent(2, 'Comment Notification Mail sent');
    }
}
