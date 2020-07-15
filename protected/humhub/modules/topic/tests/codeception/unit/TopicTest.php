<?php

namespace tests\codeception\unit;

use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use tests\codeception\_support\HumHubDbTestCase;

class TopicTest extends HumHubDbTestCase
{

    /**
     * Make sure space admin is allowed to create content by default
     * @throws \yii\base\Exception
     */
    public function testSpaceAdminCanCreateTopic()
    {
        // User2 is moderator in Space3
        $space = Space::findOne(3);
        $this->becomeUser('Admin');

        $post = new Post($space, ['message' => 'Test Post']);
        $this->assertTrue($post->save());

        Topic::attach($post->content, ['_add:NewTopic']);

        $topics = Topic::findByContent($post->content)->all();
        $this->assertCount(1, $topics);
        $this->assertEquals('NewTopic', $topics[0]->name);
    }

    /**
     * Make sure moderator is allowed to create content by default
     * @throws \yii\base\Exception
     */
    public function testSpaceModeratorCanCreateTopic()
    {
        // User2 is moderator in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User2');

        $post = new Post($space, ['message' => 'Test Post']);
        $this->assertTrue($post->save());

        Topic::attach($post->content, ['_add:NewTopic']);

        $topics = Topic::findByContent($post->content)->all();
        $this->assertCount(1, $topics);
        $this->assertEquals('NewTopic', $topics[0]->name);
    }

    /**
     * Make sure user is not allowed to create content by default
     * @throws \yii\base\Exception
     */
    public function testSpaceMemberCanNotCreateTopic()
    {
        // User1 is member in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User1');

        $post = new Post($space, ['message' => 'Test Post']);
        $this->assertTrue($post->save());

        Topic::attach($post->content, ['_add:NewTopic']);

        $topics = Topic::findByContent($post->content)->all();
        $this->assertEmpty($topics);
    }

    /**
     * Make sure user is not allowed to create content by default
     * @throws \yii\base\Exception
     */
    public function testAttachTopicByInstance()
    {
        // User2 is moderator in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User2');

        $post = new Post($space, ['message' => 'Test Post']);
        $this->assertTrue($post->save());

        $topic = new Topic($space);
        $topic->name = 'NewTopic';
        $this->assertTrue($topic->save());

        Topic::attach($post->content, [$topic]);

        $topics = Topic::findByContent($post->content)->all();
        $this->assertCount(1, $topics);
        $this->assertEquals('NewTopic', $topics[0]->name);
    }

    /**
     * Make sure user is not allowed to create content by default
     * @throws \yii\base\Exception
     */
    public function testAttachTopicById()
    {
        // User2 is moderator in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User2');

        $post = new Post($space, ['message' => 'Test Post']);
        $this->assertTrue($post->save());

        $topic = new Topic($space);
        $topic->name = 'NewTopic';
        $this->assertTrue($topic->save());

        Topic::attach($post->content, [$topic->id]);

        $topics = Topic::findByContent($post->content)->all();
        $this->assertCount(1, $topics);
        $this->assertEquals('NewTopic', $topics[0]->name);
    }
}
