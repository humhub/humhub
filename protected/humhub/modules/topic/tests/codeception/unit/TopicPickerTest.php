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

    public function testTopicPickerUsesTheTopicSourceWithinItsContainer()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

        $html = TopicPicker::widget(['name' => 'topics', 'contentContainer' => $space]);

        $this->assertStringContainsString('data-picker-source="topic"', $html);
        // data-picker-config is single-quoted Json::htmlEncode() output
        $this->assertStringContainsString('"containerId":' . $space->contentcontainer_id, $html);
        $this->assertStringContainsString('data-picker-max-input="20"', $html);
        // Admin may create topics
        $this->assertStringContainsString('data-picker-allow-new="true"', $html);
        $this->assertStringContainsString('data-picker-new-prefix="_add:"', $html);
    }

    public function testTopicPickerWithOwnUrlStaysLegacy()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);
        $html = TopicPicker::widget(['name' => 'topics', 'contentContainer' => $space, 'url' => '/custom/topics']);

        $this->assertStringContainsString('data-picker-url="/custom/topics"', $html);
        $this->assertStringNotContainsString('data-picker-source', $html);
    }

    public function testTopicImageIsItsColourElseTheModuleIcon()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);
        $coloured = new Topic($space);
        $coloured->name = 'Coloured';
        $coloured->color = '#ff0000';
        $this->assertTrue($coloured->save());

        $html = TopicPicker::widget(['name' => 'topics', 'contentContainer' => $space, 'selection' => [$coloured]]);

        $this->assertMatchesRegularExpression('/<option value="' . $coloured->id . '" selected[^>]*data-image="#ff0000"/', $html);
    }
}
