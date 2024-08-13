<?php

namespace tests\codeception\unit;

use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\TopicPicker;
use tests\codeception\_support\HumHubDbTestCase;

class TopicPickerTest extends HumHubDbTestCase
{
    /**
     * Make sure users with create topic permission sees topic picker
     */
    public function testUserWithCreateTopicPermissionSeesTopicPickerWithSpaceTopics()
    {
        // User2 is moderator in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User2');

        $topic = new Topic($space);
        $topic->name = 'TestTopic';
        $this->assertTrue($topic->save());

        $this->assertTrue(TopicPicker::showTopicPicker($space));
    }

    /**
     * Make sure users with create topic permission sees topic picker even if there are no topics available
     */
    public function testUserWithCreateTopicPermissionSeesTopicPickerWithoutSpaceTopics()
    {
        // User2 is moderator in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User2');
        $this->assertTrue(TopicPicker::showTopicPicker($space));
    }

    /**
     * Make sure users without create topic permission sees topic picker if topics are available
     */
    public function testUserWithoutCreateTopicPermissionSeesTopicPickerWithSpaceTopics()
    {
        // User1 is member in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User1');

        $topic = new Topic($space);
        $topic->name = 'TestTopic';
        $this->assertTrue($topic->save());

        $this->assertTrue(TopicPicker::showTopicPicker($space));
    }

    /**
     * Make sure users without create topic permission does not sees topic picker if there are no topics available
     */
    public function testUserWithoutCreateTopicPermissionDoesNotSeesTopicPickerWithoutSpaceTopics()
    {
        // User1 is member in Space3
        $space = Space::findOne(3);
        $this->becomeUser('User1');
        $this->assertFalse(TopicPicker::showTopicPicker($space));
    }


}
