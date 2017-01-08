<?php

namespace tests\codeception\unit\modules\space;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\notifications\Mentioned;

class MentionTest extends HumHubDbTestCase
{

    use Specify;

    public function testMentionUser()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 1]);

        $post = new \humhub\modules\post\models\Post(['message' => '@-u01e50e0d-82cd-41fc-8b0c-552392f5839c']);
        $post->content->container = $space;
        $post->save();

        Mentioning::parse($post, $post->message);

        $this->assertHasNotification(Mentioned::class, $post);
        $this->assertMailSent(1, 'Mentioned Notification');
    }

}
