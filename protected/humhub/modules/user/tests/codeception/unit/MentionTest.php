<?php

namespace tests\codeception\unit\modules\space;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\notifications\Mentioned;
use humhub\modules\comment\models\Comment;
use humhub\modules\post\models\Post;

class MentionTest extends HumHubDbTestCase
{

    use Specify;

    public function testPostMention()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 1]);

        $post = new Post(['message' => '@-u01e50e0d-82cd-41fc-8b0c-552392f5839c']);
        $post->content->container = $space;
        $post->save();

        Mentioning::parse($post, $post->message);

        $this->assertHasNotification(Mentioned::class, $post);
        $this->assertMailSent(1, 'Mentioned Notification');
    }
    
    public function testCreateComment()
    {
        $this->becomeUser('User2');
        
        $comment = new Comment([
            'message' => 'Hi @-u01e50e0d-82cd-41fc-8b0c-552392f5839c',
            'object_model' => Post::className(),
            'object_id' => 7
        ]);

        $comment->save();
        
        $this->assertHasNotification(Mentioned::class, $comment);
        // Commented and Mentioned mail
        $this->assertMailSent(2, 'Comment Notification Mail sent');
    }
}
