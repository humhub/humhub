<?php

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;

class ContentHardDeleteTest extends HumHubDbTestCase
{
    public function testDeprecatedContentHardDeleteDeletesRecord()
    {
        $post = $this->createPost();

        $this->assertTrue($post->content->hardDelete());

        $this->assertNull(Post::findOne(['id' => $post->id]));
        $this->assertNull(Content::findOne(['id' => $post->content->id]));
    }

    public function testDeprecatedContentHardDeleteOnOrphanedContent()
    {
        $post = $this->createPost();
        $content = $post->content;
        Post::deleteAll(['id' => $post->id]);

        $this->assertTrue(Content::findOne(['id' => $content->id])->hardDelete());

        $this->assertNull(Content::findOne(['id' => $content->id]));
    }

    private function createPost(): Post
    {
        $this->becomeUser('User2');

        $post = new Post(['message' => 'Content hardDelete test post']);
        $post->content->setContainer(Space::findOne(['id' => 2]));
        $post->save();

        return $post;
    }
}
