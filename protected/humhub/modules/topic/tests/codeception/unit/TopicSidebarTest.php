<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit;

use humhub\modules\content\models\Content;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use humhub\modules\topic\widgets\TopicSidebar;
use tests\codeception\_support\HumHubDbTestCase;

class TopicSidebarTest extends HumHubDbTestCase
{
    public function testHiddenContentCountsButArchivedContentNot()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $this->createPostWithTopic($space, new Topic(['name' => 'Shown', 'contentcontainer_id' => $space->contentcontainer_id]));
        $this->createPostWithTopic($space, new Topic(['name' => 'Hidden', 'contentcontainer_id' => $space->contentcontainer_id]), ['hidden' => 1]);
        $this->createPostWithTopic($space, new Topic(['name' => 'Archived', 'contentcontainer_id' => $space->contentcontainer_id]), ['archived' => 1]);
        $this->createPostWithTopic($space, new Topic(['name' => 'Global Hidden']), ['hidden' => 1]);

        $this->assertSame(['Global Hidden', 'Hidden', 'Shown'], $this->topicNames(new TopicSidebar(['contentContainer' => $space])));
        $this->assertSame(['Global Hidden'], $this->topicNames(new TopicSidebar()), 'global sidebar');
    }

    private function createPostWithTopic(Space $space, Topic $topic, array $content = []): void
    {
        $this->assertTrue($topic->save(), $topic->name);

        $post = new Post(['message' => $topic->name]);
        $post->content->setContainer($space);
        $post->content->setAttributes($content + ['visibility' => Content::VISIBILITY_PUBLIC], false);
        $this->assertTrue($post->save(), $topic->name);

        Topic::attach($post->content, [$topic]);
    }

    /**
     * @return string[]
     */
    private function topicNames(TopicSidebar $sidebar): array
    {
        $names = array_map(static fn(Topic $topic) => $topic->name, $sidebar->getTopics());
        sort($names);

        return $names;
    }
}
